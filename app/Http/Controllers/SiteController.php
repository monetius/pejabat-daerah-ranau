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

    public function announcements()
    {
        return view('site.announcements.index', [
            'items' => Announcement::published()->orderByDesc('published_at')->paginate(10),
        ]);
    }

    public function announcement(string $slug)
    {
        $item = Announcement::published()->where('slug', $slug)->firstOrFail();

        return view('site.announcements.show', ['item' => $item]);
    }
}
