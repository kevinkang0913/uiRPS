<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Faculty;
use App\Models\Program;

class UserScopeController extends Controller
{
    public function edit(User $user)
    {
        $faculties = Faculty::orderBy('name')->get(['id','name']);
        $programs  = Program::orderBy('name')->get(['id','name','faculty_id']);

        return view('users.assign-scope', compact('user','faculties','programs'));
    }

    public function update(Request $request, User $user)
{
    $data = $request->validate([
        'faculty_id' => ['nullable','integer','exists:faculties,id'],
        'program_id' => ['nullable','integer','exists:programs,id'],
    ]);

    $user->faculty_id = $data['faculty_id'] ?? null;
    $user->program_id = $data['program_id'] ?? null;
    $user->save();

    return redirect()
        ->route('users.index')
        ->with('success', 'Scope fakultas/prodi berhasil diperbarui.');
}

}
