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
        Schema::table('investment_managers', function (Blueprint $table) {
            $table->string('kode_ojk', 50)->nullable()->after('kode_mi');
            $table->text('address')->nullable()->after('kode_ojk');
            $table->string('phone', 50)->nullable()->after('address');
            $table->string('email', 100)->nullable()->after('phone');
            $table->string('website', 200)->nullable()->after('email');
            $table->string('commissioner_president', 200)->nullable()->after('website');
            $table->text('commissioners')->nullable()->after('commissioner_president');
            $table->string('director_president', 200)->nullable()->after('commissioners');
            $table->text('directors')->nullable()->after('director_president');
            $table->text('shareholders')->nullable()->after('directors');
            $table->date('last_updated_at')->nullable()->after('shareholders');
            $table->longText('description')->nullable()->after('last_updated_at');
        });

        Schema::table('investment_manager_periods', function (Blueprint $table) {
            $table->string('mata_uang', 10)->nullable()->default('IDR')->after('up');
            $table->integer('tahun')->nullable()->after('mata_uang');
            $table->integer('kuartal')->nullable()->after('tahun');
        });
    }

    public function down(): void
    {
        Schema::table('investment_manager_periods', function (Blueprint $table) {
            $table->dropColumn(['mata_uang', 'tahun', 'kuartal']);
        });

        Schema::table('investment_managers', function (Blueprint $table) {
            $table->dropColumn([
                'kode_ojk', 'address', 'phone', 'email', 'website',
                'commissioner_president', 'commissioners', 'director_president',
                'directors', 'shareholders', 'last_updated_at', 'description',
            ]);
        });
    }
};
