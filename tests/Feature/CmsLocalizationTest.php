<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Page;
use App\Models\SitePart;
use App\Models\User;
use App\Support\CmsLoginCode;
use App\Support\ContentEditor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CmsLocalizationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_cms_defaults_to_malay_and_exposes_both_language_options(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('lang="ms"', false)
            ->assertSee('Selamat kembali.')->assertSee('aria-label="English"', false)
            ->assertSee('aria-label="Bahasa Melayu"', false);
        $this->actingAsVerified($this->admin())->get('/admin/pages/create')->assertOk()
            ->assertSee('Halaman Baru')->assertSee('Hero halaman');
    }

    public function test_language_selection_is_validated_saved_and_redirects_back_to_the_cms(): void
    {
        $this->from('/admin/login')->post('/admin/language', ['locale' => 'en'])
            ->assertStatus(303)->assertRedirect('/admin/login')->assertCookie('cms_locale', 'en')
            ->assertSessionHas('cms_locale', 'en');
        $this->get('/admin/login')->assertOk()->assertSee('lang="en"', false)->assertSee('Welcome back.')
            ->assertSee('Sign in to manage your website content.');
        $this->post('/admin/language', ['locale' => '../../private'])->assertSessionHasErrors('locale');
        $this->from('https://example.net/admin')->post('/admin/language', ['locale' => 'en'])->assertRedirect('/admin/login');
    }

    public function test_every_admin_screen_has_native_english_labels(): void
    {
        $this->actingAsVerified($this->admin())->withCookie('cms_locale', 'en');
        $page = Page::create(['title' => 'Kandungan asal', 'slug' => 'asal', 'path' => 'halaman/asal', 'status' => 'published', 'body' => '<h1>Tajuk asal</h1><p>Perenggan asal</p>']);
        $part = SitePart::create(['key' => 'footer', 'label' => 'Footer', 'html' => '<footer><p>Alamat asal</p></footer>']);
        $announcement = Announcement::create(['title' => 'Berita asal', 'slug' => 'berita-asal', 'category' => 'berita', 'status' => 'published', 'body' => '<p>Maklumat asal</p>']);
        $screens = [
            '/admin' => 'CONTENT MANAGEMENT',
            '/admin/pages' => 'New page',
            '/admin/pages/create' => 'Page hero',
            '/admin/pages/'.$page->id.'/edit' => 'Existing content',
            '/admin/announcements' => 'Announcements &amp; news',
            '/admin/announcements/create' => 'Author (optional)',
            '/admin/announcements/'.$announcement->id.'/edit' => 'Scheduled publication date and time (MYT)',
            '/admin/menu' => 'Navigation menu',
            '/admin/menu/create' => 'Parent menu',
            '/admin/parts' => 'Header &amp; footer',
            '/admin/parts/footer/edit' => 'Save changes',
            '/admin/media' => 'Upload',
        ];
        foreach ($screens as $url => $label) {
            $this->get($url)->assertOk()->assertSee('lang="en"', false)->assertSee($label, false);
        }
    }

    public function test_editor_controls_receive_english_translations_and_legacy_field_labels(): void
    {
        $page = Page::create(['title' => 'Halaman asal', 'slug' => 'asal', 'path' => 'halaman/asal', 'body' => '<h1>Tajuk asal</h1><p>Perenggan asal</p><a href="/">Kembali</a>']);
        $response = $this->actingAsVerified($this->admin())->withCookie('cms_locale', 'en')->get('/admin/pages/'.$page->id.'/edit')
            ->assertOk()->assertSee('data-editor-translations', false)->assertSee('Hero style');
        preg_match('/<script type="application\/json" data-editor-translations>(.*?)<\/script>/s', $response->getContent(), $matches);
        $translations = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('Choose / upload image', $translations['Pilih / muat naik gambar']);
        $schema = $response->viewData('editor');
        $this->assertSame('Heading', $schema['fields'][0]['label']);
        $this->assertSame('Tajuk asal', $schema['fields'][0]['value']);
        $this->assertSame('Link destination: Kembali', $schema['fields'][2]['label']);
    }

    public function test_english_editor_preserves_authored_content_and_does_not_translate_the_public_website(): void
    {
        $page = Page::create(['title' => 'Nama halaman', 'slug' => 'asal', 'path' => 'halaman/asal', 'status' => 'published', 'body' => '<h1>Nama halaman</h1><p>Teks perenggan</p>']);
        $this->actingAsVerified($this->admin())->withCookie('cms_locale', 'en');
        $schema = $this->get('/admin/pages/'.$page->id.'/edit')->viewData('editor');
        $payload = ['version' => $schema['version'], 'fields' => array_map(fn (array $field): array => ['id' => $field['id'], 'value' => $field['value']], $schema['fields']), 'blocks' => $schema['blocks']];
        $this->put('/admin/pages/'.$page->id, ['title' => $page->title, 'path' => $page->path, 'status' => 'published', 'content' => json_encode($payload)])
            ->assertSessionHasNoErrors()->assertSessionHas('status', 'Page updated.');
        $this->assertSame($page->body, $page->fresh()->body);
        $this->assertSame(0, $page->revisions()->count());
        $this->get('/halaman/asal')->assertOk()->assertSee('lang="ms"', false)->assertSee('<h1>Nama halaman</h1>', false)
            ->assertSee('<p>Teks perenggan</p>', false)->assertDontSee('Page name');
    }

    public function test_english_validation_and_sign_in_errors_are_translated(): void
    {
        $this->withCookie('cms_locale', 'en')->post('/admin/login', ['email' => 'missing@example.com', 'password' => 'wrong'])
            ->assertSessionHasErrors(['email' => 'The email address or password is incorrect.']);
        $this->actingAsVerified($this->admin());
        $schema = (new ContentEditor(''))->schema();
        $payload = ['version' => $schema['version'], 'fields' => [], 'blocks' => [['type' => 'hero', 'variant' => 'image']]];
        $this->post('/admin/pages', ['title' => 'Hero', 'status' => 'draft', 'content' => json_encode($payload)])
            ->assertSessionHasErrors(['content' => 'Choose an image for the hero or select the plain background style.']);
        $this->post('/admin/pages', ['status' => 'draft', 'content' => json_encode($payload)])
            ->assertSessionHasErrors(['title' => 'The title field is required.']);
    }

    public function test_cookie_keeps_english_after_sign_in_sign_out_and_idle_expiry(): void
    {
        config(['mail.default' => 'smtp']);
        Mail::fake();
        $user = User::factory()->create(['is_admin' => true, 'password' => 'correct-password']);
        $this->withCookie('cms_locale', 'en')->post('/admin/login', ['email' => $user->email, 'password' => 'correct-password'])
            ->assertSessionHasNoErrors();
        $this->post('/admin/two-factor-challenge', ['code' => Mail::sent(CmsLoginCode::class)->last()->code])->assertSessionHasNoErrors();
        $this->get('/admin')->assertSee('Welcome,');
        $this->withCredentials()->withSession(['admin_last_activity' => time() - 3600])->postJson('/admin/keepalive')
            ->assertUnauthorized()->assertJsonPath('message', 'Your session has expired.');
        $this->get('/admin/login')->assertSee('Welcome back.');
        $this->actingAsVerified($user)->post('/admin/logout')->assertRedirect();
        $this->get('/admin/login')->assertSee('Welcome back.');
    }

    public function test_malay_can_be_restored_and_unsupported_cookies_fall_back_safely(): void
    {
        $this->actingAsVerified($this->admin())->withCookie('cms_locale', 'en');
        $this->from('/admin/pages/create')->post('/admin/language', ['locale' => 'ms'])
            ->assertStatus(303)->assertCookie('cms_locale', 'ms')->assertSessionHas('cms_locale', 'ms');
        $this->withCookie('cms_locale', 'ms')->get('/admin/pages/create')->assertSee('Hero halaman')->assertSee('lang="ms"', false);
        $this->withCookie('cms_locale', '../en')->get('/admin/pages/create')->assertOk()->assertSee('lang="ms"', false);
    }

    public function test_translation_catalogs_have_matching_keys_and_valid_values(): void
    {
        $english = json_decode(file_get_contents(resource_path('lang/en.json')), true, 512, JSON_THROW_ON_ERROR);
        $malay = json_decode(file_get_contents(resource_path('lang/ms.json')), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(array_keys($malay), array_keys($english));
        foreach ($english as $key => $translation) {
            $this->assertIsString($translation);
            $this->assertNotSame('', $translation, $key);
            preg_match_all('/:[a-z_]+/', $key, $sourcePlaceholders);
            preg_match_all('/:[a-z_]+/', $translation, $translatedPlaceholders);
            $this->assertEqualsCanonicalizing($sourcePlaceholders[0], $translatedPlaceholders[0], $key);
        }
    }
}
