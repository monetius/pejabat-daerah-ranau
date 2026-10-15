<tr>
    <td style="{{ $sub ? 'padding-left:32px' : 'font-weight:600' }}">
        {{ $sub ? '↳ ' : '' }}{{ $item->label }}
        @if (!$sub && $item->children->isNotEmpty())
            <span class="badge">{{ __('Dropdown · :count sub-menu', ['count' => $item->children->count()]) }}</span>
        @endif
    </td>
    <td>
        @if (!$sub && $item->children->isNotEmpty())
            <span class="hint">{{ __('Butang dropdown') }}</span>
        @elseif ($item->page)
            {{ $item->page->title }} <span class="hint">/{{ $item->page->path }}</span>
            @if ($item->page->status !== 'published')<span class="badge">{{ __('Halaman draf - tidak dipaparkan') }}</span>@endif
        @elseif ($item->url)
            {{ $item->url }}
        @else
            <span class="badge">{{ __('Tiada pautan') }}</span>
        @endif
        @unless ($item->is_visible)<span class="badge">{{ __('Disembunyikan') }}</span>@endunless
    </td>
    <td class="row" style="justify-content:flex-end">
        <form method="POST" action="{{ route('admin.menu.move', $item) }}">@csrf
            <input type="hidden" name="dir" value="up">
            <button class="btn gray" style="padding:3px 9px" @disabled($first) title="{{ __('Naik') }}">▲</button>
        </form>
        <form method="POST" action="{{ route('admin.menu.move', $item) }}">@csrf
            <input type="hidden" name="dir" value="down">
            <button class="btn gray" style="padding:3px 9px" @disabled($last) title="{{ __('Turun') }}">▼</button>
        </form>
        @unless ($sub)
            <a href="{{ route('admin.menu.create', ['parent_id' => $item->id]) }}">{{ __('+ Sub-menu') }}</a>
        @endunless
        <a href="{{ route('admin.menu.edit', $item) }}">{{ __('Edit') }}</a>
        <form method="POST" action="{{ route('admin.menu.destroy', $item) }}"
            onsubmit="return confirm(@js(__($sub ? 'Padam item ini?' : 'Padam menu ini dan semua sub-menunya?')))">
            @csrf @method('DELETE')
            <button class="btn red" style="padding:3px 10px">{{ __('Padam') }}</button>
        </form>
    </td>
</tr>
