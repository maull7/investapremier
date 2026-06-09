<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\StockPriceAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class StockPriceAlertController extends Controller
{
    public function index(Request $request): View
    {
        $alerts = $request->user()
            ->stockPriceAlerts()
            ->with('stock:id,kode,nama')
            ->latest()
            ->paginate(15);

        return view('notifications.alerts.index', compact('alerts'));
    }

    public function create(): View
    {
        $stocks = Stock::orderBy('kode')->get(['id', 'kode', 'nama', 'harga_terbaru']);

        return view('notifications.alerts.form', [
            'alert'  => new StockPriceAlert(['condition' => 'above', 'is_active' => true]),
            'stocks' => $stocks,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $stock = Stock::find($data['stock_id'] ?? null);
        if ($stock) {
            $data['kode_efek'] = $stock->kode;
        }
        $data['kode_efek'] = strtoupper($data['kode_efek']);
        $data['user_id']   = $request->user()->id;

        StockPriceAlert::create($data);

        return redirect()
            ->route('user.price-alerts.index')
            ->with('success', 'Alert harga saham berhasil dibuat.');
    }

    public function edit(StockPriceAlert $priceAlert): View
    {
        $this->authorizeOwnership($priceAlert);

        $stocks = Stock::orderBy('kode')->get(['id', 'kode', 'nama', 'harga_terbaru']);

        return view('notifications.alerts.form', [
            'alert'  => $priceAlert,
            'stocks' => $stocks,
        ]);
    }

    public function update(Request $request, StockPriceAlert $priceAlert): RedirectResponse
    {
        $this->authorizeOwnership($priceAlert);

        $data = $this->validateData($request);

        $stock = Stock::find($data['stock_id'] ?? null);
        if ($stock) {
            $data['kode_efek'] = $stock->kode;
        }
        $data['kode_efek'] = strtoupper($data['kode_efek']);

        $priceAlert->update($data);

        return redirect()
            ->route('user.price-alerts.index')
            ->with('success', 'Alert harga saham diperbarui.');
    }

    public function destroy(StockPriceAlert $priceAlert): RedirectResponse
    {
        $this->authorizeOwnership($priceAlert);

        $priceAlert->delete();

        return back()->with('success', 'Alert harga saham dihapus.');
    }

    public function toggle(StockPriceAlert $priceAlert): RedirectResponse
    {
        $this->authorizeOwnership($priceAlert);

        $priceAlert->update(['is_active' => ! $priceAlert->is_active]);

        return back()->with(
            'success',
            $priceAlert->is_active ? 'Alert diaktifkan.' : 'Alert dinonaktifkan.',
        );
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'stock_id'     => ['nullable', 'integer', 'exists:stocks,id'],
            'kode_efek'    => ['required', 'string', 'max:20'],
            'condition'    => ['required', 'in:above,below'],
            'target_price' => ['required', 'numeric', 'min:0'],
            'note'         => ['nullable', 'string', 'max:255'],
            'is_active'    => ['nullable', 'boolean'],
            'repeat'       => ['nullable', 'boolean'],
        ]);
    }

    private function authorizeOwnership(StockPriceAlert $alert): void
    {
        abort_unless($alert->user_id === auth()->id(), 403);
    }
}
