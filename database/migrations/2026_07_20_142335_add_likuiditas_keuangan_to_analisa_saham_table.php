<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analisa_saham', function (Blueprint $table) {
            $table->json('likuiditas_data')->nullable()->after('catatan');
            $table->json('keuangan_data')->nullable()->after('likuiditas_data');
        });
    }

    public function down(): void
    {
        Schema::table('analisa_saham', function (Blueprint $table) {
            $table->dropColumn(['likuiditas_data', 'keuangan_data']);
        });
    }
};
