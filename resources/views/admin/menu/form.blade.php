@extends('admin.layout')
@section('title', $item->exists ? 'Sunting Menu' : 'Item Menu Baru')
@section('content')
    <h1>{{ $item->exists ? 'Sunting Menu' : 'Item Menu Baru' }}</h1>
    <div class="card">
        <form method="POST" action="{{ $item->exists ? route('admin.menu.update', $item) : route('admin.menu.store') }}">
            @csrf
            @if ($item->exists) @method('PUT') @endif

            <label for="label">Nama menu</label>
            <input type="text" id="label" name="label" value="{{ old('label', $item->label) }}" required>
            @error('label')<div class="err">{{ $message }}</div>@enderror

            <label for="parent_id">Letak di bawah</label>
            <select id="parent_id" name="parent_id">
                <option value="">- Menu utama (paras atas) -</option>
                @foreach ($parents as $p)
                    <option value="{{ $p->id }}" @selected((string) old('parent_id', $item->parent_id) === (string) $p->id)>
                        {{ $p->label }}</option>
                @endforeach
            </select>
            <div class="hint">Pilih menu utama untuk menjadikan ini sub-menu dalam dropdown. Hanya dua paras dibenarkan.</div>
            @error('parent_id')<div class="err">{{ $message }}</div>@enderror

            <label for="page_id">Pautan ke halaman CMS</label>
            <select id="page_id" name="page_id">
                <option value="">- Tiada (guna URL di bawah) -</option>
                @foreach ($pages as $section => $list)
                    <optgroup label="{{ $section }}">
                        @foreach ($list as $pg)
                            <option value="{{ $pg->id }}" @selected((string) old('page_id', $item->page_id) === (string) $pg->id)>
                                {{ $pg->title }} (/{{ $pg->path }}){{ $pg->status !== 'published' ? ' - draf' : '' }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            @error('page_id')<div class="err">{{ $message }}</div>@enderror

            <label for="url">atau URL tersuai</label>
            <input type="text" id="url" name="url" value="{{ old('url', $item->url) }}"
                placeholder="https://contoh.gov.my atau /laluan-lain">
            <div class="hint">Jika halaman dipilih, URL diabaikan.
                @if ($hasChildren) Menu ini ada sub-menu, jadi ia hanya menjadi butang dropdown dan pautannya tidak digunakan.@endif
                Menu utama tanpa pautan dan tanpa sub-menu tidak dipaparkan.</div>
            @error('url')<div class="err">{{ $message }}</div>@enderror

            <label><input type="checkbox" name="new_tab" value="1" @checked(old('new_tab', $item->new_tab))> Buka dalam tab
                baharu</label>
            <label><input type="checkbox" name="is_visible" value="1" @checked(old('is_visible', $item->is_visible ?? true))>
                Paparkan di navbar</label>

            <p class="row"><button class="btn">Simpan</button><a class="btn gray"
                    href="{{ route('admin.menu.index') }}">Batal</a></p>
        </form>
    </div>
@endsection
