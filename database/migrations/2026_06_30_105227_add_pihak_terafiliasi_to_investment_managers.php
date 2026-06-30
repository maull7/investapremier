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
        Schema::table('investment_managers', function (Blueprint $table) {
            $table->text('pihak_terafiliasi')->nullable()->after('dewan_pengawas_syariah');
        });
    }

    public function down(): void
    {
        Schema::table('investment_managers', function (Blueprint $table) {
            $table->dropColumn('pihak_terafiliasi');
        });
    }
};
