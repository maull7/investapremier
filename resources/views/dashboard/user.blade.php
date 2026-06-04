@extends('layouts.user')

@section('title', 'Dashboard — InvestaPremier')

@section('content')
<style>
/* ── Gradient stat cards ── */
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

/* ── Button system ── */
.btn-ip{display:inline-flex;align-items:center;gap:7px;padding:10px 18px;border-radius:8px;font-family:'Poppins',sans-serif;font-weight:600;font-size:13px;border:1.5px solid transparent;cursor:pointer;transition:all .2s;text-decoration:none}
.btn-ip svg{width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0}
.btn-green{background:linear-gradient(135deg,#16a34a,#22c55e);color:#fff;border-color:transparent;box-shadow:0 2px 10px rgba(22,163,74,.28)}
.btn-green:hover{box-shadow:0 4px 18px rgba(22,163,74,.4);transform:translateY(-1px)}
.btn-outline-green{background:transparent;color:#16a34a;border-color:#bbf7d0}
.btn-outline-green:hover{background:#f0fdf4;border-color:#16a34a}
.btn-outline-dark{background:transparent;color:#334155;border-color:#e2e8f0}
.btn-outline-dark:hover{background:#f8fafc;border-color:#cbd5e1}
.btn-danger{background:#fff0f0;color:#ef4444;border-color:#fecaca}
.btn-danger:hover{background:#fee2e2;border-color:#ef4444}

/* ── Progress bar ── */
.prog-track{height:8px;background:#f1f5f9;border-radius:999px;overflow:hidden;margin-top:8px}
.prog-fill{height:100%;background:linear-gradient(90deg,#16a34a,#22c55e);border-radius:999px;transition:width .5s}

/* ── Section header ── */
.sec-title{font-size:15px;font-weight:700;color:#0f172a;display:flex;align-items:center;gap:8px}
.sec-title svg{width:18px;height:18px;stroke:#16a34a;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
</style>

{{-- Page header --}}
<div class="flex items-center justify-between mb-7">
    <div>
        <h1 class="text-2xl font-bold text-gray-900" style="letter-spacing:-.02em">Financial Cockpit</h1>
        <p class="text-gray-500 text-sm mt-1">Selamat datang, <span class="font-semibold text-green-600">{{ Auth::user()->name }}</span> 👋</p>
    </div>
    <a href="{{ route('user.perencanaan-investasi.create') }}" class="btn-ip btn-green hidden sm:inline-flex">
        <svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
        Buat Rencana
    </a>
</div>

{{-- Stat cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
    <div class="stat-card g1">
        <h3>Total Kekayaan</h3>
        <div class="val">Rp 18,5M</div>
        <div class="sub flex items-center gap-1">
            <svg style="width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round" viewBox="0 0 24 24"><path d="M5 15l7-7 7 7"/></svg>
            +12,5% bulan ini
        </div>
        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
    </div>
    <div class="stat-card g2">
        <h3>Aset Investasi</h3>
        <div class="val">Rp 11,2M</div>
        <div class="sub">60,5% dari kekayaan</div>
        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg></div>
    </div>
    <div class="stat-card g3">
        <h3>Likuiditas</h3>
        <div class="val">Rp 1,4M</div>
        <div class="sub">7,6% dari kekayaan</div>
        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div>
    </div>
    <div class="stat-card g4">
        <h3>Next Review</h3>
        <div class="val text-xl">24 Mar 2026</div>
        <div class="sub">Dalam 12 hari</div>
        <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
    </div>
</div>

{{-- Main content --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
    {{-- Asset Allocation --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
        <div class="flex items-center justify-between mb-5">
            <div class="sec-title">
                <svg viewBox="0 0 24 24"><path d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                Asset Allocation
            </div>
            <span class="text-xs text-gray-400 font-medium">Total Portofolio</span>
        </div>
        <div class="space-y-4">
            @foreach([
                ['Saham',30,'from-green-600 to-green-400'],
                ['Obligasi',25,'from-blue-600 to-blue-400'],
                ['Reksa Dana',20,'from-amber-500 to-yellow-400'],
                ['Kas',10,'from-cyan-500 to-cyan-400'],
                ['Properti & Lainnya',15,'from-purple-600 to-violet-400'],
            ] as [$label,$pct,$grad])
            <div>
                <div class="flex justify-between text-sm mb-1.5">
                    <span class="font-500 text-gray-700">{{ $label }}</span>
                    <span class="font-700 text-gray-900 font-bold">{{ $pct }}%</span>
                </div>
                <div class="prog-track">
                    <div class="prog-fill bg-gradient-to-r {{ $grad }}" style="width:{{ $pct }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Goal Progress --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm">
        <div class="flex items-center justify-between mb-5">
            <div class="sec-title">
                <svg viewBox="0 0 24 24"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                Goal Progress
            </div>
            <a href="{{ route('user.perencanaan-investasi.index') }}" class="btn-ip btn-outline-green" style="padding:6px 12px;font-size:12px">Kelola Goal</a>
        </div>
        <div class="space-y-5">
            @foreach([
                ['Pendidikan Anak',72,'Rp 1,2M','Rp 864Jt'],
                ['Pensiun',54,'Rp 5M','Rp 2,7M'],
                ['Legacy Fund',38,'Rp 3M','Rp 1,14M'],
            ] as [$goal,$pct,$target,$collected])
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="font-600 text-gray-800 font-semibold">{{ $goal }}</span>
                    <span class="font-bold text-green-600">{{ $pct }}%</span>
                </div>
                <div class="prog-track" style="height:10px">
                    <div class="prog-fill" style="width:{{ $pct }}%;position:relative">
                        <div style="position:absolute;right:-1px;top:50%;transform:translateY(-50%);width:14px;height:14px;background:#fff;border-radius:50%;border:2px solid #16a34a;box-shadow:0 1px 4px rgba(22,163,74,.3)"></div>
                    </div>
                </div>
                <div class="flex justify-between text-xs text-gray-400 mt-1">
                    <span>Target: {{ $target }}</span>
                    <span>Terkumpul: {{ $collected }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Bottom row --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    {{-- Alert Center --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
        <div class="sec-title mb-4">
            <svg viewBox="0 0 24 24"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
            Alert Center
        </div>
        <div class="space-y-3">
            <div class="flex items-start gap-3 p-3 rounded-xl bg-red-50 border border-red-100 text-red-700">
                <svg style="width:15px;height:15px;margin-top:1px;flex-shrink:0;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
                <span class="text-sm leading-relaxed">Goal pendidikan kurang Rp 250 juta</span>
            </div>
            <div class="flex items-start gap-3 p-3 rounded-xl bg-amber-50 border border-amber-100 text-amber-700">
                <svg style="width:15px;height:15px;margin-top:1px;flex-shrink:0;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                <span class="text-sm leading-relaxed">2 dokumen perlu diperbarui</span>
            </div>
            <div class="flex items-start gap-3 p-3 rounded-xl bg-blue-50 border border-blue-100 text-blue-700">
                <svg style="width:15px;height:15px;margin-top:1px;flex-shrink:0;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <span class="text-sm leading-relaxed">Premi asuransi jatuh tempo minggu ini</span>
            </div>
        </div>
    </div>

    {{-- Advisor Notes --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
        <div class="sec-title mb-4">
            <svg viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Advisor Notes
        </div>
        <div class="rounded-xl p-4 border border-green-100" style="background:linear-gradient(135deg,#f0fdf4,#dcfce7)">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-full flex-shrink-0 text-white text-xs font-bold grid place-items-center" style="background:linear-gradient(135deg,#16a34a,#22c55e)">AD</div>
                <div>
                    <div class="text-sm font-semibold text-gray-900">Advisor Recommendation</div>
                    <p class="text-sm text-gray-600 mt-1 leading-relaxed">Review ulang alokasi obligasi, tambah proteksi kesehatan, dan periksa dokumen legacy keluarga.</p>
                    <div class="text-xs text-green-600 font-semibold mt-2">2 hari yang lalu</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
        <div class="sec-title mb-4">
            <svg viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            Quick Actions
        </div>
        <div class="grid grid-cols-2 gap-2.5">
            <a href="{{ route('user.perencanaan-investasi.create') }}"
               class="flex flex-col items-center gap-1.5 p-3.5 rounded-xl border border-green-100 text-green-700 hover:bg-green-50 transition group"
               style="background:linear-gradient(135deg,#f0fdf4,#dcfce7)">
                <svg style="width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round" viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                <span class="text-xs font-600 font-semibold text-center leading-tight">Tambah Goal</span>
            </a>
            <a href="{{ route('user.reksa-dana.index') }}"
               class="flex flex-col items-center gap-1.5 p-3.5 rounded-xl border border-blue-100 text-blue-700 hover:bg-blue-50 transition"
               style="background:linear-gradient(135deg,#eff6ff,#dbeafe)">
                <svg style="width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round" viewBox="0 0 24 24"><path d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                <span class="text-xs font-semibold text-center leading-tight">Reksa Dana</span>
            </a>
            <a href="{{ route('user.analisa.index') }}"
               class="flex flex-col items-center gap-1.5 p-3.5 rounded-xl border border-amber-100 text-amber-700 hover:bg-amber-50 transition"
               style="background:linear-gradient(135deg,#fffbeb,#fef3c7)">
                <svg style="width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round" viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"/></svg>
                <span class="text-xs font-semibold text-center leading-tight">Analisa RD</span>
            </a>
            <a href="{{ route('user.analisa-saham.index') }}"
               class="flex flex-col items-center gap-1.5 p-3.5 rounded-xl border border-purple-100 text-purple-700 hover:bg-purple-50 transition"
               style="background:linear-gradient(135deg,#faf5ff,#ede9fe)">
                <svg style="width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round" viewBox="0 0 24 24"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                <span class="text-xs font-semibold text-center leading-tight">Analisa Saham</span>
            </a>
        </div>
    </div>
</div>
@endsection
