<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRoutePermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->isAdmin()) {
            return $next($request);
        }

        if ($user->isSubAdmin() && !$user->is_active) {
            abort(403, 'Akun Anda tidak aktif.');
        }

        $routeName = $request->route()?->getName();

        if ($routeName) {
            $permission = $this->resolvePermission($routeName);

            if ($permission && !$user->hasPermission($permission)) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Forbidden'], 403);
                }
                abort(403, 'Anda tidak memiliki akses ke halaman ini.');
            }
        }

        return $next($request);
    }

    private function resolvePermission(string $routeName): ?string
    {
        $map = [
            'admin.score-classifications.*' => 'manajemen.score-classifications',
            'admin.questions.*' => 'manajemen.questions',
            'admin.members.*' => 'manajemen.members',

            'admin.reksa-dana.*' => 'reksa-dana.monitor-ffs',
            'admin.analisa-rd.*' => 'reksa-dana.analisa-rd',
            'admin.analisa.*' => 'reksa-dana.monitor-analisa',
            'admin.daftar-reksa-dana.*' => 'reksa-dana.daftar',
            'admin.data-source-links.*' => 'reksa-dana.daftar',

            'admin.unit-link.*' => 'unit-link.daftar',
            'admin.unit-link-ffs.*' => 'unit-link.monitor-ffs',
            'admin.analisa-ul.*' => 'unit-link.analisa',
            'admin.unit-link-analisa.*' => 'unit-link.monitor-analisa',

            'admin.saham.*' => 'saham.daftar',
            'admin.analisa-saham.create' => 'saham.analisa',
            'admin.analisa-saham.store' => 'saham.analisa',
            'admin.analisa-saham.template' => 'saham.analisa',
            'admin.analisa-saham.parse-pdf*' => 'saham.analisa',
            'admin.analisa-saham.preview-ai*' => 'saham.analisa',
            'admin.analisa-saham.index' => 'saham.monitor-analisa',
            'admin.analisa-saham.show' => 'saham.monitor-analisa',
            'admin.analisa-saham.pdf' => 'saham.monitor-analisa',
            'admin.analisa-saham.download*' => 'saham.monitor-analisa',
            'admin.analisa-saham.review' => 'saham.monitor-analisa',
            'admin.analisa-saham.destroy' => 'saham.monitor-analisa',
            'admin.analisa-saham.riset-broker.*' => 'saham.daftar',
            'admin.analisa-saham.check-ai-status' => 'saham.monitor-analisa',

            'admin.obligasi.*' => 'obligasi.daftar',
            'admin.rating-obligasi.*' => 'obligasi.rating',
            'admin.ytm-normal-curve.*' => 'obligasi.ytm',
            'admin.analisa-obligasi.create' => 'obligasi.analisa',
            'admin.analisa-obligasi.store' => 'obligasi.analisa',
            'admin.analisa-obligasi.template' => 'obligasi.analisa',
            'admin.analisa-obligasi.parse-pdf*' => 'obligasi.analisa',
            'admin.analisa-obligasi.preview-ai*' => 'obligasi.analisa',
            'admin.analisa-obligasi.resolve-*' => 'obligasi.analisa',
            'admin.analisa-obligasi.lookup-*' => 'obligasi.analisa',
            'admin.analisa-obligasi.index' => 'obligasi.monitor-analisa',
            'admin.analisa-obligasi.show' => 'obligasi.monitor-analisa',
            'admin.analisa-obligasi.pdf' => 'obligasi.monitor-analisa',
            'admin.analisa-obligasi.download*' => 'obligasi.monitor-analisa',
            'admin.analisa-obligasi.review' => 'obligasi.monitor-analisa',
            'admin.analisa-obligasi.destroy' => 'obligasi.monitor-analisa',
            'admin.analisa-obligasi.check-ai-status' => 'obligasi.monitor-analisa',

            'admin.investment-managers.*' => 'investment-managers',
            'admin.ai-prompts.*' => 'ai-prompts',
        ];

        if (isset($map[$routeName])) {
            return $map[$routeName];
        }

        foreach ($map as $pattern => $permission) {
            if (str_ends_with($pattern, '.*')) {
                $prefix = substr($pattern, 0, -2);
                if (str_starts_with($routeName, $prefix . '.')) {
                    return $permission;
                }
            }
            if (str_ends_with($pattern, '*') && !str_ends_with($pattern, '.*')) {
                $prefix = substr($pattern, 0, -1);
                if (str_starts_with($routeName, $prefix)) {
                    return $permission;
                }
            }
        }

        return null;
    }
}
