<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $q         = trim($request->string('q')->toString());
        $facultyId = $request->integer('faculty_id');
        $programId = $request->integer('program_id');

        // NOTE: kamu sebelumnya whitelist semester/class_number/created_at,
        // aku biarkan sesuai punyamu supaya tidak mengubah behavior.
        $sort      = in_array($request->input('sort'), ['semester','class_number','created_at'])
                        ? $request->input('sort') : 'created_at';
        $dir       = $request->string('dir') === 'desc' ? 'desc' : 'asc';
        $perPage   = max(5, min((int)$request->integer('per_page') ?: 15, 100));

        // 🔒 Kalau Admin biasa (bukan Super Admin) & punya faculty_id → paksa ke fakultas dia
        $isFacultyAdmin = $user
            && $user->hasRole('Admin')
            && ! $user->hasRole('Super Admin')
            && $user->faculty_id;

        if ($isFacultyAdmin) {
            $facultyId = $user->faculty_id; // override input faculty_id
        }

        $items = Course::with('program.faculty')
            // scope berdasarkan fakultas ADMIN (safety tambahan)
            ->when($isFacultyAdmin, function ($q2) use ($user) {
                $q2->whereHas('program', fn($w) => $w->where('faculty_id', $user->faculty_id));
            })
            // filter program (kalau dipilih)
            ->when($programId, fn($q2) => $q2->where('program_id', $programId))
            // kalau tidak ada program tapi ada fakultas → filter via relasi
            ->when(!$programId && $facultyId, function ($q2) use ($facultyId) {
                $q2->whereHas('program', fn($w) => $w->where('faculty_id', $facultyId));
            })
            // search
            ->when($q !== '', function ($qr) use ($q) {
                $qr->where(function ($w) use ($q) {
                    $w->where('code', 'like', "%{$q}%")
                      ->orWhere('name', 'like', "%{$q}%")
                      ->orWhere('catalog_nbr', 'like', "%{$q}%");
                });
            })
            ->orderBy($sort, $dir)
            ->paginate($perPage)->withQueryString();

        // 🔽 Dropdown fakultas di filter
        if ($isFacultyAdmin) {
            $faculties = \App\Models\Faculty::where('id', $user->faculty_id)
                ->orderBy('name')
                ->get(['id','name']);
        } else {
            $faculties = \App\Models\Faculty::orderBy('name')->get(['id','name']);
        }

        // 🔽 Dropdown program juga di-scope ke fakultas yg aktif
        $programsQuery = Program::query();

        if ($facultyId) {
            $programsQuery->where('faculty_id', $facultyId);
        }

        if ($isFacultyAdmin) {
            $programsQuery->where('faculty_id', $user->faculty_id);
        }

        $programs = $programsQuery
            ->orderBy('name')
            ->get(['id','name','faculty_id']);

        return view('master.courses.index', compact(
            'items','faculties','programs','facultyId','programId','q','sort','dir','perPage'
        ));
    }

    public function create()
    {
        $programs = Program::with('faculty')->orderBy('name')->get();

        return view('master.courses.form', [
            'course'   => new Course(),
            'programs' => $programs,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'program_id'  => 'required|exists:programs,id',
            'course_id'   => 'required|string|max:50',     // misal CRSE_ID
            'catalog_nbr' => 'nullable|string|max:50',     // misal kode MK (INF123)
            'name'        => 'required|string|max:255',
        ]);

        $exists = Course::where('program_id', $data['program_id'])
            ->where('course_id', $data['course_id'])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['course_id' => 'Course ID already exists in this program.'])
                ->withInput();
        }

        Course::create($data);

        return redirect()->route('courses.index')
            ->with('success', 'Course created successfully.');
    }

    public function edit(Course $course)
    {
        $programs = Program::with('faculty')->orderBy('name')->get();

        return view('master.courses.form', [
            'course'   => $course,
            'programs' => $programs,
        ]);
    }

    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'program_id'  => 'required|exists:programs,id',
            'course_id'   => 'required|string|max:50',
            'catalog_nbr' => 'nullable|string|max:50',
            'name'        => 'required|string|max:255',
        ]);

        $exists = Course::where('program_id', $data['program_id'])
            ->where('course_id', $data['course_id'])
            ->where('id', '!=', $course->id)
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['course_id' => 'Course ID already exists in this program.'])
                ->withInput();
        }

        $course->update($data);

        return redirect()->route('courses.index')
            ->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return back()->with('success', 'Course deleted.');
    }

    /* ============================================================
     * ASSIGN DOSEN KE COURSE (course_lecturers pivot)
     * ============================================================ */

    public function lecturers(Request $request, Course $course)
    {
        $actor = $request->user();

        // hanya Admin/Super Admin (sesuai middleware routes kamu)
        abort_unless($actor && $actor->hasAnyRole(['Super Admin','Admin']), 403);

        // Admin fakultas hanya boleh manage course di fakultasnya
        if ($actor->hasRole('Admin') && ! $actor->hasRole('Super Admin')) {
            $course->loadMissing('program');
            abort_unless(optional($course->program)->faculty_id === $actor->faculty_id, 403);
        }

        // pool dosen
        $lecturers = User::query()
            ->whereHas('roles', fn($q) => $q->where('name', 'Dosen'))
            ->orderBy('name')
            ->get(['id','name','email']);

        // dosen yang sudah assigned
        $assigned = $course->lecturers()
            ->orderBy('name')
            ->get(['users.id','users.name','users.email']);

        return view('master.courses.lecturers', compact('course','lecturers','assigned'));
    }

    public function storeLecturer(Request $request, Course $course)
    {
        $actor = $request->user();
        abort_unless($actor && $actor->hasAnyRole(['Super Admin','Admin']), 403);

        if ($actor->hasRole('Admin') && ! $actor->hasRole('Super Admin')) {
            $course->loadMissing('program');
            abort_unless(optional($course->program)->faculty_id === $actor->faculty_id, 403);
        }

        $data = $request->validate([
            'user_id'        => ['required','integer','exists:users,id'],
            'can_edit'       => ['nullable'],
            'is_responsible' => ['nullable'],
        ]);

        $lecturer = User::findOrFail($data['user_id']);

        // pastikan user tersebut dosen
        abort_unless($lecturer->hasRole('Dosen'), 422);

        $canEdit = $request->boolean('can_edit');
        $isPic   = $request->boolean('is_responsible');

        DB::transaction(function () use ($course, $lecturer, $canEdit, $isPic) {

            // attach/update pivot
            $course->lecturers()->syncWithoutDetaching([
                $lecturer->id => [
                    'can_edit'       => $canEdit ? 1 : 0,
                    'is_responsible' => $isPic ? 1 : 0,
                ],
            ]);

            // enforce PIC hanya 1 per course
            if ($isPic) {
                $others = $course->lecturers()
                    ->wherePivot('is_responsible', 1)
                    ->where('users.id','!=',$lecturer->id)
                    ->pluck('users.id');

                foreach ($others as $id) {
                    $course->lecturers()->updateExistingPivot($id, ['is_responsible' => 0]);
                }
            }
        });

        return back()->with('success', 'Assignment dosen berhasil disimpan.');
    }

    public function removeLecturer(Request $request, Course $course, User $user)
    {
        $actor = $request->user();
        abort_unless($actor && $actor->hasAnyRole(['Super Admin','Admin']), 403);

        if ($actor->hasRole('Admin') && ! $actor->hasRole('Super Admin')) {
            $course->loadMissing('program');
            abort_unless(optional($course->program)->faculty_id === $actor->faculty_id, 403);
        }

        $course->lecturers()->detach($user->id);

        return back()->with('success', 'Dosen berhasil dilepas dari course.');
    }
}
