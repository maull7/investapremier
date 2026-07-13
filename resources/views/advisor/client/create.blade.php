@extends('layouts.user')

@section('title', 'Tambah Klien')

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('user.clients.index') }}" class="text-sm text-muted hover:text-primary">&larr; Kembali</a>
        <h1 class="page-title mt-2">Tambah Klien</h1>
        <p class="page-sub">Pilih user yang akan dikirim permintaan koneksi advisor</p>
    </div>

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('user.clients.store') }}" id="form-add-client">
        @csrf
        <input type="hidden" name="client_id" id="selected-client-id">

        <div x-data="{ search: '' }" class="bg-white rounded-xl border border-line overflow-hidden">
            <div class="px-5 py-4 border-b border-line bg-gradient-to-r from-primary to-primary/80">
                <h2 class="font-bold text-white text-sm">Daftar User</h2>
            </div>

            <div class="px-5 py-3 border-b border-line bg-[#f8fafc]">
                <input type="text" x-model="search" placeholder="Cari nama atau email..."
                    class="w-full max-w-sm text-sm border border-line rounded-lg px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20">
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-[#f8fafc] border-b border-line">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold text-primary w-10"></th>
                            <th class="px-5 py-3 text-left font-semibold text-primary">Nama</th>
                            <th class="px-5 py-3 text-left font-semibold text-primary">Email</th>
                            <th class="px-5 py-3 text-left font-semibold text-primary">Pekerjaan</th>
                            <th class="px-5 py-3 text-left font-semibold text-primary">Profil Risiko</th>
                            <th class="px-5 py-3 text-right font-semibold text-primary">Penghasilan</th>
                            <th class="px-5 py-3 text-center font-semibold text-primary">Member</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($users as $u)
                            @php $profile = $u->memberProfile; @endphp
                            <tr class="hover:bg-[#f8fafc] transition cursor-pointer"
                                x-show="!search || '{{ strtolower($u->name) }} {{ strtolower($u->email) }}'.includes(search.toLowerCase())"
                                @click="document.getElementById('selected-client-id').value = '{{ $u->id }}'; document.getElementById('form-add-client').submit();">
                                <td class="px-5 py-3.5 text-center">
                                    <input type="radio" name="_select" value="{{ $u->id }}"
                                        class="w-4 h-4 text-accent border-line focus:ring-accent">
                                </td>
                                <td class="px-5 py-3.5 font-medium text-primary">{{ $u->name }}</td>
                                <td class="px-5 py-3.5 text-muted text-xs">{{ $u->email }}</td>
                                <td class="px-5 py-3.5 text-muted">{{ $profile?->pekerjaan ?? '—' }}</td>
                                <td class="px-5 py-3.5">
                                    @if ($profile?->profil_risiko)
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">{{ $profile->profil_risiko }}</span>
                                    @else
                                        <span class="text-muted text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right text-xs text-muted">{{ $profile?->rata_rata_penghasilan ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    @if ($profile)
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                            {{ $profile->status === 'approved' ? 'bg-green-100 text-green-700' : ($profile->status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                            {{ $profile->status === 'approved' ? 'Aktif' : ($profile->status === 'pending' ? 'Pending' : 'Tidak') }}
                                        </span>
                                    @else
                                        <span class="text-muted text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-muted text-sm">Tidak ada user tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-800 flex items-start gap-2">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Klik baris user untuk mengirim permintaan koneksi. User akan menerima notifikasi dan perlu menyetujui.</span>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.querySelectorAll('tbody tr').forEach(row => {
    row.addEventListener('click', function() {
        const radio = this.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
    });
});
</script>
@endpush
@endsection
