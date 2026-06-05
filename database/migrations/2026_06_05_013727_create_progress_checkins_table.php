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
        Schema::create('progress_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perencanaan_investasi_id')->constrained('perencanaan_investasi')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('dana_terkumpul', 20, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->date('tanggal_checkin');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_checkins');
    }
};
