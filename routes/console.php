<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // <--- JANGAN LUPA IMPORT INI

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Panggil signature yang ada di dalam file ProjectReminderCron.php
// Terus set jamnya langsung di sini (method chaining).
Schedule::command('notify:project-reminders')->dailyAt('06:00');

// Kalau mau ngetes tiap menit (buat debugging):
// Schedule::command('notify:project-reminders')->everyMinute();
