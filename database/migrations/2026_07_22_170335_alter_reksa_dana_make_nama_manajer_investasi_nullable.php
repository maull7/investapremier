<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reksa_dana', function (Blueprint $table) {
            $table->string('nama_manajer_investasi')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('reksa_dana', function (Blueprint $table) {
            $table->string('nama_manajer_investasi')->nullable(false)->change();
        });
    }
};
