<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portofolio_items', function (Blueprint $table) {
            $table->string('produk_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('portofolio_items', function (Blueprint $table) {
            $table->unsignedBigInteger('produk_id')->nullable()->change();
        });
    }
};
