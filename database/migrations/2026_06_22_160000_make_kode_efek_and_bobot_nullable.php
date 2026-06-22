<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analisa_efek', function (Blueprint $table) {
            $table->string('kode_efek')->nullable()->change();
            $table->decimal('bobot', 8, 4)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('analisa_efek', function (Blueprint $table) {
            $table->string('kode_efek')->nullable(false)->change();
            $table->decimal('bobot', 8, 4)->nullable(false)->change();
        });
    }
};
