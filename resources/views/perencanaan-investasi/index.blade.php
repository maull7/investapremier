@extends('layouts.user')

@section('title', 'Perencanaan Investasi - InvestaPremier')

@section('content')
    <div x-data="{ deleteId: null, deleteText: '' }">

        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="page-title">Perencanaan Investasi</h1>
                <p class="page-sub">Rencanakan dan proyeksikan tujuan investasi Anda</p>
            </div>
            <a href="{{ route('user.perencanaan-investasi.create') }}"
               class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Rencana Baru
            </a>
        </div>

        @if (session('success'))
            <div class="alert-success">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="table-card">
            <div class="table-head">
                <h2 class="th-title">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Rencana Investasi
                </h2>
            </div>

            @if ($plans->isEmpty())
                <div class="py-16 text-center text-muted">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <p class="font-medium">Belum ada rencana investasi</p>
                    <p class="text-sm mt-1">Klik "Buat Rencana Baru" untuk memulai perencanaan investasi Anda</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                                <th class="px-4 py-3.5 font-semibold">Kategori</th>
                                <th class="px-4 py-3.5 font-semibold">Kebutuhan Dana</th>
                                <th class="px-4 py-3.5 font-semibold">Target</th>
                                <th class="px-4 py-3.5 font-semibold">Investasi/Bulan</th>
                                <th class="px-4 py-3.5 font-semibold">Profil Risiko</th>
                                <th class="px-4 py-3.5 font-semibold">Status</th>
                                <th class="px-4 py-3.5 font-semibold text-right">Tanggal</th>
                                <th class="px-4 py-3.5 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @foreach ($plans as $plan)
                                <tr class="hover:bg-[#f8fafc] transition-colors">
                                    <td class="px-4 py-3">
                                        <span class="font-semibold text-primary">{{ $plan->kategori_perencanaan }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-xs font-medium">Rp{{ number_format($plan->kebutuhan_dana ?? 0, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-xs text-muted">{{ $plan->target_waktu_tahun ? $plan->target_waktu_tahun . ' tahun' : '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-xs font-medium">Rp{{ number_format($plan->investasi_per_bulan ?? 0, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-xs text-muted">{{ $plan->profil_risiko ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $statusColors = ['Aktif' => 'bg-green-100 text-green-700', 'Selesai' => 'bg-blue-100 text-blue-700', 'Ditunda' => 'bg-yellow-100 text-yellow-700'];
                                            $color = $statusColors[$plan->status] ?? 'bg-gray-100 text-gray-600';
                                        @endphp
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $color }}">{{ $plan->status }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-xs text-muted">
                                        {{ $plan->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('user.perencanaan-investasi.show', $plan) }}"
                                               class="p-2 rounded-lg text-muted hover:text-accent hover:bg-[#f1f5f9] transition" title="Detail">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                            <a href="{{ route('user.perencanaan-investasi.edit', $plan) }}"
                                               class="p-2 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition" title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                            <button type="button"
                                                    @click="deleteId = {{ $plan->id }}; deleteText = '{{ addslashes($plan->kategori_perencanaan) }}'"
                                                    class="p-2 rounded-lg text-muted hover:text-red-500 hover:bg-red-50 transition" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($plans->hasPages())
                    <div class="px-6 py-4 border-t border-line flex items-center justify-between gap-4 text-sm">
                        <p class="text-muted text-xs">Menampilkan {{ $plans->firstItem() }}–{{ $plans->lastItem() }} dari {{ $plans->total() }} rencana</p>
                        <div class="flex items-center gap-1">
                            @if ($plans->onFirstPage())
                                <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">← Prev</span>
                            @else
                                <a href="{{ $plans->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">← Prev</a>
                            @endif
                            @php
                                $current = $plans->currentPage();
                                $last = $plans->lastPage();
                                $start = max(1, $current - 2);
                                $end = min($last, $current + 2);
                            @endphp
                            @if ($start > 1)
                                <a href="{{ $plans->url(1) }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">1</a>
                                @if ($start > 2) <span class="px-1 text-muted text-xs">…</span> @endif
                            @endif
                            @foreach ($plans->getUrlRange($start, $end) as $page => $url)
                                <a href="{{ $url }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold transition {{ $page == $current ? 'bg-primary text-white' : 'text-muted hover:text-primary hover:bg-[#f1f5f9]' }}">{{ $page }}</a>
                            @endforeach
                            @if ($end < $last)
                                @if ($end < $last - 1) <span class="px-1 text-muted text-xs">…</span> @endif
                                <a href="{{ $plans->url($last) }}" class="w-8 h-8 rounded-lg grid place-items-center text-xs font-semibold text-muted hover:text-primary hover:bg-[#f1f5f9] transition">{{ $last }}</a>
                            @endif
                            @if ($plans->hasMorePages())
                                <a href="{{ $plans->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-xs">Next →</a>
                            @else
                                <span class="px-3 py-1.5 rounded-lg text-muted/40 text-xs cursor-not-allowed">Next →</span>
                            @endif
                        </div>
                    </div>
                @endif
            @endif
        </div>

        <div x-show="deleteId !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100" x-transition:leave="transition duration-150"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-black/40" @click="deleteId = null"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6"
                 x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 grid place-items-center shrink-0">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-primary text-base">Hapus Rencana?</h3>
                        <p class="page-sub">Rencana berikut akan dihapus permanen:</p>
                        <p class="mt-2 text-sm text-primary font-medium bg-[#f8fafc] rounded-lg px-3 py-2 border border-line" x-text="deleteText"></p>
                        <p class="text-xs text-red-500 mt-2">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 mt-6">
                    <button type="button" @click="deleteId = null"
                            class="px-4 py-2 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary hover:border-primary/30 transition">Batal</button>
                    <form method="POST" :action="`/user/perencanaan-investasi/${deleteId}`">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700 transition">Ya, Hapus</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection
