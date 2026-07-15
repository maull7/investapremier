<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reksa_dana', function (Blueprint $table) {
            $table->text('risk_description')->nullable()->after('risk_category');
        });
    }

    public function down(): void
    {
        Schema::table('reksa_dana', function (Blueprint $table) {
            $table->dropColumn('risk_description');
        });
    }
};
