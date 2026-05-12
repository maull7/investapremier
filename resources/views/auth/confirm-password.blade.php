@extends('layouts.guest')

@section('title', 'Confirm Password - InvestaPremier')

@section('body')
<div class="min-h-screen flex items-center justify-center px-6">
    <div class="w-full max-w-md">
        <div class="mb-8 text-center">
            <div class="w-12 h-12 rounded-xl bg-primary text-white grid place-items-center text-xl font-bold mx-auto mb-4">✦</div>
            <h1 class="text-2xl font-bold text-primary">Konfirmasi Password</h1>
            <p class="text-muted text-sm mt-1">Konfirmasi password untuk melanjutkan</p>
        </div>

        <div class="bg-white rounded-2xl border border-line p-8 shadow-sm">
            <div class="mb-4 text-sm text-muted">Ini adalah area aman. Harap konfirmasi password sebelum melanjutkan.</div>

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf
                <div>
                    <label for="password" class="text-sm font-medium text-primary">Password</label>
                    <x-text-input id="password" class="block mt-1.5 w-full px-4 py-2.5 text-sm" type="password" name="password" required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <button type="submit" class="mt-6 w-full py-2.5 rounded-xl bg-primary text-white font-semibold text-sm hover:bg-primary-light transition">
                    Konfirmasi
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
