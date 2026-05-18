<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Master data reksa dana
        Schema::create('reksa_dana', function (Blueprint $table) {
            $table->id();
            $table->string('nama_reksa_dana');
            $table->string('nama_manajer_investasi');
            $table->string('jenis'); // hanya satu item per reksa dana
            $table->json('kategori'); // bisa lebih dari satu
            $table->string('mata_uang')->default('IDR');
            $table->decimal('nab_per_unit', 20, 6)->nullable(); // NAB/UP
            $table->date('tanggal_nab')->nullable();            // Tanggal NAB/UP
            $table->timestamps();
        });

        // Harga harian reksa dana
        Schema::create('harga_reksa_dana', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reksa_dana_id')->constrained('reksa_dana')->cascadeOnDelete();
            $table->date('tanggal');
            $table->decimal('nab_per_unit', 20, 6);
            $table->timestamps();

            $table->unique(['reksa_dana_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harga_reksa_dana');
        Schema::dropIfExists('reksa_dana');
    }
};
