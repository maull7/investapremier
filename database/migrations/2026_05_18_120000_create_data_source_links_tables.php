<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_source_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reksa_dana_id')->nullable()->constrained('reksa_dana')->nullOnDelete();
            $table->string('nama_sumber');
            $table->string('jenis_akses')->default('public'); // public, login, subscription
            $table->string('metode_pengambilan')->default('manual'); // manual, api, auto_download, scrape
            $table->text('catatan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('login_username')->nullable();
            $table->text('login_password')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->string('last_sync_status')->default('never'); // never, success, failed
            $table->text('last_sync_message')->nullable();
            $table->timestamps();
        });

        Schema::create('data_source_link_urls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_source_link_id')->constrained('data_source_links')->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->string('url', 2048);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('data_source_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_source_link_id')->constrained('data_source_links')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status'); // success, failed
            $table->text('message')->nullable();
            $table->unsignedInteger('rows_imported')->default(0);
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_source_sync_logs');
        Schema::dropIfExists('data_source_link_urls');
        Schema::dropIfExists('data_source_links');
    }
};
