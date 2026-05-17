<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            $table->longText('ai_narasi_plus')->nullable()->after('ai_output');
            $table->json('ai_output_plus')->nullable()->after('ai_narasi_plus');
        });
    }

    public function down(): void
    {
        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            $table->dropColumn(['ai_narasi_plus', 'ai_output_plus']);
        });
    }
};
