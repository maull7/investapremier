@extends('layouts.guest')

@section('title', 'Forgot Password - InvestaPremier')

@section('body')
<div class="min-h-screen flex items-center justify-center px-6">
    <div class="w-full max-w-md">
        <div class="mb-8 text-center">
            <div class="w-12 h-12 rounded-xl bg-primary text-white grid place-items-center text-xl font-bold mx-auto mb-4">✦</div>
            <h1 class="text-2xl font-bold text-primary">Lupa Password</h1>
            <p class="text-muted text-sm mt-1">Masukkan email untuk reset password</p>
        </div>

        <div class="bg-white rounded-2xl border border-line p-8 shadow-sm">
            <div class="mb-4 text-sm text-muted">
                Lupa password? Masukkan email Anda dan kami akan kirimkan link reset.
            </div>

            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div>
                    <label for="email" class="text-sm font-medium text-primary">Email</label>
                    <x-text-input id="email" class="block mt-1.5 w-full px-4 py-2.5 text-sm" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="you@example.com" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <button type="submit" class="mt-6 w-full py-2.5 rounded-xl bg-primary text-white font-semibold text-sm hover:bg-primary-light transition">
                    Kirim Link Reset
                </button>

                <p class="mt-4 text-center text-sm text-muted">
                    <a href="{{ route('login') }}" class="text-accent font-semibold hover:underline">Kembali ke Login</a>
                </p>
            </form>
        </div>
    </div>
</div>
@endsection
