@extends('layouts.admin')

@section('title', isset($manager) ? 'Edit Manajer Investasi' : 'Tambah Manajer Investasi')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-muted mb-3">
        <a href="{{ route('admin.investment-managers.index') }}" class="hover:text-primary transition">Manajer Investasi</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-primary font-medium">{{ isset($manager) ? 'Edit' : 'Tambah' }}</span>
    </div>
    <h1 class="text-2xl font-bold text-primary">{{ isset($manager) ? 'Edit Manajer Investasi' : 'Tambah Manajer Investasi' }}</h1>
</div>

<form method="POST"
    action="{{ isset($manager) ? route('admin.investment-managers.update', $manager) : route('admin.investment-managers.store') }}"
    class="max-w-4xl">
    @csrf
    @if(isset($manager)) @method('PUT') @endif

    <div class="bg-white rounded-2xl border border-line shadow-sm p-6 space-y-6">
        <div>
            <x-input-label value="Nama Investment Manager" class="text-sm font-semibold mb-1.5" />
            <input type="text" name="name"
                class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent @error('name') border-red-400 @enderror"
                placeholder="Allianz Global Investors Asset Management Indonesia, PT"
                value="{{ old('name', $manager->name ?? '') }}">
            <x-input-error :messages="$errors->get('name')" class="mt-1 text-xs" />
        </div>

        @if(isset($manager))
        <div>
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-primary text-sm">Data Periode</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                            <th class="px-3 py-2 font-semibold">Periode</th>
                            <th class="px-3 py-2 font-semibold text-right">AUM (Rp)</th>
                            <th class="px-3 py-2 font-semibold text-right">UP</th>
                            <th class="px-3 py-2 font-semibold text-center w-16">Hapus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line" id="periods-wrapper">
                        @foreach($manager->periods as $p)
                        <tr>
                            <td class="px-3 py-2">
                                <input type="date" name="periods[{{ $p->id }}][period_date]"
                                    value="{{ $p->period_date->format('Y-m-d') }}"
                                    class="w-full px-2 py-1.5 border border-line rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" inputmode="decimal" name="periods[{{ $p->id }}][aum]"
                                    value="{{ $p->aum }}"
                                    class="w-full px-2 py-1.5 border border-line rounded-lg text-sm text-right focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                                    placeholder="0">
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" inputmode="decimal" name="periods[{{ $p->id }}][up]"
                                    value="{{ $p->up }}"
                                    class="w-full px-2 py-1.5 border border-line rounded-lg text-sm text-right focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                                    placeholder="0">
                            </td>
                            <td class="px-3 py-2 text-center">
                                <input type="checkbox" name="periods[{{ $p->id }}][_delete]" value="1"
                                    class="rounded border-line text-red-500 focus:ring-red-500">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-muted mt-2">Centang "Hapus" untuk menghapus periode. Untuk menambah periode baru, gunakan import Excel.</p>
        </div>
        @endif
    </div>

    <div class="flex items-center gap-3 mt-5">
        <button type="submit" class="px-5 py-2.5 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent/90 transition shadow-sm shadow-accent/20">
            {{ isset($manager) ? 'Simpan Perubahan' : 'Tambah' }}
        </button>
        <a href="{{ route('admin.investment-managers.index') }}"
            class="px-5 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary hover:border-primary/30 transition">Batal</a>
    </div>
</form>
@endsection
