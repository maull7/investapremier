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
            $table->decimal('hasil_penjualan', 20, 4)->nullable()->after('total_unit_beredar');
            $table->decimal('pembelian', 20, 4)->nullable()->after('hasil_penjualan');
            $table->decimal('pembelian_efek', 20, 4)->nullable()->after('pembelian');
            $table->decimal('penjualan', 20, 4)->nullable()->after('pembelian_efek');
            $table->decimal('total_pendapatan', 20, 4)->nullable()->after('penjualan');
            $table->decimal('total_beban', 20, 4)->nullable()->after('total_pendapatan');
            $table->decimal('fee_cost_to_performance', 20, 4)->nullable()->after('total_beban');
            $table->decimal('subs_reds_to_transaction', 20, 4)->nullable()->after('fee_cost_to_performance');
            $table->decimal('pendapatan_terhadap_nab', 20, 4)->nullable()->after('subs_reds_to_transaction');
            $table->decimal('beban_terhadap_pendapatan', 20, 4)->nullable()->after('pendapatan_terhadap_nab');
            $table->decimal('pengelolaan_investasi_terhadap_pendapatan', 20, 4)->nullable()->after('beban_terhadap_pendapatan');
            $table->decimal('transaction_profit_terhadap_nab', 20, 4)->nullable()->after('pengelolaan_investasi_terhadap_pendapatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            $table->dropColumn([
                'hasil_penjualan',
                'pembelian',
                'pembelian_efek',
                'penjualan',
                'total_pendapatan',
                'total_beban',
                'fee_cost_to_performance',
                'subs_reds_to_transaction',
                'pendapatan_terhadap_nab',
                'beban_terhadap_pendapatan',
                'pengelolaan_investasi_terhadap_pendapatan',
                'transaction_profit_terhadap_nab',
            ]);
        });
    }
};
