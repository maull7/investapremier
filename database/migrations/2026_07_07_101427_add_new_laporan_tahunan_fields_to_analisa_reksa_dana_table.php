<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            // Aset
            $table->decimal('portofolio_efek', 20, 2)->nullable()->after('total_marcap_10_efek');
            $table->decimal('instrumen_pasar_uang', 20, 2)->nullable()->after('portofolio_efek');
            $table->decimal('portofolio_efek_total', 20, 2)->nullable()->after('instrumen_pasar_uang');
            $table->decimal('piutang_transaksi_efek', 20, 2)->nullable()->after('piutang_lain');
            $table->decimal('piutang_bunga_dan_dividen', 20, 2)->nullable()->after('piutang_transaksi_efek');

            // Liabilitas
            $table->decimal('uang_muka_diterima', 20, 2)->nullable()->after('utang_lain');
            $table->decimal('liabilitas_pembelian_kembali', 20, 2)->nullable()->after('uang_muka_diterima');
            $table->decimal('beban_akrual', 20, 2)->nullable()->after('liabilitas_pembelian_kembali');
            $table->decimal('liabilitas_atas_biaya', 20, 2)->nullable()->after('beban_akrual');
            $table->decimal('pembelian_kembali_unit_penyertaan', 20, 2)->nullable()->after('liabilitas_atas_biaya');
            $table->decimal('utang_pajak_lainnya', 20, 2)->nullable()->after('pembelian_kembali_unit_penyertaan');

            // Pendapatan
            $table->decimal('pendapatan_investasi', 20, 2)->nullable()->after('pendapatan_dividen');
            $table->decimal('pendapatan_lainnya', 20, 2)->nullable()->after('gain_unrealized');

            // Beban
            $table->decimal('beban_investasi', 20, 2)->nullable()->after('beban_lain');
            $table->decimal('beban_pengelolaan_investasi', 20, 2)->nullable()->after('beban_investasi');

            // Arus Kas Operasi
            $table->decimal('pembelian_efek_ekuitas', 20, 2)->nullable()->after('arus_kas_operasi');
            $table->decimal('penjualan_efek_ekuitas', 20, 2)->nullable()->after('pembelian_efek_ekuitas');
            $table->decimal('penerimaan_bunga_deposito', 20, 2)->nullable()->after('penjualan_efek_ekuitas');
            $table->decimal('penerimaan_bunga_jasa_giro', 20, 2)->nullable()->after('penerimaan_bunga_deposito');
            $table->decimal('penerimaan_dividen_kas', 20, 2)->nullable()->after('penerimaan_bunga_jasa_giro');
            $table->decimal('pembayaran_jasa_pengelolaan', 20, 2)->nullable()->after('penerimaan_dividen_kas');
            $table->decimal('pembayaran_jasa_kustodian', 20, 2)->nullable()->after('pembayaran_jasa_pengelolaan');
            $table->decimal('pembayaran_beban_lain_arus', 20, 2)->nullable()->after('pembayaran_jasa_kustodian');
            $table->decimal('kas_bersih_aktivitas_operasi', 20, 2)->nullable()->after('pembayaran_beban_lain_arus');

            // Arus Kas Pendanaan
            $table->decimal('penerimaan_penjualan_unit', 20, 2)->nullable()->after('arus_kas_pendanaan');
            $table->decimal('pembayaran_pembelian_kembali_unit', 20, 2)->nullable()->after('penerimaan_penjualan_unit');
            $table->decimal('kas_bersih_aktivitas_pendanaan', 20, 2)->nullable()->after('pembayaran_pembelian_kembali_unit');
            $table->decimal('kenaikan_kas_setara_kas', 20, 2)->nullable()->after('kas_bersih_aktivitas_pendanaan');
            $table->decimal('kas_setara_kas_awal_tahun', 20, 2)->nullable()->after('kenaikan_kas_setara_kas');
            $table->decimal('kas_setara_kas_akhir_tahun', 20, 2)->nullable()->after('kas_setara_kas_awal_tahun');
            $table->decimal('deposito_berjangka', 20, 2)->nullable()->after('kas_setara_kas_akhir_tahun');
            $table->decimal('total_kas_setara_kas', 20, 2)->nullable()->after('deposito_berjangka');
        });
    }

    public function down(): void
    {
        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            $table->dropColumn([
                'portofolio_efek', 'instrumen_pasar_uang', 'portofolio_efek_total',
                'piutang_transaksi_efek', 'piutang_bunga_dan_dividen',
                'uang_muka_diterima', 'liabilitas_pembelian_kembali', 'beban_akrual',
                'liabilitas_atas_biaya', 'pembelian_kembali_unit_penyertaan', 'utang_pajak_lainnya',
                'pendapatan_investasi', 'pendapatan_lainnya',
                'beban_investasi', 'beban_pengelolaan_investasi',
                'pembelian_efek_ekuitas', 'penjualan_efek_ekuitas',
                'penerimaan_bunga_deposito', 'penerimaan_bunga_jasa_giro',
                'penerimaan_dividen_kas', 'pembayaran_jasa_pengelolaan',
                'pembayaran_jasa_kustodian', 'pembayaran_beban_lain_arus',
                'kas_bersih_aktivitas_operasi',
                'penerimaan_penjualan_unit', 'pembayaran_pembelian_kembali_unit',
                'kas_bersih_aktivitas_pendanaan', 'kenaikan_kas_setara_kas',
                'kas_setara_kas_awal_tahun', 'kas_setara_kas_akhir_tahun',
                'deposito_berjangka', 'total_kas_setara_kas',
            ]);
        });
    }
};
