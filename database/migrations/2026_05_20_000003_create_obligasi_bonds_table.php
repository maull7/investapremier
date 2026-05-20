<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obligasi_bonds', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->index();
            $table->string('periode')->nullable();
            $table->decimal('current_asset', 24, 2)->nullable();
            $table->decimal('current_liabilities', 24, 2)->nullable();
            $table->decimal('total_asset', 24, 2)->nullable();
            $table->decimal('total_liabilities', 24, 2)->nullable();
            $table->decimal('retained_earning', 24, 2)->nullable();
            $table->decimal('equity', 24, 2)->nullable();
            $table->decimal('interest_expense', 24, 2)->nullable();
            $table->decimal('laba_operasional', 24, 2)->nullable();
            $table->decimal('cash_equivalents', 24, 2)->nullable();
            $table->decimal('account_receivable', 24, 2)->nullable();
            $table->decimal('inventories', 24, 2)->nullable();
            $table->decimal('other_current_asset', 24, 2)->nullable();
            $table->decimal('fixed_asset', 24, 2)->nullable();
            $table->decimal('other_non_current_asset', 24, 2)->nullable();
            $table->decimal('account_payable', 24, 2)->nullable();
            $table->decimal('accruals', 24, 2)->nullable();
            $table->decimal('short_term_loans', 24, 2)->nullable();
            $table->decimal('current_maturities_of_long_term_loans', 24, 2)->nullable();
            $table->decimal('other_current_liabilities', 24, 2)->nullable();
            $table->decimal('long_term_loans', 24, 2)->nullable();
            $table->decimal('employee_benefits', 24, 2)->nullable();
            $table->decimal('other_non_current_liabilities', 24, 2)->nullable();
            $table->decimal('total_non_current_liabilities', 24, 2)->nullable();
            $table->decimal('share_capital', 24, 2)->nullable();
            $table->decimal('additional_paid_in_capital', 24, 2)->nullable();
            $table->decimal('others', 24, 2)->nullable();
            $table->decimal('non_controlling_interest', 24, 2)->nullable();
            $table->decimal('total_equity_equity_to_parent_entity', 24, 2)->nullable();
            $table->decimal('net_revenue', 24, 2)->nullable();
            $table->decimal('cost_of_good_sold', 24, 2)->nullable();
            $table->decimal('gross_income', 24, 2)->nullable();
            $table->decimal('operational_expense', 24, 2)->nullable();
            $table->decimal('other_income_expense', 24, 2)->nullable();
            $table->decimal('income_before_tax', 24, 2)->nullable();
            $table->decimal('taxes', 24, 2)->nullable();
            $table->decimal('ebit', 24, 2)->nullable();
            $table->decimal('ebitda', 24, 2)->nullable();
            $table->decimal('net_income_attributable_to_non_controlling_interest', 24, 2)->nullable();
            $table->decimal('net_income', 24, 2)->nullable();
            $table->decimal('cash_flows_operating_activities', 24, 2)->nullable();
            $table->decimal('cash_flows_investment', 24, 2)->nullable();
            $table->decimal('cash_flows_financing', 24, 2)->nullable();
            $table->timestamps();

            $table->unique(['kode', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obligasi_bonds');
    }
};
