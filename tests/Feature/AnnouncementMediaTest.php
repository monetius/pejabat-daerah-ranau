<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use App\Support\ContentEditor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnnouncementMediaTest extends TestCase
{
    use RefreshDatabase;

    private string $directory;

    private string $imageUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = 'uploads/announcement-tests-'.Str::lower(Str::random(12));
        File::makeDirectory(public_path($this->directory), 0755, true);
        $this->imageUrl = '/'.$this->directory.'/existing.png';
        File::put(public_path(ltrim($this->imageUrl, '/')), base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jcXcAAAAASUVORK5CYII='));
        $this->actingAsVerified(User::factory()->create(['is_admin' => true]));
    }

    protected function tearDown(): void
    {
        if (isset($this->directory)) {
            $directory = realpath(public_path($this->directory));
            $root = realpath(public_path('uploads'));
            if ($directory && $root && str_starts_with($directory, $root.DIRECTORY_SEPARATOR)) {
                File::deleteDirectory($directory);
            }
        }
        parent::tearDown();
    }

    /** @return array<string, mixed> */
    private function payload(array $changes = [], ?Announcement $current = null): array
    {
        $editor = new ContentEditor((string) $current?->body, true, $current ? hash('sha256', serialize($current->fresh()->getRawOriginal())) : null);
        $schema = $editor->schema();

        return array_merge([
            'title' => 'Library image announcement', 'category' => 'berita', 'publication_mode' => 'now',
            'content' => json_encode([
                'version' => $schema['version'],
                'fields' => array_map(fn (array $field): array => ['id' => $field['id'], 'value' => $field['value']], $schema['fields']),
                'blocks' => $schema['blocks'] ?: [['type' => 'paragraph', 'text' => 'Announcement details']],
            ], JSON_THROW_ON_ERROR),
        ], $changes);
    }

    public function test_existing_media_can_be_used_as_cover_without_uploading_or_copying_it(): void
    {
        $filesBefore = File::allFiles(public_path($this->directory));
        $this->get('/admin/announcements/create')->assertOk()->assertSee('data-media-select="#image_media"', false);
        $this->getJson('/admin/media')->assertOk()->assertJsonFragment(['url' => $this->imageUrl, 'image' => true]);
        $this->post('/admin/announcements', $this->payload(['image_media' => $this->imageUrl]))
            ->assertSessionHasNoErrors()->assertRedirect('/admin/announcements');
        $item = Announcement::firstOrFail();
        $this->assertSame($this->imageUrl, $item->image);
        $this->assertCount(count($filesBefore), File::allFiles(public_path($this->directory)));
        $this->get('/hebahan/'.$item->slug)->assertOk()->assertSee($this->imageUrl);
        $this->get('/admin/announcements/'.$item->id.'/edit')->assertOk()->assertSee($this->imageUrl);
    }

    public function test_library_selection_can_replace_a_cover_and_ordinary_edits_preserve_it(): void
    {
        $item = Announcement::create(['title' => 'Existing', 'slug' => 'existing', 'category' => 'berita', 'status' => 'draft', 'image' => '/assets/img/coat.png', 'body' => '']);
        $this->put('/admin/announcements/'.$item->id, $this->payload(['image_media' => $this->imageUrl], $item))->assertSessionHasNoErrors();
        $this->assertSame($this->imageUrl, $item->fresh()->image);
        $this->put('/admin/announcements/'.$item->id, $this->payload(['title' => 'Updated', 'image_media' => ''], $item->fresh()))->assertSessionHasNoErrors();
        $this->assertSame($this->imageUrl, $item->fresh()->image);
        $this->put('/admin/announcements/'.$item->id, $this->payload(['remove_image' => '1'], $item->fresh()))->assertSessionHasNoErrors();
        $this->assertNull($item->fresh()->image);
        $this->assertFileExists(public_path(ltrim($this->imageUrl, '/')));
    }

    public function test_invalid_paths_remote_urls_documents_and_non_images_are_rejected(): void
    {
        File::put(public_path($this->directory.'/fake.png'), 'not an image');
        File::put(public_path($this->directory.'/file.pdf'), '%PDF-1.7');
        foreach ([
            'https://example.com/image.png', '//example.com/image.png', '/.env', '/uploads/../../.env',
            '/uploads/%2e%2e/image.png', '/uploads/missing.png', '/'.$this->directory.'/fake.png',
            '/'.$this->directory.'/file.pdf', '/uploads\\..\\secret.png', $this->imageUrl.'?extra=1',
        ] as $url) {
            $this->post('/admin/announcements', $this->payload(['image_media' => $url]))->assertSessionHasErrors('image_media');
        }
        $this->assertDatabaseCount('announcements', 0);
    }

    public function test_existing_site_images_are_available_through_the_same_picker(): void
    {
        $this->post('/admin/announcements', $this->payload(['image_media' => '/assets/img/coat.png']))->assertSessionHasNoErrors();
        $this->assertSame('/assets/img/coat.png', Announcement::firstOrFail()->image);
    }

    public function test_new_upload_has_priority_and_the_original_library_file_is_preserved(): void
    {
        $this->post('/admin/announcements', $this->payload([
            'image' => UploadedFile::fake()->image('replacement.png'), 'image_media' => $this->imageUrl,
        ]))->assertSessionHasNoErrors();
        $item = Announcement::firstOrFail();
        try {
            $this->assertNotSame($this->imageUrl, $item->image);
            $this->assertFileExists(public_path(ltrim($item->image, '/')));
            $this->assertFileExists(public_path(ltrim($this->imageUrl, '/')));
        } finally {
            File::delete(public_path(ltrim($item->image, '/')));
        }
    }
}
