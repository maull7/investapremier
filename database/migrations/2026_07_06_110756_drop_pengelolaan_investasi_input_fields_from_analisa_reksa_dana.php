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
            $table->dropColumn([
                'hasil_penjualan',
                'pembelian',
                'pembelian_efek',
                'penjualan',
                'total_pendapatan',
                'total_beban',
                'subs_reds_to_transaction',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            $table->decimal('hasil_penjualan', 20, 4)->nullable()->after('total_unit_beredar');
            $table->decimal('pembelian', 20, 4)->nullable()->after('hasil_penjualan');
            $table->decimal('pembelian_efek', 20, 4)->nullable()->after('pembelian');
            $table->decimal('penjualan', 20, 4)->nullable()->after('pembelian_efek');
            $table->decimal('total_pendapatan', 20, 4)->nullable()->after('penjualan');
            $table->decimal('total_beban', 20, 4)->nullable()->after('total_pendapatan');
            $table->decimal('subs_reds_to_transaction', 20, 4)->nullable()->after('total_beban');
        });
    }
};
