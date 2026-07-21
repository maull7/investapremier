@extends('layouts.app')

@section('body')
    <div x-data="{
        sidebarOpen: false,
        sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
        profileOpen: false,
        searchOpen: false,
        layananOpen: {{ request()->routeIs('quiz.*') || request()->routeIs('member.*') ? 'true' : 'false' }},
        reksaDanaOpen: {{ request()->routeIs('user.reksa-dana.*') || request()->routeIs('user.analisa.*') ? 'true' : 'false' }},
        unitLinkOpen: {{ request()->routeIs('user.unit-link.*') || request()->routeIs('user.unit-link-analisa.*') ? 'true' : 'false' }},
        sahamOpen: {{ request()->routeIs('user.saham.*') || request()->routeIs('user.analisa-saham.*') ? 'true' : 'false' }},
        obligasiOpen: {{ request()->routeIs('user.obligasi.*') || request()->routeIs('user.analisa-obligasi.*') ? 'true' : 'false' }},
        now: new Date(),
        init() {
            setInterval(() => { this.now = new Date() }, 1000)
        },
        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed
            localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed)
        },
        get timeStr() {
            return this.now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
        },
        get dateStr() {
            return this.now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
        },
        get greeting() {
            const h = this.now.getHours()
            if (h < 12) return 'Selamat Pagi'
            if (h < 15) return 'Selamat Siang'
            if (h < 18) return 'Selamat Sore'
            return 'Selamat Malam'
        }
    }" class="flex h-screen overflow-hidden" :class="{ 'sidebar-collapsed': sidebarCollapsed }">

        {{-- Overlay mobile --}}
        <div x-show="sidebarOpen" x-cloak @@click="sidebarOpen = false"
            class="fixed inset-0 z-20 bg-black/40 lg:hidden"></div>

        {{-- Sidebar Desktop --}}
        <aside class="hidden lg:flex w-64 sidebar-dark flex-col shrink-0 transition-all duration-300"
            :class="sidebarCollapsed ? 'w-[68px]' : 'w-64'">
            {{-- Logo --}}
            <div class="h-16 flex items-center border-b border-r border-slate-200/70 bg-cardBg-bg px-5 transition-all duration-300 shadow-sm"
                :class="sidebarCollapsed ? 'justify-center px-2' : 'justify-between'">

                <div class="flex items-center gap-3 min-w-0 *:transition-all duration-300">
                    <!-- Logo -->
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-teal-500 to-teal-700 shadow-md shadow-teal-500/20">
                        <span class="text-sm font-bold tracking-wide text-white">
                            IP
                        </span>
                    </div>

                    <!-- Brand -->
                    <div x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms class="min-w-0">
                        <h1 class="truncate text-[15px] font-semibold text-slate-800">
                            Investa<span class="text-teal-700">Premier</span>
                        </h1>

                        <p class="text-[11px] font-medium uppercase tracking-[0.25em] text-slate-400">
                            WealthOS
                        </p>
                    </div>
                </div>
            </div>

            {{-- User Profile --}}
            <div class="px-3 pt-4 pb-2 border-b border-white/5" :class="sidebarCollapsed ? 'px-0 flex justify-center' : ''">

                {{-- Quick profile dropdown --}}
                <div x-show="profileOpen && !sidebarCollapsed" x-cloak
                    @@click.outside="profileOpen = false"
                    class="mt-1 mx-1 rounded-xl border border-white/10 bg-primary-light shadow-xl overflow-hidden">
                    <a href="{{ route('profile.edit') }}"
                        class="flex items-center gap-3 px-3 py-2.5 text-sm text-white/70 hover:bg-white/5 hover:text-white transition">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Profile
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center gap-3 px-3 py-2.5 text-sm text-white/70 hover:bg-white/5 hover:text-white transition">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 py-3 px-3 space-y-1 overflow-y-auto overflow-x-hidden scrollbar-thin font-normal"
                :class="sidebarCollapsed ? 'px-1.5' : ''">
                @php
                    $navItems = [
                        [
                            'route' => 'user.dashboard',
                            'label' => 'Dashboard',
                            'icon' =>
                                'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                            'match' => 'user.dashboard',
                            'advisor' => false,
                        ],
                        [
                            'route' => 'user.advisor.dashboard',
                            'label' => 'Advisor Dashboard',
                            'icon' =>
                                'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10',
                            'match' => 'user.advisor.dashboard',
                            'advisor' => true,
                        ],
                    ];
                @endphp
                @foreach ($navItems as $item)
                    @if (($item['advisor'] && auth()->user()->isAdvisor()) || (!$item['advisor'] && !auth()->user()->isAdvisor()))
                        <a href="{{ route($item['route']) }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-150 {{ request()->routeIs($item['match']) ? 'nav-item-active' : 'nav-item' }}"
                            :class="sidebarCollapsed ? 'justify-center px-0 py-3' : ''">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="{{ $item['icon'] }}" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="sidebar-label">{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach

                {{-- Layanan --}}
                <div>
                    <button type="button" @click="layananOpen = !layananOpen"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg transition-all duration-150 {{ request()->routeIs('quiz.*') || request()->routeIs('member.*') ? 'nav-item-active' : 'nav-item' }}"
                        :class="sidebarCollapsed ? 'justify-center px-0 py-3' : ''">
                        <span class="flex items-center gap-3 min-w-0" :class="sidebarCollapsed ? '' : ''">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="sidebar-label">Layanan</span>
                        </span>
                        <svg x-show="!sidebarCollapsed" class="w-4 h-4 transition-transform shrink-0"
                            :class="{ 'rotate-180': layananOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="layananOpen && !sidebarCollapsed" x-transition class="space-y-0.5 pl-9 mt-0.5">
                        <a href="{{ route('quiz.index') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all duration-150 {{ request()->routeIs('quiz.*') ? 'nav-item-active' : 'nav-item' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            Profil Investasi
                        </a>
                        <a href="{{ route('member.create') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all duration-150 {{ request()->routeIs('member.*') ? 'nav-item-active' : 'nav-item' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            Daftar Member
                        </a>
                    </div>
                </div>

                {{-- Reksa Dana --}}
                <div>
                    <button type="button" @click="reksaDanaOpen = !reksaDanaOpen"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg transition-all duration-150 {{ request()->routeIs('user.reksa-dana.*') || request()->routeIs('user.analisa.*') ? 'nav-item-active' : 'nav-item' }}"
                        :class="sidebarCollapsed ? 'justify-center px-0 py-3' : ''">
                        <span class="flex items-center gap-3 min-w-0">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="sidebar-label">Reksa Dana</span>
                        </span>
                        <svg x-show="!sidebarCollapsed" class="w-4 h-4 transition-transform shrink-0"
                            :class="{ 'rotate-180': reksaDanaOpen }" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="reksaDanaOpen && !sidebarCollapsed" x-transition class="space-y-0.5 pl-9 mt-0.5">
                        <a href="{{ route('user.reksa-dana.index') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('user.reksa-dana.index') ? 'nav-item-active' : 'nav-item' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            Daftar Reksa Dana
                        </a>
                        <a href="{{ route('user.analisa.index') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('user.analisa.*') ? 'nav-item-active' : 'nav-item' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Monitor Reksa Dana
                        </a>
                    </div>
                </div>

                {{-- Unit Link --}}
                <div>
                    <button type="button" @click="unitLinkOpen = !unitLinkOpen"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg transition-all duration-150 {{ request()->routeIs('user.unit-link.*') || request()->routeIs('user.unit-link-analisa.*') ? 'nav-item-active' : 'nav-item' }}"
                        :class="sidebarCollapsed ? 'justify-center px-0 py-3' : ''">
                        <span class="flex items-center gap-3 min-w-0">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="sidebar-label">Unit Link</span>
                        </span>
                        <svg x-show="!sidebarCollapsed" class="w-4 h-4 transition-transform shrink-0"
                            :class="{ 'rotate-180': unitLinkOpen }" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="unitLinkOpen && !sidebarCollapsed" x-transition class="space-y-0.5 pl-9 mt-0.5">
                        <a href="{{ route('user.unit-link.index') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('user.unit-link.index') ? 'nav-item-active' : 'nav-item' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            Daftar Unit Link
                        </a>
                        <a href="{{ route('user.unit-link-analisa.index') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('user.unit-link-analisa.*') ? 'nav-item-active' : 'nav-item' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Analisa Unit Link
                        </a>
                    </div>
                </div>

                {{-- Saham --}}
                <div>
                    <button type="button" @click="sahamOpen = !sahamOpen"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg transition-all duration-150 {{ request()->routeIs('user.saham.*') || request()->routeIs('user.analisa-saham.*') ? 'nav-item-active' : 'nav-item' }}"
                        :class="sidebarCollapsed ? 'justify-center px-0 py-3' : ''">
                        <span class="flex items-center gap-3 min-w-0">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="sidebar-label">Saham</span>
                        </span>
                        <svg x-show="!sidebarCollapsed" class="w-4 h-4 transition-transform shrink-0"
                            :class="{ 'rotate-180': sahamOpen }" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="sahamOpen && !sidebarCollapsed" x-transition class="space-y-0.5 pl-9 mt-0.5">
                        <a href="{{ route('user.saham.index') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('user.saham.*') ? 'nav-item-active' : 'nav-item' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            Daftar Saham
                        </a>
                        <a href="{{ route('user.analisa-saham.index') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('user.analisa-saham.*') ? 'nav-item-active' : 'nav-item' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Analisa Saham
                        </a>
                        <a href="{{ route('user.price-alerts.index') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('user.price-alerts.*') ? 'nav-item-active' : 'nav-item' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            Alert Harga
                        </a>
                    </div>
                </div>

                {{-- Obligasi --}}
                <div>
                    <button type="button" @click="obligasiOpen = !obligasiOpen"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg transition-all duration-150 {{ request()->routeIs('user.obligasi.*') || request()->routeIs('user.analisa-obligasi.*') ? 'nav-item-active' : 'nav-item' }}"
                        :class="sidebarCollapsed ? 'justify-center px-0 py-3' : ''">
                        <span class="flex items-center gap-3 min-w-0">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="sidebar-label">Obligasi</span>
                        </span>
                        <svg x-show="!sidebarCollapsed" class="w-4 h-4 transition-transform shrink-0"
                            :class="{ 'rotate-180': obligasiOpen }" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="obligasiOpen && !sidebarCollapsed" x-transition class="space-y-0.5 pl-9 mt-0.5">
                        <a href="{{ route('user.obligasi.index') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('user.obligasi.*') ? 'nav-item-active' : 'nav-item' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            Daftar Obligasi
                        </a>
                        <a href="{{ route('user.analisa-obligasi.index') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('user.analisa-obligasi.*') ? 'nav-item-active' : 'nav-item' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Analisa Obligasi
                        </a>
                    </div>
                </div>

                {{-- Single menu items --}}
                @php
                    $singles = [
                        [
                            'route' => 'user.investment-managers.index',
                            'label' => 'Manajer Investasi',
                            'icon' =>
                                'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                            'match' => 'user.investment-managers.*',
                        ],
                        [
                            'route' => 'user.perencanaan-investasi.index',
                            'label' => 'Perencanaan Investasi',
                            'icon' =>
                                'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                            'match' => 'user.perencanaan-investasi.*',
                        ],
                        [
                            'route' => 'user.chatbot.index',
                            'label' => 'AI Chatbot',
                            'icon' =>
                                'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z',
                            'match' => 'user.chatbot.*',
                        ],
                    ];
                    if (auth()->user()->isAdvisor()) {
                        $singles[] = [
                            'route' => 'user.clients.index',
                            'label' => 'Daftar Klien',
                            'icon' =>
                                'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                            'match' => 'user.clients.*',
                        ];
                    } else {
                        $singles[] = [
                            'route' => 'user.clients.requests.index',
                            'label' => 'Koneksi Advisor',
                            'icon' =>
                                'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                            'match' => 'user.clients.requests.*',
                        ];
                    }
                    $singles[] = [
                        'route' => 'user.notifications.index',
                        'label' => 'Notifikasi',
                        'icon' =>
                            'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
                        'match' => 'user.notifications.*',
                        'badge' => auth()->user()->unreadNotifications()->count(),
                    ];
                    $singles[] = [
                        'route' => 'profile.edit',
                        'label' => 'Profile',
                        'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                        'match' => 'profile.*',
                    ];
                @endphp
                @foreach ($singles as $item)
                    <a href="{{ route($item['route']) }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-150 {{ request()->routeIs($item['match'] ?? $item['route']) ? 'nav-item-active' : 'nav-item' }}"
                        :class="sidebarCollapsed ? 'justify-center px-0 py-3' : ''">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="{{ $item['icon'] }}" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="sidebar-label flex-1">{{ $item['label'] }}</span>
                        @if (!empty($item['badge']) && $item['badge'] > 0)
                            <span x-show="!sidebarCollapsed"
                                class="ml-auto text-[10px] font-bold text-white bg-red-500 px-2 py-0.5 rounded-full">{{ $item['badge'] > 99 ? '99+' : $item['badge'] }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>

            {{-- Collapse toggle --}}
            <div class="px-3 py-3 border-t border-white/80 shrink-0 bg-cardBg-bg shadow-md">
                <button @@click="toggleSidebar()"
                    class="w-full shadow-sm border border-accent-teal/50 flex items-center justify-center gap-3 px-3 py-2.5 rounded-lg nav-item transition-all duration-150"
                    :class="sidebarCollapsed ? 'px-0' : ''">
                    <svg class="w-5 h-5 shrink-0 transition-transform duration-300" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" :class="sidebarCollapsed ? 'rotate-180' : ''">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 17l-5-5 5-5m7 10l-5-5 5-5" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="sidebar-label text-sm">Sembunyikan</span>
                </button>
            </div>
        </aside>

        {{-- Sidebar Mobile --}}
        <aside x-show="sidebarOpen" x-cloak class="fixed inset-y-0 left-0 z-30 w-64 sidebar-dark flex flex-col lg:hidden"
            x-transition:enter="transition-transform duration-300" x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0" x-transition:leave="transition-transform duration-300"
            x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
            <div class="h-16 flex items-center gap-3 px-5 border-b border-white/10">
                <div class="w-9 h-9 rounded-lg grid place-items-center shrink-0"
                    style="background:linear-gradient(135deg,#16a34a,#22c55e)">
                    <span class="text-white font-bold text-sm">IP</span>
                </div>
                <div>
                    <div class="font-bold text-sm text-accent-teal">InvestaPremier</div>
                    <div class="text-[10px] text-accent-teal/45 uppercase tracking-wider font-medium">WealthOS</div>
                </div>
            </div>
            <div class="flex items-center gap-3 px-5 py-3 border-b border-white/5">
                <div class="w-9 h-9 rounded-full text-accent-teal grid place-items-center text-xs font-bold uppercase shrink-0"
                    style="background:linear-gradient(135deg,#17D469,#14b8a6)">
                    {{ substr(Auth::user()->name, 0, 2) }}</div>
                <div class="min-w-0">
                    <div class="text-sm font-semibold text-white truncate">{{ Auth::user()->name }}</div>
                    <div class="text-[11px] text-white/40">{{ Auth::user()->role }}</div>
                </div>
            </div>
            <nav class="flex-1 py-3 px-3 space-y-1 overflow-y-auto text-sm">
                @php
                    $mobNav = [
                        [
                            'route' => 'user.dashboard',
                            'label' => 'Dashboard',
                            'icon' =>
                                'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                            'match' => 'user.dashboard',
                        ],
                        [
                            'route' => 'user.perencanaan-investasi.index',
                            'label' => 'Perencanaan Investasi',
                            'icon' =>
                                'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                            'match' => 'user.perencanaan-investasi.*',
                        ],
                        [
                            'route' => 'user.chatbot.index',
                            'label' => 'AI Chatbot',
                            'icon' =>
                                'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z',
                            'match' => 'user.chatbot.*',
                        ],
                        [
                            'route' => 'user.notifications.index',
                            'label' => 'Notifikasi',
                            'icon' =>
                                'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
                            'match' => 'user.notifications.*',
                        ],
                        [
                            'route' => 'profile.edit',
                            'label' => 'Profile',
                            'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                            'match' => 'profile.*',
                        ],
                    ];
                @endphp
                @foreach ($mobNav as $item)
                    <a href="{{ route($item['route']) }}" @@click="sidebarOpen = false"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs($item['match']) ? 'nav-item-active' : 'nav-item' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="{{ $item['icon'] }}" />
                        </svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
                <hr class="border-white/10 my-2">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg nav-item">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Logout
                    </button>
                </form>
            </nav>
        </aside>

        {{-- Main area --}}
        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-16 bg-cardBg-bg border-b border-line flex items-center gap-4 px-4 lg:px-6 shrink-0">

                {{-- Left: Hamburger + Breadcrumb --}}
                <div class="flex items-center gap-3 min-w-0 shadow-sm p-2 rounded-lg bg-white border border-black/20">
                    <button @@click="sidebarOpen = !sidebarOpen"
                        class="lg:hidden p-2 rounded-lg text-gray-400 hover:text-gray-900 hover:bg-gray-100 transition">
                        <svg x-show="!sidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg x-show="sidebarOpen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <nav class="hidden sm:flex items-center gap-1.5 text-sm text-gray-400 min-w-0">
                        <a href="{{ route('user.dashboard') }}" class="hover:text-gray-700 transition shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                        </a>
                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <span class="font-medium text-gray-700 truncate">@yield('title', 'Dashboard')</span>
                    </nav>
                </div>

                {{-- Right: Search, Clock, Notif, Profile --}}
                <div class="flex items-center gap-2 ml-auto ">

                    {{-- Live Clock --}}
                    <div
                        class="hidden md:flex items-center gap-2 p-2 rounded-lg bg-white border border-black/10 shadow-sm text-xs">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-gray-500 font-medium" x-text="dateStr"></span>
                        <span class="text-gray-700 font-semibold tabular-nums" x-text="timeStr"></span>
                    </div>

                    <div class="hidden lg:flex items-center gap-2 text-xs text-muted font-semibold px-2 py-1.5">
                        <span x-text="greeting"></span>
                    </div>

                    {{-- Quick Search --}}
                    <div class="relative" x-data="{
                        qOpen: false,
                        q: '',
                        idx: -1,
                        menus: [
                            { label: 'Dashboard', route: '{{ route('user.dashboard') }}', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
                            { label: 'Profil Investasi', route: '{{ route('quiz.index') }}', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01' },
                            { label: 'Daftar Member', route: '{{ route('member.create') }}', icon: 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z' },
                            { label: 'Daftar Reksa Dana', route: '{{ route('user.reksa-dana.index') }}', icon: 'M4 6h16M4 10h16M4 14h16M4 18h16' },
                            { label: 'Monitor Reksa Dana', route: '{{ route('user.analisa.index') }}', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
                            { label: 'Daftar Unit Link', route: '{{ route('user.unit-link.index') }}', icon: 'M4 6h16M4 10h16M4 14h16M4 18h16' },
                            { label: 'Analisa Unit Link', route: '{{ route('user.unit-link-analisa.index') }}', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
                            { label: 'Daftar Saham', route: '{{ route('user.saham.index') }}', icon: 'M4 6h16M4 10h16M4 14h16M4 18h16' },
                            { label: 'Analisa Saham', route: '{{ route('user.analisa-saham.index') }}', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
                            { label: 'Alert Harga', route: '{{ route('user.price-alerts.index') }}', icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' },
                            { label: 'Daftar Obligasi', route: '{{ route('user.obligasi.index') }}', icon: 'M4 6h16M4 10h16M4 14h16M4 18h16' },
                            { label: 'Analisa Obligasi', route: '{{ route('user.analisa-obligasi.index') }}', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
                            { label: 'Manajer Investasi', route: '{{ route('user.investment-managers.index') }}', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' },
                            { label: 'Perencanaan Investasi', route: '{{ route('user.perencanaan-investasi.index') }}', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
                            { label: 'AI Chatbot', route: '{{ route('user.chatbot.index') }}', icon: 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z' },
                            { label: 'Notifikasi', route: '{{ route('user.notifications.index') }}', icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' },
                            { label: 'Profile', route: '{{ route('profile.edit') }}', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
                        ],
                        get filtered() {
                            if (!this.q) return []
                            const s = this.q.toLowerCase()
                            return this.menus.filter(m => m.label.toLowerCase().includes(s)).slice(0, 8)
                        },
                        go(route) {
                            window.location.href = route
                        },
                        keydown(e) {
                            const f = this.filtered
                            if (!f.length) return
                            if (e.key === 'ArrowDown') {
                                e.preventDefault();
                                this.idx = Math.min(this.idx + 1, f.length - 1)
                            } else if (e.key === 'ArrowUp') {
                                e.preventDefault();
                                this.idx = Math.max(this.idx - 1, 0)
                            } else if (e.key === 'Enter' && this.idx >= 0) { this.go(f[this.idx].route) } else { this.idx = -1 }
                        }
                    }"
                        @@keydown.escape.window="qOpen = false; q = ''"
                        @@click.outside="qOpen = false; q = ''">
                        <button @@click="qOpen = !qOpen; q = ''; idx = -1"
                            class="p-2 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                        <div x-show="qOpen" x-cloak @@keydown="keydown"
                            class="absolute right-0 top-full mt-2 w-72 bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden z-50">
                            <div class="flex items-center gap-2 px-4 py-3 border-b border-gray-100">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input type="text" x-model="q" @input="idx = -1" placeholder="Cari fitur..."
                                    class="w-full border-0 outline-none text-sm text-gray-700 placeholder-gray-400 bg-transparent">
                                <kbd
                                    class="hidden sm:inline text-[10px] text-gray-300 border border-gray-200 rounded px-1.5 py-0.5">ESC</kbd>
                            </div>
                            <template x-if="!q">
                                <div class="p-2 text-xs text-gray-400 text-center py-6">
                                    Ketik nama fitur untuk mencari
                                </div>
                            </template>
                            <template x-if="q && !filtered.length">
                                <div class="p-2 text-xs text-gray-400 text-center py-6">
                                    Tidak ditemukan
                                </div>
                            </template>
                            <template x-if="filtered.length">
                                <div class="py-1 max-h-64 overflow-y-auto">
                                    <template x-for="(m, i) in filtered" :key="m.label">
                                        <a :href="m.route" @@click="qOpen = false; q = ''"
                                            class="flex items-center gap-3 px-4 py-2.5 text-sm transition"
                                            :class="i === idx ? 'bg-green-50 text-green-700 font-medium' :
                                                'text-gray-600 hover:bg-gray-50'">
                                            <svg class="w-4 h-4 shrink-0 text-gray-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    :d="m.icon" />
                                            </svg>
                                            <span x-text="m.label"></span>
                                        </a>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Notifikasi Bell --}}
                    <x-notification-bell />

                    {{-- Profile Dropdown --}}
                    <div class="relative" x-data="{ pOpen: false }"
                        @@keydown.escape.window="pOpen = false">
                        <button @@click="pOpen = !pOpen"
                            @@click.outside="pOpen = false"
                            class="flex items-center gap-2 pl-3 pr-2 py-1.5 rounded-lg hover:bg-gray-100 transition">
                            <div class="hidden sm:block text-right">
                                <div class="text-sm font-semibold text-gray-900 truncate max-w-[120px] leading-tight">
                                    {{ Auth::user()->name }}</div>
                                <div class="text-[11px] text-gray-400">{{ Auth::user()->role }}</div>
                            </div>
                            <div class="w-8 h-8 rounded-full text-white grid place-items-center text-xs font-bold uppercase shrink-0"
                                style="background:linear-gradient(135deg,#17D469,#14b8a6)">
                                {{ substr(Auth::user()->name, 0, 2) }}</div>
                            <svg class="w-3.5 h-3.5 text-gray-400 shrink-0 transition-transform"
                                :class="{ 'rotate-180': pOpen }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="pOpen" x-cloak
                            class="absolute right-0 top-full mt-2 w-56 bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden z-50"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <div class="text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-gray-400">{{ Auth::user()->email }}</div>
                            </div>
                            <div class="p-1.5">
                                <a href="{{ route('profile.edit') }}"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Profile
                                </a>
                                <a href="{{ route('user.notifications.index') }}"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    Notifikasi
                                </a>
                            </div>
                            <div class="border-t border-gray-100 p-1.5">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
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
