<?php

namespace App\Models;

use App\Services\KodeReksaDanaParser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalisaReksaDana extends Model
{
    protected $table = 'analisa_reksa_dana';

    protected $appends = [
        'display_mata_uang',
    ];

    protected $fillable = [
        'user_id',
        'product_type',
        'reksa_dana_id',
        'kode_reksa_dana',
        'nama_reksa_dana',
        'jenis_reksa_dana',
        'kategori',
        'benchmark',
        'manajer_investasi',
        'bank_kustodian',
        'tanggal_peluncuran',
        'tujuan_investasi',
        'kebijakan_investasi',
        'mata_uang',
        'total_aum',
        'unit_penyertaan',
        'nab_per_unit',
        'return_1m',
        'return_ytd',
        'return_1y',
        'total_marcap_10_efek',
        'portofolio_efek',
        'instrumen_pasar_uang',
        'portofolio_efek_total',
        'piutang_transaksi_efek',
        'piutang_bunga_dan_dividen',
        'uang_muka_diterima',
        'liabilitas_pembelian_kembali',
        'beban_akrual',
        'liabilitas_atas_biaya',
        'pembelian_kembali_unit_penyertaan',
        'utang_pajak_lainnya',
        'pendapatan_investasi',
        'pendapatan_lainnya',
        'beban_investasi',
        'beban_pengelolaan_investasi',
        'pembelian_efek_ekuitas',
        'penjualan_efek_ekuitas',
        'penerimaan_bunga_deposito',
        'penerimaan_bunga_jasa_giro',
        'penerimaan_dividen_kas',
        'pembayaran_jasa_pengelolaan',
        'pembayaran_jasa_kustodian',
        'pembayaran_beban_lain_arus',
        'kas_bersih_aktivitas_operasi',
        'penerimaan_penjualan_unit',
        'pembayaran_pembelian_kembali_unit',
        'kas_bersih_aktivitas_pendanaan',
        'kenaikan_kas_setara_kas',
        'kas_setara_kas_awal_tahun',
        'kas_setara_kas_akhir_tahun',
        'deposito_berjangka',
        'total_kas_setara_kas',
        'nilai_aset_bersih',
        'total_pendapatan',
        'total_beban',
        'laba_sebelum_pajak',
        'beban_pajak_penghasilan',
        'laba_bersih_tahun_berjalan',
        'penghasilan_komprehensif_lain_setelah_pajak',
        'penghasilan_komprehensif_tahun_berjalan',
        'kas',
        'tanggal_data',
        'ffs_bulan',
        'ffs_tahun',
        'jenis_laporan',
        'periode_awal',
        'periode_akhir',
        'tahun_laporan',
        'total_return',
        'biaya_operasi',
        'portfolio_turnover_ratio',
        'management_fee',
        'custodian_fee',
        'investment_manager_fee',
        'total_aset',
        'total_liabilitas',
        'kas_dan_bank',
        'piutang_bunga',
        'piutang_dividen',
        'piutang_lain',
        'utang_pajak',
        'utang_lain',
        'pendapatan_bunga',
        'pendapatan_dividen',
        'gain_realized',
        'gain_unrealized',
        'beban_mi',
        'beban_kustodian',
        'beban_lain',
        'laba_bersih',
        'arus_kas_operasi',
        'arus_kas_pendanaan',
        'kas_awal_tahun',
        'kas_akhir_tahun',
        'total_hasil_investasi',
        'hasil_investasi_setelah_biaya',
        'persentase_pph',
        'fair_value_level_1',
        'fair_value_level_2',
        'fair_value_level_3',
        'unit_milik_investor',
        'unit_milik_mi',
        'total_unit_beredar',
        'fee_cost_to_performance',
        'pendapatan_terhadap_nab',
        'beban_terhadap_pendapatan',
        'pengelolaan_investasi_terhadap_pendapatan',
        'transaction_profit_terhadap_nab',
        'status',
        'is_published',
        'published_at',
        'mode',
        'catatan_admin',
        'catatan',
        'ai_narasi',
        'ai_output',
        'ai_narasi_plus',
        'ai_output_plus',
        'data_tahunan',
        'pdf_path',
    ];

    protected $casts = [
        'ai_output'        => 'array',
        'ai_output_plus'   => 'array',
        'kategori'         => 'array',
        'data_tahunan'     => 'array',
        'tanggal_data'     => 'date',
        'tanggal_peluncuran' => 'date',
        'nab_per_unit'     => 'decimal:6',
        'is_published'     => 'boolean',
        'published_at'     => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reksaDana(): BelongsTo
    {
        return $this->belongsTo(ReksaDana::class);
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

    public function sukuk(): HasMany
    {
        return $this->hasMany(AnalisaSukuk::class, 'analisa_reksa_dana_id');
    }

    public function pasarUang(): HasMany
    {
        return $this->hasMany(AnalisaPasarUang::class, 'analisa_reksa_dana_id');
    }

    public function likuiditas(): HasMany
    {
        return $this->hasMany(AnalisaLikuiditas::class, 'analisa_reksa_dana_id');
    }

    public function keuangan(): HasMany
    {
        return $this->hasMany(AnalisaKeuangan::class, 'analisa_reksa_dana_id');
    }

    public function piutangBungaDetail(): HasMany
    {
        return $this->hasMany(AnalisaPiutangBunga::class, 'analisa_reksa_dana_id');
    }

    public function getDisplayMataUangAttribute(): string
    {
        return app(KodeReksaDanaParser::class)->resolveCurrencyName($this->mata_uang, (string) $this->kode_reksa_dana);
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
