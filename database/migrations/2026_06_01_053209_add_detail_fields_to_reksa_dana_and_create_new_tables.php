<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reksa_dana', function (Blueprint $table) {
            $table->foreignId('investment_manager_id')->nullable()->after('kode_reksa_dana')->constrained('investment_managers')->nullOnDelete();
            $table->longText('description')->nullable()->after('kebijakan_investasi');
            $table->string('custodian_bank', 200)->nullable()->after('description');
            $table->date('launch_date')->nullable()->after('custodian_bank');
            $table->string('risk_category', 50)->nullable()->after('mata_uang');
            $table->decimal('subscription_fee', 5, 2)->nullable()->after('risk_category');
            $table->decimal('redemption_fee', 5, 2)->nullable()->after('subscription_fee');
            $table->decimal('switching_fee', 5, 2)->nullable()->after('redemption_fee');
            $table->decimal('management_fee', 5, 2)->nullable()->after('switching_fee');
            $table->decimal('custodian_fee', 5, 2)->nullable()->after('management_fee');
            $table->decimal('minimum_subscription', 20, 2)->nullable()->after('custodian_fee');
            $table->decimal('minimum_topup', 20, 2)->nullable()->after('minimum_subscription');
            $table->decimal('minimum_redemption', 20, 2)->nullable()->after('minimum_topup');
        });

        Schema::table('harga_reksa_dana', function (Blueprint $table) {
            $table->decimal('aum', 24, 2)->nullable()->after('nab_per_unit');
            $table->decimal('unit_participation', 24, 2)->nullable()->after('aum');
        });

        Schema::create('mutual_fund_asset_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reksa_dana_id')->constrained('reksa_dana')->cascadeOnDelete();
            $table->date('period_date');
            $table->decimal('equity_percent', 5, 2)->nullable();
            $table->decimal('bond_percent', 5, 2)->nullable();
            $table->decimal('money_market_percent', 5, 2)->nullable();
            $table->decimal('cash_percent', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['reksa_dana_id', 'period_date'], 'mf_aa_rd_id_pd_unique');
        });

        Schema::create('mutual_fund_portfolio_compositions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reksa_dana_id')->constrained('reksa_dana')->cascadeOnDelete();
            $table->date('period_date');
            $table->string('security_name', 200);
            $table->string('security_type', 100)->nullable();
            $table->decimal('weight_percent', 5, 2)->nullable();
            $table->timestamps();

            $table->index(['reksa_dana_id', 'period_date'], 'mf_pc_rd_id_pd_idx');
        });

        Schema::create('mutual_fund_management_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reksa_dana_id')->constrained('reksa_dana')->cascadeOnDelete();
            $table->string('type', 50); // committee, investment_manager
            $table->string('name', 200);
            $table->string('position', 200)->nullable();
            $table->timestamps();

            $table->index(['reksa_dana_id', 'type'], 'mf_mt_rd_id_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutual_fund_management_teams');
        Schema::dropIfExists('mutual_fund_portfolio_compositions');
        Schema::dropIfExists('mutual_fund_asset_allocations');

        Schema::table('harga_reksa_dana', function (Blueprint $table) {
            $table->dropColumn(['aum', 'unit_participation']);
        });

        Schema::table('reksa_dana', function (Blueprint $table) {
            $table->dropForeign(['investment_manager_id']);
            $table->dropColumn([
                'investment_manager_id', 'description', 'custodian_bank', 'launch_date',
                'risk_category', 'subscription_fee', 'redemption_fee', 'switching_fee',
                'management_fee', 'custodian_fee', 'minimum_subscription', 'minimum_topup',
                'minimum_redemption',
            ]);
        });
    }
};
