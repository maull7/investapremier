<div x-data="{ open: false }">
    <p class="text-sm text-muted mb-4">
        Setelah akun dihapus, semua data akan dihapus secara permanen. Pastikan Anda sudah menyimpan data penting sebelum melanjutkan.
    </p>

    <button type="button" @click="open = true"
            class="px-5 py-2.5 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700 transition">
        Hapus Akun Saya
    </button>

    {{-- Modal --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-black/40" @click="open = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6"
             x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-start gap-4 mb-5">
                <div class="w-10 h-10 rounded-full bg-red-100 grid place-items-center shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-primary">Hapus Akun?</h3>
                    <p class="text-sm text-muted mt-1">Tindakan ini tidak dapat dibatalkan. Masukkan password untuk konfirmasi.</p>
                </div>
            </div>

            <form method="post" action="{{ route('profile.destroy') }}" class="space-y-4">
                @csrf
                @method('delete')

                <div>
                    <x-input-label for="del_password" value="Password" class="text-sm font-semibold mb-1.5" />
                    <x-text-input id="del_password" name="password" type="password" placeholder="Masukkan password Anda"
                           class="w-full px-3 py-2 text-sm @error('password', 'userDeletion') border-red-400 @enderror" />
                    <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-1 text-xs" />
                </div>

                <div class="flex items-center justify-end gap-3 pt-1">
                    <button type="button" @click="open = false"
                            class="px-4 py-2 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700 transition">
                        Ya, Hapus Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($errors->userDeletion->isNotEmpty())
<script>document.addEventListener('DOMContentLoaded', () => { document.querySelector('[x-data]').__x.$data.open = true })</script>
@endif
