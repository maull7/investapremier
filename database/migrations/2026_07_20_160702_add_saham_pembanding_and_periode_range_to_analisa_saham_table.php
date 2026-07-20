<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analisa_saham', function (Blueprint $table) {
            $table->integer('periode_dari')->nullable()->after('periode');
            $table->integer('periode_sampai')->nullable()->after('periode_dari');
            $table->json('saham_pembanding_data')->nullable()->after('portofolio_data');
        });
    }

    public function down(): void
    {
        Schema::table('analisa_saham', function (Blueprint $table) {
            $table->dropColumn(['periode_dari', 'periode_sampai', 'saham_pembanding_data']);
        });
    }
};
