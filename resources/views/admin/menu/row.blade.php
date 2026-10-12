<tr>
    <td style="{{ $sub ? 'padding-left:32px' : 'font-weight:600' }}">
        {{ $sub ? '↳ ' : '' }}{{ $item->label }}
        @if (!$sub && $item->children->isNotEmpty())
            <span class="badge">Dropdown · {{ $item->children->count() }} sub-menu</span>
        @endif
    </td>
    <td>
        @if (!$sub && $item->children->isNotEmpty())
            <span class="hint">Butang dropdown</span>
        @elseif ($item->page)
            {{ $item->page->title }} <span class="hint">/{{ $item->page->path }}</span>
            @if ($item->page->status !== 'published')<span class="badge">Halaman draf - tidak dipaparkan</span>@endif
        @elseif ($item->url)
            {{ $item->url }}
        @else
            <span class="badge">Tiada pautan</span>
        @endif
        @unless ($item->is_visible)<span class="badge">Disembunyikan</span>@endunless
    </td>
    <td class="row" style="justify-content:flex-end">
        <form method="POST" action="{{ route('admin.menu.move', $item) }}">@csrf
            <input type="hidden" name="dir" value="up">
            <button class="btn gray" style="padding:3px 9px" @disabled($first) title="Naik">▲</button>
        </form>
        <form method="POST" action="{{ route('admin.menu.move', $item) }}">@csrf
            <input type="hidden" name="dir" value="down">
            <button class="btn gray" style="padding:3px 9px" @disabled($last) title="Turun">▼</button>
        </form>
        @unless ($sub)
            <a href="{{ route('admin.menu.create', ['parent_id' => $item->id]) }}">+ Sub-menu</a>
        @endunless
        <a href="{{ route('admin.menu.edit', $item) }}">Sunting</a>
        <form method="POST" action="{{ route('admin.menu.destroy', $item) }}"
            onsubmit="return confirm('Padam {{ $sub ? 'item' : 'menu ini dan semua sub-menunya' }}?')">
            @csrf @method('DELETE')
            <button class="btn red" style="padding:3px 10px">Padam</button>
        </form>
    </td>
</tr>
