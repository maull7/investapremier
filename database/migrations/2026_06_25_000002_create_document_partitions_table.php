<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_partitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reksa_dana_document_id')->constrained('reksa_dana_documents')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_partisi');
            $table->unsignedInteger('start_page');
            $table->unsignedInteger('end_page');
            $table->timestamps();

            $table->index(['reksa_dana_document_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_partitions');
    }
};
