@extends('layouts.admin')

@section('content')
<div class="p-6 max-w-2xl">
    <div class="mb-6">
        <h1 class="page-title">{{ isset($ratingObligasi) ? 'Edit Rating Obligasi' : 'Tambah Rating Obligasi' }}</h1>
        <p class="page-sub">{{ isset($ratingObligasi) ? 'Ubah data rating obligasi' : 'Buat rating obligasi baru' }}</p>
    </div>

    <form method="POST"
        action="{{ isset($ratingObligasi) ? route('admin.rating-obligasi.update', $ratingObligasi) : route('admin.rating-obligasi.store') }}"
        class="bg-white rounded-xl border border-line p-6 space-y-4">
        @csrf
        @if (isset($ratingObligasi)) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Rating <span class="text-red-500">*</span></label>
            <input type="text" name="kode"
                value="{{ old('kode', $ratingObligasi->kode ?? '') }}"
                required maxlength="20"
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
            <p class="text-xs text-muted mt-1">Contoh: AAA, AA+, AA, AA-, A+, dll.</p>
            @error('kode') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Rating <span class="text-red-500">*</span></label>
            <input type="text" name="nama"
                value="{{ old('nama', $ratingObligasi->nama ?? '') }}"
                required maxlength="100"
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
            <p class="text-xs text-muted mt-1">Contoh: AAA (Triple A), AA+, dll.</p>
            @error('nama') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
            <textarea name="keterangan" rows="3"
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">{{ old('keterangan', $ratingObligasi->keterangan ?? '') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
            <input type="number" name="urutan"
                value="{{ old('urutan', $ratingObligasi->urutan ?? 0) }}"
                min="0"
                class="block w-32 border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
            <p class="text-xs text-muted mt-1">Semakin kecil angka, semakin tinggi prioritas.</p>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="px-6 py-2 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary/90 transition">
                {{ isset($ratingObligasi) ? 'Simpan' : 'Tambah' }}
            </button>
            <a href="{{ route('admin.rating-obligasi.index') }}"
                class="px-4 py-2 text-sm text-muted border border-line rounded-lg hover:bg-[#f1f5f9] transition">Batal</a>
        </div>
    </form>
</div>
@endsection
