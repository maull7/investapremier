{{-- Tab: Analisa AI untuk Lapkeu (Saham / Obligasi) --}}
<div x-show="mode==='ai'" class="p-6 space-y-4">
    <p class="text-sm text-muted">Isi data laporan keuangan di tab <strong>Input Manual</strong> terlebih dahulu, lalu klik <strong>Jalankan Analisa AI</strong> untuk mendapatkan analisa otomatis.</p>

    <button type="button" @click="runAiPreview()"
        :disabled="aiLoading"
        class="px-4 py-2.5 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary/90 disabled:opacity-50">
        <span x-show="!aiLoading">Jalankan Analisa AI</span>
        <span x-show="aiLoading">Memproses...</span>
    </button>

    <div x-show="aiError" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg p-3" x-text="aiError"></div>

    <template x-if="aiResult">
        <div class="bg-white rounded-xl border border-line p-6 space-y-4">
            <div class="flex items-center gap-2 mb-2">
                <h3 class="font-semibold text-primary">Analisa AI</h3>
                <span class="ml-auto text-xs text-muted bg-[#f1f5f9] px-2 py-1 rounded-full">Powered by Groq</span>
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
