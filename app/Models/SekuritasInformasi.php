<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SekuritasInformasi extends Model
{
    public const TYPE_GOVERNMENT = 'government';
    public const TYPE_CORPORATE = 'corporate';

    protected $table = 'sekuritas_informasi';

    protected $fillable = [
        'type',
        'kode_obligasi',
        'nama_obligasi',
        'isin_code',
        'currency',
        'outstanding_amount',
        'coupon',
        'maturity_date',
    ];

    protected function casts(): array
    {
        return [
            'outstanding_amount' => 'decimal:2',
            'coupon' => 'decimal:4',
            'maturity_date' => 'date',
        ];
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            self::TYPE_GOVERNMENT => 'Government Bond & Sukuk',
            self::TYPE_CORPORATE => 'Corporate Bond & Sukuk',
            default => ucfirst($type),
        };
    }
}
