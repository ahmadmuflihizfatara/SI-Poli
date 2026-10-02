<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daftar taruna yang kontrol hari ini ke grup Telegram (WIB; butuh cron `schedule:run` tiap menit)
Schedule::command('telegram:kontrol-harian')->dailyAt(config('services.telegram.jam_kirim'))->withoutOverlapping();
