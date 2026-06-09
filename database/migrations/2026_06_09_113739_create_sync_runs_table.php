<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_runs', function (Blueprint $table) {
            $table->id();
            // Type: 'obligasi_idx_phei', 'saham_idx', etc.
            $table->string('type', 50)->index();
            // queued | running | completed | failed | cancelled
            $table->string('status', 20)->index()->default('queued');
            // Free-form step identifier the job updates as it progresses
            // (e.g. 'fetch_idx', 'fetch_phei_govt', 'fetch_phei_corp', 'merge', 'upsert')
            $table->string('current_step', 64)->nullable();
            // Human-friendly label of current step for UI
            $table->string('current_step_label')->nullable();
            // 0..100
            $table->unsignedTinyInteger('progress_percent')->default(0);
            // JSON: final stats from merge + upsert
            $table->json('stats')->nullable();
            // JSON: list of error strings encountered
            $table->json('errors')->nullable();
            // Summary message shown in flash after completion
            $table->text('message')->nullable();
            // Who triggered it
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_runs');
    }
};
