<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investment_managers', function (Blueprint $table) {
            $table->string('kode_mi', 10)->nullable()->unique()->after('name');
        });

        Schema::table('reksa_dana', function (Blueprint $table) {
            $table->string('kode_reksa_dana', 20)->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('investment_managers', function (Blueprint $table) {
            $table->dropColumn('kode_mi');
        });

        Schema::table('reksa_dana', function (Blueprint $table) {
            $table->dropColumn('kode_reksa_dana');
        });
    }
};
