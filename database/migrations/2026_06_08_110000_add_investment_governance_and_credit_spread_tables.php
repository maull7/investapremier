<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investment_managers', function (Blueprint $table) {
            if (!Schema::hasColumn('investment_managers', 'investment_committee')) {
                $table->text('investment_committee')->nullable()->after('shareholders');
            }
            if (!Schema::hasColumn('investment_managers', 'investment_management_team')) {
                $table->text('investment_management_team')->nullable()->after('investment_committee');
            }
        });

        Schema::create('investment_person_roles', function (Blueprint $table) {
            $table->id();
            $table->string('person_name', 200);
            $table->string('normalized_name', 200);
            $table->string('role_type', 80);
            $table->string('role_title', 200)->nullable();
            $table->foreignId('investment_manager_id')->nullable()->constrained('investment_managers')->cascadeOnDelete();
            $table->foreignId('reksa_dana_id')->nullable()->constrained('reksa_dana')->cascadeOnDelete();
            $table->string('source', 80)->nullable();
            $table->timestamps();

            $table->unique(
                ['normalized_name', 'role_type', 'investment_manager_id', 'reksa_dana_id'],
                'ipr_name_role_scope_unique'
            );
            $table->index(['normalized_name', 'role_type'], 'ipr_name_role_idx');
        });

        Schema::create('phei_credit_spread_matrices', function (Blueprint $table) {
            $table->id();
            $table->date('data_date');
            $table->unsignedSmallInteger('tenor_bulan');
            $table->decimal('rating_aaa', 10, 6)->nullable();
            $table->decimal('rating_aa', 10, 6)->nullable();
            $table->decimal('rating_a', 10, 6)->nullable();
            $table->decimal('rating_bbb', 10, 6)->nullable();
            $table->string('source', 80)->default('PHEI');
            $table->timestamps();

            $table->unique(['data_date', 'tenor_bulan', 'source'], 'phei_csm_date_tenor_source_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phei_credit_spread_matrices');
        Schema::dropIfExists('investment_person_roles');

        Schema::table('investment_managers', function (Blueprint $table) {
            $drops = [];
            if (Schema::hasColumn('investment_managers', 'investment_committee')) {
                $drops[] = 'investment_committee';
            }
            if (Schema::hasColumn('investment_managers', 'investment_management_team')) {
                $drops[] = 'investment_management_team';
            }
            if ($drops !== []) {
                $table->dropColumn($drops);
            }
        });
    }
};
