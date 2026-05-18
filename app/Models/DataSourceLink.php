<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DataSourceLink extends Model
{
    public const JENIS_AKSES = [
        'public' => 'Publik',
        'login' => 'Butuh Login',
        'subscription' => 'Berlangganan',
    ];

    public const METODE = [
        'manual' => 'Upload Manual',
        'api' => 'API',
        'auto_download' => 'Unduh Otomatis',
        'scrape' => 'Scrape HTML',
    ];

    public const SYNC_STATUS = [
        'never' => 'Belum pernah',
        'success' => 'Berhasil',
        'failed' => 'Gagal',
    ];

    protected $fillable = [
        'user_id', 'reksa_dana_id', 'nama_sumber', 'jenis_akses', 'metode_pengambilan',
        'catatan', 'is_active', 'login_username', 'login_password',
        'last_synced_at', 'last_sync_status', 'last_sync_message',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
        'login_username' => 'encrypted',
        'login_password' => 'encrypted',
    ];

    protected $hidden = [
        'login_username',
        'login_password',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reksaDana(): BelongsTo
    {
        return $this->belongsTo(ReksaDana::class, 'reksa_dana_id');
    }

    public function scopeForUser($query, ?int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeGlobal($query)
    {
        return $query->whereNull('user_id');
    }

    public function urls(): HasMany
    {
        return $this->hasMany(DataSourceLinkUrl::class)->orderBy('sort_order')->orderBy('id');
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(DataSourceSyncLog::class)->latest();
    }

    public function jenisAksesLabel(): string
    {
        return self::JENIS_AKSES[$this->jenis_akses] ?? $this->jenis_akses;
    }

    public function metodeLabel(): string
    {
        return self::METODE[$this->metode_pengambilan] ?? $this->metode_pengambilan;
    }

    public function syncStatusLabel(): string
    {
        return self::SYNC_STATUS[$this->last_sync_status] ?? $this->last_sync_status;
    }
}
