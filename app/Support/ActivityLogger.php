<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public static function log(
        string $aksi,
        ?string $keterangan = null,
        string $status = 'success',
        ?Model $model = null,
        ?array $data = null,
    ): ActivityLog {
        $user = auth()->user();

        return ActivityLog::create([
            'user_id'    => $user?->id,
            'aksi'       => $aksi,
            'keterangan' => $keterangan,
            'status'     => $status,
            'model_type' => $model ? get_class($model) : null,
            'model_id'   => $model?->getKey(),
            'data'       => $data,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
