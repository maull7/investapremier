@extends('layouts.guest')

@section('title', 'Reset Password - InvestaPremier')

@section('body')
<div class="min-h-screen flex items-center justify-center px-6">
    <div class="w-full max-w-md">
        <div class="mb-8 text-center">
            <div class="w-12 h-12 rounded-xl bg-primary text-white grid place-items-center text-xl font-bold mx-auto mb-4">✦</div>
            <h1 class="text-2xl font-bold text-primary">Reset Password</h1>
            <p class="text-muted text-sm mt-1">Buat password baru</p>
        </div>

        <div class="bg-white rounded-2xl border border-line p-8 shadow-sm">
            <form method="POST" action="{{ route('password.store') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div>
                    <label for="email" class="text-sm font-medium text-primary">Email</label>
                    <x-text-input id="email" class="block mt-1.5 w-full px-4 py-2.5 text-sm" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <label for="password" class="text-sm font-medium text-primary">Password Baru</label>
                    <x-text-input id="password" class="block mt-1.5 w-full px-4 py-2.5 text-sm" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <label for="password_confirmation" class="text-sm font-medium text-primary">Konfirmasi Password</label>
                    <x-text-input id="password_confirmation" class="block mt-1.5 w-full px-4 py-2.5 text-sm" type="password" name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <button type="submit" class="mt-6 w-full py-2.5 rounded-xl bg-primary text-white font-semibold text-sm hover:bg-primary-light transition">
                    Reset Password
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
