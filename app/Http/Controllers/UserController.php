<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Kalau kamu pakai policy/gate, pastikan superadmin lolos di sana.
        // Kalau belum ada, bisa komentari dulu baris ini.
        // $this->authorize('viewAny', User::class);

        $q    = trim((string) $request->get('q', ''));
        $role = trim((string) $request->get('role', ''));

        // NOTE:
        // DB kamu pakai pivot: role_user (user_id, role_id) :contentReference[oaicite:1]{index=1}
        $usersQuery = User::query()
            ->select('users.*')
            ->with(['roles', 'faculty', 'program'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('users.name', 'like', "%{$q}%")
                      ->orWhere('users.email', 'like', "%{$q}%");
                });
            })
            ->when($role !== '' && $role !== 'Semua', function ($query) use ($role) {
                // filter berdasarkan nama role
                $query->whereHas('roles', function ($r) use ($role) {
                    $r->where('roles.name', $role);
                });
            });

        // PIN: Super Admin & CTL selalu paling atas
        // (pakai EXISTS subquery supaya tidak bikin pagination count error)
        $pinnedRoles = ["Super Admin", "CTL"];

        $usersQuery
            ->orderByRaw("
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM role_user ru
                        JOIN roles r ON r.id = ru.role_id
                        WHERE ru.user_id = users.id
                          AND r.name IN ('" . implode("','", array_map('addslashes', $pinnedRoles)) . "')
                    )
                    THEN 0 ELSE 1
                END
            ")
            // urutan dalam pinned: Super Admin dulu, lalu CTL, lalu sisanya
            ->orderByRaw("
                CASE
                    WHEN EXISTS (
                        SELECT 1 FROM role_user ru
                        JOIN roles r ON r.id = ru.role_id
                        WHERE ru.user_id = users.id AND r.name = 'Super Admin'
                    ) THEN 0
                    WHEN EXISTS (
                        SELECT 1 FROM role_user ru
                        JOIN roles r ON r.id = ru.role_id
                        WHERE ru.user_id = users.id AND r.name = 'CTL'
                    ) THEN 1
                    ELSE 2
                END
            ")
            // lalu rapihin sisanya
            ->orderBy('users.name');

        $users = $usersQuery->paginate(15)->withQueryString();

        // dropdown role
        $roles = DB::table('roles')->orderBy('name')->pluck('name');

        $filters = [
            'q'    => $q,
            'role' => $role,
        ];

        return view('users.index', compact('users', 'roles', 'filters'));
    }
}
