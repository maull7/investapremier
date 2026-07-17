@extends('layouts.app')

@section('body')
    <div x-data="{
        sidebarOpen: false,
        sidebarCollapsed: false,
        menuMasterOpen: {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.questions.*') || request()->routeIs('admin.members.*') || request()->routeIs('admin.score-classifications.*') || request()->routeIs('admin.activity-logs.*') ? 'true' : 'false' }},
        reksaDanaOpen: {{ request()->routeIs('admin.reksa-dana.*') || request()->routeIs('admin.analisa-rd.*') || request()->routeIs('admin.analisa.*') ? 'true' : 'false' }},
        unitLinkOpen: {{ request()->routeIs('admin.unit-link.*') || request()->routeIs('admin.unit-link-ffs.*') || request()->routeIs('admin.analisa-ul.*') || request()->routeIs('admin.unit-link-analisa.*') ? 'true' : 'false' }},
        sahamOpen: {{ request()->routeIs('admin.saham.*') || request()->routeIs('admin.analisa-saham.*') || (request()->routeIs('admin.idx-ai-extraction.*') && request('type') === 'saham') ? 'true' : 'false' }},
        obligasiOpen: {{ request()->routeIs('admin.obligasi.*') || request()->routeIs('admin.analisa-obligasi.*') || request()->routeIs('admin.rating-obligasi.*') || request()->routeIs('admin.ytm-normal-curve.*') || request()->routeIs('admin.sekuritas-informasi.*') || request()->routeIs('admin.sekuritas.*') || (request()->routeIs('admin.idx-ai-extraction.*') && request('type') === 'obligasi') ? 'true' : 'false' }},
        menuAiPrompts: {{ request()->routeIs('admin.ai-prompts.*') ? 'true' : 'false' }},
        adminDate: '{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}',
        clockTime: '',
        init() {
            const update = () => {
                const d = new Date();
                this.clockTime = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            };
            update();
            setInterval(update, 1000);
        }
    }" class="flex h-screen overflow-hidden">
        {{-- Overlay mobile --}}
        <div x-show="sidebarOpen" x-cloak @@click="sidebarOpen = false"
            class="fixed inset-0 z-20 bg-black/40 lg:hidden"></div>

        {{-- Sidebar Desktop --}}
        <aside x-data :class="sidebarCollapsed ? 'w-[68px] sidebar-collapsed' : 'w-64'"
            class="hidden lg:flex bg-white border-r border-gray-100 flex-col shrink-0 shadow-sm transition-all duration-200">
            <div class="h-16 flex items-center gap-3 px-3 border-b border-green-500/10">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 flex-1 min-w-0">
                    <div class="w-10 h-10 rounded-xl grid place-items-center text-white shrink-0 shadow-lg shadow-green-500/20"
                        style="background:linear-gradient(135deg,#16a34a,#22c55e)">
                        <img src="{{ asset('favicon.png') }}" class="w-full h-full object-contain rounded-xl"
                            alt="Logo InvestaPremier" />
                    </div>
                    <div x-show="!sidebarCollapsed" class="min-w-0">
                        <div class="font-bold text-sm text-gray-900 truncate">InvestaPremier</div>
                        <div
                            class="text-[10px] text-gray-400 uppercase tracking-wider font-medium flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
                            Admin Panel
                        </div>
                    </div>
                </a>
                <button @@click="sidebarCollapsed = !sidebarCollapsed"
                    class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition shrink-0">
                    <svg x-show="!sidebarCollapsed" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                    <svg x-show="sidebarCollapsed" x-cloak class="w-4 h-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
            <nav class="flex-1 py-4 px-3 space-y-1 text-sm overflow-y-auto" :class="sidebarCollapsed ? 'px-1' : 'px-3'">
                @include('layouts._sidebar_nav', ['mobile' => false])
            </nav>
        </aside>

        {{-- Sidebar Mobile --}}
        <aside x-show="sidebarOpen" x-cloak
            class="fixed inset-y-0 left-0 z-30 w-64 bg-white border-r border-gray-100 flex flex-col lg:hidden"
            x-transition:enter="transition-transform duration-300" x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0" x-transition:leave="transition-transform duration-300"
            x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
            <a href="{{ route('admin.dashboard') }}" @click="sidebarOpen = false"
                class="h-16 flex items-center gap-3 px-5 border-b border-green-500/10 hover:bg-gray-50/50 transition">
                <div class="w-10 h-10 rounded-xl grid place-items-center text-white shadow-lg shadow-green-500/20"
                    style="background:linear-gradient(135deg,#16a34a,#22c55e)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        <circle cx="3" cy="3" r="1" fill="currentColor" transform="translate(18 4)" />
                    </svg>
                </div>
                <div>
                    <div class="font-bold text-sm text-gray-900 tracking-tight">InvestaPremier</div>
                    <div class="text-[10px] text-gray-400 uppercase tracking-wider font-medium flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
                        Admin Panel
                    </div>
                </div>
            </a>
            <nav class="flex-1 py-4 px-3 text-sm overflow-y-auto">
                @include('layouts._sidebar_nav', ['mobile' => true])
            </nav>
        </aside>

        {{-- Main area --}}
        <div class="flex-1 flex flex-col min-w-0">
            <header
                class="h-16 bg-white border-b border-line flex items-center justify-between gap-4 px-4 lg:px-6 shrink-0">
                <div class="flex items-center gap-2">
                    <button @@click="sidebarOpen = !sidebarOpen"
                        class="lg:hidden p-2 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition">
                        <svg x-show="!sidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg x-show="sidebarOpen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <div class="hidden sm:flex items-center gap-2 text-sm text-gray-500">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="font-medium" x-text="adminDate"></span>
                        <span class="text-gray-300">•</span>
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-mono tabular-nums font-medium" x-text="clockTime"></span>
                    </div>

                    <div class="sm:hidden flex items-center gap-2 text-xs text-gray-400">
                        <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span x-text="adminDate"></span>
                        <span x-text="clockTime" class="font-mono tabular-nums"></span>
                    </div>

                    <div class="w-px h-5 bg-gray-200 hidden sm:block"></div>
                </div>

                <div class="flex items-center gap-2">
                    <x-notification-bell admin="true" />
                    <div class="flex items-center gap-2 sm:gap-3 text-sm">
                        <div class="hidden sm:block text-right">
                            <div class="font-semibold text-gray-900 truncate max-w-[120px]">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-muted">
                                {{ Auth::user()->role == 'admin' ? 'Administrator' : 'SubAdmin' }}
                            </div>
                        </div>
                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full text-white grid place-items-center text-xs font-bold uppercase shrink-0"
                            style="background:linear-gradient(135deg,#16a34a,#22c55e)">
                            {{ substr(Auth::user()->name, 0, 2) }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="flex items-center gap-1.5 px-2.5 py-2 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 transition text-sm">
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
