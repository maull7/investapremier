<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_prompts', function (Blueprint $table) {
            $table->string('group', 50)->nullable()->after('key');
            $table->integer('sort_order')->default(0)->after('value');
            $table->index('group');
        });
    }

    public function down(): void
    {
        Schema::table('ai_prompts', function (Blueprint $table) {
            $table->dropIndex(['group']);
            $table->dropColumn('sort_order');
            $table->dropColumn('group');
        });
    }
};
