@extends('layouts.admin')

@section('title', 'Dashboard — InvestaPremier')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900" style="letter-spacing:-.02em">
        Selamat datang, {{ Auth::user()->name }}
    </h1>
    <p class="text-gray-500 text-sm mt-1">Dashboard akses terbatas — menu yang tampil sesuai dengan izin yang diberikan.</p>
</div>

@php
    $user = Auth::user();
    $perms = $user->getPermissionsList();
    $hasAny = fn(...$keys) => !empty(array_intersect($perms, $keys)) || $perms === ['*'];
@endphp

{{-- Quick Links --}}
<div class="mb-7">
    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Menu Cepat</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
        @if($hasAny('manajemen.dashboard'))
        <a href="{{ route('admin.dashboard') }}"
           class="flex items-center gap-3 px-4 py-3.5 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-green-200 transition-all">
            <div class="w-10 h-10 rounded-lg grid place-items-center shrink-0" style="background:#f0fdf4">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-900">Dashboard</span>
        </a>
        @endif

        @if($hasAny('reksa-dana.monitor-ffs', 'reksa-dana.analisa-rd', 'reksa-dana.monitor-analisa'))
        <a href="{{ route('admin.reksa-dana.index') }}"
           class="flex items-center gap-3 px-4 py-3.5 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-green-200 transition-all">
            <div class="w-10 h-10 rounded-lg grid place-items-center shrink-0" style="background:#f0fdf4">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-900">Reksa Dana</span>
        </a>
        @endif

        @if($hasAny('unit-link.daftar', 'unit-link.monitor-ffs', 'unit-link.analisa', 'unit-link.monitor-analisa'))
        <a href="{{ route('admin.unit-link.index') }}"
           class="flex items-center gap-3 px-4 py-3.5 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-green-200 transition-all">
            <div class="w-10 h-10 rounded-lg grid place-items-center shrink-0" style="background:#f0fdf4">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-900">Unit Link</span>
        </a>
        @endif

        @if($hasAny('saham.daftar', 'saham.analisa', 'saham.monitor-analisa'))
        <a href="{{ route('admin.saham.index') }}"
           class="flex items-center gap-3 px-4 py-3.5 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-green-200 transition-all">
            <div class="w-10 h-10 rounded-lg grid place-items-center shrink-0" style="background:#f0fdf4">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-900">Saham</span>
        </a>
        @endif

        @if($hasAny('obligasi.daftar', 'obligasi.rating', 'obligasi.ytm', 'obligasi.analisa', 'obligasi.monitor-analisa'))
        <a href="{{ route('admin.obligasi.index') }}"
           class="flex items-center gap-3 px-4 py-3.5 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-green-200 transition-all">
            <div class="w-10 h-10 rounded-lg grid place-items-center shrink-0" style="background:#f0fdf4">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-900">Obligasi</span>
        </a>
        @endif

        @if($hasAny('investment-managers'))
        <a href="{{ route('admin.investment-managers.index') }}"
           class="flex items-center gap-3 px-4 py-3.5 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-green-200 transition-all">
            <div class="w-10 h-10 rounded-lg grid place-items-center shrink-0" style="background:#f0fdf4">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-900">Manajer Investasi</span>
        </a>
        @endif

        @if($hasAny('ai-prompts'))
        <a href="{{ route('admin.ai-prompts.index') }}"
           class="flex items-center gap-3 px-4 py-3.5 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-green-200 transition-all">
            <div class="w-10 h-10 rounded-lg grid place-items-center shrink-0" style="background:#f0fdf4">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-900">AI Prompts</span>
        </a>
        @endif
    </div>
</div>

{{-- Info Akun --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100" style="background:linear-gradient(135deg,#0f172a,#1a2744)">
        <h2 class="text-sm font-semibold text-white flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Informasi Akun
        </h2>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Nama</span>
                <div class="text-sm font-semibold text-gray-900 mt-1">{{ $user->name }}</div>
            </div>
            <div>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Email</span>
                <div class="text-sm font-semibold text-gray-900 mt-1">{{ $user->email }}</div>
            </div>
            <div>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Role</span>
                <div class="text-sm font-semibold text-gray-900 mt-1">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        Sub Admin
                    </span>
                </div>
            </div>
        </div>

        @if(!empty($perms) && $perms !== ['*'])
        <div class="mt-6 pt-6 border-t border-gray-100">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Izin yang diberikan</span>
            <div class="flex flex-wrap gap-1.5 mt-3">
                @foreach($perms as $perm)
                    @php
                        $label = match(true) {
                            str_starts_with($perm, 'manajemen') => str_replace('manajemen.', '', $perm),
                            str_starts_with($perm, 'reksa-dana') => str_replace('reksa-dana.', '', $perm),
                            str_starts_with($perm, 'unit-link') => str_replace('unit-link.', '', $perm),
                            str_starts_with($perm, 'saham') => str_replace('saham.', '', $perm),
                            str_starts_with($perm, 'obligasi') => str_replace('obligasi.', '', $perm),
                            default => $perm,
                        };
                        $color = match(true) {
                            str_starts_with($perm, 'manajemen') => 'bg-blue-50 text-blue-700',
                            str_starts_with($perm, 'reksa-dana') => 'bg-green-50 text-green-700',
                            str_starts_with($perm, 'unit-link') => 'bg-purple-50 text-purple-700',
                            str_starts_with($perm, 'saham') => 'bg-amber-50 text-amber-700',
                            str_starts_with($perm, 'obligasi') => 'bg-rose-50 text-rose-700',
                            default => 'bg-gray-50 text-gray-700',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $color }}">
                        {{ $label }}
                    </span>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
