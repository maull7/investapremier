<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('advisor_id')->nullable()->after('id');
            $table->foreign('advisor_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('member_profiles', function (Blueprint $table) {
            $table->date('tanggal_lahir')->nullable()->after('no_telepon');
            $table->string('profil_risiko')->nullable()->after('pekerjaan');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['advisor_id']);
            $table->dropColumn('advisor_id');
        });

        Schema::table('member_profiles', function (Blueprint $table) {
            $table->dropColumn(['tanggal_lahir', 'profil_risiko']);
        });
    }
};
