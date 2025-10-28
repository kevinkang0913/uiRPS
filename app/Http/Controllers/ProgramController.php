<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Faculty;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
{
    $q        = trim($request->string('q')->toString());
    $faculty  = $request->integer('faculty_id');
    $sort     = in_array($request->string('sort'), ['name','code']) ? $request->string('sort') : 'name';
    $dir      = $request->string('dir') === 'desc' ? 'desc' : 'asc';
    $perPage  = max(5, min((int)$request->integer('per_page') ?: 15, 100));

    $items = \App\Models\Program::with('faculty')
        ->when($faculty, fn($q2)=>$q2->where('faculty_id',$faculty))
        ->when($q !== '', function($qr) use ($q){
            $qr->where(function($w) use ($q){
                $w->where('code','like',"%{$q}%")->orWhere('name','like',"%{$q}%");
            });
        })
        ->orderBy($sort, $dir)
        ->paginate($perPage)->withQueryString();

    $faculties = \App\Models\Faculty::orderBy('name')->get(['id','name','code']);
    return view('master.programs.index', compact('items','faculties','q','faculty','sort','dir','perPage'));
}


    public function create()
    {
        $faculties = Faculty::orderBy('name')->get();
        return view('master.programs.form', [
            'program'   => new Program(),
            'faculties' => $faculties,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'faculty_id' => 'required|exists:faculties,id',
            'code'       => 'required|string|max:20',
            'name'       => 'required|string|max:255',
        ]);

        // Enforce unique compound (faculty_id + code)
        $exists = Program::where('faculty_id', $data['faculty_id'])
            ->where('code', $data['code'])
            ->exists();
        if ($exists) {
            return back()
                ->withErrors(['code' => 'Program code already exists in this faculty.'])
                ->withInput();
        }

        Program::create($data);

        return redirect()
            ->route('programs.index')
            ->with('success', 'Program created successfully.');
    }

    public function edit(Program $program)
    {
        $faculties = Faculty::orderBy('name')->get();
        return view('master.programs.form', [
            'program'   => $program,
            'faculties' => $faculties,
        ]);
    }

    public function update(Request $request, Program $program)
    {
        $data = $request->validate([
            'faculty_id' => 'required|exists:faculties,id',
            'code'       => 'required|string|max:20',
            'name'       => 'required|string|max:255',
        ]);

        // Enforce unique compound (faculty_id + code), ignore current row
        $exists = Program::where('faculty_id', $data['faculty_id'])
            ->where('code', $data['code'])
            ->where('id', '!=', $program->id)
            ->exists();
        if ($exists) {
            return back()
                ->withErrors(['code' => 'Program code already exists in this faculty.'])
                ->withInput();
        }

        $program->update($data);

        return redirect()
            ->route('programs.index')
            ->with('success', 'Program updated successfully.');
    }

    public function destroy(Program $program)
    {
        $program->delete();

        return back()->with('success', 'Program deleted.');
    }
}
