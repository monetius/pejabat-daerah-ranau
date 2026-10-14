@extends('admin.layout')
@section('title', 'Navbar & Footer')
@section('content')
    <h1>Navbar &amp; Footer</h1>
    <div class="card">
        <p class="hint">Bahagian yang dipaparkan pada setiap halaman: menu atas (navbar) dan bahagian bawah (footer). Ubah
            logo, alamat, nombor telefon dan pautan footer di sini. Pautan navigasi diurus melalui halaman Menu.</p>
        <table>
            @forelse ($parts as $part)
                <tr>
                    <td>{{ $part->label }}</td>
                    <td>{{ $part->updated_at?->format('d/m/Y H:i') }}</td>
                    <td><a href="{{ route('admin.parts.edit', $part) }}">Edit</a></td>
                </tr>
            @empty
                <tr>
                    <td>Kandungan belum disediakan. Hubungi pentadbir sistem.</td>
                </tr>
            @endforelse
        </table>
    </div>
@endsection
