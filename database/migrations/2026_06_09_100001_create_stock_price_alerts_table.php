<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_price_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_id')->nullable()->constrained('stocks')->nullOnDelete();
            $table->string('kode_efek');
            $table->enum('condition', ['above', 'below'])->default('above');
            $table->decimal('target_price', 18, 2);
            $table->string('note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('repeat')->default(false);
            $table->decimal('last_seen_price', 18, 2)->nullable();
            $table->timestamp('triggered_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index(['kode_efek', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_price_alerts');
    }
};
