@extends('layouts.admin')

@section('title', isset($unitLink) ? 'Edit Unit Link' : 'Tambah Unit Link')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-muted mb-3">
        <a href="{{ route('admin.unit-link.index') }}" class="hover:text-primary transition">Unit Link</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-primary font-medium">{{ isset($unitLink) ? 'Edit' : 'Tambah' }}</span>
    </div>
    <h1 class="text-2xl font-bold text-primary">{{ isset($unitLink) ? 'Edit Unit Link' : 'Tambah Unit Link' }}</h1>
</div>

<form method="POST"
    action="{{ isset($unitLink) ? route('admin.unit-link.update', $unitLink) : route('admin.unit-link.store') }}"
    class="max-w-3xl">
    @csrf
    @if(isset($unitLink)) @method('PUT') @endif

    <div class="bg-white rounded-2xl border border-line shadow-sm p-6 space-y-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label value="Unit Link" class="text-sm font-semibold mb-1.5" />
                <input type="text" name="unit_link"
                    class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent @error('unit_link') border-red-400 @enderror"
                    value="{{ old('unit_link', $unitLink->unit_link ?? '') }}">
                <x-input-error :messages="$errors->get('unit_link')" class="mt-1 text-xs" />
            </div>
            <div>
                <x-input-label value="Asuransi" class="text-sm font-semibold mb-1.5" />
                <input type="text" name="asuransi"
                    class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                    value="{{ old('asuransi', $unitLink->asuransi ?? '') }}">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <x-input-label value="Jenis" class="text-sm font-semibold mb-1.5" />
                <input type="text" name="jenis"
                    class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                    value="{{ old('jenis', $unitLink->jenis ?? '') }}">
            </div>
            <div>
                <x-input-label value="Tipe" class="text-sm font-semibold mb-1.5" />
                <input type="text" name="tipe"
                    class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                    value="{{ old('tipe', $unitLink->tipe ?? '') }}">
            </div>
            <div>
                <x-input-label value="Mata Uang" class="text-sm font-semibold mb-1.5" />
                <input type="text" name="mata_uang"
                    class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                    value="{{ old('mata_uang', $unitLink->mata_uang ?? '') }}">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <x-input-label value="Median Price" class="text-sm font-semibold mb-1.5" />
                <input type="text" inputmode="decimal" name="median_price"
                    class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                    value="{{ old('median_price', $unitLink->median_price ?? '') }}">
            </div>
            <div>
                <x-input-label value="Buy Price" class="text-sm font-semibold mb-1.5" />
                <input type="text" inputmode="decimal" name="buy_price"
                    class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                    value="{{ old('buy_price', $unitLink->buy_price ?? '') }}">
            </div>
            <div>
                <x-input-label value="Sell Price" class="text-sm font-semibold mb-1.5" />
                <input type="text" inputmode="decimal" name="sell_price"
                    class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                    value="{{ old('sell_price', $unitLink->sell_price ?? '') }}">
            </div>
        </div>

        <div class="max-w-xs">
            <x-input-label value="Last Update" class="text-sm font-semibold mb-1.5" />
            <input type="date" name="last_update"
                class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                value="{{ old('last_update', isset($unitLink) && $unitLink->last_update ? $unitLink->last_update->format('Y-m-d') : '') }}">
        </div>
    </div>

    <div class="flex items-center gap-3 mt-5">
        <button type="submit" class="px-5 py-2.5 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent/90 transition shadow-sm shadow-accent/20">
            {{ isset($unitLink) ? 'Simpan Perubahan' : 'Tambah' }}
        </button>
        <a href="{{ route('admin.unit-link.index') }}"
            class="px-5 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary hover:border-primary/30 transition">Batal</a>
    </div>
</form>
@endsection
