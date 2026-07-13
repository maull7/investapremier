@extends('layouts.user')

@section('title', 'Koneksi Advisor')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="page-title">Koneksi Advisor</h1>
        <p class="page-sub">Kelola permintaan koneksi dari advisor</p>
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

    {{-- Permintaan Masuk --}}
    <div class="bg-white rounded-xl border border-line overflow-hidden">
        <div class="px-6 py-4 border-b border-line">
            <h2 class="font-bold text-primary text-sm">Permintaan Koneksi</h2>
        </div>

        @if ($requests->isEmpty())
            <div class="p-12 text-center text-muted text-sm">Tidak ada permintaan koneksi.</div>
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
                                <form method="POST" action="{{ route('user.clients.requests.approve', $req) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-4 py-1.5 bg-accent text-white rounded-lg text-xs font-semibold hover:bg-accent/90 transition">Setujui</button>
                                </form>
                                <form method="POST" action="{{ route('user.clients.requests.reject', $req) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-4 py-1.5 border border-red-200 text-red-600 rounded-lg text-xs font-semibold hover:bg-red-50 transition">Tolak</button>
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
