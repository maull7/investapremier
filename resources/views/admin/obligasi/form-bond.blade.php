@extends('layouts.admin')

@section('title', isset($obligasi) ? 'Edit Keuangan Emiten' : 'Tambah Keuangan Emiten')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-muted mb-3">
        <a href="{{ route('admin.obligasi.index', ['tab' => 'bond']) }}" class="hover:text-primary transition">Daftar Obligasi</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-primary font-medium">{{ isset($obligasi) ? 'Edit' : 'Tambah' }} Bond</span>
    </div>
    <h1 class="text-2xl font-bold text-primary">{{ isset($obligasi) ? 'Edit Keuangan Emiten' : 'Tambah Keuangan Emiten' }}</h1>
</div>

<form method="POST"
    action="{{ isset($obligasi) ? route('admin.obligasi.update-bond', $obligasi) : route('admin.obligasi.store-bond') }}"
    class="max-w-7xl">
    @csrf
    @if(isset($obligasi)) @method('PUT') @endif

    <div class="bg-white rounded-2xl border border-line shadow-sm p-6 space-y-6">
        <div>
            <h3 class="font-bold text-primary text-sm mb-3">Identitas</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-input-label value="Kode" class="text-sm font-semibold mb-1.5" />
                    <input type="text" name="kode" maxlength="20"
                        class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent @error('kode') border-red-400 @enderror"
                        placeholder="PJAA" value="{{ old('kode', $obligasi->kode ?? '') }}">
                    <x-input-error :messages="$errors->get('kode')" class="mt-1 text-xs" />
                </div>
                <div>
                    <x-input-label value="Periode" class="text-sm font-semibold mb-1.5" />
                    <input type="text" name="periode" maxlength="10"
                        class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent @error('periode') border-red-400 @enderror"
                        placeholder="202603" value="{{ old('periode', $obligasi->periode ?? '') }}">
                    <x-input-error :messages="$errors->get('periode')" class="mt-1 text-xs" />
                </div>
            </div>
        </div>

        <div>
            <h3 class="font-bold text-primary text-sm mb-3">Neraca (Balance Sheet)</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @php
                $fields = [
                    'current_asset' => 'Current Asset', 'current_liabilities' => 'Current Liabilities',
                    'total_asset' => 'Total Asset', 'total_liabilities' => 'Total Liabilities',
                    'retained_earning' => 'Retained Earning', 'equity' => 'Equity',
                    'interest_expense' => 'Interest Expense', 'laba_operasional' => 'Laba Operasional',
                ];
                @endphp
                @foreach($fields as $key => $label)
                <div>
                    <x-input-label value="{{ $label }}" class="text-sm font-semibold mb-1.5" />
                    <input type="text" inputmode="decimal" name="{{ $key }}" class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                        placeholder="0" value="{{ old($key, $obligasi->$key ?? '') }}">
                </div>
                @endforeach
            </div>
        </div>

        <div>
            <h3 class="font-bold text-primary text-sm mb-3">Aset</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @php
                $asetFields = [
                    'cash_equivalents' => 'Cash & Equivalents', 'account_receivable' => 'Account Receivable',
                    'inventories' => 'Inventories', 'other_current_asset' => 'Other Current Asset',
                    'fixed_asset' => 'Fixed Asset', 'other_non_current_asset' => 'Other Non-Current Asset',
                ];
                @endphp
                @foreach($asetFields as $key => $label)
                <div>
                    <x-input-label value="{{ $label }}" class="text-sm font-semibold mb-1.5" />
                    <input type="text" inputmode="decimal" name="{{ $key }}" class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                        placeholder="0" value="{{ old($key, $obligasi->$key ?? '') }}">
                </div>
                @endforeach
            </div>
        </div>

        <div>
            <h3 class="font-bold text-primary text-sm mb-3">Liabilities</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @php
                $liabFields = [
                    'account_payable' => 'Account Payable', 'accruals' => 'Accruals',
                    'short_term_loans' => 'Short Term Loans', 'current_maturities_of_long_term_loans' => 'Curr. Mat. LT Loans',
                    'other_current_liabilities' => 'Other Curr. Liab.', 'long_term_loans' => 'Long Term Loans',
                    'employee_benefits' => 'Employee Benefits', 'other_non_current_liabilities' => 'Other Non-Curr. Liab.',
                    'total_non_current_liabilities' => 'Total Non-Curr. Liab.',
                ];
                @endphp
                @foreach($liabFields as $key => $label)
                <div>
                    <x-input-label value="{{ $label }}" class="text-sm font-semibold mb-1.5" />
                    <input type="text" inputmode="decimal" name="{{ $key }}" class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                        placeholder="0" value="{{ old($key, $obligasi->$key ?? '') }}">
                </div>
                @endforeach
            </div>
        </div>

        <div>
            <h3 class="font-bold text-primary text-sm mb-3">Ekuitas</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @php
                $ekuitasFields = [
                    'share_capital' => 'Share Capital', 'additional_paid_in_capital' => 'Additional Paid-in Capital',
                    'others' => 'Others', 'non_controlling_interest' => 'Non-Controlling Interest',
                    'total_equity_equity_to_parent_entity' => 'Total Equity',
                ];
                @endphp
                @foreach($ekuitasFields as $key => $label)
                <div>
                    <x-input-label value="{{ $label }}" class="text-sm font-semibold mb-1.5" />
                    <input type="text" inputmode="decimal" name="{{ $key }}" class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                        placeholder="0" value="{{ old($key, $obligasi->$key ?? '') }}">
                </div>
                @endforeach
            </div>
        </div>

        <div>
            <h3 class="font-bold text-primary text-sm mb-3">Laba Rugi (Income Statement)</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @php
                $lrFields = [
                    'net_revenue' => 'Net Revenue', 'cost_of_good_sold' => 'COGS',
                    'gross_income' => 'Gross Income', 'operational_expense' => 'Operational Expense',
                    'other_income_expense' => 'Other Income/Expense', 'income_before_tax' => 'Income Before Tax',
                    'taxes' => 'Taxes', 'ebit' => 'EBIT', 'ebitda' => 'EBITDA',
                    'net_income_attributable_to_non_controlling_interest' => 'Net Income (Non-Controlling)',
                    'net_income' => 'Net Income',
                ];
                @endphp
                @foreach($lrFields as $key => $label)
                <div>
                    <x-input-label value="{{ $label }}" class="text-sm font-semibold mb-1.5" />
                    <input type="text" inputmode="decimal" name="{{ $key }}" class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                        placeholder="0" value="{{ old($key, $obligasi->$key ?? '') }}">
                </div>
                @endforeach
            </div>
        </div>

        <div>
            <h3 class="font-bold text-primary text-sm mb-3">Arus Kas (Cash Flow)</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @php
                $cfFields = [
                    'cash_flows_operating_activities' => 'Operating Activities',
                    'cash_flows_investment' => 'Investment',
                    'cash_flows_financing' => 'Financing',
                ];
                @endphp
                @foreach($cfFields as $key => $label)
                <div>
                    <x-input-label value="{{ $label }}" class="text-sm font-semibold mb-1.5" />
                    <input type="text" inputmode="decimal" name="{{ $key }}" class="w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent"
                        placeholder="0" value="{{ old($key, $obligasi->$key ?? '') }}">
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3 mt-5">
        <button type="submit" class="px-5 py-2.5 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent/90 transition shadow-sm shadow-accent/20">
            {{ isset($obligasi) ? 'Simpan Perubahan' : 'Tambah' }}
        </button>
        <a href="{{ route('admin.obligasi.index', ['tab' => 'bond']) }}"
            class="px-5 py-2.5 border border-line text-muted rounded-xl text-sm font-semibold hover:text-primary hover:border-primary/30 transition">Batal</a>
    </div>
</form>
@endsection
