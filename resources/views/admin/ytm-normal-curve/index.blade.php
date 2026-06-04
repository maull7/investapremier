@extends('layouts.admin')

@section('content')
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="page-title">YTM Normal Curve</h1>
            <p class="page-sub">Kelola kurva YTM normal berdasarkan rating dan tenor</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="POST" action="{{ route('admin.ytm-normal-curve.import') }}" enctype="multipart/form-data"
                onsubmit="return confirm('Import data YTM Normal Curve dari file? Data dengan kombinasi rating+tenor yang sama akan diupdate.')">
                @csrf
                <div class="flex items-center gap-2">
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                        class="block text-sm border border-line rounded-lg file:mr-2 file:py-1.5 file:px-3 file:border-0 file:text-sm file:font-medium file:bg-[#f8fafc] hover:file:bg-gray-100">
                    <button type="submit"
                         class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition">Import</button>
                    <a href="{{ route('admin.ytm-normal-curve.template') }}"
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">Template</a>
                </div>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Form Tambah --}}
    <div class="bg-white rounded-xl border border-line p-6">
        <h3 class="font-semibold text-primary mb-4">Tambah Data YTM Normal</h3>
        <form method="POST" action="{{ route('admin.ytm-normal-curve.store') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                <select name="rating_id" required
                    class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                    <option value="">Pilih Rating</option>
                    @foreach ($ratings as $r)
                        <option value="{{ $r->id }}" {{ old('rating_id') == $r->id ? 'selected' : '' }}>
                            {{ $r->kode }} - {{ $r->nama }}
                        </option>
                    @endforeach
                </select>
                @error('rating_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tenor (Bulan)</label>
                <input type="number" name="tenor_bulan"
                    value="{{ old('tenor_bulan') }}" required min="1"
                    class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                @error('tenor_bulan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">YTM Normal (%)</label>
                <input type="number" name="ytm_normal" step="0.0001"
                    value="{{ old('ytm_normal') }}" required min="0" max="100"
                    class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                @error('ytm_normal') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-end">
                <button type="submit"
                    class="w-full px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary/90 transition">
                    Tambah
                </button>
            </div>
        </form>
    </div>

    {{-- Daftar YTM Normal Curve --}}
    @forelse ($grouped as $label => $curves)
        <div class="table-card">
            <div class="px-4 py-3 bg-[#f8fafc] border-b border-line font-semibold text-primary text-sm">
                {{ $label }}
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-line">
                        <th class="text-left px-4 py-2.5 font-semibold text-primary">Tenor (Bulan)</th>
                        <th class="text-left px-4 py-2.5 font-semibold text-primary">YTM Normal (%)</th>
                        <th class="text-center px-4 py-2.5 font-semibold text-primary">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($curves as $curve)
                        <tr class="border-b border-line/50 hover:bg-[#f8fafc]">
                            <td class="px-4 py-2.5">{{ $curve->tenor_bulan }} bln ({{ round($curve->tenor_bulan / 12, 1) }} thn)</td>
                            <td class="px-4 py-2.5 font-medium">{{ number_format($curve->ytm_normal, 4) }}%</td>
                            <td class="px-4 py-2.5">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.ytm-normal-curve.edit', $curve) }}"
                                        class="text-xs px-3 py-1.5 border border-line rounded-lg hover:bg-[#f8fafc] transition">Edit</a>
                                    <form method="POST" action="{{ route('admin.ytm-normal-curve.destroy', $curve) }}"
                                        onsubmit="return confirm('Hapus data ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="text-xs px-3 py-1.5 border border-red-200 text-red-600 rounded-lg hover:bg-red-50 transition">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div class="bg-white rounded-xl border border-line p-8 text-center text-muted text-sm">
            Belum ada data YTM Normal Curve.
        </div>
    @endforelse
</div>
@endsection
