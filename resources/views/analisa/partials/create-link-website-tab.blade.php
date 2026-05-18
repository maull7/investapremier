@php
    $oldUrls = old('urls', [['label' => '', 'url' => '']]);
    $formUrls = ($editingLink ?? null)
        ? $editingLink->urls->map(fn ($u) => ['label' => $u->label, 'url' => $u->url])->values()->all()
        : $oldUrls;
    if (empty($formUrls)) {
        $formUrls = [['label' => '', 'url' => '']];
    }
    $linkPageUrl = route($linkPageRoute ?? 'user.analisa.create', ['tab' => 'link-website']);
@endphp

<div class="p-6 space-y-6">
    <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-5 text-sm text-indigo-900">
        <p class="font-bold text-base mb-2">Link Website — milik Anda sendiri</p>
        <ol class="list-decimal list-inside space-y-1 text-indigo-800">
            <li>Simpan link situs <strong>apa saja</strong> (bisa lebih dari satu URL per sumber)</li>
            <li>Buka link → unduh file, atau coba <strong>Unduh otomatis</strong></li>
            <li>Pilih file → <strong>Isi Form Otomatis</strong> → tab Input Manual terisi</li>
        </ol>
        <p class="text-xs mt-2 text-indigo-700">Tidak terkait admin. Bebas pilih sumber data sendiri.</p>
    </div>

    @if(session('success') && request('tab') === 'link-website')
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="border border-line rounded-xl overflow-hidden" x-data="{
        urls: @js($formUrls),
        jenisAkses: '{{ old('jenis_akses', $editingLink?->jenis_akses ?? 'login') }}',
        showCreds: {{ ($editingLink ?? null) && $editingLink->jenis_akses !== 'public' ? 'true' : 'false' }},
        addUrl() { this.urls.push({ label: '', url: '' }); },
        removeUrl(i) { if (this.urls.length > 1) this.urls.splice(i, 1); }
    }">
        <div class="px-4 py-3 bg-[#f8fafc] border-b border-line flex justify-between items-center">
            <h4 class="text-sm font-semibold text-primary">{{ ($editingLink ?? null) ? 'Edit link' : 'Tambah link baru' }}</h4>
            @if($editingLink ?? null)
            <a href="{{ $linkPageUrl }}" class="text-xs text-muted hover:text-primary">Batal</a>
            @endif
        </div>
        <form method="POST" class="p-4 space-y-3"
              action="{{ ($editingLink ?? null) ? route($linkRoutes['update'], $editingLink) : route($linkRoutes['store']) }}">
            @csrf
            @if($editingLink ?? null) @method('PUT') @endif

            <div>
                <label class="block text-xs font-semibold text-muted mb-1">Nama sumber *</label>
                <input type="text" name="nama_sumber" value="{{ old('nama_sumber', $editingLink?->nama_sumber) }}" required
                       placeholder="Contoh: PasarDana, IDX, OJK"
                       class="w-full text-sm border border-line rounded-lg px-3 py-2">
            </div>

            <div>
                <div class="flex justify-between mb-2">
                    <label class="text-xs font-semibold text-muted">URL (bisa banyak)</label>
                    <button type="button" @click="addUrl()" class="text-xs text-indigo-600 font-semibold">+ Tambah URL</button>
                </div>
                <template x-for="(item, index) in urls" :key="index">
                    <div class="flex gap-2 mb-2">
                        <input type="text" :name="'urls[' + index + '][label]'" x-model="item.label" placeholder="Label"
                               class="w-28 text-sm border border-line rounded-lg px-2 py-2">
                        <input type="url" :name="'urls[' + index + '][url]'" x-model="item.url" required placeholder="https://..."
                               class="flex-1 text-sm border border-line rounded-lg px-2 py-2">
                        <button type="button" @click="removeUrl(index)" x-show="urls.length > 1" class="text-red-500 px-2">✕</button>
                    </div>
                </template>
                @error('urls')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-muted mb-1">Jenis akses</label>
                <select name="jenis_akses" x-model="jenisAkses" @change="showCreds = jenisAkses !== 'public'"
                        class="w-full text-sm border border-line rounded-lg px-3 py-2">
                    @foreach(\App\Models\DataSourceLink::JENIS_AKSES as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div x-show="showCreds" x-cloak class="grid sm:grid-cols-2 gap-3">
                <input type="text" name="login_username" placeholder="Username" class="text-sm border border-line rounded-lg px-3 py-2" autocomplete="off">
                <input type="password" name="login_password" placeholder="Password" class="text-sm border border-line rounded-lg px-3 py-2" autocomplete="new-password">
            </div>

            <textarea name="catatan" rows="2" placeholder="Catatan (opsional)" class="w-full text-sm border border-line rounded-lg px-3 py-2">{{ old('catatan', $editingLink?->catatan) }}</textarea>

            <button type="submit" class="px-4 py-2 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary/90">
                {{ ($editingLink ?? null) ? 'Simpan perubahan' : 'Simpan link' }}
            </button>
        </form>
    </div>

    @if($dataSourceLinks->isNotEmpty())
    <div class="space-y-3">
        <p class="text-xs font-semibold text-muted uppercase">Link Anda</p>
        @foreach($dataSourceLinks as $link)
        <div class="border border-line rounded-xl p-4">
            <div class="flex flex-wrap justify-between gap-2 mb-2">
                <span class="font-bold text-primary">{{ $link->nama_sumber }}</span>
                <div class="flex gap-2">
                    <a href="{{ route($linkPageRoute ?? 'user.analisa.create', ['tab' => 'link-website', 'edit' => $link->id]) }}" class="text-xs text-muted hover:text-primary">Edit</a>
                    <form method="POST" action="{{ route($linkRoutes['destroy'], $link) }}" onsubmit="return confirm('Hapus link ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-600 hover:underline">Hapus</button>
                    </form>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($link->urls as $urlItem)
                <a href="{{ $urlItem->url }}" target="_blank" rel="noopener"
                   class="inline-flex px-3 py-1.5 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700">
                    {{ $urlItem->label ?: 'Buka' }}
                </a>
                <button type="button" @click="scrapeUrl('{{ addslashes($urlItem->url) }}')" :disabled="webLoading"
                        class="px-3 py-1.5 border border-emerald-300 text-emerald-700 text-xs font-semibold rounded-lg hover:bg-emerald-50 disabled:opacity-50"
                        title="Scrape / download data dari URL ini">
                    <span x-show="!webLoading">⬇ Scrape</span>
                    <span x-show="webLoading">...</span>
                </button>
                @endforeach
                <button type="button" @click="scrapeFromLink({{ $link->id }})" :disabled="webLoading"
                        class="px-3 py-1.5 border border-indigo-300 text-indigo-700 text-xs font-semibold rounded-lg hover:bg-indigo-50 disabled:opacity-50">
                    Unduh otomatis
                </button>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <div class="border-t border-line pt-6 space-y-4">
        <p class="text-sm font-semibold text-primary">Atau langsung upload file (tanpa simpan link)</p>
        <input type="file" accept=".xlsx,.xls,.csv" @change="webFile = $event.target.files[0]"
               class="block w-full text-sm border border-line rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-indigo-600 file:text-white">
        <button type="button" @click="fillFromWebFile()" :disabled="webLoading || !webFile"
                class="px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 disabled:opacity-50">
            <span x-show="!webLoading">Isi Form Otomatis</span>
            <span x-show="webLoading">Memproses...</span>
        </button>
        <p x-show="webMessage" x-text="webMessage" class="text-sm rounded-lg px-4 py-3"
           :class="webOk ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-amber-50 text-amber-800 border border-amber-200'"></p>
    </div>
</div>
