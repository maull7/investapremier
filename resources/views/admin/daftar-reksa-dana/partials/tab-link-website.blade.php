@php
    $oldUrls = old('urls', [['label' => '', 'url' => '']]);
    $formUrls = $editingLink
        ? $editingLink->urls->map(fn ($u) => ['label' => $u->label, 'url' => $u->url])->values()->all()
        : $oldUrls;
    if (empty($formUrls)) {
        $formUrls = [['label' => '', 'url' => '']];
    }
@endphp

{{-- Form Tambah / Edit --}}
<div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden mb-5" x-data="{
    urls: @js($formUrls),
    showCreds: {{ $editingLink && $editingLink->jenis_akses !== 'public' ? 'true' : 'false' }},
    addUrl() { this.urls.push({ label: '', url: '' }); },
    removeUrl(i) { if (this.urls.length > 1) this.urls.splice(i, 1); }
}">
    <div class="px-5 py-4 border-b border-line bg-gradient-to-r from-indigo-600 to-indigo-500 flex items-center justify-between">
        <h2 class="font-bold text-white text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
            {{ $editingLink ? 'Edit Sumber Data' : 'Tambah Sumber Data & Link Website' }}
        </h2>
        @if($editingLink)
        <a href="{{ route('admin.daftar-reksa-dana.index', ['tab' => 'link-website']) }}"
           class="text-xs text-white/80 hover:text-white">Batal edit</a>
        @endif
    </div>
    <div class="p-5">
        <form method="POST"
              action="{{ $editingLink ? route('admin.data-source-links.update', $editingLink) : route('admin.data-source-links.store') }}">
            @csrf
            @if($editingLink) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Nama Sumber *</label>
                    <input type="text" name="nama_sumber" value="{{ old('nama_sumber', $editingLink?->nama_sumber) }}" required
                           placeholder="Contoh: PasarDana, IDX, OJK"
                           class="w-full text-sm border border-line rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Reksa Dana (opsional)</label>
                    <select name="reksa_dana_id" class="w-full text-sm border border-line rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30">
                        <option value="">— Umum / Semua RD —</option>
                        @foreach($reksaDanaList as $rd)
                        <option value="{{ $rd->id }}" @selected(old('reksa_dana_id', $editingLink?->reksa_dana_id) == $rd->id)>{{ $rd->nama_reksa_dana }}</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-muted mt-1">Kosongkan jika link dipakai lintas reksa dana. File upload harus punya kolom <code class="bg-[#f1f5f9] px-0.5 rounded">nama_reksa_dana</code>.</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Jenis Akses *</label>
                    <select name="jenis_akses" x-model="jenisAkses" @change="showCreds = jenisAkses !== 'public'"
                            class="w-full text-sm border border-line rounded-lg px-3 py-2">
                        @foreach(\App\Models\DataSourceLink::JENIS_AKSES as $val => $label)
                        <option value="{{ $val }}" @selected(old('jenis_akses', $editingLink?->jenis_akses ?? 'login') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-init="jenisAkses = '{{ old('jenis_akses', $editingLink?->jenis_akses ?? 'login') }}'">
                    <label class="block text-xs font-semibold text-muted mb-1">Metode Pengambilan *</label>
                    <select name="metode_pengambilan" class="w-full text-sm border border-line rounded-lg px-3 py-2">
                        @foreach(\App\Models\DataSourceLink::METODE as $val => $label)
                        <option value="{{ $val }}" @selected(old('metode_pengambilan', $editingLink?->metode_pengambilan ?? 'manual') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Multiple URLs --}}
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-semibold text-muted">Link Website * <span class="font-normal">(bisa lebih dari satu)</span></label>
                    <button type="button" @click="addUrl()" class="text-xs font-semibold text-primary hover:underline">+ Tambah URL</button>
                </div>
                <template x-for="(item, index) in urls" :key="index">
                    <div class="flex gap-2 mb-2 items-start">
                        <input type="text" :name="'urls[' + index + '][label]'" x-model="item.label"
                               placeholder="Label (opsional)"
                               class="w-36 shrink-0 text-sm border border-line rounded-lg px-3 py-2">
                        <input type="url" :name="'urls[' + index + '][url]'" x-model="item.url" required
                               placeholder="https://..."
                               class="flex-1 text-sm border border-line rounded-lg px-3 py-2">
                        <button type="button" @click="removeUrl(index)" x-show="urls.length > 1"
                                class="shrink-0 p-2 text-red-500 hover:bg-red-50 rounded-lg" title="Hapus URL">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
                @error('urls')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div x-show="showCreds" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 p-4 bg-amber-50/50 border border-amber-100 rounded-xl">
                <p class="md:col-span-2 text-xs text-amber-800">Kredensial disimpan terenkripsi. Kosongkan password saat edit jika tidak ingin mengubah.</p>
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Username / Email Login</label>
                    <input type="text" name="login_username" value="{{ old('login_username') }}" autocomplete="off"
                           class="w-full text-sm border border-line rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-muted mb-1">Password Login</label>
                    <input type="password" name="login_password" autocomplete="new-password"
                           class="w-full text-sm border border-line rounded-lg px-3 py-2">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-semibold text-muted mb-1">Catatan</label>
                <textarea name="catatan" rows="2" class="w-full text-sm border border-line rounded-lg px-3 py-2"
                          placeholder="Instruksi akses, langkah unduh manual, dll.">{{ old('catatan', $editingLink?->catatan) }}</textarea>
            </div>

            <label class="inline-flex items-center gap-2 text-sm mb-4 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $editingLink?->is_active ?? true))
                       class="rounded border-line text-primary focus:ring-primary/30">
                <span class="text-muted">Aktif</span>
            </label>

            <div class="flex gap-2">
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                    {{ $editingLink ? 'Simpan Perubahan' : 'Simpan Sumber Data' }}
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Daftar Sumber --}}
<div class="bg-white rounded-2xl border border-line overflow-hidden shadow-sm mb-5">
    <div class="px-6 py-4 border-b border-line flex flex-wrap items-center justify-between gap-3 bg-gradient-to-r from-indigo-600 to-indigo-500">
        <h2 class="font-bold text-white text-sm">Daftar Sumber Data ({{ $dataSourceLinks->total() }})</h2>
        <form method="GET" action="{{ route('admin.daftar-reksa-dana.index') }}" class="flex flex-wrap gap-2">
            <input type="hidden" name="tab" value="link-website">
            <select name="jenis_akses" class="text-xs border border-white/30 bg-white/10 text-white rounded-lg px-2 py-1.5">
                <option value="" class="text-gray-800">Semua akses</option>
                @foreach(\App\Models\DataSourceLink::JENIS_AKSES as $val => $label)
                <option value="{{ $val }}" @selected(request('jenis_akses') === $val) class="text-gray-800">{{ $label }}</option>
                @endforeach
            </select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari sumber / RD..."
                   class="text-xs border border-white/30 bg-white/10 text-white placeholder-white/50 rounded-lg px-3 py-1.5 w-44">
            <button type="submit" class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold">Filter</button>
        </form>
    </div>

    <div class="divide-y divide-line">
        @forelse($dataSourceLinks as $link)
        <div class="p-5 hover:bg-[#f8fafc] transition-colors">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="font-bold text-primary">{{ $link->nama_sumber }}</h3>
                        @if(!$link->is_active)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-600">Nonaktif</span>
                        @endif
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-700">{{ $link->jenisAksesLabel() }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-purple-100 text-purple-700">{{ $link->metodeLabel() }}</span>
                        @php
                            $syncBadge = match($link->last_sync_status) {
                                'success' => 'bg-green-100 text-green-700',
                                'failed' => 'bg-red-100 text-red-700',
                                default => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $syncBadge }}">Sync: {{ $link->syncStatusLabel() }}</span>
                    </div>
                    @if($link->reksaDana)
                    <p class="text-xs text-muted mt-1">RD: <span class="font-medium text-primary">{{ $link->reksaDana->nama_reksa_dana }}</span></p>
                    @else
                    <p class="text-xs text-muted mt-1">RD: <span class="italic">Umum (semua reksa dana)</span></p>
                    @endif
                    @if($link->last_synced_at)
                    <p class="text-[11px] text-muted mt-0.5">Terakhir sync: {{ $link->last_synced_at->format('d M Y H:i') }} — {{ Str::limit($link->last_sync_message, 80) }}</p>
                    @endif
                    @if($link->catatan)
                    <p class="text-xs text-muted mt-1">{{ $link->catatan }}</p>
                    @endif
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.daftar-reksa-dana.index', ['tab' => 'link-website', 'edit' => $link->id]) }}"
                       class="px-3 py-1.5 text-xs font-semibold border border-line rounded-lg hover:bg-[#f1f5f9] text-muted">Edit</a>
                    <form method="POST" action="{{ route('admin.data-source-links.destroy', $link) }}"
                          onsubmit="return confirm('Hapus sumber data ini beserta semua URL-nya?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 text-xs font-semibold border border-red-200 text-red-600 rounded-lg hover:bg-red-50">Hapus</button>
                    </form>
                </div>
            </div>

            {{-- Multiple URLs list --}}
            <div class="mb-3 pl-3 border-l-2 border-indigo-200 space-y-1.5">
                @foreach($link->urls as $urlItem)
                <div class="flex items-center gap-2 text-sm">
                    @if($urlItem->label)
                    <span class="text-xs font-semibold text-muted shrink-0 w-28">{{ $urlItem->label }}:</span>
                    @endif
                    <a href="{{ $urlItem->url }}" target="_blank" rel="noopener noreferrer"
                       class="text-indigo-600 hover:underline break-all text-xs">{{ $urlItem->url }}</a>
                    <svg class="w-3 h-3 shrink-0 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </div>
                @endforeach
            </div>

            {{-- Upload NAV --}}
            <div class="flex flex-wrap items-center gap-2 pt-3 border-t border-line/60">
                <span class="text-xs text-muted mr-1">Import NAV dari file:</span>
                <form method="POST" action="{{ route('admin.data-source-links.upload', $link) }}" enctype="multipart/form-data" class="flex flex-1 flex-wrap gap-2 items-center min-w-0">
                    @csrf
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                           class="flex-1 min-w-[140px] text-xs border border-line rounded-lg px-2 py-1.5 file:mr-1 file:py-0.5 file:px-2 file:rounded file:border-0 file:text-[10px] file:bg-indigo-50 file:text-indigo-700">
                    <button type="submit" class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700 whitespace-nowrap">Upload & Sync</button>
                </form>
            </div>
        </div>
        @empty
        <div class="px-6 py-12 text-center text-muted">
            <p class="font-medium">Belum ada sumber data</p>
            <p class="text-xs mt-1">Tambahkan PasarDana, IDX, OJK, atau sumber lain dengan satu atau lebih URL</p>
        </div>
        @endforelse
    </div>

    @if($dataSourceLinks->hasPages())
    <div class="px-6 py-4 border-t border-line text-xs text-muted">
        {{ $dataSourceLinks->links() }}
    </div>
    @endif
</div>

{{-- Riwayat Sync --}}
<div class="bg-white rounded-2xl border border-line overflow-hidden shadow-sm">
    <div class="px-6 py-4 border-b border-line bg-[#f8fafc]">
        <h2 class="font-bold text-primary text-sm">Riwayat Sinkronisasi</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#f8fafc] text-left text-muted text-xs uppercase">
                    <th class="px-4 py-3 font-semibold">Waktu</th>
                    <th class="px-4 py-3 font-semibold">Sumber</th>
                    <th class="px-4 py-3 font-semibold">Status</th>
                    <th class="px-4 py-3 font-semibold text-right">Baris</th>
                    <th class="px-4 py-3 font-semibold">Pesan</th>
                    <th class="px-4 py-3 font-semibold">Oleh</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse($syncLogs as $log)
                <tr class="hover:bg-[#f8fafc]">
                    <td class="px-4 py-3 text-xs text-muted whitespace-nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                    <td class="px-4 py-3 text-xs font-medium text-primary">{{ $log->link->nama_sumber ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $log->status === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $log->status }}</span>
                    </td>
                    <td class="px-4 py-3 text-right text-xs">{{ $log->rows_imported }}</td>
                    <td class="px-4 py-3 text-xs text-muted max-w-xs truncate" title="{{ $log->message }}">{{ $log->message }}</td>
                    <td class="px-4 py-3 text-xs text-muted">{{ $log->user->name ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-8 text-center text-muted text-xs">Belum ada riwayat</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($syncLogs->hasPages())
    <div class="px-6 py-3 border-t border-line">{{ $syncLogs->links() }}</div>
    @endif
</div>

<p class="text-xs text-muted mt-4">
    <strong>Format file NAV:</strong> CSV/XLS dengan kolom tanggal (<code>tanggal</code>, <code>date</code>) dan NAV (<code>nav</code>, <code>nab</code>, <code>nab_per_unit</code>).
    Jika sumber tidak terikat ke satu RD, sertakan kolom <code>nama_reksa_dana</code>.
</p>
