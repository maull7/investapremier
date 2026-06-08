<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array($request->per_page, [10, 25, 50, 100]) ? $request->per_page : 25;
        $query = ActivityLog::with('user')->latest();

        if ($request->filled('aksi')) {
            $query->where('aksi', $request->aksi);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('keterangan', 'like', "%{$s}%")
                    ->orWhere('aksi', 'like', "%{$s}%")
                    ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$s}%"));
            });
        }

        $logs = $query->paginate($perPage)->withQueryString();

        $aksiList = ActivityLog::select('aksi')->distinct()->pluck('aksi')->sort();
        $users = \App\Models\User::whereIn('role', ['admin', 'sub_admin'])->orderBy('name')->get(['id', 'name', 'role']);

        return view('admin.activity-logs.index', compact('logs', 'aksiList', 'users', 'perPage'));
    }
}
