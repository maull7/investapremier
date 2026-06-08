<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_prices', function (Blueprint $table) {
            $table->dropUnique(['kode_efek', 'tanggal']);
            $table->unique(['kode_efek', 'tanggal', 'sumber'], 'stock_prices_code_date_source_unique');
        });
    }

    public function down(): void
    {
        Schema::table('stock_prices', function (Blueprint $table) {
            $table->dropUnique('stock_prices_code_date_source_unique');
            $table->unique(['kode_efek', 'tanggal']);
        });
    }
};
