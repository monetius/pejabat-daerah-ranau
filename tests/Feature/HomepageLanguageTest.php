<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Page;
use App\Models\SitePart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageLanguageTest extends TestCase
{
    use RefreshDatabase;

    private Page $homepage;

    protected function setUp(): void
    {
        parent::setUp();
        $source = file_get_contents(resource_path('views/site/home.blade.php'));
        preg_match('/@section\(\'content\'\)([\s\S]*?)@endsection/', $source, $matches);
        $body = str_replace("@include('partials.home-calendar')", '[[kalendar]]', $matches[1]);
        $this->homepage = Page::create(['title' => 'Pejabat Daerah Ranau', 'slug' => 'home', 'path' => '', 'status' => 'published', 'body' => $body]);
    }

    public function test_native_english_translates_saved_homepage_carousel_calendar_and_footer_without_changing_records(): void
    {
        $footer = SitePart::create(['key' => 'footer', 'label' => 'Footer', 'html' => view('partials.footer')->render()]);
        $originalBody = $this->homepage->body;
        $originalFooter = $footer->html;
        $response = $this->withCookie('site_locale', 'en')->get('/')->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('<title>Ranau District Office</title>', false)
            ->assertSee('Welcome to')->assertSee('Serving the people of Ranau')
            ->assertSee('data-title="Village Directory"', false)
            ->assertSee('data-desc="Explore villages in Ranau District', false)
            ->assertSee('Image credit: hospital.com.my')
            ->assertSee('Announcements &amp; News', false)->assertSee('Gallery &amp; Links', false)
            ->assertSee('Related Links')->assertSee('Calendar')->assertSee('Today')
            ->assertSee('title="Monday">Mon', false)->assertSee('Previous month')
            ->assertSee('P.O. Box 2')->assertSee('Privacy Policy')->assertSee('All rights reserved.')
            ->assertSee('pd.ranau@sabah.gov.my')->assertSee('homepage-labels')
            ->assertDontSee('Selamat Datang ke')->assertDontSee('data-title="Senarai Kampung"', false);
        $response->assertSee('Public holidays for :year have not been imported yet.');
        $this->assertSame($originalBody, $this->homepage->fresh()->body);
        $this->assertSame($originalFooter, $footer->fresh()->html);
    }

    public function test_news_headlines_and_author_names_are_preserved_while_labels_are_translated(): void
    {
        Announcement::create(['title' => 'Senarai Kampung', 'slug' => 'senarai-kampung', 'category' => 'berita', 'status' => 'published', 'published_at' => now(), 'body' => 'Kandungan asal', 'author' => 'Berita']);
        $response = $this->withCookie('site_locale', 'en')->get('/')->assertOk();
        $response->assertSee('>Senarai Kampung</h3>', false)->assertSee('>By Berita</span>', false)
            ->assertSee('hb-tag-berita">News</span>', false)->assertDontSee('By News');
    }

    public function test_malay_remains_the_default_and_can_be_restored_using_the_same_switch(): void
    {
        $this->get('/')->assertOk()->assertSee('<html lang="ms">', false)
            ->assertSee('Selamat Datang ke')->assertSee('Tiada hebahan atau berita buat masa ini.')
            ->assertSee('data-title="Senarai Kampung"', false)->assertDontSee('id="homepage-labels"', false);
        $this->from('/')->post('/language', ['locale' => 'en'])->assertRedirect('/');
        $this->get('/')->assertSee('Welcome to');
        $this->from('/')->post('/language', ['locale' => 'ms'])->assertRedirect('/');
        $this->get('/')->assertSee('Selamat Datang ke')->assertDontSee('Welcome to');
    }

    public function test_other_public_pages_and_cms_are_not_translated_by_the_homepage_renderer(): void
    {
        Page::create(['title' => 'Galeri', 'slug' => 'gambar', 'path' => 'galeri-pautan/gambar', 'status' => 'published', 'body' => '<h1>Gambar</h1>']);
        $this->withCookie('site_locale', 'en')->get('/galeri-pautan/gambar')->assertOk()
            ->assertSee('<h1>Gambar</h1>', false)->assertSee('<html lang="ms">', false)
            ->assertDontSee('id="homepage-labels"', false);
        $this->withCookie('site_locale', 'en')->get('/admin/login')->assertOk()->assertSee('Kata laluan');
    }
}
