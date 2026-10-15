@extends('admin.layout')
@section('title', $item->exists ? __('Edit Hebahan') : __('Hebahan Baru'))
@section('content')
    <h1>{{ $item->exists ? __('Edit Hebahan') : __('Hebahan Baru') }}</h1>
    <p class="page-intro">{{ __('Sediakan kandungan, pilih kategori dan tentukan masa penerbitan.') }}</p>
    <div class="card">
        <form method="POST"
            action="{{ $item->exists ? route('admin.announcements.update', $item) : route('admin.announcements.store') }}"
            enctype="multipart/form-data">
            @csrf
            @if ($item->exists) @method('PUT') @endif

            <label for="title">{{ __('Tajuk') }}</label>
            <input type="text" id="title" name="title" value="{{ old('title', $item->title) }}" required>
            @error('title')
            <div class="err">{{ $message }}</div>@enderror

            <label for="author">{{ __('Penulis / Author (pilihan)') }}</label>
            <input type="text" id="author" name="author" maxlength="150" value="{{ old('author', $item->author) }}"
                placeholder="{{ __('Nama penulis atau jabatan') }}" @disabled(!$authorEnabled)>
            <div class="hint">{{ $authorEnabled ? __('Dipaparkan bersama berita di laman web. Kosongkan jika tidak digunakan.') : __('Fungsi penulis belum diaktifkan.') }}</div>
            @error('author')
            <div class="err">{{ $message }}</div>@enderror

            <label for="category">{{ __('Kategori') }}</label>
            <select id="category" name="category">
                @foreach (\App\Models\Announcement::CATEGORIES as $v => $l)
                    <option value="{{ $v }}" @selected(old('category', $item->category ?? 'hebahan') === $v)>{{ __($l) }}</option>
                @endforeach
            </select>
            @error('category')
            <div class="err">{{ $message }}</div>@enderror

            <label for="slug">{{ __('Nama ringkas URL (pilihan)') }}</label>
            <input type="text" id="slug" name="slug" value="{{ old('slug', $item->slug) }}">
            <div class="hint">{{ __('Kosongkan untuk dijana daripada tajuk.') }}</div>
            @error('slug')
            <div class="err">{{ $message }}</div>@enderror

            <label for="summary">{{ __('Ringkasan') }}</label>
            <textarea id="summary" name="summary"
                style="min-height:80px;font-family:inherit">{{ old('summary', $item->summary) }}</textarea>
            @error('summary')
            <div class="err">{{ $message }}</div>@enderror

            <label for="image">{{ __('Gambar utama') }}</label>
            @if ($item->image)
                <p><img src="{{ $item->image }}" alt="" style="max-width:260px;border-radius:8px;display:block"></p>
                <label style="font-weight:400;margin:4px 0"><input type="checkbox" name="remove_image" value="1"> {{ __('Buang gambar ini') }}</label>
            @endif
            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
            <div class="hint">{{ __('JPG, PNG atau WebP, maksimum 5 MB. Dipaparkan pada kad di laman utama dan senarai hebahan. Tanpa gambar, ikon kategori digunakan.') }}</div>
            @error('image')
            <div class="err">{{ $message }}</div>@enderror

            @include('admin.content-editor')

            <h2 class="sec">{{ __('Penerbitan') }}</h2>
            <label for="status">{{ __('Status') }}</label>
            <select id="status" name="status">
                @foreach (['draft' => __('Draf'), 'published' => __('Diterbitkan')] as $v => $l)
                    <option value="{{ $v }}" @selected(old('status', $item->status) === $v)>{{ $l }}</option>
                @endforeach
            </select>

            <label for="published_at">{{ __('Tarikh terbit (waktu Malaysia, MYT)') }}</label>
            <input type="datetime-local" id="published_at" name="published_at"
                value="{{ old('published_at', $item->published_at?->format('Y-m-d\TH:i')) }}">
            <div class="hint">{{ __('Kosongkan untuk terbit sekarang. Tarikh akan datang menjadualkan hebahan; ia hanya muncul di laman web apabila tiba waktunya.') }}</div>
            @error('published_at')
            <div class="err">{{ $message }}</div>@enderror

            <p class="row"><button class="btn">{{ __('Simpan hebahan') }}</button><a class="btn gray"
                    href="{{ route('admin.announcements.index') }}">{{ __('Batal') }}</a></p>
        </form>
    </div>
@endsection
