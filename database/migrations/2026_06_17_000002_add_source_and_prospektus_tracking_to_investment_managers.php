<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investment_managers', function (Blueprint $table) {
            $table->string('source', 50)->nullable()->after('description')
                ->comment('manual, pasardana, prospektus');
            $table->foreignId('prospektus_source_reksa_dana_id')
                ->nullable()
                ->after('source')
                ->constrained('reksa_dana')
                ->nullOnDelete();
            $table->integer('prospektus_source_tahun')
                ->nullable()
                ->after('prospektus_source_reksa_dana_id')
                ->comment('Tahun prospektus');
        });
    }

    public function down(): void
    {
        Schema::table('investment_managers', function (Blueprint $table) {
            $table->dropForeign(['prospektus_source_reksa_dana_id']);
            $table->dropColumn([
                'source',
                'prospektus_source_reksa_dana_id',
                'prospektus_source_tahun',
            ]);
        });
    }
};
