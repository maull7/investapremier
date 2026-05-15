<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockPrice extends Model
{
    protected $fillable = ['kode_efek', 'nama_efek', 'jenis', 'harga', 'tanggal', 'sumber'];

    protected $casts = ['tanggal' => 'date', 'harga' => 'decimal:2'];

    /**
     * Ambil harga terbaru (T-1) untuk kode efek tertentu.
     */
    public static function hargaTerbaru(string $kodeEfek): ?self
    {
        return static::where('kode_efek', strtoupper($kodeEfek))
            ->orderByDesc('tanggal')
            ->first();
    }

    /**
     * Ambil harga terbaru untuk banyak kode efek sekaligus.
     * Return: ['BBCA' => StockPrice, ...]
     */
    public static function hargaTerbaruBulk(array $kodeEfeks): array
    {
        $kodeEfeks = array_map('strtoupper', $kodeEfeks);

        // Subquery: ambil tanggal terbaru per kode_efek
        $latest = static::selectRaw('kode_efek, MAX(tanggal) as max_tanggal')
            ->whereIn('kode_efek', $kodeEfeks)
            ->groupBy('kode_efek');

        $prices = static::joinSub($latest, 'latest', function ($join) {
            $join->on('stock_prices.kode_efek', '=', 'latest.kode_efek')
                 ->on('stock_prices.tanggal', '=', 'latest.max_tanggal');
        })->get();

        return $prices->keyBy('kode_efek')->all();
    }
}
