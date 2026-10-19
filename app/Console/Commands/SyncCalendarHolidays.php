<?php

namespace App\Console\Commands;

use App\Support\HolidayFeed;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('calendar:sync-holidays {--year=* : Calendar years to refresh (defaults to this year and next year)} {--force : Refresh even when the cache is fresh}')]
#[Description('Refresh cached Sabah public holidays while preserving official dates and CMS overrides')]
class SyncCalendarHolidays extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(HolidayFeed $holidayFeed): int
    {
        if (! $holidayFeed->enabled()) {
            $this->info('The public holiday feed is disabled. Verified local holidays remain available.');

            return self::SUCCESS;
        }
        $years = $this->option('year') ?: [now('Asia/Kuala_Lumpur')->year, now('Asia/Kuala_Lumpur')->year + 1];
        foreach ($years as $year) {
            if (filter_var($year, FILTER_VALIDATE_INT, ['options' => ['min_range' => 2000, 'max_range' => 2100]]) === false) {
                $this->error('Years must be between 2000 and 2100.');

                return self::INVALID;
            }
        }
        $failed = false;
        foreach (array_unique(array_map('intval', $years)) as $year) {
            if ($holidayFeed->refresh($year, (bool) $this->option('force'))) {
                $count = count($holidayFeed->snapshot($year)['entries']);
                $this->info($year.': '.$count.' Sabah holiday records cached. CMS edits and verified dates are preserved.');
            } else {
                $failed = true;
                $this->warn($year.': feed unavailable or a refresh is already running. Existing holidays are preserved.');
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
