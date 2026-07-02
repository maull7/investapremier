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
        Schema::create('analisa_likuiditas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisa_reksa_dana_id')->constrained('analisa_reksa_dana')->cascadeOnDelete();
            $table->string('kategori');
            $table->string('kode_efek');
            $table->string('nama_efek');
            $table->decimal('rata_volume_transaksi_harian', 20, 4)->nullable();
            $table->decimal('volume_terendah', 20, 4)->nullable();
            $table->decimal('volume_saham', 20, 4)->nullable();
            $table->decimal('skenario_20_persen_reds', 20, 4)->nullable();
            $table->decimal('skenario_reds_closing_10', 20, 4)->nullable();
            $table->decimal('rasio_likuiditas_harian', 20, 4)->nullable();
            $table->decimal('rasio_likuiditas', 20, 4)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisa_likuiditas');
    }
};
