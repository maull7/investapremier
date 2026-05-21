{{-- Laba Rugi (Income Statement) --}}
<div class="bg-white rounded-xl border border-line p-6 space-y-3">
    <h3 class="font-semibold text-primary">Laba Rugi (Income Statement)</h3>
    <div class="space-y-3 max-w-xl">
        @foreach([
            ['net_revenue','Pendapatan Bersih'],
            ['cost_of_good_sold','Beban Pokok Penjualan'],
            ['gross_income','Laba Kotor'],
            ['operational_expense','Beban Operasional'],
            ['laba_operasional','Laba Operasional'],
            ['other_income_expense','Pendapatan/Beban Lain-lain'],
            ['interest_expense','Beban Bunga'],
            ['income_before_tax','Laba Sebelum Pajak'],
            ['taxes','Pajak Penghasilan'],
            ['ebit','EBIT'],
            ['ebitda','EBITDA'],
            ['net_income_attributable_to_non_controlling_interest','NCI Net Income'],
            ['net_income','Laba Bersih'],
        ] as [$name, $label])
        <div class="flex items-center gap-2">
            <label class="w-52 text-xs text-gray-600 shrink-0">{{ $label }}</label>
            <input type="number" name="{{ $name }}" step="0.01" value="{{ old($name) }}"
                class="flex-1 border-gray-300 rounded-lg text-sm focus:border-primary focus:ring focus:ring-primary/20 {{ in_array($name,['gross_income','ebit','ebitda','net_income']) ? 'font-semibold bg-[#f8fafc]' : '' }}">
        </div>
        @endforeach
    </div>
</div>
