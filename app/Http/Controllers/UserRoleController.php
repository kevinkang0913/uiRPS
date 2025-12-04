<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;

class UserRoleController extends Controller
{
    public function edit(Request $request, User $user)
    {
        $current = $request->user();

        $rolesQuery = Role::orderBy('name');

        // 🔒 Admin biasa: hanya boleh assign Dosen & Kaprodi
        if ($current->hasRole('Admin') && ! $current->hasRole('Super Admin')) {
            $rolesQuery->whereIn('name', ['Dosen', 'Kaprodi']);
        }

        $roles = $rolesQuery->get();

        return view('users.assign-roles', compact('user','roles'));
    }

    public function update(Request $request, User $user)
    {
        $current = $request->user();

        $data = $request->validate([
            'roles'   => ['nullable','array'],
            'roles.*' => ['integer','exists:roles,id'],
        ]);

        $roleIds = $data['roles'] ?? [];

        // 🔒 Admin biasa TIDAK BOLEH ubah role user yang Super Admin
        if ($current->hasRole('Admin') && ! $current->hasRole('Super Admin')) {
            if ($user->hasRole('Super Admin')) {
                abort(403, 'Anda tidak boleh mengubah role untuk Super Admin.');
            }
        }

        // 🔒 Admin biasa: sanitize list role → hanya Dosen & Kaprodi
        if ($current->hasRole('Admin') && ! $current->hasRole('Super Admin')) {
            $allowedIds = Role::whereIn('name', ['Dosen', 'Kaprodi'])
                ->pluck('id')
                ->all();

            $roleIds = array_values(array_intersect($roleIds, $allowedIds));
        }

        $user->roles()->sync($roleIds);

        return redirect()
            ->route('users.index')
            ->with('success','Roles updated.');
    }
}
