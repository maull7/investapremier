@extends($layout ?? 'layouts.user')

@section('content')
<div class="max-w-5xl" x-data="lapkeuForm('{{ $previewAiRoute }}', '{{ $parsePdfRoute }}')">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-primary">Submit Analisa {{ $productLabel }}</h1>
        <p class="text-sm text-muted mt-0.5">Isi data laporan keuangan obligasi secara manual atau upload Excel</p>
    </div>

    @if ($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-6">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form id="lapkeu-form" method="POST" action="{{ $storeRoute }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <input type="hidden" name="input_mode" :value="mode === 'ai' ? 'manual' : mode">
        <input type="hidden" name="ai_narasi" :value="aiResult?.raw || ''">
        <input type="hidden" name="ai_output" :value="aiResult ? JSON.stringify(aiResult.parsed || {}) : ''">
        <input type="hidden" name="pdf_lapkeu_path" x-model="pdfPath">

        {{-- Info Dasar --}}
        <div class="bg-white rounded-xl border border-line p-6 space-y-4">
            <h3 class="font-semibold text-primary">Informasi Obligasi</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Obligasi <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_obligasi" id="nama_obligasi" value="{{ old('nama_obligasi') }}" required
                        class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Obligasi</label>
                    <input type="text" name="kode_obligasi" value="{{ old('kode_obligasi') }}" placeholder="cth: BBCA01"
                        class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Emiten</label>
                    <input type="text" name="nama_emiten" value="{{ old('nama_emiten') }}"
                        class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                    <select name="rating" class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                        <option value="">Pilih Rating</option>
                        @foreach(['AAA','AA+','AA','AA-','A+','A','A-','BBB+','BBB','BBB-','BB+','BB','BB-','B+','B','B-','CCC','D','idAAA','idAA+','idAA','idAA-','idA+','idA','idA-','idBBB+','idBBB','idBBB-'] as $r)
                            <option value="{{ $r }}" {{ old('rating') === $r ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kupon (%)</label>
                    <input type="number" name="kupon" step="0.0001" value="{{ old('kupon') }}" placeholder="cth: 7.5"
                        class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">YTM (%)</label>
                    <input type="number" name="ytm" step="0.0001" value="{{ old('ytm') }}" placeholder="cth: 7.2"
                        class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mata Uang</label>
                    <select name="mata_uang" class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                        @foreach(['IDR','USD','EUR','SGD'] as $c)
                            <option value="{{ $c }}" {{ (old('mata_uang','IDR') === $c) ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Periode</label>
                    <input type="text" name="periode" value="{{ old('periode') }}" placeholder="cth: Q4 2024"
                        class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                </div>
            </div>
        </div>

        {{-- Tabs mode input --}}
        <div class="bg-white rounded-xl border border-line overflow-hidden">
            <div class="flex border-b border-line overflow-x-auto">
                <button type="button" @click="mode='manual'"
                    :class="mode==='manual' ? 'border-b-2 border-primary text-primary font-semibold' : 'text-muted hover:text-primary'"
                    class="px-6 py-3.5 text-sm transition whitespace-nowrap">Input Manual</button>
                <button type="button" @click="mode='excel'"
                    :class="mode==='excel' ? 'border-b-2 border-primary text-primary font-semibold' : 'text-muted hover:text-primary'"
                    class="px-6 py-3.5 text-sm transition whitespace-nowrap">Upload Excel</button>
                <button type="button" @click="mode='ai'"
                    :class="mode==='ai' ? 'border-b-2 border-primary text-primary font-semibold' : 'text-muted hover:text-primary'"
                    class="px-6 py-3.5 text-sm transition whitespace-nowrap">Analisa AI</button>
                <button type="button" @click="mode='pdf'"
                    :class="mode==='pdf' ? 'border-b-2 border-primary text-primary font-semibold' : 'text-muted hover:text-primary'"
                    class="px-6 py-3.5 text-sm transition whitespace-nowrap">PDF Lapkeu</button>
            </div>

            {{-- TAB: MANUAL --}}
            <div x-show="mode==='manual'" class="p-6 space-y-6">
                @include('analisa-obligasi.partials.form-neraca')
                @include('analisa-obligasi.partials.form-laba-rugi')
                @include('analisa-obligasi.partials.form-arus-kas')
            </div>

            {{-- TAB: EXCEL --}}
            <div x-show="mode==='excel'" class="p-6 space-y-5">
                <div class="flex items-center gap-3 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>Download template Excel terlebih dahulu, isi data sesuai format, lalu upload kembali.
                        <a href="{{ $templateRoute }}" class="font-semibold underline ml-1">Download Template</a>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload File Excel (.xlsx)</label>
                    <input type="file" name="file_excel" accept=".xlsx,.xls"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                    <p class="text-xs text-muted mt-1">Format: Excel (.xlsx/.xls). Gunakan template yang tersedia.</p>
                </div>
            </div>

            {{-- TAB: AI --}}
            @include('analisa-lapkeu.partials.create-ai-tab')

            {{-- TAB: PDF --}}
            <div x-show="mode==='pdf'" class="p-6 space-y-5">
                <div class="flex items-center gap-3 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Upload PDF laporan keuangan. AI akan mengekstrak data dan mengisi form otomatis.
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload PDF Laporan Keuangan</label>
                    <input type="file" id="pdf-parse-input" accept="application/pdf"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                </div>
                <button type="button" @click="parsePdf()"
                    :disabled="pdfLoading"
                    class="px-4 py-2.5 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary/90 disabled:opacity-50">
                    <span x-show="!pdfLoading">Ekstrak & Isi Form Otomatis</span>
                    <span x-show="pdfLoading">Memproses PDF...</span>
                </button>
                <div x-show="pdfError" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg p-3" x-text="pdfError"></div>
                <div x-show="pdfSuccess" class="text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg p-3" x-text="pdfSuccess"></div>
            </div>
        </div>

        {{-- Upload PDF Lapkeu --}}
        <div class="bg-white rounded-xl border border-line p-6 space-y-3">
            <h3 class="font-semibold text-primary">Upload PDF Laporan Keuangan <span class="text-xs font-normal text-muted">(opsional)</span></h3>
            <input type="file" name="pdf_lapkeu" accept="application/pdf"
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
            <p class="text-xs text-muted">File PDF laporan keuangan untuk referensi. Maks 10MB.</p>
        </div>

        {{-- Catatan --}}
        <div class="bg-white rounded-xl border border-line p-6">
            <h3 class="font-semibold text-primary mb-3">Catatan Analisa</h3>
            <textarea name="catatan" rows="4" placeholder="Tambahkan catatan atau konteks analisa..."
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">{{ old('catatan') }}</textarea>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ $cancelRoute }}" class="px-4 py-2 text-sm text-muted border border-line rounded-lg hover:bg-[#f1f5f9] transition">Batal</a>
            <button type="submit" class="px-6 py-2 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary/90 transition">
                Submit Analisa
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function lapkeuForm(previewAiUrl, parsePdfUrl) {
    return {
        mode: @json(old('input_mode', 'manual')),
        aiLoading: false,
        aiError: '',
        aiResult: null,
        previewAiUrl: previewAiUrl,
        pdfLoading: false,
        pdfError: '',
        pdfSuccess: '',
        pdfPath: '',

        parsePdf() {
            const fileInput = document.getElementById('pdf-parse-input');
            if (!fileInput?.files[0]) {
                this.pdfError = 'Pilih file PDF terlebih dahulu.';
                return;
            }
            this.pdfLoading = true;
            this.pdfError = '';
            this.pdfSuccess = '';
            const fd = new FormData();
            fd.append('file_pdf', fileInput.files[0]);
            fd.append('_token', document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}');
            fetch(parsePdfUrl, { method: 'POST', headers: { 'Accept': 'application/json' }, body: fd })
                .then(async r => {
                    const resp = await r.json();
                    if (!r.ok || !resp.success) { this.pdfError = resp.message || 'Gagal'; return; }
                    const d = resp.data;
                    if (d.pdf_lapkeu_path) this.pdfPath = d.pdf_lapkeu_path;
                    const set = (name, val) => { const el = document.querySelector(`[name="${name}"]`); if (el && val != null) el.value = val; };
                    set('nama_obligasi', d.nama_obligasi || d.nama_perusahaan);
                    set('kode_obligasi', d.kode_obligasi || d.kode_saham);
                    set('nama_emiten', d.nama_emiten);
                    set('periode', d.periode);
                    if (d.rating) { const sel = document.querySelector('[name="rating"]'); if (sel) { [...sel.options].forEach(o => { if (o.value === d.rating) o.selected = true; }); } }
                    if (d.mata_uang) { const sel = document.querySelector('[name="mata_uang"]'); if (sel) { [...sel.options].forEach(o => { if (o.value === d.mata_uang) o.selected = true; }); } }
                    const numFields = ['total_asset','current_asset','cash_equivalents','account_receivable','inventories','fixed_asset','total_liabilities','current_liabilities','long_term_loans','equity','net_revenue','gross_income','ebit','ebitda','interest_expense','net_income','cash_flows_operating_activities','cash_flows_investment','cash_flows_financing'];
                    numFields.forEach(f => set(f, d[f]));
                    this.pdfSuccess = 'Data berhasil diekstrak dari PDF. Periksa dan lengkapi data yang belum terisi, lalu submit.';
                    this.mode = 'manual';
                })
                .catch(e => { this.pdfError = e.message || 'Gagal'; })
                .finally(() => { this.pdfLoading = false; });
        },

        runAiPreview() {
            const form = document.getElementById('lapkeu-form');
            const nama = document.getElementById('nama_obligasi')?.value?.trim();
            if (!nama) {
                this.aiError = 'Isi Nama Obligasi di bagian Informasi Obligasi terlebih dahulu.';
                return;
            }
            this.aiLoading = true;
            this.aiError = '';
            const fd = new FormData(form);
            fetch(this.previewAiUrl, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: fd,
            })
            .then(async r => {
                const resp = await r.json();
                if (!r.ok || !resp.success) {
                    this.aiError = resp.message || 'Gagal memproses';
                    return;
                }
                this.aiResult = resp.data;
            })
            .catch(e => { this.aiError = e.message || 'Gagal memproses'; })
            .finally(() => { this.aiLoading = false; });
        },
    };
}
</script>
@endpush
@endsection
