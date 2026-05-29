<div class="border border-line rounded-lg p-4 space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Emiten</label>
            <input type="text" x-model="kodeObligasi" placeholder="cth: BBCA"
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm uppercase">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Emiten</label>
            <input type="text" x-model="namaEmiten"
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Analisa</label>
        <div class="flex flex-wrap gap-4 text-sm">
            <label class="inline-flex items-center gap-2">
                <input type="radio" value="periode" x-model="jenisAnalisa" class="text-primary focus:ring-primary/20">
                <span>Periode</span>
            </label>
            <label class="inline-flex items-center gap-2">
                <input type="radio" value="tahunan" x-model="jenisAnalisa" class="text-primary focus:ring-primary/20">
                <span>Tahunan</span>
            </label>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div x-show="jenisAnalisa === 'periode'">
            <label class="block text-sm font-medium text-gray-700 mb-1">Periode LapKeu</label>
            <input type="text" x-model="periodeAnalisa" placeholder="cth: 202503" maxlength="6" pattern="[0-9]{6}"
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
        </div>
        <div x-show="jenisAnalisa === 'tahunan'" x-cloak>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
            <input type="text" x-model="tahunAnalisa" placeholder="cth: 2025" maxlength="4" pattern="[0-9]{4}"
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <button type="button" @click="processKeuanganEmiten()" :disabled="sourceLoading || !isAnalisaSourceReady()"
            class="px-4 py-2.5 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed">
            <span x-show="!sourceLoading">Proses</span>
            <span x-show="sourceLoading">Memproses...</span>
        </button>
        <p class="text-xs text-muted">Ambil data dari Daftar Obligasi -> Keuangan Emiten dan masukkan ke form analisa.</p>
    </div>

    <div x-show="sourceMessage" class="text-sm rounded-lg border p-3"
        :class="sourceOk ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700'"
        x-text="sourceMessage"></div>
</div>
