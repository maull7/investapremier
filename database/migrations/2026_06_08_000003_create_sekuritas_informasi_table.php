<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sekuritas_informasi', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['government', 'corporate']);
            $table->string('kode_obligasi', 20);
            $table->string('nama_obligasi');
            $table->string('isin_code', 30)->nullable();
            $table->string('currency', 10)->default('IDR');
            $table->decimal('outstanding_amount', 22, 2)->nullable();
            $table->decimal('coupon', 8, 4)->nullable();
            $table->date('maturity_date')->nullable();
            $table->timestamps();

            $table->unique(['type', 'kode_obligasi']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sekuritas_informasi');
    }
};
