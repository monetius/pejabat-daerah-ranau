<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SitePart;
use Illuminate\Http\Request;

class SitePartController extends Controller
{
    public function index()
    {
        return view('admin.parts.index', ['parts' => SitePart::orderBy('label')->get()]);
    }

    public function edit(SitePart $part)
    {
        return view('admin.parts.form', ['part' => $part]);
    }

    public function update(Request $request, SitePart $part)
    {
        $data = $request->validate(['html' => ['required', 'string']]);
        $part->update(['html' => str_replace("\r\n", "\n", $data['html'])]);

        return redirect()->route('admin.parts.index')->with('status', $part->label . ' dikemas kini.');
    }
}
