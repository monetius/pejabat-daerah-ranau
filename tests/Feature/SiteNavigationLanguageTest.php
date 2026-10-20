<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Page;
use App\Models\SitePart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteNavigationLanguageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Page::create(['title' => 'Ranau', 'slug' => 'home', 'path' => '', 'status' => 'published', 'body' => '<h1>Mengenai Kami</h1><p>Kandungan asal.</p>']);
    }

    public function test_default_navigation_is_malay_even_when_cms_is_english(): void
    {
        $this->withCookie('cms_locale', 'en')->get('/')->assertOk()
            ->assertSee('Laman Utama')->assertSee('Info Korporat')
            ->assertSee('data-site-language-form', false)->assertSee('lang="ms"', false)
            ->assertDontSee('Corporate Information');
    }

    public function test_native_english_covers_branding_menus_and_accessibility_without_changing_page_content(): void
    {
        $response = $this->withCookie('site_locale', 'en')->get('/')->assertOk()
            ->assertSee('<h1>Mengenai Kami</h1>', false)->assertSee('Kandungan asal.')
            ->assertSee('translate="no"', false)->assertSee('lang="en"', false);
        foreach (['Ranau District Office', 'Sabah State Government', 'Home', 'Corporate Information', 'District Profile', 'Online Services', 'Gallery &amp; Links', 'Contact Us', 'Public Facilities', 'Village Directory', 'About Us', 'Development Plan', 'Photos', 'Videos', 'Skip to main content', 'Switch to dark mode', 'Open menu'] as $label) {
            $response->assertSee($label, false);
        }
    }

    public function test_saved_navbar_and_cms_menu_are_translated_without_modifying_authored_records_or_urls(): void
    {
        $navbar = SitePart::create(['key' => 'navbar', 'label' => 'Navbar', 'html' => view('partials.navbar')->render()]);
        $original = $navbar->html;
        $page = Page::create(['title' => 'Kampung', 'slug' => 'kampung', 'path' => 'profil-daerah/senarai-kampung', 'status' => 'published', 'body' => '<h1>Kampung</h1>']);
        $parent = MenuItem::create(['label' => 'Profil Daerah', 'is_visible' => true, 'sort_order' => 1]);
        MenuItem::create(['parent_id' => $parent->id, 'label' => 'Senarai Kampung', 'page_id' => $page->id, 'new_tab' => true, 'is_visible' => true]);
        MenuItem::create(['label' => 'Ranau 2040', 'url' => '/pelan', 'is_visible' => true, 'sort_order' => 2]);
        $this->withCookie('site_locale', 'en')->get('/')->assertOk()->assertSee('District Profile')->assertSee('Village Directory')
            ->assertSee('Ranau 2040')->assertSee('href="/profil-daerah/senarai-kampung"', false)->assertSee('target="_blank"', false);
        $this->assertSame($original, $navbar->fresh()->html);
        $this->assertSame('Profil Daerah', $parent->fresh()->label);
    }

    public function test_language_choice_persists_and_does_not_change_cms_language(): void
    {
        $this->withSession(['cms_locale' => 'ms'])->from('/hebahan?kategori=berita')->post('/language', ['locale' => 'en'])
            ->assertStatus(303)->assertRedirect('/hebahan?kategori=berita')->assertCookie('site_locale', 'en')
            ->assertSessionHas('site_locale', 'en')->assertSessionHas('cms_locale', 'ms');
        $this->get('/')->assertSee('Corporate Information');
        $this->get('/admin/login')->assertSee('lang="ms"', false)->assertSee('Kata laluan');
        $this->from('/')->post('/language', ['locale' => 'ms'])->assertRedirect('/');
        $this->get('/')->assertSee('Info Korporat')->assertDontSee('Corporate Information');
    }

    public function test_unsupported_locales_and_external_or_admin_redirects_are_rejected(): void
    {
        $this->from('/')->post('/language', ['locale' => '../en'])->assertSessionHasErrors('locale');
        $this->withCookie('site_locale', '../en')->get('/')->assertOk()->assertSee('Laman Utama');
        $this->from('https://example.net/')->post('/language', ['locale' => 'en'])->assertRedirect('/');
        $this->from('/admin/pages')->post('/language', ['locale' => 'en'])->assertRedirect('/');
    }
}
