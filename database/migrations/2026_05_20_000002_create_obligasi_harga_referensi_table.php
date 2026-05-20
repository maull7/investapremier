<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obligasi_harga_referensi', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique()->index();
            $table->string('nama')->nullable();
            $table->date('tanggal_terbit')->nullable();
            $table->string('emiten')->nullable();
            $table->string('sektor')->nullable();
            $table->string('sub_sektor')->nullable();
            $table->string('industri')->nullable();
            $table->string('sub_industri')->nullable();
            $table->string('denominasi')->nullable();
            $table->string('rating')->nullable();
            $table->boolean('syariah')->nullable();
            $table->decimal('kupon', 10, 4)->nullable();
            $table->date('jatuh_tempo')->nullable();
            $table->decimal('harga_persen', 10, 4)->nullable();
            $table->decimal('ttm', 12, 6)->nullable();
            $table->decimal('ytm', 12, 6)->nullable();
            $table->decimal('current_yield', 10, 4)->nullable();
            $table->decimal('total_val', 22, 2)->nullable();
            $table->decimal('outstanding_amount', 24, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obligasi_harga_referensi');
    }
};
