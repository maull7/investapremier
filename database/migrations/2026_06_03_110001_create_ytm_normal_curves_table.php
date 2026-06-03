<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ytm_normal_curves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rating_id')->constrained('rating_obligasi')->cascadeOnDelete();
            $table->integer('tenor_bulan');
            $table->decimal('ytm_normal', 10, 4);
            $table->timestamps();

            $table->unique(['rating_id', 'tenor_bulan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ytm_normal_curves');
    }
};
