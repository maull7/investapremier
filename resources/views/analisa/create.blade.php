@extends($formRoutes['layout'] ?? 'layouts.user')

@section('content')
    <div class="max-w-5xl" x-data="analisaForm()">
        <div class="mb-6">
            <h1 class="text-xl font-bold text-primary">Submit Analisa Reksa Dana</h1>
            <p class="text-sm text-muted mt-0.5">Isi data secara manual, upload Excel, atau ekstrak dari PDF FFS</p>
        </div>

        @if ($errors->any())
            @php
                $isLinkTab = request('tab') === 'link-website' || old('input_mode') === 'link-website';
                $displayErrors = $isLinkTab
                    ? collect($errors->messages())->filter(fn ($_, $key) => in_array($key, ['urls', 'nama_sumber', 'jenis_akses', 'login_username', 'login_password', 'catatan']) || str_starts_with($key, 'urls.'))->flatten()
                    : $errors->all();
            @endphp
            @if($displayErrors->isNotEmpty())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm mb-6">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($displayErrors as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        @endif

        <form id="analisa-form" method="POST" action="{{ $formRoutes['store'] }}" enctype="multipart/form-data" class="space-y-6"
              @submit="if (mode === 'link-website') { $event.preventDefault(); webMessage = 'Selesaikan langkah di tab Link Website: unduh file lalu klik Isi Form Otomatis. Setelah itu submit dari tab Input Manual.'; webOk = false; }">
            @csrf
            <input type="hidden" name="input_mode" :value="mode === 'link-website' ? 'manual' : mode">
            <input type="hidden" name="pdf_file" x-model="pdfFile">

            {{-- Info Dasar --}}
            <div class="bg-white rounded-xl border border-line p-6 space-y-4" x-show="mode !== 'link-website'" x-cloak>
                <h3 class="font-semibold text-primary">Informasi Reksa Dana</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="nama_reksa_dana" value="Nama Reksa Dana *" />
                        <x-text-input id="nama_reksa_dana" name="nama_reksa_dana" type="text" class="mt-1 block w-full"
                            value="{{ old('nama_reksa_dana') }}" x-bind:required="mode !== 'link-website'" />
                        <x-input-error :messages="$errors->get('nama_reksa_dana')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="jenis_reksa_dana" value="Jenis Reksa Dana *" />
                        <select id="jenis_reksa_dana" name="jenis_reksa_dana"
                            class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm"
                            x-bind:required="mode !== 'link-website'">
                            <option value="">Pilih Jenis</option>
                            @foreach (['Saham', 'Pendapatan Tetap', 'Campuran', 'Pasar Uang'] as $j)
                                <option value="{{ $j }}" {{ old('jenis_reksa_dana') === $j ? 'selected' : '' }}>
                                    {{ $j }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('jenis_reksa_dana')" class="mt-1" />
                    </div>

                </div>
            </div>

            {{-- Tab Pilih Mode --}}
            <div class="bg-white rounded-xl border border-line overflow-hidden">
                <div class="flex border-b border-line">
                    <button type="button" @click="mode='manual'"
                        :class="mode === 'manual' ? 'border-b-2 border-primary text-primary font-semibold' :
                            'text-muted hover:text-primary'"
                        class="px-6 py-3.5 text-sm transition">
                        Input Manual
                    </button>
                    <button type="button" @click="mode='excel'"
                        :class="mode === 'excel' ? 'border-b-2 border-primary text-primary font-semibold' :
                            'text-muted hover:text-primary'"
                        class="px-6 py-3.5 text-sm transition">
                        Upload Excel
                    </button>
                    <button type="button" @click="mode='pdf'"
                        :class="mode === 'pdf' ? 'border-b-2 border-primary text-primary font-semibold' :
                            'text-muted hover:text-primary'"
                        class="px-6 py-3.5 text-sm transition">
                        PDF FFS
                    </button>
                    <button type="button" @click="mode='ai'"
                        :class="mode === 'ai' ? 'border-b-2 border-primary text-primary font-semibold' :
                            'text-muted hover:text-primary'"
                        class="px-6 py-3.5 text-sm transition">
                        Analisa AI
                    </button>
                    <button type="button" @click="mode='ai-plus'"
                        :class="mode === 'ai-plus' ? 'border-b-2 border-primary text-primary font-semibold' :
                            'text-muted hover:text-primary'"
                        class="px-6 py-3.5 text-sm transition">
                        Analisa AI Plus
                    </button>
                    <button type="button" @click="mode='link-website'"
                        :class="mode === 'link-website' ? 'border-b-2 border-indigo-600 text-indigo-600 font-semibold' :
                            'text-muted hover:text-primary'"
                        class="px-6 py-3.5 text-sm transition whitespace-nowrap">
                        Link Website
                    </button>
                </div>

                {{-- TAB: MANUAL --}}
                <div x-show="mode==='manual'" class="p-6 space-y-8">

                    {{-- Sektor --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="total_aum" value="Total AUM (Rp)" />
                            <x-text-input id="total_aum" name="total_aum" type="number" step="0.01"
                                class="mt-1 block w-full" value="{{ old('total_aum') }}" />
                        </div>
                        <div>
                            <x-input-label for="total_marcap_10_efek" value="Total MarCap 10 Efek Terbesar (Rp)" />
                            <x-text-input id="total_marcap_10_efek" name="total_marcap_10_efek" type="number"
                                step="0.01" class="mt-1 block w-full" value="{{ old('total_marcap_10_efek') }}" />
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-primary text-sm">Komposisi Sektor</h4>
                            <button type="button" @click="addRow('sektor')" class="text-xs text-primary hover:underline">+
                                Tambah Baris</button>
                        </div>
                        <div class="space-y-2">
                            <template x-for="(row, i) in sektor" :key="i">
                                <div class="flex gap-2 items-center">
                                    <input type="text" :name="`sektor[${i}][nama_sektor]`" x-model="row.nama_sektor"
                                        placeholder="Nama Sektor"
                                        class="flex-1 border-gray-300 rounded-lg text-sm px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20" />
                                    <input type="number" :name="`sektor[${i}][bobot]`" x-model="row.bobot"
                                        placeholder="Bobot %" step="0.01"
                                        class="w-28 border-gray-300 rounded-lg text-sm px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20" />
                                    <button type="button" @click="removeRow('sektor', i)"
                                        class="text-red-400 hover:text-red-600 px-1">✕</button>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Efek --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-primary text-sm">Daftar Efek</h4>
                            <button type="button" @click="addRow('efek')" class="text-xs text-primary hover:underline">+
                                Tambah Baris</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-[#f8fafc]">
                                    <tr>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Kode</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Nama Efek</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Sektor</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Bobot %</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Kontribusi % IHSG
                                        </th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Market Cap</th>
                                        <th class="text-center px-2 py-2 text-xs font-semibold text-muted">Top 10</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    <template x-for="(row, i) in efek" :key="i">
                                        <tr>
                                            <td class="px-1 py-1"><input type="text" :name="`efek[${i}][kode_efek]`"
                                                    x-model="row.kode_efek" placeholder="BBCA"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="text" :name="`efek[${i}][nama_efek]`"
                                                    x-model="row.nama_efek" placeholder="Nama Efek"
                                                    class="w-40 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="text" :name="`efek[${i}][sektor]`"
                                                    x-model="row.sektor" placeholder="Sektor"
                                                    class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`efek[${i}][bobot]`"
                                                    x-model="row.bobot" step="0.01"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number"
                                                    :name="`efek[${i}][kontribusi_kinerja]`"
                                                    x-model="row.kontribusi_kinerja" step="0.0001"
                                                    class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`efek[${i}][market_cap]`"
                                                    x-model="row.market_cap" step="1"
                                                    class="w-32 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1 text-center"><input type="checkbox"
                                                    :name="`efek[${i}][top_10]`" x-model="row.top_10" value="1"
                                                    class="rounded border-gray-300 text-primary focus:ring-primary" /></td>
                                            <td class="px-1 py-1"><button type="button" @click="removeRow('efek', i)"
                                                    class="text-red-400 hover:text-red-600 text-xs">✕</button></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Kinerja Bulanan --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-primary text-sm">Kinerja Bulanan <span
                                    class="text-xs font-normal text-muted">(min. 2 bulan)</span></h4>
                            <button type="button" @click="addRow('kinerja')"
                                class="text-xs text-primary hover:underline">+ Tambah Baris</button>
                        </div>
                        <div class="space-y-2">
                            <template x-for="(row, i) in kinerja" :key="i">
                                <div class="flex gap-2 items-center">
                                    <input type="month" :name="`kinerja[${i}][periode]`" x-model="row.periode"
                                        class="border-gray-300 rounded-lg text-sm px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20" />
                                    <input type="number" :name="`kinerja[${i}][return_pct]`" x-model="row.return_pct"
                                        placeholder="Return %" step="0.0001"
                                        class="w-36 border-gray-300 rounded-lg text-sm px-3 py-2 focus:border-primary focus:ring focus:ring-primary/20" />
                                    <button type="button" @click="removeRow('kinerja', i)"
                                        class="text-red-400 hover:text-red-600 px-1">✕</button>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Obligasi --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-primary text-sm">Obligasi</h4>
                            <button type="button" @click="addRow('obligasi')"
                                class="text-xs text-primary hover:underline">+ Tambah Baris</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-[#f8fafc]">
                                    <tr>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Kode</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Nama Obligasi</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Bobot %</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Durasi (thn)</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Rating</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    <template x-for="(row, i) in obligasi" :key="i">
                                        <tr>
                                            <td class="px-1 py-1"><input type="text"
                                                    :name="`obligasi[${i}][kode_obligasi]`" x-model="row.kode_obligasi"
                                                    placeholder="FR0091"
                                                    class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="text"
                                                    :name="`obligasi[${i}][nama_obligasi]`" x-model="row.nama_obligasi"
                                                    placeholder="Nama Obligasi"
                                                    class="w-48 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`obligasi[${i}][bobot]`"
                                                    x-model="row.bobot" step="0.01"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`obligasi[${i}][durasi]`"
                                                    x-model="row.durasi" step="0.01"
                                                    class="w-24 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1">
                                                <select :name="`obligasi[${i}][rating]`" x-model="row.rating"
                                                    class="border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20">
                                                    <option value="">-</option>
                                                    @foreach (['AAA', 'AA+', 'AA', 'AA-', 'A+', 'A', 'A-', 'BBB+', 'BBB', 'BBB-', 'BB', 'B', 'CCC', 'D'] as $r)
                                                        <option value="{{ $r }}">{{ $r }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="px-1 py-1"><button type="button"
                                                    @click="removeRow('obligasi', i)"
                                                    class="text-red-400 hover:text-red-600 text-xs">✕</button></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Bank --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-primary text-sm">Bank</h4>
                            <button type="button" @click="addRow('bank')" class="text-xs text-primary hover:underline">+
                                Tambah Baris</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-[#f8fafc]">
                                    <tr>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Nama Bank</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Bobot %</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">CAR %</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">NPL %</th>
                                        <th class="text-left px-2 py-2 text-xs font-semibold text-muted">Klasifikasi KBMI
                                        </th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    <template x-for="(row, i) in bank" :key="i">
                                        <tr>
                                            <td class="px-1 py-1"><input type="text" :name="`bank[${i}][nama_bank]`"
                                                    x-model="row.nama_bank" placeholder="Nama Bank"
                                                    class="w-36 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`bank[${i}][bobot]`"
                                                    x-model="row.bobot" step="0.01"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`bank[${i}][car]`"
                                                    x-model="row.car" step="0.01"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1"><input type="number" :name="`bank[${i}][npl]`"
                                                    x-model="row.npl" step="0.01"
                                                    class="w-20 border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20" />
                                            </td>
                                            <td class="px-1 py-1">
                                                <select :name="`bank[${i}][klasifikasi_risiko]`"
                                                    x-model="row.klasifikasi_risiko"
                                                    class="border-gray-300 rounded text-xs px-2 py-1.5 focus:border-primary focus:ring focus:ring-primary/20">
                                                    <option value="">-</option>
                                                    <option value="Rendah">Rendah</option>
                                                    <option value="Sedang">Sedang</option>
                                                    <option value="Tinggi">Tinggi</option>
                                                </select>
                                            </td>
                                            <td class="px-1 py-1"><button type="button" @click="removeRow('bank', i)"
                                                    class="text-red-400 hover:text-red-600 text-xs">✕</button></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- TAB: EXCEL --}}
                <div x-show="mode==='excel'" class="p-6 space-y-5">
                    <div
                        class="flex items-center gap-3 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            Download template Excel terlebih dahulu, isi data sesuai format, lalu upload kembali.
                            <a href="{{ $formRoutes['template'] }}" class="font-semibold underline ml-1">Download
                                Template</a>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="file_excel" value="Upload File Excel (.xlsx)" />
                        <input id="file_excel" name="file_excel" type="file" accept=".xlsx,.xls"
                            class="mt-1 block w-full text-sm text-muted border border-gray-300 rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-primary file:text-white hover:file:bg-primary/90 cursor-pointer" />
                        <x-input-error :messages="$errors->get('file_excel')" class="mt-1" />
                        <p class="text-xs text-muted mt-1">Format: .xlsx atau .xls, maks 5MB. Sheet: Sektor, Efek, Kinerja,
                            Obligasi, Bank.</p>
                    </div>
                </div>

                {{-- TAB: PDF FFS --}}
                <div x-show="mode==='pdf'" class="p-6 space-y-5">
                    <div
                        class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-lg text-sm text-emerald-700">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <div>
                            Upload PDF Fund Fact Sheet (FFS) untuk mengekstrak data secara otomatis.
                            Data akan mengisi form di tab <strong>Input Manual</strong>.
                        </div>
                    </div>

                    <div>
                        <x-input-label for="file_pdf" value="Upload File PDF FFS" />
                        <div class="mt-1 flex items-center gap-3">
                            <input id="file_pdf" type="file" accept=".pdf" @change="parsePdf($event)"
                                class="block w-full text-sm text-muted border border-gray-300 rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-primary file:text-white hover:file:bg-primary/90 cursor-pointer" />
                        </div>
                        <p class="text-xs text-muted mt-1">Format: PDF, maks 10MB. Data akan diekstrak dari Fund Fact
                            Sheet.</p>
                    </div>

                    <div x-show="pdfLoading" class="flex items-center gap-2 text-sm text-muted">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                            </path>
                        </svg>
                        <span>Memproses PDF dengan AI... harap tunggu (10–30 detik).</span>
                    </div>

                    <div x-show="pdfResult" class="text-sm space-y-2">
                        <div :class="pdfSuccess ? 'p-3 bg-green-50 border border-green-200 rounded-lg text-green-700' : 'p-3 bg-red-50 border border-red-200 rounded-lg text-red-700'">
                            <span x-text="pdfResult"></span>
                        </div>
                        <p x-show="pdfSuccess" class="text-xs text-muted">Silakan cek tab <strong>Input Manual</strong> untuk melihat dan
                            mengedit data yang telah diekstrak.</p>
                    </div>
                </div>

                @include('analisa.partials.create-ai-tabs')
            </div>

            <input type="hidden" name="ai_narasi" :value="aiResult?.raw || ''">
            <input type="hidden" name="ai_output" :value="aiResult ? JSON.stringify(aiResult.parsed || {}) : ''">
            <input type="hidden" name="ai_narasi_plus" :value="aiPlusResult?.raw || ''">
            <input type="hidden" name="ai_output_plus" :value="aiPlusResult ? JSON.stringify(aiPlusResult.parsed || {}) : ''">

            <div class="flex items-center gap-3" x-show="mode !== 'link-website'" x-cloak>
                <x-primary-button>Submit Analisa</x-primary-button>
                <a href="{{ $formRoutes['cancel'] }}"
                    class="px-4 py-2 text-sm font-medium text-muted border border-line rounded-lg hover:bg-[#f1f5f9] transition">Batal</a>
            </div>
        </form>

        {{-- Tab Link Website di luar form analisa (hindari validasi nama/jenis RD) --}}
        <div x-show="mode === 'link-website'" x-cloak
             class="bg-white rounded-xl border border-line overflow-hidden mt-6 shadow-sm">
            @include('analisa.partials.create-link-website-tab')
        </div>
    </div>

    @push('scripts')
        <script>
            function analisaForm() {
                @php
                    $oldSektor   = old('sektor',   [['nama_sektor' => '', 'bobot' => '']]);
                    $oldEfek     = old('efek',     [['kode_efek' => '', 'nama_efek' => '', 'sektor' => '', 'bobot' => '', 'kontribusi_kinerja' => '', 'market_cap' => '', 'top_10' => false]]);
                    $oldKinerja  = old('kinerja',  [['periode' => '', 'return_pct' => ''], ['periode' => '', 'return_pct' => '']]);
                    $oldObligasi = old('obligasi', [['kode_obligasi' => '', 'nama_obligasi' => '', 'bobot' => '', 'durasi' => '', 'rating' => '']]);
                    $oldBank     = old('bank',     [['nama_bank' => '', 'bobot' => '', 'car' => '', 'npl' => '', 'klasifikasi_risiko' => '']]);
                    // Normalize top_10 checkbox (submitted as "1" string or absent)
                    $oldEfek = array_map(function($e) {
                        $e['top_10'] = !empty($e['top_10']);
                        return $e;
                    }, $oldEfek);

                    $plusLabels = [
                        'aum'     => 'Total AUM',
                        'marcap'  => 'Total MarCap 10 efek terbesar',
                        'sektor'  => 'Komposisi sektor (minimal 1 baris dengan bobot %)',
                        'efek'    => 'Daftar efek (minimal 1 baris: kode, nama, bobot %)',
                        'kinerja' => 'Kinerja bulanan (minimal 2 bulan dengan return %)',
                    ];
                @endphp

                return {
                    mode: @json(old('input_mode', request('tab') === 'link-website' ? 'link-website' : 'manual')),
                    webLoading: false,
                    webFile: null,
                    webMessage: '',
                    webOk: false,
                    parseWebFileUrl: @json($formRoutes['parse_web_file']),
                    scrapeWebUrl: @json($formRoutes['scrape_web']),
                    pdfLoading: false,
                    pdfResult: '',
                    pdfSuccess: false,
                    pdfFile: '',
                    aiLoading: false,
                    aiPlusLoading: false,
                    aiError: '',
                    aiPlusError: '',
                    aiResult: null,
                    aiPlusResult: null,
                    previewAiUrl: @json($formRoutes['preview_ai']),
                    previewAiPlusUrl: @json($formRoutes['preview_ai_plus']),
                    plusRequiredLabels: @json($plusLabels),
                    sektor: @json($oldSektor),
                    efek: @json($oldEfek),
                    kinerja: @json($oldKinerja),
                    obligasi: @json($oldObligasi),
                    bank: @json($oldBank),

                    addRow(type) {
                        const defaults = {
                            sektor: {
                                nama_sektor: '',
                                bobot: ''
                            },
                            efek: {
                                kode_efek: '',
                                nama_efek: '',
                                sektor: '',
                                bobot: '',
                                kontribusi_kinerja: '',
                                market_cap: '',
                                top_10: false
                            },
                            kinerja: {
                                periode: '',
                                return_pct: ''
                            },
                            obligasi: {
                                kode_obligasi: '',
                                nama_obligasi: '',
                                bobot: '',
                                durasi: '',
                                rating: ''
                            },
                            bank: {
                                nama_bank: '',
                                bobot: '',
                                car: '',
                                npl: '',
                                klasifikasi_risiko: ''
                            },
                        };
                        this[type].push({
                            ...defaults[type]
                        });
                    },

                    removeRow(type, index) {
                        if (this[type].length > 1) this[type].splice(index, 1);
                    },

                    analisaFormEl() {
                        return this.$root.querySelector('#analisa-form') || this.$root.querySelector('form');
                    },

                    buildFormPayload() {
                        const fd = new FormData(this.analisaFormEl());
                        const payload = new FormData();
                        ['nama_reksa_dana', 'jenis_reksa_dana', 'total_aum', 'total_marcap_10_efek'].forEach(k => {
                            payload.append(k, fd.get(k) ?? '');
                        });
                        for (const [key, val] of fd.entries()) {
                            if (key.startsWith('sektor[') || key.startsWith('efek[') || key.startsWith('kinerja[') || key.startsWith('obligasi[') || key.startsWith('bank[')) {
                                payload.append(key, val);
                            }
                        }
                        payload.append('_token', fd.get('_token'));
                        return payload;
                    },

                    validateAiBasics(fd) {
                        if (!String(fd.get('nama_reksa_dana') || '').trim()) {
                            return 'Isi Nama Reksa Dana di bagian Informasi Reksa Dana (atas form) terlebih dahulu.';
                        }
                        if (!fd.get('jenis_reksa_dana')) {
                            return 'Pilih Jenis Reksa Dana terlebih dahulu.';
                        }
                        return null;
                    },

                    parseJsonError(resp) {
                        if (resp.errors) {
                            return Object.values(resp.errors).flat().join(' ');
                        }
                        return resp.message || 'Gagal memproses';
                    },

                    runAiPreview() {
                        const fd = new FormData(this.analisaFormEl());
                        const basicErr = this.validateAiBasics(fd);
                        if (basicErr) {
                            this.aiError = basicErr;
                            return;
                        }
                        this.aiLoading = true;
                        this.aiError = '';
                        fetch(this.previewAiUrl, { method: 'POST', body: this.buildFormPayload(), headers: { 'Accept': 'application/json' } })
                            .then(async r => {
                                const resp = await r.json();
                                if (!r.ok || !resp.success) {
                                    this.aiError = this.parseJsonError(resp);
                                    return;
                                }
                                this.aiResult = resp.data;
                            })
                            .catch(e => { this.aiError = e.message || 'Gagal memproses'; })
                            .finally(() => { this.aiLoading = false; });
                    },

                    validatePlusManualData() {
                        const hasSektor = this.sektor.some(r =>
                            String(r.nama_sektor || '').trim() !== '' && r.bobot !== '' && r.bobot != null
                        );
                        const hasEfek = this.efek.some(r =>
                            String(r.kode_efek || '').trim() !== '' &&
                            String(r.nama_efek || '').trim() !== '' &&
                            r.bobot !== '' && r.bobot != null
                        );
                        if (!hasSektor && !hasEfek) {
                            return this.msgPlusIncomplete;
                        }
                        return null;
                    },

                    runAiPlusPreview() {
                        const fd = new FormData(this.analisaFormEl());
                        const basicErr = this.validateAiBasics(fd);
                        if (basicErr) {
                            this.aiPlusError = basicErr;
                            return;
                        }
                        const plusErr = this.validatePlusManualData();
                        if (plusErr) {
                            this.aiPlusError = plusErr;
                            return;
                        }
                        this.aiPlusLoading = true;
                        this.aiPlusError = '';
                        fetch(this.previewAiPlusUrl, { method: 'POST', body: this.buildFormPayload(), headers: { 'Accept': 'application/json' } })
                            .then(async r => {
                                const resp = await r.json();
                                if (!r.ok || !resp.success) {
                                    if (resp.missing?.length) {
                                        this.aiPlusError = resp.message || this.plusIncompleteMessage();
                                    } else {
                                        this.aiPlusError = this.parseJsonError(resp);
                                    }
                                    return;
                                }
                                this.aiPlusResult = resp.data;
                            })
                            .catch(e => { this.aiPlusError = e.message || 'Gagal memproses'; })
                            .finally(() => { this.aiPlusLoading = false; });
                    },

                    applyAiToManual() {
                        if (!this.aiResult?.parsed) {
                            alert('Jalankan Analisa AI terlebih dahulu.');
                            return;
                        }
                        const p = this.aiResult.parsed;
                        const form = this.analisaFormEl();
                        if (p.alokasi_aset?.length) {
                            this.sektor = p.alokasi_aset.map(a => ({
                                nama_sektor: a.kategori || '',
                                bobot: a.persentase ?? '',
                            }));
                        }
                        if (p.daftar_efek?.length) {
                            const sorted = [...p.daftar_efek].sort((a, b) => (b.bobot || 0) - (a.bobot || 0));
                            this.efek = sorted.map((e, i) => ({
                                kode_efek: e.kode_efek || '',
                                nama_efek: e.nama_efek || '',
                                sektor: e.sektor || '',
                                bobot: e.bobot ?? '',
                                kontribusi_kinerja: e.kontribusi_kinerja ?? '',
                                market_cap: e.market_cap ?? '',
                                top_10: i < 10,
                            }));
                        }
                        if (form && !form.total_aum?.value && p.total_aum) {
                            form.total_aum.value = p.total_aum;
                        }
                        if (form && !form.total_marcap_10_efek?.value && p.total_marcap_10_efek) {
                            form.total_marcap_10_efek.value = p.total_marcap_10_efek;
                        }
                        this.mode = 'manual';
                        alert('Data Analisa AI telah diterapkan ke Input Manual. Silakan review sebelum submit.');
                    },

                    applyExtractedData(data) {
                        const fields = {
                            nama_reksa_dana: 'nama_reksa_dana',
                            jenis_reksa_dana: 'jenis_reksa_dana',
                            total_aum: 'total_aum',
                            total_marcap_10_efek: 'total_marcap_10_efek',
                        };
                        for (const [key, id] of Object.entries(fields)) {
                            const el = document.getElementById(id);
                            if (data[key] && el) el.value = data[key];
                        }
                        if (data.sektor?.length) this.sektor = data.sektor;
                        if (data.efek?.length) {
                            this.efek = data.efek.map(e => ({
                                ...e,
                                top_10: e.top_10 === 'Ya' || e.top_10 === true,
                            }));
                        }
                        if (data.kinerja?.length >= 2) {
                            this.kinerja = data.kinerja;
                        } else if (data.kinerja?.length === 1) {
                            this.kinerja = [...data.kinerja, { periode: '', return_pct: '' }];
                        }
                        if (data.obligasi?.length) this.obligasi = data.obligasi;
                        if (data.bank?.length) this.bank = data.bank;
                        this.mode = 'manual';
                    },

                    fillFromWebFile() {
                        if (!this.webFile) {
                            alert('Pilih file unduhan dari situs terlebih dahulu.');
                            return;
                        }
                        this.webLoading = true;
                        this.webMessage = '';
                        const formData = new FormData();
                        formData.append('file', this.webFile);
                        formData.append('_token', this.analisaFormEl().querySelector('input[name="_token"]').value);
                        fetch(this.parseWebFileUrl, {
                            method: 'POST',
                            headers: { Accept: 'application/json' },
                            body: formData,
                        })
                            .then(res => res.json().then(body => ({ ok: res.ok, body })))
                            .then(({ ok, body }) => {
                                this.webLoading = false;
                                if (!ok || !body.success) {
                                    this.webOk = false;
                                    this.webMessage = body.message || 'Gagal membaca file.';
                                    return;
                                }
                                this.applyExtractedData(body.data);
                                this.webOk = true;
                                this.webMessage = body.message + ' Data sudah di tab Input Manual — lengkapi Jenis RD jika perlu, lalu Submit Analisa.';
                            })
                            .catch(() => {
                                this.webLoading = false;
                                this.webOk = false;
                                this.webMessage = 'Gagal memproses file.';
                            });
                    },

                    scrapeFromLink(linkId) {
                        this.webLoading = true;
                        this.webMessage = '';
                        const formData = new FormData();
                        formData.append('data_source_link_id', linkId);
                        formData.append('_token', this.analisaFormEl().querySelector('input[name="_token"]').value);
                        fetch(this.scrapeWebUrl, {
                            method: 'POST',
                            headers: { Accept: 'application/json' },
                            body: formData,
                        })
                            .then(res => res.json().then(body => ({ ok: res.ok, body })))
                            .then(({ ok, body }) => {
                                this.webLoading = false;
                                if (!ok || !body.success) {
                                    this.webOk = false;
                                    this.webMessage = body.message || 'Unduh otomatis gagal. Unduh manual dari link, lalu pilih file di bawah.';
                                    return;
                                }
                                this.applyExtractedData(body.data);
                                this.webOk = true;
                                this.webMessage = body.message + ' Data sudah di tab Input Manual — lengkapi Jenis RD jika perlu, lalu Submit Analisa.';
                            })
                            .catch(() => {
                                this.webLoading = false;
                                this.webOk = false;
                                this.webMessage = 'Unduh otomatis gagal. Gunakan link → unduh manual → Isi Form Otomatis.';
                            });
                    },

                    scrapeUrl(url) {
                        this.webLoading = true;
                        this.webMessage = '';
                        const formData = new FormData();
                        formData.append('url', url);
                        formData.append('_token', this.analisaFormEl().querySelector('input[name="_token"]').value);
                        fetch(@json($formRoutes['scrape_url']), {
                            method: 'POST',
                            headers: { Accept: 'application/json' },
                            body: formData,
                        })
                            .then(res => res.json().then(body => ({ ok: res.ok, body })))
                            .then(({ ok, body }) => {
                                this.webLoading = false;
                                if (!ok || !body.success) {
                                    this.webOk = false;
                                    this.webMessage = body.message || 'Gagal scrape URL.';
                                    return;
                                }
                                this.applyExtractedData(body.data);
                                this.webOk = true;
                                this.webMessage = body.message + ' Data sudah di tab Input Manual — lengkapi Jenis RD jika perlu, lalu Submit Analisa.';
                                this.mode = 'manual';
                            })
                            .catch(() => {
                                this.webLoading = false;
                                this.webOk = false;
                                this.webMessage = 'Gagal scrape URL.';
                            });
                    },

                    parsePdf(event) {
                        const file = event.target.files[0];
                        if (!file) return;

                        this.pdfLoading = true;
                        this.pdfResult = '';
                        this.pdfSuccess = false;

                        const formData = new FormData();
                        formData.append('file_pdf', file);
                        formData.append('_token', this.analisaFormEl().querySelector('input[name="_token"]').value);

                        fetch('{{ $formRoutes['parse_pdf'] }}', {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                },
                                body: formData,
                            })
                            .then(res => {
                                if (!res.ok) {
                                    return res.json().then(err => {
                                        throw new Error(err.message || 'Gagal parsing PDF');
                                    });
                                }
                                return res.json();
                            })
                            .then(resp => {
                                this.pdfLoading = false;
                                if (!resp.success) {
                                    this.pdfSuccess = false;
                                    this.pdfResult = resp.message;
                                    return;
                                }
                                this.applyExtractedData(resp.data);
                                this.pdfFile = resp.pdf_file || '';
                                this.pdfSuccess = true;
                                this.pdfResult = resp.message;
                            })
                            .catch(err => {
                                this.pdfLoading = false;
                                this.pdfSuccess = false;
                                this.pdfResult = 'Gagal: ' + err.message;
                            });
                    },
                };
            }
        </script>
    @endpush
@endsection
