<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{
    Rps, RpsPlo, RpsOutcome, RpsSubClo,
    RpsAssessmentCategory, RpsAssessmentMapping, RpsAssessment,
    RpsReference, RpsWeeklyPlan, RpsCplCpmkWeight,
};

class RpsController extends Controller
{
    /* ============================================================
     * INDEX — daftar RPS
     * ============================================================ */
    public function startNew(Request $request)
{
    // Buang RPS yang sebelumnya ada di wizard
    $request->session()->forget('rps_id');

    // Kalau mau, bisa juga reset flash lain dsb.
    // $request->session()->forget(['something']);

    return redirect()->route('rps.create.step', 1);
}
    public function index(Request $request)
{
    $user = $request->user();

    $q = Rps::query()
        ->with([
            // supaya bisa dipakai untuk filter & tampilan
            'course.program.faculty',
        ])
        ->latest();

    // 🔒 Batasi Admin biasa ke fakultas-nya saja
    if (
        $user
        && $user->hasRole('Admin')
        && ! $user->hasRole('Super Admin')
        && $user->faculty_id
    ) {
        $q->whereHas('course.program', function ($sub) use ($user) {
            $sub->where('faculty_id', $user->faculty_id);
        });
    }

    // 🔍 Filter search (tetap seperti semula)
    if ($search = $request->string('q')->toString()) {
        $q->where(function ($w) use ($search) {
            $w->where('title', 'like', "%{$search}%")
              ->orWhereHas('course', fn ($c) =>
                  $c->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
              );
        });
    }

    // 🎯 Filter status (draft/submitted/etc)
    if ($status = $request->string('status')->toString()) {
        $q->where('status', $status);
    }

    return view('rps.index', [
        'rpsList' => $q->paginate(12)->withQueryString(),
        'filters' => [
            'q'      => $search ?? '',
            'status' => $status ?? '',
        ],
    ]);
}


    /* ============================================================
     * CREATE STEP — menampilkan tiap halaman step
     * ============================================================ */
    public function createStep(Request $request, int $step)
    {
        $rpsId = $request->session()->get('rps_id');
        $rps   = $rpsId ? Rps::find($rpsId) : null;

        /* ---------- STEP 1 ---------- */
        if ($step === 1) {
            return view('rps.steps.identitas', compact('rps'));
        }

        /* ---------- STEP 2 ---------- */
        /* ---------- STEP 2 ---------- */
if ($step === 2) {
    if (!$rps) {
        return redirect()->route('rps.create.step', 1)
            ->with('error','Mulai dari Step 1 dulu.');
    }

    // Bangun seed dari data CPL → CPMK → subCPMK yang sudah ada di DB
    $plosSeed = $rps->plos()
        ->with([
            'outcomes.subClos' => function($q) {
                $q->orderBy('no');
            },
            'outcomes' => function($q) {
                $q->orderBy('no');
            },
        ])
        ->orderBy('order_no')
        ->get()
        ->map(function($plo){
            return [
                'code'        => $plo->code,
                'description' => $plo->description,
                'order_no'    => $plo->order_no,
                'clos'        => $plo->outcomes->map(function($clo){
                    return [
                        'no'             => $clo->no,
                        'description'    => $clo->description,
                        'order_no'       => $clo->order_no,
                        'weight_percent' => $clo->weight_percent,
                        'subs'           => $clo->subClos->map(function($sub){
                            return [
                                'no'          => $sub->no,
                                'description' => $sub->description,
                                'order_no'    => $sub->order_no,
                            ];
                        })->values(),
                    ];
                })->values(),
            ];
        })->values();

    return view('rps.steps.outcomes', [
        'rps'      => $rps,
        'plosSeed' => $plosSeed,
    ]);
}


        /* ---------- STEP 3 ---------- */
        if ($step === 3) {
            if (!$rps) {
                return redirect()->route('rps.create.step', 1)
                    ->with('error','Mulai dari Step 1 dulu.');
            }

            // pastikan kategori assessment ada
            if (RpsAssessmentCategory::count() === 0) {
                RpsAssessmentCategory::insert([
                    ['code'=>'PAR','name'=>'Partisipasi/Attendance','order_no'=>1,'created_at'=>now(),'updated_at'=>now()],
                    ['code'=>'PRO','name'=>'Proyek Akhir','order_no'=>2,'created_at'=>now(),'updated_at'=>now()],
                    ['code'=>'TG','name'=>'Tugas','order_no'=>3,'created_at'=>now(),'updated_at'=>now()],
                    ['code'=>'QZ','name'=>'Kuis','order_no'=>4,'created_at'=>now(),'updated_at'=>now()],
                    ['code'=>'UTS','name'=>'Ujian Tengah Semester','order_no'=>5,'created_at'=>now(),'updated_at'=>now()],
                    ['code'=>'UAS','name'=>'Ujian Akhir Semester','order_no'=>6,'created_at'=>now(),'updated_at'=>now()],
                ]);
            }

            $cats = RpsAssessmentCategory::orderBy('order_no')->get(['id','code','name']);

            // ⬇️ ambil juga weight_percent dari outcomes (CPMK)
            $clos = $rps->outcomesFlat()
                ->select('rps_outcomes.id',
                        'rps_outcomes.no',
                        'rps_outcomes.description',
                        'rps_outcomes.weight_percent')
                ->orderBy('rps_outcomes.no')
                ->get();

            $catWeights = RpsAssessment::where('rps_id',$rps->id)
                ->pluck('weight_percent','assessment_category_id')
                ->toArray();

            $weights = RpsAssessmentMapping::where('rps_id',$rps->id)
                ->get()
                ->groupBy(['assessment_category_id','outcome_id'])
                ->map(fn($g)=>$g->map(fn($w)=>$w->first()->percent))
                ->toArray();

            return view('rps.steps.assessments', [
                'rps'        => $rps,
                'cats'       => $cats,
                'clos'       => $clos,
                'weights'    => $weights,
                'catWeights' => $catWeights,
            ]);
        }


        /* ---------- STEP 4 ---------- */
        if ($step === 4) {
            if (!$rps) {
                return redirect()->route('rps.create.step',1)
                    ->with('error','Mulai dari Step 1 dulu.');
            }

            $refs = RpsReference::where('rps_id',$rps->id)
                ->orderBy('type')
                ->orderBy('order_no')
                ->get();

            return view('rps.steps.references', compact('rps','refs'));
        }

        /* ---------- STEP 5 ---------- */
        /* ---------- STEP 5 (Rencana Penilaian) ---------- */
if ($step === 5) {
    $rps = \App\Models\Rps::findOrFail($request->session()->get('rps_id'));

    $assess = \App\Models\RpsAssessment::where('rps_id', $rps->id)
        ->pluck('weight_percent','assessment_category_id'); // id => weight

    $cats = \App\Models\RpsAssessmentCategory::whereIn(
                'id', $assess->filter(fn($w)=>$w>0)->keys()
            )->orderBy('order_no')->get(['id','code','name']);

    $existing = $rps->evaluations()->get()->keyBy('assessment_category_id');

    return view('rps.steps.evaluations', compact('rps','cats','assess','existing'));
}

/* ---------- STEP 6 (RPM) ---------- */
if ($step === 6) {
    $rps = Rps::findOrFail($request->session()->get('rps_id'));
    $clos = $rps->outcomes()->orderBy('order_no')->get(['id','no','description']);
    $weeks = $rps->weeklyPlans()->with('outcomes:id')->get();

    return view('rps.steps.weekly', compact('rps','clos','weeks'));
}
        abort(404);
    }

    /* ============================================================
     * STORE STEP — simpan tiap tahap form
     * ============================================================ */
    public function storeStep(Request $request, int $step)
    {
        /* ---------- STEP 1 ---------- */
        /* ---------- STEP 1 ---------- */
    if ($step === 1) {
        $data = $request->validate([
            'course_id'     => ['required','integer','exists:courses,id'],
            'program_id'    => ['required','integer','exists:programs,id'],
            'academic_year' => ['required','string','max:20'],
            'semester'      => ['required','integer','min:1','max:14'],
            'sks'           => ['nullable','integer','min:1','max:10'],
            'delivery_mode' => ['nullable','string','max:20'],
            'language'      => ['nullable','string','max:50'],

            // FIELD BARU STEP 1
            'class_number'             => ['nullable','string','max:50'],
            'learning_activity_type'   => ['nullable','in:Kuliah,Seminar,Praktikum,Merdeka Belajar'],
            'course_category'          => ['nullable','in:MK wajib Universitas,MK wajib Fakultas,MK wajib Prodi,MK pilihan'],
            'short_description'        => ['nullable','string'],
            'prerequisite_courses'     => ['nullable','string','max:255'],
            'prerequisite_for_courses' => ['nullable','string','max:255'],
            'study_materials'          => ['nullable','string'],

            // Dosen pengampu
            'lecturers'         => ['nullable','array'],
            'lecturers.*.name'  => ['nullable','string'],
            'lecturers.*.email' => ['nullable','email'],
            'lecturers.*.nidn'  => ['nullable','string'],
        ]);

        // Rapikan lecturers: buang entri yang nama-nya kosong
        if (!empty($data['lecturers'])) {
            $data['lecturers'] = collect($data['lecturers'])
                ->filter(fn($row) => !empty($row['name']))
                ->values()
                ->all();
        }

        $rps = Rps::find($request->session()->get('rps_id')) ?? new Rps();
        $rps->fill($data);
        $rps->status = $rps->status ?? 'draft';
        $rps->submitted_by = auth()->id();
        $rps->save();

        $request->session()->put('rps_id', $rps->id);
        return redirect()->route('rps.create.step', 2)
            ->with('success','Identitas tersimpan. Lanjut ke Step 2.');
    }
        /* ---------- STEP 2 ---------- */
        if ($step === 2) {
            $rps = Rps::findOrFail($request->session()->get('rps_id'));

            $data = $request->validate([
                'plos' => ['required','array','min:1'],
                'plos.*.code'        => ['required','string','max:50'],
                'plos.*.description' => ['required','string'],
                'plos.*.clos'        => ['nullable','array'],
                'plos.*.clos.*.no'   => ['required_with:plos.*.clos','integer','min:1'],
                'plos.*.clos.*.description'      => ['required_with:plos.*.clos','string'],
                'plos.*.clos.*.weight_percent'   => ['nullable','numeric','min:0','max:100'],
                'plos.*.clos.*.subs'             => ['nullable','array'],
                'plos.*.clos.*.subs.*.no'        => ['required_with:plos.*.clos.*.subs','integer','min:1'],
                'plos.*.clos.*.subs.*.description' => ['required_with:plos.*.clos.*.subs','string'],
            ]);


            // reset (cascade by FK)
            $rps->plos()->each(function(RpsPlo $plo){
                $plo->outcomes()->each(fn(RpsOutcome $o)=>$o->subClos()->delete());
                $plo->outcomes()->delete();
            });
            $rps->plos()->delete();

            foreach (array_values($data['plos']) as $ploIn) {
                $plo = $rps->plos()->create([
                    'code'        => trim($ploIn['code']),
                    'description' => $ploIn['description'],
                ]);

                foreach (($ploIn['clos'] ?? []) as $cloIn) {
                    $clo = $plo->outcomes()->create([
                        'rps_id'         => $rps->id,
                        'no'             => (int)$cloIn['no'],
                        'clo'            => 'CPMK '.$cloIn['no'],
                        'description'    => $cloIn['description'],
                        'weight_percent' => $cloIn['weight_percent'] ?? null,
                    ]);

                    foreach (($cloIn['subs'] ?? []) as $subIn) {
                        $clo->subClos()->create([
                            'rps_id'      => $rps->id,
                            'no'          => (int)$subIn['no'],
                            'description' => $subIn['description'],
                        ]);
                    }
                }
            }

            return redirect()->route('rps.cpl_cpmk.edit', $rps)
    ->with('success','CPL → CPMK → sub-CPMK tersimpan. Lanjut atur bobot CPL–CPMK.');
        }

        /* ---------- STEP 3 ---------- */
        if ($step === 3) {
            $rps  = Rps::findOrFail($request->session()->get('rps_id'));
            $cats = RpsAssessmentCategory::orderBy('order_no')->get(['id']);
            
            // ambil CPMK + bobotnya dari Step 2
            $clos = $rps->outcomesFlat()
                ->select('rps_outcomes.id', 'rps_outcomes.no', 'rps_outcomes.weight_percent')
                ->orderBy('rps_outcomes.no')
                ->get();

            $data = $request->validate([
                'desc'         => ['sometimes','array'],
                'weights'      => ['sometimes','array'],
                'weights.*.*'  => ['nullable','numeric','min:0','max:100'],
            ]);

            $weightsInput = $data['weights'] ?? [];

            // 1) HITUNG bobot kategori SECARA OTOMATIS berdasarkan:
            //    - matriks weights[cat][clo] (kontribusi kategori → CPMK)
            //    - bobot CPMK (weight_percent) dari Step 2
            $catWeightsComputed = [];
            foreach ($cats as $cat) {
                $row   = $weightsInput[$cat->id] ?? [];
                $total = 0.0;

                foreach ($clos as $clo) {
                    $val = (float)($row[$clo->id] ?? 0);                  // kontribusi 0–100
                    $total += ($val / 100.0) * (float)($clo->weight_percent ?? 0);
                }

                $catWeightsComputed[$cat->id] = $total; // dalam persen
            }

            // 2) VALIDASI total bobot kategori (hasil perhitungan) harus ≈ 100%
            $sum = array_sum($catWeightsComputed);
            if (abs($sum - 100) > 0.001) {
                return back()->withInput()->withErrors([
                    'cat_weight' => 'Total bobot kategori (hasil perhitungan dari CPMK) harus = 100%. Sekarang: '
                        . number_format($sum, 2) . '%.',
                ]);
            }

            // 3) SIMPAN bobot kategori (RpsAssessment)
            foreach ($cats as $cat) {
                $w    = $catWeightsComputed[$cat->id] ?? 0.0;
                $desc = $data['desc'][$cat->id] ?? null;

                $row = RpsAssessment::firstOrCreate([
                    'rps_id'                => $rps->id,
                    'assessment_category_id'=> $cat->id,
                ]);

                $row->weight_percent = $w;
                $row->desc           = $desc;
                $row->save();
            }

            // 4) SIMPAN matriks mapping kategori → CPMK
            RpsAssessmentMapping::where('rps_id',$rps->id)->delete();
            foreach ($weightsInput as $catId => $cols) {
                foreach (($cols ?? []) as $cloId => $pct) {
                    $pct = (float) ($pct ?? 0);
                    if ($pct <= 0) continue;

                    RpsAssessmentMapping::create([
                        'rps_id'                => $rps->id,
                        'assessment_category_id'=> (int)$catId,
                        'outcome_id'            => (int)$cloId,
                        'percent'               => $pct,
                    ]);
                }
            }

            return redirect()->route('rps.create.step', 4)
                ->with('success','Matriks & bobot kategori (dihitung dari CPMK) tersimpan. Lanjut ke Step 4 (Referensi).');
        }


        /* ---------- STEP 4 ---------- */
        if ($step === 4) {
            $rps = Rps::findOrFail($request->session()->get('rps_id'));

            $data = $request->validate([
                'references'              => ['required','array','min:1'],
                'references.*.type'       => ['required','in:utama,pendukung'],
                'references.*.author'     => ['nullable','string','max:255'],
                'references.*.year'       => ['nullable','string','max:10'],
                'references.*.title'      => ['required','string','max:500'],
                'references.*.publisher'  => ['nullable','string','max:255'],
                'references.*.city'       => ['nullable','string','max:255'],
                'references.*.isbn_issn'  => ['nullable','string','max:50'],
                'references.*.url'        => ['nullable','string','max:500'],
            ]);

            $rps->references()->delete();
            $order = 1;
            foreach (array_values($data['references']) as $ref) {
                $rps->references()->create([
                    'type'       => $ref['type'],
                    'author'     => $ref['author'] ?? null,
                    'year'       => $ref['year'] ?? null,
                    'title'      => $ref['title'],
                    'publisher'  => $ref['publisher'] ?? null,
                    'city'       => $ref['city'] ?? null,
                    'isbn_issn'  => $ref['isbn_issn'] ?? null,
                    'url'        => $ref['url'] ?? null,
                    'order_no'   => $order++,
                ]);
            }

            return redirect()->route('rps.create.step', 5)
                ->with('success','Referensi berhasil disimpan. Lanjut ke Step 5 (Rencana Pembelajaran Mingguan).');
        }

        /* ---------- STEP 5 ---------- */
        /* ---------- STEP 5 (Rencana Penilaian) ---------- */
if ($step === 5) {
    $rps = \App\Models\Rps::findOrFail($request->session()->get('rps_id'));

    $assess = \App\Models\RpsAssessment::where('rps_id',$rps->id)
        ->pluck('weight_percent','assessment_category_id');

    $allowed = $assess->filter(fn($w)=>$w>0)->keys()->all();

    $data = $request->validate([
        'evaluations'                      => ['nullable','array'],
        'evaluations.*.method'             => ['nullable','string'],
        'evaluations.*.criteria'           => ['nullable','string'],
        'evaluations.*.due_week'           => ['nullable','integer','min:1','max:30'],
    ]);

    $rps->evaluations()->delete();

    $order = 1;
    foreach ($allowed as $catId) {
        $row = $data['evaluations'][$catId] ?? [];
        $rps->evaluations()->create([
            'assessment_category_id' => (int)$catId,
            'method'        => $row['method']   ?? null,
            'criteria'      => $row['criteria'] ?? null,
            'due_week'      => isset($row['due_week']) ? (int)$row['due_week'] : null,
            'weight_percent'=> (float)$assess[$catId], // lock dari Step 3
            'order_no'      => $order++,
        ]);
    }

    return redirect()->route('rps.create.step', 6)
        ->with('success','Rencana Penilaian tersimpan. Lanjut ke Step 6 (RPM).');
}

/* ---------- STEP 6 (RPM) ---------- */
if ($step === 6) {
    $rps = Rps::findOrFail($request->session()->get('rps_id'));

    $data = $request->validate([
        'weeks'                       => ['required','array','min:1'],
        'weeks.*.week_no'             => ['required','integer','min:1','max:30'],
        'weeks.*.topic'               => ['required','string','max:255'],
        'weeks.*.sub_topics'          => ['nullable','string'],
        'weeks.*.learning_method'     => ['nullable','string','max:255'],
        'weeks.*.student_activity'    => ['nullable','string'],
        'weeks.*.media_tools'         => ['nullable','string','max:255'],
        'weeks.*.weight_percent'      => ['nullable','numeric','min:0','max:100'],
        'weeks.*.references'          => ['nullable','string'],
        'weeks.*.clos'                => ['nullable','array'],
        'weeks.*.clos.*.id'           => ['required','integer','exists:rps_outcomes,id'],
        'weeks.*.clos.*.percent'      => ['nullable','numeric','min:0','max:100'],
    ]);

    $rps->weeklyPlans()->delete();

    $weekOrder = 1;
    foreach (array_values($data['weeks']) as $w) {
        $wp = $rps->weeklyPlans()->create([
            'week_no'         => (int)$w['week_no'],
            'topic'           => (string)$w['topic'],
            'sub_topics'      => $w['sub_topics'] ?? null,
            'learning_method' => $w['learning_method'] ?? null,
            'student_activity'=> $w['student_activity'] ?? null,
            'media_tools'     => $w['media_tools'] ?? null,
            'weight_percent'  => isset($w['weight_percent']) ? (float)$w['weight_percent'] : 0,
            'references'      => $w['references'] ?? null,
            'order_no'        => $weekOrder++,
        ]);

        foreach (($w['clos'] ?? []) as $c) {
            if (empty($c['id'])) continue;
            $wp->outcomes()->attach(
                (int)$c['id'],
                ['percent'=>isset($c['percent']) ? (float)$c['percent'] : 0]
            );
        }
    }

    return redirect()->route('rps.index')->with('success','RPM tersimpan.');
}
        abort(404);
    }
    public function resume(Request $request, Rps $rps, int $step = 1)
{
    // Simpan RPS yang mau diedit ke session,
    // supaya createStep() dan storeStep() tahu sedang mengerjakan RPS mana.
    $request->session()->put('rps_id', $rps->id);

    // Optional: kalau kamu mau kunci hanya status draft yang boleh diedit:
    // if ($rps->status !== 'draft') {
    //     return redirect()->route('rps.index')
    //         ->with('error', 'RPS dengan status ini tidak dapat diedit.');
    // }

    // Arahkan ke step yang diminta (1, 2, 3, dst.)
    return redirect()->route('rps.create.step', $step);
}

    public function editCplCpmk(Rps $rps)
{
    // CPL (PLO) & CPMK (CLO) yang sudah dibuat di Step 2
    $plos = $rps->plos()->orderBy('order_no')->get();
    $clos = $rps->outcomes()->orderBy('no')->get(); // relasi yg sudah dipakai di step lain

    // ambil bobot existing
    $weights = RpsCplCpmkWeight::where('rps_id', $rps->id)
        ->get()
        ->groupBy(['plo_id', 'outcome_id'])
        ->map(fn($g) => $g->map(fn($w) => $w->first()->percent))
        ->toArray();

    return view('rps.steps.cpl_cpmk', [
        'rps'     => $rps,
        'plos'    => $plos,
        'clos'    => $clos,
        'weights' => $weights,
    ]);
}

public function updateCplCpmk(Request $request, Rps $rps)
{
    $data = $request->validate([
        'weights'     => ['array'],
        'weights.*.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
    ]);

    $weightsInput = $data['weights'] ?? [];

    // hapus dulu mapping lama utk RPS ini
    RpsCplCpmkWeight::where('rps_id', $rps->id)->delete();

    // simpan baru
    foreach ($weightsInput as $ploId => $perOutcome) {
        foreach ($perOutcome as $outcomeId => $percent) {
            $percent = (float) $percent;
            if ($percent <= 0) {
                continue;
            }

            RpsCplCpmkWeight::create([
                'rps_id'     => $rps->id,
                'plo_id'     => $ploId,
                'outcome_id' => $outcomeId,
                'percent'    => $percent,
            ]);
        }
    }

    // ⬇⬇⬇ DI SINI YANG BERUBAH
    return redirect()
        ->route('rps.create.step', 3)
        ->with('success', 'Bobot CPL–CPMK berhasil disimpan. Lanjut ke Step 3 (Bobot CPMK & Assessment).');
}


}
