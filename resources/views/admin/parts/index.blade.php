@extends('admin.layout')
@section('title', 'Navbar & Footer')
@section('content')
    <h1>Navbar &amp; Footer</h1>
    <div class="card">
        <p class="hint">Bahagian yang dipaparkan pada setiap halaman: menu atas (navbar) dan bahagian bawah (footer). Ubah
            pautan, alamat dan nombor telefon di sini.</p>
        <table>
            @forelse ($parts as $part)
                <tr>
                    <td>{{ $part->label }}</td>
                    <td>{{ $part->updated_at?->format('d/m/Y H:i') }}</td>
                    <td><a href="{{ route('admin.parts.edit', $part) }}">Sunting</a></td>
                </tr>
            @empty
                <tr>
                    <td>Belum diimport. Jalankan <code>php artisan cms:import</code>.</td>
                </tr>
            @endforelse
        </table>
    </div>
@endsection