<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analisa_obligasi_keuangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('kode_obligasi', 50)->nullable();
            $table->string('nama_obligasi');
            $table->string('nama_emiten')->nullable();
            $table->string('rating', 20)->nullable();
            $table->string('mata_uang', 10)->default('IDR');
            $table->string('periode', 20)->nullable();
            $table->decimal('kupon', 8, 4)->nullable();
            $table->decimal('ytm', 8, 4)->nullable();

            // Neraca - Aset
            $table->decimal('current_asset', 20, 2)->nullable();
            $table->decimal('cash_equivalents', 20, 2)->nullable();
            $table->decimal('account_receivable', 20, 2)->nullable();
            $table->decimal('inventories', 20, 2)->nullable();
            $table->decimal('other_current_asset', 20, 2)->nullable();
            $table->decimal('fixed_asset', 20, 2)->nullable();
            $table->decimal('other_non_current_asset', 20, 2)->nullable();
            $table->decimal('total_asset', 20, 2)->nullable();

            // Neraca - Liabilitas
            $table->decimal('current_liabilities', 20, 2)->nullable();
            $table->decimal('account_payable', 20, 2)->nullable();
            $table->decimal('accruals', 20, 2)->nullable();
            $table->decimal('short_term_loans', 20, 2)->nullable();
            $table->decimal('current_maturities_of_long_term_loans', 20, 2)->nullable();
            $table->decimal('other_current_liabilities', 20, 2)->nullable();
            $table->decimal('long_term_loans', 20, 2)->nullable();
            $table->decimal('employee_benefits', 20, 2)->nullable();
            $table->decimal('other_non_current_liabilities', 20, 2)->nullable();
            $table->decimal('total_non_current_liabilities', 20, 2)->nullable();
            $table->decimal('total_liabilities', 20, 2)->nullable();

            // Neraca - Ekuitas
            $table->decimal('share_capital', 20, 2)->nullable();
            $table->decimal('additional_paid_in_capital', 20, 2)->nullable();
            $table->decimal('retained_earning', 20, 2)->nullable();
            $table->decimal('others', 20, 2)->nullable();
            $table->decimal('non_controlling_interest', 20, 2)->nullable();
            $table->decimal('total_equity_equity_to_parent_entity', 20, 2)->nullable();
            $table->decimal('equity', 20, 2)->nullable();

            // Laba Rugi
            $table->decimal('net_revenue', 20, 2)->nullable();
            $table->decimal('cost_of_good_sold', 20, 2)->nullable();
            $table->decimal('gross_income', 20, 2)->nullable();
            $table->decimal('operational_expense', 20, 2)->nullable();
            $table->decimal('laba_operasional', 20, 2)->nullable();
            $table->decimal('other_income_expense', 20, 2)->nullable();
            $table->decimal('interest_expense', 20, 2)->nullable();
            $table->decimal('income_before_tax', 20, 2)->nullable();
            $table->decimal('taxes', 20, 2)->nullable();
            $table->decimal('ebit', 20, 2)->nullable();
            $table->decimal('ebitda', 20, 2)->nullable();
            $table->decimal('net_income_attributable_to_non_controlling_interest', 20, 2)->nullable();
            $table->decimal('net_income', 20, 2)->nullable();

            // Arus Kas
            $table->decimal('cash_flows_operating_activities', 20, 2)->nullable();
            $table->decimal('cash_flows_investment', 20, 2)->nullable();
            $table->decimal('cash_flows_financing', 20, 2)->nullable();

            // Catatan analisa
            $table->text('catatan')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->string('status', 20)->default('submitted');
            $table->text('ai_narasi')->nullable();
            $table->json('ai_output')->nullable();
            $table->text('ai_narasi_plus')->nullable();
            $table->json('ai_output_plus')->nullable();
            $table->string('pdf_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisa_obligasi_keuangan');
    }
};
