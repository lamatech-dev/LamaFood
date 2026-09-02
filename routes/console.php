<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('backup:create database')->dailyAt('02:15')->withoutOverlapping();
Schedule::command('backup:create full')->weeklyOn(1, '03:15')->withoutOverlapping();
Schedule::command('health:scheduler-heartbeat')->everyMinute()->withoutOverlapping();
