@extends('layouts.app')

@section('body')
    <div x-data="{
        sidebarOpen: false,
        menuMasterOpen: {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.questions.*') || request()->routeIs('admin.members.*') || request()->routeIs('admin.score-classifications.*') ? 'true' : 'false' }},
        reksaDanaOpen: {{ request()->routeIs('admin.reksa-dana.*') || request()->routeIs('admin.analisa-rd.*') || request()->routeIs('admin.analisa.*') ? 'true' : 'false' }},
        unitLinkOpen: {{ request()->routeIs('admin.unit-link.*') || request()->routeIs('admin.unit-link-ffs.*') || request()->routeIs('admin.analisa-ul.*') || request()->routeIs('admin.unit-link-analisa.*') ? 'true' : 'false' }},
        sahamOpen: {{ request()->routeIs('admin.saham.*') || request()->routeIs('admin.analisa-saham.*') ? 'true' : 'false' }},
        obligasiOpen: {{ request()->routeIs('admin.obligasi.*') || request()->routeIs('admin.analisa-obligasi.*') ? 'true' : 'false' }},
        menuAiPrompts: {{ request()->routeIs('admin.ai-prompts.*') ? 'true' : 'false' }}
    }" class="flex h-screen overflow-hidden">
        {{-- Overlay mobile --}}
        <div x-show="sidebarOpen" x-cloak @@click="sidebarOpen = false"
            class="fixed inset-0 z-20 bg-black/40 lg:hidden"></div>

        {{-- Sidebar Desktop --}}
        <aside class="hidden lg:flex w-64 bg-white border-r border-gray-100 flex-col shrink-0 shadow-sm">
            <div class="h-16 flex items-center gap-3 px-5 border-b border-gray-100">
                <div class="w-9 h-9 rounded-lg grid place-items-center" style="background:linear-gradient(135deg,#16a34a,#22c55e)">IP</div>
                <div>
                    <div class="font-bold text-sm text-gray-900">InvestaPremier</div>
                    <div class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Admin Panel</div>
                </div>
            </div>
            <nav class="flex-1 py-4 px-3 space-y-1 text-sm overflow-y-auto">

                {{-- Manajemen --}}
                <div>
                    <button type="button" @click="menuMasterOpen = !menuMasterOpen"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.questions.*') || request()->routeIs('admin.members.*') || request()->routeIs('admin.score-classifications.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
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
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7" />
                            </svg>
                            Dashboard
                        </a>
                        <a href="{{ route('admin.score-classifications.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.score-classifications.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Klasifikasi Skor
                        </a>
                        <a href="{{ route('admin.questions.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.questions.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7" />
                            </svg>
                            Soal Kuis
                        </a>
                        <a href="{{ route('admin.members.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.members.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
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
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.reksa-dana.*') || request()->routeIs('admin.analisa-rd.*') || request()->routeIs('admin.analisa.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
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
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.reksa-dana.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            Monitor Reksa Dana FFS
                        </a>
                        <a href="{{ route('admin.analisa-rd.create') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.analisa-rd.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Analisa Reksa Dana
                        </a>
                        <a href="{{ route('admin.analisa.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.analisa.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Monitor Analisa Reksa Dana
                        </a>
                    </div>
                </div>

                {{-- Unit Link --}}
                <div>
                    <button type="button" @click="unitLinkOpen = !unitLinkOpen"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.unit-link.*') || request()->routeIs('admin.unit-link-ffs.*') || request()->routeIs('admin.analisa-ul.*') || request()->routeIs('admin.unit-link-analisa.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            Unit Link
                        </span>
                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': unitLinkOpen }" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="unitLinkOpen" x-transition class="space-y-1 pl-3">
                        <a href="{{ route('admin.unit-link.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.unit-link.index') || request()->routeIs('admin.unit-link.create') || request()->routeIs('admin.unit-link.edit') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            Daftar Unit Link
                        </a>
                        <a href="{{ route('admin.unit-link-ffs.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.unit-link-ffs.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            Monitor Unit Link FFS
                        </a>
                        <a href="{{ route('admin.analisa-ul.create') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.analisa-ul.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Analisa Unit Link
                        </a>
                        <a href="{{ route('admin.unit-link-analisa.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.unit-link-analisa.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Monitor Analisa Unit Link
                        </a>
                    </div>
                </div>

                {{-- Saham --}}
                <div>
                    <button type="button" @click="sahamOpen = !sahamOpen"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.saham.*') || request()->routeIs('admin.analisa-saham.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            Saham
                        </span>
                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': sahamOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="sahamOpen" x-transition class="space-y-1 pl-3">
                        <a href="{{ route('admin.saham.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.saham.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            Daftar Saham
                        </a>
                        <a href="{{ route('admin.analisa-saham.create') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.analisa-saham.create') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Analisa Saham
                        </a>
                        <a href="{{ route('admin.analisa-saham.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.analisa-saham.index') || request()->routeIs('admin.analisa-saham.show') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            Monitor Analisa Saham
                        </a>
                    </div>
                </div>

                {{-- Obligasi --}}
                <div>
                    <button type="button" @click="obligasiOpen = !obligasiOpen"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.obligasi.*') || request()->routeIs('admin.analisa-obligasi.*') || request()->routeIs('admin.rating-obligasi.*') || request()->routeIs('admin.ytm-normal-curve.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Obligasi
                        </span>
                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': obligasiOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="obligasiOpen" x-transition class="space-y-1 pl-3">
                        <a href="{{ route('admin.obligasi.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.obligasi.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            Daftar Obligasi
                        </a>
                        <a href="{{ route('admin.rating-obligasi.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.rating-obligasi.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            Rating Obligasi
                        </a>
                        <a href="{{ route('admin.ytm-normal-curve.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.ytm-normal-curve.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                            YTM Normal Curve
                        </a>
                        <a href="{{ route('admin.analisa-obligasi.create') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.analisa-obligasi.create') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Analisa Obligasi
                        </a>
                        <a href="{{ route('admin.analisa-obligasi.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.analisa-obligasi.index') || request()->routeIs('admin.analisa-obligasi.show') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            Monitor Analisa Obligasi
                        </a>
                    </div>
                </div>

                {{-- Manajer Investasi --}}
                <a href="{{ route("admin.investment-managers.index") }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs("admin.investment-managers.*") ? "bg-green-50 text-green-700 font-semibold" : "text-gray-600 hover:bg-gray-50 hover:text-gray-900" }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Manajer Investasi
                </a>

                {{-- Queue Monitor --}}
                <a href="/horizon" target="_blank"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18" />
                    </svg>
                    Queue Monitor
                </a>
                <a href="{{ route('admin.daftar-reksa-dana.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.daftar-reksa-dana.*') || request()->routeIs('admin.data-source-links.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Daftar Reksa Dana
                </a>
                {{-- AI Prompts --}}
                <div>
                    <button type="button" @click="menuAiPrompts = !menuAiPrompts"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.ai-prompts.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            AI Prompts
                        </span>
                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': menuAiPrompts }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="menuAiPrompts" x-transition class="space-y-1 pl-3">
                        @php $aiGroups = \App\Models\AiPrompt::groups(); @endphp
                        @forelse($aiGroups as $aiGroup)
                            <a href="{{ route('admin.ai-prompts.group', $aiGroup) }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('admin.ai-prompts.*') && request('group', request()->segment(4)) === $aiGroup ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                                {{ ucfirst($aiGroup) }}
                            </a>
                        @empty
                            <a href="{{ route('admin.ai-prompts.index') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                                Semua Prompt
                            </a>
                        @endforelse
                    </div>
                </div>
                <a href="{{ route('profile.edit') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('profile.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
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
            class="fixed inset-y-0 left-0 z-30 w-64 bg-white border-r border-gray-100 flex flex-col lg:hidden"
            x-transition:enter="transition-transform duration-300" x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0" x-transition:leave="transition-transform duration-300"
            x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
            <div class="h-16 flex items-center gap-3 px-5 border-b border-gray-100">
                <div class="w-9 h-9 rounded-lg grid place-items-center" style="background:linear-gradient(135deg,#16a34a,#22c55e)">IP</div>
                <div>
                    <div class="font-bold text-sm text-gray-900">InvestaPremier</div>
                    <div class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Admin Panel</div>
                </div>
            </div>
            <nav class="flex-1 py-4 px-3 space-y-1 text-sm overflow-y-auto">

                {{-- Manajemen --}}
                <div>
                    <button type="button" @click="menuMasterOpen = !menuMasterOpen"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.questions.*') || request()->routeIs('admin.members.*') || request()->routeIs('admin.score-classifications.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
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
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7" />
                            </svg>
                            Dashboard
                        </a>
                        <a href="{{ route('admin.score-classifications.index') }}"
                            @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.score-classifications.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Klasifikasi Skor
                        </a>
                        <a href="{{ route('admin.questions.index') }}" @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.questions.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7" />
                            </svg>
                            Soal Kuis
                        </a>
                        <a href="{{ route('admin.members.index') }}" @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.members.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
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
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.reksa-dana.*') || request()->routeIs('admin.analisa-rd.*') || request()->routeIs('admin.analisa.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
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
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.reksa-dana.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            Monitor Reksa Dana FFS
                        </a>
                        <a href="{{ route('admin.analisa-rd.create') }}" @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.analisa-rd.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Analisa Reksa Dana
                        </a>
                        <a href="{{ route('admin.analisa.index') }}" @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.analisa.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Monitor Analisa Reksa Dana
                        </a>
                    </div>
                </div>

                {{-- Unit Link (mobile) --}}
                <div>
                    <button type="button" @click="unitLinkOpen = !unitLinkOpen"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.unit-link.*') || request()->routeIs('admin.unit-link-ffs.*') || request()->routeIs('admin.analisa-ul.*') || request()->routeIs('admin.unit-link-analisa.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            Unit Link
                        </span>
                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': unitLinkOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="unitLinkOpen" x-transition class="space-y-1 pl-3">
                        <a href="{{ route('admin.unit-link.index') }}" @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.unit-link.index') || request()->routeIs('admin.unit-link.create') || request()->routeIs('admin.unit-link.edit') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            Daftar Unit Link
                        </a>
                        <a href="{{ route('admin.unit-link-ffs.index') }}" @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.unit-link-ffs.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            Monitor Unit Link FFS
                        </a>
                        <a href="{{ route('admin.analisa-ul.create') }}" @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.analisa-ul.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Analisa Unit Link
                        </a>
                        <a href="{{ route('admin.unit-link-analisa.index') }}" @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.unit-link-analisa.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            Monitor Analisa Unit Link
                        </a>
                    </div>
                </div>

                {{-- Saham --}}
                <div>
                    <button type="button" @click="sahamOpen = !sahamOpen"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.saham.*') || request()->routeIs('admin.analisa-saham.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            Saham
                        </span>
                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': sahamOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="sahamOpen" x-transition class="space-y-1 pl-3">
                        <a href="{{ route('admin.saham.index') }}" @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.saham.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            Daftar Saham
                        </a>
                        <a href="{{ route('admin.analisa-saham.create') }}" @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.analisa-saham.create') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Analisa Saham
                        </a>
                        <a href="{{ route('admin.analisa-saham.index') }}" @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.analisa-saham.index') || request()->routeIs('admin.analisa-saham.show') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            Monitor Analisa Saham
                        </a>
                    </div>
                </div>

                {{-- Obligasi --}}
                <div>
                    <button type="button" @click="obligasiOpen = !obligasiOpen"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.obligasi.*') || request()->routeIs('admin.analisa-obligasi.*') || request()->routeIs('admin.rating-obligasi.*') || request()->routeIs('admin.ytm-normal-curve.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Obligasi
                        </span>
                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': obligasiOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="obligasiOpen" x-transition class="space-y-1 pl-3">
                        <a href="{{ route('admin.obligasi.index') }}" @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.obligasi.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            Daftar Obligasi
                        </a>
                        <a href="{{ route('admin.rating-obligasi.index') }}" @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.rating-obligasi.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            Rating Obligasi
                        </a>
                        <a href="{{ route('admin.ytm-normal-curve.index') }}" @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.ytm-normal-curve.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                            YTM Normal Curve
                        </a>
                        <a href="{{ route('admin.analisa-obligasi.create') }}" @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.analisa-obligasi.create') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Analisa Obligasi
                        </a>
                        <a href="{{ route('admin.analisa-obligasi.index') }}" @@click="sidebarOpen = false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.analisa-obligasi.index') || request()->routeIs('admin.analisa-obligasi.show') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            Monitor Analisa Obligasi
                        </a>
                    </div>
                </div>

                {{-- Manajer Investasi --}}
                <a href="{{ route("admin.investment-managers.index") }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs("admin.investment-managers.*") ? "bg-green-50 text-green-700 font-semibold" : "text-gray-600 hover:bg-gray-50 hover:text-gray-900" }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Manajer Investasi
                </a>

                {{-- Queue Monitor --}}
                <a href="/horizon" target="_blank"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18" />
                    </svg>
                    Queue Monitor
                </a>
                <a href="{{ route('admin.daftar-reksa-dana.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.daftar-reksa-dana.*') || request()->routeIs('admin.data-source-links.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Daftar Reksa Dana
                </a>
                <a href="{{ route('profile.edit') }}" @@click="sidebarOpen = false"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('profile.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
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
                            <div class="font-semibold text-gray-900 truncate max-w-[120px]">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-muted">Admin</div>
                        </div>
                        <div
                            class="w-8 h-8 sm:w-9 sm:h-9 rounded-full text-white grid place-items-center text-xs font-bold uppercase shrink-0" style="background:linear-gradient(135deg,#16a34a,#22c55e)">
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
