<?php

namespace App\Support;

use App\Models\SitePart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * @phpstan-type Entry array{id: string, title: string, kind: string, start_date: string, end_date: string, start_time: ?string, end_time: ?string, location: ?string, description: ?string, url: ?string, status: string, source_url: ?string, deleted?: bool}
 */
class EventCalendar
{
    public const STORAGE_KEY = 'calendar_events';

    /** @return array<string, Entry> */
    private static function defaults(): array
    {
        $verifiedDates = array_keys(config('calendar.holidays', []));
        $entries = array_filter(app(HolidayFeed::class)->entries(),
            fn (array $entry): bool => ! in_array($entry['start_date'], $verifiedDates, true));
        foreach (config('calendar.holidays', []) as $date => $titles) {
            foreach ($titles as $index => $title) {
                $id = 'holiday-'.$date.'-'.$index;
                $entries[$id] = [
                    'id' => $id, 'title' => $title, 'kind' => 'holiday',
                    'start_date' => $date, 'end_date' => $date,
                    'start_time' => null, 'end_time' => null, 'location' => null,
                    'description' => null, 'url' => null, 'status' => 'published',
                    'source_url' => in_array($date, config('calendar.replacement_dates', []), true)
                        ? config('calendar.replacement_source')
                        : (config('calendar.additional_sources.'.$date) ?? config('calendar.holiday_sources.'.substr($date, 0, 4))),
                ];
            }
        }

        return $entries;
    }

    /** @return array{version: int, entries: array<string, Entry>} */
    private static function decode(?string $json): array
    {
        if ($json === null) {
            return ['version' => 1, 'entries' => []];
        }
        $document = json_decode($json, true, 64, JSON_THROW_ON_ERROR);
        if (! is_array($document) || ($document['version'] ?? null) !== 1 || ! is_array($document['entries'] ?? null)) {
            throw new RuntimeException('Invalid calendar storage format.');
        }

        return $document;
    }

    /** @param list<int> $holidayYears
     * @return list<Entry>
     */
    public static function entries(bool $includeDrafts = false, array $holidayYears = []): array
    {
        app(HolidayFeed::class)->ensureYears($holidayYears);
        $document = self::decode(SitePart::query()->whereKey(self::STORAGE_KEY)->value('html'));
        $entries = array_replace(self::defaults(), $document['entries']);
        $entries = array_filter($entries, fn (array $entry): bool => empty($entry['deleted'])
            && ($includeDrafts || $entry['status'] === 'published'));
        usort($entries, fn (array $first, array $second): int => [$first['start_date'], $first['title']] <=> [$second['start_date'], $second['title']]);

        return array_values($entries);
    }

    /** @return list<int> */
    public static function holidayYears(): array
    {
        $years = array_values(array_unique([...array_keys(config('calendar.holiday_sources', [])), ...app(HolidayFeed::class)->years()]));
        sort($years);

        return $years;
    }

    public static function holidaySource(int $year): ?string
    {
        return config('calendar.holiday_sources.'.$year)
            ?? (in_array($year, app(HolidayFeed::class)->years(), true) ? app(HolidayFeed::class)->sourceUrl($year) : null);
    }

    /** @return Entry */
    public static function find(string $id): array
    {
        $entry = array_find(self::entries(true), fn (array $entry): bool => $entry['id'] === $id);
        abort_if($entry === null, 404);

        return $entry;
    }

    /** @param Entry $entry */
    public static function revision(array $entry): string
    {
        return hash('sha256', json_encode($entry, JSON_THROW_ON_ERROR));
    }

    /** @param array{title: string, kind: string, start_date: string, end_date: string, start_time: ?string, end_time: ?string, location: ?string, description: ?string, url: ?string, status: string} $data
     * @return Entry
     */
    public static function save(array $data, ?string $id = null, ?string $revision = null): array
    {
        return DB::transaction(function () use ($data, $id, $revision): array {
            $part = self::lockedStorage();
            $document = self::decode($part->html);
            if ($id !== null) {
                self::assertCurrent($document, $id, $revision);
            }
            $id ??= 'event-'.Str::ulid();
            $entry = ['id' => $id, ...$data, 'source_url' => null];
            $document['entries'][$id] = $entry;
            $part->update(['html' => json_encode($document, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)]);

            return $entry;
        });
    }

    public static function delete(string $id, string $revision): void
    {
        DB::transaction(function () use ($id, $revision): void {
            $part = self::lockedStorage();
            $document = self::decode($part->html);
            $entry = self::assertCurrent($document, $id, $revision);
            $document['entries'][$id] = [...$entry, 'deleted' => true];
            $part->update(['html' => json_encode($document, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)]);
        });
    }

    private static function lockedStorage(): SitePart
    {
        SitePart::query()->firstOrCreate(['key' => self::STORAGE_KEY], [
            'label' => 'Kalendar', 'html' => '{"version":1,"entries":[]}',
        ]);

        return SitePart::query()->lockForUpdate()->findOrFail(self::STORAGE_KEY);
    }

    /** @param array{version: int, entries: array<string, Entry>} $document
     * @return Entry
     */
    private static function assertCurrent(array $document, string $id, ?string $revision): array
    {
        $entry = $document['entries'][$id] ?? self::defaults()[$id] ?? null;
        abort_if($entry === null || ! empty($entry['deleted']), 404);
        if ($revision === null || ! hash_equals(self::revision($entry), $revision)) {
            throw ValidationException::withMessages(['calendar' => __('Kalendar telah berubah. Muat semula halaman sebelum menyimpan.')]);
        }

        return $entry;
    }
}
