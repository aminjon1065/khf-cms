<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('news:publish-scheduled')->everyMinute();

// Shared-hosting queue draining: no persistent `queue:work` process. The single
// cron that already runs the scheduler (`* * * * * php artisan schedule:run`)
// drains queued jobs (revalidation webhook, WebP conversions, ЧС e-mail) once a
// minute. --stop-when-empty exits as soon as the queue is empty; --max-time caps
// a run under the minute so it never overlaps the next tick.
Schedule::command('queue:work --stop-when-empty --max-time=55 --tries=3')
    ->everyMinute()
    ->withoutOverlapping(5);
