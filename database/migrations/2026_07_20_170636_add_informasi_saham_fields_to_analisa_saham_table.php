<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analisa_saham', function (Blueprint $table) {
            $table->string('nama_saham')->nullable()->after('nama_perusahaan');
            $table->decimal('jumlah_lembar_saham', 18, 2)->nullable()->after('nama_saham');
            $table->decimal('harga_saham', 18, 2)->nullable()->after('jumlah_lembar_saham');
            $table->decimal('q1_saham', 18, 2)->nullable()->after('harga_saham');
            $table->decimal('q2_saham', 18, 2)->nullable()->after('q1_saham');
            $table->decimal('q3_saham', 18, 2)->nullable()->after('q2_saham');
            $table->decimal('q4_saham', 18, 2)->nullable()->after('q3_saham');
            $table->decimal('kapitalisasi_pasar', 18, 2)->nullable()->after('q4_saham');
        });
    }

    public function down(): void
    {
        Schema::table('analisa_saham', function (Blueprint $table) {
            $table->dropColumn(['nama_saham', 'jumlah_lembar_saham', 'harga_saham', 'q1_saham', 'q2_saham', 'q3_saham', 'q4_saham', 'kapitalisasi_pasar']);
        });
    }
};
