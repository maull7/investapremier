{{-- Tab: Analisa AI --}}
<div x-show="mode==='ai'" class="p-6 space-y-4">
    <p class="text-sm text-muted">Analisa AI menggunakan data dari PDF FFS yang sudah diupload. Klik <strong>Jalankan Analisa AI</strong>, lalu gunakan tombol <strong>Isi Input Manual</strong> untuk mengisi form dari hasil analisa.</p>
    <button type="button" @click="runAiPreview()"
        :disabled="aiLoading"
        class="px-4 py-2.5 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary/90 disabled:opacity-50">
        <span x-show="!aiLoading">Jalankan Analisa AI</span>
        <span x-show="aiLoading">Memproses...</span>
    </button>
    <div x-show="aiError" class="text-sm text-red-600" x-text="aiError"></div>
    <template x-if="aiResult">
        <div class="bg-white rounded-xl border border-line p-6 space-y-0">
            <div class="flex items-center gap-2 mb-4">
                <span class="text-lg">🤖</span>
                <h3 class="font-semibold text-primary">Analisa AI</h3>
                <span class="ml-auto text-xs text-muted bg-[#f1f5f9] px-2 py-1 rounded-full">Powered by OpenAI</span>
            </div>

            {{-- Tombol terapkan --}}
            <div class="mb-5">
                <button type="button" @click="applyAiToManual()"
                    class="px-4 py-2 bg-accent text-white rounded-lg text-sm font-semibold hover:bg-accent/90 transition">
                    ✦ Isi Input Manual
                </button>
                <p class="text-xs text-muted mt-1">Sektor, efek, dan data lainnya dari hasil AI akan mengisi tab Input Manual.</p>
            </div>

            <template x-if="aiResult.parsed?.ringkasan_utama">
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-primary mb-2">Ringkasan Utama</h4>
                    <div class="text-sm text-gray-700 leading-relaxed" x-text="aiResult.parsed.ringkasan_utama"></div>
                </div>
            </template>

            <template x-if="aiResult.parsed?.alokasi_aset?.length">
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-primary mb-2">Alokasi Aset</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-[#f8fafc] border-b border-line">
                                <tr>
                                    <th class="text-left px-4 py-2.5 font-semibold text-primary">Kategori</th>
                                    <th class="text-right px-4 py-2.5 font-semibold text-primary">Persentase</th>
                                    <th class="text-left px-4 py-2.5 font-semibold text-primary">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                <template x-for="aa in aiResult.parsed.alokasi_aset" :key="aa.kategori">
                                    <tr>
                                        <td class="px-4 py-2.5 font-medium" x-text="aa.kategori"></td>
                                        <td class="px-4 py-2.5 text-right font-mono" x-text="Number(aa.persentase).toFixed(2) + '%'"></td>
                                        <td class="px-4 py-2.5 text-muted" x-text="aa.keterangan || '-'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>

            <template x-if="aiResult.parsed?.daftar_efek?.length">
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-primary mb-2">Daftar Efek & Persentase</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-[#f8fafc] border-b border-line">
                                <tr>
                                    <th class="text-left px-4 py-2.5 font-semibold text-primary">Kode</th>
                                    <th class="text-left px-4 py-2.5 font-semibold text-primary">Nama Efek</th>
                                    <th class="text-left px-4 py-2.5 font-semibold text-primary">Sektor</th>
                                    <th class="text-right px-4 py-2.5 font-semibold text-primary">Bobot (%)</th>
                                    <th class="text-right px-4 py-2.5 font-semibold text-primary">Kontribusi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                <template x-for="de in aiResult.parsed.daftar_efek" :key="de.kode_efek">
                                    <tr>
                                        <td class="px-4 py-2.5 font-mono text-xs" x-text="de.kode_efek"></td>
                                        <td class="px-4 py-2.5" x-text="de.nama_efek"></td>
                                        <td class="px-4 py-2.5 text-muted" x-text="de.sektor || '-'"></td>
                                        <td class="px-4 py-2.5 text-right font-mono" x-text="Number(de.bobot).toFixed(2) + '%'"></td>
                                        <td class="px-4 py-2.5 text-right font-mono" x-text="de.kontribusi_kinerja != null ? (de.kontribusi_kinerja >= 0 ? '+' : '') + de.kontribusi_kinerja + '%' : '-'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>

            <template x-if="aiResult.parsed?.analisa_risiko">
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-primary mb-2">Analisa Risiko</h4>
                    <div class="text-sm text-gray-700 leading-relaxed" x-text="aiResult.parsed.analisa_risiko"></div>
                </div>
            </template>

            <template x-if="aiResult.parsed?.rekomendasi_investor">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-blue-800 mb-1">Rekomendasi Investor</h4>
                    <div class="text-sm text-blue-700" x-text="aiResult.parsed.rekomendasi_investor"></div>
                </div>
            </template>

            <template x-if="!aiResult.parsed || Object.keys(aiResult.parsed).length === 0">
                <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-line" x-text="aiResult.raw"></div>
            </template>
        </div>
    </template>
</div>

{{-- Tab: Analisa AI Plus --}}
<div x-show="mode==='ai-plus'" class="p-6 space-y-4">
    <p class="text-sm text-muted">Analisa AI Plus memakai data dari tab <strong>Input Lengkap</strong> sebagai acuan utama: AUM, MarCap 10 saham terbesar, alokasi sektor, komposisi sektor, dan daftar efek.</p>

    <div x-show="!isPlusManualReady()" class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-900 space-y-2">
        <p class="font-semibold">Data Input Lengkap belum lengkap</p>
        <p class="text-amber-800">Lengkapi bagian berikut di tab <strong>Input Lengkap</strong> sebelum menjalankan Analisa AI Plus:</p>
        <ul class="list-disc list-inside space-y-1 text-amber-900">
            <template x-for="item in plusMissingList()" :key="item">
                <li x-text="item"></li>
            </template>
        </ul>
    </div>

    <button type="button" @click="runAiPlusPreview()"
        :disabled="aiPlusLoading || !isPlusManualReady()"
        class="px-4 py-2.5 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed">
        <span x-show="!aiPlusLoading">Jalankan Analisa AI Plus</span>
        <span x-show="aiPlusLoading">Memproses...</span>
    </button>
    <p x-show="!isPlusManualReady()" class="text-xs text-muted">Tombol aktif setelah semua data di atas terisi.</p>

    <div x-show="aiPlusError" class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700 whitespace-pre-line" x-text="aiPlusError"></div>
    <template x-if="aiPlusResult">
        <div class="space-y-3 border-t border-line pt-4 text-sm text-gray-700 leading-relaxed whitespace-pre-line" x-text="aiPlusResult.raw"></div>
    </template>
</div>
