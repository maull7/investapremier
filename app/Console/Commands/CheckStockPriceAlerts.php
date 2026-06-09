<?php

namespace App\Console\Commands;

use App\Models\Stock;
use App\Models\StockPrice;
use App\Models\StockPriceAlert;
use App\Notifications\StockPriceAlertTriggered;
use Illuminate\Console\Command;

class CheckStockPriceAlerts extends Command
{
    protected $signature = 'stocks:check-price-alerts
                            {--dry-run : Hanya tampilkan alert yang akan ter-trigger tanpa kirim notifikasi}';

    protected $description = 'Cek seluruh alert harga saham aktif & kirim notifikasi ke pemilik alert jika kondisi terpenuhi';

    public function handle(): int
    {
        $alerts = StockPriceAlert::query()
            ->where('is_active', true)
            ->whereNotNull('user_id')
            ->with('user')
            ->get();

        if ($alerts->isEmpty()) {
            $this->info('Tidak ada alert aktif.');
            return self::SUCCESS;
        }

        $kodeEfeks = $alerts->pluck('kode_efek')
            ->map(fn ($k) => strtoupper($k))
            ->unique()
            ->values()
            ->all();

        $latestPrices = StockPrice::hargaTerbaruBulk($kodeEfeks);

        // Fallback: gunakan kolom harga_terbaru di tabel stocks bila harga harian belum ada.
        $stocksByKode = Stock::whereIn('kode', $kodeEfeks)
            ->get(['id', 'kode', 'harga_terbaru', 'last_update'])
            ->keyBy(fn ($s) => strtoupper($s->kode));

        $triggered = 0;
        $skipped   = 0;

        foreach ($alerts as $alert) {
            $kode = strtoupper($alert->kode_efek);

            $price        = null;
            $tanggalHarga = null;

            if (isset($latestPrices[$kode])) {
                $price        = (float) $latestPrices[$kode]->harga;
                $tanggalHarga = optional($latestPrices[$kode]->tanggal)->toDateString();
            } elseif (isset($stocksByKode[$kode]) && $stocksByKode[$kode]->harga_terbaru !== null) {
                $price        = (float) $stocksByKode[$kode]->harga_terbaru;
                $tanggalHarga = optional($stocksByKode[$kode]->last_update)->toDateString();
            }

            if ($price === null) {
                $skipped++;
                continue;
            }

            $alert->last_seen_price = $price;

            if (! $alert->isConditionMet($price)) {
                $alert->save();
                continue;
            }

            // Sudah pernah ter-trigger & tidak repeat → skip.
            if ($alert->triggered_at && ! $alert->repeat) {
                $alert->save();
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line(sprintf(
                    '[DRY] %s → %s | target %s | harga %s',
                    $alert->user->email ?? 'unknown',
                    $kode,
                    number_format((float) $alert->target_price, 2, ',', '.'),
                    number_format($price, 2, ',', '.'),
                ));
                $triggered++;
                continue;
            }

            $alert->user?->notify(new StockPriceAlertTriggered($alert, $price, $tanggalHarga));

            $alert->triggered_at = now();
            // Auto-disable jika tidak repeat.
            if (! $alert->repeat) {
                $alert->is_active = false;
            }
            $alert->save();

            $triggered++;
        }

        $this->info(sprintf(
            'Selesai. Triggered: %d, Skipped (no price): %d, Total alerts: %d',
            $triggered,
            $skipped,
            $alerts->count(),
        ));

        return self::SUCCESS;
    }
}
