@extends('admin.layout')
@section('title', 'Hebahan')
@section('content')
    <div class="row" style="justify-content:space-between;margin-bottom:12px">
        <div><p class="eyebrow">KANDUNGAN LAMAN</p><h1 style="margin:0">Hebahan &amp; Berita</h1><p class="page-intro">Urus berita, notis dan maklumat untuk orang awam.</p></div>
        <a class="btn" href="{{ route('admin.announcements.create') }}">+ Hebahan baru</a>
    </div>
    <div class="card">
        <table>
            <tr>
                <th>Tajuk</th>
                <th>Kategori</th>
                <th>Status</th>
                <th>Tarikh terbit (MYT)</th>
                <th></th>
            </tr>
            @forelse ($items as $item)
                <tr>
                    <td>{{ $item->title }}</td>
                    <td>{{ $item->categoryLabel() }}</td>
                    <td><span class="badge {{ $item->publicationState() }}">{{ $item->publicationLabel() }}</span></td>
                    <td>{{ $item->published_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td class="row">
                        @if ($item->publicationState() === 'published')<a href="{{ route('announcements.show', $item->slug) }}"
                        target="_blank">Lihat</a>@endif
                        <a href="{{ route('admin.announcements.edit', $item) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.announcements.destroy', $item) }}"
                            onsubmit="return confirm('Padam hebahan ini?')">
                            @csrf @method('DELETE')
                            <button class="btn red" style="padding:3px 10px">Padam</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Tiada hebahan lagi.</td>
                </tr>
            @endforelse
        </table>
        {{ $items->links() }}
    </div>
@endsection