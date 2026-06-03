@extends('layouts.admin')

@section('content')
<div class="p-6 max-w-2xl">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-primary">Edit YTM Normal Curve</h1>
        <p class="text-sm text-muted mt-0.5">Ubah data YTM normal untuk rating dan tenor tertentu</p>
    </div>

    <form method="POST" action="{{ route('admin.ytm-normal-curve.update', $ytmNormalCurve) }}"
        class="bg-white rounded-xl border border-line p-6 space-y-4">
        @csrf @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
            <select name="rating_id" required
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                <option value="">Pilih Rating</option>
                @foreach ($ratings as $r)
                    <option value="{{ $r->id }}" {{ old('rating_id', $ytmNormalCurve->rating_id) == $r->id ? 'selected' : '' }}>
                        {{ $r->kode }} - {{ $r->nama }}
                    </option>
                @endforeach
            </select>
            @error('rating_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tenor (Bulan)</label>
            <input type="number" name="tenor_bulan"
                value="{{ old('tenor_bulan', $ytmNormalCurve->tenor_bulan) }}" required min="1"
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
            @error('tenor_bulan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">YTM Normal (%)</label>
            <input type="number" name="ytm_normal" step="0.0001"
                value="{{ old('ytm_normal', $ytmNormalCurve->ytm_normal) }}" required min="0" max="100"
                class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
            @error('ytm_normal') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="px-6 py-2 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary/90 transition">
                Simpan
            </button>
            <a href="{{ route('admin.ytm-normal-curve.index') }}"
                class="px-4 py-2 text-sm text-muted border border-line rounded-lg hover:bg-[#f1f5f9] transition">Batal</a>
        </div>
    </form>
</div>
@endsection
