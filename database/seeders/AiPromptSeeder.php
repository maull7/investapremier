<?php

namespace Database\Seeders;

use App\Models\AiPrompt;
use Illuminate\Database\Seeder;

class AiPromptSeeder extends Seeder
{
    public function run(): void
    {
        $prompts = [
            [
                'key'         => 'system_analisa',
                'label'       => 'System Prompt — Analisa AI',
                'description' => 'Peran dan instruksi dasar AI saat membuat Analisa AI standar.',
                'value'       => 'Kamu adalah analis investasi profesional Indonesia yang ahli dalam analisa Reksa Dana. Gunakan Bahasa Indonesia yang baik. Keluarkan jawaban dalam format JSON valid tanpa teks tambahan.',
            ],
            [
                'key'         => 'system_analisa_plus',
                'label'       => 'System Prompt — Analisa AI Plus',
                'description' => 'Peran dan instruksi dasar AI saat membuat Analisa AI Plus.',
                'value'       => 'Kamu adalah analis investasi senior Indonesia yang ahli analisa mendalam Reksa Dana. Gunakan Bahasa Indonesia. Keluarkan jawaban dalam format JSON valid tanpa teks tambahan.',
            ],
            [
                'key'         => 'instruksi_analisa',
                'label'       => 'Instruksi Analisa AI',
                'description' => 'Instruksi yang dikirim ke AI setelah data reksa dana. Menentukan apa saja yang harus dianalisa.',
                'value'       => "Berdasarkan data di atas, buatkan analisa dalam format JSON dengan struktur EXACT berikut (jangan tambah atau kurangi field):\n{\n  \"ringkasan_utama\": \"Ringkasan kinerja keseluruhan dalam 2-3 paragraf, mencakup return, komposisi sektor, dan posisi portfolio secara umum\",\n  \"alokasi_aset\": [\n    {\"kategori\": \"Nama Sektor/Kategori Aset\", \"persentase\": 25.5, \"keterangan\": \"Penjelasan singkat tentang alokasi ini\"}\n  ],\n  \"daftar_efek\": [\n    {\"kode_efek\": \"BBCA\", \"nama_efek\": \"Bank Central Asia Tbk.\", \"sektor\": \"Keuangan\", \"bobot\": 12.5, \"kontribusi_kinerja\": 2.3}\n  ],\n  \"analisa_risiko\": \"Analisa risiko likuiditas, durasi, rating obligasi, dan bank dalam 1-2 paragraf\",\n  \"rekomendasi_investor\": \"Rekomendasi singkat untuk investor berdasarkan profil risiko dan kondisi portfolio\"\n}\n\nPETUNJUK PENTING:\n- Isi `alokasi_aset` dengan data komposisi sektor yang sudah diberikan\n- Isi `daftar_efek` dengan data efek yang sudah diberikan\n- Gunakan Bahasa Indonesia yang baik dan benar\n- Output HANYA JSON valid, tanpa teks lain, tanpa markdown\n- Pastikan JSON bisa diparse dengan json_decode()",
            ],
            [
                'key'         => 'instruksi_analisa_plus',
                'label'       => 'Instruksi Analisa AI Plus',
                'description' => 'Instruksi yang dikirim ke AI untuk Analisa AI Plus. Menentukan kedalaman analisa.',
                'value'       => "Berdasarkan data Input Manual lengkap di atas, buatkan analisa mendalam (Analisa AI Plus) dalam format JSON:\n{\n  \"ringkasan_utama\": \"Ringkasan eksekutif 2-3 paragraf dengan metrik kunci\",\n  \"analisa_kinerja\": \"Analisa kinerja bulanan, Sharpe, RAR, dan tren return\",\n  \"analisa_risiko\": \"Analisa risiko obligasi, bank, durasi, rating, konsentrasi sektor\",\n  \"analisa_likuiditas\": \"Analisa likuiditas portofolio dan rasio AUM vs MarCap 10 efek\",\n  \"rekomendasi_investor\": \"Rekomendasi investasi spesifik berdasarkan profil risiko\",\n  \"metrik_saran\": {\n    \"sharpe_ratio\": null,\n    \"rar\": null,\n    \"liquidity_ratio\": null,\n    \"durasi_rata_rata\": null\n  }\n}\n\nPETUNJUK:\n- Gunakan semua data sektor, efek, kinerja, obligasi, dan bank yang tersedia\n- Jika metrik tidak bisa dihitung, jelaskan di narasi dan set null di metrik_saran\n- Output HANYA JSON valid tanpa markdown",
            ],
        ];

        foreach ($prompts as $prompt) {
            AiPrompt::updateOrCreate(['key' => $prompt['key']], $prompt);
        }
    }
}
