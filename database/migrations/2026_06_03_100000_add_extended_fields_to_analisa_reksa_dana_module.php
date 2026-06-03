<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── analisa_reksa_dana ────────────────────────────────────────────────
        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            // Informasi Umum
            $table->string('manajer_investasi', 255)->nullable()->after('benchmark');
            $table->string('bank_kustodian', 255)->nullable()->after('manajer_investasi');
            $table->date('tanggal_peluncuran')->nullable()->after('bank_kustodian');

            // Kinerja
            $table->decimal('return_ytd', 10, 4)->nullable()->after('return_1m');
            $table->decimal('return_1y', 10, 4)->nullable()->after('return_ytd');

            // Rasio Keuangan
            $table->decimal('total_return', 10, 4)->nullable();
            $table->decimal('biaya_operasi', 10, 4)->nullable();
            $table->decimal('portfolio_turnover_ratio', 10, 4)->nullable();

            // Biaya
            $table->decimal('management_fee', 10, 4)->nullable();
            $table->decimal('custodian_fee', 10, 4)->nullable();

            // Laporan Keuangan - Neraca
            $table->decimal('total_aset', 20, 4)->nullable();
            $table->decimal('total_liabilitas', 20, 4)->nullable();
            $table->decimal('kas_dan_bank', 20, 4)->nullable();
            $table->decimal('piutang_bunga', 20, 4)->nullable();
            $table->decimal('piutang_dividen', 20, 4)->nullable();
            $table->decimal('piutang_lain', 20, 4)->nullable();
            $table->decimal('utang_pajak', 20, 4)->nullable();
            $table->decimal('utang_lain', 20, 4)->nullable();

            // Laporan Keuangan - Laba Rugi
            $table->decimal('pendapatan_bunga', 20, 4)->nullable();
            $table->decimal('pendapatan_dividen', 20, 4)->nullable();
            $table->decimal('gain_realized', 20, 4)->nullable();
            $table->decimal('gain_unrealized', 20, 4)->nullable();
            $table->decimal('beban_mi', 20, 4)->nullable();
            $table->decimal('beban_kustodian', 20, 4)->nullable();
            $table->decimal('beban_lain', 20, 4)->nullable();
            $table->decimal('laba_bersih', 20, 4)->nullable();

            // Laporan Keuangan - Arus Kas
            $table->decimal('arus_kas_operasi', 20, 4)->nullable();
            $table->decimal('arus_kas_pendanaan', 20, 4)->nullable();
            $table->decimal('kas_awal_tahun', 20, 4)->nullable();
            $table->decimal('kas_akhir_tahun', 20, 4)->nullable();

            // Rasio Keuangan Lengkap
            $table->decimal('total_hasil_investasi', 10, 4)->nullable();
            $table->decimal('hasil_investasi_setelah_biaya', 10, 4)->nullable();
            $table->decimal('persentase_pph', 10, 4)->nullable();

            // Fair Value
            $table->decimal('fair_value_level_1', 20, 4)->nullable();
            $table->decimal('fair_value_level_2', 20, 4)->nullable();
            $table->decimal('fair_value_level_3', 20, 4)->nullable();

            // Unit Penyertaan Detail
            $table->decimal('unit_milik_investor', 20, 4)->nullable();
            $table->decimal('unit_milik_mi', 20, 4)->nullable();
            $table->decimal('total_unit_beredar', 20, 4)->nullable();
        });

        // ── analisa_efek ─────────────────────────────────────────────────────
        Schema::table('analisa_efek', function (Blueprint $table) {
            $table->decimal('harga_perolehan', 20, 4)->nullable()->after('market_cap');
            $table->decimal('persen_nab', 10, 4)->nullable()->after('harga_perolehan');
        });

        // ── analisa_obligasi ─────────────────────────────────────────────────
        Schema::table('analisa_obligasi', function (Blueprint $table) {
            $table->decimal('ytm', 10, 4)->nullable()->after('rating');
            $table->decimal('kupon', 10, 4)->nullable()->after('ytm');
            $table->date('tanggal_jatuh_tempo')->nullable()->after('kupon');
            $table->string('penerbit', 255)->nullable()->after('tanggal_jatuh_tempo');
            $table->decimal('persen_nab', 10, 4)->nullable()->after('penerbit');
        });

        // ── analisa_reksadana_sukuk ──────────────────────────────────────────
        Schema::table('analisa_reksadana_sukuk', function (Blueprint $table) {
            $table->decimal('persen_nab', 10, 4)->nullable()->after('rating');
        });

        // ── analisa_bank ─────────────────────────────────────────────────────
        Schema::table('analisa_bank', function (Blueprint $table) {
            $table->decimal('tingkat_bunga', 10, 4)->nullable()->after('npl');
            $table->string('jangka_waktu', 100)->nullable()->after('tingkat_bunga');
            $table->decimal('persen_nab', 10, 4)->nullable()->after('jangka_waktu');
        });
    }

    public function down(): void
    {
        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            $table->dropColumn([
                'manajer_investasi', 'bank_kustodian', 'tanggal_peluncuran',
                'return_ytd', 'return_1y', 'total_return', 'biaya_operasi', 'portfolio_turnover_ratio',
                'management_fee', 'custodian_fee',
                'total_aset', 'total_liabilitas', 'kas_dan_bank', 'piutang_bunga',
                'piutang_dividen', 'piutang_lain', 'utang_pajak', 'utang_lain',
                'pendapatan_bunga', 'pendapatan_dividen', 'gain_realized', 'gain_unrealized',
                'beban_mi', 'beban_kustodian', 'beban_lain', 'laba_bersih',
                'arus_kas_operasi', 'arus_kas_pendanaan', 'kas_awal_tahun', 'kas_akhir_tahun',
                'total_hasil_investasi', 'hasil_investasi_setelah_biaya', 'persentase_pph',
                'fair_value_level_1', 'fair_value_level_2', 'fair_value_level_3',
                'unit_milik_investor', 'unit_milik_mi', 'total_unit_beredar',
            ]);
        });
        Schema::table('analisa_efek', function (Blueprint $table) {
            $table->dropColumn(['harga_perolehan', 'persen_nab']);
        });
        Schema::table('analisa_obligasi', function (Blueprint $table) {
            $table->dropColumn(['ytm', 'kupon', 'tanggal_jatuh_tempo', 'penerbit', 'persen_nab']);
        });
        Schema::table('analisa_reksadana_sukuk', function (Blueprint $table) {
            $table->dropColumn('persen_nab');
        });
        Schema::table('analisa_bank', function (Blueprint $table) {
            $table->dropColumn(['tingkat_bunga', 'jangka_waktu', 'persen_nab']);
        });
    }
};
