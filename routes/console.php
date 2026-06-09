<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Horizon metrics snapshot setiap 5 menit
Schedule::command('horizon:snapshot')->everyFiveMinutes();

// Cek alert harga saham member setiap 5 menit pada jam pasar (Senin-Jumat 08:30-16:30 WIB)
// Skip akhir pekan untuk hemat resource. Tetap jalan tiap 30 menit di luar jam pasar
// agar alert tetap masuk ketika data harga di-update di luar jam.
Schedule::command('stocks:check-price-alerts')
    ->everyFiveMinutes()
    ->weekdays()
    ->between('8:30', '16:30')
    ->withoutOverlapping();

Schedule::command('stocks:check-price-alerts')
    ->everyThirtyMinutes()
    ->withoutOverlapping();
