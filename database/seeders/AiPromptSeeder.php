<?php

namespace Database\Seeders;

use App\Models\AiPrompt;
use Illuminate\Database\Seeder;

class AiPromptSeeder extends Seeder
{
    public function run(): void
    {
        $prompts = [
            // ── Reksa Dana ──
            [
                'key'         => 'system_analisa',
                'group'       => 'reksa-dana',
                'label'       => 'System Prompt — Analisa AI',
                'description' => 'Peran dan instruksi dasar AI saat membuat Analisa AI standar untuk reksa dana.',
                'value'       => 'Kamu adalah analis investasi profesional Indonesia yang ahli dalam analisa Reksa Dana. Gunakan Bahasa Indonesia yang baik. Keluarkan jawaban dalam format JSON valid tanpa teks tambahan.',
                'sort_order'  => 10,
            ],
            [
                'key'         => 'instruksi_analisa',
                'group'       => 'reksa-dana',
                'label'       => 'Instruksi Analisa AI',
                'description' => 'Instruksi yang dikirim ke AI setelah data reksa dana. Menentukan struktur output JSON.',
                'value'       => "Berdasarkan data di atas, buatkan analisa dalam format JSON dengan struktur EXACT berikut (jangan tambah atau kurangi field):\n{\n  \"ringkasan_utama\": \"Ringkasan kinerja keseluruhan dalam 2-3 paragraf, mencakup return, komposisi sektor, dan posisi portfolio secara umum\",\n  \"alokasi_aset\": [\n    {\"kategori\": \"Nama Sektor/Kategori Aset\", \"persentase\": 25.5, \"keterangan\": \"Penjelasan singkat tentang alokasi ini\"}\n  ],\n  \"daftar_efek\": [\n    {\"kode_efek\": \"BBCA\", \"nama_efek\": \"Bank Central Asia Tbk.\", \"sektor\": \"Keuangan\", \"bobot\": 12.5, \"kontribusi_kinerja\": 2.3}\n  ],\n  \"analisa_risiko\": \"Analisa risiko likuiditas, durasi, rating obligasi, dan bank dalam 1-2 paragraf\",\n  \"rekomendasi_investor\": \"Rekomendasi singkat untuk investor berdasarkan profil risiko dan kondisi portfolio\"\n}\n\nPETUNJUK PENTING:\n- Isi `alokasi_aset` dengan data komposisi sektor yang sudah diberikan\n- Isi `daftar_efek` dengan data efek yang sudah diberikan\n- Gunakan Bahasa Indonesia yang baik dan benar\n- Output HANYA JSON valid, tanpa teks lain, tanpa markdown\n- Pastikan JSON bisa diparse dengan json_decode()",
                'sort_order'  => 20,
            ],
            [
                'key'         => 'system_analisa_plus',
                'group'       => 'reksa-dana',
                'label'       => 'System Prompt — Analisa AI Plus',
                'description' => 'Peran dan instruksi dasar AI saat membuat Analisa AI Plus untuk reksa dana.',
                'value'       => 'Kamu adalah analis investasi senior Indonesia yang ahli analisa mendalam Reksa Dana. Gunakan Bahasa Indonesia. Keluarkan jawaban dalam format JSON valid tanpa teks tambahan.',
                'sort_order'  => 30,
            ],
            [
                'key'         => 'instruksi_analisa_plus',
                'group'       => 'reksa-dana',
                'label'       => 'Instruksi Analisa AI Plus',
                'description' => 'Instruksi yang dikirim ke AI untuk Analisa AI Plus. Menentukan kedalaman analisa.',
                'value'       => "Berdasarkan data Input Manual lengkap di atas, buatkan analisa mendalam (Analisa AI Plus) dalam format JSON dengan struktur EXACT berikut:\n{\n  \"ringkasan_utama\": \"Ringkasan eksekutif kondisi investasi dalam 2-3 paragraf\",\n  \"analisa_sektor\": \"Analisa mendalam komposisi sektor, overweight/underweight, tren sektor\",\n  \"analisa_efek\": \"Analisa saham-saham terbesar: valuasi, prospek, risiko masing-masing\",\n  \"analisa_kinerja\": \"Analisa return historis: tren, volatilitas, konsistensi\",\n  \"analisa_risiko\": \"Analisa risiko likuiditas, konsentrasi, durasi, rating\",\n  \"rekomendasi\": \"Rekomendasi strategis untuk investor: hold/add/reduce\"\n}\n\nOutput HANYA JSON valid, tanpa teks lain.",
                'sort_order'  => 40,
            ],
            // ── Perencanaan Investasi ──
            [
                'key'         => 'system_perencanaan_investasi',
                'group'       => 'perencanaan-investasi',
                'label'       => 'System Prompt — Perencanaan Investasi',
                'description' => 'Peran dan instruksi dasar AI saat menganalisis perencanaan investasi.',
                'value'       => 'Kamu adalah AI Financial Planning Assistant yang membantu pengguna merencanakan keuangan dan investasi. Analisa data yang diberikan dan berikan rekomendasi yang jelas, terstruktur, dan mudah dipahami dalam Bahasa Indonesia. Keluarkan jawaban dalam format JSON valid tanpa teks tambahan.',
                'sort_order'  => 10,
            ],
            [
                'key'         => 'instruksi_perencanaan_investasi',
                'group'       => 'perencanaan-investasi',
                'label'       => 'Instruksi Perencanaan Investasi',
                'description' => 'Instruksi yang dikirim ke AI setelah data perencanaan investasi. Menentukan struktur output JSON.',
                'value'       => "Berdasarkan data perencanaan investasi di atas, buatkan analisis dalam format JSON dengan struktur EXACT berikut:\n{\n  \"ringkasan\": \"Ringkasan kondisi keuangan pengguna dalam 2-3 kalimat\",\n  \"analisa_kebutuhan\": \"Analisa apakah dana yang dibutuhkan sudah realistis atau perlu penyesuaian\",\n  \"rekomendasi_portofolio\": [\n    {\"jenis\": \"Saham\", \"alokasi_persen\": 40, \"keterangan\": \"Penjelasan alokasi saham\"},\n    {\"jenis\": \"Pendapatan Tetap\", \"alokasi_persen\": 30, \"keterangan\": \"Penjelasan alokasi obligasi\"},\n    {\"jenis\": \"Pasar Uang\", \"alokasi_persen\": 20, \"keterangan\": \"Penjelasan alokasi pasar uang\"},\n    {\"jenis\": \"Alternatif\", \"alokasi_persen\": 10, \"keterangan\": \"Penjelasan alokasi alternatif\"}\n  ],\n  \"strategi_investasi\": \"Strategi investasi yang disarankan (bulanan, lumpsum, atau kombinasi)\",\n  \"rekomendasi\": \"Kesimpulan dan langkah selanjutnya\"\n}\n\nOutput HANYA JSON valid.",
                'sort_order'  => 20,
            ],
            // ── Unit Link ──
            [
                'key'         => 'system_analisa_unit_link',
                'group'       => 'unit-link',
                'label'       => 'System Prompt — Analisa Unit Link',
                'description' => 'Peran dan instruksi dasar AI saat menganalisis Unit Link.',
                'value'       => 'Kamu adalah analis investasi profesional Indonesia yang ahli dalam analisa Unit Link. Gunakan Bahasa Indonesia yang baik. Keluarkan jawaban dalam format JSON valid tanpa teks tambahan.',
                'sort_order'  => 10,
            ],
            [
                'key'         => 'instruksi_analisa_unit_link',
                'group'       => 'unit-link',
                'label'       => 'Instruksi Analisa Unit Link',
                'description' => 'Instruksi yang dikirim ke AI setelah data unit link.',
                'value'       => "Berdasarkan data di atas, buatkan analisa Unit Link dalam format JSON dengan struktur EXACT berikut:\n{\n  \"ringkasan_utama\": \"Ringkasan analisa unit link dalam 2-3 paragraf\",\n  \"alokasi_aset\": [{\"kategori\": \"Nama Sektor/Kategori Aset\", \"persentase\": 25.5, \"keterangan\": \"Penjelasan\"}],\n  \"analisa_risiko\": \"Analisa risiko dalam 1-2 paragraf\",\n  \"rekomendasi_investor\": \"Rekomendasi untuk investor\"\n}\n\nOutput HANYA JSON valid.",
                'sort_order'  => 20,
            ],
            // ── Saham ──
            [
                'key'         => 'system_analisa_saham',
                'group'       => 'saham',
                'label'       => 'System Prompt — Analisa Saham',
                'description' => 'Peran dan instruksi dasar AI saat menganalisis saham.',
                'value'       => 'Kamu adalah analis saham profesional Indonesia yang ahli membaca laporan keuangan dan menganalisa saham. Gunakan Bahasa Indonesia yang baik. Keluarkan jawaban dalam format JSON valid tanpa teks tambahan.',
                'sort_order'  => 10,
            ],
            // ── Obligasi ──
            [
                'key'         => 'system_analisa_obligasi',
                'group'       => 'obligasi',
                'label'       => 'System Prompt — Analisa Obligasi',
                'description' => 'Peran dan instruksi dasar AI saat menganalisis obligasi.',
                'value'       => 'Kamu adalah analis obligasi profesional Indonesia yang ahli membaca laporan keuangan dan menganalisa obligasi. Gunakan Bahasa Indonesia yang baik. Keluarkan jawaban dalam format JSON valid tanpa teks tambahan.',
                'sort_order'  => 10,
            ],
        ];

        foreach ($prompts as $prompt) {
            AiPrompt::updateOrCreate(['key' => $prompt['key']], $prompt);
        }
    }
}
