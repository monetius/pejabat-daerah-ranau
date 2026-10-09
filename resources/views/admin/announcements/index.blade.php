@extends('admin.layout')
@section('title', 'Hebahan')
@section('content')
    <div class="row" style="justify-content:space-between;margin-bottom:12px">
        <h1 style="margin:0">Hebahan</h1>
        <a class="btn" href="{{ route('admin.announcements.create') }}">+ Hebahan baru</a>
    </div>
    <div class="card">
        <table>
            <tr>
                <th>Tajuk</th>
                <th>Status</th>
                <th>Tarikh terbit</th>
                <th></th>
            </tr>
            @forelse ($items as $item)
                <tr>
                    <td>{{ $item->title }}</td>
                    <td><span class="badge {{ $item->status }}">{{ $item->status }}</span></td>
                    <td>{{ $item->published_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td class="row">
                        @if ($item->status === 'published')<a href="{{ route('announcements.show', $item->slug) }}"
                        target="_blank">Lihat</a>@endif
                        <a href="{{ route('admin.announcements.edit', $item) }}">Sunting</a>
                        <form method="POST" action="{{ route('admin.announcements.destroy', $item) }}"
                            onsubmit="return confirm('Padam hebahan ini?')">
                            @csrf @method('DELETE')
                            <button class="btn red" style="padding:3px 10px">Padam</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Tiada hebahan lagi.</td>
                </tr>
            @endforelse
        </table>
        {{ $items->links() }}
    </div>
@endsection