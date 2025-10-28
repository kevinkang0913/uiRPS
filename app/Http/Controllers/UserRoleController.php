<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;

class UserRoleController extends Controller
{
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('users.assign-roles', compact('user','roles'));
    }

    public function update(Request $request, User $user)
    {
        $user->roles()->sync($request->roles ?? []);
        return redirect()->route('users.roles.edit', $user->id)->with('success','Roles updated.');
    }
}
