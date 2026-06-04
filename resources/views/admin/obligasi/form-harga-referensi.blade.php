@extends('layouts.admin')

@section('title', isset($obligasi) ? 'Edit Obligasi Harga Referensi' : 'Tambah Obligasi Harga Referensi')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-muted mb-3">
        <a href="{{ route('admin.obligasi.index', ['tab' => 'harga-referensi']) }}" class="hover:text-primary transition">Daftar Obligasi</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-primary font-medium">{{ isset($obligasi) ? 'Edit' : 'Tambah' }} Harga Referensi</span>
    </div>
    <h1 class="page-title">{{ isset($obligasi) ? 'Edit Obligasi Harga Referensi' : 'Tambah Obligasi Harga Referensi' }}</h1>
</div>

<form method="POST"
    action="{{ isset($obligasi) ? route('admin.obligasi.update-harga-referensi', $obligasi) : route('admin.obligasi.store-harga-referensi') }}"
    class="max-w-4xl">
    @csrf
    @if(isset($obligasi)) @method('PUT') @endif

    <div class="bg-white rounded-2xl border border-line shadow-sm p-6 space-y-6">
        <div>
            <h3 class="font-bold text-primary text-sm mb-3">Informasi Dasar</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-input-label value="Kode Obligasi" class="text-sm font-semibold mb-1.5" />
                    <input type="text" name="kode" maxlength="20"
                        class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent @error('kode') border-red-400 @enderror"
                        placeholder="ABLS01XXMF" value="{{ old('kode', $obligasi->kode ?? '') }}">
                    <x-input-error :messages="$errors->get('kode')" class="mt-1 text-xs" />
                </div>
                <div>
                    <x-input-label value="Nama Obligasi" class="text-sm font-semibold mb-1.5" />
                    <input type="text" name="nama" class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                        placeholder="MTN Asian Bulk Logistics I Tahun 2022" value="{{ old('nama', $obligasi->nama ?? '') }}">
                </div>
                <div>
                    <x-input-label value="Tanggal Terbit" class="text-sm font-semibold mb-1.5" />
                    <input type="date" name="tanggal_terbit" class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                        value="{{ old('tanggal_terbit', isset($obligasi) && $obligasi->tanggal_terbit ? $obligasi->tanggal_terbit->format('Y-m-d') : '') }}">
                </div>
                <div>
                    <x-input-label value="Emiten" class="text-sm font-semibold mb-1.5" />
                    <input type="text" name="emiten" class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                        placeholder="ABLS" value="{{ old('emiten', $obligasi->emiten ?? '') }}">
                </div>
                <div>
                    <x-input-label value="Sektor" class="text-sm font-semibold mb-1.5" />
                    <input type="text" name="sektor" class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                        value="{{ old('sektor', $obligasi->sektor ?? '') }}">
                </div>
                <div>
                    <x-input-label value="Sub Sektor" class="text-sm font-semibold mb-1.5" />
                    <input type="text" name="sub_sektor" class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                        value="{{ old('sub_sektor', $obligasi->sub_sektor ?? '') }}">
                </div>
                <div>
                    <x-input-label value="Industri" class="text-sm font-semibold mb-1.5" />
                    <input type="text" name="industri" class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                        value="{{ old('industri', $obligasi->industri ?? '') }}">
                </div>
                <div>
                    <x-input-label value="Sub Industri" class="text-sm font-semibold mb-1.5" />
                    <input type="text" name="sub_industri" class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                        value="{{ old('sub_industri', $obligasi->sub_industri ?? '') }}">
                </div>
            </div>
        </div>

        <div>
            <h3 class="font-bold text-primary text-sm mb-3">Detail Obligasi</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <x-input-label value="Denominasi" class="text-sm font-semibold mb-1.5" />
                    <input type="text" name="denominasi" class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                        placeholder="IDR" value="{{ old('denominasi', $obligasi->denominasi ?? '') }}">
                </div>
                <div>
                    <x-input-label value="Rating" class="text-sm font-semibold mb-1.5" />
                    <input type="text" name="rating" class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                        value="{{ old('rating', $obligasi->rating ?? '') }}">
                </div>
                <div>
                    <x-input-label value="Syariah" class="text-sm font-semibold mb-1.5" />
                    <select name="syariah" class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                        <option value="0" {{ old('syariah', isset($obligasi) ? ($obligasi->syariah ? '1' : '0') : '0') == '0' ? 'selected' : '' }}>Tidak</option>
                        <option value="1" {{ old('syariah', isset($obligasi) ? ($obligasi->syariah ? '1' : '0') : '0') == '1' ? 'selected' : '' }}>Ya</option>
                    </select>
                </div>
                <div>
                    <x-input-label value="Kupon (%)" class="text-sm font-semibold mb-1.5" />
                    <input type="text" inputmode="decimal" name="kupon" class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                        placeholder="0.09" value="{{ old('kupon', $obligasi->kupon ?? '') }}">
                </div>
                <div>
                    <x-input-label value="Jatuh Tempo" class="text-sm font-semibold mb-1.5" />
                    <input type="date" name="jatuh_tempo" class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                        value="{{ old('jatuh_tempo', isset($obligasi) && $obligasi->jatuh_tempo ? $obligasi->jatuh_tempo->format('Y-m-d') : '') }}">
                </div>
                <div>
                    <x-input-label value="Harga (%)" class="text-sm font-semibold mb-1.5" />
                    <input type="text" inputmode="decimal" name="harga_persen" class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                        placeholder="100" value="{{ old('harga_persen', $obligasi->harga_persen ?? '') }}">
                </div>
                <div>
                    <x-input-label value="TTM" class="text-sm font-semibold mb-1.5" />
                    <input type="text" inputmode="decimal" name="ttm" class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                        placeholder="1.08966" value="{{ old('ttm', $obligasi->ttm ?? '') }}">
                </div>
                <div>
                    <x-input-label value="YTM (%)" class="text-sm font-semibold mb-1.5" />
                    <input type="text" inputmode="decimal" name="ytm" class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                        placeholder="0.09" value="{{ old('ytm', $obligasi->ytm ?? '') }}">
                </div>
                <div>
                    <x-input-label value="Current Yield (%)" class="text-sm font-semibold mb-1.5" />
                    <input type="text" inputmode="decimal" name="current_yield" class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                        placeholder="0.09" value="{{ old('current_yield', $obligasi->current_yield ?? '') }}">
                </div>
                <div>
                    <x-input-label value="Total Val" class="text-sm font-semibold mb-1.5" />
                    <input type="text" inputmode="decimal" name="total_val" class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                        placeholder="100000000" value="{{ old('total_val', $obligasi->total_val ?? '') }}">
                </div>
                <div>
                    <x-input-label value="Outstanding Amount" class="text-sm font-semibold mb-1.5" />
                    <input type="text" inputmode="decimal" name="outstanding_amount" class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                        placeholder="1000000000000" value="{{ old('outstanding_amount', $obligasi->outstanding_amount ?? '') }}">
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3 mt-5">
        <button type="submit" class="px-5 py-2.5 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent/90 transition shadow-sm shadow-accent/20">
            {{ isset($obligasi) ? 'Simpan Perubahan' : 'Tambah' }}
        </button>
        <a href="{{ route('admin.obligasi.index', ['tab' => 'harga-referensi']) }}"
            class="px-5 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary hover:border-primary/30 transition">Batal</a>
    </div>
</form>
@endsection
