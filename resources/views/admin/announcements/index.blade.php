@extends('admin.layout')
@section('title', __('Hebahan'))
@section('content')
    <div class="row" style="justify-content:space-between;margin-bottom:12px">
        <div><p class="eyebrow">{{ __('KANDUNGAN LAMAN') }}</p><h1 style="margin:0">{{ __('Hebahan & Berita') }}</h1><p class="page-intro">{{ __('Urus berita, notis dan maklumat untuk orang awam.') }}</p></div>
        <a class="btn" href="{{ route('admin.announcements.create') }}">{{ __('+ Hebahan baru') }}</a>
    </div>
    <div class="card">
        <table>
            <tr>
                <th>{{ __('Tajuk') }}</th>
                <th>{{ __('Kategori') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Tarikh terbit (MYT)') }}</th>
                <th></th>
            </tr>
            @forelse ($items as $item)
                <tr>
                    <td>{{ $item->title }}@if ($item->author)<div class="hint">{{ __('Oleh :name', ['name' => $item->author]) }}</div>@endif</td>
                    <td>{{ __($item->categoryLabel()) }}</td>
                    <td><span class="badge {{ $item->publicationState() }}">{{ __($item->publicationLabel()) }}</span></td>
                    <td>{{ $item->published_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td class="row">
                        @if ($item->publicationState() === 'published')<a href="{{ route('announcements.show', $item->slug) }}"
                        target="_blank">{{ __('Lihat') }}</a>@endif
                        <a href="{{ route('admin.announcements.edit', $item) }}">{{ __('Edit') }}</a>
                        <form method="POST" action="{{ route('admin.announcements.destroy', $item) }}"
                            onsubmit="return confirm(@js(__('Padam hebahan ini?')))">
                            @csrf @method('DELETE')
                            <button class="btn red" style="padding:3px 10px">{{ __('Padam') }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">{{ __('Tiada hebahan lagi.') }}</td>
                </tr>
            @endforelse
        </table>
        {{ $items->links() }}
    </div>
@endsection
