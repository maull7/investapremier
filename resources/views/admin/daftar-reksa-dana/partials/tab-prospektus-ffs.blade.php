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

<div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden mb-5">
    <div class="px-5 py-4 border-b border-line bg-gradient-to-r from-emerald-700 to-emerald-600">
        <h2 class="font-bold text-white text-sm">Upload Prospektus atau Fund Fact Sheet</h2>
    </div>
    <form method="POST" action="{{ route('admin.daftar-reksa-dana.documents.store') }}" enctype="multipart/form-data"
        class="p-5 space-y-4" x-data="{ type: @js(old('document_type', 'prospektus')) }">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-muted mb-1">Reksa Dana *</label>
                <select name="reksa_dana_id" required class="w-full text-sm border border-line rounded-lg px-3 py-2">
                    <option value="">Pilih Reksa Dana</option>
                    @foreach ($reksaDanaOptions as $rd)
                        <option value="{{ $rd->id }}" @selected(old('reksa_dana_id') == $rd->id)>
                            {{ $rd->kode_reksa_dana ? $rd->kode_reksa_dana . ' - ' : '' }}{{ $rd->nama_reksa_dana }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted mb-1">Jenis Dokumen *</label>
                <select name="document_type" x-model="type" required
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
                <label class="block text-xs font-semibold text-muted mb-1">Tahun Prospektus *</label>
                <input type="number" name="prospektus_year" min="2000" max="2100"
                    value="{{ old('prospektus_year', now()->year) }}" :required="type === 'prospektus'"
                    class="w-full text-sm border border-line rounded-lg px-3 py-2">
            </div>
            <div x-show="type === 'ffs'">
                <label class="block text-xs font-semibold text-muted mb-1">Bulan FFS *</label>
                <select name="ffs_month" :required="type === 'ffs'"
                    class="w-full text-sm border border-line rounded-lg px-3 py-2">
                    <option value="">Pilih Bulan</option>
                    @foreach ($months as $index => $month)
                        <option value="{{ $index + 1 }}" @selected(old('ffs_month') == $index + 1)>{{ $month }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="type === 'ffs'">
                <label class="block text-xs font-semibold text-muted mb-1">Tahun FFS *</label>
                <input type="number" name="ffs_year" min="2000" max="2100"
                    value="{{ old('ffs_year', now()->year) }}" :required="type === 'ffs'"
                    class="w-full text-sm border border-line rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted mb-1">Catatan</label>
                <input type="text" name="notes" value="{{ old('notes') }}" maxlength="1000"
                    class="w-full text-sm border border-line rounded-lg px-3 py-2" placeholder="Opsional">
            </div>
        </div>
        <p class="text-[11px] text-muted">Format PDF, maksimal 20 MB. Dokumen FFS wajib memiliki bulan dan tahun.</p>
        <button class="px-5 py-2.5 bg-emerald-700 text-white rounded-lg text-sm font-semibold hover:bg-emerald-800">
            Upload Dokumen
        </button>
    </form>
</div>

<div class="table-card">
    <div class="px-6 py-4 border-b border-line flex items-center justify-between gap-3 bg-gradient-to-r from-emerald-700 to-emerald-600">
        <h2 class="font-bold text-white text-sm">Daftar Reksa Dana (Alfabetis)</h2>
        <span class="text-xs text-white/80">{{ $documentFunds->total() }} reksa dana</span>
    </div>
    @if ($lastSyncRun)
        <div class="px-5 py-2 bg-emerald-50 border-b border-emerald-200 flex items-center gap-2 text-xs text-emerald-800">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span class="font-semibold">Sinkronisasi Terakhir :</span>
            <span>{{ $lastSyncRun->completed_at ? $lastSyncRun->completed_at->format('d M Y H:i') : $lastSyncRun->created_at->format('d M Y H:i') }} WIB</span>
            @if ($lastSyncRun->stats && isset($lastSyncRun->stats['total']))
                <span class="text-emerald-600">({{ number_format($lastSyncRun->stats['total']) }} data)</span>
            @endif
        </div>
    @endif

    {{-- Filter & Search --}}
    <div class="px-5 py-4 border-b border-line bg-[#f8fafc]">
        <form method="GET" action="{{ route('admin.daftar-reksa-dana.index') }}" class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="tab" value="prospektus-ffs">
            <div class="flex-1 min-w-56">
                <label class="block text-xs font-semibold text-muted mb-1">Cari Reksa Dana</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Nama, kode, atau manajer investasi..."
                    class="w-full text-sm border border-line rounded-lg px-3 py-2 focus:border-accent focus:ring focus:ring-accent/30">
            </div>
            <div>
                <label class="block text-xs font-semibold text-muted mb-1">Jenis</label>
                <select name="jenis" class="w-full text-sm border border-line rounded-lg px-3 py-2 focus:border-accent focus:ring focus:ring-accent/30">
                    <option value="">Semua Jenis</option>
                    @foreach(['Saham', 'Pendapatan Tetap', 'Campuran', 'Pasar Uang', 'Terproteksi', 'Global', 'DIRE-DINFRA', 'Penyertaan terbatas'] as $j)
                        <option value="{{ $j }}" @selected(request('jenis') == $j)>{{ $j }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary/90 transition">Filter</button>
            @if(request('search') || request('jenis'))
                <a href="{{ route('admin.daftar-reksa-dana.index', ['tab' => 'prospektus-ffs']) }}" class="px-4 py-2 border border-line text-muted rounded-lg text-sm font-semibold hover:text-primary transition">Reset</a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase">
                    <th class="px-4 py-3 font-semibold">Reksa Dana</th>
                    <th class="px-4 py-3 font-semibold">Prospektus</th>
                    <th class="px-4 py-3 font-semibold">Catatan</th>
                    <th class="px-4 py-3 font-semibold">Fund Fact Sheet</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($documentFunds as $rd)
                    @php
                        $prospectuses = $rd->documents->where('document_type', 'prospektus');
                        $ffsDocuments = $rd->documents->where('document_type', 'ffs')->sortByDesc(fn($d) => sprintf('%04d%02d', $d->ffs_year, $d->ffs_month));
                    @endphp
                    <tr class="align-top hover:bg-emerald-50/50 transition-colors">
                        <td class="px-4 py-3 min-w-56">
                            <a href="{{ route('admin.daftar-reksa-dana.show', $rd) }}" class="font-semibold text-primary hover:underline text-sm">{{ $rd->nama_reksa_dana }}</a>
                            @if ($rd->kode_reksa_dana)
                                <p class="text-xs text-muted mt-0.5 font-mono">{{ $rd->kode_reksa_dana }}</p>
                            @endif
                            @if ($rd->nama_manajer_investasi)
                                <p class="text-xs text-muted">{{ $rd->nama_manajer_investasi }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 min-w-72">
                            @forelse ($prospectuses as $document)
                                @include('admin.daftar-reksa-dana.partials.document-actions', [
                                    'document' => $document,
                                    'label' => $document->ffs_year ?? $document->original_name,
                                ])
                            @empty
                                <p class="text-xs text-muted">Prospektus belum tersedia.</p>
                            @endforelse
                        </td>
                        <td class="px-4 py-3 text-xs text-muted max-w-xs">
                            @php
                                $allNotes = $rd->documents->pluck('notes')->filter()->unique();
                            @endphp
                            @if ($allNotes->isNotEmpty())
                                @foreach ($allNotes as $note)
                                    <p class="mb-1 last:mb-0">{{ $note }}</p>
                                @endforeach
                            @else
                                <span class="italic">Tidak ada catatan</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 min-w-80">
                            @forelse ($ffsDocuments as $document)
                                @include('admin.daftar-reksa-dana.partials.document-actions', [
                                    'document' => $document,
                                    'label' => ($months[$document->ffs_month - 1] ?? '-') . ' ' . $document->ffs_year,
                                ])
                            @empty
                                <span class="text-xs text-muted italic">Belum diupload</span>
                            @endforelse
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-muted">Belum ada data Reksa Dana.</td>
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
                <p class="text-xs text-muted mt-0.5">Nama file: <span id="edit-doc-filename" class="font-semibold text-primary">—</span></p>
            </div>
            <button type="button" onclick="closeModal('modal-document-edit')"
                class="p-1 hover:bg-[#f1f5f9] rounded-lg transition">
                <svg class="w-5 h-5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form method="POST" action="" class="p-6 space-y-4" id="form-document-edit" enctype="multipart/form-data">
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
                        <input type="number" name="ffs_year" id="edit-doc-ffs-year" min="2000" max="2100" :required="docType === 'ffs'"
                            class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-emerald-700 focus:ring focus:ring-emerald-700/20">
                    </div>
                </div>
                <div x-show="docType === 'prospektus'" class="mt-4">
                    <label class="block text-xs font-semibold text-primary mb-1">Tahun Prospektus *</label>
                    <input type="number" name="ffs_year" id="edit-doc-prospektus-year" min="2000" max="2100" :required="docType === 'prospektus'"
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-emerald-700 focus:ring focus:ring-emerald-700/20">
                </div>
                <div class="mt-4">
                    <label class="block text-xs font-semibold text-primary mb-1">Ganti File (PDF)</label>
                    <input type="file" name="file" accept="application/pdf"
                        class="w-full text-xs border border-line rounded-lg px-3 py-2 file:mr-2 file:rounded file:border-0 file:bg-emerald-50 file:px-2 file:py-1 file:text-emerald-700">
                    <p class="text-[11px] text-muted mt-1">Kosongkan jika tidak ingin mengganti file. Format PDF, maksimal 20 MB.</p>
                </div>
                <div class="mt-4">
                    <label class="block text-xs font-semibold text-primary mb-1">Catatan</label>
                    <input type="text" name="notes" id="edit-doc-notes" maxlength="1000"
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-emerald-700 focus:ring focus:ring-emerald-700/20" placeholder="Opsional">
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
