{{-- Search & Actions --}}
<div class="mb-5 flex items-center justify-between gap-3 flex-wrap">
    <form method="GET" action="{{ route('admin.unit-link.index') }}" class="flex items-center gap-3 flex-1">
        <input type="hidden" name="tab" value="unit-prices">
        <div class="relative flex-1 max-w-md">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   class="w-full pl-10 pr-4 py-2.5 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                   placeholder="Cari nama unit link...">
        </div>
        <button type="submit" class="px-4 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition">Cari</button>
        @if(request('search'))<a href="{{ route('admin.unit-link.index', ['tab' => 'unit-prices']) }}" class="px-4 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">Reset</a>@endif
    </form>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.unit-link.template-harga') }}"
           class="flex items-center gap-2 px-4 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary hover:border-primary/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Template
        </a>
        <button @click="showImport = true"
                class="flex items-center gap-2 px-4 py-2.5 border border-accent text-accent rounded-xl text-sm font-semibold hover:bg-accent/5 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Import
        </button>
        <button @click="showHargaForm = true; editHargaId = null; hargaForm = { unit_link_id: '', datetime: '', harga_median: '', sell_buy_low: '', sell_buy_high: '' }"
                class="flex items-center gap-2 px-4 py-2.5 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent/90 transition shadow-sm shadow-accent/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah
        </button>
    </div>
</div>

<div class="bg-white rounded-2xl border border-line overflow-hidden shadow-sm">
    <div class="px-6 py-4 border-b border-line flex items-center justify-between bg-gradient-to-r from-primary to-primary-light">
        <h2 class="font-bold text-white flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            Unit Prices
        </h2>
        <div class="flex items-center gap-2">
            <span class="text-xs text-white/60">Tampilkan:</span>
            <form method="GET" action="{{ route('admin.unit-link.index') }}">
                <input type="hidden" name="tab" value="unit-prices">
                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                <select name="per_page" onchange="this.form.submit()"
                        class="text-xs bg-white/10 text-white border border-white/20 rounded-lg px-2 py-1 focus:outline-none cursor-pointer">
                    @foreach([10, 25, 50] as $n)
                    <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </form>
            <span class="text-xs text-white/60">{{ $hargaUnitLinks->total() }} total</span>
        </div>
    </div>

    @if($hargaUnitLinks->isEmpty())
    <div class="py-16 text-center text-muted">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
        <p class="font-medium">Belum ada data</p>
        <p class="text-sm mt-1">Klik "Tambah" atau import dari Excel</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                    <th class="px-4 py-3.5 font-semibold">Nama Unit Link</th>
                    <th class="px-4 py-3.5 font-semibold">DateTime</th>
                    <th class="px-4 py-3.5 font-semibold text-right">Harga Median</th>
                    <th class="px-4 py-3.5 font-semibold text-right">Sell-Buy (low)</th>
                    <th class="px-4 py-3.5 font-semibold text-right">Sell-Buy (high)</th>
                    <th class="px-4 py-3.5 font-semibold text-right w-20">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @foreach($hargaUnitLinks as $h)
                <tr class="hover:bg-[#f8fafc] transition-colors">
                    <td class="px-4 py-3 font-semibold text-primary text-sm">{{ $h->unitLink?->unit_link ?? '-' }}</td>
                    <td class="px-4 py-3 text-xs tabular-nums">{{ $h->datetime ? $h->datetime->format('d/m/Y H:i') : '-' }}</td>
                    <td class="px-4 py-3 text-right text-xs tabular-nums">{{ $h->harga_median !== null ? number_format($h->harga_median, 6, ',', '.') : '-' }}</td>
                    <td class="px-4 py-3 text-right text-xs tabular-nums">{{ $h->sell_buy_low !== null ? number_format($h->sell_buy_low, 6, ',', '.') : '-' }}</td>
                    <td class="px-4 py-3 text-right text-xs tabular-nums">{{ $h->sell_buy_high !== null ? number_format($h->sell_buy_high, 6, ',', '.') : '-' }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <button type="button"
                                    @click="editHarga({{ $h->id }}, '{{ addslashes($h->unitLink?->unit_link ?? '') }}', '{{ $h->unit_link_id }}', '{{ $h->datetime->format('Y-m-d\TH:i') }}', '{{ $h->harga_median }}', '{{ $h->sell_buy_low ?? '' }}', '{{ $h->sell_buy_high ?? '' }}')"
                                    class="p-2 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button type="button"
                                    @click="deleteHargaId = {{ $h->id }}; deleteHargaText = '{{ addslashes($h->unitLink?->unit_link ?? '') }} - {{ $h->datetime->format('d/m/Y H:i') }}'"
                                    class="p-2 rounded-lg text-muted hover:text-red-500 hover:bg-red-50 transition" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($hargaUnitLinks->hasPages())
    <div class="px-6 py-4 border-t border-line flex items-center justify-between gap-4 text-sm">
        <p class="text-muted text-xs">Menampilkan {{ $hargaUnitLinks->firstItem() }}–{{ $hargaUnitLinks->lastItem() }} dari {{ $hargaUnitLinks->total() }}</p>
        <div class="flex items-center gap-1">
            @if($hargaUnitLinks->onFirstPage())
            <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">← Prev</span>
            @else
            <a href="{{ $hargaUnitLinks->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">← Prev</a>
            @endif
            @php $cur=$hargaUnitLinks->currentPage();$last=$hargaUnitLinks->lastPage();$s=max(1,$cur-2);$e=min($last,$cur+2); @endphp
            @if($s>1)
                <a href="{{ $hargaUnitLinks->url(1) }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">1</a>
                @if($s>2)<span class="px-1 text-muted text-xs">…</span>@endif
            @endif
            @foreach($hargaUnitLinks->getUrlRange($s,$e) as $page => $url)
            <a href="{{ $url }}"
               class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold transition {{ $page == $cur ? 'bg-primary text-white' : 'text-muted hover:text-primary hover:bg-[#f1f5f9]' }}">{{ $page }}</a>
            @endforeach
            @if($e<$last)
                @if($e<$last-1)<span class="px-1 text-muted text-xs">…</span>@endif
                <a href="{{ $hargaUnitLinks->url($last) }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">{{ $last }}</a>
            @endif
            @if($hargaUnitLinks->hasMorePages())
            <a href="{{ $hargaUnitLinks->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">Next →</a>
            @else
            <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">Next →</span>
            @endif
        </div>
    </div>
    @endif
    @endif
</div>

{{-- Modal Import --}}
<div x-show="showImport" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="absolute inset-0 bg-black/40" @click="showImport = false"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6"
         x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <h3 class="font-bold text-primary text-base mb-1">Import Harga Unit Link</h3>
        <p class="text-muted text-sm mb-4">Upload file Excel sesuai format template.</p>
        <form method="POST" action="{{ route('admin.unit-link.import-harga') }}" enctype="multipart/form-data">
            @csrf
            <div class="border-2 border-dashed border-line rounded-xl p-6 text-center mb-4 hover:border-accent/40 transition">
                <svg class="w-8 h-8 mx-auto text-muted mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <label class="cursor-pointer">
                    <span class="text-sm font-semibold text-accent">Pilih file</span>
                    <span class="text-sm text-muted"> atau drag & drop</span>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" class="hidden" required>
                </label>
                <p class="text-xs text-muted mt-1">Format: .xlsx, .xls, .csv</p>
            </div>
            @error('file')<p class="text-red-500 text-xs mb-3">{{ $message }}</p>@enderror
            <div class="flex items-center justify-between">
                <a href="{{ route('admin.unit-link.template-harga') }}" class="text-xs text-accent hover:underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download template
                </a>
                <div class="flex items-center gap-2">
                    <button type="button" @click="showImport = false" class="px-4 py-2 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent/90 transition">Upload & Import</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Form Tambah/Edit Harga --}}
<div x-show="showHargaForm" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="absolute inset-0 bg-black/40" @click="showHargaForm = false"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6"
         x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <h3 class="font-bold text-primary text-base mb-1" x-text="editHargaId ? 'Edit Harga Unit Link' : 'Tambah Harga Unit Link'"></h3>
        <p class="text-muted text-sm mb-4">Isi data harga unit link.</p>
        <form method="POST" :action="editHargaId ? `/admin/unit-link-harga/${editHargaId}` : '{{ route('admin.unit-link.store-harga') }}'">
            @csrf
            <input type="hidden" name="_method" :value="editHargaId ? 'PUT' : 'POST'">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-primary mb-1">Nama Unit Link <span class="text-red-500">*</span></label>
                    <select name="unit_link_id" x-model="hargaForm.unit_link_id" required
                            class="w-full border border-line rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                        <option value="">Pilih Unit Link</option>
                        @foreach(\App\Models\UnitLink::orderBy('unit_link')->get() as $ul)
                        <option value="{{ $ul->id }}">{{ $ul->unit_link }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-primary mb-1">DateTime <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="datetime" x-model="hargaForm.datetime" required
                           class="w-full border border-line rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-primary mb-1">Harga Median <span class="text-red-500">*</span></label>
                    <input type="number" name="harga_median" x-model="hargaForm.harga_median" step="0.000001" required
                           class="w-full border border-line rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-primary mb-1">Sell-Buy (low)</label>
                        <input type="number" name="sell_buy_low" x-model="hargaForm.sell_buy_low" step="0.000001"
                               class="w-full border border-line rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-primary mb-1">Sell-Buy (high)</label>
                        <input type="number" name="sell_buy_high" x-model="hargaForm.sell_buy_high" step="0.000001"
                               class="w-full border border-line rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 mt-6">
                <button type="button" @click="showHargaForm = false" class="px-4 py-2 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent/90 transition" x-text="editHargaId ? 'Simpan Perubahan' : 'Tambah'"></button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Hapus Harga --}}
<div x-show="deleteHargaId !== null" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="absolute inset-0 bg-black/40" @click="deleteHargaId = null"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6"
         x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-full bg-red-100 grid place-items-center shrink-0">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <div class="flex-1">
                <h3 class="font-bold text-primary text-base">Hapus Harga Unit Link?</h3>
                <p class="text-muted text-sm mt-1">Data berikut akan dihapus permanen:</p>
                <p class="mt-2 text-sm text-primary font-medium bg-[#f8fafc] rounded-lg px-3 py-2 border border-line" x-text="deleteHargaText"></p>
                <p class="text-xs text-red-500 mt-2">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 mt-6">
            <button type="button" @click="deleteHargaId = null"
                    class="px-4 py-2 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary hover:border-primary/30 transition">Batal</button>
            <form method="POST" :action="`/admin/unit-link-harga/${deleteHargaId}`">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700 transition">Ya, Hapus</button>
            </form>
        </div>
    </div>
</div>
