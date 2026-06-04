<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubAdminPermission
{
    /**
     * Usage: middleware('permission:reksa-dana.monitor-ffs')
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        // Admin utama selalu lolos
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Sub admin yang tidak aktif
        if ($user->isSubAdmin() && !$user->is_active) {
            abort(403, 'Akun Anda tidak aktif.');
        }

        if (!$user->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
