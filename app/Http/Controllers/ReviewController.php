<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rps;
use App\Models\Review;
use App\Models\ReviewItem;

class ReviewController extends Controller
{
    /* ============================================================
     * INDEX — daftar RPS yang perlu direview CTL
     * ============================================================ */
    public function index(Request $request)
    {
        $user = $request->user();

        // Hanya CTL & Super Admin yang boleh ke halaman review
        abort_unless(
            $user->hasRole('CTL') || $user->hasRole('Super Admin'),
            403,
            'Hanya CTL yang dapat mengakses halaman ini.'
        );

        $q = Rps::query()
            ->with(['course.program.faculty'])
            ->whereIn('status', ['submitted', 'reviewed']) // RPS yang sudah/sedang diajukan
            ->orderByDesc('submitted_at')
            ->orderByDesc('created_at');

        // optional search
        $search = $request->string('q')->toString();
        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('academic_year', 'like', "%{$search}%")
                    ->orWhereHas('course', function ($c) use ($search) {
                        $c->where('name', 'like', "%{$search}%")
                          ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        $rpsList = $q->paginate(15)->withQueryString();

        return view('reviews.index', [
            'rpsList' => $rpsList,
            'search'  => $search,
        ]);
    }

    /* ============================================================
     * EDIT — tampilkan form rubric review
     * ============================================================ */
    public function edit(Request $request, Rps $rps)
    {
        $user = $request->user();

        abort_unless(
            $user->hasRole('CTL') || $user->hasRole('Super Admin'),
            403,
            'Hanya CTL yang dapat melakukan review.'
        );

        // RPS yang bisa direview: status submitted / reviewed
        abort_unless(
            in_array($rps->status, ['submitted', 'reviewed']),
            403,
            'RPS bukan dalam status yang dapat direview.'
        );

        $rubric = config('rps_rubric'); // sudah kamu buat
        $indicators = $rubric['indicators'] ?? [];

        // Ambil review CTL untuk RPS ini (kalau sudah ada)
        $review = Review::firstOrNew([
            'rps_id'      => $rps->id,
            'reviewer_id' => $user->id,
        ]);

        if (!$review->exists) {
            $review->status         = 'revisi'; // default
            $review->rubric_version = $rubric['version'] ?? null;
        } else {
            $review->load('items');
        }

        $itemsByKey = $review->exists
            ? $review->items->keyBy('criterion_key')
            : collect();

        return view('reviews.edit', [
            'rps'        => $rps,
            'rubric'     => $rubric,
            'review'     => $review,
            'itemsByKey' => $itemsByKey,
            'indicators' => $indicators,
        ]);
    }

    /* ============================================================
     * STORE — simpan hasil rubric review
     * ============================================================ */
    public function store(Request $request, Rps $rps)
    {
        $user = $request->user();

        abort_unless(
            $user->hasRole('CTL') || $user->hasRole('Super Admin'),
            403,
            'Hanya CTL yang dapat melakukan review.'
        );

        $rubric     = config('rps_rubric');
        $indicators = $rubric['indicators'] ?? [];

        // Kumpulkan semua key kriteria dari config (1.1, 1.2, dst.)
        $criterionKeys = [];
        foreach ($indicators as $ind) {
            foreach ($ind['criteria'] ?? [] as $crit) {
                $criterionKeys[] = $crit['key'];
            }
        }

        // Validasi basic
        $data = $request->validate([
            'status'        => ['required', 'in:revisi,forwarded'],
            'comments'      => ['nullable', 'string'],
            'scores'        => ['required', 'array'],
            'scores.*'      => ['nullable', 'integer', 'min:1', 'max:5'],
            'notes'         => ['nullable', 'array'],
            'notes.*'       => ['nullable', 'string'],
        ]);

        $scores = $data['scores'] ?? [];
        $notes  = $data['notes'] ?? [];

        // Buat / update record Review
        $review = Review::firstOrNew([
            'rps_id'      => $rps->id,
            'reviewer_id' => $user->id,
        ]);

        $review->status         = $data['status']; // revisi / forwarded
        $review->comments       = $data['comments'] ?? null;
        $review->rubric_version = $rubric['version'] ?? null;
        $review->save();

        // Hapus item lama, lalu buat ulang (supaya simple)
        $review->items()->delete();

        $totalScore = 0;

        // Sementara: semua criteria = bobot 1 (nanti bisa diubah kalau kamu mau ikuti Excel persis)
        $defaultWeight = 1;

        foreach ($indicators as $ind) {
            foreach ($ind['criteria'] ?? [] as $crit) {
                $key = $crit['key'];

                // kalau di form tidak dipilih (null/0), lewati
                $levelIndex = (int)($scores[$key] ?? 0);
                if ($levelIndex <= 0) {
                    continue;
                }

                $scale      = $crit['scale'] ?? [];
                $levelLabel = $scale[$levelIndex] ?? '';
                // misal skor mentah = index (1–5)
                $levelScore    = $levelIndex;
                $weightedScore = $defaultWeight * $levelScore;
                $totalScore   += $weightedScore;

                ReviewItem::create([
                    'review_id'      => $review->id,
                    'criterion_key'  => $key,
                    'criterion_label'=> $crit['label'],
                    'weight'         => $defaultWeight,
                    'level_index'    => $levelIndex,
                    'level_label'    => $levelLabel,
                    'level_score'    => $levelScore,
                    'weighted_score' => $weightedScore,
                    'notes'          => $notes[$key] ?? null,
                ]);
            }
        }

        $review->total_score = $totalScore;
        $review->save();

        /*
         * Update RPS:
         * - Kaprodi boleh approve walau belum direview,
         *   jadi CTL tidak jadi "stopper".
         * - Tapi kita tetap tandai bahwa RPS ini SUDAH pernah direview CTL.
         */
        $rps->is_reviewed_by_ctl = 1;   // penanda sudah pernah dicek CTL

if ($data['status'] === 'revisi') {
    // CTL minta revisi → RPS balik ke dosen
    $rps->status = 'need_revision';
} elseif ($data['status'] === 'forwarded') {
    // CTL setuju diteruskan ke Kaprodi
    // Kaprodi tetap bisa approve walau CTL belum review,
    // tapi kalau sudah diforward CTL, kita tandai sebagai 'reviewed'
    $rps->status = 'reviewed';
}

$rps->save();

        return redirect()
            ->route('reviews.index')
            ->with('success', 'Rubrik review berhasil disimpan.');
    }
}
