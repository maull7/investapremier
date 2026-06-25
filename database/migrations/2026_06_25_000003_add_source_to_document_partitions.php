<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_partitions', function (Blueprint $table) {
            $table->string('source', 30)->default('manual')->after('end_page');
        });
    }

    public function down(): void
    {
        Schema::table('document_partitions', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
