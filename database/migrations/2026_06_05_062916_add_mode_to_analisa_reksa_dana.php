<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            $table->string('mode', 50)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            $table->dropColumn('mode');
        });
    }
};
