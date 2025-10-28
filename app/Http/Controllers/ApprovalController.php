<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rps;
use App\Models\Approval;

class ApprovalController extends Controller
{
    public function index()
    {
        $rpsList = Rps::where('status','forwarded')->with('lecturer','classSection.course')->get();
        return view('approvals.index', compact('rpsList'));
    }

    public function edit(Rps $rps)
    {
        abort_unless($rps->status === 'forwarded', 403, 'RPS bukan dalam status forwarded');
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
