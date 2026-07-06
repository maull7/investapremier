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

// Sync RD + Harga Harian dari Pasardana setiap hari jam 07:00 WIB
Schedule::call(function () {
    $inflight = \App\Models\SyncRun::whereIn('type', [
        \App\Models\SyncRun::TYPE_RD_HARGA_HARIAN,
        \App\Models\SyncRun::TYPE_ALL_PASARDANA,
    ])->whereIn('status', [
        \App\Models\SyncRun::STATUS_QUEUED,
        \App\Models\SyncRun::STATUS_RUNNING,
    ])->where('updated_at', '>=', now()->subHours(2))
        ->exists();

    if ($inflight) return;

    $run = \App\Models\SyncRun::create([
        'type' => \App\Models\SyncRun::TYPE_RD_HARGA_HARIAN,
        'status' => \App\Models\SyncRun::STATUS_QUEUED,
        'current_step' => 'queued',
        'current_step_label' => 'Menunggu worker mengambil job dari antrian (scheduled)',
        'progress_percent' => 0,
    ]);

    \App\Jobs\SyncReksaDanaFromPasardanaJob::dispatch($run->id);
})->name('sync-rd-harian')->dailyAt('07:00')->withoutOverlapping()->timezone(config('app.timezone'));
