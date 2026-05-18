<?php

namespace App\Services;

use App\Imports\WebsiteNavImport;
use App\Models\DataSourceLink;
use App\Models\DataSourceSyncLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DataSourceSyncService
{
    public function syncFromUpload(DataSourceLink $link, UploadedFile $file, ?int $userId = null): DataSourceSyncLog
    {
        $storedPath = $file->store('data-source-sync/' . $link->id, 'local');

        try {
            $import = new WebsiteNavImport($link->reksa_dana_id);
            Excel::import($import, $file);

            $message = $import->imported > 0
                ? "Berhasil mengimpor {$import->imported} baris NAV."
                : 'File diproses tetapi tidak ada baris yang valid.';

            if ($import->skipped > 0) {
                $message .= " ({$import->skipped} baris dilewati)";
            }

            $status = $import->imported > 0 ? 'success' : 'failed';

            return $this->finish($link, $status, $message, $import->imported, $storedPath, $userId);
        } catch (\Throwable $e) {
            return $this->finish($link, 'failed', 'Gagal memproses file: ' . $e->getMessage(), 0, $storedPath, $userId);
        }
    }

    protected function finish(
        DataSourceLink $link,
        string $status,
        string $message,
        int $rowsImported,
        ?string $filePath,
        ?int $userId,
    ): DataSourceSyncLog {
        return DB::transaction(function () use ($link, $status, $message, $rowsImported, $filePath, $userId) {
            $log = DataSourceSyncLog::create([
                'data_source_link_id' => $link->id,
                'user_id' => $userId,
                'status' => $status,
                'message' => $message,
                'rows_imported' => $rowsImported,
                'file_path' => $filePath,
            ]);

            $link->update([
                'last_synced_at' => now(),
                'last_sync_status' => $status,
                'last_sync_message' => $message,
            ]);

            return $log;
        });
    }

    public function syncUrls(array $validatedUrls): array
    {
        $urls = [];
        foreach ($validatedUrls as $i => $item) {
            $url = trim($item['url'] ?? '');
            if ($url === '') {
                continue;
            }
            $urls[] = [
                'label' => trim($item['label'] ?? '') ?: null,
                'url' => $url,
                'sort_order' => $i,
            ];
        }

        return $urls;
    }
}
