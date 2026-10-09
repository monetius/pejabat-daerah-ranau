@extends('admin.layout')
@section('title', 'Media')
@section('content')
<h1>Media</h1>
<div class="card">
    <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="row">
        @csrf
        <input type="file" name="file" accept=".jpg,.jpeg,.png,.webp,.gif,.pdf" required>
        <button class="btn">Muat naik</button>
        <span class="hint">JPG, PNG, WEBP, GIF atau PDF, maksimum 10 MB.</span>
    </form>
    @error('file')
    <div class="err">{{ $message }}</div>@enderror
</div>

<div class="card">
    <table>
        <tr>
            <th>Pratonton</th>
            <th>URL (salin ke dalam halaman)</th>
            <th>Saiz</th>
            <th></th>
        </tr>
        @forelse ($files as $f)
        @php($rel = str_replace('\\', '/', $f->getRelativePathname()))
        <tr>
            <td style="width:90px">
                @if (preg_match('/\.(jpe?g|png|webp|gif)$/i', $rel))
                    <img src="/uploads/{{ $rel }}" alt="" style="width:80px;height:60px;object-fit:cover;border-radius:4px">
                @else PDF @endif
            </td>
            <td><input type="text" readonly value="/uploads/{{ $rel }}"
                    onclick="this.select();document.execCommand('copy')"></td>
            <td>{{ number_format($f->getSize() / 1024, 0) }} KB</td>
            <td>
                <form method="POST" action="{{ route('admin.media.destroy') }}"
                    onsubmit="return confirm('Padam fail ini? Halaman yang menggunakannya akan rosak.')">
                    @csrf @method('DELETE')
                    <input type="hidden" name="path" value="{{ $rel }}">
                    <button class="btn red" style="padding:3px 10px">Padam</button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4">Tiada fail dimuat naik lagi.</td>
        </tr>
        @endforelse
    </table>
    <div class="hint" style="margin-top:8px">Klik pada URL untuk menyalinnya. Contoh dalam HTML:
        <code>&lt;img src="/uploads/2026/10/gambar.jpg" alt=""&gt;</code>
    </div>
</div>
@endsection