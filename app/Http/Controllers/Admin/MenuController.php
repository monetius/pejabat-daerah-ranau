<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Page;
use App\Support\NavMenu;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MenuController extends Controller
{
    public function index()
    {
        return view('admin.menu.index', [
            'items' => MenuItem::whereNull('parent_id')
                ->orderBy('sort_order')->orderBy('id')
                ->with(['page', 'children.page'])
                ->get(),
        ]);
    }

    public function create(Request $request)
    {
        $item = new MenuItem([
            'is_visible' => true,
            'parent_id' => $request->integer('parent_id') ?: null,
            'page_id' => $request->integer('page_id') ?: null,
        ]);

        return view('admin.menu.form', $this->formData($item));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['sort_order'] = (int) MenuItem::where('parent_id', $data['parent_id'])->max('sort_order') + 1;
        MenuItem::create($data);

        return redirect()->route('admin.menu.index')->with('status', __('Item menu disimpan.'));
    }

    public function edit(MenuItem $item)
    {
        return view('admin.menu.form', $this->formData($item));
    }

    public function update(Request $request, MenuItem $item)
    {
        $data = $this->validated($request, $item);

        if ($data['parent_id'] !== $item->parent_id) {
            $data['sort_order'] = (int) MenuItem::where('parent_id', $data['parent_id'])->max('sort_order') + 1;
        }

        $item->update($data);

        return redirect()->route('admin.menu.index')->with('status', __('Item menu dikemas kini.'));
    }

    public function destroy(MenuItem $item)
    {
        $item->delete(); // sub-items are removed with it

        return redirect()->route('admin.menu.index')->with('status', __('Item menu dipadam.'));
    }

    public function move(Request $request, MenuItem $item)
    {
        $step = $request->input('dir') === 'up' ? -1 : 1;

        $siblings = MenuItem::where('parent_id', $item->parent_id)
            ->orderBy('sort_order')->orderBy('id')->get()->all();

        $from = null;
        foreach ($siblings as $i => $s) {
            if ($s->id === $item->id) {
                $from = $i;
            }
        }
        $to = $from === null ? null : $from + $step;

        if ($to !== null && isset($siblings[$to])) {
            [$siblings[$from], $siblings[$to]] = [$siblings[$to], $siblings[$from]];
            foreach ($siblings as $i => $s) {
                if ($s->sort_order !== $i + 1) {
                    $s->update(['sort_order' => $i + 1]);
                }
            }
        }

        return redirect()->route('admin.menu.index');
    }

    /** One-click: builds the menu from the navbar that is on the site now. */
    public function import()
    {
        if (MenuItem::exists()) {
            return redirect()->route('admin.menu.index')->with('status', __('Menu sudah wujud.'));
        }

        $count = NavMenu::importFromNavbar();

        return redirect()->route('admin.menu.index')->with('status', __(':count item menu diimport daripada navbar semasa.', ['count' => $count]));
    }

    private function formData(MenuItem $item): array
    {
        return [
            'item' => $item,
            'parents' => MenuItem::whereNull('parent_id')
                ->when($item->exists, fn($q) => $q->whereKeyNot($item->id))
                ->orderBy('sort_order')->orderBy('id')->get(),
            'pages' => Page::orderBy('section')->orderBy('path')
                ->get(['id', 'title', 'path', 'section', 'status'])
                ->groupBy(fn(Page $p) => $p->section ?: 'Lain-lain'),
            'hasChildren' => $item->exists && $item->children()->exists(),
        ];
    }

    private function validated(Request $request, ?MenuItem $current = null): array
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'parent_id' => ['nullable', 'integer', Rule::exists('menu_items', 'id')->whereNull('parent_id')],
            'page_id' => ['nullable', 'integer', 'exists:pages,id'],
            'url' => ['nullable', 'string', 'max:500', 'regex:/^(\/|https?:\/\/|mailto:|tel:|#)/i'],
        ], [
            'url.regex' => __('URL mesti bermula dengan /, http://, https://, mailto: atau tel:.'),
            'parent_id.exists' => __('Menu induk tidak sah.'),
        ]);

        $data['parent_id'] = $data['parent_id'] ?? null;
        $data['page_id'] = $data['page_id'] ?? null;
        $data['url'] = $data['page_id'] ? null : ($data['url'] ?? null);
        $data['new_tab'] = $request->boolean('new_tab');
        $data['is_visible'] = $request->boolean('is_visible');

        if ($current && $data['parent_id'] !== null) {
            if ($data['parent_id'] === $current->id) {
                throw ValidationException::withMessages(['parent_id' => __('Item tidak boleh menjadi induk kepada dirinya sendiri.')]);
            }
            if ($current->children()->exists()) {
                throw ValidationException::withMessages(['parent_id' => __('Item ini mempunyai sub-menu, jadi ia mesti kekal sebagai menu utama.')]);
            }
        }

        if ($data['parent_id'] !== null && $data['page_id'] === null && empty($data['url'])) {
            throw ValidationException::withMessages(['url' => __('Sub-menu perlu halaman atau URL.')]);
        }

        return $data;
    }
}
