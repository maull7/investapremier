{{-- Neraca (Balance Sheet) --}}
<div class="bg-white rounded-xl border border-line p-6 space-y-4">
    <h3 class="font-semibold text-primary">Neraca (Balance Sheet) <span class="text-xs font-normal text-muted">— dalam juta Rupiah (atau sesuai mata uang)</span></h3>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Aset Lancar --}}
        <div class="space-y-3">
            <h4 class="text-xs font-semibold text-muted uppercase tracking-wide">Aset Lancar</h4>
            @foreach([
                ['cash_equivalents','Kas & Setara Kas'],
                ['account_receivable','Piutang Usaha'],
                ['inventories','Persediaan'],
                ['other_current_asset','Aset Lancar Lainnya'],
                ['current_asset','Total Aset Lancar'],
            ] as [$name, $label])
            <div class="flex items-center gap-2">
                <label class="w-44 text-xs text-gray-600 shrink-0">{{ $label }}</label>
                <input type="number" name="{{ $name }}" step="0.01" value="{{ old($name) }}" x-model="{{ $name }}"
                    class="flex-1 border-gray-300 rounded-lg text-sm focus:border-primary focus:ring focus:ring-primary/20 {{ in_array($name,['current_asset','total_asset','total_liabilities','equity']) ? 'font-semibold bg-[#f8fafc]' : '' }}">
            </div>
            @endforeach
        </div>

        {{-- Aset Tidak Lancar --}}
        <div class="space-y-3">
            <h4 class="text-xs font-semibold text-muted uppercase tracking-wide">Aset Tidak Lancar</h4>
            @foreach([
                ['fixed_asset','Aset Tetap'],
                ['other_non_current_asset','Aset Tidak Lancar Lainnya'],
                ['total_asset','Total Aset'],
            ] as [$name, $label])
            <div class="flex items-center gap-2">
                <label class="w-44 text-xs text-gray-600 shrink-0">{{ $label }}</label>
                <input type="number" name="{{ $name }}" step="0.01" value="{{ old($name) }}" x-model="{{ $name }}"
                    class="flex-1 border-gray-300 rounded-lg text-sm focus:border-primary focus:ring focus:ring-primary/20 {{ in_array($name,['total_asset']) ? 'font-semibold bg-[#f8fafc]' : '' }}">
            </div>
            @endforeach
        </div>

        {{-- Liabilitas Jangka Pendek --}}
        <div class="space-y-3">
            <h4 class="text-xs font-semibold text-muted uppercase tracking-wide">Liabilitas Jangka Pendek</h4>
            @foreach([
                ['account_payable','Utang Usaha'],
                ['accruals','Akrual'],
                ['short_term_loans','Pinjaman Jangka Pendek'],
                ['current_maturities_of_long_term_loans','Bagian Lancar Utang JK Panjang'],
                ['other_current_liabilities','Liabilitas Lancar Lainnya'],
                ['current_liabilities','Total Liabilitas Lancar'],
            ] as [$name, $label])
            <div class="flex items-center gap-2">
                <label class="w-44 text-xs text-gray-600 shrink-0">{{ $label }}</label>
                <input type="number" name="{{ $name }}" step="0.01" value="{{ old($name) }}" x-model="{{ $name }}"
                    class="flex-1 border-gray-300 rounded-lg text-sm focus:border-primary focus:ring focus:ring-primary/20 {{ in_array($name,['current_liabilities']) ? 'font-semibold bg-[#f8fafc]' : '' }}">
            </div>
            @endforeach
        </div>

        {{-- Liabilitas JK Panjang & Ekuitas --}}
        <div class="space-y-3">
            <h4 class="text-xs font-semibold text-muted uppercase tracking-wide">Liabilitas JK Panjang & Ekuitas</h4>
            @foreach([
                ['long_term_loans','Pinjaman Jangka Panjang'],
                ['other_non_current_liabilities','Liabilitas Tidak Lancar Lainnya'],
                ['total_non_current_liabilities','Total Liabilitas Tidak Lancar'],
                ['total_liabilities','Total Liabilitas'],
                ['retained_earning','Saldo Laba'],
                ['equity','Total Ekuitas'],
                ['share_capital','Modal Saham'],
                ['additional_paid_in_capital','Tambahan Modal Disetor'],
                ['others','Komponen Ekuitas Lain'],
                ['non_controlling_interest','Kepentingan Non-Pengendali'],
                ['total_equity_equity_to_parent_entity','Ekuitas ke Entitas Induk'],
            ] as [$name, $label])
            <div class="flex items-center gap-2">
                <label class="w-44 text-xs text-gray-600 shrink-0">{{ $label }}</label>
                <input type="number" name="{{ $name }}" step="0.01" value="{{ old($name) }}" x-model="{{ $name }}"
                    class="flex-1 border-gray-300 rounded-lg text-sm focus:border-primary focus:ring focus:ring-primary/20 {{ in_array($name,['total_liabilities','equity']) ? 'font-semibold bg-[#f8fafc]' : '' }}">
            </div>
            @endforeach
        </div>
    </div>
</div>
