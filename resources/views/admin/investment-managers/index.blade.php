@extends('layouts.admin')

@section('title', 'Manajer Investasi - InvestaPremier')

@section('content')
<div x-data="{ showImport: false }">

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-primary">Manajer Investasi</h1>
        <p class="text-muted text-sm mt-1">Kelola data manajer investasi beserta AUM dan UP per periode</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.investment-managers.template') }}"
           class="flex items-center gap-2 px-4 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary hover:border-primary/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Template
        </a>
        <button @click="showImport = true"
                class="flex items-center gap-2 px-4 py-2.5 border border-accent text-accent rounded-xl text-sm font-semibold hover:bg-accent/5 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Import
        </button>
        <a href="{{ route('admin.investment-managers.create') }}"
           class="flex items-center gap-2 px-4 py-2.5 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent/90 transition shadow-sm shadow-accent/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah
        </a>
    </div>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('success') }}
</div>
@endif

<div class="mb-5">
    <form method="GET" action="{{ route('admin.investment-managers.index') }}">
        <div class="flex items-center gap-3">
            <div class="relative flex-1 max-w-md">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full pl-10 pr-4 py-2.5 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                       placeholder="Cari nama manajer investasi...">
            </div>
            <button type="submit" class="px-4 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition">Cari</button>
            @if(request('search'))<a href="{{ route('admin.investment-managers.index') }}" class="px-4 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">Reset</a>@endif
        </div>
    </form>
</div>

@if($managers->isEmpty())
<div class="py-16 text-center text-muted bg-white rounded-2xl border border-line">
    <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
    <p class="font-medium">Belum ada data</p>
    <p class="text-sm mt-1">Klik "Tambah" atau import dari Excel</p>
</div>
@else
@foreach($managers as $m)
<div class="bg-white rounded-2xl border border-line overflow-hidden shadow-sm mb-4">
    <div class="px-5 py-3 border-b border-line flex items-center justify-between bg-gradient-to-r from-primary to-primary-light">
        <h3 class="font-bold text-white flex items-center gap-2 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            {{ $m->name }}
        </h3>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.investment-managers.edit', $m) }}"
               class="text-xs text-white/70 hover:text-white transition flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Manajer
            </a>
        </div>
    </div>
    @if($m->periods->isEmpty())
    <div class="py-8 text-center text-muted text-sm">Belum ada data periode. Import Excel untuk menambahkan periode.</div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                    <th class="px-4 py-3 font-semibold">Periode</th>
                    <th class="px-4 py-3 font-semibold text-right">AUM (Rp)</th>
                    <th class="px-4 py-3 font-semibold text-right">UP</th>
                    <th class="px-4 py-3 font-semibold text-right w-20">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @foreach($m->periods->sortBy('period_date') as $p)
                <tr class="hover:bg-[#f8fafc] transition-colors">
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-primary/5 text-primary font-semibold text-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ $p->period_date->format('d M Y') }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right text-xs font-semibold text-primary tabular-nums">
                        {{ $p->aum ? 'Rp' . number_format($p->aum, 0, ',', '.') : '-' }}
                    </td>
                    <td class="px-4 py-3 text-right text-xs tabular-nums">
                        {{ $p->up ? number_format($p->up, 2, ',', '.') : '-' }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('admin.investment-managers.period-destroy', $p) }}"
                              onsubmit="return confirm('Hapus periode {{ $p->period_date->format('d M Y') }} untuk {{ addslashes($m->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="p-1.5 rounded-lg text-muted hover:text-red-500 hover:bg-red-50 transition" title="Hapus periode">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endforeach

<div class="flex items-center justify-between gap-4 text-sm">
    <div class="flex items-center gap-2">
        <span class="text-muted text-xs">Tampilkan:</span>
        <form method="GET" action="{{ route('admin.investment-managers.index') }}">
            @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
            <select name="per_page" onchange="this.form.submit()"
                    class="text-xs border border-line rounded-lg px-2 py-1 focus:outline-none cursor-pointer">
                @foreach([10, 25, 50] as $n)
                <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                @endforeach
            </select>
        </form>
        <span class="text-muted text-xs">{{ $managers->total() }} manajer</span>
    </div>
    @if($managers->hasPages())
    <div class="flex items-center gap-1">
        @if($managers->onFirstPage())
        <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">← Prev</span>
        @else
        <a href="{{ $managers->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">← Prev</a>
        @endif
        @foreach($managers->getUrlRange(1, $managers->lastPage()) as $page => $url)
        <a href="{{ $url }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold transition {{ $page == $managers->currentPage() ? 'bg-primary text-white' : 'text-muted hover:text-primary hover:bg-[#f1f5f9]' }}">{{ $page }}</a>
        @endforeach
        @if($managers->hasMorePages())
        <a href="{{ $managers->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">Next →</a>
        @else
        <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">Next →</span>
        @endif
    </div>
    @endif
</div>
@endif

{{-- Modal Import --}}
<div x-show="showImport" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="absolute inset-0 bg-black/40" @click="showImport = false"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6"
         x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <h3 class="font-bold text-primary text-base mb-1">Import Manajer Investasi</h3>
        <p class="text-muted text-sm mb-4">Upload file Excel sesuai format template. Data akan ditambahkan atau diperbarui.</p>
        <form method="POST" action="{{ route('admin.investment-managers.import') }}" enctype="multipart/form-data">
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
                <a href="{{ route('admin.investment-managers.template') }}" class="text-xs text-accent hover:underline flex items-center gap-1">
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

</div>
@endsection