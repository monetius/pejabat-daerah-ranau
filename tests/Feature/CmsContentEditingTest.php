<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\SitePart;
use App\Models\User;
use App\Support\ContentEditor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CmsContentEditingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsVerified(User::factory()->create(['is_admin' => true]));
    }

    /** @param array<string, mixed> $schema
     * @return array<string, mixed>
     */
    private function payload(array $schema): array
    {
        return ['version' => $schema['version'], 'fields' => array_map(fn (array $field): array => ['id' => $field['id'], 'value' => $field['value']], $schema['fields']), 'blocks' => $schema['blocks']];
    }

    private function page(): Page
    {
        return Page::create([
            'title' => 'Pelan', 'slug' => 'pelan', 'path' => 'profil-daerah/pelan', 'status' => 'published',
            'body_class' => 'has-banner', 'styles' => 'pelan-pembangunan.css',
            'body' => '<section class="original"><h1>Pelan Ranau</h1><p>Maklumat <strong>penting</strong> di sini.</p><a href="/uploads/plan.pdf">Dokumen</a><img src="/assets/img/banner.jpg" alt="Ranau"><details><summary>Teruskan</summary><table><tbody><tr><td>Bandar</td></tr></tbody></table></details><svg viewBox="0 0 20 20"><path d="M0 0L20 20"/></svg></section><script>window.original = true;</script><style>.original{display:block}</style>[[hebahan-berita]]',
            'scripts' => '<script src="/assets/js/location-directory.js"></script>',
        ]);
    }

    public function test_pages_announcements_and_site_parts_use_the_simple_editor(): void
    {
        $page = $this->page();
        $part = SitePart::create(['key' => 'footer', 'label' => 'Footer', 'html' => '<footer><p>Ranau</p></footer>']);
        foreach (['/admin/pages/create', '/admin/pages/'.$page->id.'/edit', '/admin/announcements/create', '/admin/parts/'.$part->key.'/edit'] as $url) {
            $this->get($url)->assertOk()->assertSee('content-editor.js')->assertSee('Editor mudah')
                ->assertDontSee('CodeMirror')->assertDontSee('name="body"', false)->assertDontSee('name="html"', false)
                ->assertDontSee('name="styles"', false)->assertDontSee('name="scripts"', false);
        }
    }

    public function test_new_pages_render_safe_heading_paragraph_list_image_and_download_blocks(): void
    {
        $payload = $this->payload((new ContentEditor(''))->schema());
        $payload['blocks'] = [
            ['type' => 'heading', 'level' => 'h1', 'text' => 'Maklumat baharu'],
            ['type' => 'paragraph', 'text' => "Baris pertama\n<script>alert(1)</script>"],
            ['type' => 'list', 'text' => "Satu\nDua"],
            ['type' => 'image', 'url' => '/assets/img/banner.jpg', 'alt' => 'Pemandangan', 'caption' => 'Daerah Ranau'],
            ['type' => 'download', 'url' => '/uploads/plan.pdf', 'text' => 'Muat turun PDF'],
        ];
        $this->post('/admin/pages', ['title' => 'Halaman baharu', 'status' => 'published', 'content' => json_encode($payload)])
            ->assertSessionHasNoErrors()->assertRedirect();
        $page = Page::firstOrFail();
        $this->get('/'.$page->path)->assertOk()->assertSee('Maklumat baharu')->assertSee('Pemandangan')
            ->assertSee('<li>Satu</li>', false)->assertSee('Muat turun PDF')->assertDontSee('<script>alert(1)</script>', false);
        $schema = $this->get('/admin/pages/'.$page->id.'/edit')->viewData('editor');
        $this->assertCount(5, $schema['blocks']);
        $this->assertSame("Baris pertama\n<script>alert(1)</script>", $schema['blocks'][1]['text']);
    }

    public function test_list_styles_render_correct_markers_and_survive_reopening_and_changing_style(): void
    {
        foreach (['disc' => null, 'circle' => null, 'square' => null, 'decimal' => '1', 'lower-alpha' => 'a', 'upper-alpha' => 'A', 'lower-roman' => 'i', 'upper-roman' => 'I'] as $style => $type) {
            $editor = new ContentEditor('');
            $payload = $this->payload($editor->schema());
            $payload['blocks'] = [['type' => 'list', 'list_style' => $style, 'text' => "First\n\n<script>unsafe</script>\nThird"]];
            $html = $editor->apply(json_encode($payload));
            $tag = $type === null ? 'ul' : 'ol';
            $this->assertStringContainsString('<'.$tag.' data-cms-type="list"', $html);
            $this->assertStringContainsString('list-style-type:'.$style, $html);
            if ($type !== null) {
                $this->assertStringContainsString('type="'.$type.'"', $html);
            }
            $this->assertSame(3, substr_count($html, '<li>'));
            $this->assertStringNotContainsString('<script>', $html);
            $reopened = new ContentEditor($html);
            $this->assertSame($style, $reopened->schema()['blocks'][0]['list_style']);
            $this->assertSame($html, $reopened->apply(json_encode($this->payload($reopened->schema()))));
            $changed = $this->payload($reopened->schema());
            $changed['blocks'][0]['list_style'] = 'upper-roman';
            $this->assertStringContainsString('type="I"', $reopened->apply(json_encode($changed)));
        }
    }

    public function test_legacy_lists_keep_default_markers_and_ordered_list_types(): void
    {
        foreach (['<ul data-cms-type="list"><li>One</li></ul>' => 'disc', '<ol data-cms-type="list" type="i"><li>One</li></ol>' => 'lower-roman'] as $list => $style) {
            $html = '<section data-cms-blocks>'.$list.'</section>';
            $editor = new ContentEditor($html);
            $this->assertSame($style, $editor->schema()['blocks'][0]['list_style']);
            $this->assertSame($html, $editor->apply(json_encode($this->payload($editor->schema()))));
        }
    }

    public function test_unrecognised_list_styles_are_rejected(): void
    {
        $payload = $this->payload((new ContentEditor(''))->schema());
        $payload['blocks'] = [['type' => 'list', 'text' => 'One', 'list_style' => 'disc;position:fixed']];
        $this->post('/admin/pages', ['title' => 'Invalid list', 'status' => 'published', 'content' => json_encode($payload)])
            ->assertSessionHasErrors();
        $this->assertDatabaseMissing('pages', ['title' => 'Invalid list']);
    }

    public function test_saving_unchanged_legacy_content_keeps_every_byte_and_page_settings(): void
    {
        $page = $this->page();
        $page->update(['body' => $page->body.'<img class="placeholder">']);
        $schema = $this->get('/admin/pages/'.$page->id.'/edit')->viewData('editor');
        $this->put('/admin/pages/'.$page->id, ['title' => $page->title, 'path' => $page->path, 'status' => 'published', 'content' => json_encode($this->payload($schema)), 'scripts' => '<script>unwanted()</script>', 'styles' => 'unwanted.css'])
            ->assertSessionHasNoErrors();
        $this->assertSame($page->body, $page->fresh()->body);
        $this->assertSame($page->scripts, $page->fresh()->scripts);
        $this->assertSame($page->styles, $page->fresh()->styles);
        $this->assertSame($page->body_class, $page->fresh()->body_class);
        $this->assertSame(0, $page->revisions()->count());
    }

    public function test_plain_hero_is_saved_first_and_reopens_without_creating_unnecessary_revisions(): void
    {
        $payload = $this->payload((new ContentEditor(''))->schema());
        $payload['blocks'] = [
            ['type' => 'paragraph', 'text' => 'Kandungan halaman'],
            ['type' => 'hero', 'variant' => 'plain', 'text' => 'Selamat <datang>', 'eyebrow' => 'Profil Daerah',
                'description' => "Baris pertama\nBaris kedua", 'alignment' => 'center', 'size' => 'compact',
                'button_text' => 'Ketahui lebih lanjut', 'button_url' => '/profil-daerah/pelan-pembangunan'],
        ];
        $this->post('/admin/pages', ['title' => 'Halaman hero', 'status' => 'published', 'content' => json_encode($payload)])
            ->assertSessionHasNoErrors()->assertRedirect();
        $page = Page::firstOrFail();
        $this->assertStringStartsWith('<section class="cms-hero ', $page->body);
        $this->get('/'.$page->path)->assertOk()->assertSee('Selamat &lt;datang&gt;', false)
            ->assertSee('cms-hero--center')->assertSee('cms-hero--compact')->assertSee('Baris pertama<br>Baris kedua', false)
            ->assertSee('href="/profil-daerah/pelan-pembangunan"', false)->assertSee('Kandungan halaman');
        $schema = $this->get('/admin/pages/'.$page->id.'/edit')->viewData('editor');
        $this->assertSame('hero', $schema['blocks'][0]['type']);
        $this->assertSame("Baris pertama\nBaris kedua", $schema['blocks'][0]['description']);
        $this->assertCount(0, $schema['fields']);
        $this->put('/admin/pages/'.$page->id, ['title' => $page->title, 'path' => $page->path, 'status' => 'published', 'content' => json_encode($this->payload($schema))])->assertSessionHasNoErrors();
        $this->assertSame($page->body, $page->fresh()->body);
        $this->assertSame(0, $page->revisions()->count());
    }

    public function test_image_only_hero_can_switch_styles_without_losing_its_image(): void
    {
        $editor = new ContentEditor('');
        $payload = $this->payload($editor->schema());
        $payload['blocks'] = [['type' => 'hero', 'variant' => 'image', 'url' => '/assets/img/banner.jpg', 'alt' => 'Pemandangan Ranau', 'image_fit' => 'contain']];
        $imageHtml = $editor->apply(json_encode($payload));
        $this->assertStringContainsString('cms-hero--image-only', $imageHtml);
        $this->assertStringContainsString('cms-hero--fit-contain', $imageHtml);
        $this->assertStringContainsString('<img class="cms-hero-media"', $imageHtml);
        $editor = new ContentEditor($imageHtml);
        $payload = $this->payload($editor->schema());
        $this->assertSame($imageHtml, $editor->apply(json_encode($payload)));
        $payload['blocks'][0]['variant'] = 'plain';
        $payload['blocks'][0]['text'] = 'Daerah Ranau';
        $plainHtml = $editor->apply(json_encode($payload));
        $this->assertStringContainsString('cms-hero--plain', $plainHtml);
        $this->assertStringNotContainsString('<img', $plainHtml);
        $editor = new ContentEditor($plainHtml);
        $payload = $this->payload($editor->schema());
        $this->assertSame('/assets/img/banner.jpg', $payload['blocks'][0]['url']);
        $this->assertSame('Pemandangan Ranau', $payload['blocks'][0]['alt']);
        $this->assertSame($plainHtml, $editor->apply(json_encode($payload)));
        $payload['blocks'][0]['variant'] = 'image';
        $this->assertStringContainsString('<img class="cms-hero-media" src="/assets/img/banner.jpg"', $editor->apply(json_encode($payload)));
    }

    public function test_hero_can_be_previewed_added_and_removed_without_losing_legacy_content(): void
    {
        $page = $this->page();
        $payload = $this->payload($this->get('/admin/pages/'.$page->id.'/edit')->viewData('editor'));
        $payload['blocks'] = [['type' => 'hero', 'text' => 'Hero baharu']];
        $request = ['page_id' => $page->id, 'title' => $page->title, 'path' => $page->path, 'status' => 'published', 'content' => json_encode($payload)];
        $this->put('/admin/pages/preview', $request)->assertOk()->assertSee('Hero baharu')->assertSee('Pratonton - belum disimpan');
        $this->assertSame($page->body, $page->fresh()->body);
        $this->put('/admin/pages/'.$page->id, $request)->assertSessionHasNoErrors();
        $body = $page->fresh()->body;
        $this->assertStringStartsWith('<section class="cms-hero ', $body);
        foreach (['class="original"', '<strong>penting</strong>', '<table>', 'window.original = true;', '[[hebahan-berita]]'] as $expected) {
            $this->assertStringContainsString($expected, $body);
        }
        $payload = $this->payload($this->get('/admin/pages/'.$page->id.'/edit')->viewData('editor'));
        $this->assertNotEmpty($payload['fields']);
        $payload['blocks'] = [];
        $this->put('/admin/pages/'.$page->id, array_replace($request, ['content' => json_encode($payload)]))->assertSessionHasNoErrors();
        $this->assertStringNotContainsString('data-cms-type="hero"', $page->fresh()->body);
        $this->assertStringContainsString('class="original"', $page->fresh()->body);
        $this->assertSame(2, $page->revisions()->count());
    }

    public function test_hero_rejects_invalid_images_buttons_duplicate_banners_and_layout_values(): void
    {
        $baseHero = ['type' => 'hero', 'text' => '<script>alert(1)</script>'];
        $invalidBlocks = [
            [array_replace($baseHero, ['variant' => 'image', 'url' => ''])],
            [array_replace($baseHero, ['variant' => 'image', 'url' => 'javascript:alert(1)'])],
            [array_replace($baseHero, ['button_text' => 'Buka', 'button_url' => 'javascript:alert(1)'])],
            [array_replace($baseHero, ['button_text' => 'Buka'])],
            [array_replace($baseHero, ['alignment' => 'left" onclick="alert(1)'])],
            [array_replace($baseHero, ['text' => str_repeat('a', 221)])],
            [['type' => 'hero', 'variant' => 'plain']],
            [$baseHero, $baseHero],
        ];
        foreach ($invalidBlocks as $blocks) {
            $payload = $this->payload((new ContentEditor(''))->schema());
            $payload['blocks'] = $blocks;
            $this->post('/admin/pages', ['title' => 'Hero tidak sah', 'status' => 'draft', 'content' => json_encode($payload)])->assertSessionHasErrors();
        }
        $this->assertDatabaseCount('pages', 0);
        $payload['blocks'] = [$baseHero];
        $html = (new ContentEditor(''))->apply(json_encode($payload));
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }

    public function test_editing_existing_text_preserves_layout_scripts_tables_svg_and_revisions(): void
    {
        $page = $this->page();
        $schema = $this->get('/admin/pages/'.$page->id.'/edit')->viewData('editor');
        $payload = $this->payload($schema);
        foreach ($payload['fields'] as &$field) {
            if ($field['value'] === 'Pelan Ranau') {
                $field['value'] = 'Pelan <Baharu>';
            }
        }
        unset($field);
        $this->put('/admin/pages/'.$page->id, ['title' => $page->title, 'path' => $page->path, 'status' => 'published', 'content' => json_encode($payload)])->assertSessionHasNoErrors();
        $body = $page->fresh()->body;
        foreach (['class="original"', 'Pelan &lt;Baharu&gt;', '<strong>penting</strong>', '<table>', 'viewBox="0 0 20 20"', 'window.original = true;', '.original{display:block}', '[[hebahan-berita]]'] as $expected) {
            $this->assertStringContainsString($expected, $body);
        }
        $revision = $page->revisions()->firstOrFail();
        $this->assertSame($page->body, $revision->body);
        $this->post(route('admin.pages.restore', [$page, $revision]))->assertSessionHasNoErrors();
        $this->assertSame($page->body, $page->fresh()->body);
    }

    public function test_preview_keeps_original_assets_and_does_not_save(): void
    {
        $page = $this->page();
        $schema = $this->get('/admin/pages/'.$page->id.'/edit')->viewData('editor');
        $this->put('/admin/pages/preview', ['page_id' => $page->id, 'title' => 'Pratonton baharu', 'path' => $page->path, 'status' => 'draft', 'content' => json_encode($this->payload($schema))])
            ->assertOk()->assertSee('Pratonton baharu')->assertSee('pelan-pembangunan.css')->assertSee('location-directory.js');
        $this->assertSame('Pelan', $page->fresh()->title);
        $this->assertSame(0, $page->revisions()->count());
    }

    public function test_stale_forms_cannot_overwrite_a_newer_save(): void
    {
        $page = $this->page();
        $schema = $this->get('/admin/pages/'.$page->id.'/edit')->viewData('editor');
        $page->update(['title' => 'Disimpan oleh editor lain']);
        $this->put('/admin/pages/'.$page->id, ['title' => 'Tajuk lapuk', 'path' => $page->path, 'status' => 'published', 'content' => json_encode($this->payload($schema))])->assertSessionHasErrors('content');
        $this->assertSame('Disimpan oleh editor lain', $page->fresh()->title);
    }

    public function test_dangerous_urls_unknown_fields_and_raw_code_requests_are_rejected(): void
    {
        $page = $this->page();
        $schema = $this->get('/admin/pages/'.$page->id.'/edit')->viewData('editor');
        $payload = $this->payload($schema);
        foreach ($payload['fields'] as &$field) {
            if ($field['value'] === '/uploads/plan.pdf') {
                $field['value'] = 'javascript:alert(1)';
            }
        }
        unset($field);
        $base = ['title' => $page->title, 'path' => $page->path, 'status' => 'published'];
        $this->put('/admin/pages/'.$page->id, $base + ['content' => json_encode($payload)])->assertSessionHasErrors('content');
        $payload = $this->payload($schema);
        $payload['fields'][0]['id'] = 'unknown';
        $this->put('/admin/pages/'.$page->id, $base + ['content' => json_encode($payload)])->assertSessionHasErrors('content');
        $this->put('/admin/pages/'.$page->id, $base + ['body' => '<script>alert(1)</script>'])->assertSessionHasErrors('content');
        $this->assertSame($page->body, $page->fresh()->body);
    }

    public function test_blocks_can_be_reordered_removed_and_saved_again_without_changes(): void
    {
        $editor = new ContentEditor('');
        $payload = $this->payload($editor->schema());
        $payload['blocks'] = [['type' => 'heading', 'text' => 'Tajuk', 'level' => 'h2'], ['type' => 'paragraph', 'text' => "Satu\nDua"], ['type' => 'list', 'text' => "A\nB"]];
        $html = $editor->apply(json_encode($payload));
        $editor = new ContentEditor($html);
        $payload = $this->payload($editor->schema());
        $this->assertSame($html, $editor->apply(json_encode($payload)));
        $payload['blocks'] = [$payload['blocks'][1], $payload['blocks'][0]];
        $updated = $editor->apply(json_encode($payload));
        $this->assertLessThan(strpos($updated, 'Tajuk'), strpos($updated, 'Satu'));
        $this->assertStringNotContainsString('<ul', $updated);
    }

    public function test_legacy_action_links_stay_behind_the_scenes_and_an_empty_heading_size_uses_a_safe_default(): void
    {
        $html = '<a href="javascript:void(0)" class="toggle">Buka menu</a>';
        $editor = new ContentEditor($html);
        $schema = $editor->schema();
        $this->assertSame(['Buka menu'], array_column($schema['fields'], 'value'));
        $this->assertSame($html, $editor->apply(json_encode($this->payload($schema))));
        $payload = $this->payload($schema);
        $payload['blocks'] = [['type' => 'heading', 'text' => 'Tajuk', 'level' => '']];
        $this->assertStringContainsString('<h2 data-cms-type="heading">Tajuk</h2>', $editor->apply(json_encode($payload)));
    }

    public function test_shared_parts_preserve_their_structure_and_do_not_accept_new_blocks(): void
    {
        $part = SitePart::create(['key' => 'footer', 'label' => 'Footer', 'html' => '<footer class="original"><p>Alamat lama</p></footer>']);
        $payload = $this->payload((new ContentEditor($part->html, false))->schema());
        $payload['fields'][0]['value'] = 'Alamat baharu';
        $this->put('/admin/parts/footer', ['content' => json_encode($payload)])->assertSessionHasNoErrors();
        $this->assertSame('<footer class="original"><p>Alamat baharu</p></footer>', $part->fresh()->html);
    }

    public function test_navigation_links_are_managed_through_the_menu_screen(): void
    {
        $html = '<nav><a href="/">Pejabat Daerah Ranau</a><ul id="navLinks"><li><a href="/profil-daerah">Profil Daerah</a></li></ul></nav>';
        $editor = new ContentEditor($html, false);
        $schema = $editor->schema();
        $this->assertNotContains('Profil Daerah', array_column($schema['fields'], 'value'));
        $this->assertSame($html, $editor->apply(json_encode($this->payload($schema))));
    }

    public function test_media_picker_lists_and_uploads_reusable_files_without_exposing_disk_paths(): void
    {
        $directory = (getenv('CMS_TEST_TMP') ?: sys_get_temp_dir()).'/pdr-editor-test-'.bin2hex(random_bytes(8));
        File::ensureDirectoryExists($directory);
        $this->app->usePublicPath($directory);
        try {
            file_put_contents($directory.'/source.pdf', "%PDF-1.7\n1 0 obj\n<<>>\nendobj\n%%EOF");
            $file = new UploadedFile($directory.'/source.pdf', 'panduan.pdf', 'application/pdf', null, true);
            $response = $this->postJson('/admin/media', ['file' => $file])->assertCreated()->assertJsonPath('file.image', false);
            $url = $response->json('file.url');
            $this->assertStringStartsWith('/uploads/', $url);
            $this->assertFileExists($directory.$url);
            $this->getJson('/admin/media')->assertOk()->assertJsonPath('files.0.url', $url)->assertDontSee($directory);
            $this->postJson('/admin/media', ['file' => UploadedFile::fake()->createWithContent('bad.php', '<?php echo 1;')])->assertUnprocessable();
        } finally {
            File::deleteDirectory($directory);
        }
    }
}
