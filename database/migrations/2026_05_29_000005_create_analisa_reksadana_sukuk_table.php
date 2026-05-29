<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analisa_reksadana_sukuk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisa_reksa_dana_id')->constrained('analisa_reksa_dana')->cascadeOnDelete();
            $table->string('kode_sukuk');
            $table->string('nama_sukuk');
            $table->string('jenis_sukuk')->nullable(); // Negara, Korporasi
            $table->decimal('bobot', 8, 4);              // % bobot dalam portofolio
            $table->decimal('yield', 8, 4)->nullable();  // yield/imbal hasil
            $table->string('jatuh_tempo')->nullable();   // tahun jatuh tempo (e.g. 2028)
            $table->string('rating')->nullable();        // AAA, AA+, AA, dll
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisa_reksadana_sukuk');
    }
};
