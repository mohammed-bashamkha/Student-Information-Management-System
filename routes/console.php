<?php

use App\Jobs\SuspendExpiredAdmissionsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(SuspendExpiredAdmissionsJob::class)
    ->everyMinute()
    ->name('suspend-expired-admissions')
    ->withoutOverlapping()
    ->onFailure(function () {
        Log::error('[Scheduler] فشل تشغيل SuspendExpiredAdmissionsJob');
    });
