<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analisa_obligasi_keuangan', function (Blueprint $table) {
            $table->string('sektor')->nullable()->after('nama_emiten');
            $table->decimal('nominal_penerbit', 18, 2)->nullable()->after('sektor');
            $table->date('tanggal_terbit')->nullable()->after('nominal_penerbit');
            $table->date('tanggal_jatuh_tempo')->nullable()->after('tanggal_terbit');
            $table->boolean('tanpa_jaminan')->default(false)->after('tanggal_jatuh_tempo');
            $table->boolean('dengan_jaminan')->default(false)->after('tanpa_jaminan');
            $table->integer('periode_dari')->nullable()->after('dengan_jaminan');
            $table->integer('periode_sampai')->nullable()->after('periode_dari');
            $table->json('keuangan_saham_data')->nullable()->after('data_tahunan');
        });
    }

    public function down(): void
    {
        Schema::table('analisa_obligasi_keuangan', function (Blueprint $table) {
            $table->dropColumn(['sektor', 'nominal_penerbit', 'tanggal_terbit', 'tanggal_jatuh_tempo', 'tanpa_jaminan', 'dengan_jaminan', 'periode_dari', 'periode_sampai', 'keuangan_saham_data']);
        });
    }
};
