<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            $table->decimal('nilai_aset_bersih', 20, 2)->nullable()->after('total_liabilitas');
            $table->decimal('total_pendapatan', 20, 2)->nullable()->after('pendapatan_lainnya');
            $table->decimal('total_beban', 20, 2)->nullable()->after('beban_pengelolaan_investasi');
            $table->decimal('laba_sebelum_pajak', 20, 2)->nullable()->after('total_beban');
            $table->decimal('beban_pajak_penghasilan', 20, 2)->nullable()->after('laba_sebelum_pajak');
            $table->decimal('laba_bersih_tahun_berjalan', 20, 2)->nullable()->after('beban_pajak_penghasilan');
            $table->decimal('penghasilan_komprehensif_lain_setelah_pajak', 20, 2)->nullable()->after('laba_bersih_tahun_berjalan');
            $table->decimal('penghasilan_komprehensif_tahun_berjalan', 20, 2)->nullable()->after('penghasilan_komprehensif_lain_setelah_pajak');
            $table->decimal('kas', 20, 2)->nullable()->after('kas_dan_bank');
        });
    }

    public function down(): void
    {
        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            $table->dropColumn([
                'nilai_aset_bersih',
                'total_pendapatan',
                'total_beban',
                'laba_sebelum_pajak',
                'beban_pajak_penghasilan',
                'laba_bersih_tahun_berjalan',
                'penghasilan_komprehensif_lain_setelah_pajak',
                'penghasilan_komprehensif_tahun_berjalan',
                'kas',
            ]);
        });
    }
};
