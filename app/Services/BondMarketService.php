<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Service untuk lookup data obligasi.
 *
 * TODO: Implementasikan sumber data obligasi yang tersedia (IBPA, KSEI, atau sumber lain).
 *       Saat ini service mengembalikan null sebagai fallback.
 */
class BondMarketService
{
    /**
     * Dapatkan harga obligasi pada tanggal tertentu.
     */
    public function getBondPrice(string $bondCode, string $date): ?float
    {
        try {
            // TODO: Implementasi lookup harga obligasi dari sumber data
            return null;
        } catch (\Throwable $e) {
            Log::warning('BondMarketService::getBondPrice gagal: ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Hitung return obligasi untuk periode tertentu.
     */
    public function getBondReturn(string $bondCode, string $date, int $months): ?float
    {
        try {
            $currentDate = \Carbon\Carbon::parse($date);
            $pastDate = $currentDate->copy()->subMonths($months);

            $currentPrice = $this->getBondPrice($bondCode, $currentDate->format('Y-m-d'));
            $pastPrice = $this->getBondPrice($bondCode, $pastDate->format('Y-m-d'));

            if ($currentPrice === null || $pastPrice === null || $pastPrice == 0) {
                return null;
            }

            return round(($currentPrice - $pastPrice) / $pastPrice * 100, 4);
        } catch (\Throwable $e) {
            Log::warning('BondMarketService::getBondReturn gagal: ' . $e->getMessage());
        }
        return null;
    }
}
