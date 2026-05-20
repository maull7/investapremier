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
        Schema::create('unit_links', function (Blueprint $table) {
            $table->id();
            $table->string('unit_link');
            $table->string('asuransi')->nullable();
            $table->string('jenis')->nullable();
            $table->string('tipe')->nullable();
            $table->string('mata_uang')->nullable();
            $table->decimal('median_price', 18, 4)->nullable();
            $table->decimal('buy_price', 18, 4)->nullable();
            $table->decimal('sell_price', 18, 4)->nullable();
            $table->date('last_update')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_links');
    }
};
