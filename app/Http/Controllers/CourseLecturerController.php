<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseLecturerController extends Controller
{
    public function edit(Request $request, Course $course)
    {
        $user = $request->user();

        abort_unless($user->hasAnyRole(['Super Admin','Admin']), 403);

        // Admin Fakultas hanya boleh manage course dalam fakultasnya
        if ($user->hasRole('Admin') && !$user->hasRole('Super Admin')) {
            $facultyId = $user->faculty_id;

            abort_unless($facultyId, 403);

            $course->loadMissing('program');
            abort_unless(optional($course->program)->faculty_id === $facultyId, 403);
        }

        // daftar dosen (pool) — kamu bisa filter aktif saja kalau mau
        $lecturers = User::query()
            ->whereHas('roles', fn($q) => $q->where('name', 'Dosen'))
            ->orderBy('name')
            ->get(['id','name','email']);

        $assigned = $course->lecturers()
            ->orderBy('name')
            ->get(['users.id','users.name','users.email']);

        return view('courses.lecturers', compact('course','lecturers','assigned'));
    }

    public function store(Request $request, Course $course)
    {
        $actor = $request->user();

        abort_unless($actor->hasAnyRole(['Super Admin','Admin']), 403);

        if ($actor->hasRole('Admin') && !$actor->hasRole('Super Admin')) {
            $facultyId = $actor->faculty_id;
            abort_unless($facultyId, 403);

            $course->loadMissing('program');
            abort_unless(optional($course->program)->faculty_id === $facultyId, 403);
        }

        $data = $request->validate([
            'user_id'        => ['required','integer','exists:users,id'],
            'can_edit'       => ['nullable','boolean'],
            'is_responsible' => ['nullable','boolean'],
        ]);

        $lecturer = User::findOrFail($data['user_id']);

        // pastikan user itu memang dosen (punya role Dosen)
        abort_unless($lecturer->hasRole('Dosen'), 422);

        $canEdit = (bool) ($data['can_edit'] ?? false);
        $isPic   = (bool) ($data['is_responsible'] ?? false);

        DB::transaction(function () use ($course, $lecturer, $canEdit, $isPic) {

            // attach atau update pivot
            $course->lecturers()->syncWithoutDetaching([
                $lecturer->id => [
                    'can_edit'       => $canEdit ? 1 : 0,
                    'is_responsible' => $isPic ? 1 : 0,
                ]
            ]);

            // enforce: PIC hanya 1 per course
            if ($isPic) {
                $course->lecturers()
                    ->wherePivot('is_responsible', 1)
                    ->where('users.id', '!=', $lecturer->id)
                    ->updateExistingPivot(
                        $course->lecturers()
                            ->wherePivot('is_responsible', 1)
                            ->where('users.id','!=',$lecturer->id)
                            ->pluck('users.id')
                            ->all(),
                        ['is_responsible' => 0]
                    );
            }
        });

        return back()->with('success', 'Assignment dosen berhasil disimpan.');
    }

    public function destroy(Request $request, Course $course, User $user)
    {
        $actor = $request->user();

        abort_unless($actor->hasAnyRole(['Super Admin','Admin']), 403);

        if ($actor->hasRole('Admin') && !$actor->hasRole('Super Admin')) {
            $facultyId = $actor->faculty_id;
            abort_unless($facultyId, 403);

            $course->loadMissing('program');
            abort_unless(optional($course->program)->faculty_id === $facultyId, 403);
        }

        // detach
        $course->lecturers()->detach($user->id);

        return back()->with('success', 'Dosen berhasil dihapus dari course.');
    }
}
