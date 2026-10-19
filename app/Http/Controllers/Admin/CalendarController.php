<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\EventCalendar;
use App\Support\HolidayFeed;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['year' => ['nullable', 'integer', 'between:2000,2100'], 'kind' => ['nullable', Rule::in(['program', 'holiday'])]]);
        $year = (int) ($filters['year'] ?? now('Asia/Kuala_Lumpur')->year);
        $kind = $filters['kind'] ?? '';
        $entries = collect(EventCalendar::entries(true, [$year]));
        $years = $entries->flatMap(fn (array $entry): array => [(int) substr($entry['start_date'], 0, 4), (int) substr($entry['end_date'], 0, 4)])
            ->push($year)->unique()->sortDesc()->values();
        $filtered = $entries->filter(fn (array $entry): bool => $entry['start_date'] <= $year.'-12-31' && $entry['end_date'] >= $year.'-01-01'
            && ($kind === '' || $entry['kind'] === $kind))->values();
        $items = new LengthAwarePaginator($filtered->forPage(LengthAwarePaginator::resolveCurrentPage(), 20)->values(), $filtered->count(), 20, null, [
            'path' => $request->url(), 'query' => $request->query(),
        ]);

        $holidayFeed = app(HolidayFeed::class);
        $holidaySnapshot = $holidayFeed->snapshot($year);

        return view('admin.calendar-index', compact('items', 'years', 'year', 'kind', 'holidayFeed', 'holidaySnapshot'));
    }

    public function sync(Request $request, HolidayFeed $holidayFeed): RedirectResponse
    {
        $data = $request->validate(['year' => ['required', 'integer', 'between:2000,2100']]);
        $success = $holidayFeed->refresh((int) $data['year'], true);

        return redirect()->route('admin.calendar.index', ['year' => $data['year']])->with('status', $success
            ? __('Cuti umum dikemas kini. Perubahan CMS anda dikekalkan.')
            : __('Suapan cuti belum tersedia. Data tersimpan dan jadual rasmi dikekalkan.'));
    }

    public function create(Request $request): View
    {
        $data = $request->validate(['date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:2000-01-01', 'before_or_equal:2100-12-31']]);

        return view('admin.calendar-form', ['entry' => null, 'initialDate' => $data['date'] ?? now('Asia/Kuala_Lumpur')->toDateString()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $entry = EventCalendar::save($this->validated($request));

        return redirect()->route('admin.calendar.index', ['year' => substr($entry['start_date'], 0, 4)])->with('status', __('Item kalendar disimpan.'));
    }

    public function edit(string $event): View
    {
        return view('admin.calendar-form', ['entry' => EventCalendar::find($event), 'initialDate' => null]);
    }

    public function update(Request $request, string $event): RedirectResponse
    {
        $revision = $request->validate(['revision' => ['required', 'string', 'size:64']])['revision'];
        $entry = EventCalendar::save($this->validated($request), $event, $revision);

        return redirect()->route('admin.calendar.index', ['year' => substr($entry['start_date'], 0, 4)])->with('status', __('Item kalendar dikemas kini.'));
    }

    public function destroy(Request $request, string $event): RedirectResponse
    {
        $revision = $request->validate(['revision' => ['required', 'string', 'size:64']])['revision'];
        EventCalendar::delete($event, $revision);

        return redirect()->route('admin.calendar.index')->with('status', __('Item kalendar dipadam.'));
    }

    /** @return array{title: string, kind: string, start_date: string, end_date: string, start_time: ?string, end_time: ?string, location: ?string, description: ?string, url: ?string, status: string} */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'kind' => ['required', Rule::in(['program', 'holiday'])],
            'start_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:2000-01-01', 'before_or_equal:2100-12-31'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date', 'before_or_equal:2100-12-31'],
            'start_time' => ['nullable', 'required_with:end_time', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:3000'],
            'url' => ['nullable', 'string', 'max:2000', 'regex:~^(?:/(?!/)[^\\\\\s]*|https?://[^\s]+)$~i',
                Rule::when(is_string($request->input('url')) && preg_match('~^https?://~i', $request->input('url')), ['url:http,https']),
            ],
            'status' => ['required', Rule::in(['draft', 'published'])],
        ]);
        $data['end_date'] = $data['end_date'] ?? $data['start_date'];
        if (CarbonImmutable::parse($data['start_date'])->diffInDays(CarbonImmutable::parse($data['end_date'])) > 366) {
            throw ValidationException::withMessages(['end_date' => __('Tempoh acara tidak boleh melebihi 366 hari.')]);
        }
        foreach (['start_time', 'end_time', 'location', 'description', 'url'] as $field) {
            $data[$field] = isset($data[$field]) ? trim($data[$field]) : null;
        }
        if ($data['kind'] === 'holiday') {
            $data['start_time'] = $data['end_time'] = null;
        } elseif ($data['start_date'] === $data['end_date'] && $data['end_time'] !== null && $data['end_time'] <= $data['start_time']) {
            throw ValidationException::withMessages(['end_time' => __('Masa tamat mestilah selepas masa mula.')]);
        }

        return $data;
    }
}
