@extends('layouts.admin')

@section('title', 'Daftar Obligasi - InvestaPremier')

@section('content')
<div x-data="{
    deleteId: null, deleteType: '', deleteText: '',
    showImportHarga: false, showImportBond: false, showExtraction: false
}">

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="page-title">Daftar Obligasi</h1>
        <p class="page-sub">Kelola data obligasi harga referensi dan keuangan emiten</p>
    </div>
    <button @click="showExtraction = true" class="btn-outline">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18M6 8h12m-9 4h6m-8 4h10m-7 4h4" />
        </svg>
        Ekstrak Data
    </button>
</div>

@if(session('success'))
<div class="alert-success">
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-5 flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('error') }}
</div>
@endif

{{-- Tabs --}}
<div class="mb-5">
    <div class="flex items-center gap-1 bg-[#f1f5f9] rounded-xl p-1 w-fit">
        <a href="{{ route('admin.obligasi.index', ['tab' => 'harga-referensi']) }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $tab === 'harga-referensi' ? 'bg-white text-primary shadow-sm' : 'text-muted hover:text-primary' }}">
            Obligasi Harga Referensi
        </a>
        <a href="{{ route('admin.obligasi.index', ['tab' => 'bond']) }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $tab === 'bond' ? 'bg-white text-primary shadow-sm' : 'text-muted hover:text-primary' }}">
            Keuangan Emiten
        </a>
        <a href="{{ route('admin.obligasi.index', ['tab' => 'hasil-ekstrak']) }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $tab === 'hasil-ekstrak' ? 'bg-white text-primary shadow-sm' : 'text-muted hover:text-primary' }}">
            Hasil Ekstrak
        </a>
    </div>
</div>

@if($tab === 'harga-referensi')
    @include('admin.obligasi._tab-harga-referensi')
@elseif($tab === 'bond')
    @include('admin.obligasi._tab-bond')
@else
    @include('admin.obligasi._tab-hasil-ekstrak')
@endif

{{-- Modal Ekstrak Data Obligasi --}}
<div x-show="showExtraction" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="absolute inset-0 bg-black/40" @click="showExtraction = false"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6"
         x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <h3 class="font-bold text-primary text-base mb-1">Ekstrak Data Obligasi</h3>
        <p class="text-muted text-sm mb-4">Semua data obligasi akan diproses lewat Horizon. PHEI dicoba lebih dulu, lalu IDX jika endpoint tersedia.</p>

        <form method="POST" action="{{ route('admin.obligasi.extraction-batches.store') }}">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-primary mb-1.5">Tanggal data</label>
                <input type="date" name="data_date" value="{{ now()->toDateString() }}"
                       class="w-full border border-line rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30"
                       required>
            </div>
            <div class="mt-4">
                <label class="block text-xs font-semibold text-primary mb-1.5">Rentang data</label>
                <select name="range"
                        class="w-full border border-line rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30"
                        required>
                    @foreach ($extractionRanges as $range)
                        <option value="{{ $range['value'] }}">{{ $range['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mt-4 rounded-xl border border-line bg-[#f8fafc] px-4 py-3 text-xs text-muted">
                Data masuk staging dulu. Database utama baru berubah setelah tombol Simpan ke Database diklik dari tab Hasil Ekstrak.
            </div>
            <div class="flex items-center justify-end gap-3 mt-6">
                <button type="button" @click="showExtraction = false"
                        class="px-4 py-2 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent/90 transition">
                    Proses
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Konfirmasi Hapus --}}
<div x-show="deleteId !== null" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="absolute inset-0 bg-black/40" @click="deleteId = null"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6"
         x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-full bg-red-100 grid place-items-center shrink-0">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <div class="flex-1">
                <h3 class="font-bold text-primary text-base">Hapus Data?</h3>
                <p class="page-sub">Data berikut akan dihapus permanen:</p>
                <p class="mt-2 text-sm text-primary font-medium bg-[#f8fafc] rounded-lg px-3 py-2 border border-line" x-text="deleteText"></p>
                <p class="text-xs text-red-500 mt-2">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 mt-6">
            <button type="button" @click="deleteId = null"
                    class="px-4 py-2 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary hover:border-primary/30 transition">
                Batal
            </button>
            <form method="POST" :action="deleteId ? `/admin/obligasi/${deleteType}/${deleteId}` : ''">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700 transition">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Modal Import Harga Referensi --}}
@include('admin.obligasi._modal-import-harga')

{{-- Modal Import Bond --}}
@include('admin.obligasi._modal-import-bond')

</div>
@endsection
