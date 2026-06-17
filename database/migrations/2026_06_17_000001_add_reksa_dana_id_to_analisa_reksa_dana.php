<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            $table->foreignId('reksa_dana_id')
                ->nullable()
                ->after('product_type')
                ->constrained('reksa_dana')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            $table->dropForeign(['reksa_dana_id']);
            $table->dropColumn('reksa_dana_id');
        });
    }
};
