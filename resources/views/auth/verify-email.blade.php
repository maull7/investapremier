@extends('layouts.guest')

@section('title', 'Verifikasi Email - InvestaPremier')

@section('body')
<div class="min-h-screen flex items-center justify-center px-6">
    <div class="w-full max-w-md">
        <div class="mb-8 text-center">
            <div class="w-12 h-12 rounded-xl bg-primary text-white grid place-items-center text-xl font-bold mx-auto mb-4">✦</div>
            <h1 class="text-2xl font-bold text-primary">Verifikasi Email</h1>
        </div>

        <div class="bg-white rounded-2xl border border-line p-8 shadow-sm">
            <div class="mb-4 text-sm text-muted">
                Terima kasih sudah mendaftar! Sebelum memulai, verifikasi email Anda dengan mengklik link yang kami kirimkan. Jika tidak menerima email, kami akan kirim ulang.
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="mb-4 font-medium text-sm text-green-600">
                    Link verifikasi baru telah dikirim ke email Anda.
                </div>
            @endif

            <div class="flex items-center justify-between mt-4">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="py-2.5 px-5 rounded-xl bg-primary text-white font-semibold text-sm hover:bg-primary-light transition">
                        Kirim Ulang Email
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-muted hover:text-primary underline">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
