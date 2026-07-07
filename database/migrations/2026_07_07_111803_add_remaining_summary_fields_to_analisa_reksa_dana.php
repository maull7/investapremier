<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            $table->decimal('penghasilan_komprehensif_lain_setelah_pajak', 20, 2)->nullable()->after('laba_bersih_tahun_berjalan');
            $table->decimal('penghasilan_komprehensif_tahun_berjalan', 20, 2)->nullable()->after('penghasilan_komprehensif_lain_setelah_pajak');
            $table->decimal('kas', 20, 2)->nullable()->after('kas_dan_bank');
        });
    }

    public function down(): void
    {
        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            $table->dropColumn([
                'penghasilan_komprehensif_lain_setelah_pajak',
                'penghasilan_komprehensif_tahun_berjalan',
                'kas',
            ]);
        });
    }
};
