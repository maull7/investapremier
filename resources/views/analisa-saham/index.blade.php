@extends('layouts.user')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-primary">Analisa Saham</h1>
            <p class="text-sm text-muted mt-0.5">Kelola dan lihat hasil analisa laporan keuangan saham Anda</p>
        </div>
        <a href="{{ route('user.analisa-saham.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary/90 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Submit Analisa Baru
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($items->isEmpty())
        <div class="bg-white rounded-xl border border-line p-12 text-center">
            <svg class="w-12 h-12 text-muted mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <p class="text-muted text-sm">Belum ada data analisa. Klik tombol di atas untuk memulai.</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-line overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-[#f8fafc] border-b border-line">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-primary">Nama Perusahaan</th>
                        <th class="text-left px-5 py-3 font-semibold text-primary">Kode</th>
                        <th class="text-left px-5 py-3 font-semibold text-primary">Sektor</th>
                        <th class="text-left px-5 py-3 font-semibold text-primary">Periode</th>
                        <th class="text-left px-5 py-3 font-semibold text-primary">Status</th>
                        <th class="text-left px-5 py-3 font-semibold text-primary">Tanggal</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach($items as $analisa)
                    <tr class="hover:bg-[#f8fafc] transition">
                        <td class="px-5 py-3.5 font-medium text-primary">{{ $analisa->nama_perusahaan }}</td>
                        <td class="px-5 py-3.5 text-muted">{{ $analisa->kode_saham ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-muted">{{ $analisa->sektor ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-muted">{{ $analisa->periode ?? '—' }}</td>
                        <td class="px-5 py-3.5">
                            @php
                                $badge = match($analisa->status) {
                                    'draft'     => 'bg-gray-100 text-gray-600',
                                    'submitted' => 'bg-yellow-100 text-yellow-700',
                                    'reviewed'  => 'bg-green-100 text-green-700',
                                    default     => 'bg-gray-100 text-gray-600',
                                };
                                $label = match($analisa->status) {
                                    'draft'     => 'Draft',
                                    'submitted' => 'Menunggu Review',
                                    'reviewed'  => 'Sudah Direview',
                                    default     => $analisa->status,
                                };
                            @endphp
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">{{ $label }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-muted">{{ $analisa->created_at->format('d M Y') }}</td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('user.analisa-saham.show', $analisa) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-primary border border-line rounded-lg hover:bg-[#f1f5f9] transition">
                                    Lihat Hasil
                                </a>
                                @if($analisa->status !== 'reviewed')
                                <form method="POST" action="{{ route('user.analisa-saham.destroy', $analisa) }}"
                                      onsubmit="return confirm('Hapus data analisa ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 text-xs font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition">
                                        Hapus
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
