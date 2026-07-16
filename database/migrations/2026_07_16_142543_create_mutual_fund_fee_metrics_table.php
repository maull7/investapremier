<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mutual_fund_fee_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reksa_dana_id')->constrained('reksa_dana')->cascadeOnDelete();
            $table->date('period_date')->comment('First day of month, e.g. 2026-01-01');
            
            $table->decimal('subscription_fee', 10, 4)->nullable();
            $table->decimal('redemption_fee', 10, 4)->nullable();
            $table->decimal('switching_fee', 10, 4)->nullable();
            $table->decimal('management_fee', 10, 4)->nullable();
            $table->decimal('custodian_fee', 10, 4)->nullable();
            $table->decimal('expense_ratio', 10, 6)->nullable();
            $table->decimal('investment_manager_fee', 10, 4)->nullable();
            
            $table->decimal('minimum_subscription', 20, 2)->nullable();
            $table->decimal('minimum_topup', 20, 2)->nullable();
            $table->decimal('minimum_redemption', 20, 2)->nullable();
            
            $table->timestamps();
            
            $table->unique(['reksa_dana_id', 'period_date']);
            $table->index('period_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutual_fund_fee_metrics');
    }
};