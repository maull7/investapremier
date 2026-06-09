@extends('layouts.admin')

@section('title', 'Daftar Obligasi - InvestaPremier')

@section('content')
<div x-data="{
    deleteId: null, deleteType: '', deleteText: '',
    showImportHarga: false, showImportBond: false, showExtraction: false,
    // ── Async sync state (polled from server) ─────────────────────────
    isSyncing: false,
    syncRunId: null,
    syncStep: 'queued',
    syncStepLabel: 'Menunggu worker...',
    syncProgress: 0,
    syncStatus: 'queued',
    syncMessage: '',
    syncErrors: [],
    syncPollTimer: null,

    init() {
        // If the controller flashed a sync_run_id (page reloaded after submit),
        // immediately enter polling mode and pick up live progress.
        const initialRunId = @json(session('sync_run_id'));
        if (initialRunId) {
            this.startPolling(initialRunId);
        }
    },

    async submitSync(event) {
        event.preventDefault();
        const form = event.target;
        this.isSyncing = true;
        this.syncStep = 'queued';
        this.syncStepLabel = 'Mengirim job ke antrian...';
        this.syncProgress = 0;
        this.syncStatus = 'queued';
        this.syncErrors = [];
        this.syncMessage = '';

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': form.querySelector('input[name=_token]').value,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            if (!data.run_id) throw new Error('Server tidak mengembalikan run_id');
            this.startPolling(data.run_id);
        } catch (e) {
            this.isSyncing = false;
            alert('Gagal memulai sync: ' + e.message);
        }
    },

    startPolling(runId) {
        this.syncRunId = runId;
        this.isSyncing = true;
        this.poll();
    },

    async poll() {
        if (!this.syncRunId) return;
        try {
            const res = await fetch(`/admin/obligasi/sync-idx/status/${this.syncRunId}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const d = await res.json();
            this.syncStep = d.current_step || 'queued';
            this.syncStepLabel = d.current_step_label || '...';
            this.syncProgress = d.progress_percent || 0;
            this.syncStatus = d.status;
            this.syncMessage = d.message || '';
            this.syncErrors = d.errors || [];

            if (d.is_terminal) {
                // Show final state for ~3 seconds then reload page so the
                // user sees the updated data + flash message.
                this.syncProgress = 100;
                setTimeout(() => { window.location.href = window.location.pathname + '?tab=harga-referensi'; }, 2500);
                return;
            }
            this.syncPollTimer = setTimeout(() => this.poll(), 2000);
        } catch (e) {
            // Transient errors — retry with longer backoff
            this.syncPollTimer = setTimeout(() => this.poll(), 5000);
        }
    },

    cancelSync() {
        if (this.syncPollTimer) clearTimeout(this.syncPollTimer);
        this.isSyncing = false;
        this.syncRunId = null;
    }
}">

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="page-title">Daftar Obligasi</h1>
        <p class="page-sub">Kelola data obligasi harga referensi dan keuangan emiten</p>
    </div>
    <div class="flex items-center gap-2">
        <button @click="showExtraction = true" class="btn-outline">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18M6 8h12m-9 4h6m-8 4h10m-7 4h4" />
            </svg>
            Ekstrak Data
        </button>
        <form method="POST" action="{{ route('admin.obligasi.sync-idx') }}" @submit="submitSync($event)">
            @csrf
            <button type="submit" class="btn-outline" :disabled="isSyncing"
                :class="isSyncing ? 'opacity-50 cursor-not-allowed' : ''"
                title="Tarik metadata obligasi dari IDX + PHEI via background job (harga & YTM tetap manual)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    :class="isSyncing ? 'animate-spin' : ''">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Sync dari IDX + PHEI
            </button>
        </form>
    </div>
</div>

@if(session('success'))
<div class="alert-success">
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-5 flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('error') }}
</div>
@endif

@if(session('warning'))
<div class="mb-5 flex items-center gap-3 px-4 py-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-sm">
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.74-2.99l-6.93-12a2 2 0 00-3.48 0l-6.93 12A2 2 0 005.07 19z"/></svg>
    {{ session('warning') }}
</div>
@endif

{{-- Tabs --}}
<div class="mb-5">
    <div class="flex items-center gap-1 bg-[#f1f5f9] rounded-xl p-1 w-fit">
        <a href="{{ route('admin.obligasi.index', ['tab' => 'harga-referensi']) }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $tab === 'harga-referensi' ? 'bg-white text-primary shadow-sm' : 'text-muted hover:text-primary' }}">
            Obligasi Harga Referensi
        </a>
        <a href="{{ route('admin.obligasi.index', ['tab' => 'bond']) }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $tab === 'bond' ? 'bg-white text-primary shadow-sm' : 'text-muted hover:text-primary' }}">
            Keuangan Emiten
        </a>
        <a href="{{ route('admin.obligasi.index', ['tab' => 'hasil-ekstrak']) }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $tab === 'hasil-ekstrak' ? 'bg-white text-primary shadow-sm' : 'text-muted hover:text-primary' }}">
            Hasil Ekstrak
        </a>
    </div>
</div>

@if($tab === 'harga-referensi')
    @include('admin.obligasi._tab-harga-referensi')
@elseif($tab === 'bond')
    @include('admin.obligasi._tab-bond')
@else
    @include('admin.obligasi._tab-hasil-ekstrak')
@endif

{{-- Modal Ekstrak Data Obligasi --}}
<div x-show="showExtraction" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="absolute inset-0 bg-black/40" @click="showExtraction = false"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6"
         x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <h3 class="font-bold text-primary text-base mb-1">Ekstrak Data Obligasi</h3>
        <p class="text-muted text-sm mb-4">Semua data obligasi akan diproses lewat Horizon. PHEI dicoba lebih dulu, lalu IDX jika endpoint tersedia.</p>

        <form method="POST" action="{{ route('admin.obligasi.extraction-batches.store') }}">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-primary mb-1.5">Tanggal data</label>
                <input type="date" name="data_date" value="{{ now()->toDateString() }}"
                       class="w-full border border-line rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30"
                       required>
            </div>
            <div class="mt-4">
                <label class="block text-xs font-semibold text-primary mb-1.5">Rentang data</label>
                <select name="range"
                        class="w-full border border-line rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30"
                        required>
                    @foreach ($extractionRanges as $range)
                        <option value="{{ $range['value'] }}">{{ $range['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mt-4 rounded-xl border border-line bg-[#f8fafc] px-4 py-3 text-xs text-muted">
                Data masuk staging dulu. Database utama baru berubah setelah tombol Simpan ke Database diklik dari tab Hasil Ekstrak.
            </div>
            <div class="flex items-center justify-end gap-3 mt-6">
                <button type="button" @click="showExtraction = false"
                        class="px-4 py-2 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent/90 transition">
                    Proses
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Konfirmasi Hapus --}}
<div x-show="deleteId !== null" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="absolute inset-0 bg-black/40" @click="deleteId = null"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6"
         x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-full bg-red-100 grid place-items-center shrink-0">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <div class="flex-1">
                <h3 class="font-bold text-primary text-base">Hapus Data?</h3>
                <p class="page-sub">Data berikut akan dihapus permanen:</p>
                <p class="mt-2 text-sm text-primary font-medium bg-[#f8fafc] rounded-lg px-3 py-2 border border-line" x-text="deleteText"></p>
                <p class="text-xs text-red-500 mt-2">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 mt-6">
            <button type="button" @click="deleteId = null"
                    class="px-4 py-2 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary hover:border-primary/30 transition">
                Batal
            </button>
            <form method="POST" :action="deleteId ? `/admin/obligasi/${deleteType}/${deleteId}` : ''">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700 transition">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Modal Import Harga Referensi --}}
@include('admin.obligasi._modal-import-harga')

{{-- Modal Import Bond --}}
@include('admin.obligasi._modal-import-bond')

{{-- Modal Loading: Sync dari IDX + PHEI (server-polled progress) --}}
<div x-show="isSyncing" x-cloak
    class="fixed inset-0 z-[60] bg-white/95 backdrop-blur-sm grid place-items-center px-4"
    x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100">
    <div class="text-center max-w-lg w-full">
        {{-- Animated spinner when still running, checkmark on success, red X on fail --}}
        <template x-if="syncStatus !== 'completed' && syncStatus !== 'failed'">
            <div class="w-14 h-14 border-4 border-accent border-t-transparent rounded-full mx-auto mb-6 animate-spin"></div>
        </template>
        <template x-if="syncStatus === 'completed'">
            <div class="w-14 h-14 rounded-full mx-auto mb-6 bg-green-100 grid place-items-center">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </template>
        <template x-if="syncStatus === 'failed'">
            <div class="w-14 h-14 rounded-full mx-auto mb-6 bg-red-100 grid place-items-center">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
        </template>

        <h3 class="text-lg font-bold text-primary mb-1">
            <span x-show="syncStatus === 'completed'">Sync Selesai</span>
            <span x-show="syncStatus === 'failed'">Sync Gagal</span>
            <span x-show="syncStatus !== 'completed' && syncStatus !== 'failed'">Sinkronisasi Obligasi</span>
        </h3>
        <p class="text-sm text-muted mb-2" x-text="syncStepLabel"></p>
        <p class="text-xs text-muted mb-5">
            Run ID: <span class="font-mono" x-text="syncRunId"></span> · Status: <span class="font-semibold" x-text="syncStatus"></span>
        </p>

        {{-- Real progress bar driven by server-side step --}}
        <div class="max-w-sm mx-auto mb-5">
            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-accent transition-all duration-500 ease-out"
                    :style="`width: ${syncProgress}%`"
                    :class="syncStatus === 'failed' ? 'bg-red-500' : (syncStatus === 'completed' ? 'bg-green-500' : 'bg-accent')"></div>
            </div>
            <p class="text-xs text-muted mt-1.5"><span x-text="syncProgress"></span>%</p>
        </div>

        {{-- Final message --}}
        <template x-if="syncMessage">
            <div class="mt-2 mx-auto max-w-md text-left rounded-xl border px-4 py-3 text-xs"
                :class="syncStatus === 'failed' ? 'border-red-200 bg-red-50 text-red-800' : 'border-green-200 bg-green-50 text-green-800'">
                <p x-text="syncMessage"></p>
            </div>
        </template>

        {{-- Errors list --}}
        <template x-if="syncErrors && syncErrors.length">
            <div class="mt-3 mx-auto max-w-md text-left rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800">
                <p class="font-semibold mb-1">Catatan / Error</p>
                <ul class="list-disc pl-4 space-y-0.5">
                    <template x-for="err in syncErrors" :key="err">
                        <li x-text="err"></li>
                    </template>
                </ul>
            </div>
        </template>

        <div class="mt-6 mx-auto max-w-md text-left rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800"
            x-show="syncStatus !== 'completed' && syncStatus !== 'failed'">
            <p class="font-semibold mb-1">Catatan</p>
            <p>Sumber free IDX/PHEI tidak menyediakan harga real-time per-obligasi (harga_persen, YTM, current_yield). Fields tersebut tetap NULL setelah sync — perlu diisi manual via menu Edit atau import Excel.</p>
        </div>

        <p class="text-xs text-muted mt-4" x-show="syncStatus !== 'completed' && syncStatus !== 'failed'">
            Job berjalan di background. Aman jika kamu tutup tab — progress akan dilanjutkan oleh worker.
        </p>

        <button type="button" @click="cancelSync()" x-show="syncStatus === 'failed'"
            class="mt-4 px-4 py-2 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary transition">
            Tutup
        </button>
    </div>
</div>

</div>
@endsection
