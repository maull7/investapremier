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
        Schema::create('harga_unit_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_link_id')->constrained('unit_links')->cascadeOnDelete();
            $table->datetime('datetime');
            $table->decimal('harga_median', 20, 6);
            $table->decimal('sell_buy_low', 20, 6)->nullable();
            $table->decimal('sell_buy_high', 20, 6)->nullable();
            $table->timestamps();
            $table->unique(['unit_link_id', 'datetime']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harga_unit_links');
    }
};
