<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate existing string values to JSON array
        DB::table('analisa_reksa_dana')
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->get(['id', 'kategori'])
            ->each(function ($row) {
                // If already JSON, skip
                $decoded = json_decode($row->kategori, true);
                if (is_array($decoded)) return;

                DB::table('analisa_reksa_dana')
                    ->where('id', $row->id)
                    ->update(['kategori' => json_encode([$row->kategori])]);
            });

        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            $table->json('kategori')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Convert back to string (take first element)
        DB::table('analisa_reksa_dana')
            ->whereNotNull('kategori')
            ->get(['id', 'kategori'])
            ->each(function ($row) {
                $decoded = json_decode($row->kategori, true);
                $value = is_array($decoded) ? ($decoded[0] ?? null) : $row->kategori;
                DB::table('analisa_reksa_dana')
                    ->where('id', $row->id)
                    ->update(['kategori' => $value]);
            });

        Schema::table('analisa_reksa_dana', function (Blueprint $table) {
            $table->string('kategori')->nullable()->change();
        });
    }
};
