@extends('admin.layout')
@section('title', $item->exists ? 'Sunting Hebahan' : 'Hebahan Baru')
@section('content')
    <h1>{{ $item->exists ? 'Sunting Hebahan' : 'Hebahan Baru' }}</h1>
    <div class="card">
        <form method="POST"
            action="{{ $item->exists ? route('admin.announcements.update', $item) : route('admin.announcements.store') }}">
            @csrf
            @if ($item->exists) @method('PUT') @endif

            <label for="title">Tajuk</label>
            <input type="text" id="title" name="title" value="{{ old('title', $item->title) }}" required>
            @error('title')
            <div class="err">{{ $message }}</div>@enderror

            <label for="slug">Slug (URL)</label>
            <input type="text" id="slug" name="slug" value="{{ old('slug', $item->slug) }}">
            <div class="hint">Kosongkan untuk dijana daripada tajuk.</div>
            @error('slug')
            <div class="err">{{ $message }}</div>@enderror

            <label for="summary">Ringkasan</label>
            <textarea id="summary" name="summary"
                style="min-height:80px;font-family:inherit">{{ old('summary', $item->summary) }}</textarea>
            @error('summary')
            <div class="err">{{ $message }}</div>@enderror

            <label for="body">Kandungan (HTML dibenarkan)</label>
            <textarea id="body" name="body" required>{{ old('body', $item->body) }}</textarea>
            @error('body')
            <div class="err">{{ $message }}</div>@enderror

            <label for="status">Status</label>
            <select id="status" name="status">
                @foreach (['draft' => 'Draf', 'published' => 'Diterbitkan'] as $v => $l)
                    <option value="{{ $v }}" @selected(old('status', $item->status) === $v)>{{ $l }}</option>
                @endforeach
            </select>

            <label for="published_at">Tarikh terbit</label>
            <input type="datetime-local" id="published_at" name="published_at"
                value="{{ old('published_at', $item->published_at?->format('Y-m-d\TH:i')) }}">
            <div class="hint">Kosongkan untuk guna masa sekarang apabila diterbitkan.</div>
            @error('published_at')
            <div class="err">{{ $message }}</div>@enderror

            <p class="row"><button class="btn">Simpan</button><a class="btn gray"
                    href="{{ route('admin.announcements.index') }}">Batal</a></p>
        </form>
    </div>
@endsection