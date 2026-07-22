@extends('layouts.user')

@section('title', 'Notifikasi - InvestaPremier')

@section('content')
    <div>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-accent-teal/85">Notifikasi</h1>
                <p class="page-sub">
                    Riwayat notifikasi alert harga saham &amp; aktivitas lainnya.
                    @if ($unreadCount > 0)
                        <span
                            class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-red-100 text-red-600">{{ $unreadCount }}
                            belum dibaca</span>
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('user.price-alerts.create') }}"
                    class="bg-accent-teal/85 hover:bg-accent-teal/90 text-white font-medium py-2 px-4 rounded-lg transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Alert Harga
                </a>
                <a href="{{ route('user.price-alerts.index') }}" class="btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Log Notifikasi
                </a>
                @if ($unreadCount > 0)
                    <form method="POST" action="{{ route('user.notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="btn-secondary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Tandai semua dibaca
                        </button>
                    </form>
                @endif
                @if ($notifications->total() > 0)
                    <form method="POST" action="{{ route('user.notifications.clear') }}"
                        onsubmit="return confirm('Hapus semua notifikasi? Tindakan ini tidak dapat dibatalkan.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2" />
                            </svg>
                            Bersihkan
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="alert-success">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="table-card">
            <div class="table-head">
                <h2 class="th-title">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    Daftar Notifikasi
                </h2>
                <div class="th-meta">{{ $notifications->total() }} total</div>
            </div>

            @if ($notifications->isEmpty())
                <div class="empty-state">
                    <svg viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p>Belum ada notifikasi.</p>
                    <p class="text-xs mt-1">Buat alert harga saham dulu, nanti notifikasi muncul saat target tercapai.</p>
                </div>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach ($notifications as $n)
                        @php
                            $data = $n->data ?? [];
                            $isBelow = ($data['condition'] ?? '') === 'below';
                            $url = $data['url'] ?? null;
                        @endphp
                        <li class="px-5 py-4 hover:bg-gray-50 transition {{ $n->read_at ? '' : 'bg-green-50/30' }}">
                            <div class="flex items-start gap-4">
                                <div
                                    class="w-10 h-10 rounded-full grid place-items-center shrink-0 text-white {{ $isBelow ? 'bg-red-500' : 'bg-accent-teal/85' }}">
                                    @if ($isBelow)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span
                                            class="font-semibold text-sm text-gray-900">{{ $data['title'] ?? 'Notifikasi' }}</span>
                                        @if (!$n->read_at)
                                            <span
                                                class="text-[10px] font-bold uppercase tracking-wide bg-red-100 text-red-600 px-2 py-0.5 rounded-full">Baru</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">{{ $data['message'] ?? '' }}</p>
                                    @if (!empty($data['note']))
                                        <p class="text-xs text-gray-400 mt-1 italic">Catatan: {{ $data['note'] }}</p>
                                    @endif
                                    <div class="flex items-center gap-3 mt-2 text-xs text-gray-400">
                                        <span>{{ $n->created_at->format('d M Y, H:i') }}</span>
                                        <span>·</span>
                                        <span>{{ $n->created_at->diffForHumans() }}</span>
                                        @if ($url)
                                            <span>·</span>
                                            <a href="{{ $url }}"
                                                class="text-accent hover:text-accent-teal/90 font-semibold">Lihat detail
                                                →</a>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 shrink-0">
                                    @if (!$n->read_at)
                                        <form method="POST" action="{{ route('user.notifications.read', $n->id) }}">
                                            @csrf
                                            <button type="submit" class="btn-icon btn-secondary" title="Tandai dibaca">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('user.notifications.destroy', $n->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon btn-danger" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
