<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            if (!Schema::hasColumn('analisa_reksa_dana', 'kode_reksa_dana')) {
                $table->string('kode_reksa_dana', 20)->nullable()->after('product_type');
            }
            if (!Schema::hasColumn('analisa_reksa_dana', 'benchmark')) {
                $table->string('benchmark')->nullable()->after('kategori');
            }
            if (!Schema::hasColumn('analisa_reksa_dana', 'tujuan_investasi')) {
                $table->text('tujuan_investasi')->nullable()->after('benchmark');
            }
            if (!Schema::hasColumn('analisa_reksa_dana', 'kebijakan_investasi')) {
                $table->text('kebijakan_investasi')->nullable()->after('tujuan_investasi');
            }
            if (!Schema::hasColumn('analisa_reksa_dana', 'ffs_bulan')) {
                $table->unsignedTinyInteger('ffs_bulan')->nullable()->after('tanggal_data');
            }
            if (!Schema::hasColumn('analisa_reksa_dana', 'ffs_tahun')) {
                $table->unsignedSmallInteger('ffs_tahun')->nullable()->after('ffs_bulan');
            }
            if (!Schema::hasColumn('analisa_reksa_dana', 'nab_per_unit')) {
                $table->decimal('nab_per_unit', 20, 6)->nullable()->after('unit_penyertaan');
            }
        });

        Schema::table('reksa_dana', function (Blueprint $table) {
            if (!Schema::hasColumn('reksa_dana', 'benchmark')) {
                $table->string('benchmark')->nullable()->after('kategori_produk');
            }
            if (!Schema::hasColumn('reksa_dana', 'tujuan_investasi')) {
                $table->text('tujuan_investasi')->nullable()->after('benchmark');
            }
            if (!Schema::hasColumn('reksa_dana', 'kebijakan_investasi')) {
                $table->text('kebijakan_investasi')->nullable()->after('tujuan_investasi');
            }
        });

        if (!Schema::hasTable('analisa_alokasi_aset')) {
            Schema::create('analisa_alokasi_aset', function (Blueprint $table) {
                $table->id();
                $table->foreignId('analisa_reksa_dana_id')->constrained('analisa_reksa_dana')->cascadeOnDelete();
                $table->string('nama_aset');
                $table->decimal('persentase', 8, 4);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('analisa_alokasi_aset');

        Schema::table('reksa_dana', function (Blueprint $table) {
            $columns = array_values(array_filter(
                ['benchmark', 'tujuan_investasi', 'kebijakan_investasi'],
                fn ($column) => Schema::hasColumn('reksa_dana', $column)
            ));
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            $columns = array_values(array_filter([
                'kode_reksa_dana',
                'benchmark',
                'tujuan_investasi',
                'kebijakan_investasi',
                'ffs_bulan',
                'ffs_tahun',
                'nab_per_unit',
            ], fn ($column) => Schema::hasColumn('analisa_reksa_dana', $column)));
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
