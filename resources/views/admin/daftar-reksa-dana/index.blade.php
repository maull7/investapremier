@extends('layouts.admin')

@section('title', 'Daftar Reksa Dana')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-primary">Daftar Reksa Dana</h1>
    <p class="text-muted text-sm mt-1">Master data reksa dana beserta riwayat harga harian</p>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-5 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
    <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

{{-- Tab Navigation --}}
<div class="flex gap-1 mb-5 border-b border-line">
    <a href="{{ route('admin.daftar-reksa-dana.index', array_merge(request()->except('tab', 'harga_page', 'harian_page'), ['tab' => 'harga'])) }}"
       class="px-5 py-2.5 text-sm font-semibold border-b-2 transition -mb-px {{ $tab === 'harga' ? 'border-primary text-primary' : 'border-transparent text-muted hover:text-primary' }}">
        Harga Reksa Dana
    </a>
    <a href="{{ route('admin.daftar-reksa-dana.index', array_merge(request()->except('tab', 'harga_page', 'harian_page'), ['tab' => 'harian'])) }}"
       class="px-5 py-2.5 text-sm font-semibold border-b-2 transition -mb-px {{ $tab === 'harian' ? 'border-primary text-primary' : 'border-transparent text-muted hover:text-primary' }}">
        Harian Reksa Dana
    </a>
</div>

{{-- ===================== TAB HARGA ===================== --}}
@if($tab === 'harga')

{{-- Upload Panel --}}
<div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden mb-5">
    <div class="px-5 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light flex items-center justify-between">
        <h2 class="font-bold text-white text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Upload Harga Reksa Dana
        </h2>
        <a href="{{ route('admin.daftar-reksa-dana.template-harga') }}"
           class="flex items-center gap-1.5 px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Download Template
        </a>
    </div>
    <div class="p-5">
        <p class="text-xs text-muted mb-3">Kolom: <code class="bg-[#f1f5f9] px-1 rounded">nama_reksa_dana | nama_manajer_investasi | jenis | kategori | mata_uang | nab_per_unit | tanggal_nab</code></p>
        <form method="POST" action="{{ route('admin.daftar-reksa-dana.upload-harga') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_redirect_tab" value="harga">
            <div class="flex gap-2">
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                       class="flex-1 text-xs border border-line rounded-lg px-3 py-2 text-muted file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-primary/10 file:text-primary">
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-xs font-semibold hover:bg-primary/90 transition whitespace-nowrap">Upload</button>
            </div>
        </form>
    </div>
</div>

{{-- Tabel Harga --}}
<div class="bg-white rounded-2xl border border-line overflow-hidden shadow-sm">
    <div class="px-6 py-4 border-b border-line flex items-center justify-between bg-gradient-to-r from-primary to-primary-light">
        <h2 class="font-bold text-white text-sm">Daftar Reksa Dana ({{ $reksaDanas->total() }} total)</h2>
        <form method="GET" action="{{ route('admin.daftar-reksa-dana.index') }}" class="flex gap-2">
            <input type="hidden" name="tab" value="harga">
            @if(request('jenis'))<input type="hidden" name="jenis" value="{{ request('jenis') }}">@endif
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama..."
                   class="text-xs border border-white/30 bg-white/10 text-white placeholder-white/50 rounded-lg px-3 py-1.5 w-44 focus:outline-none focus:bg-white/20">
            <button type="submit" class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition">Cari</button>
        </form>
    </div>

    {{-- Filter Jenis --}}
    <div class="px-6 py-3 border-b border-line flex gap-2 text-xs flex-wrap">
        @foreach(['', 'Saham', 'Pendapatan Tetap', 'Campuran', 'Pasar Uang'] as $j)
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
                    <th class="px-4 py-3.5 font-semibold">Nama Reksa Dana</th>
                    <th class="px-4 py-3.5 font-semibold">Manajer Investasi</th>
                    <th class="px-4 py-3.5 font-semibold">Jenis</th>
                    <th class="px-4 py-3.5 font-semibold">Kategori</th>
                    <th class="px-4 py-3.5 font-semibold">Mata Uang</th>
                    <th class="px-4 py-3.5 font-semibold text-right">NAB/UP</th>
                    <th class="px-4 py-3.5 font-semibold">Tanggal NAB/UP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse($reksaDanas as $rd)
                <tr class="hover:bg-[#f8fafc] transition-colors">
                    <td class="px-4 py-3.5 font-semibold text-primary">{{ $rd->nama_reksa_dana }}</td>
                    <td class="px-4 py-3.5 text-muted text-xs">{{ $rd->nama_manajer_investasi }}</td>
                    <td class="px-4 py-3.5">
                        @php $jenisColor = match($rd->jenis) { 'Saham' => 'bg-blue-100 text-blue-700', 'Pendapatan Tetap' => 'bg-amber-100 text-amber-700', 'Campuran' => 'bg-purple-100 text-purple-700', default => 'bg-green-100 text-green-700' }; @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $jenisColor }}">{{ $rd->jenis }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-xs text-muted">
                        @if(is_array($rd->kategori) && count($rd->kategori))
                            <div class="flex flex-wrap gap-1">
                                @foreach($rd->kategori as $kat)
                                    <span class="px-1.5 py-0.5 bg-[#f1f5f9] rounded text-[11px]">{{ $kat }}</span>
                                @endforeach
                            </div>
                        @else —
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-xs text-muted">{{ $rd->mata_uang }}</td>
                    <td class="px-4 py-3.5 text-right text-xs font-semibold text-primary">
                        {{ $rd->nab_per_unit ? number_format($rd->nab_per_unit, 2, ',', '.') : '—' }}
                    </td>
                    <td class="px-4 py-3.5 text-xs text-muted">
                        {{ $rd->tanggal_nab ? $rd->tanggal_nab->format('d M Y') : '—' }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-12 text-center text-muted">
                    <p class="font-medium">Belum ada data</p>
                    <p class="text-xs mt-1">Upload file excel menggunakan form di atas</p>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($reksaDanas->hasPages())
    <div class="px-6 py-4 border-t border-line flex items-center justify-between text-sm">
        <p class="text-muted text-xs">{{ $reksaDanas->firstItem() }}–{{ $reksaDanas->lastItem() }} dari {{ $reksaDanas->total() }}</p>
        <div class="flex items-center gap-1">
            @if(!$reksaDanas->onFirstPage())
            <a href="{{ $reksaDanas->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">← Prev</a>
            @endif
            @foreach($reksaDanas->getUrlRange(1, $reksaDanas->lastPage()) as $page => $url)
            <a href="{{ $url }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold transition {{ $page == $reksaDanas->currentPage() ? 'bg-primary text-white' : 'text-muted hover:text-primary hover:bg-[#f1f5f9]' }}">{{ $page }}</a>
            @endforeach
            @if($reksaDanas->hasMorePages())
            <a href="{{ $reksaDanas->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">Next →</a>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- ===================== TAB HARIAN ===================== --}}
@else

{{-- Upload Panel --}}
<div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden mb-5">
    <div class="px-5 py-4 border-b border-line bg-gradient-to-r from-accent to-accent/80 flex items-center justify-between">
        <h2 class="font-bold text-white text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Upload Harian Reksa Dana
        </h2>
        <a href="{{ route('admin.daftar-reksa-dana.template-harian') }}"
           class="flex items-center gap-1.5 px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Download Template
        </a>
    </div>
    <div class="p-5">
        <p class="text-xs text-muted mb-3">Kolom: <code class="bg-[#f1f5f9] px-1 rounded">nama_reksa_dana | tanggal | nab_per_unit | total_dana_kelolaan | unit_penyertaan</code></p>
        <form method="POST" action="{{ route('admin.daftar-reksa-dana.upload-harian') }}" enctype="multipart/form-data">
            @csrf
            <div class="flex gap-2">
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                       class="flex-1 text-xs border border-line rounded-lg px-3 py-2 text-muted file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-accent/10 file:text-accent">
                <button type="submit" class="px-4 py-2 bg-accent text-white rounded-lg text-xs font-semibold hover:bg-accent/90 transition whitespace-nowrap">Upload</button>
            </div>
        </form>
    </div>
</div>

{{-- Tabel Harian --}}
<div class="bg-white rounded-2xl border border-line overflow-hidden shadow-sm">
    <div class="px-6 py-4 border-b border-line flex items-center justify-between bg-gradient-to-r from-accent to-accent/80">
        <h2 class="font-bold text-white text-sm">Riwayat Harian ({{ $harian->total() }} data)</h2>
        <form method="GET" action="{{ route('admin.daftar-reksa-dana.index') }}" class="flex gap-2">
            <input type="hidden" name="tab" value="harian">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama reksa dana..."
                   class="text-xs border border-white/30 bg-white/10 text-white placeholder-white/50 rounded-lg px-3 py-1.5 w-44 focus:outline-none focus:bg-white/20">
            <button type="submit" class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition">Cari</button>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                    <th class="px-4 py-3.5 font-semibold">Tanggal</th>
                    <th class="px-4 py-3.5 font-semibold">Reksadana</th>
                    <th class="px-4 py-3.5 font-semibold text-right">NAB</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse($harian as $h)
                <tr class="hover:bg-[#f8fafc] transition-colors">
                    <td class="px-4 py-3.5 text-xs text-muted">{{ $h->tanggal->format('d M Y') }}</td>
                    <td class="px-4 py-3.5 font-semibold text-primary text-sm">{{ $h->reksaDana->nama_reksa_dana ?? '—' }}</td>
                    <td class="px-4 py-3.5 text-right text-xs font-semibold text-primary">
                        {{ number_format($h->nab_per_unit, 2, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="px-6 py-12 text-center text-muted">
                    <p class="font-medium">Belum ada data harian</p>
                    <p class="text-xs mt-1">Upload file excel menggunakan form di atas</p>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($harian->hasPages())
    <div class="px-6 py-4 border-t border-line flex items-center justify-between text-sm">
        <p class="text-muted text-xs">{{ $harian->firstItem() }}–{{ $harian->lastItem() }} dari {{ $harian->total() }}</p>
        <div class="flex items-center gap-1">
            @if(!$harian->onFirstPage())
            <a href="{{ $harian->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">← Prev</a>
            @endif
            @foreach($harian->getUrlRange(1, $harian->lastPage()) as $page => $url)
            <a href="{{ $harian->currentPage() == $page ? '#' : $url }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold transition {{ $page == $harian->currentPage() ? 'bg-accent text-white' : 'text-muted hover:text-primary hover:bg-[#f1f5f9]' }}">{{ $page }}</a>
            @endforeach
            @if($harian->hasMorePages())
            <a href="{{ $harian->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">Next →</a>
            @endif
        </div>
    </div>
    @endif
</div>

@endif
@endsection
