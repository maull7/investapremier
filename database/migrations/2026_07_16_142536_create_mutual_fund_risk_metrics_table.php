<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mutual_fund_risk_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reksa_dana_id')->constrained('reksa_dana')->cascadeOnDelete();
            $table->date('period_date')->comment('First day of month, e.g. 2026-01-01');
            
            // Sharpe Ratio
            $table->decimal('sharpe_ratio_1m', 10, 6)->nullable();
            $table->decimal('sharpe_ratio_3m', 10, 6)->nullable();
            $table->decimal('sharpe_ratio_6m', 10, 6)->nullable();
            $table->decimal('sharpe_ratio_1y', 10, 6)->nullable();
            $table->decimal('sharpe_ratio_3y', 10, 6)->nullable();
            $table->decimal('sharpe_ratio_5y', 10, 6)->nullable();
            $table->decimal('sharpe_ratio_10y', 10, 6)->nullable();
            
            // Standard Deviation
            $table->decimal('stdev_1m', 10, 6)->nullable();
            $table->decimal('stdev_3m', 10, 6)->nullable();
            $table->decimal('stdev_6m', 10, 6)->nullable();
            $table->decimal('stdev_1y', 10, 6)->nullable();
            $table->decimal('stdev_3y', 10, 6)->nullable();
            $table->decimal('stdev_5y', 10, 6)->nullable();
            $table->decimal('stdev_10y', 10, 6)->nullable();
            
            // Beta
            $table->decimal('beta_1m', 10, 6)->nullable();
            $table->decimal('beta_3m', 10, 6)->nullable();
            $table->decimal('beta_6m', 10, 6)->nullable();
            $table->decimal('beta_1y', 10, 6)->nullable();
            $table->decimal('beta_3y', 10, 6)->nullable();
            $table->decimal('beta_5y', 10, 6)->nullable();
            $table->decimal('beta_10y', 10, 6)->nullable();
            
            // Max Drawdown
            $table->decimal('max_drawdown_1m', 10, 6)->nullable();
            $table->decimal('max_drawdown_3m', 10, 6)->nullable();
            $table->decimal('max_drawdown_6m', 10, 6)->nullable();
            $table->decimal('max_drawdown_1y', 10, 6)->nullable();
            $table->decimal('max_drawdown_3y', 10, 6)->nullable();
            $table->decimal('max_drawdown_5y', 10, 6)->nullable();
            $table->decimal('max_drawdown_10y', 10, 6)->nullable();
            
            // Sortino, Treynor, Jensen Alpha, Tracking Error
            $table->decimal('sortino_ratio_1m', 10, 6)->nullable();
            $table->decimal('sortino_ratio_3m', 10, 6)->nullable();
            $table->decimal('sortino_ratio_6m', 10, 6)->nullable();
            $table->decimal('sortino_ratio_1y', 10, 6)->nullable();
            $table->decimal('sortino_ratio_3y', 10, 6)->nullable();
            $table->decimal('sortino_ratio_5y', 10, 6)->nullable();
            
            $table->decimal('treynor_ratio_1m', 10, 6)->nullable();
            $table->decimal('treynor_ratio_3m', 10, 6)->nullable();
            $table->decimal('treynor_ratio_6m', 10, 6)->nullable();
            $table->decimal('treynor_ratio_1y', 10, 6)->nullable();
            $table->decimal('treynor_ratio_3y', 10, 6)->nullable();
            $table->decimal('treynor_ratio_5y', 10, 6)->nullable();
            
            $table->decimal('jensen_alpha_1m', 10, 6)->nullable();
            $table->decimal('jensen_alpha_3m', 10, 6)->nullable();
            $table->decimal('jensen_alpha_6m', 10, 6)->nullable();
            $table->decimal('jensen_alpha_1y', 10, 6)->nullable();
            $table->decimal('jensen_alpha_3y', 10, 6)->nullable();
            $table->decimal('jensen_alpha_5y', 10, 6)->nullable();
            
            $table->decimal('tracking_error_1m', 10, 6)->nullable();
            $table->decimal('tracking_error_3m', 10, 6)->nullable();
            $table->decimal('tracking_error_6m', 10, 6)->nullable();
            $table->decimal('tracking_error_1y', 10, 6)->nullable();
            $table->decimal('tracking_error_3y', 10, 6)->nullable();
            $table->decimal('tracking_error_5y', 10, 6)->nullable();
            
            $table->timestamps();
            
            $table->unique(['reksa_dana_id', 'period_date']);
            $table->index('period_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutual_fund_risk_metrics');
    }
};