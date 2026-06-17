<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('investment_manager_prospektus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_manager_id')->constrained('investment_managers')->cascadeOnDelete();
            $table->foreignId('reksa_dana_id')->nullable()->constrained('reksa_dana')->nullOnDelete();
            $table->unsignedSmallInteger('tahun')->nullable();
            $table->json('data');
            $table->timestamps();

            $table->index(['investment_manager_id', 'tahun']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_manager_prospektus');
    }
};
