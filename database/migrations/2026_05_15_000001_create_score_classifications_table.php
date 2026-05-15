<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('score_classifications', function (Blueprint $table) {
            $table->id();
            $table->string('profile_name');        // Conservative, Tolerant, Moderate, Risk Taker
            $table->integer('min_score');
            $table->integer('max_score');
            $table->integer('alloc_pasar_uang')->default(0);
            $table->integer('alloc_pendapatan_tetap')->default(0);
            $table->integer('alloc_campuran')->default(0);
            $table->integer('alloc_saham')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('score_classifications');
    }
};
