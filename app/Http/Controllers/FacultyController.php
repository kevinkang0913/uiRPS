<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    /**
     * List fakultas.
     * - Super Admin: semua fakultas
     * - Admin fakultas: hanya fakultas miliknya
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // query params
        $q       = trim($request->string('q')->toString());
        $sort    = $request->string('sort')->toString();
        $dir     = strtolower($request->string('dir')->toString()) === 'desc' ? 'desc' : 'asc';
        $perPage = (int) $request->integer('per_page') ?: 15;
        $perPage = max(5, min($perPage, 100));

        // whitelist kolom yang boleh disort
        $sortable = ['code','name','created_at'];
        if (!in_array($sort, $sortable)) $sort = 'name';

        $query = Faculty::query()
            ->when($q !== '', function($qr) use ($q) {
                $qr->where(function($w) use ($q) {
                    $w->where('code','like',"%{$q}%")
                      ->orWhere('name','like',"%{$q}%");
                });
            });

        // 🔒 BATASI ADMIN HANYA KE FAKULTASNYA
        if ($user->isFacultyAdmin() && $user->faculty_id) {
            $query->where('id', $user->faculty_id);
        }

        $faculties = $query
            ->orderBy($sort, $dir)
            ->paginate($perPage)
            ->withQueryString();

        return view('master.faculties.index', compact('faculties','q','sort','dir','perPage','sortable'));
    }

    /**
     * Form create fakultas.
     * - HANYA Super Admin yang boleh.
     */
    public function create()
    {
        if (! auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Hanya Super Admin yang dapat menambah fakultas.');
        }

        return view('master.faculties.form', ['faculty' => new Faculty()]);
    }

    /**
     * Simpan fakultas baru.
     * - HANYA Super Admin yang boleh.
     */
    public function store(Request $request)
    {
        if (! auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Hanya Super Admin yang dapat menambah fakultas.');
        }

        $data = $request->validate([
            'code' => 'required|string|max:20|unique:faculties,code',
            'name' => 'required|string|max:255',
        ]);

        Faculty::create($data);

        return redirect()->route('faculties.index')
            ->with('success', 'Faculty created successfully.');
    }

    /**
     * Form edit fakultas.
     * - Super Admin: boleh edit semua
     * - Admin: hanya boleh edit fakultas miliknya
     */
    public function edit(Request $request, Faculty $faculty)
    {
        $user = $request->user();

        if ($user->hasRole('Super Admin')) {
            // ok
        } elseif ($user->isFacultyAdmin() && $user->faculty_id === $faculty->id) {
            // admin fakultas boleh edit fakultasnya sendiri
        } else {
            abort(403, 'Anda tidak boleh mengedit fakultas ini.');
        }

        return view('master.faculties.form', compact('faculty'));
    }

    /**
     * Update fakultas.
     * - Super Admin: boleh update semua
     * - Admin: hanya boleh update fakultas miliknya
     */
    public function update(Request $request, Faculty $faculty)
    {
        $user = $request->user();

        if ($user->hasRole('Super Admin')) {
            // ok
        } elseif ($user->isFacultyAdmin() && $user->faculty_id === $faculty->id) {
            // ok
        } else {
            abort(403, 'Anda tidak boleh mengedit fakultas ini.');
        }

        $data = $request->validate([
            'code' => 'required|string|max:20|unique:faculties,code,' . $faculty->id,
            'name' => 'required|string|max:255',
        ]);

        $faculty->update($data);

        return redirect()->route('faculties.index')
            ->with('success', 'Faculty updated successfully.');
    }

    /**
     * Hapus fakultas.
     * - HANYA Super Admin.
     */
    public function destroy(Request $request, Faculty $faculty)
    {
        $user = $request->user();

        if (! $user->hasRole('Super Admin')) {
            abort(403, 'Hanya Super Admin yang dapat menghapus fakultas.');
        }

        $faculty->delete();

        return redirect()->route('faculties.index')
            ->with('success', 'Faculty deleted.');
    }
}
