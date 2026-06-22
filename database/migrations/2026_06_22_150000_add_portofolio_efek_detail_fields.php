<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Efek Utang - tambah nilai_nominal, harga_perolehan_rata_rata, suku_bunga
        Schema::table('analisa_obligasi', function (Blueprint $table) {
            $table->decimal('nilai_nominal', 20, 2)->nullable()->after('persen_nab');
            $table->decimal('harga_perolehan_rata_rata', 10, 4)->nullable()->after('nilai_nominal'); // dalam %
            $table->decimal('suku_bunga', 10, 4)->nullable()->after('harga_perolehan_rata_rata'); // per annum %
        });

        // Efek Ekuitas - tambah jumlah_lembar, harga_perolehan_rata_rata
        Schema::table('analisa_efek', function (Blueprint $table) {
            $table->bigInteger('jumlah_lembar')->nullable()->after('persen_nab');
            $table->decimal('harga_perolehan_rata_rata', 20, 2)->nullable()->after('jumlah_lembar'); // Rp per lembar
        });

        // Sukuk - tambah nilai_nominal, harga_perolehan_rata_rata, nilai_wajar, tingkat_bagi_hasil
        Schema::table('analisa_reksadana_sukuk', function (Blueprint $table) {
            $table->decimal('nilai_nominal', 20, 2)->nullable()->after('persen_nab');
            $table->decimal('harga_perolehan_rata_rata', 10, 4)->nullable()->after('nilai_nominal'); // dalam %
            $table->decimal('nilai_wajar', 20, 2)->nullable()->after('harga_perolehan_rata_rata');
            $table->decimal('tingkat_bagi_hasil', 10, 4)->nullable()->after('nilai_wajar'); // %
        });

        // Bank/Kas - tambah saldo
        Schema::table('analisa_bank', function (Blueprint $table) {
            $table->decimal('saldo', 20, 2)->nullable()->after('persen_nab');
        });

        // Tabel baru: Instrumen Pasar Uang
        Schema::create('analisa_reksadana_pasar_uang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisa_reksa_dana_id')->constrained('analisa_reksa_dana')->cascadeOnDelete();
            $table->string('nama_instrumen');
            $table->string('jenis_instrumen')->nullable(); // Deposito berjangka, dll
            $table->decimal('nilai_tercatat', 20, 2)->nullable();
            $table->decimal('suku_bunga', 10, 4)->nullable(); // per annum %
            $table->date('jatuh_tempo')->nullable();
            $table->decimal('persen_nab', 10, 4)->nullable();
            $table->timestamps();
        });

        // Tabel baru: Detail Piutang Bunga per instrumen
        Schema::create('analisa_reksadana_piutang_bunga', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisa_reksa_dana_id')->constrained('analisa_reksa_dana')->cascadeOnDelete();
            $table->string('jenis_instrumen'); // Efek utang, Sukuk, Kas di bank, Instrumen pasar uang
            $table->decimal('jumlah', 20, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisa_reksadana_piutang_bunga');
        Schema::dropIfExists('analisa_reksadana_pasar_uang');

        Schema::table('analisa_bank', function (Blueprint $table) {
            $table->dropColumn('saldo');
        });
        Schema::table('analisa_reksadana_sukuk', function (Blueprint $table) {
            $table->dropColumn(['nilai_nominal', 'harga_perolehan_rata_rata', 'nilai_wajar', 'tingkat_bagi_hasil']);
        });
        Schema::table('analisa_efek', function (Blueprint $table) {
            $table->dropColumn(['jumlah_lembar', 'harga_perolehan_rata_rata']);
        });
        Schema::table('analisa_obligasi', function (Blueprint $table) {
            $table->dropColumn(['nilai_nominal', 'harga_perolehan_rata_rata', 'suku_bunga']);
        });
    }
};
