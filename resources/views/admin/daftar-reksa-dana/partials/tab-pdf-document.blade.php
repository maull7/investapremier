<div class="space-y-6">
    @php
        $documents = $fund->documents->where('document_type', $docType)->sortByDesc('ffs_year');
    @endphp

    @if($documents->isEmpty())
        <div class="py-12 text-center text-muted bg-white rounded-2xl border border-line">
            <svg class="w-10 h-10 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="font-medium">Dokumen {{ $docType === 'prospektus' ? 'Prospektus' : 'FFS' }} belum tersedia.</p>
            @if($docType === 'ffs')
                <div class="mt-4 max-w-sm mx-auto">
                    <div class="bg-[#f8fafc] border border-line rounded-xl p-4 text-left space-y-3">
                        <p class="text-xs font-semibold text-primary">Upload FFS Baru</p>
                        <input type="file" accept="application/pdf"
                            @change="uploadFfsFile = $event.target.files[0]"
                            class="w-full text-xs border border-line rounded-lg px-3 py-2 file:mr-2 file:rounded file:border-0 file:bg-emerald-50 file:px-2 file:py-1 file:text-emerald-700">
                        <div class="grid grid-cols-2 gap-2">
                            <select x-model="uploadFfsMonth"
                                class="w-full text-xs border border-line rounded-lg px-3 py-2">
                                <option value="">Bulan</option>
                                @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $m)
                                    <option value="{{ $i + 1 }}">{{ $m }}</option>
                                @endforeach
                            </select>
                            <input type="number" x-model="uploadFfsYear" placeholder="Tahun" min="2000" max="2100"
                                class="w-full text-xs border border-line rounded-lg px-3 py-2">
                        </div>
                        <button @click="uploadFfs()" :disabled="uploadFfsLoading || !uploadFfsFile || !uploadFfsMonth || !uploadFfsYear"
                            class="w-full px-3 py-2 bg-emerald-700 text-white rounded-lg text-xs font-semibold hover:bg-emerald-800 transition disabled:opacity-50 flex items-center justify-center gap-2">
                            <span x-show="uploadFfsLoading" class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span x-text="uploadFfsLoading ? 'Mengupload...' : 'Upload FFS'"></span>
                        </button>
                        <div x-show="uploadFfsError" x-text="uploadFfsError"
                            class="px-3 py-2 rounded-lg text-xs bg-red-50 border border-red-200 text-red-700"></div>
                        <div x-show="uploadFfsSuccess" x-text="uploadFfsSuccess"
                            class="px-3 py-2 rounded-lg text-xs bg-green-50 border border-green-200 text-green-700"></div>
                    </div>
                </div>
            @else
                <p class="text-xs mt-1">Upload di <a href="{{ route('admin.daftar-reksa-dana.index', ['tab' => 'prospektus-ffs']) }}" class="text-accent hover:underline">Daftar Reksa Dana</a>.</p>
            @endif
        </div>
    @endif

    @foreach($documents as $doc)
    <div class="bg-white rounded-2xl border border-line shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-line bg-gradient-to-r from-primary to-primary-light flex items-center justify-between">
            <h2 class="font-bold text-white text-sm">
                @if($docType === 'prospektus')
                    Prospektus {{ $doc->ffs_year }}
                @else
                    @php $months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']; @endphp
                    FFS {{ $months[$doc->ffs_month - 1] ?? '-' }} {{ $doc->ffs_year }}
                @endif
                @if($doc->parsedPages->isNotEmpty())
                    <span class="text-[10px] font-normal opacity-75 ml-2">({{ $doc->parsedPages->count() }} hlm diparse)</span>
                @endif
            </h2>
            <div class="flex items-center gap-2">
                <a target="_blank" href="{{ route('admin.daftar-reksa-dana.documents.view', $doc) }}" class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition">Preview PDF</a>
                <a href="{{ route('admin.daftar-reksa-dana.documents.download', $doc) }}" class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-semibold transition">Download</a>
            </div>
        </div>

        @if($doc->parsedPages->isEmpty())
        <div class="p-6 text-center text-muted">
            <p class="text-sm">Dokumen belum diparse.</p>
            <p class="text-xs mt-1">Gunakan tombol <strong>Parse Dokumen</strong> di tab Prospektus dan FFS untuk mengekstrak teks.</p>
        </div>
        @else
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-0 lg:gap-0 divide-y lg:divide-y-0 lg:divide-x divide-line">
            {{-- Kolom Partisi (hanya Prospektus) atau Aksi FFS --}}
            <div class="p-4 lg:col-span-1">
                @if($docType === 'ffs')
                    {{-- FFS: langsung parse tanpa partisi --}}
                    <div class="space-y-3">
                        <button @click="uploadFfsOpen = !uploadFfsOpen"
                            class="w-full px-3 py-2 border-2 border-dashed border-emerald-300 text-emerald-700 rounded-lg text-xs font-semibold hover:bg-emerald-50 transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span x-text="uploadFfsOpen ? 'Tutup' : 'Upload FFS Baru'"></span>
                        </button>

                        <div x-show="uploadFfsOpen" class="bg-[#f8fafc] border border-line rounded-xl p-4 space-y-3">
                            <p class="text-xs font-semibold text-primary">Upload FFS Baru</p>
                            <input type="file" accept="application/pdf"
                                @change="uploadFfsFile = $event.target.files[0]"
                                class="w-full text-xs border border-line rounded-lg px-3 py-2 file:mr-2 file:rounded file:border-0 file:bg-emerald-50 file:px-2 file:py-1 file:text-emerald-700">
                            <div class="grid grid-cols-2 gap-2">
                                <select x-model="uploadFfsMonth"
                                    class="w-full text-xs border border-line rounded-lg px-3 py-2">
                                    <option value="">Bulan</option>
                                    @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $m)
                                        <option value="{{ $i + 1 }}">{{ $m }}</option>
                                    @endforeach
                                </select>
                                <input type="number" x-model="uploadFfsYear" placeholder="Tahun" min="2000" max="2100"
                                    class="w-full text-xs border border-line rounded-lg px-3 py-2">
                            </div>
                            <button @click="uploadFfs()" :disabled="uploadFfsLoading || !uploadFfsFile || !uploadFfsMonth || !uploadFfsYear"
                                class="w-full px-3 py-2 bg-emerald-700 text-white rounded-lg text-xs font-semibold hover:bg-emerald-800 transition disabled:opacity-50 flex items-center justify-center gap-2">
                                <span x-show="uploadFfsLoading" class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                <span x-text="uploadFfsLoading ? 'Mengupload...' : 'Upload'"></span>
                            </button>
                            <div x-show="uploadFfsError" x-text="uploadFfsError"
                                class="px-3 py-2 rounded-lg text-xs bg-red-50 border border-red-200 text-red-700"></div>
                            <div x-show="uploadFfsSuccess" x-text="uploadFfsSuccess"
                                class="px-3 py-2 rounded-lg text-xs bg-green-50 border border-green-200 text-green-700"></div>
                        </div>

                        <div>
                            <h3 class="font-bold text-primary text-xs mb-1">Ekstrak Data FFS</h3>
                            <p class="text-[10px] text-muted">Untuk dokumen FFS, data langsung diparse dari seluruh halaman tanpa perlu partisi.</p>
                        </div>

                        @php
                            $latestFfs = $doc->ffsExtractionResults->first();
                        @endphp
                        @if($latestFfs)
                            <div class="px-3 py-2 bg-emerald-50 border border-emerald-200 rounded-lg">
                                <p class="text-[10px] font-semibold text-emerald-700">Sudah diekstrak</p>
                                <p class="text-[10px] text-muted mt-0.5">
                                    Periode: {{ $latestFfs->ffs_month ? str_pad($latestFfs->ffs_month, 2, '0', STR_PAD_LEFT) : '-' }}/{{ $latestFfs->ffs_year ?: '-' }}
                                </p>
                                <p class="text-[10px] text-muted">
                                    Tanggal data: {{ $latestFfs->tanggal_data?->format('d M Y') ?: '-' }}
                                </p>
                            </div>
                        @endif

                        <button @click='handleParseFfs({{ $doc->id }})'
                                :disabled="loadingFfs[{{ $doc->id }}]"
                                class="w-full px-3 py-2 bg-emerald-700 text-white rounded-lg text-xs font-semibold hover:bg-emerald-800 transition disabled:opacity-50 flex items-center justify-center gap-2">
                            <span x-show="loadingFfs[{{ $doc->id }}]" class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span x-text="loadingFfs[{{ $doc->id }}] ? 'Memproses...' : 'Parse FFS & Simpan'"></span>
                        </button>

                        <div x-show="ffsSuccess[{{ $doc->id }}]" x-text="ffsSuccess[{{ $doc->id }}]"
                             class="px-3 py-2 rounded-lg text-xs bg-green-50 border border-green-200 text-green-700"></div>
                        <div x-show="ffsError[{{ $doc->id }}]" x-text="ffsError[{{ $doc->id }}]"
                             class="px-3 py-2 rounded-lg text-xs bg-red-50 border border-red-200 text-red-700"></div>
                    </div>
                @else
                    {{-- Prospektus: partisi --}}
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-primary text-xs">Partisi</h3>
                        <button @click="openPartitionModal({{ $doc->id }})" class="px-2 py-1 bg-primary text-white rounded text-[10px] font-semibold hover:bg-primary/90 transition">+ Partisi</button>
                    </div>
                    <div class="space-y-1.5">
                        @forelse($doc->partitions as $partition)
                        <label class="flex items-start gap-2 px-3 py-2 rounded-lg border border-line bg-[#f8fafc] hover:border-accent/30 transition cursor-pointer"
                               :class="selectedPartitionIds.includes({{ $partition->id }}) ? 'border-accent bg-accent/5' : ''">
                            <input type="checkbox" :value="{{ $partition->id }}" x-model="selectedPartitionIds" class="mt-0.5 rounded border-gray-300 text-accent focus:ring-accent/30 shrink-0">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <p class="text-xs font-semibold text-primary truncate">{{ $partition->nama_partisi }}</p>
                                    @if($partition->source === 'toc_ai')
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-medium bg-violet-50 text-violet-700 border border-violet-100">AI</span>
                                    @endif
                                </div>
                                <p class="text-[10px] text-muted">
                                    Parse {{ $partition->start_page }}-{{ $partition->end_page }}
                                    @if($partition->start_page_pdf && $partition->end_page_pdf)
                                        <span class="text-[9px] text-slate-400">(PDF {{ $partition->start_page_pdf }}-{{ $partition->end_page_pdf }})</span>
                                    @endif
                                </p>
                            </div>
                            <button type="button" @click.stop.prevent="deletePartition({{ $partition->id }})" class="p-1 text-red-400 hover:text-red-600 rounded transition shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </label>
                        @empty
                        <p class="text-xs text-muted italic">Belum ada partisi. Aktifkan "Otomatis buat partisi dari daftar isi" saat parse, atau buat manual.</p>
                        @endforelse
                    </div>

                    {{-- Parse & Simpan Data --}}
                    <div class="mt-4 pt-4 border-t border-line">
                        <button @click='handleParseSimpan({{ $doc->id }})'
                                :disabled="selectedPartitionIds.length === 0 || loading"
                                class="w-full px-3 py-2 bg-emerald-700 text-white rounded-lg text-xs font-semibold hover:bg-emerald-800 transition disabled:opacity-50 flex items-center justify-center gap-2">
                            <span x-show="loading" class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span x-text="loading ? 'Memproses...' : 'Parse & Simpan Data'"></span>
                        </button>
                        <p class="text-[10px] text-muted mt-1">Pilih satu atau beberapa partisi, lalu klik tombol untuk mengekstrak data dengan AI.</p>
                        <div x-show="error" x-text="error"
                            class="mt-3 px-3 py-2 rounded-lg text-xs bg-red-50 border border-red-200 text-red-700"></div>
                        <div x-show="success" x-text="success"
                            class="mt-3 px-3 py-2 rounded-lg text-xs bg-green-50 border border-green-200 text-green-700"></div>
                    </div>
                @endif
            </div>

            {{-- Kolom Daftar Halaman Parsing --}}
            <div class="p-4 lg:col-span-1">
                <h3 class="font-bold text-primary text-xs mb-3">Halaman Hasil Parsing</h3>
                <p class="text-[10px] text-muted mb-2">Gunakan nomor <strong>Parsing</strong> (bukan PDF asli) saat membuat partisi.</p>
                <div class="space-y-1 max-h-96 overflow-y-auto">
                    @foreach($doc->parsedPages as $page)
                    <div @click='showPageContent({{ $doc->id }}, {{ $page->id }})'
                         :class="isPageInSelectedPartition({{ $doc->id }}, {{ $page->page_parse }})
                            ? 'bg-accent/10 border border-accent/30' : 'border border-line hover:border-accent/30 hover:bg-[#f8fafc]'"
                         class="px-3 py-2 rounded-lg cursor-pointer transition text-xs flex items-center gap-2">
                        <span class="text-[10px] text-muted font-mono w-10 shrink-0">PDF {{ $page->page_pdf }}</span>
                        <span class="text-[10px] text-muted">→ Parsing {{ $page->page_parse }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Kolom Isi Teks --}}
            <div class="p-4 lg:col-span-2">
                <h3 class="font-bold text-primary text-xs mb-3">Isi Teks</h3>
                <div x-show="!selectedPageContent" class="text-xs text-muted italic py-8 text-center">
                    Klik nomor halaman di sebelah kiri untuk melihat isi teks.
                </div>
                <div x-show="selectedPageContent" x-html="selectedPageContent"
                    class="text-xs whitespace-pre-wrap bg-[#f8fafc] rounded-xl p-4 border border-line max-h-96 overflow-y-auto font-mono leading-relaxed">
                </div>
            </div>
        </div>
        @endif
    </div>
    @endforeach

    {{-- Modal Tambah Partisi --}}
    <div x-show="partitionModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6" @click.self="partitionModal.open = false">
        <div class="bg-white rounded-2xl shadow-xl border border-line w-full max-w-md">
            <div class="flex items-center justify-between px-6 py-4 border-b border-line">
                <h3 class="font-bold text-primary" x-text="partitionModal.editing ? 'Edit Partisi' : 'Tambah Partisi'"></h3>
                <button @click="partitionModal.open = false" class="p-1 hover:bg-[#f1f5f9] rounded-lg transition">
                    <svg class="w-5 h-5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">Nama Partisi *</label>
                    <input type="text" x-model="partitionModal.nama" required maxlength="255"
                        class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-accent focus:ring focus:ring-accent/30"
                        placeholder="Contoh: Bab II - Manajer Investasi">
                </div>
                <p class="text-[11px] text-muted">Isi nomor halaman berdasarkan kolom <strong>Parsing</strong> di daftar sebelah kiri, bukan nomor PDF asli.</p>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Halaman Parsing Mulai *</label>
                        <input type="number" x-model="partitionModal.start" min="1" required
                            class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-accent focus:ring focus:ring-accent/30">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1">Halaman Parsing Selesai *</label>
                        <input type="number" x-model="partitionModal.end" min="1" required
                            class="w-full border border-line rounded-lg px-3 py-2 text-sm focus:border-accent focus:ring focus:ring-accent/30">
                    </div>
                </div>
                <div x-show="partitionModal.error" x-text="partitionModal.error" class="px-3 py-2 rounded-lg text-xs bg-red-50 border border-red-200 text-red-700"></div>
                <div class="flex justify-end gap-2 pt-2">
                    <button @click="partitionModal.open = false" class="px-4 py-2 text-sm text-muted border border-line rounded-lg hover:bg-[#f1f5f9] transition">Batal</button>
                    <button @click="savePartition()" :disabled="partitionModal.saving"
                        class="px-4 py-2 text-sm text-white bg-primary rounded-lg hover:bg-primary/90 transition disabled:opacity-50">
                        <span x-show="partitionModal.saving" class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin inline-block mr-1 align-middle"></span>
                        <span>Simpan</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
