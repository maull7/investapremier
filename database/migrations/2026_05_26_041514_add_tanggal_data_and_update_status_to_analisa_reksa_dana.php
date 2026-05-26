<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            $table->date('tanggal_data')->nullable()->after('total_marcap_10_efek');
        });

        DB::statement("ALTER TABLE analisa_reksa_dana MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'draft'");
        DB::statement("UPDATE analisa_reksa_dana SET status = 'submitted' WHERE status NOT IN ('draft', 'submitted', 'reviewed')");
    }

    public function down(): void
    {
        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            $table->dropColumn('tanggal_data');
        });

        DB::statement("ALTER TABLE analisa_reksa_dana MODIFY COLUMN status ENUM('draft','submitted','reviewed') NOT NULL DEFAULT 'draft'");
    }
};
