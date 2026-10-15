@extends('admin.layout')
@section('title', __('Media'))
@section('content')
<h1>{{ __('Media') }}</h1>
<div class="card">
    <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="row">
        @csrf
        <input type="file" name="file" accept=".jpg,.jpeg,.png,.webp,.gif,.pdf" required>
        <button class="btn">{{ __('Muat naik') }}</button>
        <span class="hint">{{ __('JPG, PNG, WEBP, GIF atau PDF, maksimum 10 MB.') }}</span>
    </form>
    @error('file')
    <div class="err">{{ $message }}</div>@enderror
</div>

<div class="card">
    <table>
        <tr>
            <th>{{ __('Pratonton') }}</th>
            <th>{{ __('Alamat fail') }}</th>
            <th>{{ __('Saiz') }}</th>
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
                    onsubmit="return confirm(@js(__('Padam fail ini? Halaman yang menggunakannya akan rosak.')))">
                    @csrf @method('DELETE')
                    <input type="hidden" name="path" value="{{ $rel }}">
                    <button class="btn red" style="padding:3px 10px">{{ __('Padam') }}</button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4">{{ __('Tiada fail dimuat naik lagi.') }}</td>
        </tr>
        @endforelse
    </table>
    <div class="hint" style="margin-top:8px">{{ __('Gunakan butang “Pilih / muat naik gambar” atau “Pilih / muat naik fail” dalam editor halaman dan hebahan untuk menggunakan fail ini.') }}
    </div>
</div>
@endsection
