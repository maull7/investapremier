<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reksa_dana_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reksa_dana_id')->constrained('reksa_dana')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('document_type', 20);
            $table->unsignedTinyInteger('ffs_month')->nullable();
            $table->unsignedSmallInteger('ffs_year')->nullable();
            $table->string('original_name');
            $table->string('file_path');
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('file_size');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['reksa_dana_id', 'document_type']);
            $table->index(['ffs_year', 'ffs_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reksa_dana_documents');
    }
};
