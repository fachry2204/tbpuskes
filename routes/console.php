<?php

use App\Jobs\SendScheduledRemindersJob;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new SendScheduledRemindersJob())->hourly();

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
