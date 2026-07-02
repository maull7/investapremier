<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('analisa_keuangan', function (Blueprint $table) {
            $table->decimal('aktivitas_lancar', 20, 4)->nullable()->after('current_ratio');
        });
    }

    public function down(): void
    {
        Schema::table('analisa_keuangan', function (Blueprint $table) {
            $table->dropColumn('aktivitas_lancar');
        });
    }
};
