<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            // Data diri tambahan
            $table->string('agama')->nullable();
            $table->string('pekerjaan')->nullable();

            // Checkbox multi-pilih (disimpan sebagai JSON)
            $table->json('jenis_investasi')->nullable();   // Deposito, Reksa Dana, Saham, dll
            $table->json('sumber_dana')->nullable();        // Gaji, Tabungan, Warisan, dll
            $table->json('tujuan_investasi')->nullable();   // Pendidikan, Pensiun, dll
            $table->string('maksud_tujuan_lain')->nullable();

            // Pilihan tunggal
            $table->string('rata_rata_penghasilan')->nullable(); // < 50jt, 50-100jt, dll
            $table->string('pembukaan_rekening_efek')->nullable(); // Ya / Tidak

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });

        Schema::create('member_portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('jenis');          // Dana, Saham, Obligasi
            $table->string('nama_efek');
            $table->date('mulai_kepemilikan')->nullable();
            $table->decimal('jumlah', 18, 2)->nullable(); // lembar/unit
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_portfolios');
        Schema::dropIfExists('member_profiles');
    }
};
