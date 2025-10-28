<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
{
    // query params
    $q       = trim($request->string('q')->toString());
    $sort    = $request->string('sort')->toString();
    $dir     = strtolower($request->string('dir')->toString()) === 'desc' ? 'desc' : 'asc';
    $perPage = (int) $request->integer('per_page') ?: 15;
    $perPage = max(5, min($perPage, 100));

    // whitelist kolom yang boleh disort
    $sortable = ['code','name','created_at'];
    if (!in_array($sort, $sortable)) $sort = 'name';

    $faculties = \App\Models\Faculty::query()
        ->when($q !== '', function($qr) use ($q) {
            $qr->where(function($w) use ($q) {
                $w->where('code','like',"%{$q}%")
                  ->orWhere('name','like',"%{$q}%");
            });
        })
        ->orderBy($sort, $dir)
        ->paginate($perPage)
        ->withQueryString();

    return view('master.faculties.index', compact('faculties','q','sort','dir','perPage','sortable'));
}


    public function create()
    {
        return view('master.faculties.form', ['faculty' => new Faculty()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:20|unique:faculties,code',
            'name' => 'required|string|max:255',
        ]);
        Faculty::create($data);
        return redirect()->route('faculties.index')->with('success', 'Faculty created successfully.');
    }

    public function edit(Faculty $faculty)
    {
        return view('master.faculties.form', compact('faculty'));
    }

    public function update(Request $request, Faculty $faculty)
    {
        $data = $request->validate([
            'code' => 'required|string|max:20|unique:faculties,code,' . $faculty->id,
            'name' => 'required|string|max:255',
        ]);
        $faculty->update($data);
        return redirect()->route('faculties.index')->with('success', 'Faculty updated successfully.');
    }

    public function destroy(Faculty $faculty)
    {
        $faculty->delete();
        return redirect()->route('faculties.index')->with('success', 'Faculty deleted.');
    }
}
