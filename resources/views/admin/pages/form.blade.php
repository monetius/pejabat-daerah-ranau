@extends('admin.layout')
@section('title', $item->exists ? 'Edit: ' . $item->title : 'Halaman Baru')

@section('content')
@php($isHome = $item->exists && $item->path === '')
<div class="row" style="justify-content:space-between;margin-bottom:12px">
    <h1 style="margin:0">{{ $item->exists ? 'Edit Halaman' : 'Halaman Baru' }}</h1>
    @if ($item->exists)<a href="{{ $item->url() }}" target="_blank">Lihat di laman web &rarr;</a>@endif
</div>

@if ($errors->any() && !$errors->has('delete'))
    <div class="errbox">Perubahan belum disimpan. Sila semak mesej di bawah.</div>
@endif

<form method="POST" id="pageForm"
    action="{{ $item->exists ? route('admin.pages.update', $item) : route('admin.pages.store') }}">
    @csrf
    @if ($item->exists)<input type="hidden" name="page_id" value="{{ $item->id }}">@endif
    @if ($item->exists) @method('PUT') @endif

    <div class="card">
        <div class="grid2">
            <div>
                <label for="title">Nama halaman</label>
                <input type="text" id="title" name="title" value="{{ old('title', $item->title) }}" required>
            </div>
            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    @foreach (['draft' => 'Draf (tersembunyi)', 'published' => 'Diterbitkan'] as $v => $l)
                        <option value="{{ $v }}" @selected(old('status', $item->status) === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <details class="editor-page-options">
            <summary>Pilihan halaman: URL, kumpulan dan penerangan</summary>
            <div class="grid2">
                <div>
                    <label for="path">URL halaman</label>
                    <input type="text" id="path" name="path" value="{{ old('path', $item->path) }}" @disabled($isHome)
                        placeholder="contoh: profil-daerah/halaman-baru">
                    <div class="hint">
                        @if ($isHome) Halaman utama sentiasa di /.
                        @elseif ($item->exists) Kekalkan URL ini supaya pautan sedia ada terus berfungsi.
                        @else Kosongkan untuk dijana daripada nama halaman. @endif
                    </div>
                </div>
                <div>
                    <label for="section">Kumpulan</label>
                    <input type="text" id="section" name="section" value="{{ old('section', $item->section) }}" placeholder="Contoh: Profil Daerah">
                </div>
            </div>
            <label for="meta_description">Penerangan ringkas untuk carian</label>
            <input type="text" id="meta_description" name="meta_description" value="{{ old('meta_description', $item->meta_description) }}">
            <label for="slug">Nama ringkas URL (pilihan)</label>
            <input type="text" id="slug" name="slug" value="{{ old('slug', $item->slug) }}" @disabled($isHome)>
        </details>
    </div>

    <div class="card">
        @include('admin.content-editor')
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

