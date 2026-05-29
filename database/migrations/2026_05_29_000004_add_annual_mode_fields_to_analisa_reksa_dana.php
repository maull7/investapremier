<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            $table->string('jenis_laporan', 30)->default('kalender_ffs')->after('ffs_tahun');
            $table->string('periode_awal', 6)->nullable()->after('jenis_laporan');
            $table->string('periode_akhir', 6)->nullable()->after('periode_awal');
            $table->unsignedSmallInteger('tahun_laporan')->nullable()->after('periode_akhir');
        });
    }

    public function down(): void
    {
        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            $table->dropColumn(['jenis_laporan', 'periode_awal', 'periode_akhir', 'tahun_laporan']);
        });
    }
};
