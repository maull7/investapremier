<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncRun extends Model
{
    public const STATUS_QUEUED = 'queued';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    public const TYPE_OBLIGASI_IDX_PHEI = 'obligasi_idx_phei';
    public const TYPE_SAHAM_IDX = 'saham_idx';
    public const TYPE_MI_PASARDANA = 'mi_pasardana';
    public const TYPE_RD_PASARDANA = 'rd_pasardana';
    public const TYPE_MI_PERIOD = 'mi_period';
    public const TYPE_RD_HARGA_HARIAN = 'rd_harga_harian';
    public const TYPE_RELASI_MI_RD = 'relasi_mi_rd';
    public const TYPE_ALL_PASARDANA = 'all_pasardana';
    public const TYPE_REPLACE_REWRITE = 'replace_rewrite';

    protected $fillable = [
        'type', 'status', 'current_step', 'current_step_label',
        'progress_percent', 'stats', 'errors', 'message', 'user_id',
        'started_at', 'completed_at', 'applied_at',
    ];

    protected $casts = [
        'stats' => 'array',
        'errors' => 'array',
        'progress_percent' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'applied_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark the run as actively running a given step, with progress percent and
     * a human-readable label for the UI.
     */
    public function markStep(string $stepKey, string $label, int $percent): void
    {
        $this->update([
            'status' => self::STATUS_RUNNING,
            'current_step' => $stepKey,
            'current_step_label' => $label,
            'progress_percent' => max(0, min(100, $percent)),
            'started_at' => $this->started_at ?? now(),
        ]);
    }

    public function markCompleted(string $message, ?array $stats = null, ?array $errors = null): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'progress_percent' => 100,
            'message' => $message,
            'stats' => $stats,
            'errors' => $errors,
            'completed_at' => now(),
        ]);
    }

    public function markFailed(string $message, ?array $errors = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'message' => $message,
            'errors' => $errors,
            'completed_at' => now(),
        ]);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_FAILED,
            self::STATUS_CANCELLED,
        ], true);
    }
}
