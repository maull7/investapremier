{{-- Tab: Analisa AI untuk Lapkeu (Saham / Obligasi) --}}
<div x-show="mode==='ai'" class="p-6 space-y-4">
    <div class="flex items-center gap-3 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
        <div>
            Upload PDF laporan keuangan, lalu klik <strong>Jalankan Analisa AI</strong>.
            Data akan otomatis diekstrak ke form Input Manual dan AI akan menganalisa.
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Upload PDF Laporan Keuangan</label>
        <input type="file" id="ai-pdf-parse-input" accept="application/pdf" @change="onPdfSelected($event)"
            class="block w-full text-sm text-gray-500 border border-gray-300 rounded-lg px-3 py-2 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
        <p class="text-xs text-muted mt-1">Format: PDF, maks 20MB. Data akan diekstrak dan mengisi form otomatis.</p>
    </div>

    <div class="flex flex-wrap gap-3 text-sm">
        <label class="inline-flex items-center gap-2">
            <input type="radio" value="text" x-model="pdfScanMode" class="text-primary focus:ring-primary/20">
            <span>PDF parser teks</span>
        </label>
        <label class="inline-flex items-center gap-2">
            <input type="radio" value="vision" x-model="pdfScanMode" class="text-primary focus:ring-primary/20">
            <span>Scan AI Vision</span>
        </label>
    </div>

    <button type="button" @click="runAiFromPdf()"
        :disabled="aiLoading || !aiPdfFile"
        class="px-4 py-2.5 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed">
        <span x-show="!aiLoading">Jalankan Analisa AI</span>
        <span x-show="aiLoading">Memproses...</span>
    </button>
    <p x-show="!aiPdfFile" class="text-xs text-muted">Pilih file PDF terlebih dahulu.</p>

    <div x-show="aiParseLoading" class="flex items-center gap-2 text-sm text-muted">
        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        <span>Membaca & mengekstrak PDF di background...</span>
    </div>

    <div x-show="aiParseError" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg p-3" x-text="aiParseError"></div>

    <div x-show="aiParseSuccess" class="text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg p-3" x-text="aiParseSuccess"></div>

    <div x-show="aiError" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg p-3" x-text="aiError"></div>

    <template x-if="aiResult">
        <div class="bg-white rounded-xl border border-line p-6 space-y-4">
            <div class="flex items-center gap-2 mb-2">
                <h3 class="font-semibold text-primary">Analisa AI</h3>
                <span class="ml-auto text-xs text-muted bg-[#f1f5f9] px-2 py-1 rounded-full">Powered by OpenAI</span>
            </div>

            <div class="mb-4">
                <button type="button" @click="applyAiToManual()"
                    class="px-4 py-2 bg-accent text-white rounded-lg text-sm font-semibold hover:bg-accent/90 transition">
                    ✦ Isi Input Manual
                </button>
                <p class="text-xs text-muted mt-1">Data dari PDF sudah diisikan ke form. Klik untuk beralih ke tab Input Manual dan review sebelum submit.</p>
            </div>

            <template x-if="aiResult.parsed?.ringkasan_utama">
                <div>
                    <h4 class="text-sm font-semibold text-primary mb-1">Ringkasan Utama</h4>
                    <p class="text-sm text-gray-700 leading-relaxed" x-text="aiResult.parsed.ringkasan_utama"></p>
                </div>
            </template>

            <template x-if="aiResult.parsed?.analisa_neraca">
                <div>
                    <h4 class="text-sm font-semibold text-primary mb-1">Analisa Neraca</h4>
                    <p class="text-sm text-gray-700 leading-relaxed" x-text="aiResult.parsed.analisa_neraca"></p>
                </div>
            </template>

            <template x-if="aiResult.parsed?.analisa_laba_rugi">
                <div>
                    <h4 class="text-sm font-semibold text-primary mb-1">Analisa Laba Rugi</h4>
                    <p class="text-sm text-gray-700 leading-relaxed" x-text="aiResult.parsed.analisa_laba_rugi"></p>
                </div>
            </template>

            <template x-if="aiResult.parsed?.analisa_arus_kas">
                <div>
                    <h4 class="text-sm font-semibold text-primary mb-1">Analisa Arus Kas</h4>
                    <p class="text-sm text-gray-700 leading-relaxed" x-text="aiResult.parsed.analisa_arus_kas"></p>
                </div>
            </template>

            <template x-if="aiResult.parsed?.rasio_keuangan">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <template x-if="aiResult.parsed.rasio_keuangan.current_ratio != null">
                        <div class="bg-[#f8fafc] rounded-lg p-3 border border-line">
                            <p class="text-xs text-muted">Current Ratio</p>
                            <p class="text-lg font-bold text-primary mt-1" x-text="Number(aiResult.parsed.rasio_keuangan.current_ratio).toFixed(2) + 'x'"></p>
                        </div>
                    </template>
                    <template x-if="aiResult.parsed.rasio_keuangan.debt_to_equity != null">
                        <div class="bg-[#f8fafc] rounded-lg p-3 border border-line">
                            <p class="text-xs text-muted">DER</p>
                            <p class="text-lg font-bold text-primary mt-1" x-text="Number(aiResult.parsed.rasio_keuangan.debt_to_equity).toFixed(2) + 'x'"></p>
                        </div>
                    </template>
                    <template x-if="aiResult.parsed.rasio_keuangan.net_profit_margin != null">
                        <div class="bg-[#f8fafc] rounded-lg p-3 border border-line">
                            <p class="text-xs text-muted">Net Margin</p>
                            <p class="text-lg font-bold text-primary mt-1" x-text="Number(aiResult.parsed.rasio_keuangan.net_profit_margin).toFixed(2) + '%'"></p>
                        </div>
                    </template>
                    <template x-if="aiResult.parsed.rasio_keuangan.roe != null">
                        <div class="bg-[#f8fafc] rounded-lg p-3 border border-line">
                            <p class="text-xs text-muted">ROE</p>
                            <p class="text-lg font-bold text-primary mt-1" x-text="Number(aiResult.parsed.rasio_keuangan.roe).toFixed(2) + '%'"></p>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="aiResult.parsed?.rekomendasi">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-blue-800 mb-1">Rekomendasi</h4>
                    <p class="text-sm text-blue-700" x-text="aiResult.parsed.rekomendasi"></p>
                </div>
            </template>

            <template x-if="!aiResult.parsed || Object.keys(aiResult.parsed).length === 0">
                <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-line" x-text="aiResult.raw"></div>
            </template>
        </div>
    </template>
</div>
