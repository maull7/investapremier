<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mutual_fund_risk_metrics', function (Blueprint $table) {
            $table->decimal('sharpe_ratio_10y', 10, 6)->nullable()->after('sharpe_ratio_5y');
            $table->decimal('stdev_10y', 10, 6)->nullable()->after('stdev_5y');
            $table->decimal('beta_10y', 10, 6)->nullable()->after('beta_5y');
            $table->decimal('max_drawdown_10y', 10, 6)->nullable()->after('max_drawdown_5y');
        });
    }

    public function down(): void
    {
        Schema::table('mutual_fund_risk_metrics', function (Blueprint $table) {
            $table->dropColumn([
                'sharpe_ratio_10y',
                'stdev_10y',
                'beta_10y',
                'max_drawdown_10y',
            ]);
        });
    }
};