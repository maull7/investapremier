<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reksa_dana', function (Blueprint $table) {
            $table->string('kelas', 10)->nullable()->after('kategori_produk');
        });
    }

    public function down(): void
    {
        Schema::table('reksa_dana', function (Blueprint $table) {
            $table->dropColumn('kelas');
        });
    }
};
