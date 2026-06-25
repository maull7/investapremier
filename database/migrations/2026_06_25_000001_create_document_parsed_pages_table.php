<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_parsed_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reksa_dana_document_id')->constrained('reksa_dana_documents')->cascadeOnDelete();
            $table->unsignedInteger('page_pdf');
            $table->unsignedInteger('page_parse');
            $table->longText('text_content');
            $table->timestamps();

            $table->unique(['reksa_dana_document_id', 'page_pdf']);
            $table->index(['reksa_dana_document_id', 'page_parse']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_parsed_pages');
    }
};
