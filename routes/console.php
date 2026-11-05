<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('calendar:sync-holidays --force')->dailyAt('03:15')->timezone('Asia/Kuala_Lumpur')->withoutOverlapping();
