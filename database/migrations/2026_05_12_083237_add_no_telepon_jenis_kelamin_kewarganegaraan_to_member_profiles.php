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
        Schema::table('member_profiles', function (Blueprint $table) {
            $table->string('no_telepon')->nullable()->after('user_id');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable()->after('no_telepon');
            $table->enum('kewarganegaraan', ['WNI', 'WNA'])->nullable()->after('jenis_kelamin');
        });
    }

    public function down(): void
    {
        Schema::table('member_profiles', function (Blueprint $table) {
            $table->dropColumn(['no_telepon', 'jenis_kelamin', 'kewarganegaraan']);
        });
    }
};
