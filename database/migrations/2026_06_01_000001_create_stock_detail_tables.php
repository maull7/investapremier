<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('stock_code')->index();
            $table->string('company_name');
            $table->string('sector')->nullable();
            $table->string('sub_sector')->nullable();
            $table->string('industry')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_corporate_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained()->cascadeOnDelete();
            $table->string('action_type', 50);
            $table->date('action_date');
            $table->text('description');
            $table->decimal('value', 24, 2)->nullable();
            $table->timestamps();
        });

        Schema::table('stock_prices', function (Blueprint $table) {
            $table->foreignId('stock_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->decimal('open', 18, 2)->nullable()->after('harga');
            $table->decimal('high', 18, 2)->nullable()->after('open');
            $table->decimal('low', 18, 2)->nullable()->after('high');
            $table->decimal('close', 18, 2)->nullable()->after('low');
            $table->unsignedBigInteger('volume')->nullable()->after('close');
        });

        Schema::create('stock_financial_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('report_year');
            $table->string('report_period', 20);
            $table->decimal('total_asset', 24, 2)->nullable();
            $table->decimal('total_liabilities', 24, 2)->nullable();
            $table->decimal('total_equity', 24, 2)->nullable();
            $table->decimal('revenue', 24, 2)->nullable();
            $table->decimal('operating_income', 24, 2)->nullable();
            $table->decimal('net_income', 24, 2)->nullable();
            $table->decimal('cfo', 24, 2)->nullable();
            $table->decimal('cfi', 24, 2)->nullable();
            $table->decimal('cff', 24, 2)->nullable();
            $table->timestamps();
            $table->unique(['stock_id', 'report_year', 'report_period'], 'stock_report_period_unique');
        });

        Schema::create('stock_news', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('source')->nullable();
            $table->string('url')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->text('summary')->nullable();
            $table->text('ai_summary')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_broker_researches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained()->cascadeOnDelete();
            $table->string('broker_name');
            $table->date('research_date')->nullable();
            $table->string('rating', 50)->nullable();
            $table->decimal('target_price', 18, 2)->nullable();
            $table->string('pdf_file')->nullable();
            $table->text('ai_summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_broker_researches');
        Schema::dropIfExists('stock_news');
        Schema::dropIfExists('stock_financial_reports');
        Schema::table('stock_prices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('stock_id');
            $table->dropColumn(['open', 'high', 'low', 'close', 'volume']);
        });
        Schema::dropIfExists('stock_corporate_actions');
        Schema::dropIfExists('stock_profiles');
    }
};
