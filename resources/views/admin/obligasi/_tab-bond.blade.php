{{-- Search & Actions --}}
<div class="mb-5 flex items-center justify-between gap-3 flex-wrap">
    <form method="GET" action="{{ route('admin.obligasi.index') }}" class="flex items-center gap-3 flex-1">
        <input type="hidden" name="tab" value="bond">
        <div class="relative flex-1 max-w-md">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   class="w-full pl-10 pr-4 py-2.5 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                   placeholder="Cari kode atau periode...">
        </div>
        <button type="submit" class="px-4 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition">Cari</button>
        @if(request('search'))<a href="{{ route('admin.obligasi.index', ['tab' => 'bond']) }}" class="px-4 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">Reset</a>@endif
    </form>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.obligasi.template-bond') }}"
           class="btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Template
        </a>
        <button @click="showImportBond = true"
                class="btn-outline">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Import
        </button>
        <a href="{{ route('admin.obligasi.create-bond') }}"
           class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah
        </a>
    </div>
</div>

<div class="table-card">
    <div class="table-head">
        <h2 class="th-title">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Keuangan Emiten
        </h2>
        <div class="flex items-center gap-2">
            <span class="th-meta">Tampilkan:</span>
            <form method="GET" action="{{ route('admin.obligasi.index') }}">
                <input type="hidden" name="tab" value="bond">
                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                <select name="per_page" onchange="this.form.submit()"
                        class="text-xs bg-white/10 text-white border border-white/20 rounded-lg px-2 py-1 focus:outline-none cursor-pointer">
                    @foreach([10, 25, 50] as $n)
                    <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </form>
            <span class="th-meta">{{ $bonds->total() }} total</span>
        </div>
    </div>
    @if ($lastSyncRun)
        <div class="px-5 py-2 bg-blue-50 border-b border-blue-200 flex items-center gap-2 text-xs text-blue-800">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span class="font-semibold">Sinkronisasi Terakhir :</span>
            <span>{{ $lastSyncRun->completed_at ? $lastSyncRun->completed_at->format('d M Y H:i') : $lastSyncRun->created_at->format('d M Y H:i') }} WIB</span>
            @if ($lastSyncRun->stats && isset($lastSyncRun->stats['total']))
                <span class="text-blue-600">({{ number_format($lastSyncRun->stats['total']) }} data)</span>
            @endif
        </div>
    @endif

    @if($bonds->isEmpty())
    <div class="py-16 text-center text-muted">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <p class="font-medium">Belum ada data</p>
        <p class="text-sm mt-1">Klik "Tambah" atau import dari Excel</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                    <th class="px-4 py-3.5 font-semibold">Kode</th>
                    <th class="px-4 py-3.5 font-semibold">Periode</th>
                    <th class="px-4 py-3.5 font-semibold text-right">Total Asset</th>
                    <th class="px-4 py-3.5 font-semibold text-right">Total Liabilities</th>
                    <th class="px-4 py-3.5 font-semibold text-right">Equity</th>
                    <th class="px-4 py-3.5 font-semibold text-right">Net Revenue</th>
                    <th class="px-4 py-3.5 font-semibold text-right">Net Income</th>
                    <th class="px-4 py-3.5 font-semibold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @foreach($bonds as $o)
                <tr class="hover:bg-[#f8fafc] transition-colors">
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.sekuritas.obligasi', $o->kode) }}"
                            class="w-fit px-2.5 py-1 rounded-lg bg-primary/10 text-primary font-bold text-xs hover:bg-primary/20 transition">{{ $o->kode }}</a>
                    </td>
                    <td class="px-4 py-3 text-xs">{{ $o->periode }}</td>
                    <td class="px-4 py-3 text-right text-xs">{{ $o->total_asset ? number_format($o->total_asset, 0, ',', '.') : '-' }}</td>
                    <td class="px-4 py-3 text-right text-xs">{{ $o->total_liabilities ? number_format($o->total_liabilities, 0, ',', '.') : '-' }}</td>
                    <td class="px-4 py-3 text-right text-xs">{{ $o->equity ? number_format($o->equity, 0, ',', '.') : '-' }}</td>
                    <td class="px-4 py-3 text-right text-xs">{{ $o->net_revenue ? number_format($o->net_revenue, 0, ',', '.') : '-' }}</td>
                    <td class="px-4 py-3 text-right text-xs">{{ $o->net_income ? number_format($o->net_income, 0, ',', '.') : '-' }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('admin.obligasi.edit-bond', $o) }}"
                               class="p-2 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button type="button"
                                    @click="deleteId = {{ $o->id }}; deleteType = 'bond'; deleteText = '{{ addslashes($o->kode . ' - ' . $o->periode) }}'"
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

    @if($bonds->hasPages())
    <div class="px-6 py-4 border-t border-line flex items-center justify-between gap-4 text-sm">
        <p class="text-muted text-xs">Menampilkan {{ $bonds->firstItem() }}–{{ $bonds->lastItem() }} dari {{ $bonds->total() }}</p>
        <div class="flex items-center gap-1">
            @if($bonds->onFirstPage())
            <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">← Prev</span>
            @else
            <a href="{{ $bonds->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">← Prev</a>
            @endif
            @php $cur=$bonds->currentPage();$last=$bonds->lastPage();$s=max(1,$cur-2);$e=min($last,$cur+2); @endphp
            @if($s>1)
                <a href="{{ $bonds->url(1) }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">1</a>
                @if($s>2)<span class="px-1 text-muted text-xs">…</span>@endif
            @endif
            @foreach($bonds->getUrlRange($s,$e) as $page => $url)
            <a href="{{ $url }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold transition {{ $page == $cur ? 'bg-primary text-white' : 'text-muted hover:text-primary hover:bg-[#f1f5f9]' }}">{{ $page }}</a>
            @endforeach
            @if($e<$last)
                @if($e<$last-1)<span class="px-1 text-muted text-xs">…</span>@endif
                <a href="{{ $bonds->url($last) }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">{{ $last }}</a>
            @endif
            @if($bonds->hasMorePages())
            <a href="{{ $bonds->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">Next →</a>
            @else
            <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">Next →</span>
            @endif
        </div>
    </div>
    @endif
    @endif
</div>
