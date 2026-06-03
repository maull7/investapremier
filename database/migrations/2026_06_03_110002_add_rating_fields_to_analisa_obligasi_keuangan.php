<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analisa_obligasi_keuangan', function (Blueprint $table) {
            $table->string('official_rating', 20)->nullable()->after('rating');
            $table->string('shadow_rating', 20)->nullable()->after('official_rating');
            $table->decimal('shadow_score', 10, 4)->nullable()->after('shadow_rating');
            $table->decimal('shadow_confidence', 10, 4)->nullable()->after('shadow_score');
            $table->decimal('ytm_normal', 10, 4)->nullable()->after('ytm');
            $table->decimal('ytm_spread', 10, 4)->nullable()->after('ytm_normal');
            $table->string('rating_source', 50)->nullable()->after('shadow_confidence');
            $table->integer('tenor_bulan')->nullable()->after('rating_source');
        });
    }

    public function down(): void
    {
        Schema::table('analisa_obligasi_keuangan', function (Blueprint $table) {
            $table->dropColumn([
                'official_rating',
                'shadow_rating',
                'shadow_score',
                'shadow_confidence',
                'ytm_normal',
                'ytm_spread',
                'rating_source',
                'tenor_bulan',
            ]);
        });
    }
};
