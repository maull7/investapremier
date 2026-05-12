@php
    $layout = Auth::user()->isAdmin() ? 'layouts.admin' : 'layouts.user';
@endphp

@extends($layout)

@section('title', 'Profile - InvestaPremier')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-primary">Profile</h1>
        <p class="text-muted text-sm mt-1">Kelola informasi akun Anda</p>
    </div>

    <div class="max-w-2xl space-y-5">
        <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light">
                <h2 class="font-bold text-white">Informasi Profil</h2>
                <p class="text-white/60 text-xs mt-0.5">Update nama dan email akun Anda</p>
            </div>
            <div class="p-6">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light">
                <h2 class="font-bold text-white">Ubah Password</h2>
                <p class="text-white/60 text-xs mt-0.5">Gunakan password yang kuat untuk keamanan akun</p>
            </div>
            <div class="p-6">
                @include('profile.partials.update-password-form')
            </div>
        </div>


    </div>
@endsection
