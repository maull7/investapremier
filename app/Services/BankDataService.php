<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Service untuk lookup data bank (CAR, NPL).
 *
 * TODO: Implementasikan sumber data bank (OJK, website Bank Indonesia, atau sumber lain).
 *       Saat ini service mengembalikan null sebagai fallback.
 */
class BankDataService
{
    /**
     * Dapatkan CAR (Capital Adequacy Ratio) bank pada tanggal tertentu.
     */
    public function getCar(string $bankName, string $date): ?float
    {
        try {
            // TODO: Implementasi lookup CAR dari sumber data OJK/BI
            return null;
        } catch (\Throwable $e) {
            Log::warning('BankDataService::getCar gagal: ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Dapatkan NPL (Non-Performing Loan) bank pada tanggal tertentu.
     */
    public function getNpl(string $bankName, string $date): ?float
    {
        try {
            // TODO: Implementasi lookup NPL dari sumber data OJK/BI
            return null;
        } catch (\Throwable $e) {
            Log::warning('BankDataService::getNpl gagal: ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Dapatkan klasifikasi risiko bank.
     */
    public function getKlasifikasiRisiko(string $bankName, string $date): ?string
    {
        try {
            // TODO: Implementasi lookup klasifikasi risiko dari sumber data
            return null;
        } catch (\Throwable $e) {
            Log::warning('BankDataService::getKlasifikasiRisiko gagal: ' . $e->getMessage());
        }
        return null;
    }
}
