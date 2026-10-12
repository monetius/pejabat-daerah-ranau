<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Page;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    /** Serves every CMS page by its path ('' = home page). */
    public function show(Request $request, string $path = '')
    {
        $path = trim($path, '/');
        $page = Page::where('path', $path)->firstOrFail();

        // Drafts are only visible to logged-in admins (so they can check them).
        if ($page->status !== 'published' && !$request->user()?->is_admin) {
            abort(404);
        }

        return view('site.cms-page', ['page' => $page]);
    }

    /** Every announcement / news item, with category filter, search and paging. */
    public function announcements(Request $request)
    {
        $category = (string) $request->query('kategori');
        if (!array_key_exists($category, Announcement::CATEGORIES)) {
            $category = '';
        }
        $q = trim((string) $request->query('q'));
        $like = '%' . addcslashes($q, '%_\\') . '%';

        $items = Announcement::published()
            ->when($category !== '', fn($b) => $b->where('category', $category))
            ->when($q !== '', fn($b) => $b->where(
                fn($w) => $w->where('title', 'like', $like)->orWhere('summary', 'like', $like)
            ))
            ->orderByDesc('published_at')->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('site.announcements.index', [
            'items' => $items,
            'category' => $category,
            'q' => $q,
        ]);
    }

    public function announcement(string $slug)
    {
        $item = Announcement::published()->where('slug', $slug)->firstOrFail();

        $more = Announcement::published()
            ->whereKeyNot($item->getKey())
            ->orderByDesc('published_at')->orderByDesc('id')
            ->take(3)->get();

        return view('site.announcements.show', ['item' => $item, 'more' => $more]);
    }
}
