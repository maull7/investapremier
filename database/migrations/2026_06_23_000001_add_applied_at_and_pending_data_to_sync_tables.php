<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sync_runs', function (Blueprint $table) {
            $table->timestamp('applied_at')->nullable()->after('completed_at');
        });

        Schema::table('sync_change_logs', function (Blueprint $table) {
            $table->json('pending_data')->nullable()->after('change_type');
        });
    }

    public function down(): void
    {
        Schema::table('sync_runs', function (Blueprint $table) {
            $table->dropColumn('applied_at');
        });

        Schema::table('sync_change_logs', function (Blueprint $table) {
            $table->dropColumn('pending_data');
        });
    }
};
