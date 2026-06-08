<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('extraction_batches', function (Blueprint $table) {
            $table->unsignedInteger('range_start')->nullable()->after('identifier');
            $table->unsignedInteger('range_end')->nullable()->after('range_start');
            $table->string('range_label', 30)->nullable()->after('range_end');
        });
    }

    public function down(): void
    {
        Schema::table('extraction_batches', function (Blueprint $table) {
            $table->dropColumn(['range_start', 'range_end', 'range_label']);
        });
    }
};
