<?php

return [
    /*
    |--------------------------------------------------------------------------
    | IDX (Indonesia Stock Exchange) API Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk mengakses data dari website IDX (www.idx.co.id).
    |
    */

    'base_url' => env('IDX_BASE_URL', 'https://www.idx.co.id'),

    'user_agent' => env('IDX_USER_AGENT', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'),

    'timeout' => env('IDX_TIMEOUT', 15),

    /*
    |--------------------------------------------------------------------------
    | Catatan Sumber Data
    |--------------------------------------------------------------------------
    |
    | Sejak 2025, www.idx.co.id menggunakan Cloudflare yang memblokir akses
    | non-browser. Semua endpoint /primary/ IDX tidak bisa diakses langsung
    | dari server. Service ini menggunakan Yahoo Finance sebagai alternatif.
    |
    | Sumber data saat ini:
    | - Sektor: Yahoo Finance Search API (/v1/finance/search)
    | - Harga & Return: Yahoo Finance Chart API (/v8/finance/chart)
    | - Kontribusi IHSG: TIDAK tersedia (return null, input manual)
    |
    | Data yang sudah di-fetch di-cache ke database lokal (effect_sectors,
    | stock_prices) untuk mempercepat akses berikutnya.
    |
    */
];
