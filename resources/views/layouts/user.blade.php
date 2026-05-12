@extends('layouts.app')

@section('body')
<div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">
    {{-- Overlay mobile --}}
    <div x-show="sidebarOpen" x-cloak @@click="sidebarOpen = false" class="fixed inset-0 z-20 bg-black/40 lg:hidden"></div>

    {{-- Sidebar Desktop --}}
    <aside class="hidden lg:flex w-64 bg-primary text-white flex-col shrink-0">
        <div class="h-16 flex items-center gap-3 px-5 border-b border-white/10">
            <div class="w-9 h-9 rounded-lg bg-white/10 grid place-items-center font-bold text-sm">✦</div>
            <div>
                <div class="font-bold text-sm">InvestaPremier</div>
                <div class="text-[11px] text-white/60">Client Dashboard</div>
            </div>
        </div>
        <nav class="flex-1 py-4 px-3 space-y-1 text-sm">
            <a href="{{ route('user.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.dashboard') ? 'bg-white/10 font-semibold' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="{{ route('quiz.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('quiz.*') ? 'bg-white/10 font-semibold' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Profil Investasi
            </a>
            <a href="{{ route('member.create') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('member.*') ? 'bg-white/10 font-semibold' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Daftar Member
            </a>
            <a href="{{ route('profile.edit') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('profile.*') ? 'bg-white/10 font-semibold' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Profile
            </a>
        </nav>
    </aside>

    {{-- Sidebar Mobile --}}
    <aside x-show="sidebarOpen" x-cloak
           class="fixed inset-y-0 left-0 z-30 w-64 bg-primary text-white flex flex-col lg:hidden"
           x-transition:enter="transition-transform duration-300"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition-transform duration-300"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="-translate-x-full">
        <div class="h-16 flex items-center gap-3 px-5 border-b border-white/10">
            <div class="w-9 h-9 rounded-lg bg-white/10 grid place-items-center font-bold text-sm">✦</div>
            <div>
                <div class="font-bold text-sm">InvestaPremier</div>
                <div class="text-[11px] text-white/60">Client Dashboard</div>
            </div>
        </div>
        <nav class="flex-1 py-4 px-3 space-y-1 text-sm">
            <a href="{{ route('user.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('user.dashboard') ? 'bg-white/10 font-semibold' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="{{ route('quiz.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('quiz.*') ? 'bg-white/10 font-semibold' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Profil Investasi
            </a>
            <a href="{{ route('member.create') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('member.*') ? 'bg-white/10 font-semibold' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Daftar Member
            </a>
            <a href="{{ route('profile.edit') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('profile.*') ? 'bg-white/10 font-semibold' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Profile
            </a>
        </nav>
    </aside>

    {{-- Main area --}}
    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-16 bg-white border-b border-line flex items-center justify-between gap-4 px-4 lg:px-6 shrink-0">
            <button @@click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition">
                <svg x-show="!sidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="sidebarOpen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="flex items-center gap-2 ml-auto">
                <div class="flex items-center gap-2 sm:gap-3 text-sm">
                    <div class="hidden sm:block text-right">
                        <div class="font-medium text-primary truncate max-w-[120px]">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-muted">{{ Auth::user()->isMember() ? 'Member' : 'Nasabah' }}</div>
                    </div>
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-accent text-white grid place-items-center text-xs font-bold uppercase shrink-0">{{ substr(Auth::user()->name, 0, 2) }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-1.5 px-2.5 py-2 rounded-lg text-muted hover:text-primary hover:bg-[#f1f5f9] transition text-sm">
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
