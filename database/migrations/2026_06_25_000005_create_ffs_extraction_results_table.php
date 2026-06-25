<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ffs_extraction_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reksa_dana_document_id')->constrained('reksa_dana_documents')->cascadeOnDelete();
            $table->foreignId('reksa_dana_id')->nullable()->constrained('reksa_dana')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->tinyInteger('ffs_month')->nullable();
            $table->year('ffs_year')->nullable();
            $table->date('tanggal_data')->nullable();
            $table->json('extracted_data');
            $table->timestamps();

            $table->index(['reksa_dana_document_id']);
            $table->index(['reksa_dana_id', 'ffs_year', 'ffs_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ffs_extraction_results');
    }
};
