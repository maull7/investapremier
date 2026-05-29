<?php

namespace App\Services;

use App\Models\ObligasiBond;
use Illuminate\Database\Eloquent\Collection;

class KeuanganEmitenService
{
    public function getByPeriod(string $kode, string $periode): ?ObligasiBond
    {
        return ObligasiBond::query()
            ->whereRaw('UPPER(kode) = ?', [strtoupper(trim($kode))])
            ->where('periode', trim($periode))
            ->first();
    }

    public function getByYear(string $kode, string $tahun): Collection
    {
        return ObligasiBond::query()
            ->whereRaw('UPPER(kode) = ?', [strtoupper(trim($kode))])
            ->where('periode', 'like', trim($tahun) . '%')
            ->orderBy('periode')
            ->get();
    }
}
