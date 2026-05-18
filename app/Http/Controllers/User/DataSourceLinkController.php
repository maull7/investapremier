<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DataSourceLink;
use App\Models\DataSourceLinkUrl;
use App\Services\DataSourceSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DataSourceLinkController extends Controller
{
    public function __construct(
        protected DataSourceSyncService $syncService,
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateLink($request);
        $urls = $this->syncService->syncUrls($request->input('urls', []));

        if (count($urls) < 1) {
            return back()->withInput()->withErrors(['urls' => 'Minimal satu URL wajib diisi.']);
        }

        $link = DataSourceLink::create([
            ...$data,
            'user_id' => auth()->id(),
            'metode_pengambilan' => $data['metode_pengambilan'] ?? 'manual',
        ]);
        $this->saveUrls($link, $urls);

        return $this->redirectBack('success', 'Link sumber data berhasil disimpan.');
    }

    public function update(Request $request, DataSourceLink $dataSourceLink): RedirectResponse
    {
        $this->ensureOwned($dataSourceLink);

        $data = $this->validateLink($request);
        $urls = $this->syncService->syncUrls($request->input('urls', []));

        if (count($urls) < 1) {
            return back()->withInput()->withErrors(['urls' => 'Minimal satu URL wajib diisi.']);
        }

        if (empty($data['login_password'])) {
            unset($data['login_password']);
        }
        if (empty($data['login_username'])) {
            unset($data['login_username']);
        }

        $dataSourceLink->update($data);
        $dataSourceLink->urls()->delete();
        $this->saveUrls($dataSourceLink, $urls);

        return $this->redirectBack('success', 'Link berhasil diperbarui.');
    }

    public function destroy(DataSourceLink $dataSourceLink): RedirectResponse
    {
        $this->ensureOwned($dataSourceLink);
        $dataSourceLink->delete();

        return $this->redirectBack('success', 'Link berhasil dihapus.');
    }

    public function upload(Request $request, DataSourceLink $dataSourceLink): RedirectResponse
    {
        $this->ensureOwned($dataSourceLink);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $log = $this->syncService->syncFromUpload(
            $dataSourceLink,
            $request->file('file'),
            auth()->id(),
        );

        $flash = $log->status === 'success' ? 'success' : 'error';

        return $this->redirectBack($flash, $log->message);
    }

    protected function ensureOwned(DataSourceLink $link): void
    {
        if ($link->user_id !== auth()->id()) {
            abort(403, 'Anda tidak dapat mengubah link ini.');
        }
    }

    protected function redirectBack(string $flash, string $message): RedirectResponse
    {
        return redirect()
            ->route('user.analisa.create', ['tab' => 'link-website'])
            ->with($flash, $message);
    }

    protected function validateLink(Request $request): array
    {
        $data = $request->validate([
            'nama_sumber' => 'required|string|max:255',
            'jenis_akses' => ['required', Rule::in(array_keys(DataSourceLink::JENIS_AKSES))],
            'metode_pengambilan' => ['nullable', Rule::in(array_keys(DataSourceLink::METODE))],
            'catatan' => 'nullable|string|max:2000',
            'is_active' => 'sometimes|boolean',
            'login_username' => 'nullable|string|max:255',
            'login_password' => 'nullable|string|max:255',
            'urls' => 'required|array|min:1',
            'urls.*.url' => 'nullable|url|max:2048',
            'urls.*.label' => 'nullable|string|max:100',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['metode_pengambilan'] = $data['metode_pengambilan'] ?? 'manual';

        return $data;
    }

    protected function saveUrls(DataSourceLink $link, array $urls): void
    {
        foreach ($urls as $item) {
            DataSourceLinkUrl::create([
                'data_source_link_id' => $link->id,
                'label' => $item['label'],
                'url' => $item['url'],
                'sort_order' => $item['sort_order'],
            ]);
        }
    }
}
