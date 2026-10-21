<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Page;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_contains_only_public_pages_and_currently_published_news(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 20)->setTime(12, 0));
        foreach (['' => 'published', 'profil-daerah/senarai-kampung' => 'published', 'draf' => 'draft', 'admin/tersembunyi' => 'published'] as $path => $status) {
            Page::create(['title' => $path ?: 'Home', 'slug' => str_replace('/', '-', $path) ?: 'home', 'path' => $path, 'status' => $status, 'body' => '<p>Content</p>']);
        }
        foreach (['terkini' => ['published', now()], 'tanpa-tarikh' => ['published', null], 'draf' => ['draft', now()], 'berjadual' => ['published', now()->addDay()]] as $slug => [$status, $date]) {
            Announcement::create(['title' => $slug, 'slug' => $slug, 'category' => 'berita', 'status' => $status, 'published_at' => $date, 'body' => '<p>News</p>']);
        }

        $response = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $document = new DOMDocument;
        $this->assertTrue($document->loadXML($response->getContent()));
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $locations = array_map(fn ($node): string => $node->textContent, iterator_to_array($xpath->query('//s:loc')));
        $this->assertEqualsCanonicalizing([
            url('/'), url('/profil-daerah/senarai-kampung'), route('announcements.index'), route('sitemap.show'),
            route('announcements.show', 'terkini'), route('announcements.show', 'tanpa-tarikh'),
        ], $locations);
        $this->assertSame(5, $xpath->query('//s:lastmod')->length);

        $this->travel(2)->days();
        $this->get('/sitemap.xml')->assertSee('/hebahan/berjadual');
        Announcement::where('slug', 'terkini')->update(['status' => 'draft']);
        $this->get('/sitemap.xml')->assertDontSee('/hebahan/terkini');
    }

    public function test_empty_site_still_has_a_valid_news_index_entry(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertSee('<loc>'.route('announcements.index').'</loc>', false)->assertDontSee('<lastmod>', false);
    }

    public function test_robots_advertises_the_sitemap_using_the_current_domain(): void
    {
        $this->get('https://ranau.example/robots.txt')->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Sitemap: https://ranau.example/sitemap.xml', false)
            ->assertSee('Disallow: /admin')->assertSee('Disallow: /login');
        $this->assertFileDoesNotExist(public_path('robots.txt'));
    }

    public function test_visitor_sitemap_lists_published_content_and_footer_links(): void
    {
        Page::create(['title' => 'Kampung diterbitkan', 'slug' => 'kampung', 'path' => 'profil-daerah/kampung', 'section' => 'Profil Daerah', 'status' => 'published', 'body' => '<p>Content</p>']);
        Page::create(['title' => 'Draf tersembunyi', 'slug' => 'draf', 'path' => 'draf', 'status' => 'draft', 'body' => '<p>Draft</p>']);
        Announcement::create(['title' => 'Berita masa hadapan', 'slug' => 'masa-hadapan', 'category' => 'berita', 'status' => 'published', 'published_at' => now()->addDay(), 'body' => '<p>News</p>']);
        $this->get('/peta-laman')->assertOk()->assertSee('Kampung diterbitkan')->assertSee('Profil Daerah')
            ->assertSee('href="/info-korporat/soalan-lazim"', false)->assertSee('href="/peta-laman"', false)
            ->assertDontSee('Draf tersembunyi')->assertDontSee('Berita masa hadapan');
    }

    public function test_faq_is_imported_as_an_editable_page_without_importing_the_dynamic_sitemap(): void
    {
        $this->artisan('cms:import')->assertSuccessful();
        $this->assertDatabaseHas('pages', ['path' => 'info-korporat/soalan-lazim', 'status' => 'published']);
        $this->assertDatabaseMissing('pages', ['path' => 'sitemap']);
        $this->get('/info-korporat/soalan-lazim')->assertOk()->assertSee('Soalan Lazim')
            ->assertSee('pd.ranau@sabah.gov.my')->assertSee('href="/peta-laman"', false);
        $this->get('/sitemap.xml')->assertSee('/info-korporat/soalan-lazim');
    }
}
