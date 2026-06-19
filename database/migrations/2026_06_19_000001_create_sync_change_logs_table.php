<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sync_run_id')->constrained()->cascadeOnDelete();
            $table->string('entity_type')->index();
            $table->string('entity_id')->nullable()->index();
            $table->string('entity_label')->nullable();
            $table->string('field');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('change_type')->index(); // created, updated, deleted
            $table->timestamp('created_at')->useCurrent();

            $table->index(['sync_run_id', 'entity_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_change_logs');
    }
};
