<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rps;
use App\Models\Approval;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // ambil semua RPS yang sudah "forwarded" ke Kaprodi
        $q = Rps::where('status', 'forwarded')
            ->with(['lecturer', 'classSection.course.program.faculty']);

        // 🔒 BATAS UNTUK KAPRODI
        if ($user && $user->hasRole('Kaprodi')) {

            // 1) Jika Kaprodi di-assign ke 1 program studi tertentu
            if ($user->program_id) {
                $q->where('program_id', $user->program_id);
            }
            // 2) Jika hanya di-assign ke fakultas (program_id null tapi faculty_id ada)
            elseif ($user->faculty_id) {
                $q->whereHas('course.program', function ($sub) use ($user) {
                    $sub->where('faculty_id', $user->faculty_id);
                });
            }

            // Jika Kaprodi tidak punya faculty_id / program_id sama sekali,
            // secara praktis dia tidak akan melihat apapun (boleh kamu atur nanti).
        }

        // Super Admin (atau role lain) tidak dibatasi di sini

        $rpsList = $q->get();

        return view('approvals.index', compact('rpsList'));
    }

    public function edit(Rps $rps)
    {
        abort_unless($rps->status === 'forwarded', 403, 'RPS bukan dalam status forwarded');

        $user = auth()->user();

        // 🔒 CEK AKSES KAPRODI SAAT BUKA SATU RPS
        if ($user && $user->hasRole('Kaprodi')) {

            // 1) Cek berdasarkan program_id langsung di Rps
            if ($user->program_id && $rps->program_id !== $user->program_id) {
                abort(403, 'Anda tidak berhak mengakses RPS dari program studi lain.');
            }

            // 2) Jika program_id kosong tapi faculty_id ada → fallback ke fakultas
            if (!$user->program_id && $user->faculty_id) {
                // coba ambil faculty_id dari relasi program RPS
                $rpsFacultyId = optional($rps->program)->faculty_id
                    ?? optional(optional($rps->classSection)->course->program)->faculty_id;

                if ($rpsFacultyId !== $user->faculty_id) {
                    abort(403, 'Anda tidak berhak mengakses RPS dari fakultas lain.');
                }
            }
        }

        return view('approvals.edit', compact('rps'));
    }

    public function store(Request $request, Rps $rps)
    {
        $validated = $request->validate([
            'status' => ['required','in:approved,rejected'],
            'notes'  => ['nullable','string'],
        ]);

        Approval::create([
            'rps_id'      => $rps->id,
            'approver_id' => auth()->id(),
            'status'      => $validated['status'],
            'notes'       => $validated['notes'] ?? null,
        ]);

        $rps->update(['status' => $validated['status']]);

        \App\Models\ActivityLog::create([
            'rps_id' => $rps->id,
            'user_id'=> auth()->id(),
            'action' => 'approval',
            'notes'  => "RPS {$validated['status']}",
        ]);

        return redirect()->route('approvals.index')->with('success','Keputusan disimpan.');
    }
}
