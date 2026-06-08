<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extraction_batches', function (Blueprint $table) {
            $table->id();
            $table->string('data_type', 50);
            $table->string('source', 100);
            $table->date('data_date')->nullable();
            $table->string('identifier')->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->unsignedInteger('total_records')->default(0);
            $table->text('error_message')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('extracted_stock_daily_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extraction_batch_id')->constrained('extraction_batches')->cascadeOnDelete();
            $table->string('stock_code', 20);
            $table->date('data_date');
            $table->decimal('open', 18, 2)->nullable();
            $table->decimal('high', 18, 2)->nullable();
            $table->decimal('low', 18, 2)->nullable();
            $table->decimal('close', 18, 2)->nullable();
            $table->unsignedBigInteger('volume')->nullable();
            $table->decimal('price_change', 18, 2)->nullable();
            $table->decimal('change_percent', 10, 4)->nullable();
            $table->decimal('market_cap', 24, 2)->nullable();
            $table->string('source', 100);
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->unique(['stock_code', 'data_date', 'source'], 'extracted_stock_daily_unique');
        });

        Schema::create('extracted_bond_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extraction_batch_id')->constrained('extraction_batches')->cascadeOnDelete();
            $table->string('bond_code', 50);
            $table->string('bond_name')->nullable();
            $table->string('issuer')->nullable();
            $table->date('maturity_date')->nullable();
            $table->decimal('coupon', 10, 4)->nullable();
            $table->string('rating', 50)->nullable();
            $table->decimal('yield', 12, 6)->nullable();
            $table->decimal('fair_price', 12, 4)->nullable();
            $table->date('data_date')->nullable();
            $table->string('source', 100);
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->unique(['bond_code', 'data_date', 'source'], 'extracted_bond_data_unique');
        });

        Schema::create('extracted_market_news', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extraction_batch_id')->constrained('extraction_batches')->cascadeOnDelete();
            $table->dateTime('news_date')->nullable();
            $table->string('title');
            $table->string('url');
            $table->string('source', 100);
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->unique(['url', 'source'], 'extracted_market_news_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extracted_market_news');
        Schema::dropIfExists('extracted_bond_data');
        Schema::dropIfExists('extracted_stock_daily_transactions');
        Schema::dropIfExists('extraction_batches');
    }
};
