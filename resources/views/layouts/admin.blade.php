@extends('layouts.app')

@section('body')
    <div x-data="{
        sidebarOpen: false,
        menuMasterOpen: {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.questions.*') || request()->routeIs('admin.members.*') || request()->routeIs('admin.score-classifications.*') || request()->routeIs('admin.activity-logs.*') ? 'true' : 'false' }},
        reksaDanaOpen: {{ request()->routeIs('admin.reksa-dana.*') || request()->routeIs('admin.analisa-rd.*') || request()->routeIs('admin.analisa.*') ? 'true' : 'false' }},
        unitLinkOpen: {{ request()->routeIs('admin.unit-link.*') || request()->routeIs('admin.unit-link-ffs.*') || request()->routeIs('admin.analisa-ul.*') || request()->routeIs('admin.unit-link-analisa.*') ? 'true' : 'false' }},
        sahamOpen: {{ request()->routeIs('admin.saham.*') || request()->routeIs('admin.analisa-saham.*') || (request()->routeIs('admin.idx-ai-extraction.*') && request('type') === 'saham') ? 'true' : 'false' }},
        obligasiOpen: {{ request()->routeIs('admin.obligasi.*') || request()->routeIs('admin.analisa-obligasi.*') || request()->routeIs('admin.rating-obligasi.*') || request()->routeIs('admin.ytm-normal-curve.*') || request()->routeIs('admin.sekuritas-informasi.*') || request()->routeIs('admin.sekuritas.*') || (request()->routeIs('admin.idx-ai-extraction.*') && request('type') === 'obligasi') ? 'true' : 'false' }},
        menuAiPrompts: {{ request()->routeIs('admin.ai-prompts.*') ? 'true' : 'false' }}
    }" class="flex h-screen overflow-hidden">
        {{-- Overlay mobile --}}
        <div x-show="sidebarOpen" x-cloak @@click="sidebarOpen = false"
            class="fixed inset-0 z-20 bg-black/40 lg:hidden"></div>

        {{-- Sidebar Desktop --}}
        <aside class="hidden lg:flex w-64 bg-white border-r border-gray-100 flex-col shrink-0 shadow-sm">
            <div class="h-16 flex items-center gap-3 px-5 border-b border-gray-100">
                <div class="w-9 h-9 rounded-lg grid place-items-center"
                    style="background:linear-gradient(135deg,#16a34a,#22c55e)">IP</div>
                <div>
                    <div class="font-bold text-sm text-gray-900">InvestaPremier</div>
                    <div class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Admin Panel</div>
                </div>
            </div>
            <nav class="flex-1 py-4 px-3 space-y-1 text-sm overflow-y-auto">
                @include('layouts._sidebar_nav', ['mobile' => false])
            </nav>
        </aside>

        {{-- Sidebar Mobile --}}
        <aside x-show="sidebarOpen" x-cloak
            class="fixed inset-y-0 left-0 z-30 w-64 bg-white border-r border-gray-100 flex flex-col lg:hidden"
            x-transition:enter="transition-transform duration-300" x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0" x-transition:leave="transition-transform duration-300"
            x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
            <div class="h-16 flex items-center gap-3 px-5 border-b border-gray-100">
                <div class="w-9 h-9 rounded-lg grid place-items-center"
                    style="background:linear-gradient(135deg,#16a34a,#22c55e)">IP</div>
                <div>
                    <div class="font-bold text-sm text-gray-900">InvestaPremier</div>
                    <div class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">Admin Panel</div>
                </div>
            </div>
            <nav class="flex-1 py-4 px-3 space-y-1 text-sm overflow-y-auto">
                @include('layouts._sidebar_nav', ['mobile' => true])
            </nav>
        </aside>

        {{-- Main area --}}
        <div class="flex-1 flex flex-col min-w-0">
            <header
                class="h-16 bg-white border-b border-line flex items-center justify-between gap-4 px-4 lg:px-6 shrink-0">
                <button @@click="sidebarOpen = !sidebarOpen"
                    class="lg:hidden p-2 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition">
                    <svg x-show="!sidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
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
