<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Page;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'announcementCount' => Announcement::count(),
            'pageCount' => Page::count(),
            'latest' => Announcement::latest()->take(5)->get(),
            'recentPages' => Page::orderByDesc('updated_at')->take(5)->get(),
        ]);
    }

    /** Called by the admin pages while the user is active, to keep the session alive. */
    public function keepalive()
    {
        return response()->noContent();
    }
}
