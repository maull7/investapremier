<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            if (!Schema::hasColumn('stocks', 'perubahan_persen')) {
                $table->string('perubahan_persen')->nullable()->after('harga_terbaru');
            }
            if (!Schema::hasColumn('stocks', 'listing_board')) {
                $table->string('listing_board')->nullable()->after('jumlah_saham');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn(['perubahan_persen', 'listing_board']);
        });
    }
};
