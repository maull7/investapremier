<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analisa_efek', function (Blueprint $table) {
            $table->decimal('nilai_pasar', 20, 2)->nullable()->after('market_cap');
            $table->decimal('return_1m', 10, 4)->nullable()->after('nilai_pasar');
            $table->decimal('return_3m', 10, 4)->nullable()->after('return_1m');
            $table->decimal('return_6m', 10, 4)->nullable()->after('return_3m');
            $table->decimal('return_1y', 10, 4)->nullable()->after('return_6m');
            $table->decimal('ihsg_contribution', 10, 4)->nullable()->after('return_1y');
            $table->string('effect_type', 50)->nullable()->after('ihsg_contribution');
        });

        Schema::table('analisa_obligasi', function (Blueprint $table) {
            $table->decimal('nilai_pasar', 20, 2)->nullable()->after('rating');
            $table->decimal('return_1m', 10, 4)->nullable()->after('nilai_pasar');
            $table->decimal('return_3m', 10, 4)->nullable()->after('return_1m');
            $table->decimal('return_6m', 10, 4)->nullable()->after('return_3m');
            $table->decimal('return_1y', 10, 4)->nullable()->after('return_6m');
        });

        Schema::table('analisa_bank', function (Blueprint $table) {
            $table->string('jenis_bank', 50)->nullable()->after('nama_bank');
            $table->decimal('nilai_pasar', 20, 2)->nullable()->after('jenis_bank');
            $table->decimal('return_1m', 10, 4)->nullable()->after('nilai_pasar');
            $table->decimal('return_3m', 10, 4)->nullable()->after('return_1m');
            $table->decimal('return_6m', 10, 4)->nullable()->after('return_3m');
            $table->decimal('return_1y', 10, 4)->nullable()->after('return_6m');
        });

        Schema::create('effect_sectors', function (Blueprint $table) {
            $table->id();
            $table->string('effect_code', 20)->index();
            $table->string('effect_name')->nullable();
            $table->string('sector_name')->nullable();
            $table->string('effect_type', 50)->nullable();
            $table->string('source', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('analisa_efek', function (Blueprint $table) {
            $table->dropColumn([
                'nilai_pasar', 'return_1m', 'return_3m', 'return_6m', 'return_1y',
                'ihsg_contribution', 'effect_type',
            ]);
        });

        Schema::table('analisa_obligasi', function (Blueprint $table) {
            $table->dropColumn([
                'nilai_pasar', 'return_1m', 'return_3m', 'return_6m', 'return_1y',
            ]);
        });

        Schema::table('analisa_bank', function (Blueprint $table) {
            $table->dropColumn([
                'jenis_bank', 'nilai_pasar', 'return_1m', 'return_3m', 'return_6m', 'return_1y',
            ]);
        });

        Schema::dropIfExists('effect_sectors');
    }
};
