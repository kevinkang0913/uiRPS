<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rps;       // ✅ ini wajib
use App\Models\Course;    // kalau dipakai
use App\Models\Semester;  // kalau dipakai
use App\Models\User;      // kalau dipakai relasi

class RpsController extends Controller
{
    public function index()
    {
        $rps = Rps::with(['user','course','semester'])->get();
        return view('rps.index', compact('rps'));
    }

    public function create()
    {
        $courses = Course::all();
        $semesters = Semester::all();
        return view('rps.create', compact('courses','semesters'));
    }

    public function store(Request $request)
    {
        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('rps_files', 'public');
        }

        Rps::create([
            'user_id' => auth()->id(),   // user login
            'course_id' => $request->course_id,
            'semester_id' => $request->semester_id,
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $filePath,
            'status' => 'submitted'
        ]);

        return redirect()->route('rps.index')->with('success','RPS submitted successfully');
    }
}

