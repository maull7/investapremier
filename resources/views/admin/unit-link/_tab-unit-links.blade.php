{{-- Search & Actions --}}
<div class="mb-5 flex items-center justify-between gap-3 flex-wrap">
    <form method="GET" action="{{ route('admin.unit-link.index') }}" class="flex items-center gap-3 flex-1">
        <input type="hidden" name="tab" value="unit-links">
        <div class="relative flex-1 max-w-md">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   class="w-full pl-10 pr-4 py-2.5 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                   placeholder="Cari unit link, asuransi, atau jenis...">
        </div>
        <button type="submit" class="px-4 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition">Cari</button>
        @if(request('search'))<a href="{{ route('admin.unit-link.index', ['tab' => 'unit-links']) }}" class="px-4 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">Reset</a>@endif
    </form>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.unit-link.template') }}"
           class="btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Template
        </a>
        <button @click="showImport = true"
                class="btn-outline">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Import
        </button>
        <a href="{{ route('admin.unit-link.create') }}"
           class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah
        </a>
    </div>
</div>

<div class="table-card">
    <div class="table-head">
        <h2 class="th-title">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            Unit Links
        </h2>
        <div class="flex items-center gap-2">
            <span class="th-meta">Tampilkan:</span>
            <form method="GET" action="{{ route('admin.unit-link.index') }}">
                <input type="hidden" name="tab" value="unit-links">
                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                <select name="per_page" onchange="this.form.submit()"
                        class="text-xs bg-white/10 text-white border border-white/20 rounded-lg px-2 py-1 focus:outline-none cursor-pointer">
                    @foreach([10, 25, 50] as $n)
                    <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </form>
            <span class="th-meta">{{ $unitLinks->total() }} total</span>
        </div>
    </div>

    @if($unitLinks->isEmpty())
    <div class="py-16 text-center text-muted">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
        <p class="font-medium">Belum ada data</p>
        <p class="text-sm mt-1">Klik "Tambah" atau import dari Excel</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                    <th class="px-4 py-3.5 font-semibold">Unit Link</th>
                    <th class="px-4 py-3.5 font-semibold">Asuransi</th>
                    <th class="px-4 py-3.5 font-semibold">Jenis</th>
                    <th class="px-4 py-3.5 font-semibold">Tipe</th>
                    <th class="px-4 py-3.5 font-semibold">Mata Uang</th>
                    <th class="px-4 py-3.5 font-semibold text-right">Median Price</th>
                    <th class="px-4 py-3.5 font-semibold text-right">Buy Price</th>
                    <th class="px-4 py-3.5 font-semibold text-right">Sell Price</th>
                    <th class="px-4 py-3.5 font-semibold text-right">Last Update</th>
                    <th class="px-4 py-3.5 font-semibold text-right w-20">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @foreach($unitLinks as $u)
                <tr class="hover:bg-[#f8fafc] transition-colors">
                    <td class="px-4 py-3 font-semibold text-primary text-sm">{{ $u->unit_link }}</td>
                    <td class="px-4 py-3 text-xs">{{ $u->asuransi ?: '-' }}</td>
                    <td class="px-4 py-3 text-xs">{{ $u->jenis ?: '-' }}</td>
                    <td class="px-4 py-3 text-xs">{{ $u->tipe ?: '-' }}</td>
                    <td class="px-4 py-3 text-xs">
                        <span class="px-2 py-0.5 rounded bg-primary/5 text-primary font-semibold">{{ $u->mata_uang ?: '-' }}</span>
                    </td>
                    <td class="px-4 py-3 text-right text-xs tabular-nums">{{ $u->median_price !== null ? number_format($u->median_price, 4, ',', '.') : '-' }}</td>
                    <td class="px-4 py-3 text-right text-xs tabular-nums">{{ $u->buy_price !== null ? number_format($u->buy_price, 4, ',', '.') : '-' }}</td>
                    <td class="px-4 py-3 text-right text-xs tabular-nums">{{ $u->sell_price !== null ? number_format($u->sell_price, 4, ',', '.') : '-' }}</td>
                    <td class="px-4 py-3 text-right text-xs">{{ $u->last_update ? $u->last_update->format('d M Y') : '-' }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('admin.unit-link.edit', $u) }}"
                               class="p-2 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button type="button"
                                    @click="deleteId = {{ $u->id }}; deleteText = '{{ addslashes($u->unit_link) }}'"
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

    @if($unitLinks->hasPages())
    <div class="px-6 py-4 border-t border-line flex items-center justify-between gap-4 text-sm">
        <p class="text-muted text-xs">Menampilkan {{ $unitLinks->firstItem() }}–{{ $unitLinks->lastItem() }} dari {{ $unitLinks->total() }}</p>
        <div class="flex items-center gap-1">
            @if($unitLinks->onFirstPage())
            <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">← Prev</span>
            @else
            <a href="{{ $unitLinks->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">← Prev</a>
            @endif
            @php $cur=$unitLinks->currentPage();$last=$unitLinks->lastPage();$s=max(1,$cur-2);$e=min($last,$cur+2); @endphp
            @if($s>1)
                <a href="{{ $unitLinks->url(1) }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">1</a>
                @if($s>2)<span class="px-1 text-muted text-xs">…</span>@endif
            @endif
            @foreach($unitLinks->getUrlRange($s,$e) as $page => $url)
            <a href="{{ $url }}"
               class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold transition {{ $page == $cur ? 'bg-primary text-white' : 'text-muted hover:text-primary hover:bg-[#f1f5f9]' }}">{{ $page }}</a>
            @endforeach
            @if($e<$last)
                @if($e<$last-1)<span class="px-1 text-muted text-xs">…</span>@endif
                <a href="{{ $unitLinks->url($last) }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">{{ $last }}</a>
            @endif
            @if($unitLinks->hasMorePages())
            <a href="{{ $unitLinks->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">Next →</a>
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
        <h3 class="font-bold text-primary text-base mb-1">Import Unit Link</h3>
        <p class="text-muted text-sm mb-4">Upload file Excel sesuai format template.</p>
        <form method="POST" action="{{ route('admin.unit-link.import') }}" enctype="multipart/form-data">
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
                <a href="{{ route('admin.unit-link.template') }}" class="text-xs text-accent hover:underline flex items-center gap-1">
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
