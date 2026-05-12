@extends('layouts.guest')

@section('title', 'Daftar - InvestaPremier')

@section('body')
<div class="min-h-screen flex">
    <div class="hidden lg:flex flex-1 bg-primary items-center justify-center px-12 relative overflow-hidden">
        <div class="absolute w-96 h-96 rounded-full bg-accent/20 blur-[100px] -top-20 -right-20"></div>
        <div class="absolute w-80 h-80 rounded-full bg-gold/10 blur-[80px] -bottom-20 -left-20"></div>
        <div class="relative text-white max-w-md">
            <div class="text-6xl mb-6">✦</div>
            <h2 class="text-3xl font-bold leading-tight">Mulai Perjalanan<br>Wealth Anda.</h2>
            <p class="mt-4 text-white/70 leading-relaxed">Bergabunglah dengan platform wealth advisory premium dan kelola seluruh portofolio keluarga dalam satu dashboard.</p>
            <div class="mt-8">
                <div class="text-sm text-white/50">Sudah punya akun? <a href="{{ route('login') }}" class="text-white font-semibold underline">Masuk</a></div>
            </div>
        </div>
    </div>
    <div class="flex-1 flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-sm">
            <div class="mb-8 text-center">
                <div class="w-12 h-12 rounded-xl bg-primary text-white grid place-items-center text-xl font-bold mx-auto mb-4">✦</div>
                <h1 class="text-2xl font-bold text-primary">Buat Akun</h1>
                <p class="text-muted text-sm mt-1">Daftar untuk mengakses dashboard</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div>
                    <label for="name" class="text-sm font-medium text-primary">Nama Lengkap</label>
                    <x-text-input id="name" class="block mt-1.5 w-full px-4 py-2.5 text-sm" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Nama Anda" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <label for="email" class="text-sm font-medium text-primary">Email</label>
                    <x-text-input id="email" class="block mt-1.5 w-full px-4 py-2.5 text-sm" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="you@example.com" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <label for="password" class="text-sm font-medium text-primary">Password</label>
                    <x-text-input id="password" class="block mt-1.5 w-full px-4 py-2.5 text-sm" type="password" name="password" required autocomplete="new-password" placeholder="Min. 8 karakter" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <label for="password_confirmation" class="text-sm font-medium text-primary">Konfirmasi Password</label>
                    <x-text-input id="password_confirmation" class="block mt-1.5 w-full px-4 py-2.5 text-sm" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <button type="submit" class="mt-6 w-full py-2.5 rounded-xl bg-primary text-white font-semibold text-sm hover:bg-primary-light transition">
                    Daftar
                </button>

                <div class="relative my-5">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-line"></div></div>
                    <div class="relative flex justify-center"><span class="bg-white px-3 text-xs text-muted">atau</span></div>
                </div>

                <a href="{{ route('auth.google') }}"
                   class="flex items-center justify-center gap-3 w-full py-2.5 rounded-xl border border-line bg-white text-sm font-semibold text-primary hover:bg-[#f8fafc] transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    Daftar dengan Google
                </a>

                <p class="mt-6 text-center text-sm text-muted lg:hidden">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="text-accent font-semibold hover:underline">Masuk</a>
                </p>
            </form>
        </div>
    </div>
</div>
@endsection
