<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Program;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
{
    $q         = trim($request->string('q')->toString());
    $facultyId = $request->integer('faculty_id');
    $programId = $request->integer('program_id');
    $sort = in_array($request->input('sort'), ['semester','class_number','created_at'])
            ? $request->input('sort') : 'created_at';
    $dir       = $request->string('dir') === 'desc' ? 'desc' : 'asc';
    $perPage   = max(5, min((int)$request->integer('per_page') ?: 15, 100));

    $items = \App\Models\Course::with('program.faculty')
        ->when($programId, fn($q2)=>$q2->where('program_id',$programId))
        ->when(!$programId && $facultyId, function($q2) use ($facultyId) {
            $q2->whereHas('program', fn($w)=>$w->where('faculty_id',$facultyId));
        })
        ->when($q !== '', function($qr) use ($q){
            $qr->where(function($w) use ($q){
                $w->where('code','like',"%{$q}%")
                  ->orWhere('name','like',"%{$q}%")
                  ->orWhere('catalog_nbr','like',"%{$q}%");
            });
        })
        ->orderBy($sort,$dir)
        ->paginate($perPage)->withQueryString();

    $faculties = \App\Models\Faculty::orderBy('name')->get(['id','name']);
    $programs  = \App\Models\Program::when($facultyId, fn($q)=>$q->where('faculty_id',$facultyId))
                 ->orderBy('name')->get(['id','name','faculty_id']);

    return view('master.courses.index', compact('items','faculties','programs','facultyId','programId','q','sort','dir','perPage'));
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
}
