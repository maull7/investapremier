@extends('layouts.user')

@section('title', isset($stock) ? 'Edit Saham' : 'Tambah Saham')

@section('content')
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-muted mb-3">
            <a href="{{ route('user.saham.index') }}" class="hover:text-primary transition">Daftar Saham</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-primary font-medium">{{ isset($stock) ? 'Edit Saham' : 'Tambah Saham' }}</span>
        </div>
        <h1 class="text-2xl font-bold text-primary">{{ isset($stock) ? 'Edit Saham' : 'Tambah Saham Baru' }}</h1>
    </div>

    <form method="POST"
        action="{{ isset($stock) ? route('user.saham.update', $stock) : route('user.saham.store') }}"
        class="max-w-4xl">
        @csrf
        @if (isset($stock))
            @method('PUT')
        @endif

        <div class="bg-white rounded-2xl border border-line shadow-sm p-6 space-y-6">

            <div>
                <h3 class="font-bold text-primary text-sm mb-3">Informasi Dasar</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Kode Saham" class="text-sm font-semibold mb-1.5" />
                        <input type="text" name="kode"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent @error('kode') border-red-400 @enderror"
                            placeholder="AADI"
                            value="{{ old('kode', $stock->kode ?? '') }}" maxlength="10">
                        <x-input-error :messages="$errors->get('kode')" class="mt-1 text-xs" />
                    </div>
                    <div>
                        <x-input-label value="Nama Perusahaan" class="text-sm font-semibold mb-1.5" />
                        <input type="text" name="nama"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent @error('nama') border-red-400 @enderror"
                            placeholder="Adaro Andalan Indonesia Tbk."
                            value="{{ old('nama', $stock->nama ?? '') }}">
                        <x-input-error :messages="$errors->get('nama')" class="mt-1 text-xs" />
                    </div>
                    <div>
                        <x-input-label value="Sektor" class="text-sm font-semibold mb-1.5" />
                        <input type="text" name="sektor"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                            placeholder="Energi"
                            value="{{ old('sektor', $stock->sektor ?? '') }}">
                    </div>
                    <div>
                        <x-input-label value="Sub Industri" class="text-sm font-semibold mb-1.5" />
                        <input type="text" name="sub_industri"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                            placeholder="Produksi Batu Bara"
                            value="{{ old('sub_industri', $stock->sub_industri ?? '') }}">
                    </div>
                </div>
            </div>

            <div>
                <h3 class="font-bold text-primary text-sm mb-3">Data Harga</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-input-label value="Harga Terbaru" class="text-sm font-semibold mb-1.5" />
                        <input type="text" inputmode="decimal" name="harga_terbaru"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent @error('harga_terbaru') border-red-400 @enderror"
                            placeholder="8200"
                            value="{{ old('harga_terbaru', $stock->harga_terbaru ?? '') }}">
                        <x-input-error :messages="$errors->get('harga_terbaru')" class="mt-1 text-xs" />
                    </div>
                    <div>
                        <x-input-label value="Harga Penutupan Sebelumnya" class="text-sm font-semibold mb-1.5" />
                        <input type="text" inputmode="decimal" name="harga_penutupan_sebelumnya"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                            placeholder="8950"
                            value="{{ old('harga_penutupan_sebelumnya', $stock->harga_penutupan_sebelumnya ?? '') }}">
                    </div>
                    <div>
                        <x-input-label value="Harga Pembukaan" class="text-sm font-semibold mb-1.5" />
                        <input type="text" inputmode="decimal" name="harga_pembukaan"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                            placeholder="8950"
                            value="{{ old('harga_pembukaan', $stock->harga_pembukaan ?? '') }}">
                    </div>
                    <div>
                        <x-input-label value="Harga Tertinggi" class="text-sm font-semibold mb-1.5" />
                        <input type="text" inputmode="decimal" name="harga_tertinggi"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                            placeholder="9100"
                            value="{{ old('harga_tertinggi', $stock->harga_tertinggi ?? '') }}">
                    </div>
                    <div>
                        <x-input-label value="Harga Terendah" class="text-sm font-semibold mb-1.5" />
                        <input type="text" inputmode="decimal" name="harga_terendah"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                            placeholder="7825"
                            value="{{ old('harga_terendah', $stock->harga_terendah ?? '') }}">
                    </div>
                </div>
            </div>

            <div>
                <h3 class="font-bold text-primary text-sm mb-3">Data Transaksi</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-input-label value="Volume (Lembar)" class="text-sm font-semibold mb-1.5" />
                        <input type="text" inputmode="numeric" name="volume"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                            placeholder="67259900"
                            value="{{ old('volume', $stock->volume ?? '') }}">
                    </div>
                    <div>
                        <x-input-label value="Value (Rp)" class="text-sm font-semibold mb-1.5" />
                        <input type="text" inputmode="decimal" name="value"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                            placeholder="554546260000"
                            value="{{ old('value', $stock->value ?? '') }}">
                    </div>
                    <div>
                        <x-input-label value="Frekuensi" class="text-sm font-semibold mb-1.5" />
                        <input type="text" inputmode="numeric" name="frekuensi"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                            placeholder="21888"
                            value="{{ old('frekuensi', $stock->frekuensi ?? '') }}">
                    </div>
                    <div>
                        <x-input-label value="Jumlah Saham Beredar" class="text-sm font-semibold mb-1.5" />
                        <input type="text" inputmode="numeric" name="jumlah_saham"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                            placeholder="7786891760"
                            value="{{ old('jumlah_saham', $stock->jumlah_saham ?? '') }}">
                    </div>
                    <div>
                        <x-input-label value="Market Capital (Rp)" class="text-sm font-semibold mb-1.5" />
                        <input type="text" inputmode="decimal" name="market_capital"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                            placeholder="63852512432000"
                            value="{{ old('market_capital', $stock->market_capital ?? '') }}">
                    </div>
                    <div>
                        <x-input-label value="Last Update" class="text-sm font-semibold mb-1.5" />
                        <input type="date" name="last_update"
                            class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                            value="{{ old('last_update', isset($stock) && $stock->last_update ? $stock->last_update->format('Y-m-d') : '') }}">
                    </div>
                </div>
            </div>

        </div>

        <div class="flex items-center gap-3 mt-5">
            <button type="submit"
                class="px-5 py-2.5 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent/90 transition shadow-sm shadow-accent/20">
                {{ isset($stock) ? 'Simpan Perubahan' : 'Tambah Saham' }}
            </button>
            <a href="{{ route('user.saham.index') }}"
                class="px-5 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary hover:border-primary/30 transition">
                Batal
            </a>
        </div>
    </form>
@endsection
