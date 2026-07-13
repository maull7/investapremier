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
        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            $table->boolean('is_published')->default(false)->after('status');
            $table->timestamp('published_at')->nullable()->after('is_published');
        });
    }

    public function down(): void
    {
        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            $table->dropColumn(['is_published', 'published_at']);
        });
    }
};
