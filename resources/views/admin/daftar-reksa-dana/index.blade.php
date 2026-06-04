@extends('layouts.admin')

@section('title', 'Daftar Reksa Dana')

@section('content')
    <div class="mb-6">
        <h1 class="page-title">Daftar Reksa Dana</h1>
        <p class="page-sub">Master data reksa dana beserta riwayat harga harian</p>
    </div>

    @if (session('success'))
        <div
            class="alert-success">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-5 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">{{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Tab Navigation --}}
    <div class="flex gap-1 mb-5 border-b border-line">
        <a href="{{ route('admin.daftar-reksa-dana.index', array_merge(request()->except('tab', 'harga_page', 'harian_page'), ['tab' => 'harga'])) }}"
            class="px-5 py-2.5 text-sm font-semibold border-b-2 transition -mb-px {{ $tab === 'harga' ? 'border-primary text-primary' : 'border-transparent text-muted hover:text-primary' }}">
            Harga Reksa Dana
        </a>
        <a href="{{ route('admin.daftar-reksa-dana.index', array_merge(request()->except('tab', 'harga_page', 'harian_page', 'link_page', 'log_page', 'edit'), ['tab' => 'harian'])) }}"
            class="px-5 py-2.5 text-sm font-semibold border-b-2 transition -mb-px {{ $tab === 'harian' ? 'border-primary text-primary' : 'border-transparent text-muted hover:text-primary' }}">
            Harian Reksa Dana
        </a>
        <a href="{{ route('admin.daftar-reksa-dana.index', array_merge(request()->except('tab', 'harga_page', 'harian_page', 'link_page', 'log_page', 'edit'), ['tab' => 'link-website'])) }}"
            class="px-5 py-2.5 text-sm font-semibold border-b-2 transition -mb-px {{ $tab === 'link-website' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-muted hover:text-primary' }}">
            Link Website
        </a>
        <a href="{{ route('admin.daftar-reksa-dana.index', ['tab' => 'prospektus-ffs']) }}"
            class="px-5 py-2.5 text-sm font-semibold border-b-2 transition -mb-px {{ $tab === 'prospektus-ffs' ? 'border-emerald-700 text-emerald-700' : 'border-transparent text-muted hover:text-primary' }}">
            Prospektus dan FFS
        </a>
    </div>

    {{-- ===================== TAB HARGA ===================== --}}
    @if ($tab === 'harga')

        {{-- Upload Panel --}}
        <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden mb-5">
            <div
                class="px-5 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light flex items-center justify-between">
                <h2 class="font-bold text-white text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Upload Harga Reksa Dana
                </h2>
                <a href="{{ route('admin.daftar-reksa-dana.template-harga') }}"
                    class="flex items-center gap-1.5 px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Download Template
                </a>
            </div>
            <div class="p-5">
                <p class="text-xs text-muted mb-3">Kolom: <code class="bg-[#f1f5f9] px-1 rounded">kode_reksa_dana (opsional) |
                        nama_reksa_dana | nama_manajer_investasi | jenis | kategori | kategori_produk (Konvensional/Syariah/Index/ETF) | mata_uang | nab_per_unit | tanggal_nab</code>
                    — jika kode dikosongkan, akan digenerate otomatis dari kode MI + jenis + kategori produk.</p>
                <form method="POST" action="{{ route('admin.daftar-reksa-dana.upload-harga') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_redirect_tab" value="harga">
                    <div class="flex gap-2">
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                            class="flex-1 text-xs border border-line rounded-lg px-3 py-2 text-muted file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-primary/10 file:text-primary">
                        <button type="submit"
                            class="px-4 py-2 bg-primary text-white rounded-lg text-xs font-semibold hover:bg-primary/90 transition whitespace-nowrap">Upload</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabel Harga --}}
        <div class="table-card">
            <div
                class="table-head">
                <h2 class="font-bold text-white text-sm">Daftar Reksa Dana ({{ $reksaDanas->total() }} total)</h2>
                <div class="flex gap-2">
                    <button type="button" onclick="openModal('modal-harga-create')"
                        class="flex items-center gap-1.5 px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Baru
                    </button>
                    <form method="GET" action="{{ route('admin.daftar-reksa-dana.index') }}" class="flex gap-2">
                        <input type="hidden" name="tab" value="harga">
                        @if (request('jenis'))
                            <input type="hidden" name="jenis" value="{{ request('jenis') }}">
                        @endif
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama..."
                            class="text-xs border border-white/30 bg-white/10 text-white placeholder-white/50 rounded-lg px-3 py-1.5 w-44 focus:outline-none focus:bg-white/20">
                        <button type="submit"
                            class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition">Cari</button>
                    </form>
                </div>
            </div>

            {{-- Filter Jenis --}}
            <div class="px-6 py-3 border-b border-line flex gap-2 text-xs flex-wrap">
                @foreach (['', 'Saham', 'Pendapatan Tetap', 'Campuran', 'Pasar Uang', 'Terproteksi', 'Global', 'DIRE-DINFRA', 'Penyertaan terbatas'] as $j)
                    <a href="{{ route('admin.daftar-reksa-dana.index', array_merge(request()->except('jenis', 'harga_page'), ['tab' => 'harga'], $j ? ['jenis' => $j] : [])) }}"
                        class="px-3 py-1.5 rounded-lg border transition {{ request('jenis') === $j || (!request('jenis') && $j === '') ? 'bg-primary text-white border-primary' : 'border-line text-muted hover:bg-[#f1f5f9]' }}">
                        {{ $j ?: 'Semua' }}
                    </a>
                @endforeach
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                            <th class="px-4 py-3.5 font-semibold">Kode</th>
                            <th class="px-4 py-3.5 font-semibold">Nama Reksa Dana</th>
                            <th class="px-4 py-3.5 font-semibold">Manajer Investasi</th>
                            <th class="px-4 py-3.5 font-semibold">Jenis</th>
                            <th class="px-4 py-3.5 font-semibold">Kategori Produk</th>
                            <th class="px-4 py-3.5 font-semibold">Kategori</th>
                            <th class="px-4 py-3.5 font-semibold">Mata Uang</th>
                            <th class="px-4 py-3.5 font-semibold text-right">NAB/UP</th>
                            <th class="px-4 py-3.5 font-semibold">Tanggal NAB/UP</th>
                            <th class="px-4 py-3.5 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse($reksaDanas as $rd)
                            <tr class="hover:bg-[#f8fafc] transition-colors">
                                <td class="px-4 py-3.5 font-mono text-xs text-muted">{{ $rd->kode_reksa_dana ?? '—' }}</td>
                                 <td class="px-4 py-3.5 font-semibold text-primary">
                                    <a href="{{ route('admin.daftar-reksa-dana.show', $rd) }}" class="hover:underline text-primary">{{ $rd->nama_reksa_dana }}</a>
                                </td>
                                <td class="px-4 py-3.5 text-muted text-xs">{{ $rd->nama_manajer_investasi }}</td>
                                <td class="px-4 py-3.5">
                                    @php
                                        $jenisColor = match ($rd->jenis) {
                                            'Saham' => 'bg-blue-100 text-blue-700',
                                            'Pendapatan Tetap' => 'bg-amber-100 text-amber-700',
                                            'Campuran' => 'bg-purple-100 text-purple-700',
                                            default => 'bg-green-100 text-green-700',
                                        };
                                    @endphp
                                    <span
                                        class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $jenisColor }}">{{ $rd->jenis }}</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    @if($rd->kategori_produk)
                                        @php
                                            $kpColor = match ($rd->kategori_produk) {
                                                'Konvensional' => 'bg-green-100 text-green-700',
                                                'Syariah' => 'bg-emerald-100 text-emerald-700',
                                                'Index' => 'bg-blue-100 text-blue-700',
                                                'ETF' => 'bg-purple-100 text-purple-700',
                                                default => 'bg-gray-100 text-gray-700',
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $kpColor }}">{{ $rd->kategori_produk }}</span>
                                    @else
                                        <span class="text-xs text-muted">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-xs text-muted">
                                    @if (is_array($rd->kategori) && count($rd->kategori))
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($rd->kategori as $kat)
                                                <span
                                                    class="px-1.5 py-0.5 bg-[#f1f5f9] rounded text-[11px]">{{ $kat }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-xs text-muted">{{ $rd->mata_uang }}</td>
                                <td class="px-4 py-3.5 text-right text-xs font-semibold text-primary">
                                     {{ $rd->nab_per_unit ? number_format($rd->nab_per_unit, 4, ',', '.') : '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-xs text-muted">
                                    {{ $rd->tanggal_nab ? $rd->tanggal_nab->format('d M Y') : '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button" onclick='openEditHarga(@json($rd))'
                                            class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <form method="POST" action="{{ route('admin.daftar-reksa-dana.harga.destroy', $rd) }}" class="inline"
                                            onsubmit="return confirm('Yakin ingin menghapus {{ $rd->nama_reksa_dana }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-12 text-center text-muted">
                                    <p class="font-medium">Belum ada data</p>
                                    <p class="text-xs mt-1">Upload file excel menggunakan form di atas</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($reksaDanas->hasPages())
                <div class="px-6 py-4 border-t border-line flex items-center justify-between text-sm">
                    <p class="text-muted text-xs">{{ $reksaDanas->firstItem() }}–{{ $reksaDanas->lastItem() }} dari
                        {{ $reksaDanas->total() }}</p>
                    <div class="flex items-center gap-1">
                        @if (!$reksaDanas->onFirstPage())
                            <a href="{{ $reksaDanas->previousPageUrl() }}"
                                class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">←
                                Prev</a>
                        @endif
                        @php $cur=$reksaDanas->currentPage();$last=$reksaDanas->lastPage();$s=max(1,$cur-2);$e=min($last,$cur+2); @endphp
                        @if($s>1)
                            <a href="{{ $reksaDanas->url(1) }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">1</a>
                            @if($s>2)<span class="px-1 text-muted text-xs">…</span>@endif
                        @endif
                        @foreach ($reksaDanas->getUrlRange($s,$e) as $page => $url)
                            <a href="{{ $url }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold transition {{ $page == $cur ? 'bg-primary text-white' : 'text-muted hover:text-primary hover:bg-[#f1f5f9]' }}">{{ $page }}</a>
                        @endforeach
                        @if($e<$last)
                            @if($e<$last-1)<span class="px-1 text-muted text-xs">…</span>@endif
                            <a href="{{ $reksaDanas->url($last) }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">{{ $last }}</a>
                        @endif
                        @if ($reksaDanas->hasMorePages())
                            <a href="{{ $reksaDanas->nextPageUrl() }}"
                                class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">Next
                                →</a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- ===================== TAB HARIAN ===================== --}}
    @elseif($tab === 'harian')
        {{-- Upload Panel --}}
        <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden mb-5">
            <div
                class="px-5 py-4 border-b border-line bg-gradient-to-r from-accent to-accent/80 flex items-center justify-between">
                <h2 class="font-bold text-white text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Upload Harian Reksa Dana
                </h2>
                <a href="{{ route('admin.daftar-reksa-dana.template-harian') }}"
                    class="flex items-center gap-1.5 px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Download Template
                </a>
            </div>
            <div class="p-5">
                <p class="text-xs text-muted mb-3">Kolom: <code class="bg-[#f1f5f9] px-1 rounded">nama_reksa_dana |
                        tanggal | nab_per_unit | total_dana_kelolaan | unit_penyertaan</code></p>
                <form method="POST" action="{{ route('admin.daftar-reksa-dana.upload-harian') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="flex gap-2">
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                            class="flex-1 text-xs border border-line rounded-lg px-3 py-2 text-muted file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-accent/10 file:text-accent">
                        <button type="submit"
                            class="px-4 py-2 bg-accent text-white rounded-lg text-xs font-semibold hover:bg-accent/90 transition whitespace-nowrap">Upload</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabel Harian --}}
        <div class="table-card">
            <div
                class="px-6 py-4 border-b border-line flex items-center justify-between bg-gradient-to-r from-accent to-accent/80">
                <h2 class="font-bold text-white text-sm">Riwayat Harian ({{ $harian->total() }} data)</h2>
                <div class="flex gap-2">
                    <button type="button" onclick="openModal('modal-harian-create')"
                        class="flex items-center gap-1.5 px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Baru
                    </button>
                    <form method="GET" action="{{ route('admin.daftar-reksa-dana.index') }}" class="flex gap-2">
                        <input type="hidden" name="tab" value="harian">
                        <input type="date" name="harian_tanggal" value="{{ $harianTanggal }}"
                            class="text-xs border border-white/30 bg-white/10 text-white rounded-lg px-3 py-1.5 focus:outline-none focus:bg-white/20 [color-scheme:dark]">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama reksa dana..."
                            class="text-xs border border-white/30 bg-white/10 text-white placeholder-white/50 rounded-lg px-3 py-1.5 w-44 focus:outline-none focus:bg-white/20">
                        <button type="submit"
                            class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition">Cari</button>
                        @if($harianTanggal || request('search'))
                            <a href="{{ route('admin.daftar-reksa-dana.index', ['tab' => 'harian']) }}"
                                class="px-3 py-1.5 bg-white/10 hover:bg-white/20 text-white rounded-lg text-xs font-semibold transition">Reset</a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                            <th class="px-4 py-3.5 font-semibold">Tanggal</th>
                            <th class="px-4 py-3.5 font-semibold">Kode</th>
                            <th class="px-4 py-3.5 font-semibold">Reksadana</th>
                            <th class="px-4 py-3.5 font-semibold text-right">NAB/UP</th>
                            <th class="px-4 py-3.5 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse($harian as $h)
                            <tr class="hover:bg-[#f8fafc] transition-colors">
                                <td class="px-4 py-3.5 text-xs text-muted">{{ $h->tanggal->format('d M Y') }}</td>
                                <td class="px-4 py-3.5 font-mono text-xs text-muted">{{ $h->reksaDana->kode_reksa_dana ?? '—' }}</td>
                                <td class="px-4 py-3.5 font-semibold text-primary text-sm">
                                    <a href="{{ $h->reksaDana ? route('admin.daftar-reksa-dana.show', $h->reksaDana) : '#' }}" class="hover:underline text-primary">{{ $h->reksaDana->nama_reksa_dana ?? '—' }}</a>
                                </td>
                                <td class="px-4 py-3.5 text-right text-xs font-semibold text-primary">
                                    {{ number_format($h->nab_per_unit, 4, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button" onclick='openEditHarian(@json($h))'
                                            class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <form method="POST" action="{{ route('admin.daftar-reksa-dana.harian.destroy', $h) }}" class="inline"
                                            onsubmit="return confirm('Yakin ingin menghapus data harian ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-muted">
                                    <p class="font-medium">Belum ada data harian</p>
                                    <p class="text-xs mt-1">Upload file excel menggunakan form di atas</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($harian->hasPages())
                <div class="px-6 py-4 border-t border-line flex items-center justify-between text-sm">
                    <p class="text-muted text-xs">{{ $harian->firstItem() }}–{{ $harian->lastItem() }} dari
                        {{ $harian->total() }}</p>
                    <div class="flex items-center gap-1">
                        @if (!$harian->onFirstPage())
                            <a href="{{ $harian->previousPageUrl() }}"
                                class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">←
                                Prev</a>
                        @endif
                        @php $cur=$harian->currentPage();$last=$harian->lastPage();$s=max(1,$cur-2);$e=min($last,$cur+2); @endphp
                        @if($s>1)
                            <a href="{{ $harian->url(1) }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">1</a>
                            @if($s>2)<span class="px-1 text-muted text-xs">…</span>@endif
                        @endif
                        @foreach ($harian->getUrlRange($s,$e) as $page => $url)
                            <a href="{{ $harian->currentPage() == $page ? '#' : $url }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold transition {{ $page == $cur ? 'bg-accent text-white' : 'text-muted hover:text-primary hover:bg-[#f1f5f9]' }}">{{ $page }}</a>
                        @endforeach
                        @if($e<$last)
                            @if($e<$last-1)<span class="px-1 text-muted text-xs">…</span>@endif
                            <a href="{{ $harian->url($last) }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">{{ $last }}</a>
                        @endif
                        @if ($harian->hasMorePages())
                            <a href="{{ $harian->nextPageUrl() }}"
                                class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">Next
                                →</a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- ===================== TAB LINK WEBSITE ===================== --}}
    @elseif($tab === 'link-website')
        @include('admin.daftar-reksa-dana.partials.tab-link-website')
    @elseif($tab === 'prospektus-ffs')
        @include('admin.daftar-reksa-dana.partials.tab-prospektus-ffs')
    @endif
{{-- ===================== MODAL HARGA CREATE ===================== --}}
<div id="modal-harga-create" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4" onclick="if(event.target===this)closeModal('modal-harga-create')">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-line sticky top-0 bg-white z-10">
            <h3 class="font-bold text-primary">Tambah Reksa Dana</h3>
            <button type="button" onclick="closeModal('modal-harga-create')" class="p-1 hover:bg-[#f1f5f9] rounded-lg transition">
                <svg class="w-5 h-5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.daftar-reksa-dana.harga.store') }}" class="p-6 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Kode Reksa Dana</label>
                    <input type="text" name="kode_reksa_dana" class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Nama Reksa Dana <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_reksa_dana" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">Nama Manajer Investasi <span class="text-red-500">*</span></label>
                <input type="text" name="nama_manajer_investasi" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Jenis <span class="text-red-500">*</span></label>
                    <select name="jenis" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                        <option value="">— Pilih Jenis —</option>
                        @foreach (['Saham', 'Pendapatan Tetap', 'Campuran', 'Pasar Uang', 'Terproteksi', 'Global', 'DIRE-DINFRA', 'Penyertaan terbatas'] as $j)
                            <option value="{{ $j }}">{{ $j }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Kategori Produk</label>
                    <select name="kategori_produk" class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                        <option value="">— Pilih —</option>
                        @foreach (['Konvensional', 'Syariah', 'Index', 'ETF'] as $kp)
                            <option value="{{ $kp }}">{{ $kp }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">Kategori</label>
                <div class="flex flex-wrap gap-3">
                    @foreach (['Konvensional', 'Syariah', 'index', 'ETF'] as $kat)
                        <label class="flex items-center gap-1.5 text-sm">
                            <input type="checkbox" name="kategori[]" value="{{ $kat }}" class="rounded border-line text-primary focus:ring-primary/20">
                            {{ $kat }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Benchmark</label>
                    <input type="text" name="benchmark" class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Mata Uang</label>
                    <input type="text" name="mata_uang" value="IDR" class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">Tujuan Investasi</label>
                <textarea name="tujuan_investasi" rows="2" class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">Kebijakan Investasi</label>
                <textarea name="kebijakan_investasi" rows="2" class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">NAB/UP</label>
                    <input type="number" step="0.000001" name="nab_per_unit" class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Tanggal NAB</label>
                    <input type="date" name="tanggal_nab" class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('modal-harga-create')" class="px-4 py-2 text-sm text-muted border border-line rounded-lg hover:bg-[#f1f5f9] transition">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm text-white bg-primary rounded-lg hover:bg-primary/90 transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ===================== MODAL HARGA EDIT ===================== --}}
<div id="modal-harga-edit" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4" onclick="if(event.target===this)closeModal('modal-harga-edit')">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-line sticky top-0 bg-white z-10">
            <h3 class="font-bold text-primary">Edit Reksa Dana</h3>
            <button type="button" onclick="closeModal('modal-harga-edit')" class="p-1 hover:bg-[#f1f5f9] rounded-lg transition">
                <svg class="w-5 h-5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="" class="p-6 space-y-4" id="form-harga-edit">
            @csrf
            @method('POST')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Kode Reksa Dana</label>
                    <input type="text" name="kode_reksa_dana" id="edit-harga-kode" class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Nama Reksa Dana <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_reksa_dana" id="edit-harga-nama" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">Nama Manajer Investasi <span class="text-red-500">*</span></label>
                <input type="text" name="nama_manajer_investasi" id="edit-harga-mi" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Jenis <span class="text-red-500">*</span></label>
                    <select name="jenis" id="edit-harga-jenis" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                        @foreach (['Saham', 'Pendapatan Tetap', 'Campuran', 'Pasar Uang', 'Terproteksi', 'Global', 'DIRE-DINFRA', 'Penyertaan terbatas'] as $j)
                            <option value="{{ $j }}">{{ $j }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Kategori Produk</label>
                    <select name="kategori_produk" id="edit-harga-kp" class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                        <option value="">— Pilih —</option>
                        @foreach (['Konvensional', 'Syariah', 'Index', 'ETF'] as $kp)
                            <option value="{{ $kp }}">{{ $kp }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">Kategori</label>
                <div class="flex flex-wrap gap-3" id="edit-harga-kategori-container">
                    @foreach (['Konvensional', 'Syariah', 'index', 'ETF'] as $kat)
                        <label class="flex items-center gap-1.5 text-sm">
                            <input type="checkbox" name="kategori[]" value="{{ $kat }}" class="kategori-checkbox rounded border-line text-primary focus:ring-primary/20">
                            {{ $kat }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Benchmark</label>
                    <input type="text" name="benchmark" id="edit-harga-benchmark" class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Mata Uang</label>
                    <input type="text" name="mata_uang" id="edit-harga-matauang" class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">Tujuan Investasi</label>
                <textarea name="tujuan_investasi" id="edit-harga-tujuan" rows="2" class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">Kebijakan Investasi</label>
                <textarea name="kebijakan_investasi" id="edit-harga-kebijakan" rows="2" class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">NAB/UP</label>
                    <input type="number" step="0.000001" name="nab_per_unit" id="edit-harga-nab" class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Tanggal NAB</label>
                    <input type="date" name="tanggal_nab" id="edit-harga-tgl-nab" class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('modal-harga-edit')" class="px-4 py-2 text-sm text-muted border border-line rounded-lg hover:bg-[#f1f5f9] transition">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm text-white bg-primary rounded-lg hover:bg-primary/90 transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ===================== MODAL HARIAN CREATE ===================== --}}
<div id="modal-harian-create" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4" onclick="if(event.target===this)closeModal('modal-harian-create')">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b border-line">
            <h3 class="font-bold text-primary">Tambah Data Harian</h3>
            <button type="button" onclick="closeModal('modal-harian-create')" class="p-1 hover:bg-[#f1f5f9] rounded-lg transition">
                <svg class="w-5 h-5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.daftar-reksa-dana.harian.store') }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">Reksa Dana <span class="text-red-500">*</span></label>
                <select name="reksa_dana_id" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-accent focus:ring focus:ring-accent/20">
                    <option value="">— Pilih Reksa Dana —</option>
                    @foreach($reksaDanaOptions as $rd)
                        <option value="{{ $rd->id }}">{{ $rd->kode_reksa_dana ? '['.$rd->kode_reksa_dana.'] ' : '' }}{{ $rd->nama_reksa_dana }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-accent focus:ring focus:ring-accent/20">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">NAB/UP <span class="text-red-500">*</span></label>
                    <input type="number" step="0.000001" name="nab_per_unit" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-accent focus:ring focus:ring-accent/20">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('modal-harian-create')" class="px-4 py-2 text-sm text-muted border border-line rounded-lg hover:bg-[#f1f5f9] transition">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm text-white bg-accent rounded-lg hover:bg-accent/90 transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ===================== MODAL HARIAN EDIT ===================== --}}
<div id="modal-harian-edit" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4" onclick="if(event.target===this)closeModal('modal-harian-edit')">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b border-line">
            <div>
                <h3 class="font-bold text-primary">Edit Data Harian</h3>
                <p class="text-xs text-muted mt-0.5">
                    Kode: <span id="edit-harian-info-kode" class="font-semibold text-primary">—</span>
                    &nbsp;·&nbsp; Tanggal: <span id="edit-harian-info-tanggal" class="font-semibold text-primary">—</span>
                </p>
            </div>
            <button type="button" onclick="closeModal('modal-harian-edit')" class="p-1 hover:bg-[#f1f5f9] rounded-lg transition">
                <svg class="w-5 h-5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="" class="p-6 space-y-4" id="form-harian-edit">
            @csrf
            @method('POST')
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">Reksa Dana <span class="text-red-500">*</span></label>
                <select name="reksa_dana_id" id="edit-harian-rd" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-accent focus:ring focus:ring-accent/20">
                    @foreach($reksaDanaOptions as $rd)
                        <option value="{{ $rd->id }}">{{ $rd->kode_reksa_dana ? '['.$rd->kode_reksa_dana.'] ' : '' }}{{ $rd->nama_reksa_dana }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Kode</label>
                    <input type="text" id="edit-harian-kode" readonly class="w-full border border-line rounded-lg px-3 py-2 text-sm bg-[#f8fafc] text-muted">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal" id="edit-harian-tanggal" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-accent focus:ring focus:ring-accent/20">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">NAB/UP <span class="text-red-500">*</span></label>
                    <input type="number" step="0.000001" name="nab_per_unit" id="edit-harian-nab" required class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-accent focus:ring focus:ring-accent/20">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('modal-harian-edit')" class="px-4 py-2 text-sm text-muted border border-line rounded-lg hover:bg-[#f1f5f9] transition">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm text-white bg-accent rounded-lg hover:bg-accent/90 transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ===================== JAVASCRIPT ===================== --}}
<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

function openEditHarga(data) {
    const form = document.getElementById('form-harga-edit');
    form.action = '{{ route("admin.daftar-reksa-dana.harga.update", "REPLACE_ID") }}'.replace('REPLACE_ID', data.id);

    document.getElementById('edit-harga-kode').value = data.kode_reksa_dana || '';
    document.getElementById('edit-harga-nama').value = data.nama_reksa_dana;
    document.getElementById('edit-harga-mi').value = data.nama_manajer_investasi;
    document.getElementById('edit-harga-jenis').value = data.jenis;
    document.getElementById('edit-harga-kp').value = data.kategori_produk || '';
    document.getElementById('edit-harga-benchmark').value = data.benchmark || '';
    document.getElementById('edit-harga-matauang').value = data.mata_uang || 'IDR';
    document.getElementById('edit-harga-tujuan').value = data.tujuan_investasi || '';
    document.getElementById('edit-harga-kebijakan').value = data.kebijakan_investasi || '';
    document.getElementById('edit-harga-nab').value = data.nab_per_unit || '';
    document.getElementById('edit-harga-tgl-nab').value = data.tanggal_nab || '';

    document.querySelectorAll('#edit-harga-kategori-container .kategori-checkbox').forEach(cb => {
        cb.checked = Array.isArray(data.kategori) && data.kategori.includes(cb.value);
    });

    openModal('modal-harga-edit');
}

function openEditHarian(data) {
    const form = document.getElementById('form-harian-edit');
    form.action = '{{ route("admin.daftar-reksa-dana.harian.update", "REPLACE_ID") }}'.replace('REPLACE_ID', data.id);

    document.getElementById('edit-harian-rd').value = data.reksa_dana_id;
    document.getElementById('edit-harian-tanggal').value = (data.tanggal || '').substring(0, 10);
    document.getElementById('edit-harian-nab').value = data.nab_per_unit;
    document.getElementById('edit-harian-kode').value = data.reksa_dana?.kode_reksa_dana || '—';

    // Update header info juga
    document.getElementById('edit-harian-info-kode').textContent = data.reksa_dana?.kode_reksa_dana || '—';
    document.getElementById('edit-harian-info-tanggal').textContent = (data.tanggal || '').substring(0, 10);

    openModal('modal-harian-edit');
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.fixed.inset-0.z-50').forEach(m => {
            if (!m.classList.contains('hidden')) m.classList.add('hidden');
        });
    }
});
</script>
@endsection
