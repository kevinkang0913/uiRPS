<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rps;
use App\Models\Review;
use App\Models\ReviewItem;

class ReviewController extends Controller
{
    public function index()
    {
        $rpsList = Rps::where('status','submitted')->with('lecturer','classSection.course')->get();
        return view('reviews.index', compact('rpsList'));
    }

    public function edit(Rps $rps)
    {
        abort_unless($rps->status === 'submitted', 403, 'RPS bukan dalam status submitted');
        $rubric = config('rps_rubric');
        return view('reviews.edit', compact('rps','rubric'));
    }

    public function store(Request $request, Rps $rps)
    {
        $rubric = config('rps_rubric');
        $criteria = $rubric['criteria'] ?? [];

        $validated = $request->validate([
            'selections'              => ['required','array'],              // selections[c_key] = level_index
            'selections.*'            => ['required','integer','min:0'],
            'notes'                   => ['nullable','array'],
            'notes.*'                 => ['nullable','string'],
            'overall_comment'         => ['nullable','string'],
            'decision'                => ['required','in:revisi,forwarded'],
        ]);

        // hitung skor total
        $total = 0;
        foreach ($criteria as $key => $c) {
            $levelIdx = (int)($validated['selections'][$key] ?? 0);
            $level = $c['levels'][$levelIdx] ?? $c['levels'][0];
            $weighted = (int) round(($level['score'] * $c['weight']) / 100);
            $total += $weighted;
        }

        // simpan review + items
        $review = Review::create([
            'rps_id'       => $rps->id,
            'reviewer_id'  => auth()->id(),
            'comments'     => $validated['overall_comment'] ?? null,
            'status'       => $validated['decision'], // 'revisi' atau 'forwarded'
            'total_score'  => $total,
            'rubric_version' => $rubric['version'] ?? null,
        ]);

        foreach ($criteria as $key => $c) {
            $levelIdx = (int)($validated['selections'][$key] ?? 0);
            $level = $c['levels'][$levelIdx] ?? $c['levels'][0];
            $weighted = (int) round(($level['score'] * $c['weight']) / 100);

            ReviewItem::create([
                'review_id'       => $review->id,
                'criterion_key'   => $key,
                'criterion_label' => $c['label'],
                'weight'          => $c['weight'],
                'level_index'     => $levelIdx,
                'level_label'     => $level['label'],
                'level_score'     => $level['score'],
                'weighted_score'  => $weighted,
                'notes'           => $validated['notes'][$key] ?? null,
            ]);
        }

        // update status RPS sesuai keputusan CTL
        $rps->update(['status' => $validated['decision']]);

        // log (opsional)
        \App\Models\ActivityLog::create([
            'rps_id' => $rps->id,
            'user_id'=> auth()->id(),
            'action' => 'review',
            'notes'  => "Decision: {$validated['decision']}, Score: {$total}",
        ]);

        return redirect()->route('reviews.index')->with('success', 'Review tersimpan.');
    }
}
