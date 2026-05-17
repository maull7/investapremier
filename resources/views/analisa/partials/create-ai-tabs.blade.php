{{-- Tab: Analisa AI --}}
<div x-show="mode==='ai'" class="p-6 space-y-4">
    <p class="text-sm text-muted">Pastikan <strong>Nama</strong> dan <strong>Jenis Reksa Dana</strong> sudah diisi di bagian atas form. Analisa AI memakai data dari PDF/Excel/Manual. Hasilnya bisa diterapkan ke tab Input Manual.</p>
    <button type="button" @click="runAiPreview()"
        :disabled="aiLoading"
        class="px-4 py-2.5 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary/90 disabled:opacity-50">
        <span x-show="!aiLoading">Jalankan Analisa AI</span>
        <span x-show="aiLoading">Memproses...</span>
    </button>
    <div x-show="aiError" class="text-sm text-red-600" x-text="aiError"></div>
    <template x-if="aiResult">
        <div class="space-y-4 border-t border-line pt-4">
            <button type="button" @click="applyAiToManual()"
                class="px-4 py-2 bg-accent text-white rounded-lg text-sm font-semibold hover:bg-accent/90">
                Terapkan ke Input Manual
            </button>
            <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-line" x-text="aiResult.raw"></div>
        </div>
    </template>
</div>

{{-- Tab: Analisa AI Plus --}}
<div x-show="mode==='ai-plus'" class="p-6 space-y-4">
    <p class="text-sm text-muted">Analisa AI Plus membutuhkan data <strong>Input Manual yang lengkap</strong> (AUM, sektor, efek, dan kinerja bulanan).</p>

    <div x-show="!isPlusManualReady()" class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-900 space-y-2">
        <p class="font-semibold">Data Input Manual belum lengkap</p>
        <p class="text-amber-800">Lengkapi bagian berikut di tab <strong>Input Manual</strong> sebelum menjalankan Analisa AI Plus:</p>
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
