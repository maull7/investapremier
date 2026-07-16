<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remove duplicates before adding unique constraint
        DB::statement("
            DELETE t1 FROM mutual_fund_portfolio_compositions t1
            INNER JOIN mutual_fund_portfolio_compositions t2 
            WHERE 
                t1.id > t2.id
                AND t1.reksa_dana_id = t2.reksa_dana_id
                AND t1.period_date = t2.period_date
                AND t1.security_name = t2.security_name
        ");

        Schema::table('mutual_fund_portfolio_compositions', function (Blueprint $table) {
            $table->unique(['reksa_dana_id', 'period_date', 'security_name'], 'mfpc_unique_holding');
        });
    }

    public function down(): void
    {
        Schema::table('mutual_fund_portfolio_compositions', function (Blueprint $table) {
            $table->dropUnique('mfpc_unique_holding');
        });
    }
};
