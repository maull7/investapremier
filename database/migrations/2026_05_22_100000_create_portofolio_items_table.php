<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portofolio_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('perencanaan_investasi_id')->nullable();
            $table->foreign('perencanaan_investasi_id', 'fk_portofolio_perencanaan')
                ->references('id')->on('perencanaan_investasi')->cascadeOnDelete();
            $table->string('jenis');
            $table->string('produk_type')->nullable();
            $table->unsignedBigInteger('produk_id')->nullable();
            $table->string('nama_produk');
            $table->decimal('nominal', 20, 2)->default(0);
            $table->decimal('harga_akuisisi', 20, 2)->default(0);
            $table->decimal('nilai', 20, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portofolio_items');
    }
};
