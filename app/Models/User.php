<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_member', 'phone', 'avatar', 'google_id', 'permissions', 'is_active', 'advisor_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_member' => 'boolean',
            'is_active' => 'boolean',
            'permissions' => 'array',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSubAdmin(): bool
    {
        return $this->role === 'sub_admin';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function isAdvisor(): bool
    {
        return $this->role === 'advisor';
    }

    public function isMember(): bool
    {
        return $this->is_member;
    }

    public function stockPriceAlerts(): HasMany
    {
        return $this->hasMany(StockPriceAlert::class);
    }

    public function advisor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    public function clients(): HasMany
    {
        return $this->hasMany(User::class, 'advisor_id');
    }

    public function memberProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MemberProfile::class, 'user_id');
    }

    public function perencanaanInvestasi(): HasMany
    {
        return $this->hasMany(PerencanaanInvestasi::class, 'user_id');
    }

    public function memberPortfolios(): HasMany
    {
        return $this->hasMany(MemberPortfolio::class, 'user_id');
    }

    public function portofolioItems(): HasMany
    {
        return $this->hasMany(PortofolioItem::class, 'user_id');
    }

    /**
     * Check if user has a specific permission.
     * Format: 'menu', 'menu.submenu', 'menu.submenu.tab'
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (!$this->is_active) {
            return false;
        }

        $perms = $this->permissions ?? [];
        if (empty($perms)) {
            return false;
        }

        $parts = explode('.', $permission);

        $current = $perms;
        foreach ($parts as $part) {
            if (!is_array($current) || !array_key_exists($part, $current)) {
                return false;
            }
            $value = $current[$part];
            if ($value === false) {
                return false;
            }
            // true / "1" / 1 = granted (parent granted means all children granted)
            if ($value === true || $value === '1' || $value === 1) {
                return true;
            }
            $current = $value;
        }

        return $current === true || $current === '1' || $current === 1
            || (is_array($current) && !empty($current));
    }

    /**
     * Get flat list of all granted permissions.
     */
    public function getPermissionsList(): array
    {
        if ($this->isAdmin()) {
            return ['*'];
        }
        return $this->flattenPermissions($this->permissions ?? []);
    }

    private function flattenPermissions(array $perms, string $prefix = ''): array
    {
        $list = [];
        foreach ($perms as $key => $value) {
            $full = $prefix ? "{$prefix}.{$key}" : $key;
            if ($value === true || $value === '1' || $value === 1) {
                $list[] = $full;
            } elseif (is_array($value)) {
                $list = array_merge($list, $this->flattenPermissions($value, $full));
            }
        }
        return $list;
    }
}
