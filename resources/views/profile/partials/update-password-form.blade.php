<form method="post" action="{{ route('password.update') }}" class="space-y-5">
    @csrf
    @method('put')

    <div>
        <x-input-label for="update_password_current_password" value="Password Saat Ini" class="text-sm font-semibold mb-1.5" />
        <x-text-input id="update_password_current_password" name="current_password" type="password"
               autocomplete="current-password"
               class="w-full px-3 py-2 text-sm @error('current_password', 'updatePassword') border-red-400 @enderror" />
        <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1 text-xs" />
    </div>

    <div>
        <x-input-label for="update_password_password" value="Password Baru" class="text-sm font-semibold mb-1.5" />
        <x-text-input id="update_password_password" name="password" type="password"
               autocomplete="new-password"
               class="w-full px-3 py-2 text-sm @error('password', 'updatePassword') border-red-400 @enderror" />
        <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1 text-xs" />
    </div>

    <div>
        <x-input-label for="update_password_password_confirmation" value="Konfirmasi Password Baru" class="text-sm font-semibold mb-1.5" />
        <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password"
               autocomplete="new-password"
               class="w-full px-3 py-2 text-sm @error('password_confirmation', 'updatePassword') border-red-400 @enderror" />
        <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1 text-xs" />
    </div>

    <div class="flex items-center gap-3 pt-1">
        <button type="submit"
                class="px-5 py-2.5 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent/90 transition shadow-sm shadow-accent/20">
            Ubah Password
        </button>
        @if (session('status') === 'password-updated')
        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
           class="text-sm text-green-600 font-medium flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Password diubah
        </p>
        @endif
    </div>
</form>
