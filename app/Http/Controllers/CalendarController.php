<?php

namespace App\Http\Controllers;

use App\Support\EventCalendar;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate(['month' => ['nullable', 'date_format:Y-m', 'regex:/^(?:20\d{2}|2100)-(0[1-9]|1[0-2])$/']]);
        $month = $data['month'] ?? now('Asia/Kuala_Lumpur')->format('Y-m');
        $first = CarbonImmutable::createFromFormat('!Y-m', $month, 'Asia/Kuala_Lumpur')->startOfWeek(CarbonImmutable::MONDAY);
        $last = $first->addDays(41)->toDateString();
        $entries = array_values(array_filter(EventCalendar::entries(false, array_values(array_unique([
            (int) substr($month, 0, 4), $first->year, (int) substr($last, 0, 4),
        ]))), fn (array $entry): bool => $entry['start_date'] <= $last && $entry['end_date'] >= $first->toDateString()));

        return response()->json([
            'month' => $month, 'timezone' => 'Asia/Kuala_Lumpur', 'entries' => $entries,
            'holiday_source' => EventCalendar::holidaySource((int) substr($month, 0, 4)),
            'holiday_years' => EventCalendar::holidayYears(),
        ])->header('Cache-Control', 'no-store');
    }
}
