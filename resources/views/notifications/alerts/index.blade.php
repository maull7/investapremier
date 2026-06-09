@extends('layouts.user')

@section('title', 'Alert Harga Saham - InvestaPremier')

@section('content')
    <div>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="page-title">Alert Harga Saham</h1>
                <p class="page-sub">Kelola target harga saham. Notifikasi otomatis muncul saat harga menyentuh target.</p>
            </div>
            <a href="{{ route('user.price-alerts.create') }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Alert
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
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Daftar Alert
                </h2>
                <div class="th-meta">{{ $alerts->total() }} alert</div>
            </div>

            @if ($alerts->isEmpty())
                <div class="empty-state">
                    <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <p>Belum ada alert.</p>
                    <p class="text-xs mt-1">Buat alert harga untuk dapat notifikasi saat target tercapai.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th>Saham</th>
                                <th>Kondisi</th>
                                <th>Target</th>
                                <th>Harga Terakhir</th>
                                <th>Status</th>
                                <th>Terakhir Trigger</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($alerts as $alert)
                                <tr>
                                    <td>
                                        <div class="font-semibold text-gray-900">{{ $alert->kode_efek }}</div>
                                        @if ($alert->stock)
                                            <div class="text-xs text-gray-400">{{ $alert->stock->nama }}</div>
                                        @endif
                                        @if ($alert->note)
                                            <div class="text-xs text-gray-400 italic mt-1">{{ $alert->note }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($alert->condition === 'above')
                                            <span class="inline-flex items-center gap-1 text-green-700 text-xs font-semibold">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                                ≥ Naik
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-red-600 text-xs font-semibold">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                                                ≤ Turun
                                            </span>
                                        @endif
                                    </td>
                                    <td class="font-semibold">Rp {{ number_format((float) $alert->target_price, 2, ',', '.') }}</td>
                                    <td>
                                        @if ($alert->last_seen_price)
                                            Rp {{ number_format((float) $alert->last_seen_price, 2, ',', '.') }}
                                        @else
                                            <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($alert->is_active)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-green-100 text-green-700">Aktif</span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-gray-100 text-gray-500">Nonaktif</span>
                                        @endif
                                        @if ($alert->repeat)
                                            <span class="ml-1 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-700">Repeat</span>
                                        @endif
                                    </td>
                                    <td class="text-xs text-gray-500">
                                        {{ $alert->triggered_at ? $alert->triggered_at->diffForHumans() : '-' }}
                                    </td>
                                    <td class="text-right">
                                        <div class="inline-flex items-center gap-1">
                                            <form method="POST" action="{{ route('user.price-alerts.toggle', $alert) }}">
                                                @csrf
                                                <button type="submit" class="btn-icon btn-secondary" title="{{ $alert->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                    @if ($alert->is_active)
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    @else
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    @endif
                                                </button>
                                            </form>
                                            <a href="{{ route('user.price-alerts.edit', $alert) }}" class="btn-icon btn-secondary" title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                            <button type="button"
                                                    onclick="confirmDelete(this)"
                                                    data-action="{{ route('user.price-alerts.destroy', $alert) }}"
                                                    data-label="Alert {{ $alert->kode_efek }}"
                                                    class="btn-icon btn-danger" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $alerts->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
