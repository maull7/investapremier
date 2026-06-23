<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class SyncChangeLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'sync_run_id',
        'entity_type',
        'entity_id',
        'entity_label',
        'field',
        'old_value',
        'new_value',
        'change_type',
        'pending_data',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'pending_data' => 'array',
    ];

    public function syncRun(): BelongsTo
    {
        return $this->belongsTo(SyncRun::class);
    }

    public static function logCreated(int $syncRunId, string $entityType, array $fields, string $label, string|int $entityId): void
    {
        $rows = [];
        foreach ($fields as $field => $value) {
            $rows[] = [
                'sync_run_id' => $syncRunId,
                'entity_type' => $entityType,
                'entity_id' => (string) $entityId,
                'entity_label' => $label,
                'field' => $field,
                'old_value' => null,
                'new_value' => static::stringify($value),
                'change_type' => 'created',
            ];
        }
        if ($rows) static::insert($rows);
    }

    public static function logUpdated(int $syncRunId, string $entityType, array $diffs, string $label, string|int $entityId): void
    {
        $rows = [];
        foreach ($diffs as $field => $diff) {
            $rows[] = [
                'sync_run_id' => $syncRunId,
                'entity_type' => $entityType,
                'entity_id' => (string) $entityId,
                'entity_label' => $label,
                'field' => $field,
                'old_value' => static::stringify($diff['old'] ?? null),
                'new_value' => static::stringify($diff['new'] ?? null),
                'change_type' => 'updated',
            ];
        }
        if ($rows) static::insert($rows);
    }

    public static function logDeleted(int $syncRunId, string $entityType, string $label, string|int $entityId): void
    {
        static::insert([
            'sync_run_id' => $syncRunId,
            'entity_type' => $entityType,
            'entity_id' => (string) $entityId,
            'entity_label' => $label,
            'field' => '',
            'old_value' => null,
            'new_value' => null,
            'change_type' => 'deleted',
        ]);
    }

    public static function captureModelDiff(int $syncRunId, string $entityType, ?Model $old, array $newAttrs, string $label, string|int $entityId): void
    {
        if (!$old) {
            static::logCreated($syncRunId, $entityType, $newAttrs, $label, $entityId);
            return;
        }
        $diffs = [];
        $fillable = array_intersect_key($newAttrs, array_flip($old->getFillable()));
        foreach ($fillable as $field => $newVal) {
            $oldVal = $old->{$field};
            if ($oldVal instanceof \DateTime) $oldVal = $oldVal->format('Y-m-d');
            if ($oldVal instanceof \BackedEnum) $oldVal = $oldVal->value;
            $oldStr = static::stringify($oldVal);
            $newStr = static::stringify($newVal);
            if ($oldStr !== $newStr) {
                $diffs[$field] = ['old' => $oldVal, 'new' => $newVal];
            }
        }
        if ($diffs) {
            static::logUpdated($syncRunId, $entityType, $diffs, $label, $entityId);
        }
    }

    public static function captureCollectionDiff(int $syncRunId, string $entityType, Collection $oldModels, array $newDataList, callable $getKey, callable $getMatchAttrs, callable $getLabel, callable $getEntityId): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0];
        $keyed = [];
        foreach ($oldModels as $m) {
            $keyed[$getKey($m)] = $m;
        }
        foreach ($newDataList as $item) {
            $key = $getKey($item);
            $old = $keyed[$key] ?? null;
            $newAttrs = $getMatchAttrs($item);
            $label = $getLabel($item, $old);
            $entityId = $getEntityId($item, $old);

            if ($old) {
                static::captureModelDiff($syncRunId, $entityType, $old, $newAttrs, $label, $entityId);
                $stats['updated']++;
            } else {
                static::logCreated($syncRunId, $entityType, $newAttrs, $label, $entityId);
                $stats['created']++;
            }
        }
        return $stats;
    }

    private static function stringify(mixed $val): string
    {
        if ($val === null || $val === '') return '';
        if ($val instanceof \DateTime) return $val->format('Y-m-d H:i:s');
        if ($val instanceof \BackedEnum) return (string) $val->value;
        if (is_bool($val)) return $val ? '1' : '0';
        if (is_array($val)) return json_encode($val);
        if (is_object($val)) return method_exists($val, '__toString') ? (string) $val : json_encode($val);
        return (string) $val;
    }
}
