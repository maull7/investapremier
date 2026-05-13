<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel utama: satu upload = satu reksa dana yang dianalisa
        Schema::create('analisa_reksa_dana', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nama_reksa_dana');
            $table->string('jenis_reksa_dana'); // Saham, Pendapatan Tetap, Campuran, Pasar Uang
            $table->decimal('total_aum', 20, 2)->nullable();          // Total AUM
            $table->decimal('total_marcap_10_efek', 20, 2)->nullable(); // Total MarCap 10 Efek Terbesar
            $table->enum('status', ['draft', 'submitted', 'reviewed'])->default('draft');
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });

        // 1. Analisa per Sektor
        Schema::create('analisa_sektor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisa_reksa_dana_id')->constrained('analisa_reksa_dana')->cascadeOnDelete();
            $table->string('nama_sektor');
            $table->decimal('bobot', 8, 4); // % bobot dalam portofolio
            $table->timestamps();
        });

        // 2. Analisa per Efek (termasuk 10 efek terbesar + kontribusi kinerja)
        Schema::create('analisa_efek', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisa_reksa_dana_id')->constrained('analisa_reksa_dana')->cascadeOnDelete();
            $table->string('kode_efek');
            $table->string('nama_efek');
            $table->string('sektor')->nullable();
            $table->decimal('bobot', 8, 4);                    // % bobot dalam portofolio
            $table->decimal('kontribusi_kinerja', 10, 4)->nullable(); // positif/negatif
            $table->decimal('market_cap', 20, 2)->nullable();
            $table->boolean('top_10')->default(false);         // masuk 10 efek terbesar
            $table->timestamps();
        });

        // 3. Kinerja Bulanan (untuk hitung Return, Risiko, Sharpe, RAR)
        Schema::create('analisa_kinerja_bulanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisa_reksa_dana_id')->constrained('analisa_reksa_dana')->cascadeOnDelete();
            $table->date('periode');       // bulan/tahun
            $table->decimal('return_pct', 10, 4); // return bulanan dalam %
            $table->timestamps();
        });

        // 4. Analisa Obligasi (Durasi Risk + Rating Risk)
        Schema::create('analisa_obligasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisa_reksa_dana_id')->constrained('analisa_reksa_dana')->cascadeOnDelete();
            $table->string('kode_obligasi');
            $table->string('nama_obligasi');
            $table->decimal('bobot', 8, 4);          // % bobot dalam portofolio
            $table->decimal('durasi', 8, 4)->nullable(); // durasi dalam tahun
            $table->string('rating')->nullable();     // AAA, AA+, AA, A, BBB, dll
            $table->timestamps();
        });

        // 5. Analisa Bank (Bank Risk: CAR & NPL)
        Schema::create('analisa_bank', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisa_reksa_dana_id')->constrained('analisa_reksa_dana')->cascadeOnDelete();
            $table->string('nama_bank');
            $table->decimal('bobot', 8, 4);          // % bobot dalam portofolio
            $table->decimal('car', 8, 4)->nullable(); // Capital Adequacy Ratio (%)
            $table->decimal('npl', 8, 4)->nullable(); // Non-Performing Loan (%)
            $table->string('klasifikasi_risiko')->nullable(); // Rendah, Sedang, Tinggi
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisa_bank');
        Schema::dropIfExists('analisa_obligasi');
        Schema::dropIfExists('analisa_kinerja_bulanan');
        Schema::dropIfExists('analisa_efek');
        Schema::dropIfExists('analisa_sektor');
        Schema::dropIfExists('analisa_reksa_dana');
    }
};
