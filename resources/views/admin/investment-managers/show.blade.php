@extends('layouts.admin')

@section('title', $manager->name . ' - InvestaPremier')

@section('content')
    <div x-data="{
        tab: {{ Js::from(request('tab', 'detail')) }},
        personModal: { open: false, loading: false, error: null, data: null },
        async openPerson(name) {
            this.personModal = { open: true, loading: true, error: null, data: null };
            try {
                const url = '{{ route('admin.investment-person-roles.detail') }}?name=' + encodeURIComponent(name);
                const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const json = await res.json();
                if (!res.ok) {
                    this.personModal.error = json.message || 'Gagal mengambil detail.';
                    return;
                }
                this.personModal.data = json;
            } catch (e) {
                this.personModal.error = e.message;
            } finally {
                this.personModal.loading = false;
            }
        }
    }">

        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-muted mb-3">
                <a href="{{ route('admin.investment-managers.index') }}" class="hover:text-primary transition">Manajer
                    Investasi</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-primary font-medium">{{ $manager->name }}</span>
            </div>
            <h1 class="page-title">{{ $manager->name }}</h1>
        </div>

        {{-- Tabs --}}
        <div class="flex items-center gap-1 mb-6 border-b border-line">
            <button @click="tab = 'detail'"
                :class="tab === 'detail' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
                class="px-5 py-3 text-sm font-semibold border-b-2 transition">
                Detail
            </button>
            <button @click="tab = 'produk'"
                :class="tab === 'produk' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
                class="px-5 py-3 text-sm font-semibold border-b-2 transition">
                Produk
            </button>
            <button @click="tab = 'grafik'"
                :class="tab === 'grafik' ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-primary'"
                class="px-5 py-3 text-sm font-semibold border-b-2 transition">
                Grafik
            </button>
        </div>

        {{-- Tab: Detail --}}
        <div x-show="tab === 'detail'" x-cloak x-data="{
            reksaDanaId: '',
            tahun: '',
            useAi: true,
            loading: false,
            error: null,
            success: null,
            extractUrl: '{{ route('admin.investment-managers.extract-prospektus', $manager) }}',
            saveUrl: '{{ route('admin.investment-managers.save-prospektus', $manager) }}',
            csrfToken: '{{ csrf_token() }}',
            fundsData: {{ Js::from($fundsWithProspektus->map(fn($f) => ['id' => $f->id, 'nama' => $f->nama_reksa_dana, 'years' => $f->documents->pluck('ffs_year')->filter()->unique()->values()])) }},
            get filteredYears() {
                const fund = this.fundsData.find(f => f.id == this.reksaDanaId);
                return fund ? fund.years : [];
            },
            async extract() {
                if (!this.reksaDanaId || !this.tahun) return;
                this.loading = true;
                this.error = null;
                this.success = null;
                try {
                    const params = new URLSearchParams({ reksa_dana_id: this.reksaDanaId, tahun: this.tahun, use_ai: this.useAi });
                    const res = await fetch(this.extractUrl + '?' + params, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });
                    const json = await res.json();
                    if (!res.ok) { this.error = json.error || 'Gagal mengekstrak.'; return; }
                    // langsung simpan beserta sumber prospektus
                    const form = new FormData();
                    form.append('_token', this.csrfToken);
                    form.append('reksa_dana_id', this.reksaDanaId);
                    form.append('tahun', this.tahun);
                    Object.entries(json.data).forEach(([k, v]) => {
                        if (v) {
                            // Clean website: add https:// if missing
                            let val = v;
                            if (k === 'website' && val && !/^https?:\/\//i.test(val)) {
                                val = 'https://' + val;
                            }
                            form.append(k, val);
                        }
                    });
                    const saveRes = await fetch(this.saveUrl, { method: 'POST', body: form, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                    if (!saveRes.ok) {
                        try {
                            const errJson = await saveRes.json();
                            this.error = errJson.errors ? Object.values(errJson.errors).flat().join(', ') : (errJson.error || 'Ekstrak berhasil tapi gagal menyimpan.');
                        } catch(e2) {
                            this.error = 'Ekstrak berhasil tapi gagal menyimpan (HTTP ' + saveRes.status + ').';
                        }
                        return;
                    }
                    const mode = json.ai_used ? 'AI' : 'RegEx';
                    this.success = 'Data berhasil diekstrak (' + mode + ') dan disimpan. Refresh halaman untuk melihat perubahan.';
                } catch (e) { this.error = e.message; } finally { this.loading = false; }
            }
        }">

            {{-- Ekstrak dari Prospektus --}}
            <div class="bg-white rounded-2xl border border-line shadow-sm p-6 mb-6 space-y-4">
                <h2 class="font-bold text-primary text-sm">Ekstrak & Simpan Data dari Prospektus</h2>
                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-xs text-muted mb-1">Reksa Dana</label>
                        <select x-model="reksaDanaId" @change="tahun=''"
                            class="px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 min-w-[280px]">
                            <option value="">-- Pilih Reksa Dana --</option>
                            @foreach ($fundsWithProspektus as $fund)
                                <option value="{{ $fund->id }}">{{ $fund->nama_reksa_dana }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-muted mb-1">Tahun</label>
                        <select x-model="tahun" :disabled="!reksaDanaId"
                            class="px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 disabled:opacity-50">
                            <option value="">-- Tahun --</option>
                            <template x-for="y in filteredYears" :key="y">
                                <option :value="y" x-text="y"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="useAi" x-model="useAi"
                            class="rounded border-gray-300 text-accent focus:ring-accent/30">
                        <label for="useAi" class="text-xs text-muted cursor-pointer">Gunakan AI <span class="text-[10px] opacity-60">(lebih akurat)</span></label>
                    </div>
                    <button @click="extract()" :disabled="!reksaDanaId || !tahun || loading"
                        class="px-4 py-2 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition disabled:opacity-50 flex items-center gap-2">
                        <span x-show="loading"
                            class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span x-text="loading ? 'Memproses...' : 'Ekstrak & Simpan'"></span>
                    </button>
                </div>
                @if ($fundsWithProspektus->isEmpty())
                    <p class="text-sm text-muted">Belum ada reksa dana dengan prospektus. Upload di <a
                            href="{{ route('admin.daftar-reksa-dana.index', ['tab' => 'prospektus-ffs']) }}"
                            class="text-accent hover:underline">Daftar Reksa Dana</a>.</p>
                @endif
                <div x-show="error" x-text="error"
                    class="px-4 py-3 rounded-xl text-sm bg-red-50 border border-red-200 text-red-700"></div>
                <div x-show="success" x-text="success"
                    class="px-4 py-3 rounded-xl text-sm bg-green-50 border border-green-200 text-green-700"></div>
            </div>
            {{-- Informasi MI --}}
            <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light flex items-center justify-between">
                    <h2 class="font-bold text-white text-sm">Informasi Manajer Investasi</h2>
                    @if($manager->source)
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full
                            @if($manager->source === 'prospektus') bg-yellow-300 text-yellow-900
                            @elseif($manager->source === 'pasardana') bg-blue-300 text-blue-900
                            @else bg-gray-300 text-gray-800 @endif">
                            Sumber: {{ ucfirst($manager->source) }}
                            @if($manager->source === 'prospektus' && $manager->prospektusSourceReksaDana)
                                &middot; {{ $manager->prospektus_source_tahun }}
                            @endif
                        </span>
                    @endif
                </div>
                <div class="divide-y divide-line">
                    @foreach ([
            'kode_ojk' => 'Kode OJK',
            'kode_mi' => 'Kode MI',
            'address' => 'Alamat',
            'phone' => 'Nomor Telepon',
            'fax' => 'Fax',
            'email' => 'Email',
            'website' => 'Website',
            'modal_dasar' => 'Modal Dasar',
            'modal_disetor' => 'Modal Disetor',
            'izin_mi' => 'Izin MI',
            'izin_ppe' => 'Izin PPE',
            'izin_pee' => 'Izin PEE',
            'description' => 'Deskripsi',
            'last_updated_at' => 'Tanggal Update',
        ] as $field => $label)
                        <div class="px-6 py-3.5 flex items-start gap-4">
                            <span class="text-xs font-semibold text-muted w-40 shrink-0">{{ $label }}</span>
                            <span class="text-sm whitespace-pre-line">
                                @if ($field === 'email' && $manager->$field)
                                    <a href="mailto:{{ $manager->$field }}"
                                        class="text-accent hover:underline">{{ $manager->$field }}</a>
                                @elseif($field === 'website' && $manager->$field)
                                    <a href="{{ $manager->$field }}" target="_blank"
                                        class="text-accent hover:underline">{{ $manager->$field }}</a>
                                @elseif(in_array($field, ['modal_dasar', 'modal_disetor']) && $manager->$field)
                                    Rp{{ number_format($manager->$field, 0, ',', '.') }}
                                @elseif($field === 'last_updated_at' && $manager->$field)
                                    {{ $manager->$field->format('d M Y') }}
                                @else
                                    {{ $manager->$field ?: '-' }}
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            @foreach($governanceSections as $section)
                @if(!empty($section['items']))
                    <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden mt-6">
                        <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light">
                            <h2 class="font-bold text-white text-sm">{{ $section['label'] }}
                                @if($manager->source === 'prospektus' && $manager->prospektus_source_tahun)
                                    <span class="text-[10px] font-normal opacity-75 ml-2">(Prospektus {{ $manager->prospektus_source_tahun }})</span>
                                @endif
                            </h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                                        <th class="px-4 py-3 font-semibold">Nama / Pihak</th>
                                        <th class="px-4 py-3 font-semibold">Jabatan / Peran</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    @foreach($section['items'] as $item)
                                        <tr class="hover:bg-[#f8fafc] transition-colors">
                                            <td class="px-4 py-3 text-xs font-semibold">
                                                <button type="button"
                                                    @click="openPerson({{ Js::from($item['name']) }})"
                                                    class="text-accent hover:underline text-left">
                                                    {{ $item['name'] }}
                                                </button>
                                            </td>
                                            <td class="px-4 py-3 text-xs text-muted">{{ $item['position'] ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endforeach


        </div>

        {{-- Pasardana Governance --}}
        @if(!empty($pasardanaGovernance['directors']))
        <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden mt-6">
            <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light">
                <h2 class="font-bold text-white text-sm">Direksi (Pasardana)</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide"><th class="px-4 py-3 font-semibold">Nama</th><th class="px-4 py-3 font-semibold">Jabatan</th></tr></thead>
                    <tbody class="divide-y divide-line">
                        @foreach($pasardanaGovernance['directors'] as $item)
                        <tr class="hover:bg-[#f8fafc] transition-colors">
                            <td class="px-4 py-3 text-xs font-semibold">
                                <button type="button" @click="openPerson({{ Js::from($item['name']) }})" class="text-accent hover:underline text-left">{{ $item['name'] }}</button>
                            </td>
                            <td class="px-4 py-3 text-xs text-muted">{{ $item['position'] ?: '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if(!empty($pasardanaGovernance['commissioners']))
        <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden mt-6">
            <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light">
                <h2 class="font-bold text-white text-sm">Komisaris (Pasardana)</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide"><th class="px-4 py-3 font-semibold">Nama</th><th class="px-4 py-3 font-semibold">Jabatan</th></tr></thead>
                    <tbody class="divide-y divide-line">
                        @foreach($pasardanaGovernance['commissioners'] as $item)
                        <tr class="hover:bg-[#f8fafc] transition-colors">
                            <td class="px-4 py-3 text-xs font-semibold">
                                <button type="button" @click="openPerson({{ Js::from($item['name']) }})" class="text-accent hover:underline text-left">{{ $item['name'] }}</button>
                            </td>
                            <td class="px-4 py-3 text-xs text-muted">{{ $item['position'] ?: '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if(!empty($pasardanaGovernance['shareholders']))
        <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden mt-6">
            <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light">
                <h2 class="font-bold text-white text-sm">Pemegang Saham (Pasardana)</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide"><th class="px-4 py-3 font-semibold">Nama</th><th class="px-4 py-3 font-semibold">Jabatan</th></tr></thead>
                    <tbody class="divide-y divide-line">
                        @foreach($pasardanaGovernance['shareholders'] as $item)
                        <tr class="hover:bg-[#f8fafc] transition-colors">
                            <td class="px-4 py-3 text-xs font-semibold">
                                <button type="button" @click="openPerson({{ Js::from($item['name']) }})" class="text-accent hover:underline text-left">{{ $item['name'] }}</button>
                            </td>
                            <td class="px-4 py-3 text-xs text-muted">{{ $item['position'] ?: '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Riwayat Prospektus --}}
        @if($prospektusHistory->isNotEmpty())
        <div class="mt-6 space-y-4">
            <h3 class="font-bold text-primary text-sm">Riwayat Ekstraksi Prospektus</h3>
            @foreach($prospektusHistory as $entry)
            <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
                <div class="px-6 py-3 border-b border-line bg-gray-50 flex items-center justify-between">
                    <h4 class="font-semibold text-sm text-primary">
                        {{ $entry->tahun }}
                        @if($entry->reksaDana)
                            &middot; {{ $entry->reksaDana->nama_reksa_dana }}
                        @endif
                    </h4>
                    <span class="text-[10px] text-muted">{{ $entry->created_at->format('d M Y H:i') }}</span>
                </div>
                <div class="divide-y divide-line text-xs">
                    @php $fields = [
                        'address' => 'Alamat', 'phone' => 'Telepon', 'email' => 'Email', 'website' => 'Website',
                        'commissioner_president' => 'Komisaris Utama', 'commissioners' => 'Komisaris',
                        'director_president' => 'Direktur Utama', 'directors' => 'Direktur',
                        'shareholders' => 'Pemegang Saham',
                        'investment_committee' => 'Komite Investasi',
                        'investment_management_team' => 'Tim Pengelola Investasi',
                        'description' => 'Deskripsi',
                    ]; @endphp
                    @foreach($fields as $key => $label)
                        @php $val = $entry->data[$key] ?? null; @endphp
                        @if(filled($val))
                        <div class="px-6 py-2.5 flex items-start gap-4">
                            <span class="font-semibold text-muted w-36 shrink-0">{{ $label }}</span>
                            <span class="whitespace-pre-line">{{ $val }}</span>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @endif


        {{-- Tab: Produk --}}
        <div x-show="tab === 'produk'" x-cloak>
            <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light">
                    <h2 class="font-bold text-white text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Produk Reksa Dana
                    </h2>
                </div>
                @if ($manager->funds->isEmpty())
                    <div class="py-12 text-center text-muted text-sm">
                        <svg class="w-10 h-10 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        Produk reksa dana belum tersedia.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide">
                                    <th class="px-4 py-3 font-semibold">Kode Reksa Dana</th>
                                    <th class="px-4 py-3 font-semibold">Nama Reksa Dana</th>
                                    <th class="px-4 py-3 font-semibold">Jenis</th>
                                    <th class="px-4 py-3 font-semibold">Kategori</th>
                                    <th class="px-4 py-3 font-semibold text-right">NAB/UP</th>
                                    <th class="px-4 py-3 font-semibold text-right">AUM</th>
                                    <th class="px-4 py-3 font-semibold">Tanggal Data</th>
                                    <th class="px-4 py-3 font-semibold text-right w-20">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @foreach ($manager->funds as $fund)
                                    <tr class="hover:bg-[#f8fafc] transition-colors">
                                        <td class="px-4 py-3 text-xs font-mono">{{ $fund->kode_reksa_dana ?? '-' }}</td>
                                        <td class="px-4 py-3 text-xs font-semibold">{{ $fund->nama_reksa_dana ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-xs">{{ $fund->jenis ?? '-' }}</td>
                                        <td class="px-4 py-3 text-xs">{{ $fund->kategori_label ?? '-' }}</td>
                                        <td class="px-4 py-3 text-xs text-right tabular-nums">
                                            {{ $fund->nab_per_unit ? number_format($fund->nab_per_unit, 4, ',', '.') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-xs text-right tabular-nums text-primary font-semibold">
                                            {{ $fund->aum ? number_format($fund->aum, 2, ',', '.') : '-' }}</td>
                                        <td class="px-4 py-3 text-xs">
                                            {{ $fund->tanggal_nab ? $fund->tanggal_nab->format('d M Y') : '-' }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('admin.daftar-reksa-dana.show', $fund) }}"
                                                class="p-1.5 rounded-lg text-muted hover:text-accent hover:bg-accent/5 transition inline-block"
                                                title="Lihat detail">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Tab: Grafik --}}
        <div x-show="tab === 'grafik'" x-cloak>
            @php
                $rangeOptions = ['1m'=>'1 Bulan','3m'=>'3 Bulan','6m'=>'6 Bulan','ytd'=>'YTD','1y'=>'1 Tahun','3y'=>'3 Tahun','5y'=>'5 Tahun','all'=>'All'];
                $aumPointCount = collect($chartData['aum']['series'])->sum(fn($series) => count($series['data']));
                $upPointCount = collect($chartData['up']['series'])->sum(fn($series) => count($series['data']));
            @endphp

            <div class="mb-4 space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                    @foreach($rangeOptions as $k=>$l)
                        <a href="{{ route('admin.investment-managers.show', ['investmentManager' => $manager, 'tab' => 'grafik', 'range' => $k]) }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $range === $k && !request()->filled('from_date') && !request()->filled('to_date') ? 'bg-primary text-white' : 'border border-line text-muted hover:bg-[#f1f5f9]' }}">{{ $l }}</a>
                    @endforeach
                </div>
                <form method="GET" action="{{ route('admin.investment-managers.show', $manager) }}"
                    class="flex flex-wrap items-end gap-3">
                    <input type="hidden" name="tab" value="grafik">
                    <div>
                        <label class="block text-xs text-muted mb-1">From Date</label>
                        <input type="date" name="from_date" value="{{ request('from_date') }}"
                            class="px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
                    </div>
                    <div>
                        <label class="block text-xs text-muted mb-1">To Date</label>
                        <input type="date" name="to_date" value="{{ request('to_date') }}"
                            class="px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
                    </div>
                    <button type="submit"
                        class="px-4 py-2 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition">Terapkan</button>
                    @if(request()->filled('from_date') || request()->filled('to_date'))
                        <a href="{{ route('admin.investment-managers.show', ['investmentManager' => $manager, 'tab' => 'grafik', 'range' => $range]) }}"
                            class="px-4 py-2 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">Reset</a>
                    @endif
                </form>
            </div>

            @if (!$chartData['has_data'])
                <div class="py-16 text-center text-muted bg-white rounded-2xl border border-line">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <p class="font-medium">Belum terdapat data historis untuk ditampilkan.</p>
                </div>
            @else
                <div class="space-y-6">
                    @if($aumPointCount > 0)
                        <div class="bg-white rounded-2xl border border-line shadow-sm p-5">
                            <h3 class="font-bold text-primary text-sm mb-4">AUM Bulanan</h3>
                            <div id="chartAum" class="min-h-[320px]"></div>
                        </div>
                    @endif
                    @if($upPointCount > 0)
                        <div class="bg-white rounded-2xl border border-line shadow-sm p-5">
                            <h3 class="font-bold text-primary text-sm mb-4">Total UP Bulanan</h3>
                            <div id="chartUp" class="min-h-[320px]"></div>
                        </div>
                    @endif
                </div>

                <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const chartData = @json($chartData);
                        const formatRupiah = value => {
                            const n = Number(value || 0);
                            if (Math.abs(n) >= 1_000_000_000_000) return 'Rp ' + (n / 1_000_000_000_000).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' T';
                            if (Math.abs(n) >= 1_000_000_000) return 'Rp ' + (n / 1_000_000_000).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' M';
                            return 'Rp ' + n.toLocaleString('id-ID', { maximumFractionDigits: 0 });
                        };
                        const formatUnit = value => {
                            const n = Number(value || 0);
                            if (Math.abs(n) >= 1_000_000_000) return (n / 1_000_000_000).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' Miliar';
                            if (Math.abs(n) >= 1_000_000) return (n / 1_000_000).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' Juta';
                            return n.toLocaleString('id-ID', { maximumFractionDigits: 0 });
                        };
                        const options = (series, formatter, csvName) => ({
                            chart: {
                                type: 'line',
                                height: 320,
                                toolbar: { show: true, tools: { download: true, selection: true, zoom: true, zoomin: true, zoomout: true, pan: true, reset: true }, export: { csv: { filename: csvName }, png: { filename: csvName } } },
                                zoom: { enabled: true, type: 'x' }
                            },
                            series,
                            stroke: { curve: 'smooth', width: 2.5 },
                            markers: { size: 3, hover: { size: 5 } },
                            dataLabels: { enabled: false },
                            legend: { show: true, position: 'top', horizontalAlign: 'left' },
                            xaxis: { type: 'datetime', labels: { datetimeUTC: false } },
                            yaxis: { labels: { formatter } },
                            tooltip: { shared: true, x: { format: 'MMM yyyy' }, y: { formatter } },
                            grid: { borderColor: '#e2e8f0' },
                            colors: ['#2563eb', '#059669'],
                        });

                        if (document.getElementById('chartAum')) {
                            new ApexCharts(document.getElementById('chartAum'), options(chartData.aum.series, formatRupiah, 'aum-bulanan-manajer-investasi')).render();
                        }
                        if (document.getElementById('chartUp')) {
                            new ApexCharts(document.getElementById('chartUp'), options(chartData.up.series, formatUnit, 'total-up-bulanan-manajer-investasi')).render();
                        }
                    });
                </script>
            @endif
        </div>


        <div x-show="personModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
            <div class="absolute inset-0 bg-black/40" @click="personModal.open = false"></div>
            <div class="relative bg-white rounded-2xl shadow-xl border border-line w-full max-w-3xl max-h-[85vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-line flex items-center justify-between">
                    <div>
                        <p class="text-xs text-muted">Detail keterkaitan</p>
                        <h2 class="font-bold text-primary" x-text="personModal.data?.name || 'Memuat...'"></h2>
                    </div>
                    <button type="button" @click="personModal.open = false" class="text-muted hover:text-primary text-xl leading-none">&times;</button>
                </div>
                <div class="p-6 space-y-6">
                    <div x-show="personModal.loading" class="text-sm text-muted">Memuat data...</div>
                    <div x-show="personModal.error" x-text="personModal.error" class="px-4 py-3 rounded-xl text-sm bg-red-50 border border-red-200 text-red-700"></div>
                    <template x-if="personModal.data">
                        <div class="space-y-6">
                            <div>
                                <h3 class="font-bold text-primary text-sm mb-3">Reksa Dana yang Pernah Diikuti</h3>
                                <template x-if="personModal.data.funds.length === 0">
                                    <p class="text-sm text-muted">Belum ada data Reksa Dana terkait.</p>
                                </template>
                                <div class="overflow-x-auto" x-show="personModal.data.funds.length > 0">
                                    <table class="w-full text-sm">
                                        <thead><tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide"><th class="px-3 py-2">Reksa Dana</th><th class="px-3 py-2">Kode</th><th class="px-3 py-2">Peran</th><th class="px-3 py-2">Jabatan</th><th class="px-3 py-2">Sumber</th></tr></thead>
                                        <tbody class="divide-y divide-line">
                                            <template x-for="row in personModal.data.funds" :key="row.name + row.role + row.position">
                                                <tr><td class="px-3 py-2 font-semibold" x-text="row.name"></td><td class="px-3 py-2 font-mono text-xs" x-text="row.code || '-'"></td><td class="px-3 py-2" x-text="row.role || '-'"></td><td class="px-3 py-2 text-muted" x-text="row.position || '-'"></td><td class="px-3 py-2 text-xs text-muted" x-text="row.source || '-'"></td></tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-bold text-primary text-sm mb-3">Manajer Investasi Terkait</h3>
                                <template x-if="personModal.data.managers.length === 0">
                                    <p class="text-sm text-muted">Belum ada data Manajer Investasi terkait.</p>
                                </template>
                                <div class="overflow-x-auto" x-show="personModal.data.managers.length > 0">
                                    <table class="w-full text-sm">
                                        <thead><tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase tracking-wide"><th class="px-3 py-2">Manajer Investasi</th><th class="px-3 py-2">Peran</th><th class="px-3 py-2">Jabatan</th><th class="px-3 py-2">Sumber</th></tr></thead>
                                        <tbody class="divide-y divide-line">
                                            <template x-for="row in personModal.data.managers" :key="row.name + row.role + row.position">
                                                <tr><td class="px-3 py-2 font-semibold" x-text="row.name"></td><td class="px-3 py-2" x-text="row.role || '-'"></td><td class="px-3 py-2 text-muted" x-text="row.position || '-'"></td><td class="px-3 py-2 text-xs text-muted" x-text="row.source || '-'"></td></tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-bold text-primary text-sm mb-3">Berita Utama Terkait</h3>
                                <template x-if="personModal.data.news.length === 0">
                                    <p class="text-sm text-muted">Belum ada berita terkait.</p>
                                </template>
                                <div class="space-y-2" x-show="personModal.data.news.length > 0">
                                    <template x-for="item in personModal.data.news" :key="item.url || item.title">
                                        <a :href="item.url" target="_blank" class="block border border-line rounded-xl px-4 py-3 hover:border-accent transition">
                                            <p class="text-sm font-semibold text-primary" x-text="item.title"></p>
                                            <p class="text-xs text-muted mt-1"><span x-text="item.source || '-'"></span> <span x-show="item.published_at">-</span> <span x-text="item.published_at || ''"></span></p>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>


    </div>
@endsection
