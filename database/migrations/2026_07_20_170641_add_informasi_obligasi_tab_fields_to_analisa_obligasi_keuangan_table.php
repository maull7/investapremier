<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analisa_obligasi_keuangan', function (Blueprint $table) {
            $table->string('info_nama_obligasi')->nullable()->after('nama_obligasi');
            $table->decimal('info_ytm', 8, 4)->nullable()->after('info_nama_obligasi');
            $table->decimal('harga_obligasi', 18, 2)->nullable()->after('info_ytm');
            $table->decimal('q1_obligasi', 18, 2)->nullable()->after('harga_obligasi');
            $table->decimal('q2_obligasi', 18, 2)->nullable()->after('q1_obligasi');
            $table->decimal('q3_obligasi', 18, 2)->nullable()->after('q2_obligasi');
            $table->decimal('q4_obligasi', 18, 2)->nullable()->after('q3_obligasi');
            $table->decimal('info_nominal_penerbitan', 18, 2)->nullable()->after('q4_obligasi');
        });
    }

    public function down(): void
    {
        Schema::table('analisa_obligasi_keuangan', function (Blueprint $table) {
            $table->dropColumn(['info_nama_obligasi', 'info_ytm', 'harga_obligasi', 'q1_obligasi', 'q2_obligasi', 'q3_obligasi', 'q4_obligasi', 'info_nominal_penerbitan']);
        });
    }
};
