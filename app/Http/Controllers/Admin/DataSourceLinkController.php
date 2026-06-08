<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataSourceLink;
use App\Models\DataSourceLinkUrl;
use App\Services\DataSourceSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Support\ActivityLogger;
use Illuminate\Validation\Rule;

class DataSourceLinkController extends Controller
{
    public function __construct(
        protected DataSourceSyncService $syncService,
    ) {}

    protected function redirectIndex(string $flash = 'success', ?string $message = null): RedirectResponse
    {
        $redirect = redirect()->route('admin.daftar-reksa-dana.index', ['tab' => 'link-website']);

        return $message ? $redirect->with($flash, $message) : $redirect;
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateLink($request);
        $urls = $this->syncService->syncUrls($request->input('urls', []));

        if (count($urls) < 1) {
            return back()->withInput()->withErrors(['urls' => 'Minimal satu URL wajib diisi.']);
        }

        $link = DataSourceLink::create([
            ...$data,
            'user_id' => null,
        ]);
        $this->saveUrls($link, $urls);

        ActivityLogger::log(
            'Membuat Sumber Data',
            "Sumber data {$data['nama_sumber']} berhasil ditambahkan",
            'success',
            $link,
        );

        return $this->redirectIndex('success', 'Sumber data berhasil ditambahkan.');
    }

    public function update(Request $request, DataSourceLink $dataSourceLink): RedirectResponse
    {
        $data = $this->validateLink($request, $dataSourceLink);
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

        ActivityLogger::log(
            'Mengubah Sumber Data',
            "Sumber data {$data['nama_sumber']} berhasil diperbarui",
            'success',
            $dataSourceLink,
        );

        return $this->redirectIndex('success', 'Sumber data berhasil diperbarui.');
    }

    public function destroy(DataSourceLink $dataSourceLink): RedirectResponse
    {
        ActivityLogger::log(
            'Menghapus Sumber Data',
            "Sumber data {$dataSourceLink->nama_sumber} berhasil dihapus",
            'success',
            $dataSourceLink,
        );

        $dataSourceLink->delete();

        return $this->redirectIndex('success', 'Sumber data berhasil dihapus.');
    }

    public function upload(Request $request, DataSourceLink $dataSourceLink): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $log = $this->syncService->syncFromUpload(
            $dataSourceLink,
            $request->file('file'),
            $request->user()?->id,
        );

        ActivityLogger::log(
            'Upload Data Sumber',
            $log->message,
            $log->status === 'success' ? 'success' : 'failed',
        );

        $flash = $log->status === 'success' ? 'success' : 'error';

        return $this->redirectIndex($flash, $log->message);
    }

    protected function validateLink(Request $request, ?DataSourceLink $existing = null): array
    {
        $rules = [
            'reksa_dana_id' => 'nullable|exists:reksa_dana,id',
            'nama_sumber' => 'required|string|max:255',
            'jenis_akses' => ['required', Rule::in(array_keys(DataSourceLink::JENIS_AKSES))],
            'metode_pengambilan' => ['required', Rule::in(array_keys(DataSourceLink::METODE))],
            'catatan' => 'nullable|string|max:2000',
            'is_active' => 'sometimes|boolean',
            'login_username' => 'nullable|string|max:255',
            'login_password' => 'nullable|string|max:255',
            'urls' => 'required|array|min:1',
            'urls.*.url' => 'nullable|url|max:2048',
            'urls.*.label' => 'nullable|string|max:100',
        ];

        $data = $request->validate($rules);
        $data['is_active'] = $request->boolean('is_active', true);

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
