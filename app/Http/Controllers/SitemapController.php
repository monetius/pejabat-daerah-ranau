<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Page;
use DateTimeInterface;
use DOMDocument;
use DOMElement;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = true;
        $root = $document->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'urlset');
        $document->appendChild($root);

        foreach ($this->publicPages() as $page) {
            $this->appendUrl($document, $root, $page->url(), $page->updated_at);
        }
        $this->appendUrl($document, $root, route('sitemap.show'), null);

        $announcements = Announcement::published()->orderBy('slug')->get(['slug', 'published_at', 'updated_at']);
        $latest = $announcements->map(fn(Announcement $announcement) => max($announcement->updated_at, $announcement->published_at))->max();
        $this->appendUrl($document, $root, route('announcements.index'), $latest);
        foreach ($announcements as $announcement) {
            $this->appendUrl($document, $root, route('announcements.show', $announcement->slug), max($announcement->updated_at, $announcement->published_at));
        }

        return response($document->saveXML(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'no-cache',
        ]);
    }

    public function robots(): Response
    {
        return response("User-agent: *\nDisallow: /admin\nDisallow: /login\n\nSitemap: " . route('sitemap') . "\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function show(): View
    {
        return view('site.sitemap', [
            'pages' => $this->publicPages()->groupBy(fn(Page $page): string => $page->section ?: 'Lain-lain'),
            'announcements' => Announcement::published()->orderByDesc('published_at')->get(['title', 'slug']),
        ]);
    }

    /** @return Collection<int, Page> */
    private function publicPages(): Collection
    {
        return Page::published()->orderBy('path')->get(['title', 'path', 'section', 'updated_at'])->reject(function (Page $page): bool {
            $prefix = explode('/', trim((string) $page->path, '/'))[0];

            return in_array($prefix, ['admin', 'login', 'assets', 'uploads', 'storage', 'build', 'up', 'hebahan', 'language', 'kalendar', 'sitemap.xml', 'robots.txt', 'peta-laman'], true);
        });
    }

    private function appendUrl(DOMDocument $document, DOMElement $root, string $location, ?DateTimeInterface $modified): void
    {
        $entry = $document->createElement('url');
        $url = $document->createElement('loc');
        $url->appendChild($document->createTextNode($location));
        $entry->appendChild($url);
        if ($modified !== null) {
            $entry->appendChild($document->createElement('lastmod', $modified->format(DateTimeInterface::ATOM)));
        }
        $root->appendChild($entry);
    }
}
