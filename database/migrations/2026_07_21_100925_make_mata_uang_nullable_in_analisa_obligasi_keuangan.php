<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analisa_obligasi_keuangan', function (Blueprint $table) {
            $table->string('mata_uang', 10)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('analisa_obligasi_keuangan', function (Blueprint $table) {
            $table->string('mata_uang', 10)->default('IDR')->change();
        });
    }
};
