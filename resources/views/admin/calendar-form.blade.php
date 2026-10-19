@extends('admin.layout')
@section('title', $entry ? __('Edit acara') : __('Tambah acara'))
@section('content')
    <h1>{{ $entry ? __('Edit acara') : __('Tambah acara') }}</h1>
    <p class="page-intro">{{ __('Masukkan tajuk dan tarikh. Acara yang diterbitkan akan ditandakan pada kalendar laman utama.') }}</p>
    @if ($errors->any())<div class="errbox" role="alert"><ul>@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>@endif
    <div class="card">
        <form class="calendar-editor" method="POST" action="{{ $entry ? route('admin.calendar.update', $entry['id']) : route('admin.calendar.store') }}">
            @csrf
            @if ($entry) @method('PUT')<input type="hidden" name="revision" value="{{ old('revision', \App\Support\EventCalendar::revision($entry)) }}">@endif
            <label for="title">{{ __('Tajuk') }}</label>
            <input id="title" type="text" name="title" maxlength="180" value="{{ old('title', $entry['title'] ?? '') }}" required>
            <div class="grid2">
                <div><label for="kind">{{ __('Jenis') }}</label><select id="kind" name="kind"><option value="program" @selected(old('kind', $entry['kind'] ?? 'program') === 'program')>{{ __('Program / Acara') }}</option><option value="holiday" @selected(old('kind', $entry['kind'] ?? 'program') === 'holiday')>{{ __('Cuti umum') }}</option></select></div>
                <div><label for="status">{{ __('Status') }}</label><select id="status" name="status"><option value="draft" @selected(old('status', $entry['status'] ?? 'draft') === 'draft')>{{ __('Draf') }}</option><option value="published" @selected(old('status', $entry['status'] ?? 'draft') === 'published')>{{ __('Diterbitkan') }}</option></select></div>
                <div><label for="start_date">{{ __('Tarikh mula') }}</label><input id="start_date" name="start_date" type="date" min="2000-01-01" max="2100-12-31" value="{{ old('start_date', $entry['start_date'] ?? $initialDate) }}" required></div>
                <div><label for="end_date">{{ __('Tarikh tamat (pilihan)') }}</label><input id="end_date" name="end_date" type="date" min="2000-01-01" max="2100-12-31" value="{{ old('end_date', $entry['end_date'] ?? '') }}"><div class="hint">{{ __('Kosongkan untuk acara satu hari.') }}</div></div>
                <div><label for="start_time">{{ __('Masa mula (pilihan, MYT)') }}</label><input id="start_time" name="start_time" type="time" value="{{ old('start_time', $entry['start_time'] ?? '') }}"></div>
                <div><label for="end_time">{{ __('Masa tamat (pilihan, MYT)') }}</label><input id="end_time" name="end_time" type="time" value="{{ old('end_time', $entry['end_time'] ?? '') }}"></div>
            </div>
            <div class="hint">{{ __('Tanpa masa, acara ditandakan sepanjang hari. Cuti umum sentiasa ditandakan sepanjang hari.') }}</div>
            <label for="location">{{ __('Lokasi (pilihan)') }}</label><input id="location" name="location" type="text" maxlength="180" value="{{ old('location', $entry['location'] ?? '') }}">
            <label for="description">{{ __('Maklumat acara (pilihan)') }}</label><textarea id="description" name="description" maxlength="3000">{{ old('description', $entry['description'] ?? '') }}</textarea>
            <label for="url">{{ __('Pautan maklumat (pilihan)') }}</label><input id="url" name="url" type="text" maxlength="2000" value="{{ old('url', $entry['url'] ?? '') }}" placeholder="https://...">
            <div class="hint">{{ __('Gunakan pautan laman web http://, https:// atau pautan halaman bermula dengan /.') }}</div>
            @if ($entry && $entry['source_url'])<p class="hint">{{ str_starts_with($entry['id'], 'api-holiday-') ? __('Item ini diimport daripada suapan API. Perubahan CMS anda dikekalkan semasa penyegerakan.') : __('Item ini diimport daripada sumber rasmi. Perubahan anda akan disimpan sebagai kemas kini CMS.') }} <a href="{{ $entry['source_url'] }}" target="_blank" rel="noopener noreferrer">{{ str_starts_with($entry['id'], 'api-holiday-') ? __('Sumber API') : __('Rujukan rasmi') }} ↗</a></p>@endif
            <p class="row"><button class="btn">{{ __('Simpan acara') }}</button><a class="btn gray" href="{{ route('admin.calendar.index') }}">{{ __('Batal') }}</a></p>
        </form>
    </div>
@endsection
