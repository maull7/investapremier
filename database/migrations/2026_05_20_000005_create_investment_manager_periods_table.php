<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investment_manager_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_manager_id')->constrained('investment_managers')->cascadeOnDelete();
            $table->date('period_date');
            $table->decimal('aum', 24, 2)->nullable();
            $table->decimal('up', 24, 2)->nullable();
            $table->timestamps();

            $table->unique(['investment_manager_id', 'period_date'], 'imp_im_id_pd_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_manager_periods');
    }
};
