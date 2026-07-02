<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('analisa_keuangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisa_reksa_dana_id')->constrained('analisa_reksa_dana')->cascadeOnDelete();
            $table->string('kategori');
            $table->string('kode_efek');
            $table->string('nama_efek');
            // Saham + Obligasi shared
            $table->decimal('der', 20, 4)->nullable();
            $table->decimal('current_ratio', 20, 4)->nullable();
            $table->decimal('gross_profit_margin', 20, 4)->nullable();
            $table->decimal('operating_profit_margin', 20, 4)->nullable();
            // Saham
            $table->decimal('per', 20, 4)->nullable();
            $table->decimal('pbv', 20, 4)->nullable();
            $table->decimal('roe', 20, 4)->nullable();
            $table->decimal('roa', 20, 4)->nullable();
            $table->decimal('npm', 20, 4)->nullable();
            $table->decimal('ev_ebitda', 20, 4)->nullable();
            // Obligasi
            $table->decimal('ytm', 20, 4)->nullable();
            $table->string('rating')->nullable();
            $table->decimal('kupon', 20, 4)->nullable();
            $table->decimal('tenor', 20, 4)->nullable();
            $table->decimal('durasi', 20, 4)->nullable();
            $table->string('shadow_rating')->nullable();
            // Bank
            $table->decimal('npl', 20, 4)->nullable();
            $table->decimal('car', 20, 4)->nullable();
            $table->decimal('ldr', 20, 4)->nullable();
            $table->decimal('nim', 20, 4)->nullable();
            $table->decimal('cir', 20, 4)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisa_keuangan');
    }
};
