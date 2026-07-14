<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SubAdminController extends Controller
{
    // Struktur menu yang bisa di-assign ke sub admin
    public static function menuStructure(): array
    {
        return [
            'manajemen' => [
                'label' => 'Manajemen',
                'children' => [
                    'dashboard' => ['label' => 'Dashboard'],
                    'members' => ['label' => 'Pendaftaran Member'],
                    'questions' => ['label' => 'Soal Kuis'],
                    'score-classifications' => ['label' => 'Klasifikasi Skor'],
                ],
            ],
            'reksa-dana' => [
                'label' => 'Reksa Dana',
                'children' => [
                    'monitor-ffs' => ['label' => 'Monitor Reksa Dana FFS'],
                    'analisa-rd' => ['label' => 'Analisa Reksa Dana'],
                    'monitor-analisa' => ['label' => 'Monitor Analisa Reksa Dana'],
                    'daftar' => ['label' => 'Daftar Reksa Dana'],
                ],
            ],
            'unit-link' => [
                'label' => 'Unit Link',
                'children' => [
                    'daftar' => ['label' => 'Daftar Unit Link'],
                    'monitor-ffs' => ['label' => 'Monitor Unit Link FFS'],
                    'analisa' => ['label' => 'Analisa Unit Link'],
                    'monitor-analisa' => ['label' => 'Monitor Analisa Unit Link'],
                ],
            ],
            'saham' => [
                'label' => 'Saham',
                'children' => [
                    'daftar' => [
                        'label' => 'Daftar Saham',
                        'tabs' => [
                            'snapshot' => ['label' => 'Snapshot'],
                            'grafik' => ['label' => 'Grafik dan Data'],
                            'risiko' => ['label' => 'Risiko'],
                            'riset-broker' => ['label' => 'Riset Broker'],
                        ],
                    ],
                    'analisa' => ['label' => 'Analisa Saham'],
                    'monitor-analisa' => ['label' => 'Monitor Analisa Saham'],
                ],
            ],
            'obligasi' => [
                'label' => 'Obligasi',
                'children' => [
                    'daftar' => ['label' => 'Daftar Obligasi'],
                    'rating' => ['label' => 'Rating Obligasi'],
                    'ytm' => ['label' => 'YTM Normal Curve'],
                    'sekuritas-informasi' => ['label' => 'Sekuritas Informasi'],
                    'analisa' => ['label' => 'Analisa Obligasi'],
                    'monitor-analisa' => ['label' => 'Monitor Analisa Obligasi'],
                ],
            ],
            'investment-managers' => [
                'label' => 'Manajer Investasi',
                'children' => [],
            ],
            'ai-prompts' => [
                'label' => 'AI Prompts',
                'children' => [],
            ],
        ];
    }

    public function index()
    {
        $subAdmins = User::where('role', 'sub_admin')->latest()->paginate(20);
        return view('admin.sub-admins.index', compact('subAdmins'));
    }

    public function create()
    {
        $menuStructure = self::menuStructure();
        return view('admin.sub-admins.create', compact('menuStructure'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $permissions = $this->normalizePermissions($request->input('permissions', []));

        if (empty($permissions)) {
            return back()->withInput()->withErrors(['permissions' => 'Pilih minimal satu permission.']);
        }

        $subAdmin = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => 'sub_admin',
            'is_active' => $request->boolean('is_active', true),
            'permissions' => $permissions,
        ]);

        ActivityLogger::log(
            'Membuat Sub Admin',
            "Sub Admin {$subAdmin->name} ({$subAdmin->email}) berhasil dibuat",
            'success',
            $subAdmin,
        );

        return redirect()->route('admin.sub-admins.index')
            ->with('success', 'Sub Admin berhasil dibuat.');
    }

    public function show(User $subAdmin)
    {
        abort_if($subAdmin->role !== 'sub_admin', 404);
        $menuStructure = self::menuStructure();
        return view('admin.sub-admins.show', compact('subAdmin', 'menuStructure'));
    }

    public function edit(User $subAdmin)
    {
        abort_if($subAdmin->role !== 'sub_admin', 404);
        $menuStructure = self::menuStructure();
        return view('admin.sub-admins.edit', compact('subAdmin', 'menuStructure'));
    }

    public function update(Request $request, User $subAdmin)
    {
        abort_if($subAdmin->role !== 'sub_admin', 404);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $subAdmin->id],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        $permissions = $this->normalizePermissions($request->input('permissions', []));

        if (empty($permissions)) {
            return back()->withInput()->withErrors(['permissions' => 'Pilih minimal satu permission.']);
        }

        $update = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'is_active' => $request->boolean('is_active', true),
            'permissions' => $permissions,
        ];

        if ($request->filled('password')) {
            $update['password'] = Hash::make($request->input('password'));
        }

        $subAdmin->update($update);

        ActivityLogger::log(
            'Mengubah Sub Admin',
            "Sub Admin {$subAdmin->name} ({$subAdmin->email}) berhasil diperbarui",
            'success',
            $subAdmin,
        );

        return redirect()->route('admin.sub-admins.index')
            ->with('success', 'Sub Admin berhasil diperbarui.');
    }

    /**
     * Normalize permissions dari form input.
     * Form mengirim nilai "1" (string), kita ubah ke struktur tree boolean.
     * Jika parent key di-check beserta children, simpan sebagai array (tidak sebagai "1").
     */
    private function normalizePermissions(array $raw): array
    {
        $result = [];
        foreach ($raw as $menuKey => $menuValue) {
            if (is_array($menuValue)) {
                // Ada children yang di-submit
                $children = [];
                foreach ($menuValue as $subKey => $subValue) {
                    if (is_array($subValue)) {
                        $tabs = [];
                        foreach ($subValue as $tabKey => $tabVal) {
                            if ($tabVal === '1' || $tabVal === true || $tabVal === 1) {
                                $tabs[$tabKey] = true;
                            }
                        }
                        if (!empty($tabs)) {
                            $children[$subKey] = $tabs;
                        }
                    } elseif ($subValue === '1' || $subValue === true || $subValue === 1) {
                        $children[$subKey] = true;
                    }
                }
                if (!empty($children)) {
                    $result[$menuKey] = $children;
                }
            } elseif ($menuValue === '1' || $menuValue === true || $menuValue === 1) {
                $result[$menuKey] = true;
            }
        }
        return $result;
    }

    public function toggleStatus(User $subAdmin)
    {
        abort_if($subAdmin->role !== 'sub_admin', 404);

        $newStatus = !$subAdmin->is_active;
        $subAdmin->update(['is_active' => $newStatus]);

        ActivityLogger::log(
            $newStatus ? 'Mengaktifkan Sub Admin' : 'Menonaktifkan Sub Admin',
            "Sub Admin {$subAdmin->name} ({$subAdmin->email}) " . ($newStatus ? 'diaktifkan' : 'dinonaktifkan'),
            'success',
            $subAdmin,
        );

        return back()->with('success', "Sub Admin berhasil " . ($newStatus ? 'diaktifkan' : 'dinonaktifkan') . ".");
    }

    public function destroy(User $subAdmin)
    {
        abort_if($subAdmin->role !== 'sub_admin', 404);
        ActivityLogger::log(
            'Menghapus Sub Admin',
            "Sub Admin {$subAdmin->name} ({$subAdmin->email}) berhasil dihapus",
            'success',
            $subAdmin,
        );
        $subAdmin->delete();
        return back()->with('success', 'Sub Admin berhasil dihapus.');
    }
}
