<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Page;
use App\Models\User;
use App\Support\ContentEditor;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AnnouncementPublishingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 12)->setTime(12, 0));
        Page::create(['title' => 'Laman Utama', 'slug' => 'home', 'path' => '', 'status' => 'published', 'body' => '[[hebahan-berita]]']);
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function payload(array $changes = [], ?Announcement $current = null): array
    {
        $editor = new ContentEditor((string) $current?->body, true, $current ? hash('sha256', serialize($current->fresh()->getRawOriginal())) : null);
        $schema = $editor->schema();
        $content = json_encode(['version' => $schema['version'], 'fields' => array_map(fn (array $field): array => ['id' => $field['id'], 'value' => $field['value']], $schema['fields']), 'blocks' => $current ? $schema['blocks'] : [['type' => 'paragraph', 'text' => 'Maklumat terkini']]]);
        $changes['content'] = $content;

        return array_merge(['title' => 'Berita baharu', 'category' => 'berita', 'body' => '<p>Maklumat terkini</p>', 'status' => 'published'], $changes);
    }

    public function test_publishing_without_a_date_or_slug_is_immediately_visible(): void
    {
        $this->actingAs($this->admin())->post(route('admin.announcements.store'), $this->payload())
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.announcements.index'));
        $item = Announcement::firstOrFail();
        $this->assertSame('published', $item->publicationState());
        $this->get('/')->assertOk()->assertSee('Berita baharu');
        $this->get('/hebahan')->assertOk()->assertSee('Berita baharu');
        $this->get('/hebahan/'.$item->slug)->assertOk()->assertSee('Maklumat terkini');
    }

    public function test_malaysian_morning_publication_is_live_at_noon(): void
    {
        $this->assertSame('Asia/Kuala_Lumpur', config('app.timezone'));
        $this->actingAs($this->admin())->post(route('admin.announcements.store'), $this->payload(['published_at' => '2026-10-12T10:45']))->assertSessionHasNoErrors();
        $this->assertSame(1, Announcement::published()->count());
        $this->get('/')->assertSee('Berita baharu');
    }

    public function test_news_author_is_saved_editable_and_displayed_on_public_news_views(): void
    {
        $this->actingAs($this->admin())->post(route('admin.announcements.store'), $this->payload(['author' => '  Unit Komunikasi Ranau  ']))->assertSessionHasNoErrors();
        $item = Announcement::firstOrFail();
        $this->assertSame('Unit Komunikasi Ranau', $item->author);
        foreach (['/', '/hebahan', '/hebahan/'.$item->slug] as $url) {
            $this->get($url)->assertOk()->assertSee('Oleh Unit Komunikasi Ranau');
        }
        $this->get(route('admin.announcements.edit', $item))->assertOk()->assertSee('name="author"', false)->assertSee('value="Unit Komunikasi Ranau"', false);
        $this->put(route('admin.announcements.update', $item), $this->payload(['author' => 'Penulis Baharu'], $item))->assertSessionHasNoErrors();
        $this->get('/hebahan/'.$item->slug)->assertSee('Oleh Penulis Baharu');
    }

    public function test_news_author_can_be_omitted_or_cleared_and_legacy_updates_preserve_it(): void
    {
        $this->actingAs($this->admin())->post(route('admin.announcements.store'), $this->payload())->assertSessionHasNoErrors();
        $item = Announcement::firstOrFail();
        $this->assertNull($item->author);
        $this->get('/hebahan/'.$item->slug)->assertDontSee('class="hb-author"', false);
        $item->update(['author' => 'Penulis Asal']);
        $this->put(route('admin.announcements.update', $item), $this->payload([], $item))->assertSessionHasNoErrors();
        $this->assertSame('Penulis Asal', $item->fresh()->author);
        $this->put(route('admin.announcements.update', $item), $this->payload(['author' => ''], $item->fresh()))->assertSessionHasNoErrors();
        $this->assertNull($item->fresh()->author);
        $this->get('/hebahan/'.$item->slug)->assertDontSee('class="hb-author"', false);
    }

    public function test_author_length_is_validated_and_author_text_is_escaped(): void
    {
        $this->actingAs($this->admin())->post(route('admin.announcements.store'), $this->payload(['author' => str_repeat('a', 151)]))->assertSessionHasErrors('author');
        $this->assertDatabaseCount('announcements', 0);
        $author = '<script>alert(1)</script>';
        $this->post(route('admin.announcements.store'), $this->payload(['author' => $author]))->assertSessionHasNoErrors();
        $item = Announcement::firstOrFail();
        foreach (['/', '/hebahan', '/hebahan/'.$item->slug] as $url) {
            $this->get($url)->assertOk()->assertSee('Oleh &lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee($author, false);
        }
    }

    public function test_existing_news_editor_keeps_working_before_the_author_migration_is_applied(): void
    {
        Schema::table('announcements', function (Blueprint $table): void {
            $table->dropColumn('author');
        });
        $this->actingAs($this->admin());
        $this->get(route('admin.announcements.create'))->assertOk()->assertViewHas('authorEnabled', false);
        $this->post(route('admin.announcements.store'), $this->payload(['author' => '']))->assertSessionHasNoErrors();
        $item = Announcement::firstOrFail();
        $this->put(route('admin.announcements.update', $item), $this->payload([], $item))->assertSessionHasNoErrors();
        $this->get('/hebahan/'.$item->slug)->assertOk();
        $this->post(route('admin.announcements.store'), $this->payload(['author' => 'Nama penulis']))->assertSessionHasErrors('author');
    }

    public function test_scheduled_posts_are_labelled_and_hidden_until_due(): void
    {
        $this->actingAs($this->admin())->post(route('admin.announcements.store'), $this->payload(['published_at' => '2026-10-12T13:00']))->assertSessionHasNoErrors();
        $item = Announcement::firstOrFail();
        $this->assertSame('scheduled', $item->publicationState());
        $this->get('/admin/announcements')->assertOk()->assertSee('Dijadualkan')->assertDontSee('href="'.route('announcements.show', $item->slug).'"', false);
        $this->get('/')->assertDontSee('Berita baharu');
        $this->get('/hebahan')->assertDontSee('Berita baharu');
        $this->get('/hebahan/'.$item->slug)->assertNotFound();
        $this->travelTo(now()->setTime(13, 0));
        $this->get('/hebahan/'.$item->slug)->assertOk();
    }

    public function test_clearing_a_future_date_publishes_now(): void
    {
        $item = Announcement::create($this->payload(['slug' => 'scheduled', 'published_at' => now()->addDay()]));
        $this->actingAs($this->admin())->put(route('admin.announcements.update', $item), $this->payload(['published_at' => ''], $item))->assertSessionHasNoErrors();
        $this->assertSame('published', $item->fresh()->publicationState());
        $this->get('/hebahan/'.$item->fresh()->slug)->assertOk();
    }

    public function test_drafts_are_not_visible_to_visitors(): void
    {
        $item = Announcement::create($this->payload(['slug' => 'draft', 'status' => 'draft']));
        $this->get('/')->assertDontSee($item->title);
        $this->get('/hebahan')->assertDontSee($item->title);
        $this->get('/hebahan/draft')->assertNotFound();
    }

    public function test_admin_screens_render_with_the_shared_design(): void
    {
        $this->actingAs($this->admin());
        foreach (['/admin', '/admin/announcements', '/admin/announcements/create', '/admin/pages', '/admin/menu', '/admin/parts', '/admin/media'] as $url) {
            $this->get($url)->assertOk()->assertSee('assets/css/admin.css');
        }
    }

    public function test_non_admin_cannot_publish(): void
    {
        $this->actingAs(User::factory()->create())->post(route('admin.announcements.store'), $this->payload())->assertForbidden();
        $this->assertDatabaseCount('announcements', 0);
    }
}
