<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mutual_fund_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reksa_dana_id')->constrained('reksa_dana')->cascadeOnDelete();
            $table->date('period_date')->comment('First day of month, e.g. 2026-01-01');
            
            // Ringkasan fields
            $table->decimal('nab_per_unit', 20, 6)->nullable()->comment('NAB per unit');
            $table->decimal('aum', 25, 2)->nullable()->comment('Total AUM');
            $table->decimal('total_unit', 25, 2)->nullable()->comment('Total unit penyertaan');
            
            // Return fields
            $table->decimal('return_1m', 10, 6)->nullable()->comment('Return 1 bulan (%)');
            $table->decimal('return_3m', 10, 6)->nullable()->comment('Return 3 bulan (%)');
            $table->decimal('return_6m', 10, 6)->nullable()->comment('Return 6 bulan (%)');
            $table->decimal('return_ytd', 10, 6)->nullable()->comment('Return YTD (%)');
            $table->decimal('return_1y', 10, 6)->nullable()->comment('Return 1 tahun (%)');
            $table->decimal('return_3y', 10, 6)->nullable()->comment('Return 3 tahun (% p.a.)');
            $table->decimal('return_5y', 10, 6)->nullable()->comment('Return 5 tahun (% p.a.)');
            $table->decimal('return_inception', 10, 6)->nullable()->comment('Return since inception (% p.a.)');
            
            $table->timestamps();
            
            $table->unique(['reksa_dana_id', 'period_date']);
            $table->index('period_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutual_fund_snapshots');
    }
};