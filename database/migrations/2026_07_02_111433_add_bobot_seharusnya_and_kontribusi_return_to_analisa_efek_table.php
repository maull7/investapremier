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
        Schema::table('analisa_efek', function (Blueprint $table) {
            $table->decimal('bobot_seharusnya', 15, 4)->nullable()->after('bobot');
            $table->decimal('kontribusi_return', 15, 4)->nullable()->after('ihsg_contribution');
        });
    }

    public function down(): void
    {
        Schema::table('analisa_efek', function (Blueprint $table) {
            $table->dropColumn(['bobot_seharusnya', 'kontribusi_return']);
        });
    }
};
