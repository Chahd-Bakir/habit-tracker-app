<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use App\Jobs\GenerateDailyPrompts;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new GenerateDailyPrompts)->dailyAt('00:00');

Schedule::command('app:send-mood-reminders')->everyFifteenMinutes();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('prompts:generate', function () {
    dispatch_sync(new GenerateDailyPrompts);
    $this->info('Daily prompts generated successfully!');
})->purpose('Generate daily mood journal prompts via AI');
