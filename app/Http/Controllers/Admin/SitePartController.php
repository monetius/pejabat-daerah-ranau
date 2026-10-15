<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SitePart;
use App\Support\ContentEditor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SitePartController extends Controller
{
    public function index(): View
    {
        return view('admin.parts.index', ['parts' => SitePart::orderBy('label')->get()]);
    }

    public function edit(SitePart $part): View
    {
        return view('admin.parts.form', ['part' => $part, 'editor' => (new ContentEditor($part->html, false))->schema()]);
    }

    public function update(Request $request, SitePart $part): RedirectResponse
    {
        $data = $request->validate(['content' => ['required', 'string', 'max:2000000']]);
        DB::transaction(function () use ($part, $data): void {
            $current = SitePart::lockForUpdate()->findOrFail($part->key);
            $current->update(['html' => (new ContentEditor($current->html, false))->apply($data['content'])]);
        });

        return redirect()->route('admin.parts.index')->with('status', __(':name dikemas kini.', ['name' => $part->label]));
    }
}
