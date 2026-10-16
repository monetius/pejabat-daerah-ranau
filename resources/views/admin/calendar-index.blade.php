@extends('admin.layout')
@section('title', __('Kalendar'))
@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">{{ __('KANDUNGAN LAMAN') }}</p><h1>{{ __('Kalendar program & acara') }}</h1><p class="page-intro">{{ __('Tandakan program, acara dan cuti umum pada kalendar laman utama.') }}</p></div>
        <a class="btn" href="{{ route('admin.calendar.create') }}">{{ __('+ Tambah acara') }}</a>
    </div>
    @error('calendar')<div class="errbox">{{ $message }}</div>@enderror
    <div class="card calendar-admin-filters">
        <form method="GET" action="{{ route('admin.calendar.index') }}" class="row">
            <div><label for="calendar-year">{{ __('Tahun') }}</label><select id="calendar-year" name="year">@foreach ($years as $option)<option value="{{ $option }}" @selected($year === $option)>{{ $option }}</option>@endforeach</select></div>
            <div><label for="calendar-kind">{{ __('Jenis') }}</label><select id="calendar-kind" name="kind"><option value="">{{ __('Semua jenis') }}</option><option value="program" @selected($kind === 'program')>{{ __('Program / Acara') }}</option><option value="holiday" @selected($kind === 'holiday')>{{ __('Cuti umum') }}</option></select></div>
            <button class="btn gray">{{ __('Tapis') }}</button>
        </form>
        <p class="hint">{{ __('Cuti umum yang diimport menggunakan jadual rasmi Sabah, termasuk cuti Malaysia yang terpakai di Sabah. Anda boleh mengemas kini tarikh, menambah cuti gantian atau memasukkan tahun baharu.') }} <a href="https://sabah.gov.my/public-holidays" target="_blank" rel="noopener noreferrer">{{ __('Rujukan rasmi') }} ↗</a></p>
        <p class="hint">{{ __('Tahun cuti diimport: :years.', ['years' => implode(', ', array_keys(config('calendar.holiday_sources', [])))]) }}</p>
    </div>
    <div class="card">
        <table>
            <tr><th>{{ __('Tarikh') }}</th><th>{{ __('Program / Acara') }}</th><th>{{ __('Jenis') }}</th><th>{{ __('Status') }}</th><th>{{ __('Tindakan') }}</th></tr>
            @forelse ($items as $entry)
                <tr>
                    <td class="calendar-admin-date">{{ \Carbon\CarbonImmutable::parse($entry['start_date'])->format('d/m/Y') }}@if ($entry['end_date'] !== $entry['start_date'])<br>– {{ \Carbon\CarbonImmutable::parse($entry['end_date'])->format('d/m/Y') }}@endif @if ($entry['start_time'])<div class="hint">{{ $entry['start_time'] }}@if ($entry['end_time']) – {{ $entry['end_time'] }}@endif MYT</div>@endif</td>
                    <td><strong>{{ $entry['title'] }}</strong>@if ($entry['location'])<div class="hint">{{ $entry['location'] }}</div>@endif @if ($entry['source_url'])<div class="hint"><a href="{{ $entry['source_url'] }}" target="_blank" rel="noopener noreferrer">{{ __('Rujukan rasmi') }} ↗</a></div>@endif</td>
                    <td>{{ $entry['kind'] === 'holiday' ? __('Cuti umum') : __('Program / Acara') }}</td>
                    <td><span class="badge {{ $entry['status'] }}">{{ $entry['status'] === 'published' ? __('Diterbitkan') : __('Draf') }}</span></td>
                    <td><div class="row"><a href="{{ route('admin.calendar.edit', $entry['id']) }}">{{ __('Edit') }}</a><form method="POST" action="{{ route('admin.calendar.destroy', $entry['id']) }}" onsubmit="return confirm(@js(__('Padam item kalendar ini?')))">@csrf @method('DELETE')<input type="hidden" name="revision" value="{{ \App\Support\EventCalendar::revision($entry) }}"><button class="btn red calendar-delete">{{ __('Padam') }}</button></form></div></td>
                </tr>
            @empty
                <tr><td colspan="5">{{ __('Tiada item kalendar untuk pilihan ini.') }}</td></tr>
            @endforelse
        </table>
        {{ $items->links() }}
    </div>
@endsection
