<?php

namespace App\Http\Controllers;

use App\Models\Rps;
use App\Models\Approval;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    /**
     * Daftar RPS untuk approval Kaprodi.
     * - draft              : terlihat (info saja, belum bisa di-approve)
     * - submitted          : bisa di-approve
     * - reviewed           : bisa di-approve (sudah via CTL)
     * - revision_submitted : bisa di-approve
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Pastikan hanya Kaprodi / Super Admin yang bisa akses
        if (! $user->hasRole('Kaprodi') && ! $user->hasRole('Super Admin')) {
            abort(403, 'Hanya Kaprodi atau Super Admin yang dapat mengakses halaman approval.');
        }

        $status = $request->string('status')->toString();

        $q = Rps::query()
            ->with([
                'course:id,name,code,program_id',
                'course.program:id,name,faculty_id',
                'latestApproval',
            ])
            // semua status yang relevan untuk tab approval
            ->whereIn('status', ['draft', 'submitted', 'reviewed', 'revision_submitted']);

        // Scope ke prodi Kaprodi
        if ($user->hasRole('Kaprodi') && $user->program_id) {
            $q->whereHas('course', function ($c) use ($user) {
                $c->where('program_id', $user->program_id);
            });
        }

        // Filter tambahan berdasarkan status (opsional)
        if ($status !== '') {
            $q->where('status', $status);
        }

        $rpsList = $q->latest()->paginate(15)->withQueryString();

        $filters = [
            'status' => $status,
        ];

        return view('approvals.index', compact('rpsList', 'filters'));
    }

    /**
     * Form approve / not approve satu RPS.
     */
    public function edit(Rps $rps)
    {
        $user = Auth::user();

        if (! $user->hasRole('Kaprodi') && ! $user->hasRole('Super Admin')) {
            abort(403, 'Hanya Kaprodi atau Super Admin yang dapat melakukan approval.');
        }

        // Kaprodi hanya boleh akses RPS di prodi-nya sendiri
        if ($user->hasRole('Kaprodi') && $user->program_id) {
            if (optional($rps->course)->program_id !== $user->program_id) {
                abort(403, 'Anda tidak berwenang meng-approve RPS di luar prodi Anda.');
            }
        }

        // HANYA status ini yang boleh di-approve
        if (! in_array($rps->status, ['submitted', 'reviewed', 'revision_submitted'])) {
            abort(403, 'RPS tidak dalam status yang dapat di-approve.');
        }

        $latestReview = Review::with('items')
            ->where('rps_id', $rps->id)
            ->latest()
            ->first();

        $latestApproval = $rps->latestApproval;

        return view('approvals.edit', compact('rps', 'latestReview', 'latestApproval'));
    }

    /**
     * Simpan keputusan Kaprodi.
     * - status form: approved / not_approved (di kolom rps.status)
     * - di table approvals.status: 'approved' / 'rejected'
     */
    public function store(Request $request, Rps $rps)
    {
        $user = Auth::user();

        if (! $user->hasRole('Kaprodi') && ! $user->hasRole('Super Admin')) {
            abort(403, 'Hanya Kaprodi atau Super Admin yang dapat melakukan approval.');
        }

        // Kaprodi hanya boleh akses RPS di prodi-nya sendiri
        if ($user->hasRole('Kaprodi') && $user->program_id) {
            if (optional($rps->course)->program_id !== $user->program_id) {
                abort(403, 'Anda tidak berwenang meng-approve RPS di luar prodi Anda.');
            }
        }

        // Sama seperti di edit(): hanya status ini yang boleh di-approve
        if (! in_array($rps->status, ['submitted', 'reviewed', 'revision_submitted'])) {
            abort(403, 'RPS tidak dalam status yang dapat di-approve.');
        }

        $data = $request->validate([
            'status' => ['required', 'in:approved,not_approved'],
            'notes'  => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($rps, $user, $data) {

            // Map ke table approvals: approved / rejected
            $approvalStatus = $data['status'] === 'approved' ? 'approved' : 'rejected';

            Approval::create([
                'rps_id'      => $rps->id,
                'approver_id' => $user->id,
                'status'      => $approvalStatus,
                'notes'       => $data['notes'] ?? null,
            ]);

            // Update status di tabel RPS
            $rps->status = $data['status']; // 'approved' atau 'not_approved'
            $rps->save();
        });

        return redirect()
            ->route('approvals.index')
            ->with('success', 'Keputusan approval berhasil disimpan.');
    }
}
