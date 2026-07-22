@extends('layouts.user')

@section('title', 'Koneksi Advisor')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="page-title">Koneksi Advisor</h1>
                <p class="page-sub">Kelola koneksi Anda dengan advisor</p>
            </div>

            <a href="{{ route('user.clients.requests.create') }}"
                class="px-4 py-2 bg-accent-teal text-white rounded-lg text-sm font-semibold hover:bg-accent-teal/90 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Advisor
            </a>

        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">{{ session('error') }}
            </div>
        @endif

        {{-- Advisor terhubung --}}
        @if ($approvedAdvisors->count() > 0)
            <div class="bg-white rounded-xl border border-line overflow-hidden">
                <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-accent to-accent/80">
                    <h2 class="font-bold text-white text-sm">Advisor Anda</h2>
                </div>
                <div class="divide-y divide-line">
                    @foreach ($approvedAdvisors as $advisor)
                        <div
                            class="flex items-center justify-between p-5 rounded-2xl border border-slate-200 bg-white hover:shadow-md transition">

                            <div class="flex items-center gap-4">

                                {{-- Avatar --}}
                                <div
                                    class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-lg shadow">
                                    {{ strtoupper(substr($advisor->name, 0, 1)) }}
                                </div>

                                {{-- Informasi --}}
                                <div>
                                    <h3 class="font-semibold text-slate-800">
                                        {{ $advisor->name }}
                                    </h3>

                                    <div class="mt-1 flex flex-wrap items-center gap-4 text-xs text-slate-500">

                                        <div class="flex items-center gap-1">
                                            <i class="fa-regular fa-envelope text-slate-400"></i>
                                            <span>{{ $advisor->email }}</span>
                                        </div>

                                        <div class="flex items-center gap-1">
                                            <i class="fa-solid fa-user-tie text-slate-400"></i>
                                            <span class="font-semibold text-muted">Financial Advisor</span>
                                        </div>

                                        @if ($advisor->created_at)
                                            <div class="flex items-center gap-1">
                                                <i class="fa-regular fa-calendar text-slate-400"></i>
                                                <span>Bergabung {{ $advisor->created_at->format('M Y') }}</span>
                                            </div>
                                        @endif

                                    </div>
                                </div>

                            </div>

                            {{-- Status --}}
                            <div class="flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="text-xs font-semibold text-emerald-700">
                                    Terhubung
                                </span>
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Permintaan yang dikirim --}}
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">

            {{-- Header --}}
            <div
                class="bg-gradient-to-r from-accent to-accent-teal/85 flex items-center justify-between px-6 py-5 border-b border-slate-200">
                <div>
                    <h2 class="text-lg font-bold text-cardBg-bg">
                        Permintaan Koneksi
                    </h2>
                    <p class="text-sm text-white">
                        Kelola permintaan koneksi dengan financial advisor.
                    </p>
                </div>

                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">
                    {{ $requests->count() }} Permintaan
                </span>
            </div>

            @if ($requests->isEmpty())

                <div class="py-16 text-center">

                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
                        <i class="fa-regular fa-user text-2xl text-slate-400"></i>
                    </div>

                    <h3 class="font-semibold text-slate-700">
                        Belum ada permintaan
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        Permintaan koneksi akan muncul di sini.
                    </p>

                </div>
            @else
                <div class="divide-y divide-slate-200">

                    @foreach ($requests as $req)
                        <div class="flex items-center justify-between gap-5 p-5 hover:bg-slate-50 transition">

                            {{-- Informasi Advisor --}}
                            <div class="flex items-center gap-4">

                                <div
                                    class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-100/70 to-slate-200/60 text-lg font-bold text-accent-teal shadow">
                                    {{ strtoupper(substr($req->advisor->name, 0, 1)) }}
                                </div>

                                <div>

                                    <h3 class="font-semibold text-slate-800">
                                        {{ $req->advisor->name }}
                                    </h3>

                                    <div class="mt-1 flex flex-wrap items-center gap-4 text-xs text-slate-500">

                                        <div class="flex items-center gap-1">
                                            <i class="fa-regular fa-envelope"></i>
                                            {{ $req->advisor->email }}
                                        </div>

                                        <div class="flex items-center gap-1">
                                            <i class="fa-solid fa-user-tie"></i>
                                            Financial Advisor
                                        </div>

                                    </div>

                                </div>

                            </div>

                            {{-- Status & Action --}}
                            <div class="flex items-center gap-3">

                                @if ($req->status === 'pending')
                                    <span
                                        class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-2 text-xs font-semibold text-amber-700">

                                        <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>

                                        Menunggu

                                    </span>

                                    <form method="POST" action="{{ route('user.clients.requests.cancel', $req) }}"
                                        onsubmit="return confirm('Batalkan permintaan?')">

                                        @csrf

                                        <button
                                            class="rounded-xl border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition">
                                            Batalkan
                                        </button>

                                    </form>
                                @elseif($req->status === 'approved')
                                    <span
                                        class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-2 text-xs font-semibold text-emerald-700">

                                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>

                                        Terhubung

                                    </span>

                                    <form id="break-form-{{ $req->id }}" method="POST"
                                        action="{{ route('user.clients.requests.break-connection', $req) }}">

                                        @csrf
                                        @method('PUT')

                                        <button type="button"
                                            onclick="openBreakModal('{{ $req->id }}', '{{ $req->advisor->name }}')"
                                            class="rounded-xl border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition">

                                            Putus Koneksi

                                        </button>

                                    </form>
                                @else
                                    <span
                                        class="inline-flex items-center gap-2 rounded-full bg-red-100 px-3 py-2 text-xs font-semibold text-red-700">

                                        <span class="h-2 w-2 rounded-full bg-red-500"></span>

                                        Ditolak

                                    </span>
                                @endif

                            </div>

                        </div>
                    @endforeach

                </div>

            @endif

        </div>
    </div>

    {{-- Modal Konfirmasi Putus Hubungan --}}
    <div id="break-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-primary text-sm">Putuskan Koneksi?</h3>
                    <p class="text-xs text-muted mt-1">
                        Anda akan memutuskan hubungan dengan
                        <span id="break-advisor-name" class="font-semibold text-primary"></span>.
                        Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-5">
                <button type="button" onclick="closeBreakModal()"
                    class="px-4 py-2 border border-line rounded-lg text-xs font-semibold text-muted hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="button" onclick="confirmBreakModal()"
                    class="px-4 py-2 bg-red-600 rounded-lg text-xs font-semibold text-white hover:bg-red-700 transition">
                    Ya, Putuskan
                </button>
            </div>
        </div>
    </div>

    <script>
        let breakFormId = null;

        function openBreakModal(reqId, advisorName) {
            breakFormId = reqId;
            document.getElementById('break-advisor-name').textContent = advisorName;
            const modal = document.getElementById('break-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeBreakModal() {
            breakFormId = null;
            const modal = document.getElementById('break-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function confirmBreakModal() {
            if (breakFormId) {
                document.getElementById('break-form-' + breakFormId).submit();
            }
            closeBreakModal();
        }

        // Tutup modal jika klik area luar
        document.getElementById('break-modal').addEventListener('click', function(e) {
            if (e.target === this) closeBreakModal();
        });
    </script>
@endsection
