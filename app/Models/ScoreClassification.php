<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoreClassification extends Model
{
    protected $fillable = [
        'profile_name', 'min_score', 'max_score',
        'alloc_pasar_uang', 'alloc_pendapatan_tetap', 'alloc_campuran', 'alloc_saham',
        'sort_order',
    ];

    public function getAllocation(): array
    {
        return [
            'Pasar Uang'       => $this->alloc_pasar_uang,
            'Pendapatan Tetap' => $this->alloc_pendapatan_tetap,
            'Campuran'         => $this->alloc_campuran,
            'Saham'            => $this->alloc_saham,
        ];
    }
}
