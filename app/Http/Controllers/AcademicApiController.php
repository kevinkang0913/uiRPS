<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use App\Models\Program;
use App\Models\Course;
use App\Models\ClassSection;

class AcademicApiController extends Controller
{
    public function faculties()
    {
        return response()->json(
            Faculty::select('id','name')->orderBy('name')->get()
        );
    }

    public function programsByFaculty(Faculty $faculty)
    {
        return response()->json(
            Program::where('faculty_id', $faculty->id)
                ->select('id','name')
                ->orderBy('name')
                ->get()
        );
    }

    public function coursesByProgram(Program $program)
    {
        return response()->json(
            Course::where('program_id', $program->id)
                ->select('id','code','name') // ← name, bukan title
                ->orderBy('code')
                ->get()
        );
    }

    public function sectionsByCourse(Course $course)
    {
        return response()->json(
            ClassSection::where('course_id', $course->id)
                ->select('id','class_section','semester') // ← class_section
                ->orderBy('class_section')
                ->get()
        );
    }
}
