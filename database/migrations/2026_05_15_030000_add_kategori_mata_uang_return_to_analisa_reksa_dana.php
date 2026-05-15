<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            $table->string('kategori')->nullable()->after('jenis_reksa_dana');
            $table->string('mata_uang', 10)->default('IDR')->after('kategori');
            $table->decimal('return_1m', 10, 4)->nullable()->after('total_aum');
        });
    }

    public function down(): void
    {
        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            $table->dropColumn(['kategori', 'mata_uang', 'return_1m']);
        });
    }
};
