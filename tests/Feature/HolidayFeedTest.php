<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\EventCalendar;
use App\Support\HolidayFeed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

use function Illuminate\Support\defer;

class HolidayFeedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['calendar.api.enabled' => true]);
        $this->freezeTime();
        Http::preventStrayRequests();
    }

    /** @return array{data: list<array{name: string, date: string}>, meta: array{year: int, state: string, count: int}} */
    private function payload(int $year = 2028): array
    {
        $data = [];
        for ($day = 1; $day <= 10; $day++) {
            $data[] = ['name' => 'Cuti Suapan '.$day, 'date' => sprintf('%d-01-%02d', $year, $day)];
        }

        return ['data' => $data, 'meta' => ['year' => $year, 'state' => 'SBH', 'count' => count($data)]];
    }

    /** @return array<string, mixed> */
    private function fields(array $entry): array
    {
        return array_intersect_key($entry, array_fill_keys([
            'title', 'kind', 'start_date', 'end_date', 'start_time', 'end_time', 'location', 'description', 'url', 'status',
        ], true));
    }

    public function test_fetches_sabah_only_caches_it_and_exposes_new_years_to_the_calendar(): void
    {
        Http::fake(fn (Request $request) => Http::response($this->payload((int) $request['year'])));
        $response = $this->getJson('/kalendar/data?month=2028-01')->assertOk()
            ->assertJsonFragment(['title' => 'Cuti Suapan 1', 'start_date' => '2028-01-01'])
            ->assertJsonPath('holiday_source', app(HolidayFeed::class)->sourceUrl(2028));
        $this->assertContains(2028, $response->json('holiday_years'));
        $this->assertCount(10, app(HolidayFeed::class)->snapshot(2028)['entries']);
        Http::assertSent(fn (Request $request): bool => $request['year'] === 2028 && $request['state'] === 'SBH');
        $requests = Http::recorded()->count();
        $this->getJson('/kalendar/data?month=2028-01')->assertOk();
        Http::assertSentCount($requests);
        $this->assertDatabaseCount('site_parts', 0);
    }

    public function test_verified_dates_replacement_holidays_and_original_ids_take_priority(): void
    {
        $payload = $this->payload(2026);
        $dates = ['2026-01-01', '2026-02-17', '2026-02-18', '2026-03-21', '2026-03-22',
            '2026-04-03', '2026-05-30', '2026-05-31', '2026-06-01', '2026-12-24'];
        foreach ($dates as $index => $date) {
            $payload['data'][$index]['date'] = $date;
        }
        Http::fake(['*' => Http::response($payload)]);
        $this->assertTrue(app(HolidayFeed::class)->refresh(2026));
        $entries = EventCalendar::entries();
        $this->assertCount(2, array_filter($entries, fn (array $entry): bool => $entry['start_date'] === '2026-05-31'));
        $this->assertSame('Pesta Kaamatan (Hari Kedua)', EventCalendar::find('holiday-2026-05-31-0')['title']);
        $this->assertSame('Cuti Tambahan Hari Raya Aidilfitri', EventCalendar::find('holiday-2026-03-20-0')['title']);
        $this->assertSame('Hari Deepavali (Cuti gantian)', EventCalendar::find('holiday-2026-11-09-0')['title']);
        $this->assertCount(0, array_filter($entries, fn (array $entry): bool => str_starts_with($entry['id'], 'api-holiday-')));
    }

    public function test_stale_data_is_served_while_a_refresh_runs_after_the_response(): void
    {
        $updated = $this->payload();
        $updated['data'][0]['name'] = 'Nama dikemas kini';
        Http::fakeSequence()->push($this->payload())->push($updated);
        $feed = app(HolidayFeed::class);
        $this->assertTrue($feed->refresh(2028));
        $this->travel(86401)->seconds();
        $feed->ensureYears([2028]);
        Http::assertSentCount(1);
        $this->assertSame('Cuti Suapan 1', array_first($feed->snapshot(2028)['entries'])['title']);
        defer()->invoke();
        Http::assertSentCount(2);
        $this->assertSame('Nama dikemas kini', array_first($feed->snapshot(2028)['entries'])['title']);
    }

    public function test_outages_keep_the_last_successful_data_and_use_a_retry_backoff(): void
    {
        Http::fakeSequence()->push($this->payload())->pushStatus(503);
        $feed = app(HolidayFeed::class);
        $this->assertTrue($feed->refresh(2028));
        $snapshot = $feed->snapshot(2028);
        $this->travel(86401)->seconds();
        $feed->ensureYears([2028]);
        defer()->invoke();
        $this->assertSame($snapshot, $feed->snapshot(2028));
        $this->assertContains(2028, $feed->years());
        $feed->ensureYears([2028]);
        defer()->invoke();
        Http::assertSentCount(2);
        $this->assertSame('Cuti Suapan 1', array_first($feed->entries())['title']);
    }

    public function test_a_first_time_outage_does_not_break_verified_holidays_or_claim_year_coverage(): void
    {
        Http::fake(['*' => Http::response([], 503)]);
        $feed = app(HolidayFeed::class);
        $feed->ensureYears([2028]);
        $feed->ensureYears([2028]);
        Http::assertSentCount(1);
        $this->assertNull($feed->snapshot(2028));
        $this->assertNull(EventCalendar::holidaySource(2028));
        $this->assertNotContains(2028, EventCalendar::holidayYears());
        $this->assertSame('Hari Malaysia', EventCalendar::find('holiday-2026-09-16-0')['title']);
    }

    public function test_invalid_or_incomplete_data_never_replaces_the_good_snapshot(): void
    {
        $valid = $this->payload();
        $sequence = Http::fakeSequence()->push($valid);
        $feed = app(HolidayFeed::class);
        $this->assertTrue($feed->refresh(2028));
        $snapshot = $feed->snapshot(2028);
        $bad = [
            [], ['data' => [], 'meta' => ['year' => 2028, 'state' => 'SBH', 'count' => 0]],
            array_replace_recursive($valid, ['meta' => ['state' => 'SWK']]),
            array_replace_recursive($valid, ['meta' => ['year' => 2029]]),
            array_replace_recursive($valid, ['meta' => ['count' => 100]]),
            array_replace_recursive($valid, ['data' => [0 => ['date' => '2028-02-30']]]),
            array_replace_recursive($valid, ['data' => [0 => ['name' => '<script>alert(1)</script>']]]),
            ['data' => array_fill(0, 10, $valid['data'][0]), 'meta' => $valid['meta']],
            '<html>Not JSON</html>',
        ];
        foreach ($bad as $payload) {
            $sequence->push($payload);
            $this->assertFalse($feed->refresh(2028, true));
            $this->assertSame($snapshot, $feed->snapshot(2028));
        }
    }

    public function test_cms_edits_and_deletions_survive_upstream_date_and_name_changes(): void
    {
        $sequence = Http::fakeSequence()->push($this->payload());
        $feed = app(HolidayFeed::class);
        $feed->refresh(2028);
        $entries = array_values($feed->entries());
        $first = $entries[0];
        $second = $entries[1];
        EventCalendar::save([...$this->fields($first), 'title' => 'Cuti disemak CMS', 'start_date' => '2028-02-01', 'end_date' => '2028-02-01'], $first['id'], EventCalendar::revision($first));
        EventCalendar::delete($second['id'], EventCalendar::revision($second));
        $updated = $this->payload();
        $updated['data'][0]['date'] = '2028-01-17';
        $updated['data'][1]['name'] = 'Nama baharu daripada API';
        $sequence->push($updated);
        $this->assertTrue($feed->refresh(2028, true));
        $this->assertSame('2028-01-17', $feed->entries()[$first['id']]['start_date']);
        $this->assertSame('Nama baharu daripada API', $feed->entries()[$second['id']]['title']);
        $this->assertSame('Cuti disemak CMS', EventCalendar::find($first['id'])['title']);
        $this->assertSame('2028-02-01', EventCalendar::find($first['id'])['start_date']);
        $ids = array_column(EventCalendar::entries(), 'id');
        $this->assertNotContains($second['id'], $ids);
        $this->assertCount(1, array_filter(EventCalendar::entries(), fn (array $entry): bool => $entry['id'] === $first['id']));
    }

    public function test_reordering_and_adding_a_same_day_holiday_does_not_steal_existing_ids(): void
    {
        $sequence = Http::fakeSequence()->push($this->payload());
        $feed = app(HolidayFeed::class);
        $feed->refresh(2028);
        $original = $feed->entries();
        $updated = $this->payload();
        $updated['data'][] = ['name' => 'AAA additional holiday', 'date' => '2028-01-01'];
        $updated['data'] = array_reverse($updated['data']);
        $updated['meta']['count'] = count($updated['data']);
        $sequence->push($updated);
        $this->assertTrue($feed->refresh(2028, true));
        $this->assertCount(11, $feed->entries());
        foreach ($original as $id => $entry) {
            $this->assertSame($entry, $feed->entries()[$id]);
        }
    }

    public function test_admin_can_refresh_and_edit_api_holidays_without_changing_site_parts(): void
    {
        Http::fake(fn (Request $request) => Http::response($this->payload((int) $request['year'])));
        $this->post('/admin/calendar/sync', ['year' => 2028])->assertRedirect('/login');
        $this->actingAsVerified(User::factory()->create())->post('/admin/calendar/sync', ['year' => 2028])->assertForbidden();
        Http::assertNothingSent();
        $this->actingAsVerified(User::factory()->create(['is_admin' => true]))->withCookie('cms_locale', 'en');
        $this->post('/admin/calendar/sync', ['year' => 2028])->assertSessionHasNoErrors()->assertRedirect('/admin/calendar?year=2028');
        $this->get('/admin/calendar?year=2028')->assertOk()->assertSee('Refresh public holidays')->assertSee('API source')->assertSee('Last successful feed update:');
        $entry = array_first(app(HolidayFeed::class)->entries());
        $this->get('/admin/calendar/'.$entry['id'].'/edit')->assertOk()->assertSee('Your CMS changes are preserved when it synchronizes.');
        $this->assertDatabaseCount('site_parts', 0);
    }

    public function test_sync_command_validates_years_and_uses_cached_results_when_fresh(): void
    {
        Http::fake(['*' => Http::response($this->payload())]);
        $this->artisan('calendar:sync-holidays', ['--year' => [2028]])->assertSuccessful();
        $this->artisan('calendar:sync-holidays', ['--year' => [2028]])->assertSuccessful();
        Http::assertSentCount(1);
        $this->artisan('calendar:sync-holidays', ['--year' => ['not-a-year']])->assertExitCode(2);
        Http::assertSentCount(1);
    }

    public function test_database_cache_and_locks_keep_snapshots_across_requests(): void
    {
        config(['cache.default' => 'database']);
        Http::fake(['*' => Http::response($this->payload())]);
        $feed = app(HolidayFeed::class);
        $this->assertTrue($feed->refresh(2028));
        $this->assertSame([2028], $feed->years());
        $feed->ensureYears([2028]);
        Http::assertSentCount(1);
        $this->assertCount(10, $feed->entries());
    }

    public function test_disabling_the_feed_keeps_verified_dates_and_does_not_make_requests(): void
    {
        config(['calendar.api.enabled' => false]);
        $feed = app(HolidayFeed::class);
        $feed->ensureYears([2028]);
        $this->assertFalse($feed->refresh(2028, true));
        $this->assertSame([], $feed->entries());
        $this->assertSame('Hari Malaysia', EventCalendar::find('holiday-2026-09-16-0')['title']);
        $this->artisan('calendar:sync-holidays')->assertSuccessful();
        Http::assertNothingSent();
    }

    public function test_connection_failure_uses_fallback_and_does_not_mark_an_unknown_year_available(): void
    {
        Http::fake(function (): never {
            throw new ConnectionException('Connection unavailable');
        });
        $feed = app(HolidayFeed::class);
        $this->assertFalse($feed->refresh(2028));
        $this->assertNull($feed->snapshot(2028));
        $this->assertNull(EventCalendar::holidaySource(2028));
        $this->assertSame('Hari Malaysia', EventCalendar::find('holiday-2026-09-16-0')['title']);
    }

    public function test_tentative_dates_are_labelled_and_changed_api_records_reject_stale_cms_edits(): void
    {
        $payload = $this->payload();
        $payload['data'][0]['is_subject_to_change'] = true;
        $updated = $payload;
        $updated['data'][0]['date'] = '2028-01-17';
        Http::fakeSequence()->push($payload)->push($updated);
        $feed = app(HolidayFeed::class);
        $feed->refresh(2028);
        $entry = array_first($feed->entries());
        $this->assertSame('Tarikh ini tertakluk kepada pengumuman rasmi.', $entry['description']);
        $feed->refresh(2028, true);
        $this->actingAsVerified(User::factory()->create(['is_admin' => true]));
        $this->put('/admin/calendar/'.$entry['id'], [...$this->fields($entry), 'revision' => EventCalendar::revision($entry)])
            ->assertSessionHasErrors('calendar');
        $this->assertSame('2028-01-17', EventCalendar::find($entry['id'])['start_date']);
    }
}
