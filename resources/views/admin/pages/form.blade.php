@extends('admin.layout')
@section('title', $item->exists ? 'Sunting: ' . $item->title : 'Halaman Baru')

@push('head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
@endpush

@section('content')
@php($isHome = $item->exists && $item->path === '')
<div class="row" style="justify-content:space-between;margin-bottom:12px">
    <h1 style="margin:0">{{ $item->exists ? 'Sunting Halaman' : 'Halaman Baru' }}</h1>
    @if ($item->exists)<a href="{{ $item->url() }}" target="_blank">Lihat di laman web &rarr;</a>@endif
</div>

@if ($errors->any() && !$errors->has('delete'))
    <div class="errbox">Sila semak ruangan yang bertanda merah di bawah.</div>
@endif

<form method="POST" id="pageForm"
    action="{{ $item->exists ? route('admin.pages.update', $item) : route('admin.pages.store') }}">
    @csrf
    @if ($item->exists) @method('PUT') @endif

    <div class="card">
        <label for="title">Tajuk (tajuk tab pelayar)</label>
        <input type="text" id="title" name="title" value="{{ old('title', $item->title) }}" required>
        @error('title')
        <div class="err">{{ $message }}</div>@enderror

        <div class="grid2">
            <div>
                <label for="path">URL halaman</label>
                <input type="text" id="path" name="path" value="{{ old('path', $item->path) }}" @disabled($isHome)
                    placeholder="contoh: info-korporat/halaman-baru">
                <div class="hint">
                    @if ($isHome) Halaman utama sentiasa di <code>/</code>.
                    @elseif ($item->exists && $item->is_system) Amaran: menukar URL akan memutuskan pautan sedia ada
                        dalam menu dan halaman lain.
                    @else Kosongkan untuk guna <code>halaman/slug-tajuk</code>. @endif
                </div>
                @error('path')
                <div class="err">{{ $message }}</div>@enderror
            </div>
            <div>
                <label for="section">Kumpulan (dalam senarai admin)</label>
                <input type="text" id="section" name="section" value="{{ old('section', $item->section) }}">
            </div>
        </div>

        <label for="meta_description">Penerangan SEO</label>
        <input type="text" id="meta_description" name="meta_description"
            value="{{ old('meta_description', $item->meta_description) }}">
        @error('meta_description')
        <div class="err">{{ $message }}</div>@enderror

        <div class="grid2">
            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    @foreach (['draft' => 'Draf (tersembunyi)', 'published' => 'Diterbitkan'] as $v => $l)
                        <option value="{{ $v }}" @selected(old('status', $item->status) === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="slug">Slug</label>
                <input type="text" id="slug" name="slug" value="{{ old('slug', $item->slug) }}" @disabled($isHome)>
                @error('slug')
                <div class="err">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    <div class="card">
        <h2 class="sec" style="margin-top:0">Kandungan halaman (HTML)</h2>
        <div class="hint" style="margin-bottom:8px">Ini ialah HTML di dalam bahagian utama halaman. Ubah teks di antara
            tag, jangan padam tag atau nama kelas (class) kecuali anda tahu kesannya. Gunakan <b>Pratonton</b> sebelum
            simpan, dan halaman Media untuk URL gambar.</div>
        <textarea id="body" name="body">{{ old('body', $item->body) }}</textarea>
        @error('body')
        <div class="err">{{ $message }}</div>@enderror
    </div>

    <div class="card">
        <h2 class="sec" style="margin-top:0">Tetapan lanjutan</h2>
        <div class="grid2">
            <div>
                <label for="body_class">Kelas &lt;body&gt;</label>
                <input type="text" id="body_class" name="body_class" value="{{ old('body_class', $item->body_class) }}">
                <div class="hint">Contoh: <code>has-banner</code>, <code>is-home</code>.</div>
            </div>
            <div>
                <label for="styles">Fail CSS (satu setiap baris)</label>
                <textarea id="styles" name="styles"
                    style="min-height:70px">{{ old('styles', $item->styles) }}</textarea>
                <div class="hint">Nama fail dalam <code>assets/css/</code>, contoh <code>dasar.css</code>.</div>
            </div>
        </div>
        <label for="scripts">Skrip tambahan (pilihan)</label>
        <textarea id="scripts" name="scripts" style="min-height:120px">{{ old('scripts', $item->scripts) }}</textarea>
        <div class="hint">Contoh: <code>&lt;script src="/assets/js/mengenai-slider.js"&gt;&lt;/script&gt;</code></div>
    </div>

    <p class="row">
        <button class="btn">Simpan</button>
        <button class="btn gray" type="submit" formaction="{{ route('admin.pages.preview') }}"
            formtarget="_blank">Pratonton (belum simpan)</button>
        <a class="btn gray" href="{{ route('admin.pages.index') }}">Kembali</a>
    </p>
</form>

@if ($item->exists && !$item->is_system)
    <form method="POST" action="{{ route('admin.pages.destroy', $item) }}" onsubmit="return confirm('Padam halaman ini?')">
        @csrf @method('DELETE')
        <button class="btn red">Padam halaman ini</button>
    </form>
@endif

@if (!empty($revisions) && $revisions->count())
    <div class="card" style="margin-top:20px">
        <h2 class="sec" style="margin-top:0">Versi lama (30 terkini)</h2>
        <div class="hint" style="margin-bottom:6px">Setiap kali anda menyimpan perubahan, versi sebelumnya disimpan di sini.
            Pulihkan jika tersilap.</div>
        <table>
            @foreach ($revisions as $rev)
                <tr>
                    <td>{{ $rev->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $rev->author?->name ?? '-' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.pages.restore', [$item, $rev]) }}"
                            onsubmit="return confirm('Pulihkan versi ini? Versi semasa akan disimpan dalam senarai ini.')">
                            @csrf
                            <button class="btn gray" style="padding:3px 10px">Pulihkan</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@endif
@endsection

@push('foot')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js"></script>
    <script>
        if (window.CodeMirror) {
            CodeMirror.fromTextArea(document.getElementById('body'), { mode: 'htmlmixed', lineNumbers: true, lineWrapping: true });
        }
    </script>
@endpush