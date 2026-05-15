<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_prices', function (Blueprint $table) {
            $table->id();
            $table->string('kode_efek')->index();       // Kode saham/efek, misal: BBCA, TLKM
            $table->string('nama_efek')->nullable();     // Nama lengkap efek
            $table->string('jenis')->nullable();         // Saham, Obligasi, Reksa Dana
            $table->decimal('harga', 18, 2);             // Harga penutupan T-1
            $table->date('tanggal');                     // Tanggal harga (T-1)
            $table->string('sumber')->nullable();        // Sumber data (IDX, Yahoo, dll)
            $table->timestamps();

            $table->unique(['kode_efek', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_prices');
    }
};
