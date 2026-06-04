@extends('layouts.admin')

@section('title', 'Daftar Obligasi - InvestaPremier')

@section('content')
<div x-data="{
    deleteId: null, deleteType: '', deleteText: '',
    showImportHarga: false, showImportBond: false
}">

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="page-title">Daftar Obligasi</h1>
        <p class="page-sub">Kelola data obligasi harga referensi dan keuangan emiten</p>
    </div>
</div>

@if(session('success'))
<div class="alert-success">
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('success') }}
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
    </div>
</div>

@if($tab === 'harga-referensi')
    @include('admin.obligasi._tab-harga-referensi')
@else
    @include('admin.obligasi._tab-bond')
@endif

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
