@extends('layouts.user')

@section('title', 'Koneksi Advisor')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="page-title">Koneksi Advisor</h1>
            <p class="page-sub">Kelola koneksi Anda dengan advisor</p>
        </div>
        @if (!auth()->user()->advisor_id)
            <a href="{{ route('user.clients.requests.create') }}" class="btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Advisor
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    {{-- Advisor terhubung --}}
    @if ($approvedAdvisor)
        <div class="bg-white rounded-xl border border-line overflow-hidden">
            <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-accent to-accent/80">
                <h2 class="font-bold text-white text-sm">Advisor Anda</h2>
            </div>
            <div class="p-5 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-accent/10 flex items-center justify-center text-accent font-bold text-lg">
                        {{ substr($approvedAdvisor->name, 0, 1) }}
                    </div>
                    <div>
                        <p class="font-semibold text-primary">{{ $approvedAdvisor->name }}</p>
                        <p class="text-xs text-muted">{{ $approvedAdvisor->email }}</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Terhubung</span>
            </div>
        </div>
    @endif

    {{-- Permintaan yang dikirim --}}
    <div class="bg-white rounded-xl border border-line overflow-hidden">
        <div class="px-6 py-4 border-b border-line">
            <h2 class="font-bold text-primary text-sm">Permintaan Koneksi</h2>
        </div>

        @if ($requests->isEmpty())
            <div class="p-12 text-center text-muted text-sm">Belum ada permintaan koneksi.</div>
        @else
            <div class="divide-y divide-line">
                @foreach ($requests as $req)
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-[#f8fafc] transition">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-accent/10 flex items-center justify-center text-accent font-bold">
                                {{ substr($req->advisor->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="font-semibold text-primary text-sm">{{ $req->advisor->name }}</p>
                                <p class="text-xs text-muted">{{ $req->advisor->email }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if ($req->status === 'pending')
                                <span class="px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Menunggu</span>
                                <form method="POST" action="{{ route('user.clients.requests.cancel', $req) }}" class="inline"
                                      onsubmit="return confirm('Batalkan permintaan?')">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 border border-red-200 text-red-600 rounded-lg text-xs font-semibold hover:bg-red-50 transition">Batalkan</button>
                                </form>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                    {{ $req->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $req->status === 'approved' ? 'Disetujui' : 'Ditolak' }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
