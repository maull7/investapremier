<form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

<form method="post" action="{{ route('profile.update') }}" class="space-y-5">
    @csrf
    @method('patch')

    <div>
        <x-input-label for="name" value="Nama" class="text-sm font-semibold mb-1.5" />
        <x-text-input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
            autofocus autocomplete="name"
            class="w-full px-3 py-2 text-sm @error('name') border-accent-teal/85 @enderror" />
        <x-input-error :messages="$errors->get('name')" class="mt-1 text-xs" />
    </div>

    <div>
        <x-input-label for="email" value="Email" class="text-sm font-semibold mb-1.5" />
        <x-text-input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
            autocomplete="username" class="w-full px-3 py-2 text-sm @error('email') border-accent-teal/85 @enderror" />
        <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs" />

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
            <div class="mt-2 p-3 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-700">
                Email belum terverifikasi.
                <button form="send-verification" class="underline font-semibold hover:text-amber-900">
                    Kirim ulang email verifikasi
                </button>
                @if (session('status') === 'verification-link-sent')
                    <p class="mt-1 font-medium text-green-600">Link verifikasi telah dikirim.</p>
                @endif
            </div>
        @endif
    </div>

    <div class="flex items-center gap-3 pt-1">
        <button type="submit"
            class="px-5 py-2.5 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent/90 transition shadow-sm shadow-accent/20">
            Simpan Perubahan
        </button>
        @if (session('status') === 'profile-updated')
            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-green-600 font-medium flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Tersimpan
            </p>
        @endif
    </div>
</form>
