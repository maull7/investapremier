<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analisa_saham', function (Blueprint $table) {
            $table->json('portofolio_data')->nullable()->after('keuangan_data');
        });
    }

    public function down(): void
    {
        Schema::table('analisa_saham', function (Blueprint $table) {
            $table->dropColumn('portofolio_data');
        });
    }
};
