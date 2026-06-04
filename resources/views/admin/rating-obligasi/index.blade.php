@extends('layouts.admin')

@section('content')
    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="page-title">Master Rating Obligasi</h1>
                <p class="page-sub">Kelola daftar rating obligasi untuk analisa</p>
            </div>
            <div class="flex items-center gap-3">
                <form method="POST" action="{{ route('admin.rating-obligasi.import') }}" enctype="multipart/form-data"
                    onsubmit="return confirm('Import data rating dari file? Data dengan kode yang sama akan diupdate.')">
                    @csrf
                    <div class="flex items-center gap-2">
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                            class="block text-sm border border-line rounded-lg file:mr-2 file:py-1.5 file:px-3 file:border-0 file:text-sm file:font-medium file:bg-[#f8fafc] hover:file:bg-gray-100">
                        <button type="submit"
                            class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition">Import</button>
                        <a href="{{ route('admin.rating-obligasi.template') }}"
                            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">Template</a>
                    </div>
                </form>
                <a href="{{ route('admin.rating-obligasi.create') }}"
                    class="btn-primary btn-sm">
                    Tambah
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="table-card">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#f8fafc] border-b border-line">
                        <th class="text-left px-4 py-3 font-semibold text-primary">Kode</th>
                        <th class="text-left px-4 py-3 font-semibold text-primary">Nama</th>
                        <th class="text-left px-4 py-3 font-semibold text-primary">Keterangan</th>
                        <th class="text-center px-4 py-3 font-semibold text-primary">Urutan</th>
                        <th class="text-center px-4 py-3 font-semibold text-primary">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ratings as $rating)
                        <tr class="border-b border-line/50 hover:bg-[#f8fafc]">
                            <td class="px-4 py-3 font-medium">{{ $rating->kode }}</td>
                            <td class="px-4 py-3">{{ $rating->nama }}</td>
                            <td class="px-4 py-3 text-muted max-w-xs truncate">{{ $rating->keterangan ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">{{ $rating->urutan }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.rating-obligasi.edit', $rating) }}"
                                        class="text-xs px-3 py-1.5 border border-line rounded-lg hover:bg-[#f8fafc] transition">Edit</a>
                                    <form method="POST" action="{{ route('admin.rating-obligasi.destroy', $rating) }}"
                                        onsubmit="return confirm('Hapus rating {{ $rating->kode }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="text-xs px-3 py-1.5 border border-red-200 text-red-600 rounded-lg hover:bg-red-50 transition">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-muted">Belum ada data rating obligasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
