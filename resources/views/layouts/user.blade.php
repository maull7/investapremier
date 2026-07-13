@extends('layouts.app')

@section('body')
<div x-data="{
    sidebarOpen: false,
    layananOpen: {{ request()->routeIs('quiz.*') || request()->routeIs('member.*') ? 'true' : 'false' }},
    reksaDanaOpen: {{ request()->routeIs('user.reksa-dana.*') || request()->routeIs('user.analisa.*') ? 'true' : 'false' }},
    unitLinkOpen: {{ request()->routeIs('user.unit-link.*') || request()->routeIs('user.unit-link-analisa.*') ? 'true' : 'false' }},
    sahamOpen: {{ request()->routeIs('user.saham.*') || request()->routeIs('user.analisa-saham.*') ? 'true' : 'false' }},
    obligasiOpen: {{ request()->routeIs('user.obligasi.*') || request()->routeIs('user.analisa-obligasi.*') ? 'true' : 'false' }},
}" class="flex h-screen overflow-hidden">
    {{-- Overlay mobile --}}
    <div x-show="sidebarOpen" x-cloak @@click="sidebarOpen = false" class="fixed inset-0 z-20 bg-black/40 lg:hidden"></div>

    {{-- Sidebar Desktop --}}
    <aside class="hidden lg:flex w-64 bg-white border-r border-gray-100 flex-col shrink-0 shadow-sm">
        <div class="h-16 flex items-center gap-3 px-5 border-b border-gray-100">
            <div class="w-9 h-9 rounded-lg grid place-items-center" style="background:linear-gradient(135deg,#16a34a,#22c55e)">IP</div>
            <div>
                <div class="font-bold text-sm text-gray-900">InvestaPremier</div>
                <div class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">WealthOS</div>
            </div>
        </div>
        <nav class="flex-1 py-4 px-3 space-y-1 text-sm overflow-y-auto">

            {{-- Dashboard --}}
            <a href="{{ route('user.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.dashboard') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>

            {{-- Layanan --}}
            <div>
                <button type="button" @click="layananOpen = !layananOpen"
                    class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('quiz.*') || request()->routeIs('member.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        Layanan
                    </span>
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': layananOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="layananOpen" x-transition class="space-y-1 pl-3">
                    <a href="{{ route('quiz.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('quiz.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        Profil Investasi
                    </a>
                    <a href="{{ route('member.create') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('member.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        Daftar Member
                    </a>
                </div>
            </div>

            {{-- Reksa Dana --}}
            <div>
                <button type="button" @click="reksaDanaOpen = !reksaDanaOpen"
                    class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.reksa-dana.*') || request()->routeIs('user.analisa.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        Reksa Dana
                    </span>
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': reksaDanaOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="reksaDanaOpen" x-transition class="space-y-1 pl-3">
                    <a href="{{ route('user.reksa-dana.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.reksa-dana.index') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        Daftar Reksa Dana
                    </a>
                    <a href="{{ route('user.analisa.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.analisa.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Monitor Reksa Dana
                    </a>
                </div>
            </div>

            {{-- Unit Link --}}
            <div>
                <button type="button" @click="unitLinkOpen = !unitLinkOpen"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.unit-link.*') || request()->routeIs('user.unit-link-analisa.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        Unit Link
                    </span>
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': unitLinkOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="unitLinkOpen" x-transition class="space-y-1 pl-3">
                    <a href="{{ route('user.unit-link.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.unit-link.index') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        Daftar Unit Link
                    </a>
                    <a href="{{ route('user.unit-link-analisa.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.unit-link-analisa.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Analisa Unit Link
                    </a>
                </div>
            </div>

            {{-- Saham --}}
            <div>
                <button type="button" @click="sahamOpen = !sahamOpen"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.saham.*') || request()->routeIs('user.analisa-saham.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        Saham
                    </span>
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': sahamOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="sahamOpen" x-transition class="space-y-1 pl-3">
                    <a href="{{ route('user.saham.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.saham.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        Daftar Saham
                    </a>
                    <a href="{{ route('user.analisa-saham.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.analisa-saham.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Analisa Saham
                    </a>
                    <a href="{{ route('user.price-alerts.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.price-alerts.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        Alert Harga
                    </a>
                </div>
            </div>

            {{-- Obligasi --}}
            <div>
                <button type="button" @click="obligasiOpen = !obligasiOpen"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.obligasi.*') || request()->routeIs('user.analisa-obligasi.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Obligasi
                    </span>
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': obligasiOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="obligasiOpen" x-transition class="space-y-1 pl-3">
                    <a href="{{ route('user.obligasi.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.obligasi.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        Daftar Obligasi
                    </a>
                    <a href="{{ route('user.analisa-obligasi.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.analisa-obligasi.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Analisa Obligasi
                    </a>
                </div>
            </div>

            {{-- Manajer Investasi --}}
            <a href="{{ route("user.investment-managers.index") }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs("user.investment-managers.*") ? "bg-green-50 text-green-700 font-semibold" : "text-gray-600 hover:bg-gray-50 hover:text-gray-900" }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Manajer Investasi
            </a>

            {{-- Perencanaan Investasi --}}
            <a href="{{ route('user.perencanaan-investasi.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.perencanaan-investasi.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Perencanaan Investasi
            </a>

            {{-- Notifikasi --}}
            @php($__unread = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0)
            <a href="{{ route('user.notifications.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.notifications.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span>Notifikasi</span>
                @if ($__unread > 0)
                    <span class="ml-auto text-[10px] font-bold text-white bg-red-500 px-2 py-0.5 rounded-full">{{ $__unread > 99 ? '99+' : $__unread }}</span>
                @endif
            </a>

            {{-- Profile --}}
            <a href="{{ route('profile.edit') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('profile.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Profile
            </a>
        </nav>
    </aside>

    {{-- Sidebar Mobile --}}
    <aside x-show="sidebarOpen" x-cloak
           class="fixed inset-y-0 left-0 z-30 w-64 bg-white border-r border-gray-100 flex flex-col lg:hidden"
           x-transition:enter="transition-transform duration-300"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition-transform duration-300"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="-translate-x-full">
        <div class="h-16 flex items-center gap-3 px-5 border-b border-gray-100">
            <div class="w-9 h-9 rounded-lg grid place-items-center" style="background:linear-gradient(135deg,#16a34a,#22c55e)">IP</div>
            <div>
                <div class="font-bold text-sm text-gray-900">InvestaPremier</div>
                <div class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">WealthOS</div>
            </div>
        </div>
        <nav class="flex-1 py-4 px-3 space-y-1 text-sm overflow-y-auto">

            {{-- Dashboard --}}
            <a href="{{ route('user.dashboard') }}"
               @@click="sidebarOpen = false"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.dashboard') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>

            {{-- Layanan --}}
            <div>
                <button type="button" @click="layananOpen = !layananOpen"
                    class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('quiz.*') || request()->routeIs('member.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        Layanan
                    </span>
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': layananOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="layananOpen" x-transition class="space-y-1 pl-3">
                    <a href="{{ route('quiz.index') }}"
                       @@click="sidebarOpen = false"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('quiz.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        Profil Investasi
                    </a>
                    <a href="{{ route('member.create') }}"
                       @@click="sidebarOpen = false"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('member.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        Daftar Member
                    </a>
                </div>
            </div>

            {{-- Reksa Dana --}}
            <div>
                <button type="button" @click="reksaDanaOpen = !reksaDanaOpen"
                    class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.reksa-dana.*') || request()->routeIs('user.analisa.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        Reksa Dana
                    </span>
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': reksaDanaOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="reksaDanaOpen" x-transition class="space-y-1 pl-3">
                    <a href="{{ route('user.reksa-dana.index') }}"
                       @@click="sidebarOpen = false"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.reksa-dana.index') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        Daftar Reksa Dana
                    </a>
                    <a href="{{ route('user.analisa.index') }}"
                       @@click="sidebarOpen = false"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.analisa.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Monitor Reksa Dana
                    </a>
                </div>
            </div>

            {{-- Unit Link (mobile) --}}
            <div>
                <button type="button" @click="unitLinkOpen = !unitLinkOpen"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.unit-link.*') || request()->routeIs('user.unit-link-analisa.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        Unit Link
                    </span>
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': unitLinkOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="unitLinkOpen" x-transition class="space-y-1 pl-3">
                    <a href="{{ route('user.unit-link.index') }}"
                       @@click="sidebarOpen = false"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.unit-link.index') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        Daftar Unit Link
                    </a>
                    <a href="{{ route('user.unit-link-analisa.index') }}"
                       @@click="sidebarOpen = false"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.unit-link-analisa.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Analisa Unit Link
                    </a>
                </div>
            </div>

            {{-- Saham --}}
            <div>
                <button type="button" @click="sahamOpen = !sahamOpen"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.saham.*') || request()->routeIs('user.analisa-saham.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        Saham
                    </span>
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': sahamOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="sahamOpen" x-transition class="space-y-1 pl-3">
                    <a href="{{ route('user.saham.index') }}" @@click="sidebarOpen = false"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.saham.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        Daftar Saham
                    </a>
                    <a href="{{ route('user.analisa-saham.index') }}" @@click="sidebarOpen = false"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.analisa-saham.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Analisa Saham
                    </a>
                    <a href="{{ route('user.price-alerts.index') }}" @@click="sidebarOpen = false"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.price-alerts.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        Alert Harga
                    </a>
                </div>
            </div>

            {{-- Obligasi --}}
            <div>
                <button type="button" @click="obligasiOpen = !obligasiOpen"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.obligasi.*') || request()->routeIs('user.analisa-obligasi.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Obligasi
                    </span>
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': obligasiOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="obligasiOpen" x-transition class="space-y-1 pl-3">
                    <a href="{{ route('user.obligasi.index') }}" @@click="sidebarOpen = false"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.obligasi.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        Daftar Obligasi
                    </a>
                    <a href="{{ route('user.analisa-obligasi.index') }}" @@click="sidebarOpen = false"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.analisa-obligasi.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Analisa Obligasi
                    </a>
                </div>
            </div>

                {{-- Manajer Investasi --}}
            <a href="{{ route("user.investment-managers.index") }}"
               @@click="sidebarOpen = false"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs("user.investment-managers.*") ? "bg-white/10 font-semibold text-white" : "text-white/70 hover:bg-white/5 hover:text-white" }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Manajer Investasi
            </a>

            {{-- Perencanaan Investasi --}}
            <a href="{{ route('user.perencanaan-investasi.index') }}"
               @@click="sidebarOpen = false"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.perencanaan-investasi.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Perencanaan Investasi
            </a>

            {{-- Notifikasi (mobile) --}}
            @php($__unreadM = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0)
            <a href="{{ route('user.notifications.index') }}"
               @@click="sidebarOpen = false"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.notifications.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span>Notifikasi</span>
                @if ($__unreadM > 0)
                    <span class="ml-auto text-[10px] font-bold text-white bg-red-500 px-2 py-0.5 rounded-full">{{ $__unreadM > 99 ? '99+' : $__unreadM }}</span>
                @endif
            </a>

            {{-- Profile --}}
            <a href="{{ route('profile.edit') }}"
               @@click="sidebarOpen = false"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('profile.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Profile
            </a>
        </nav>
    </aside>

    {{-- Main area --}}
    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-16 bg-white border-b border-line flex items-center justify-between gap-4 px-4 lg:px-6 shrink-0">
            <button @@click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg text-gray-400 hover:text-gray-900 hover:bg-gray-100 transition">
                <svg x-show="!sidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="sidebarOpen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="flex items-center gap-2 ml-auto">
                {{-- Notifikasi Bell --}}
                <x-notification-bell />

                <div class="flex items-center gap-2 sm:gap-3 text-sm">
                    <div class="hidden sm:block text-right">
                        <div class="font-semibold text-gray-900 truncate max-w-[120px]">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-gray-400">Member</div>
                    </div>
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full text-white grid place-items-center text-xs font-bold uppercase shrink-0" style="background:linear-gradient(135deg,#16a34a,#22c55e)">{{ substr(Auth::user()->name, 0, 2) }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-1.5 px-2.5 py-2 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span class="hidden sm:inline">Logout</span>
                    </button>
                </form>
            </div>
        </header>
        <main class="flex-1 overflow-y-auto bg-[#f8fafc]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-6 py-4 sm:py-8">
                @yield('content')
            </div>
        </main>
    </div>
</div>
@endsection
