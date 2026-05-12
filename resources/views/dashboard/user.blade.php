@extends('layouts.user')

@section('title', 'Dashboard - InvestaPremier')

@section('content')
<div class="mb-8">
    <div class="flex items-center gap-3 mb-1">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-primary-light text-white grid place-items-center text-lg shadow-lg shadow-primary/20">✦</div>
        <div>
            <h1 class="text-2xl font-bold text-primary">Financial Cockpit</h1>
            <p class="text-muted text-sm">Ringkasan wealth portfolio keluarga</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="relative bg-white rounded-2xl border border-line p-5 shadow-sm overflow-hidden">
        <div class="absolute top-0 right-0 w-24 h-24 bg-accent/5 rounded-full -mt-6 -mr-6"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-2">
                <div class="w-9 h-9 rounded-lg bg-primary/10 grid place-items-center text-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs text-muted">Total Kekayaan</span>
            </div>
            <div class="text-2xl font-extrabold text-primary">Rp 18,5 M</div>
            <div class="text-xs text-green-600 font-medium mt-1 flex items-center gap-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                +12.5% dari bulan lalu
            </div>
        </div>
    </div>

    <div class="relative bg-white rounded-2xl border border-line p-5 shadow-sm overflow-hidden">
        <div class="absolute top-0 right-0 w-24 h-24 bg-accent/5 rounded-full -mt-6 -mr-6"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-2">
                <div class="w-9 h-9 rounded-lg bg-accent/10 grid place-items-center text-accent">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <span class="text-xs text-muted">Aset Investasi</span>
            </div>
            <div class="text-2xl font-extrabold text-primary">Rp 11,2 M</div>
            <div class="text-xs text-muted mt-1">60.5% dari total kekayaan</div>
        </div>
    </div>

    <div class="relative bg-white rounded-2xl border border-line p-5 shadow-sm overflow-hidden">
        <div class="absolute top-0 right-0 w-24 h-24 bg-accent/5 rounded-full -mt-6 -mr-6"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-2">
                <div class="w-9 h-9 rounded-lg bg-accent/10 grid place-items-center text-accent">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <span class="text-xs text-muted">Likuiditas</span>
            </div>
            <div class="text-2xl font-extrabold text-accent">Rp 1,4 M</div>
            <div class="text-xs text-muted mt-1">7.6% dari total kekayaan</div>
        </div>
    </div>

    <div class="relative bg-white rounded-2xl border border-line p-5 shadow-sm overflow-hidden">
        <div class="absolute top-0 right-0 w-24 h-24 bg-gold/5 rounded-full -mt-6 -mr-6"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-2">
                <div class="w-9 h-9 rounded-lg bg-gold/10 grid place-items-center text-gold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <span class="text-xs text-muted">Next Review</span>
            </div>
            <div class="text-2xl font-extrabold text-gold">24 Mar 2026</div>
            <div class="text-xs text-muted mt-1">Dalam 12 hari</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
    <div class="bg-white rounded-2xl border border-line p-6 shadow-sm">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="font-bold text-primary flex items-center gap-2">
                    <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                    Asset Allocation
                </h3>
                <p class="text-xs text-muted mt-0.5 ml-7">Portofolio per kategori aset</p>
            </div>
        </div>
        <div class="space-y-4">
            <div class="group">
                <div class="flex justify-between text-sm mb-1.5"><span class="text-primary font-medium">Saham</span><span class="font-bold text-primary">30%</span></div>
                <div class="h-3 bg-[#e2e8f0] rounded-full overflow-hidden shadow-inner">
                    <div class="h-full bg-gradient-to-r from-primary to-primary-light rounded-full transition-all duration-500 group-hover:shadow-lg group-hover:shadow-primary/20" style="width:30%"></div>
                </div>
            </div>
            <div class="group">
                <div class="flex justify-between text-sm mb-1.5"><span class="text-primary font-medium">Obligasi</span><span class="font-bold text-primary">25%</span></div>
                <div class="h-3 bg-[#e2e8f0] rounded-full overflow-hidden shadow-inner">
                    <div class="h-full bg-gradient-to-r from-accent to-accent-light rounded-full transition-all duration-500 group-hover:shadow-lg group-hover:shadow-accent/20" style="width:25%"></div>
                </div>
            </div>
            <div class="group">
                <div class="flex justify-between text-sm mb-1.5"><span class="text-primary font-medium">Reksa Dana</span><span class="font-bold text-primary">20%</span></div>
                <div class="h-3 bg-[#e2e8f0] rounded-full overflow-hidden shadow-inner">
                    <div class="h-full bg-gradient-to-r from-gold to-amber-400 rounded-full transition-all duration-500 group-hover:shadow-lg group-hover:shadow-gold/20" style="width:20%"></div>
                </div>
            </div>
            <div class="group">
                <div class="flex justify-between text-sm mb-1.5"><span class="text-primary font-medium">Kas</span><span class="font-bold text-primary">10%</span></div>
                <div class="h-3 bg-[#e2e8f0] rounded-full overflow-hidden shadow-inner">
                    <div class="h-full bg-gradient-to-r from-cyan-500 to-cyan-400 rounded-full transition-all duration-500 group-hover:shadow-lg group-hover:shadow-cyan-400/20" style="width:10%"></div>
                </div>
            </div>
            <div class="group">
                <div class="flex justify-between text-sm mb-1.5"><span class="text-primary font-medium">Properti & Lainnya</span><span class="font-bold text-primary">15%</span></div>
                <div class="h-3 bg-[#e2e8f0] rounded-full overflow-hidden shadow-inner">
                    <div class="h-full bg-gradient-to-r from-violet-500 to-violet-400 rounded-full transition-all duration-500 group-hover:shadow-lg group-hover:shadow-violet-400/20" style="width:15%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-line p-6 shadow-sm">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="font-bold text-primary flex items-center gap-2">
                    <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    Goal Progress
                </h3>
                <p class="text-xs text-muted mt-0.5 ml-7">Status tujuan utama keluarga</p>
            </div>
        </div>
        <div class="space-y-6">
            <div>
                <div class="flex justify-between text-sm mb-1.5"><span class="text-primary font-medium">Pendidikan Anak</span><span class="font-bold text-accent">72%</span></div>
                <div class="h-3.5 bg-[#e2e8f0] rounded-full overflow-hidden shadow-inner">
                    <div class="h-full bg-gradient-to-r from-accent to-emerald-400 rounded-full relative" style="width:72%">
                        <div class="absolute right-0 top-1/2 -translate-y-1/2 w-4 h-4 bg-white rounded-full shadow-md border-2 border-accent"></div>
                    </div>
                </div>
                <div class="flex justify-between text-xs text-muted mt-1"><span>Target: Rp 1,2 M</span><span>Terkumpul: Rp 864 Jt</span></div>
            </div>
            <div>
                <div class="flex justify-between text-sm mb-1.5"><span class="text-primary font-medium">Pensiun</span><span class="font-bold text-accent">54%</span></div>
                <div class="h-3.5 bg-[#e2e8f0] rounded-full overflow-hidden shadow-inner">
                    <div class="h-full bg-gradient-to-r from-accent to-emerald-400 rounded-full relative" style="width:54%">
                        <div class="absolute right-0 top-1/2 -translate-y-1/2 w-4 h-4 bg-white rounded-full shadow-md border-2 border-accent"></div>
                    </div>
                </div>
                <div class="flex justify-between text-xs text-muted mt-1"><span>Target: Rp 5 M</span><span>Terkumpul: Rp 2,7 M</span></div>
            </div>
            <div>
                <div class="flex justify-between text-sm mb-1.5"><span class="text-primary font-medium">Legacy Fund</span><span class="font-bold text-accent">38%</span></div>
                <div class="h-3.5 bg-[#e2e8f0] rounded-full overflow-hidden shadow-inner">
                    <div class="h-full bg-gradient-to-r from-accent to-emerald-400 rounded-full relative" style="width:38%">
                        <div class="absolute right-0 top-1/2 -translate-y-1/2 w-4 h-4 bg-white rounded-full shadow-md border-2 border-accent"></div>
                    </div>
                </div>
                <div class="flex justify-between text-xs text-muted mt-1"><span>Target: Rp 3 M</span><span>Terkumpul: Rp 1,14 M</span></div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="bg-white rounded-2xl border border-line p-5 shadow-sm">
        <h3 class="font-bold text-primary flex items-center gap-2 mb-4">
            <span class="w-7 h-7 rounded-lg bg-amber-100 text-amber-600 grid place-items-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </span>
            Alert Center
        </h3>
        <div class="space-y-3">
            <div class="flex items-start gap-3 p-3 rounded-xl bg-red-50 text-red-700">
                <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-sm">Goal pendidikan kurang Rp 250 juta</span>
            </div>
            <div class="flex items-start gap-3 p-3 rounded-xl bg-amber-50 text-amber-700">
                <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-sm">2 dokumen perlu diperbarui</span>
            </div>
            <div class="flex items-start gap-3 p-3 rounded-xl bg-blue-50 text-blue-700">
                <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span class="text-sm">Premi asuransi jatuh tempo minggu ini</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-line p-5 shadow-sm">
        <h3 class="font-bold text-primary flex items-center gap-2 mb-4">
            <span class="w-7 h-7 rounded-lg bg-accent/10 text-accent grid place-items-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </span>
            Advisor Notes
        </h3>
        <div class="bg-gradient-to-br from-accent/5 to-accent/10 rounded-xl p-4 border border-accent/10">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-accent text-white grid place-items-center text-xs font-bold shrink-0">AD</div>
                <div>
                    <div class="text-sm font-semibold text-primary">Advisor Recommendation</div>
                    <p class="text-sm text-muted mt-1 leading-relaxed">Review ulang alokasi obligasi, tambah proteksi kesehatan, dan periksa dokumen legacy keluarga.</p>
                    <div class="text-xs text-accent font-medium mt-2">2 hari yang lalu</div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-line p-5 shadow-sm">
        <h3 class="font-bold text-primary flex items-center gap-2 mb-4">
            <span class="w-7 h-7 rounded-lg bg-primary/10 text-primary grid place-items-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </span>
            Quick Actions
        </h3>
        <div class="grid grid-cols-2 gap-2.5">
            <button class="flex flex-col items-center gap-1.5 p-3.5 rounded-xl bg-gradient-to-br from-accent/5 to-accent/10 border border-accent/10 hover:from-accent/10 hover:to-accent/20 transition-all text-accent group">
                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span class="text-xs font-semibold">Tambah Aset</span>
            </button>
            <button class="flex flex-col items-center gap-1.5 p-3.5 rounded-xl bg-gradient-to-br from-gold/5 to-amber-400/10 border border-gold/10 hover:from-gold/10 hover:to-amber-400/20 transition-all text-gold group">
                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                <span class="text-xs font-semibold">Tambah Goal</span>
            </button>
            <button class="flex flex-col items-center gap-1.5 p-3.5 rounded-xl bg-gradient-to-br from-blue-500/5 to-blue-500/10 border border-blue-500/10 hover:from-blue-500/10 hover:to-blue-500/20 transition-all text-blue-600 group">
                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span class="text-xs font-semibold">Upload Dokumen</span>
            </button>
            <button class="flex flex-col items-center gap-1.5 p-3.5 rounded-xl bg-gradient-to-br from-violet-500/5 to-violet-500/10 border border-violet-500/10 hover:from-violet-500/10 hover:to-violet-500/20 transition-all text-violet-600 group">
                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span class="text-xs font-semibold">Jadwalkan Review</span>
            </button>
        </div>
    </div>
</div>
@endsection
