<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perencanaan_investasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('kategori_perencanaan');
            $table->decimal('kebutuhan_dana', 20, 2)->nullable();
            $table->integer('target_waktu_tahun')->nullable();
            $table->decimal('dana_tersedia', 20, 2)->nullable();
            $table->decimal('investasi_per_bulan', 20, 2)->nullable();
            $table->text('sumber_dana')->nullable();
            $table->string('profil_risiko')->nullable();
            $table->string('usia_anak')->nullable();
            $table->string('target_pendidikan')->nullable();
            $table->string('tipe_pendidikan')->nullable();
            $table->string('lokasi_pendidikan')->nullable();
            $table->decimal('estimasi_biaya_saat_ini', 20, 2)->nullable();
            $table->decimal('pemenuhan_dana', 20, 2)->nullable();
            $table->string('status')->default('Aktif');
            $table->text('ai_narasi')->nullable();
            $table->json('ai_output')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perencanaan_investasi');
    }
};
