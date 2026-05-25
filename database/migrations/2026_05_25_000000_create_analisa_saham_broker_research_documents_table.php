<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analisa_saham_broker_research_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisa_saham_id');
            $table->foreignId('uploaded_by')->nullable();
            $table->string('broker', 100);
            $table->string('original_name');
            $table->string('file_path');
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('file_size');
            $table->timestamps();

            $table->foreign('analisa_saham_id', 'asbrd_analisa_saham_fk')
                ->references('id')->on('analisa_saham')->cascadeOnDelete();
            $table->foreign('uploaded_by', 'asbrd_uploaded_by_fk')
                ->references('id')->on('users')->nullOnDelete();
            $table->index(['analisa_saham_id', 'created_at'], 'asbrd_analisa_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisa_saham_broker_research_documents');
    }
};
