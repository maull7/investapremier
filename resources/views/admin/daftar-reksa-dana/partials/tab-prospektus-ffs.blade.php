@php
    $months = [
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember',
    ];
@endphp

<div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden mb-5" x-data="{
    type: @js(old('document_type', 'prospektus')),
    rdSelected: @js(old('reksa_dana_id') ? (int) old('reksa_dana_id') : null),
    rdOptions: @js($reksaDanaOptions->map(fn($rd) => ['id' => $rd->id, 'label' => ($rd->kode_reksa_dana ? $rd->kode_reksa_dana . ' - ' : '') . $rd->nama_reksa_dana])->values()),
    rdSearch: '',
    rdOpen: false,
    months: @js($months),
    formatMonthYear(dateStr) {
        if (!dateStr) return '—';
        const d = new Date(dateStr);
        return this.months[d.getMonth()] + ' ' + d.getFullYear();
    },
    ffsMonth: @js(old('ffs_month') ? (int) old('ffs_month') : ''),
    ffsYear: @js(old('ffs_year', now()->year)),
    prospektusMonth: @js(old('prospektus_month', '')),
    prospektusYear: @js(old('prospektus_year', now()->year)),
    existingDoc: null,
    checking: false,
    checkUrl: @js(route('admin.daftar-reksa-dana.documents.check')),
    get rdFiltered() {
        const term = this.rdSearch.toLowerCase();
        return term === '' ?
            this.rdOptions.slice(0, 50) :
            this.rdOptions.filter(o => o.label.toLowerCase().includes(term)).slice(0, 50);
    },
    rdSelect(id, label) {
        this.rdSelected = id;
        this.rdSearch = label;
        this.rdOpen = false;
        this.checkExisting();
    },
    rdClear() {
        this.rdSelected = null;
        this.rdSearch = '';
        this.rdOpen = true;
        this.existingDoc = null;
    },
    async checkExisting() {
        if (!this.rdSelected) return;
        const params = new URLSearchParams({
            reksa_dana_id: this.rdSelected,
            document_type: this.type,
            ffs_month: this.type === 'ffs' ? this.ffsMonth : this.prospektusMonth,
            ffs_year: this.type === 'ffs' ? this.ffsYear : this.prospektusYear,
        });
        this.checking = true;
        this.existingDoc = null;
        try {
            const res = await fetch(this.checkUrl + '?' + params.toString());
            const json = await res.json();
            if (json.exists) this.existingDoc = json.document;
        } catch (e) {}
        this.checking = false;
    }
}">
    <div class="px-5 py-4 border-b border-line bg-gradient-to-r from-emerald-700 to-emerald-600">
        <h2 class="font-bold text-white text-sm">Upload Prospektus atau Fund Fact Sheet</h2>
    </div>
    <form method="POST" action="{{ route('admin.daftar-reksa-dana.documents.store') }}" enctype="multipart/form-data"
        class="p-5 space-y-4">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative" @click.away="rdOpen = false">
                <label class="block text-xs font-semibold text-muted mb-1">Reksa Dana *</label>
                <input type="hidden" name="reksa_dana_id" x-model="rdSelected" required>
                <div class="relative">
                    <input type="text" x-model="rdSearch" @focus="rdOpen = true" @input="rdOpen = true"
                        placeholder="Ketik kode atau nama reksa dana..." autocomplete="off"
                        class="w-full text-sm border border-line rounded-lg px-3 py-2 pr-8 focus:border-accent focus:ring focus:ring-accent/30">
                    <button type="button" @click="rdClear()" x-show="rdSelected || rdSearch"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-muted hover:text-red-500 text-xs">✕</button>
                </div>
                <div x-show="rdOpen" x-cloak
                    class="absolute z-20 mt-1 left-0 right-0 max-h-60 overflow-y-auto bg-white border border-line rounded-lg shadow-lg">
                    <template x-for="option in rdFiltered" :key="option.id">
                        <div @click="rdSelect(option.id, option.label)"
                            class="px-3 py-2 text-sm hover:bg-emerald-50 cursor-pointer border-b border-line last:border-b-0"
                            :class="{ 'bg-emerald-50': rdSelected === option.id }" x-text="option.label"></div>
                    </template>
                    <div x-show="rdFiltered.length === 0" class="px-3 py-2 text-sm text-muted">Tidak ditemukan</div>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted mb-1">Jenis Dokumen *</label>
                <select name="document_type" x-model="type" @change="checkExisting()" required
                    class="w-full text-sm border border-line rounded-lg px-3 py-2">
                    <option value="prospektus">Prospektus</option>
                    <option value="ffs">Fund Fact Sheet (FFS)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted mb-1">File PDF *</label>
                <input type="file" name="file" accept="application/pdf" required
                    class="w-full text-xs border border-line rounded-lg px-3 py-2 file:mr-2 file:rounded file:border-0 file:bg-emerald-50 file:px-2 file:py-1 file:text-emerald-700">
            </div>
            <div x-show="type === 'prospektus'">
                <label class="block text-xs font-semibold text-muted mb-1">Bulan Prospektus *</label>
                <select name="prospektus_month" x-model="prospektusMonth" @change="checkExisting()"
                    :required="type === 'prospektus'" class="w-full text-sm border border-line rounded-lg px-3 py-2">
                    <option value="">Pilih Bulan</option>
                    @foreach ($months as $index => $month)
                        <option value="{{ $index + 1 }}">{{ $month }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="type === 'prospektus'">
                <label class="block text-xs font-semibold text-muted mb-1">Tahun Prospektus *</label>
                <input type="number" x-model="prospektusYear" name="prospektus_year" min="2000" max="2100"
                    @input.debounce="checkExisting()" :required="type === 'prospektus'"
                    class="w-full text-sm border border-line rounded-lg px-3 py-2">
            </div>
            <div x-show="type === 'ffs'">
                <label class="block text-xs font-semibold text-muted mb-1">Bulan FFS *</label>
                <select name="ffs_month" x-model="ffsMonth" @change="checkExisting()" :required="type === 'ffs'"
                    class="w-full text-sm border border-line rounded-lg px-3 py-2">
                    <option value="">Pilih Bulan</option>
                    @foreach ($months as $index => $month)
                        <option value="{{ $index + 1 }}">{{ $month }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="type === 'ffs'">
                <label class="block text-xs font-semibold text-muted mb-1">Tahun FFS *</label>
                <input type="number" x-model="ffsYear" name="ffs_year" min="2000" max="2100"
                    @input.debounce="checkExisting()" :required="type === 'ffs'"
                    class="w-full text-sm border border-line rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted mb-1">Catatan</label>
                <input type="text" name="notes" value="{{ old('notes') }}" maxlength="1000"
                    class="w-full text-sm border border-line rounded-lg px-3 py-2" placeholder="Opsional">
            </div>
        </div>

        {{-- Existing document notification --}}
        <template x-if="existingDoc">
            <div class="px-4 py-3 rounded-xl text-sm bg-amber-50 border border-amber-200 text-amber-800">
                <p class="font-semibold">Dokumen sudah ada!</p>
                <p class="mt-1"
                    x-text="'Dokumen ' + (existingDoc.document_type === 'prospektus' ? 'Prospektus' : 'FFS') + ' untuk periode tersebut sudah tersedia: ' + existingDoc.original_name">
                </p>
                <p class="mt-1 text-xs"><span class="font-semibold">Bulan Pembaruan Terakhir:</span> <span
                        x-text="formatMonthYear(existingDoc.updated_at)"></span></p>
                <button @click="openEditModalFromData(existingDoc)"
                    class="mt-2 inline-block text-sm font-semibold text-emerald-700 hover:underline">
                    Edit dokumen yang sudah ada →
                </button>
            </div>
        </template>
        <template x-if="checking">
            <div class="text-xs text-muted">Memeriksa dokumen yang sudah ada...</div>
        </template>

        <p class="text-[11px] text-muted">Format PDF, maksimal 20 MB. Dokumen wajib memiliki bulan dan tahun.</p>
        <button :disabled="existingDoc !== null" class="px-5 py-2.5 rounded-lg text-sm font-semibold transition"
            :class="existingDoc ? 'bg-gray-300 text-gray-500 cursor-not-allowed' :
                'bg-emerald-700 text-white hover:bg-emerald-800'">
            Upload Dokumen
        </button>
    </form>
</div>

<div class="table-card">
    <div
        class="px-6 py-4 border-b border-line flex items-center justify-between gap-3 bg-gradient-to-r from-emerald-700 to-emerald-600">
        <h2 class="font-bold text-white text-sm">Daftar Reksa Dana (Alfabetis)</h2>
        <span class="text-xs text-white/80">{{ $documentFunds->total() }} reksa dana</span>
    </div>
    @if ($lastSyncRun)
        <div
            class="px-5 py-2 bg-emerald-50 border-b border-emerald-200 flex items-center gap-2 text-xs text-emerald-800">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span class="font-semibold">Sinkronisasi Terakhir :</span>
            <span>{{ $lastSyncRun->completed_at ? $lastSyncRun->completed_at->format('d M Y H:i') : $lastSyncRun->created_at->format('d M Y H:i') }}
                WIB</span>
            @if ($lastSyncRun->stats && isset($lastSyncRun->stats['total']))
                <span class="text-emerald-600">({{ number_format($lastSyncRun->stats['total']) }} data)</span>
            @endif
        </div>
    @endif

    {{-- Filter & Search --}}
    <div class="px-5 py-4 border-b border-line bg-[#f8fafc]">
        <form method="GET" action="{{ route('admin.daftar-reksa-dana.index') }}"
            class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="tab" value="prospektus-ffs">
            <div class="flex-1 min-w-56">
                <label class="block text-xs font-semibold text-muted mb-1">Cari Reksa Dana</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Nama, kode, atau manajer investasi..."
                    class="w-full text-sm border border-line rounded-lg px-3 py-2 focus:border-accent focus:ring focus:ring-accent/30">
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted mb-1">Jenis</label>
                <select name="jenis"
                    class="w-full text-sm border border-line rounded-lg px-3 py-2 focus:border-accent focus:ring focus:ring-accent/30">
                    <option value="">Semua Jenis</option>
                    @foreach (['Saham', 'Pendapatan Tetap', 'Campuran', 'Pasar Uang', 'Terproteksi', 'Global', 'DIRE-DINFRA', 'Penyertaan terbatas'] as $j)
                        <option value="{{ $j }}" @selected(request('jenis') == $j)>{{ $j }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary/90 transition">Filter</button>
            @if (request('search') || request('jenis'))
                <a href="{{ route('admin.daftar-reksa-dana.index', ['tab' => 'prospektus-ffs']) }}"
                    class="px-4 py-2 border border-line text-muted rounded-lg text-sm font-semibold hover:text-primary transition">Reset</a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase">
                    <th class="px-4 py-3 font-semibold">Reksa Dana</th>
                    <th class="px-4 py-3 font-semibold">Prospektus</th>
                    <th class="px-4 py-3 font-semibold">Keterangan Prospektus</th>
                    <th class="px-4 py-3 font-semibold">Fund Fact Sheet</th>
                    <th class="px-4 py-3 font-semibold">Keterangan FFS</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($documentFunds as $rd)
                    @php
                        $prospectuses = $rd->documents->where('document_type', 'prospektus');
                        $ffsDocuments = $rd->documents
                            ->where('document_type', 'ffs')
                            ->sortByDesc(fn($d) => sprintf('%04d%02d', $d->ffs_year, $d->ffs_month));
                        $hasMoreDocs = $prospectuses->count() > 1 || $ffsDocuments->count() > 1;
                    @endphp
                    <tr x-data="{ showAll: false }" class="align-top hover:bg-emerald-50/50 transition-colors">
                        <td class="px-4 py-3 min-w-56">
                            <a href="{{ route('admin.daftar-reksa-dana.show', $rd) }}"
                                class="font-semibold text-primary hover:underline text-sm">{{ $rd->nama_reksa_dana }}</a>
                            @if ($rd->kode_reksa_dana)
                                <p class="text-xs text-muted mt-0.5 font-mono">{{ $rd->kode_reksa_dana }}</p>
                            @endif
                            @if ($rd->nama_manajer_investasi)
                                <p class="text-xs text-muted">{{ $rd->nama_manajer_investasi }}</p>
                            @endif
                            @if ($hasMoreDocs)
                                <button @click="showAll = !showAll"
                                    class="text-xs text-accent-dark underline hover:underline mt-1.5 block"
                                    x-text="showAll ? 'Sembunyikan' : 'Melihat Lainnya'"></button>
                            @endif
                        </td>
                        <td class="px-4 py-3 min-w-72">
                            @forelse ($prospectuses as $i => $document)
                                <div @if ($i > 0) x-show="showAll" x-cloak @endif>
                                    @include('admin.daftar-reksa-dana.partials.document-actions', [
                                        'document' => $document,
                                        'label' => $document->ffs_month
                                            ? ($months[$document->ffs_month - 1] ?? '-') .
                                                ' ' .
                                                $document->ffs_year
                                            : (string) $document->ffs_year,
                                    ])
                                </div>
                            @empty
                                <p class="text-xs text-muted">Prospektus belum tersedia.</p>
                            @endforelse
                        </td>
                        <td class="px-4 py-3 text-xs text-muted max-w-xs">
                            @php
                                $prospektusNotes = $prospectuses->pluck('notes')->filter()->unique();
                            @endphp
                            @if ($prospektusNotes->isNotEmpty())
                                @foreach ($prospektusNotes as $note)
                                    <p class="mb-1 last:mb-0">{{ $note }}</p>
                                @endforeach
                            @else
                                <span class="italic">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 min-w-80">
                            @forelse ($ffsDocuments as $i => $document)
                                <div @if ($i > 0) x-show="showAll" x-cloak @endif>
                                    @include('admin.daftar-reksa-dana.partials.document-actions', [
                                        'document' => $document,
                                        'label' =>
                                            ($months[$document->ffs_month - 1] ?? '-') .
                                            ' ' .
                                            $document->ffs_year,
                                    ])
                                </div>
                            @empty
                                <span class="text-xs text-muted italic">Belum diupload</span>
                            @endforelse
                        </td>
                        <td class="px-4 py-3 text-xs text-muted max-w-xs">
                            @php
                                $ffsNotes = $ffsDocuments->pluck('notes')->filter()->unique();
                            @endphp
                            @if ($ffsNotes->isNotEmpty())
                                @foreach ($ffsNotes as $note)
                                    <p class="mb-1 last:mb-0">{{ $note }}</p>
                                @endforeach
                            @else
                                <span class="italic">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-muted">Belum ada data Reksa Dana.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($documentFunds->hasPages())
        <div class="px-6 py-4 border-t border-line">{{ $documentFunds->links() }}</div>
    @endif
</div>

{{-- Modal Edit Dokumen --}}
<div id="modal-document-edit" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4"
    onclick="if(event.target===this)closeModal('modal-document-edit')">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-line sticky top-0 bg-white z-10">
            <div>
                <h3 class="font-bold text-primary">Edit Dokumen</h3>
                <p class="text-xs text-muted mt-0.5">Nama file: <span id="edit-doc-filename"
                        class="font-semibold text-primary">—</span></p>
            </div>
            <button type="button" onclick="closeModal('modal-document-edit')"
                class="p-1 hover:bg-[#f1f5f9] rounded-lg transition">
                <svg class="w-5 h-5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form method="POST" action="" class="p-6 space-y-4" id="form-document-edit"
            enctype="multipart/form-data">
            @csrf
            @method('POST')
            <div x-data="{ docType: document.getElementById('edit-doc-type')?.value || 'prospektus' }">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Jenis Dokumen *</label>
                    <select name="document_type" x-model="docType" required
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-emerald-700 focus:ring focus:ring-emerald-700/20">
                        <option value="prospektus">Prospektus</option>
                        <option value="ffs">Fund Fact Sheet (FFS)</option>
                    </select>
                </div>
                <div x-show="docType === 'ffs'" class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Bulan FFS *</label>
                        <select name="ffs_month" id="edit-doc-ffs-month" :required="docType === 'ffs'"
                            class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-emerald-700 focus:ring focus:ring-emerald-700/20">
                            <option value="">— Pilih Bulan —</option>
                            @foreach ($months as $index => $month)
                                <option value="{{ $index + 1 }}">{{ $month }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Tahun FFS *</label>
                        <input type="number" name="ffs_year" id="edit-doc-ffs-year" min="2000" max="2100"
                            :required="docType === 'ffs'"
                            class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-emerald-700 focus:ring focus:ring-emerald-700/20">
                    </div>
                </div>
                <div x-show="docType === 'prospektus'" class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Bulan Prospektus *</label>
                        <select name="ffs_month" id="edit-doc-prospektus-month" :required="docType === 'prospektus'"
                            class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-emerald-700 focus:ring focus:ring-emerald-700/20">
                            <option value="">— Pilih Bulan —</option>
                            @foreach ($months as $index => $month)
                                <option value="{{ $index + 1 }}">{{ $month }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Tahun Prospektus *</label>
                        <input type="number" name="ffs_year" id="edit-doc-prospektus-year" min="2000"
                            max="2100" :required="docType === 'prospektus'"
                            class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-emerald-700 focus:ring focus:ring-emerald-700/20">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-xs font-semibold text-primary mb-1">Ganti File (PDF)</label>
                    <input type="file" name="file" accept="application/pdf"
                        class="w-full text-xs border border-line rounded-lg px-3 py-2 file:mr-2 file:rounded file:border-0 file:bg-emerald-50 file:px-2 file:py-1 file:text-emerald-700">
                    <p class="text-[11px] text-muted mt-1">Kosongkan jika tidak ingin mengganti file. Format PDF,
                        maksimal 20 MB.</p>
                </div>
                <div class="mt-4">
                    <label class="block text-xs font-semibold text-primary mb-1">Catatan</label>
                    <input type="text" name="notes" id="edit-doc-notes" maxlength="1000"
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-emerald-700 focus:ring focus:ring-emerald-700/20"
                        placeholder="Opsional">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('modal-document-edit')"
                    class="px-4 py-2 text-sm text-muted border border-line rounded-lg hover:bg-[#f1f5f9] transition">Batal</button>
                <button type="submit"
                    class="px-4 py-2 text-sm text-white bg-emerald-700 rounded-lg hover:bg-emerald-800 transition">Simpan</button>
            </div>
        </form>
    </div>
</div>
</div>
</div>
