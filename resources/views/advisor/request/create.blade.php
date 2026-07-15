@extends('layouts.user')

@section('title', 'Tambah Advisor')

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('user.clients.requests.index') }}" class="text-sm text-muted hover:text-primary">&larr; Kembali</a>
        <h1 class="page-title mt-2">Tambah Advisor</h1>
        <p class="page-sub">Pilih advisor yang akan dikirim permintaan koneksi</p>
    </div>

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('user.clients.requests.store') }}" id="form-add-advisor">
        @csrf
        <input type="hidden" name="advisor_id" id="selected-advisor-id">

        <div x-data="{ search: '' }" class="bg-white rounded-xl border border-line overflow-hidden">
            <div class="px-5 py-4 border-b border-line bg-gradient-to-r from-accent to-accent/80">
                <h2 class="font-bold text-white text-sm">Daftar Advisor</h2>
            </div>

            <div class="px-5 py-3 border-b border-line bg-[#f8fafc]">
                <input type="text" x-model="search" placeholder="Cari nama atau email..."
                    class="w-full max-w-sm text-sm border border-line rounded-lg px-3 py-2 focus:border-accent focus:ring focus:ring-accent/20">
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-[#f8fafc] border-b border-line">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold text-primary w-10"></th>
                            <th class="px-5 py-3 text-left font-semibold text-primary">Nama</th>
                            <th class="px-5 py-3 text-left font-semibold text-primary">Email</th>
                            <th class="px-5 py-3 text-left font-semibold text-primary">Telepon</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($advisors as $a)
                            <tr class="hover:bg-[#f8fafc] transition cursor-pointer"
                                x-show="!search || '{{ strtolower($a->name) }} {{ strtolower($a->email) }}'.includes(search.toLowerCase())"
                                @click="document.getElementById('selected-advisor-id').value = '{{ $a->id }}'; document.getElementById('form-add-advisor').submit();">
                                <td class="px-5 py-3.5 text-center">
                                    <input type="radio" name="_select" value="{{ $a->id }}"
                                        class="w-4 h-4 text-accent border-line focus:ring-accent">
                                </td>
                                <td class="px-5 py-3.5 font-medium text-primary">{{ $a->name }}</td>
                                <td class="px-5 py-3.5 text-muted text-xs">{{ $a->email }}</td>
                                <td class="px-5 py-3.5 text-muted">{{ $a->phone ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center text-muted text-sm">Tidak ada advisor tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-800 flex items-start gap-2 mt-4">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Klik baris advisor untuk mengirim permintaan koneksi. Advisor akan menerima notifikasi dan perlu menyetujui.</span>
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
