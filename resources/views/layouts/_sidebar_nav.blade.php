@props(['mobile' => false])

{{-- Manajemen --}}
@if (Auth::user()->isAdmin() ||
        Auth::user()->hasAnyPermission([
            'manajemen.dashboard',
            'manajemen.score-classifications',
            'manajemen.questions',
            'manajemen.members',
        ]))
    <div>
        <button type="button" @click="menuMasterOpen = !menuMasterOpen"
            class="sidebar-item w-full justify-between {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.questions.*') || request()->routeIs('admin.members.*') || request()->routeIs('admin.score-classifications.*') || request()->routeIs('admin.activity-logs.*') ? 'sidebar-item-active' : '' }}">
            <span class="flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                </svg>
                <span class="sidebar-label">Manajemen</span>
            </span>
            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': menuMasterOpen }" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div x-show="menuMasterOpen" x-transition class="space-y-0.5 pl-3 mt-0.5 sidebar-connector">
            @canAccess('manajemen.dashboard')
            <a href="{{ route('admin.dashboard') }}"
                @if ($mobile) x-on:click="sidebarOpen = false" @endif
                class="sidebar-item sidebar-sub {{ request()->routeIs('admin.dashboard') ? 'sidebar-item-active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7" />
                </svg>
                <span class="sidebar-label">Dashboard</span>
            </a>
            @endcanAccess
            @canAccess('manajemen.score-classifications')
            <a href="{{ route('admin.score-classifications.index') }}"
                @if ($mobile) x-on:click="sidebarOpen = false" @endif
                class="sidebar-item sidebar-sub {{ request()->routeIs('admin.score-classifications.*') ? 'sidebar-item-active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="sidebar-label">Klasifikasi Skor</span>
            </a>
            @endcanAccess
            @if (Auth::user()->isAdmin())
            <a href="{{ route('admin.users.index') }}"
                @if ($mobile) x-on:click="sidebarOpen = false" @endif
                class="sidebar-item sidebar-sub {{ request()->routeIs('admin.users.*') ? 'sidebar-item-active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span class="sidebar-label">Pengguna</span>
            </a>
            @endif
            @canAccess('manajemen.questions')
            <a href="{{ route('admin.questions.index') }}"
                @if ($mobile) x-on:click="sidebarOpen = false" @endif
                class="sidebar-item sidebar-sub {{ request()->routeIs('admin.questions.*') ? 'sidebar-item-active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7" />
                </svg>
                <span class="sidebar-label">Soal Kuis</span>
            </a>
            @endcanAccess
            @canAccess('manajemen.members')
            <a href="{{ route('admin.members.index') }}"
                @if ($mobile) x-on:click="sidebarOpen = false" @endif
                class="sidebar-item sidebar-sub {{ request()->routeIs('admin.members.*') ? 'sidebar-item-active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3" />
                </svg>
                <span class="sidebar-label">Pendaftaran Member</span>
            </a>
            @endcanAccess
            @if (Auth::user()->isAdmin())
                <a href="{{ route('admin.activity-logs.index') }}"
                    @if ($mobile) x-on:click="sidebarOpen = false" @endif
                    class="sidebar-item sidebar-sub {{ request()->routeIs('admin.activity-logs.*') ? 'sidebar-item-active' : '' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="sidebar-label">Activity Logs</span>
                </a>
            @endif
        </div>
    </div>
@endif

{{-- Reksa Dana --}}
@if (Auth::user()->isAdmin() ||
        Auth::user()->hasAnyPermission([
            'reksa-dana.monitor-ffs',
            'reksa-dana.analisa-rd',
            'reksa-dana.monitor-analisa',
            'reksa-dana.daftar',
        ]))
    <div>
        <button type="button" @click="reksaDanaOpen = !reksaDanaOpen"
            class="sidebar-item w-full justify-between {{ request()->routeIs('admin.reksa-dana.*') || request()->routeIs('admin.analisa-rd.*') || request()->routeIs('admin.analisa.*') ? 'sidebar-item-active' : '' }}">
            <span class="flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                </svg>
                <span class="sidebar-label">Reksa Dana</span>
            </span>
            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': reksaDanaOpen }" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div x-show="reksaDanaOpen" x-transition class="space-y-0.5 pl-3 mt-0.5 sidebar-connector">
            @canAccess('reksa-dana.monitor-ffs')
            <a href="{{ route('admin.reksa-dana.index') }}"
                @if ($mobile) x-on:click="sidebarOpen = false" @endif
                class="sidebar-item sidebar-sub {{ request()->routeIs('admin.reksa-dana.*') ? 'sidebar-item-active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                <span class="sidebar-label">Monitor Reksa Dana FFS</span>
            </a>
            @endcanAccess
            @canAccess('reksa-dana.analisa-rd')
            <a href="{{ route('admin.analisa-rd.create') }}"
                @if ($mobile) x-on:click="sidebarOpen = false" @endif
                class="sidebar-item sidebar-sub {{ request()->routeIs('admin.analisa-rd.*') ? 'sidebar-item-active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="sidebar-label">Analisa Reksa Dana</span>
            </a>
            @endcanAccess
            @canAccess('reksa-dana.monitor-analisa')
            <a href="{{ route('admin.analisa.index') }}"
                @if ($mobile) x-on:click="sidebarOpen = false" @endif
                class="sidebar-item sidebar-sub {{ request()->routeIs('admin.analisa.*') ? 'sidebar-item-active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="sidebar-label">Monitor Analisa Reksa Dana</span>
            </a>
            @endcanAccess
        </div>
    </div>
@endif

{{-- Unit Link --}}
@if (Auth::user()->isAdmin() ||
        Auth::user()->hasAnyPermission([
            'unit-link.daftar',
            'unit-link.monitor-ffs',
            'unit-link.analisa',
            'unit-link.monitor-analisa',
        ]))
    <div>
        <button type="button" @click="unitLinkOpen = !unitLinkOpen"
            class="sidebar-item w-full justify-between {{ request()->routeIs('admin.unit-link.*') || request()->routeIs('admin.unit-link-ffs.*') || request()->routeIs('admin.analisa-ul.*') || request()->routeIs('admin.unit-link-analisa.*') ? 'sidebar-item-active' : '' }}">
            <span class="flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/>
                </svg>
                <span class="sidebar-label">Unit Link</span>
            </span>
            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': unitLinkOpen }" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div x-show="unitLinkOpen" x-transition class="space-y-0.5 pl-3 mt-0.5 sidebar-connector">
            @canAccess('unit-link.daftar')
            <a href="{{ route('admin.unit-link.index') }}"
                @if ($mobile) x-on:click="sidebarOpen = false" @endif
                class="sidebar-item sidebar-sub {{ request()->routeIs('admin.unit-link.index') || request()->routeIs('admin.unit-link.create') || request()->routeIs('admin.unit-link.edit') ? 'sidebar-item-active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                <span class="sidebar-label">Daftar Unit Link</span>
            </a>
            @endcanAccess
            @canAccess('unit-link.monitor-ffs')
            <a href="{{ route('admin.unit-link-ffs.index') }}"
                @if ($mobile) x-on:click="sidebarOpen = false" @endif
                class="sidebar-item sidebar-sub {{ request()->routeIs('admin.unit-link-ffs.*') ? 'sidebar-item-active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                <span class="sidebar-label">Monitor Unit Link FFS</span>
            </a>
            @endcanAccess
            @canAccess('unit-link.analisa')
            <a href="{{ route('admin.analisa-ul.create') }}"
                @if ($mobile) x-on:click="sidebarOpen = false" @endif
                class="sidebar-item sidebar-sub {{ request()->routeIs('admin.analisa-ul.*') ? 'sidebar-item-active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="sidebar-label">Analisa Unit Link</span>
            </a>
            @endcanAccess
            @canAccess('unit-link.monitor-analisa')
            <a href="{{ route('admin.unit-link-analisa.index') }}"
                @if ($mobile) x-on:click="sidebarOpen = false" @endif
                class="sidebar-item sidebar-sub {{ request()->routeIs('admin.unit-link-analisa.*') ? 'sidebar-item-active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="sidebar-label">Monitor Analisa Unit Link</span>
            </a>
            @endcanAccess
        </div>
    </div>
@endif

{{-- Saham --}}
@if (Auth::user()->isAdmin() ||
        Auth::user()->hasAnyPermission(['saham.daftar', 'saham.analisa', 'saham.monitor-analisa']))
    <div>
        <button type="button" @click="sahamOpen = !sahamOpen"
            class="sidebar-item w-full justify-between {{ request()->routeIs('admin.saham.*') || request()->routeIs('admin.analisa-saham.*') ? 'sidebar-item-active' : '' }}">
            <span class="flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                <span class="sidebar-label">Saham</span>
            </span>
            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': sahamOpen }" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div x-show="sahamOpen" x-transition class="space-y-0.5 pl-3 mt-0.5 sidebar-connector">
            @canAccess('saham.daftar')
            <a href="{{ route('admin.saham.index') }}"
                @if ($mobile) x-on:click="sidebarOpen = false" @endif
                class="sidebar-item sidebar-sub {{ request()->routeIs('admin.saham.*') ? 'sidebar-item-active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                <span class="sidebar-label">Daftar Saham</span>
            </a>
            {{-- <a href="{{ route('admin.idx-ai-extraction.index', ['type' => 'saham']) }}"

                class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.idx-ai-extraction.*') && request('type') === 'saham' ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Ekstrak AI dari IDX
            </a> --}}
            @endcanAccess
            @canAccess('saham.analisa')
            <a href="{{ route('admin.analisa-saham.create') }}"
                @if ($mobile) x-on:click="sidebarOpen = false" @endif
                class="sidebar-item sidebar-sub {{ request()->routeIs('admin.analisa-saham.create') ? 'sidebar-item-active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="sidebar-label">Analisa Saham</span>
            </a>
            @endcanAccess
            @canAccess('saham.monitor-analisa')
            <a href="{{ route('admin.analisa-saham.index') }}"
                @if ($mobile) x-on:click="sidebarOpen = false" @endif
                class="sidebar-item sidebar-sub {{ request()->routeIs('admin.analisa-saham.index') || request()->routeIs('admin.analisa-saham.show') ? 'sidebar-item-active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="sidebar-label">Monitor Analisa Saham</span>
            </a>
            @endcanAccess
        </div>
    </div>
@endif

<div class="sidebar-separator"></div>

{{-- Obligasi --}}
@if (Auth::user()->isAdmin() ||
        Auth::user()->hasAnyPermission([
            'obligasi.daftar',
            'obligasi.rating',
            'obligasi.ytm',
            'obligasi.sekuritas-informasi',
            'obligasi.analisa',
            'obligasi.monitor-analisa',
        ]))
    <div>
        <button type="button" @click="obligasiOpen = !obligasiOpen"
            class="sidebar-item w-full justify-between {{ request()->routeIs('admin.obligasi.*') || request()->routeIs('admin.analisa-obligasi.*') || request()->routeIs('admin.rating-obligasi.*') || request()->routeIs('admin.ytm-normal-curve.*') || request()->routeIs('admin.sekuritas-informasi.*') || request()->routeIs('admin.sekuritas.*') ? 'sidebar-item-active' : '' }}">
            <span class="flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="sidebar-label">Obligasi</span>
            </span>
            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': obligasiOpen }" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div x-show="obligasiOpen" x-transition class="space-y-0.5 pl-3 mt-0.5 sidebar-connector">
            @canAccess('obligasi.daftar')
            <a href="{{ route('admin.obligasi.index') }}"
                @if ($mobile) x-on:click="sidebarOpen = false" @endif
                class="sidebar-item sidebar-sub {{ request()->routeIs('admin.obligasi.*') ? 'sidebar-item-active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                <span class="sidebar-label">Daftar Obligasi</span>
            </a>
            {{-- <a href="{{ route('admin.idx-ai-extraction.index', ['type' => 'obligasi']) }}"

                class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.idx-ai-extraction.*') && request('type') === 'obligasi' ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Ekstrak AI dari IDX
            </a> --}}
            @endcanAccess
            @canAccess('obligasi.sekuritas-informasi')
            <a href="{{ route('admin.sekuritas-informasi.index') }}"
                @if ($mobile) x-on:click="sidebarOpen = false" @endif
                class="sidebar-item sidebar-sub {{ request()->routeIs('admin.sekuritas-informasi.*') ? 'sidebar-item-active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                <span class="sidebar-label">Sekuritas Informasi</span>
            </a>
            @endcanAccess
            @canAccess('obligasi.rating')
            <a href="{{ route('admin.rating-obligasi.index') }}"
                @if ($mobile) x-on:click="sidebarOpen = false" @endif
                class="sidebar-item sidebar-sub {{ request()->routeIs('admin.rating-obligasi.*') ? 'sidebar-item-active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
                <span class="sidebar-label">Rating Obligasi</span>
            </a>
            @endcanAccess
            @canAccess('obligasi.ytm')
            <a href="{{ route('admin.ytm-normal-curve.index') }}"
                @if ($mobile) x-on:click="sidebarOpen = false" @endif
                class="sidebar-item sidebar-sub {{ request()->routeIs('admin.ytm-normal-curve.*') ? 'sidebar-item-active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                </svg>
                <span class="sidebar-label">YTM Normal Curve</span>
            </a>
            @endcanAccess
            @canAccess('obligasi.analisa')
            <a href="{{ route('admin.analisa-obligasi.create') }}"
                @if ($mobile) x-on:click="sidebarOpen = false" @endif
                class="sidebar-item sidebar-sub {{ request()->routeIs('admin.analisa-obligasi.create') ? 'sidebar-item-active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="sidebar-label">Analisa Obligasi</span>
            </a>
            @endcanAccess
            @canAccess('obligasi.monitor-analisa')
            <a href="{{ route('admin.analisa-obligasi.index') }}"
                @if ($mobile) x-on:click="sidebarOpen = false" @endif
                class="sidebar-item sidebar-sub {{ request()->routeIs('admin.analisa-obligasi.index') || request()->routeIs('admin.analisa-obligasi.show') ? 'sidebar-item-active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="sidebar-label">Monitor Analisa Obligasi</span>
            </a>
            @endcanAccess
        </div>
    </div>
@endif

<div class="sidebar-separator"></div>

{{-- Manajer Investasi --}}
@canAccess('investment-managers')
<a href="{{ route('admin.investment-managers.index') }}"
    @if ($mobile) x-on:click="sidebarOpen = false" @endif
    class="sidebar-item {{ request()->routeIs('admin.investment-managers.*') ? 'sidebar-item-active' : '' }}">
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
    </svg>
    <span class="sidebar-label">Manajer Investasi</span>
</a>
@endcanAccess

{{-- Queue Monitor --}}
@if (Auth::user()->isAdmin())
    <a href="/horizon" target="_blank"
        class="sidebar-item text-gray-600 hover:bg-gray-50 hover:text-gray-900">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18" />
        </svg>
        <span class="sidebar-label">Queue Monitor</span>
    </a>
@endif

<div class="sidebar-separator"></div>

@canAccess('reksa-dana.daftar')
<a href="{{ route('admin.daftar-reksa-dana.index') }}"
    @if ($mobile) x-on:click="sidebarOpen = false" @endif
    class="sidebar-item {{ request()->routeIs('admin.daftar-reksa-dana.*') || request()->routeIs('admin.data-source-links.*') ? 'sidebar-item-active' : '' }}">
    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
    </svg>
    <span class="sidebar-label">Daftar Reksa Dana</span>
</a>
@endcanAccess

<div class="sidebar-separator"></div>

{{-- AI Prompts --}}
@if (Auth::user()->isAdmin() || Auth::user()->hasPermission('ai-prompts'))
    <div>
        <button type="button" @click="menuAiPrompts = !menuAiPrompts"
            class="sidebar-item w-full justify-between {{ request()->routeIs('admin.ai-prompts.*') ? 'sidebar-item-active' : '' }}">
            <span class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span class="sidebar-label">AI Prompts</span>
            </span>
            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': menuAiPrompts }" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div x-show="menuAiPrompts" x-transition class="space-y-0.5 pl-3 mt-0.5 sidebar-connector">
            @php $aiGroups = \App\Models\AiPrompt::groups(); @endphp
            @forelse($aiGroups as $aiGroup)
                <a href="{{ route('admin.ai-prompts.group', $aiGroup) }}"
                    @if ($mobile) x-on:click="sidebarOpen = false" @endif
                    class="sidebar-item sidebar-sub {{ request()->routeIs('admin.ai-prompts.*') && request('group', request()->segment(4)) === $aiGroup ? 'sidebar-item-active' : '' }}">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="sidebar-label">{{ ucfirst($aiGroup) }}</span>
                </a>
            @empty
                <a href="{{ route('admin.ai-prompts.index') }}"
                    @if ($mobile) x-on:click="sidebarOpen = false" @endif
                    class="sidebar-item sidebar-sub text-gray-600">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="sidebar-label">Semua Prompt</span>
                </a>
            @endforelse
        </div>
    </div>
@endif

<div class="sidebar-separator"></div>

@if (Auth::user()->isAdmin())
    <a href="{{ route('admin.sub-admins.index') }}"
        @if ($mobile) x-on:click="sidebarOpen = false" @endif
        class="sidebar-item {{ request()->routeIs('admin.sub-admins.*') ? 'sidebar-item-active' : '' }}">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <span class="sidebar-label">Sub Admin</span>
    </a>

    <a href="{{ route('admin.advisors.index') }}"
        @if ($mobile) x-on:click="sidebarOpen = false" @endif
        class="sidebar-item {{ request()->routeIs('admin.advisors.*') ? 'sidebar-item-active' : '' }}">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <span class="sidebar-label">Kelola Advisor</span>
        @php($pendingAdvisors = \App\Models\User::where('role', 'advisor')->where('is_active', false)->count())
        @if ($pendingAdvisors > 0)
            <span class="ml-auto inline-flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-bold text-white bg-red-500">{{ $pendingAdvisors }}</span>
        @endif
    </a>
@endif

<a href="{{ route('profile.edit') }}" @if ($mobile) x-on:click="sidebarOpen = false" @endif
    class="sidebar-item {{ request()->routeIs('profile.*') ? 'sidebar-item-active' : '' }}">
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
    </svg>
    <span class="sidebar-label">Profile</span>
</a>
