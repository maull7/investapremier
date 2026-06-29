<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investment_managers', function (Blueprint $table) {
            $table->text('dewan_pengawas_syariah')->nullable()->after('directors');
        });
    }

    public function down(): void
    {
        Schema::table('investment_managers', function (Blueprint $table) {
            $table->dropColumn('dewan_pengawas_syariah');
        });
    }
};
