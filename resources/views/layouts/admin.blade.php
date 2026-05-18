@extends('layouts.app')

@section('body')
    <div x-data="{
        sidebarOpen: false,
        menuMasterOpen: {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.questions.*') || request()->routeIs('admin.members.*') || request()->routeIs('admin.score-classifications.*') ? 'true' : 'false' }},
        reksaDanaOpen: {{ request()->routeIs('admin.reksa-dana.*') || request()->routeIs('admin.analisa-rd.*') || request()->routeIs('admin.analisa.*') ? 'true' : 'false' }},
        pasarModalOpen: {{ request()->routeIs('admin.saham.*') || request()->routeIs('admin.analisa-saham.*') || request()->routeIs('admin.obligasi.*') || request()->routeIs('admin.analisa-obligasi.*') ? 'true' : 'false' }}
    }" class="flex h-screen overflow-hidden">
        {{-- Overlay mobile --}}
        <div x-show="sidebarOpen" x-cloak @@click="sidebarOpen = false"
            class="fixed inset-0 z-20 bg-black/40 lg:hidden"></div>

        {{-- Sidebar Desktop --}}
        <aside class="hidden lg:flex w-64 bg-primary text-white flex-col shrink-0">
            <div class="h-16 flex items-center gap-3 px-5 border-b border-white/10">
                <div class="w-9 h-9 rounded-lg bg-white/10 grid place-items-center font-bold text-sm">✦</div>
                <div>
                    <div class="font-bold text-sm">InvestaPremier</div>
                    <div class="text-[11px] text-white/60">Admin Panel</div>
                </div>
            </div>
            <nav class="flex-1 py-4 px-3 space-y-1 text-sm overflow-y-auto">

                {{-- Manajemen --}}
                <div>
                    <button type="button" @click="menuMasterOpen = !menuMasterOpen"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.questions.*') || request()->routeIs('admin.members.*') || request()->routeIs('admin.score-classifications.*') ? 'bg-white/10 font-semibold' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 7h18M3 12h18M3 17h18" />
                            </svg>
                            Manajemen
                        </span>
                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': menuMasterOpen }" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="menuMasterOpen" x-transition class="space-y-1 pl-3">
                        <a href="{{ route('admin.dashboard') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 font-semibold text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7" />
                            </svg>
                            Dashboard
                        </a>
                        <a href="{{ route('admin.score-classifications.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.score-classifications.*') ? 'bg-white/10 font-semibold text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Klasifikasi Skor
                        </a>
                        <a href="{{ route('admin.questions.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.questions.*') ? 'bg-white/10 font-semibold text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7" />
                            </svg>
                            Soal Kuis
                        </a>
                        <a href="{{ route('admin.members.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.members.*') ? 'bg-white/10 font-semibold text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3" />
                            </svg>
                            Pendaftaran Member
                        </a>
                    </div>
                </div>

                {{-- Reksa Dana --}}
                <div>
                    <button type="button" @click="reksaDanaOpen = !reksaDanaOpen"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.reksa-dana.*') || request()->routeIs('admin.analisa-rd.*') || request()->routeIs('admin.analisa.*') ? 'bg-white/10 font-semibold' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            Reksa Dana
                        </span>
                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': reksaDanaOpen }" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="reksaDanaOpen" x-transition class="space-y-1 pl-3">

                        <a href="{{ route('admin.reksa-dana.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.reksa-dana.*') ? 'bg-white/10 font-semibold text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            Monitor Reksa Dana FFS
                        </a>
                        <a href="{{ route('admin.analisa-rd.create') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.analisa-rd.*') ? 'bg-white/10 font-semibold text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Analisa Reksa Dana
                        </a>
                        <a href="{{ route('admin.analisa.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.analisa.*') ? 'bg-white/10 font-semibold text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Monitor Analisa Reksa Dana
                        </a>
                    </div>
                </div>

                {{-- Pasar Modal --}}
                <div>
                    <button type="button" @click="pasarModalOpen = !pasarModalOpen"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.saham.*') || request()->routeIs('admin.analisa-saham.*') || request()->routeIs('admin.obligasi.*') || request()->routeIs('admin.analisa-obligasi.*') ? 'bg-white/10 font-semibold' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            Pasar Modal
                        </span>
                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': pasarModalOpen }" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="pasarModalOpen" x-transition class="space-y-1 pl-3">
                        <a href="{{ route('admin.saham.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.saham.*') ? 'bg-white/10 font-semibold text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            Daftar Saham
                        </a>
                        <a href="{{ route('admin.analisa-saham.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.analisa-saham.*') ? 'bg-white/10 font-semibold text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Monitor Analisa Saham
                        </a>
                        <a href="{{ route('admin.obligasi.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.obligasi.*') ? 'bg-white/10 font-semibold text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Daftar Obligasi
                        </a>
                        <a href="{{ route('admin.analisa-obligasi.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.analisa-obligasi.*') ? 'bg-white/10 font-semibold text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Monitor Analisa Obligasi
                        </a>

                    </div>
                </div>

                {{-- Queue Monitor --}}
                <a href="/horizon" target="_blank"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-white/70 hover:bg-white/5 hover:text-white">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18" />
                    </svg>
                    Queue Monitor
                </a>
                <a href="{{ route('admin.daftar-reksa-dana.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.daftar-reksa-dana.*') ? 'bg-white/10 font-semibold text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Daftar Reksa Dana
                </a>
                <a href="{{ route('profile.edit') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('profile.*') ? 'bg-white/10 font-semibold' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Profile
                </a>
            </nav>
        </aside>

        {{-- Sidebar Mobile --}}
        <aside x-show="sidebarOpen" x-cloak
            class="fixed inset-y-0 left-0 z-30 w-64 bg-primary text-white flex flex-col lg:hidden"
            x-transition:enter="transition-transform duration-300" x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0" x-transition:leave="transition-transform duration-300"
            x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
            <div class="h-16 flex items-center gap-3 px-5 border-b border-white/10">
                <div class="w-9 h-9 rounded-lg bg-white/10 grid place-items-center font-bold text-sm">✦</div>
                <div>
                    <div class="font-bold text-sm">InvestaPremier</div>
                    <div class="text-[11px] text-white/60">Admin Panel</div>
                </div>
            </div>
            <nav class="flex-1 py-4 px-3 space-y-1 text-sm overflow-y-auto">

                {{-- Manajemen --}}
                <div>
                    <button type="button" @click="menuMasterOpen = !menuMasterOpen"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.questions.*') || request()->routeIs('admin.members.*') || request()->routeIs('admin.score-classifications.*') ? 'bg-white/10 font-semibold' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 7h18M3 12h18M3 17h18" />
                            </svg>
                            Manajemen
                        </span>
                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': menuMasterOpen }" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="menuMasterOpen" x-transition class="space-y-1 pl-3">
                        <a href="{{ route('admin.dashboard') }}" @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 font-semibold text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7" />
                            </svg>
                            Dashboard
                        </a>
                        <a href="{{ route('admin.score-classifications.index') }}"
                            @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.score-classifications.*') ? 'bg-white/10 font-semibold text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Klasifikasi Skor
                        </a>
                        <a href="{{ route('admin.questions.index') }}" @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.questions.*') ? 'bg-white/10 font-semibold text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7" />
                            </svg>
                            Soal Kuis
                        </a>
                        <a href="{{ route('admin.members.index') }}" @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.members.*') ? 'bg-white/10 font-semibold text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3" />
                            </svg>
                            Pendaftaran Member
                        </a>
                    </div>
                </div>

                {{-- Reksa Dana --}}
                <div>
                    <button type="button" @click="reksaDanaOpen = !reksaDanaOpen"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.reksa-dana.*') || request()->routeIs('admin.analisa-rd.*') || request()->routeIs('admin.analisa.*') ? 'bg-white/10 font-semibold' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            Reksa Dana
                        </span>
                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': reksaDanaOpen }" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="reksaDanaOpen" x-transition class="space-y-1 pl-3">

                        <a href="{{ route('admin.reksa-dana.index') }}" @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.reksa-dana.*') ? 'bg-white/10 font-semibold text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            Monitor Reksa Dana FFS
                        </a>
                        <a href="{{ route('admin.analisa-rd.create') }}" @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.analisa-rd.*') ? 'bg-white/10 font-semibold text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Analisa Reksa Dana
                        </a>
                        <a href="{{ route('admin.analisa.index') }}" @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.analisa.*') ? 'bg-white/10 font-semibold text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Monitor Analisa Reksa Dana
                        </a>
                    </div>
                </div>

                {{-- Pasar Modal --}}
                <div>
                    <button type="button" @click="pasarModalOpen = !pasarModalOpen"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.saham.*') || request()->routeIs('admin.analisa-saham.*') || request()->routeIs('admin.obligasi.*') || request()->routeIs('admin.analisa-obligasi.*') ? 'bg-white/10 font-semibold' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            Pasar Modal
                        </span>
                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': pasarModalOpen }" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="pasarModalOpen" x-transition class="space-y-1 pl-3">
                        <a href="{{ route('admin.saham.index') }}" @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.saham.*') ? 'bg-white/10 font-semibold text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            Daftar Saham
                        </a>
                        <a href="{{ route('admin.analisa-saham.index') }}"
                            @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.analisa-saham.*') ? 'bg-white/10 font-semibold text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Monitor Analisa Saham
                        </a>
                        <a href="{{ route('admin.obligasi.index') }}" @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.obligasi.*') ? 'bg-white/10 font-semibold text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Daftar Obligasi
                        </a>
                        <a href="{{ route('admin.analisa-obligasi.index') }}"
                            @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.analisa-obligasi.*') ? 'bg-white/10 font-semibold text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Monitor Analisa Obligasi
                        </a>
                    </div>
                </div>

                {{-- Queue Monitor --}}
                <a href="/horizon" target="_blank"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-white/70 hover:bg-white/5 hover:text-white">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18" />
                    </svg>
                    Queue Monitor
                </a>
                <a href="{{ route('admin.daftar-reksa-dana.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.daftar-reksa-dana.*') ? 'bg-white/10 font-semibold text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Daftar Reksa Dana
                </a>
                <a href="{{ route('profile.edit') }}" @@click="sidebarOpen = false"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('profile.*') ? 'bg-white/10 font-semibold' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Profile
                </a>
            </nav>
        </aside>

        {{-- Main area --}}
        <div class="flex-1 flex flex-col min-w-0">
            <header
                class="h-16 bg-white border-b border-line flex items-center justify-between gap-4 px-4 lg:px-6 shrink-0">
                <button @@click="sidebarOpen = !sidebarOpen"
                    class="lg:hidden p-2 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition">
                    <svg x-show="!sidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="sidebarOpen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="flex items-center gap-2 ml-auto">
                    <div class="flex items-center gap-2 sm:gap-3 text-sm">
                        <div class="hidden sm:block text-right">
                            <div class="font-medium text-primary truncate max-w-[120px]">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-muted">Admin</div>
                        </div>
                        <div
                            class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-primary text-white grid place-items-center text-xs font-bold uppercase shrink-0">
                            {{ substr(Auth::user()->name, 0, 2) }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="flex items-center gap-1.5 px-2.5 py-2 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
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
