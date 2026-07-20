{{-- Arus Kas (Cash Flow Statement) --}}
<div class="bg-white rounded-xl border border-line p-6 space-y-3">
    <h3 class="font-semibold text-primary">Arus Kas (Cash Flow Statement)</h3>
    <div class="space-y-3 max-w-xl">
        @foreach([
            ['cash_flows_operating_activities','Arus Kas dari Operasi'],
            ['cash_flows_investment','Arus Kas dari Investasi'],
            ['cash_flows_financing','Arus Kas dari Pendanaan'],
        ] as [$name, $label])
        <div class="flex items-center gap-2">
            <label class="w-52 text-xs text-gray-600 shrink-0">{{ $label }}</label>
            <input type="number" name="{{ $name }}" x-model="{{ $name }}" step="0.01" value="{{ old($name) }}"
                class="flex-1 border-gray-300 rounded-lg text-sm focus:border-primary focus:ring focus:ring-primary/20">
        </div>
        @endforeach
    </div>
</div>
