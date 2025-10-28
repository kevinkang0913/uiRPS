<?php

namespace App\Http\Controllers;

use App\Models\ClassSection;
use App\Models\Course;
use Illuminate\Http\Request;

class ClassSectionController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
{
    $q         = trim($request->input('q', ''));
    $facultyId = $request->integer('faculty_id');
    $programId = $request->integer('program_id');
    $courseId  = $request->integer('course_id');
    $semester  = $request->integer('semester');
    $year      = $request->integer('year');
    $sort      = in_array($request->input('sort'), ['year','semester','class_number'])
                    ? $request->input('sort') : 'year';
    $dir       = strtolower($request->input('dir')) === 'asc' ? 'asc' : 'desc';
    $perPage   = max(5, min((int)$request->input('per_page', 15), 100));

    $sections = \App\Models\ClassSection::with('course.program.faculty')
        ->when($courseId, fn($q2) => $q2->where('course_id', $courseId))
        ->when(!$courseId && $programId, function($q2) use ($programId) {
            $q2->whereHas('course', fn($w) => $w->where('program_id', $programId));
        })
        ->when(!$courseId && !$programId && $facultyId, function($q2) use ($facultyId) {
            $q2->whereHas('course.program', fn($w) => $w->where('faculty_id', $facultyId));
        })
        ->when($semester, fn($q2) => $q2->where('semester', $semester))
        ->when($year, fn($q2) => $q2->where('year', $year))
        ->when($q !== '', function($qr) use ($q) {
            $qr->where(function($w) use ($q) {
                $w->where('class_number', 'like', "%{$q}%")
                  ->orWhere('year', 'like', "%{$q}%");
            });
        })
        ->orderBy($sort, $dir)
        ->paginate($perPage)
        ->withQueryString();

    $faculties = \App\Models\Faculty::orderBy('name')->get(['id','name']);
    $programs  = \App\Models\Program::when($facultyId, fn($q) => $q->where('faculty_id',$facultyId))
                    ->orderBy('name')->get(['id','name','faculty_id']);
    $courses   = \App\Models\Course::when($programId, fn($q) => $q->where('program_id',$programId))
                    ->orderBy('name')->get(['id','name','program_id']);

    return view('master.class_sections.index', compact(
        'sections','faculties','programs','courses',
        'facultyId','programId','courseId','semester','year',
        'q','sort','dir','perPage'
    ));
}


    public function create()
    {
        $courses = Course::with('program.faculty')->orderBy('name')->get();
        return view('master.class_sections.form', [
            'section' => new ClassSection(),
            'courses' => $courses,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'course_id'    => 'required|exists:courses,id',
            'class_number' => 'required|string|max:10',
            'semester'     => 'required|integer|min:1|max:14',
            'year'         => 'required|integer|min:2000|max:2100',
        ]);

        // Prevent duplicates (course + class_number + semester + year)
        $exists = ClassSection::where('course_id', $data['course_id'])
            ->where('class_number', $data['class_number'])
            ->where('semester', $data['semester'])
            ->where('year', $data['year'])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['class_number' => 'This class already exists for the selected course, semester, and year.'])
                ->withInput();
        }

        ClassSection::create($data);
        return redirect()->route('class-sections.index')->with('success', 'Class section created successfully.');
    }

    public function edit(ClassSection $class_section)
    {
        $courses = Course::with('program.faculty')->orderBy('name')->get();
        return view('master.class_sections.form', [
            'section' => $class_section,
            'courses' => $courses,
        ]);
    }

    public function update(Request $request, ClassSection $class_section)
    {
        $data = $request->validate([
            'course_id'    => 'required|exists:courses,id',
            'class_number' => 'required|string|max:10',
            'semester'     => 'required|integer|min:1|max:14',
            'year'         => 'required|integer|min:2000|max:2100',
        ]);

        $exists = ClassSection::where('course_id', $data['course_id'])
            ->where('class_number', $data['class_number'])
            ->where('semester', $data['semester'])
            ->where('year', $data['year'])
            ->where('id', '!=', $class_section->id)
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['class_number' => 'This class already exists for the selected course, semester, and year.'])
                ->withInput();
        }

        $class_section->update($data);
        return redirect()->route('class-sections.index')->with('success', 'Class section updated successfully.');
    }

    public function destroy(ClassSection $class_section)
    {
        $class_section->delete();
        return back()->with('success', 'Class section deleted.');
    }
}
