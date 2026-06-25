<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_partitions', function (Blueprint $table) {
            $table->integer('start_page_pdf')->nullable()->after('end_page');
            $table->integer('end_page_pdf')->nullable()->after('start_page_pdf');
        });
    }

    public function down(): void
    {
        Schema::table('document_partitions', function (Blueprint $table) {
            $table->dropColumn(['start_page_pdf', 'end_page_pdf']);
        });
    }
};
