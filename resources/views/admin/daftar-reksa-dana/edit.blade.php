@extends('layouts.admin')

@section('title', 'Edit ' . $reksaDana->nama_reksa_dana . ' - Informasi Reksa Dana')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-muted mb-3">
        <a href="{{ route('admin.daftar-reksa-dana.index') }}" class="hover:text-primary transition">Daftar Reksa Dana</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('admin.daftar-reksa-dana.show', $reksaDana) }}" class="hover:text-primary transition">{{ $reksaDana->nama_reksa_dana }}</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-primary font-medium">Edit Informasi</span>
    </div>
    <h1 class="page-title">Edit Informasi Reksa Dana</h1>
</div>

<form method="POST" action="{{ route('admin.daftar-reksa-dana.update-info', $reksaDana) }}" class="max-w-3xl">
    @csrf
    <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light">
            <h2 class="font-bold text-white text-sm">Informasi Reksa Dana</h2>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Nama Reksa Dana <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_reksa_dana" value="{{ old('nama_reksa_dana', $reksaDana->nama_reksa_dana) }}" required maxlength="255"
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                    @error('nama_reksa_dana')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Kode Reksa Dana</label>
                    <input type="text" name="kode_reksa_dana" value="{{ old('kode_reksa_dana', $reksaDana->kode_reksa_dana) }}" maxlength="20"
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                    @error('kode_reksa_dana')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Manajer Investasi</label>
                    <input type="text" name="nama_manajer_investasi" value="{{ old('nama_manajer_investasi', $reksaDana->nama_manajer_investasi) }}" maxlength="255"
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Bank Kustodian</label>
                    <input type="text" name="custodian_bank" value="{{ old('custodian_bank', $reksaDana->custodian_bank) }}" maxlength="255"
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Tanggal Efektif</label>
                    <input type="date" name="launch_date" value="{{ old('launch_date', $reksaDana->launch_date?->format('Y-m-d')) }}"
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Benchmark Tolak Ukur</label>
                    <input type="text" name="benchmark" value="{{ old('benchmark', $reksaDana->benchmark) }}" maxlength="255"
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20" placeholder="Contoh: IHSG, IDX80">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-primary mb-1">Tujuan Investasi</label>
                <textarea name="tujuan_investasi" rows="3" maxlength="5000"
                    class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">{{ old('tujuan_investasi', $reksaDana->tujuan_investasi) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-primary mb-1">Kebijakan Investasi</label>
                <textarea name="kebijakan_investasi" rows="3" maxlength="5000"
                    class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">{{ old('kebijakan_investasi', $reksaDana->kebijakan_investasi) }}</textarea>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Jenis Reksa Dana</label>
                    <select name="jenis"
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                        <option value="">—</option>
                        @foreach (['Saham', 'Pendapatan Tetap', 'Campuran', 'Pasar Uang', 'Terproteksi', 'Global', 'DIRE-DINFRA', 'Penyertaan terbatas'] as $opt)
                            <option value="{{ $opt }}" {{ old('jenis', $reksaDana->jenis) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Kategori Produk</label>
                    <select name="kategori_produk"
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                        <option value="">—</option>
                        @foreach (['Konvensional', 'Syariah', 'Index', 'ETF'] as $opt)
                            <option value="{{ $opt }}" {{ old('kategori_produk', $reksaDana->kategori_produk) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Kelas</label>
                    <input type="text" name="kelas" value="{{ old('kelas', $reksaDana->kelas) }}" maxlength="10"
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-primary mb-1">Kategori</label>
                <div class="flex flex-wrap gap-3">
                    @php $kategori = old('kategori', $reksaDana->kategori ?? []); @endphp
                    @foreach (['Konvensional', 'Syariah', 'index', 'ETF'] as $opt)
                        <label class="flex items-center gap-1.5 text-sm">
                            <input type="checkbox" name="kategori[]" value="{{ $opt }}"
                                {{ in_array($opt, $kategori) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-accent focus:ring-accent/30">
                            {{ $opt }}
                        </label>
                    @endforeach
                </div>
                @error('kategori')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Mata Uang</label>
                    <select name="mata_uang"
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                        @foreach (['IDR' => 'IDR - Rupiah', 'USD' => 'USD - Dollar AS', 'SGD' => 'SGD - Dollar Singapura'] as $val => $label)
                            <option value="{{ $val }}" {{ old('mata_uang', $reksaDana->mata_uang ?: 'IDR') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">ISIN Code</label>
                    <input type="text" name="isin_code" value="{{ old('isin_code', $reksaDana->isin_code) }}" maxlength="20"
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">ETF</label>
                    <select name="is_etf"
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                        <option value="">—</option>
                        <option value="1" {{ old('is_etf', $reksaDana->is_etf) === true || old('is_etf', $reksaDana->is_etf) === '1' ? 'selected' : '' }}>Ya</option>
                        <option value="0" {{ old('is_etf', $reksaDana->is_etf) === false || old('is_etf', $reksaDana->is_etf) === '0' ? 'selected' : '' }}>Tidak</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Index Fund</label>
                    <select name="is_index"
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                        <option value="">—</option>
                        <option value="1" {{ old('is_index', $reksaDana->is_index) === true || old('is_index', $reksaDana->is_index) === '1' ? 'selected' : '' }}>Ya</option>
                        <option value="0" {{ old('is_index', $reksaDana->is_index) === false || old('is_index', $reksaDana->is_index) === '0' ? 'selected' : '' }}>Tidak</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Dividen</label>
                    <select name="dividend"
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
                        <option value="">—</option>
                        <option value="1" {{ old('dividend', $reksaDana->dividend) === true || old('dividend', $reksaDana->dividend) === '1' ? 'selected' : '' }}>Ya</option>
                        <option value="0" {{ old('dividend', $reksaDana->dividend) === false || old('dividend', $reksaDana->dividend) === '0' ? 'selected' : '' }}>Tidak</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-primary mb-1">Kategori Konservatif</label>
                <input type="text" name="conservative_category" value="{{ old('conservative_category', $reksaDana->conservative_category) }}" maxlength="100"
                    class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-primary focus:ring focus:ring-primary/20">
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3 mt-6">
        <button type="submit"
            class="px-6 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition">Simpan</button>
        <a href="{{ route('admin.daftar-reksa-dana.show', $reksaDana) }}"
            class="px-6 py-2.5 text-sm text-muted border border-line rounded-xl hover:bg-[#f1f5f9] transition">Batal</a>
    </div>
</form>
@endsection