<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;

class UserRoleController extends Controller
{
    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();

        return view('users.assign-roles', compact('user','roles'));
    }

   public function update(Request $request, User $user)
{
    $data = $request->validate([
        'roles'   => ['nullable','array'],
        'roles.*' => ['integer','exists:roles,id'],
    ]);

    $user->roles()->sync($data['roles'] ?? []);

    return redirect()
        ->route('users.index')
        ->with('success','Roles updated.');
}

}
