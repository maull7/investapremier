@extends('layouts.user')

@section('title', 'Advisor Dashboard — InvestaPremier')

@section('content')
<style>
.stat-card{border-radius:16px;padding:22px;position:relative;overflow:hidden;color:#fff}
.stat-card h3{font-size:12px;font-weight:600;opacity:.8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px}
.stat-card .val{font-size:26px;font-weight:800;letter-spacing:-.02em}
.stat-card .sub{font-size:12px;opacity:.75;margin-top:4px}
.stat-card .card-icon{position:absolute;right:-10px;bottom:-10px;opacity:.15;width:80px;height:80px}
.stat-card .card-icon svg{width:80px;height:80px;stroke:currentColor;fill:none;stroke-width:1.2}
.g1{background:linear-gradient(135deg,#16a34a 0%,#22c55e 100%);box-shadow:0 8px 24px rgba(22,163,74,.3)}
.g2{background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 100%);box-shadow:0 8px 24px rgba(15,23,42,.25)}
.g3{background:linear-gradient(135deg,#0891b2 0%,#06b6d4 100%);box-shadow:0 8px 24px rgba(8,145,178,.25)}
.g4{background:linear-gradient(135deg,#7c3aed 0%,#a78bfa 100%);box-shadow:0 8px 24px rgba(124,58,237,.25)}
</style>

<div class="flex items-center justify-between mb-7">
    <div>
        <h1 class="text-2xl font-bold text-gray-900" style="letter-spacing:-.02em">Advisor Dashboard</h1>
        <p class="text-gray-500 text-sm mt-1">Overview portfolio klien Anda</p>
    </div>

</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
    <div class="stat-card g1">
        <h3>Total Klien</h3>
        <div class="val">{{ $totalClients }}</div>
        <div class="sub">Klien terdaftar</div>
        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
    </div>
    <div class="stat-card g2">
        <h3>Total AUM</h3>
        <div class="val">{{ $totalAumFormatted }}</div>
        <div class="sub">Aset yang dikelola</div>
        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
    </div>
    <div class="stat-card g3">
        <h3>Rata-rata AUM</h3>
        <div class="val">{{ $averageAumFormatted }}</div>
        <div class="sub">Per klien</div>
        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg></div>
    </div>
    <div class="stat-card g4">
        <h3>Permintaan Tertunda</h3>
        <div class="val">{{ $pendingCount }}</div>
        <div class="sub">Menunggu persetujuan</div>
        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100" style="background:linear-gradient(135deg,#0f172a,#1a2744)">
            <h2 class="text-sm font-bold" style="color:#fff">Klien Terbaru</h2>
            <a href="{{ route('user.clients.index') }}" class="text-xs font-semibold" style="color:#4ade80">Lihat Semua →</a>
        </div>
        @if(count($recentClients) > 0)
        <div class="divide-y divide-gray-50">
            @foreach($recentClients as $client)
            <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full text-white text-xs font-bold grid place-items-center flex-shrink-0" style="background:linear-gradient(135deg,#16a34a,#22c55e)">
                        {{ substr($client['name'], 0, 2) }}
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900 text-sm">{{ $client['name'] }}</div>
                        <div class="text-xs text-gray-400">{{ $client['riskProfile'] ?? 'Profil risiko: —' }}</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-gray-900">{{ $client['totalAumFormatted'] }}</div>
                    <a href="{{ route('user.clients.show', $client['id']) }}" class="text-xs text-green-600 hover:underline">Detail</a>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="p-12 text-center text-sm text-gray-400">Belum ada klien terdaftar.</div>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100" style="background:linear-gradient(135deg,#0f172a,#1a2744)">
            <h2 class="text-sm font-bold" style="color:#fff">Semua Klien — AUM</h2>
            <a href="{{ route('user.clients.index') }}" class="text-xs font-semibold" style="color:#4ade80">Kelola</a>
        </div>
        @if(count($clientAumList) > 0)
        <div class="divide-y divide-gray-50">
            @foreach($clientAumList as $client)
            <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full text-white text-xs font-bold grid place-items-center flex-shrink-0" style="background:linear-gradient(135deg,#16a34a,#22c55e)">
                        {{ substr($client['name'], 0, 2) }}
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900 text-sm">{{ $client['name'] }}</div>
                        <div class="text-xs text-gray-400">{{ $client['email'] }}</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-gray-900">{{ $client['totalAumFormatted'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="p-12 text-center text-sm text-gray-400">Belum ada data portfolio klien.</div>
        @endif
    </div>
</div>
@endsection
