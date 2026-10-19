<?php

namespace App\Support;

use DateTimeImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;
use UnexpectedValueException;

use function Illuminate\Support\defer;

/**
 * @phpstan-import-type Entry from EventCalendar
 *
 * @phpstan-type Snapshot array{fetched_at: int, entries: array<string, Entry>}
 */
class HolidayFeed
{
    public function enabled(): bool
    {
        return (bool) config('calendar.api.enabled', true);
    }

    public function sourceUrl(int $year): string
    {
        return config('calendar.api.endpoint').'?'.http_build_query(['year' => $year, 'state' => 'SBH']);
    }

    /** @param list<int> $years */
    public function ensureYears(array $years): void
    {
        if (! $this->enabled()) {
            return;
        }
        foreach (array_unique($years) as $year) {
            if ($year < 2000 || $year > 2100 || Cache::has($this->key('checked:'.$year))) {
                continue;
            }
            if ($this->snapshot($year) !== null) {
                defer(fn (): bool => $this->refresh($year), 'calendar-holidays-'.$year);
            } else {
                $this->refresh($year);
            }
        }
    }

    /** @return array<string, Entry> */
    public function entries(): array
    {
        $entries = [];
        foreach ($this->years() as $year) {
            $entries = array_replace($entries, $this->snapshot($year)['entries'] ?? []);
        }

        return $entries;
    }

    /** @return list<int> */
    public function years(): array
    {
        if (! $this->enabled()) {
            return [];
        }
        $years = array_values(array_filter(Cache::get($this->key('years'), []),
            fn (int $year): bool => $this->snapshot($year) !== null));
        sort($years);

        return $years;
    }

    /** @return Snapshot|null */
    public function snapshot(int $year): ?array
    {
        return $this->enabled() ? Cache::get($this->key('snapshot:'.$year)) : null;
    }

    public function refresh(int $year, bool $force = false): bool
    {
        if (! $this->enabled() || $year < 2000 || $year > 2100) {
            return false;
        }
        $lock = Cache::lock($this->key('refresh:'.$year), 30);
        if (! $lock->get()) {
            return false;
        }
        try {
            if (! $force && Cache::has($this->key('checked:'.$year))) {
                return $this->snapshot($year) !== null;
            }
            $certificateBundle = config('calendar.api.ca_bundle');
            $response = Http::acceptJson()->connectTimeout(1)->timeout(3)->withoutRedirecting()
                ->withOptions(['verify' => filled($certificateBundle) ? $certificateBundle : true])
                ->get(config('calendar.api.endpoint'), ['year' => $year, 'state' => 'SBH'])->throw();
            if (strlen($response->body()) > 262144) {
                throw new UnexpectedValueException('Holiday response exceeds maximum size.');
            }
            $entries = $this->normalize($response->json(), $year);
            $snapshot = ['fetched_at' => now()->timestamp, 'entries' => $entries];
            Cache::lock($this->key('index-lock'), 10)->block(2, function () use ($year, $snapshot): void {
                $years = array_values(array_unique([...Cache::get($this->key('years'), []), $year]));
                Cache::forever($this->key('snapshot:'.$year), $snapshot);
                Cache::forever($this->key('years'), $years);
            });
            Cache::put($this->key('checked:'.$year), true, (int) config('calendar.api.refresh_seconds', 86400));

            return true;
        } catch (Throwable $exception) {
            Cache::put($this->key('checked:'.$year), true, (int) config('calendar.api.retry_seconds', 3600));
            Log::warning('Sabah holiday refresh failed; retaining cached and verified holidays.', [
                'year' => $year, 'exception_type' => $exception::class,
            ]);

            return false;
        } finally {
            $lock->release();
        }
    }

    /** @return array<string, Entry> */
    private function normalize(mixed $payload, int $year): array
    {
        if (! is_array($payload) || ! is_array($payload['data'] ?? null) || ! array_is_list($payload['data'])
            || ($payload['meta']['year'] ?? null) !== $year || ($payload['meta']['state'] ?? null) !== 'SBH'
            || ($payload['meta']['count'] ?? null) !== count($payload['data'])
            || count($payload['data']) < 10 || count($payload['data']) > 100) {
            throw new UnexpectedValueException('Invalid or incomplete Sabah holiday response.');
        }
        $rows = [];
        foreach ($payload['data'] as $row) {
            $date = is_array($row) && is_string($row['date'] ?? null) ? $row['date'] : '';
            $parsed = preg_match('/^'.$year.'-\d{2}-\d{2}$/D', $date) ? DateTimeImmutable::createFromFormat('!Y-m-d', $date) : false;
            $title = is_array($row) && is_string($row['name'] ?? null) ? trim($row['name']) : '';
            if (! $parsed || $parsed->format('Y-m-d') !== $date || $title === '' || mb_strlen($title) > 180
                || preg_match('/[\x00-\x1f<>]/u', $title)) {
                throw new UnexpectedValueException('Invalid holiday date or name.');
            }
            $name = $this->normalizedName($title);
            $rows[$date.'|'.$name] = ['date' => $date, 'title' => $title, 'name' => $name,
                'tentative' => ($row['is_subject_to_change'] ?? false) === true];
        }
        $rows = array_values($rows);
        if (count($rows) < 10) {
            throw new UnexpectedValueException('Holiday response has too few distinct records.');
        }
        usort($rows, fn (array $first, array $second): int => [$first['date'], $first['name']] <=> [$second['date'], $second['name']]);
        $previous = $this->snapshot($year)['entries'] ?? [];
        $used = [];
        $occurrences = [];
        $entries = [];
        $newNames = array_column($rows, 'name');
        foreach ($rows as $row) {
            $occurrence = $occurrences[$row['name']] ?? 0;
            $occurrences[$row['name']] = $occurrence + 1;
            $match = array_find($previous, fn (array $entry): bool => ! isset($used[$entry['id']]) && $this->normalizedName($entry['title']) === $row['name']);
            if ($match === null) {
                $sameDate = array_filter($previous, fn (array $entry): bool => ! isset($used[$entry['id']]) && $entry['start_date'] === $row['date']
                    && ! in_array($this->normalizedName($entry['title']), $newNames, true));
                $newOnDate = array_filter($rows, fn (array $candidate): bool => $candidate['date'] === $row['date']);
                $match = count($sameDate) === 1 && count($newOnDate) === 1 ? array_first($sameDate) : null;
            }
            $id = $match['id'] ?? 'api-holiday-'.$year.'-'.substr(hash('sha256', $row['name']), 0, 24).'-'.$occurrence;
            $used[$id] = true;
            $entries[$id] = [
                'id' => $id, 'title' => $row['title'], 'kind' => 'holiday',
                'start_date' => $row['date'], 'end_date' => $row['date'], 'start_time' => null, 'end_time' => null,
                'location' => null,
                'description' => ($row['tentative'] ?? false) ? 'Tarikh ini tertakluk kepada pengumuman rasmi.' : null,
                'url' => null, 'status' => 'published',
                'source_url' => $this->sourceUrl($year),
            ];
        }

        return $entries;
    }

    private function normalizedName(string $name): string
    {
        return Str::lower(Str::squish($name));
    }

    private function key(string $suffix): string
    {
        return 'calendar-holidays:v1:'.substr(hash('sha256', (string) config('calendar.api.endpoint')), 0, 16).':'.$suffix;
    }
}
