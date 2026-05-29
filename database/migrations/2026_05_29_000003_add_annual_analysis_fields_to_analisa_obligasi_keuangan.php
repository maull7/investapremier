<?php

use App\Enums\AnalisaDataSource;
use App\Enums\AnalisaType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analisa_obligasi_keuangan', function (Blueprint $table) {
            $table->string('jenis_analisa', 20)->default(AnalisaType::ANALISA_PERIODE->value)->after('periode');
            $table->string('sumber_data', 50)->nullable()->after('jenis_analisa');
            $table->string('tahun', 4)->nullable()->after('sumber_data');
            $table->json('data_tahunan')->nullable()->after('tahun');
        });
    }

    public function down(): void
    {
        Schema::table('analisa_obligasi_keuangan', function (Blueprint $table) {
            $table->dropColumn(['jenis_analisa', 'sumber_data', 'tahun', 'data_tahunan']);
        });
    }
};
