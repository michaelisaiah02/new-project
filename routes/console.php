<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Kirim via Email tepat jam 00:00 (Tengah Malem)
$schedule->command('notify:project-reminders email')->dailyAt('00:00');

// Kirim via WA jam 06:00 (Pagi pas orang pada bangun)
$schedule->command('notify:project-reminders wa')->dailyAt('06:00');

// Kalau mau ngetes tiap menit (buat debugging):
// Schedule::command('notify:project-reminders')->everyMinute();
