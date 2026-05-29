<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalisaReksaDana extends Model
{
    protected $table = 'analisa_reksa_dana';

    protected $fillable = [
        'user_id',
        'product_type',
        'kode_reksa_dana',
        'nama_reksa_dana',
        'jenis_reksa_dana',
        'kategori',
        'benchmark',
        'tujuan_investasi',
        'kebijakan_investasi',
        'mata_uang',
        'total_aum',
        'unit_penyertaan',
        'nab_per_unit',
        'return_1m',
        'total_marcap_10_efek',
        'tanggal_data',
        'ffs_bulan',
        'ffs_tahun',
        'status',
        'catatan_admin',
        'ai_narasi',
        'ai_output',
        'ai_narasi_plus',
        'ai_output_plus',
        'pdf_path',
    ];

    protected $casts = [
        'ai_output'      => 'array',
        'ai_output_plus' => 'array',
        'kategori'       => 'array',
        'tanggal_data'   => 'date',
        'nab_per_unit'   => 'decimal:6',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sektor(): HasMany
    {
        return $this->hasMany(AnalisaSektor::class, 'analisa_reksa_dana_id');
    }

    public function efek(): HasMany
    {
        return $this->hasMany(AnalisaEfek::class, 'analisa_reksa_dana_id');
    }

    public function kinerja(): HasMany
    {
        return $this->hasMany(AnalisaKinerjaBulanan::class, 'analisa_reksa_dana_id');
    }

    public function obligasi(): HasMany
    {
        return $this->hasMany(AnalisaObligasi::class, 'analisa_reksa_dana_id');
    }

    public function bank(): HasMany
    {
        return $this->hasMany(AnalisaBank::class, 'analisa_reksa_dana_id');
    }

    public function alokasiAset(): HasMany
    {
        return $this->hasMany(AnalisaAlokasiAset::class, 'analisa_reksa_dana_id');
    }

    // Total MarCap 10 Saham Terbesar = SUM ihsg_contribution untuk efek Top 10 + Saham
    public function getTotalMarcap10SahamTerbesarAttribute(): ?float
    {
        return $this->efek
            ->filter(fn($e) => $e->top_10 && (!$e->effect_type || $e->effect_type === 'Saham'))
            ->sum('ihsg_contribution');
    }

    // Hitung Sharpe Ratio dari data kinerja bulanan
    public function getSharpeRatioAttribute(): ?float
    {
        $returns = $this->kinerja->pluck('return_pct')->toArray();
        if (count($returns) < 2) return null;

        $avg = array_sum($returns) / count($returns);
        $variance = array_sum(array_map(fn($r) => pow($r - $avg, 2), $returns)) / count($returns);
        $stddev = sqrt($variance);

        if ($stddev == 0) return null;

        $riskFreeMonthly = 0.4167; // asumsi risk free 5% per tahun / 12
        return round(($avg - $riskFreeMonthly) / $stddev, 4);
    }

    // RAR = Return Tahunan / Risiko (Std Dev Tahunan)
    public function getRarAttribute(): ?float
    {
        $returns = $this->kinerja->pluck('return_pct')->toArray();
        if (count($returns) < 2) return null;

        $avg = array_sum($returns) / count($returns);
        $variance = array_sum(array_map(fn($r) => pow($r - $avg, 2), $returns)) / count($returns);
        $stddev = sqrt($variance) * sqrt(12); // annualized
        $annualReturn = $avg * 12;

        if ($stddev == 0) return null;
        return round($annualReturn / $stddev, 4);
    }

    // Weighted Average Duration (Durasi Risk)
    public function getDurasiRataRataAttribute(): ?float
    {
        $obligasi = $this->obligasi->filter(fn($o) => $o->durasi !== null);
        if ($obligasi->isEmpty()) return null;

        $totalBobot = $obligasi->sum('bobot');
        if ($totalBobot == 0) return null;

        return round($obligasi->sum(fn($o) => $o->bobot * $o->durasi) / $totalBobot, 4);
    }

    // Liquidity Risk: AUM / Total MarCap 10 Efek Terbesar
    public function getLiquidityRatioAttribute(): ?float
    {
        if (!$this->total_marcap_10_efek || $this->total_marcap_10_efek == 0) return null;
        return round($this->total_aum / $this->total_marcap_10_efek, 4);
    }
}
