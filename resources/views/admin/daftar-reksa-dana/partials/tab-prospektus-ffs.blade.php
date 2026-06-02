@php
    $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
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
                <select name="document_type" x-model="type" required class="w-full text-sm border border-line rounded-lg px-3 py-2">
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
                <input type="number" name="prospektus_year" min="2000" max="2100" value="{{ old('prospektus_year', now()->year) }}"
                    :required="type === 'prospektus'" class="w-full text-sm border border-line rounded-lg px-3 py-2">
            </div>
            <div x-show="type === 'ffs'">
                <label class="block text-xs font-semibold text-muted mb-1">Bulan FFS *</label>
                <select name="ffs_month" :required="type === 'ffs'" class="w-full text-sm border border-line rounded-lg px-3 py-2">
                    <option value="">Pilih Bulan</option>
                    @foreach ($months as $index => $month)
                        <option value="{{ $index + 1 }}" @selected(old('ffs_month') == $index + 1)>{{ $month }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="type === 'ffs'">
                <label class="block text-xs font-semibold text-muted mb-1">Tahun FFS *</label>
                <input type="number" name="ffs_year" min="2000" max="2100" value="{{ old('ffs_year', now()->year) }}"
                    :required="type === 'ffs'" class="w-full text-sm border border-line rounded-lg px-3 py-2">
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

<div class="bg-white rounded-2xl border border-line overflow-hidden shadow-sm">
    <div class="px-6 py-4 border-b border-line flex flex-wrap items-center justify-between gap-3 bg-gradient-to-r from-emerald-700 to-emerald-600">
        <h2 class="font-bold text-white text-sm">Daftar Reksa Dana dengan Prospektus dan FFS</h2>
        <form method="GET" action="{{ route('admin.daftar-reksa-dana.index') }}" class="flex gap-2">
            <input type="hidden" name="tab" value="prospektus-ffs">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode / nama RD..."
                class="w-52 text-xs border border-white/30 bg-white/10 text-white placeholder-white/60 rounded-lg px-3 py-1.5">
            <button class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold">Cari</button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase">
                    <th class="px-4 py-3 font-semibold">Reksa Dana</th>
                    <th class="px-4 py-3 font-semibold">Prospektus</th>
                    <th class="px-4 py-3 font-semibold">Fund Fact Sheet (FFS)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($documentFunds as $fund)
                    @php
                        $prospectuses = $fund->documents->where('document_type', 'prospektus');
                        $ffsDocuments = $fund->documents->where('document_type', 'ffs')
                            ->sortByDesc(fn ($document) => sprintf('%04d%02d', $document->ffs_year, $document->ffs_month));
                    @endphp
                    <tr class="align-top hover:bg-[#f8fafc]">
                        <td class="px-4 py-4 min-w-56">
                            <p class="font-semibold text-primary">{{ $fund->nama_reksa_dana }}</p>
                            <p class="text-xs text-muted mt-1">{{ $fund->kode_reksa_dana ?: 'Tanpa kode' }}</p>
                            <p class="text-xs text-muted">{{ $fund->nama_manajer_investasi }}</p>
                        </td>
                        <td class="px-4 py-4 min-w-72">
                            @forelse ($prospectuses as $document)
                                @include('admin.daftar-reksa-dana.partials.document-actions', ['document' => $document, 'label' => $document->ffs_year ?? $document->original_name])
                            @empty
                                <p class="text-xs text-muted">Prospektus belum tersedia.</p>
                            @endforelse
                        </td>
                        <td class="px-4 py-4 min-w-80">
                            @forelse ($ffsDocuments as $document)
                                @include('admin.daftar-reksa-dana.partials.document-actions', [
                                    'document' => $document,
                                    'label' => ($months[$document->ffs_month - 1] ?? '-') . ' ' . $document->ffs_year,
                                ])
                            @empty
                                <p class="text-xs text-muted">FFS belum tersedia.</p>
                            @endforelse
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-6 py-12 text-center text-muted">Belum ada data Reksa Dana.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($documentFunds->hasPages())
        <div class="px-6 py-4 border-t border-line">{{ $documentFunds->links() }}</div>
    @endif
</div>
