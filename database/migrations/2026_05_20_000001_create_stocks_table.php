<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique()->index();
            $table->string('nama');
            $table->string('sektor')->nullable();
            $table->string('sub_industri')->nullable();
            $table->decimal('harga_terbaru', 18, 2)->nullable();
            $table->decimal('harga_penutupan_sebelumnya', 18, 2)->nullable();
            $table->decimal('harga_pembukaan', 18, 2)->nullable();
            $table->decimal('harga_tertinggi', 18, 2)->nullable();
            $table->decimal('harga_terendah', 18, 2)->nullable();
            $table->bigInteger('volume')->nullable();
            $table->decimal('value', 22, 2)->nullable();
            $table->bigInteger('frekuensi')->nullable();
            $table->bigInteger('jumlah_saham')->nullable();
            $table->decimal('market_capital', 32, 2)->nullable();
            $table->date('last_update')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
