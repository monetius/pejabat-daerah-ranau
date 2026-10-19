<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\SitePart;
use App\Models\User;
use App\Support\ContentEditor;
use App\Support\EventCalendar;
use App\Support\Shortcodes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['calendar.api.enabled' => false]);
        $this->travelTo(now()->setDate(2026, 10, 16)->setTime(12, 0));
    }

    private function signInAdmin(): void
    {
        $this->actingAsVerified(User::factory()->create(['is_admin' => true]));
    }

    /** @return array{title: string, kind: string, start_date: string, status: string} */
    private function payload(): array
    {
        return ['title' => 'Program Komuniti Ranau', 'kind' => 'program', 'start_date' => '2026-10-20', 'status' => 'published'];
    }

    public function test_the_public_feed_includes_sabah_and_malaysian_holidays_and_overlapping_dates(): void
    {
        $this->getJson('/kalendar/data?month=2026-09')->assertOk()->assertJsonFragment(['title' => 'Hari Malaysia'])
            ->assertJsonPath('timezone', 'Asia/Kuala_Lumpur')->assertHeader('Cache-Control', 'no-store, private');
        $this->getJson('/kalendar/data?month=2026-05')->assertOk()->assertJsonFragment(['title' => 'Pesta Kaamatan (Hari Kedua)'])
            ->assertJsonFragment(['title' => 'Hari Wesak']);
        $this->getJson('/kalendar/data?month=2026-04')->assertJsonFragment(['title' => 'Good Friday']);
        $this->getJson('/kalendar/data?month=2026-02')->assertJsonMissing(['title' => 'Thaipusam']);
        $this->getJson('/kalendar/data?month=2028-09')->assertOk()->assertJsonPath('holiday_source', null)
            ->assertJsonMissing(['title' => 'Hari Malaysia']);
    }

    public function test_2027_dates_follow_the_latest_federal_schedule_and_include_sunday_replacements(): void
    {
        $this->getJson('/kalendar/data?month=2027-06')->assertOk()
            ->assertJsonFragment(['title' => 'Hari Keputeraan Rasmi Yang di-Pertuan Agong', 'start_date' => '2027-06-07'])
            ->assertJsonFragment(['title' => 'Awal Muharam (Cuti gantian)', 'start_date' => '2027-06-08'])
            ->assertJsonFragment(['title' => 'Pesta Kaamatan (Cuti gantian)', 'start_date' => '2027-06-01']);
        $this->getJson('/kalendar/data?month=2026-11')->assertJsonFragment(['title' => 'Hari Deepavali (Cuti gantian)', 'start_date' => '2026-11-09']);
        $this->getJson('/kalendar/data?month=2027-05')->assertJsonMissing(['title' => 'Hari Raya Aidiladha (Hari Kedua)']);
    }

    public function test_calendar_management_is_restricted_to_admins(): void
    {
        $this->get('/admin/calendar')->assertRedirect(route('login'));
        $this->post('/admin/calendar', $this->payload())->assertRedirect(route('login'));
        $this->actingAsVerified(User::factory()->create())->get('/admin/calendar')->assertForbidden();
        $this->post('/admin/calendar', $this->payload())->assertForbidden();
        $this->assertDatabaseCount('site_parts', 0);
    }

    public function test_admin_can_add_edit_and_delete_a_published_event_without_html(): void
    {
        $this->signInAdmin();
        $this->post('/admin/calendar', $this->payload())->assertSessionHasNoErrors()->assertRedirect('/admin/calendar?year=2026');
        $entry = array_find(EventCalendar::entries(), fn (array $entry): bool => $entry['kind'] === 'program');
        $this->assertSame('2026-10-20', $entry['end_date']);
        $this->assertNull($entry['start_time']);
        $this->getJson('/kalendar/data?month=2026-10')->assertJsonFragment(['title' => $entry['title']]);
        $this->get('/admin/calendar/'.$entry['id'].'/edit')->assertOk()->assertSee('name="start_date"', false)
            ->assertSee('name="description"', false)->assertDontSee('name="content"', false);
        $this->put('/admin/calendar/'.$entry['id'], [
            ...$this->payload(), 'title' => 'Program Dikemas Kini', 'location' => 'Dewan Ranau',
            'start_time' => '09:00', 'end_time' => '12:00', 'url' => '/hebahan',
            'revision' => EventCalendar::revision($entry),
        ])->assertSessionHasNoErrors();
        $entry = EventCalendar::find($entry['id']);
        $this->getJson('/kalendar/data?month=2026-10')->assertJsonFragment(['title' => 'Program Dikemas Kini', 'location' => 'Dewan Ranau', 'start_time' => '09:00']);
        $this->delete('/admin/calendar/'.$entry['id'], ['revision' => EventCalendar::revision($entry)])->assertSessionHasNoErrors();
        $this->getJson('/kalendar/data?month=2026-10')->assertJsonMissing(['title' => 'Program Dikemas Kini']);
    }

    public function test_drafts_remain_private_and_can_be_published_later(): void
    {
        $this->signInAdmin();
        $this->post('/admin/calendar', [...$this->payload(), 'title' => 'Draf Sulit', 'status' => 'draft'])->assertSessionHasNoErrors();
        $this->getJson('/kalendar/data?month=2026-10')->assertJsonMissing(['title' => 'Draf Sulit']);
        $entry = array_find(EventCalendar::entries(true), fn (array $entry): bool => $entry['title'] === 'Draf Sulit');
        $this->put('/admin/calendar/'.$entry['id'], [...$this->payload(), 'revision' => EventCalendar::revision($entry)])->assertSessionHasNoErrors();
        $this->getJson('/kalendar/data?month=2026-10')->assertJsonFragment(['title' => 'Program Komuniti Ranau']);
    }

    public function test_multi_day_events_appear_across_month_and_year_boundaries(): void
    {
        $this->signInAdmin();
        $this->post('/admin/calendar', [...$this->payload(), 'start_date' => '2026-12-31', 'end_date' => '2027-01-02'])->assertSessionHasNoErrors();
        foreach (['2026-12', '2027-01'] as $month) {
            $this->getJson('/kalendar/data?month='.$month)->assertJsonFragment(['title' => 'Program Komuniti Ranau', 'end_date' => '2027-01-02']);
        }
        $this->getJson('/kalendar/data?month=2026-10')->assertJsonMissing(['title' => 'Program Komuniti Ranau']);
    }

    public function test_invalid_dates_times_statuses_and_unsafe_links_are_rejected(): void
    {
        $this->signInAdmin();
        $cases = [
            ['start_date' => '2026-02-30'], ['end_date' => '2026-10-19'],
            ['end_date' => '2028-10-20'], ['start_time' => '25:00'],
            ['start_time' => '12:00', 'end_time' => '11:00'], ['end_time' => '11:00'],
            ['url' => 'javascript:alert(1)'], ['url' => '//example.com'], ['url' => '/\\example.com'], ['url' => 'http://['],
            ['kind' => 'unsupported'], ['status' => 'invalid'],
        ];
        foreach ($cases as $changes) {
            $this->post('/admin/calendar', [...$this->payload(), ...$changes])->assertSessionHasErrors();
        }
        $this->assertDatabaseCount('site_parts', 0);
        $this->getJson('/kalendar/data?month=2026-13')->assertUnprocessable();
    }

    public function test_admin_can_correct_hide_delete_and_add_public_holidays(): void
    {
        $this->signInAdmin();
        $entry = EventCalendar::find('holiday-2026-11-08-0');
        $data = [...$this->payload(), 'kind' => 'holiday', 'title' => $entry['title'], 'start_date' => '2026-11-09',
            'start_time' => '09:00', 'end_time' => '10:00', 'revision' => EventCalendar::revision($entry)];
        $this->put('/admin/calendar/'.$entry['id'], $data)->assertSessionHasNoErrors();
        $entry = EventCalendar::find($entry['id']);
        $this->assertSame('2026-11-09', $entry['start_date']);
        $this->assertNull($entry['start_time']);
        $this->assertNull($entry['source_url']);
        $this->put('/admin/calendar/'.$entry['id'], [...$data, 'status' => 'draft', 'revision' => EventCalendar::revision($entry)])->assertSessionHasNoErrors();
        $this->getJson('/kalendar/data?month=2026-11')->assertJsonMissing(['title' => 'Hari Deepavali']);
        $entry = EventCalendar::find($entry['id']);
        $this->delete('/admin/calendar/'.$entry['id'], ['revision' => EventCalendar::revision($entry)])->assertSessionHasNoErrors();
        $this->get('/admin/calendar/'.$entry['id'].'/edit')->assertNotFound();
        $this->getJson('/kalendar/data?month=2026-11')->assertJsonMissing(['title' => 'Hari Deepavali']);
        $this->post('/admin/calendar', [...$this->payload(), 'kind' => 'holiday', 'title' => 'Cuti Tambahan', 'start_date' => '2027-01-04'])->assertSessionHasNoErrors();
        $this->getJson('/kalendar/data?month=2027-01')->assertJsonFragment(['title' => 'Cuti Tambahan', 'kind' => 'holiday']);
    }

    public function test_stale_edits_and_deletions_cannot_overwrite_a_newer_version(): void
    {
        $this->signInAdmin();
        $this->post('/admin/calendar', $this->payload())->assertSessionHasNoErrors();
        $entry = array_find(EventCalendar::entries(true), fn (array $entry): bool => $entry['kind'] === 'program');
        $revision = EventCalendar::revision($entry);
        $this->put('/admin/calendar/'.$entry['id'], [...$this->payload(), 'title' => 'Versi Baharu', 'revision' => $revision])->assertSessionHasNoErrors();
        $this->put('/admin/calendar/'.$entry['id'], [...$this->payload(), 'title' => 'Versi Lama', 'revision' => $revision])->assertSessionHasErrors('calendar');
        $this->delete('/admin/calendar/'.$entry['id'], ['revision' => $revision])->assertSessionHasErrors('calendar');
        $this->assertSame('Versi Baharu', EventCalendar::find($entry['id'])['title']);
    }

    public function test_saving_an_event_preserves_other_events_and_existing_site_parts(): void
    {
        SitePart::create(['key' => 'footer', 'label' => 'Footer', 'html' => '<footer>Alamat Ranau</footer>']);
        $this->signInAdmin();
        $this->post('/admin/calendar', $this->payload())->assertSessionHasNoErrors();
        $this->post('/admin/calendar', [...$this->payload(), 'title' => 'Acara Kedua'])->assertSessionHasNoErrors();
        $this->assertCount(2, array_filter(EventCalendar::entries(), fn (array $entry): bool => $entry['kind'] === 'program'));
        $this->assertSame('<footer>Alamat Ranau</footer>', SitePart::findOrFail('footer')->html);
        $this->get('/admin/parts')->assertOk()->assertDontSee('/parts/'.EventCalendar::STORAGE_KEY.'/edit');
        $this->get('/admin/parts/'.EventCalendar::STORAGE_KEY.'/edit')->assertNotFound();
        $this->put('/admin/parts/'.EventCalendar::STORAGE_KEY, ['content' => '{}'])->assertNotFound();
    }

    public function test_calendar_form_and_navigation_have_native_english_labels_and_escape_content(): void
    {
        $this->signInAdmin();
        $this->withCookie('cms_locale', 'en')->get('/admin/calendar')->assertOk()->assertSee('Programs &amp; events calendar', false)->assertSee('+ Add event');
        $this->get('/admin/calendar/create?date=2026-11-12')->assertOk()->assertSee('Start date')->assertSee('Location (optional)')->assertSee('value="2026-11-12"', false);
        $title = '<script>alert(1)</script>';
        $this->post('/admin/calendar', [...$this->payload(), 'title' => $title])->assertSessionHasNoErrors();
        $this->get('/admin/calendar?kind=program')->assertSee(e($title), false)->assertDontSee($title, false);
    }

    public function test_the_homepage_calendar_shortcode_survives_simple_cms_edits(): void
    {
        $body = '<h1>Ranau</h1>'.Shortcodes::CALENDAR;
        $editor = new ContentEditor($body);
        $schema = $editor->schema();
        $updated = $editor->apply(json_encode(['version' => $schema['version'], 'fields' => array_map(fn (array $field): array => ['id' => $field['id'], 'value' => 'Ranau Baharu'], $schema['fields']), 'blocks' => []]));
        $this->assertStringContainsString(Shortcodes::CALENDAR, $updated);
        Page::create(['title' => 'Laman Utama', 'slug' => 'home', 'path' => '', 'status' => 'published', 'body' => $updated]);
        $this->get('/')->assertOk()->assertSee('Ranau Baharu')->assertSee('data-home-calendar', false)
            ->assertSee('data-calendar-url="/kalendar/data"', false)->assertDontSee(Shortcodes::CALENDAR);
    }
}
