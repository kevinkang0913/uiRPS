<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{
    Rps, RpsPlo, RpsOutcome, RpsSubClo,
    RpsAssessmentCategory, RpsAssessmentMapping, RpsAssessment,
    RpsReference, RpsWeeklyPlan, RpsCplCpmkWeight,RpsWeeklyActivity,
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

    $query = Rps::query()
        ->with(['course.program.faculty'])
        ->latest();

    /*
     * ========== SCOPING BERDASARKAN ROLE ==========
     *
     * Super Admin   : lihat semua
     * Admin         : hanya RPS di fakultas (dan optional prodi) dia
     * Kaprodi       : hanya RPS di prodi (atau fakultas) dia
     * Dosen         : RPS di prodi/fakultas dia; kalau scope kosong, fallback ke RPS yang dia submit sendiri
     * CTL           : biarkan lihat semua (fokus di menu Review)
     */

    if ($user->hasRole('Super Admin')) {
        // no restriction
    }
    elseif ($user->hasRole('Admin')) {
        // Admin fakultas → wajib punya faculty_id
        if ($user->faculty_id) {
            $query->whereHas('course.program', function ($q) use ($user) {
                $q->where('faculty_id', $user->faculty_id);

                // kalau admin di-scope sampai prodi
                if (!is_null($user->program_id)) {
                    $q->where('id', $user->program_id);
                }
            });
        } else {
            // admin tanpa scope fakultas → tidak lihat apa-apa
            $query->whereRaw('1 = 0');
        }
    }
    elseif ($user->hasRole('Kaprodi')) {
        if (!is_null($user->program_id)) {
            // Kaprodi prodi tertentu
            $query->whereHas('course', function ($q) use ($user) {
                $q->where('program_id', $user->program_id);
            });
        } elseif ($user->faculty_id) {
            // fallback: kaprodi di-scope fakultas
            $query->whereHas('course.program', function ($q) use ($user) {
                $q->where('faculty_id', $user->faculty_id);
            });
        }
    }
    elseif ($user->hasRole('Dosen')) {
        if (!is_null($user->program_id)) {
            // dosen di-scope prodi
            $query->whereHas('course', function ($q) use ($user) {
                $q->where('program_id', $user->program_id);
            });
        } elseif ($user->faculty_id) {
            // fallback: scope fakultas
            $query->whereHas('course.program', function ($q) use ($user) {
                $q->where('faculty_id', $user->faculty_id);
            });
        } else {
            // kalau belum di-assign fakultas/prodi → minimal hanya lihat RPS yang dia submit sendiri
            $query->where('submitted_by', $user->id);
        }
    }
    // else: CTL atau role lain → biarin akses full index, tapi operasi review tetap lewat menu Review

    /*
     * ========== SEARCH & FILTER STATUS (sama seperti versi kamu) ==========
     */
    $search = $request->string('q')->toString();
    if ($search !== '') {
        $query->where(function($w) use ($search) {
            $w->where('title','like',"%{$search}%")
              ->orWhereHas('course', function($c) use ($search) {
                  $c->where('name','like',"%{$search}%")
                    ->orWhere('code','like',"%{$search}%");
              });
        });
    }

    $status = $request->string('status')->toString();
    if ($status !== '') {
        $query->where('status',$status);
    }

    return view('rps.index', [
        'rpsList' => $query->paginate(12)->withQueryString(),
        'filters' => [
            'q'      => $search,
            'status' => $status,
        ],
    ]);
}
public function show(Rps $rps)
{
    // load relasi dasar untuk header
    $rps->load([
        'course.program.faculty',
        'plos' => fn($q) => $q->orderBy('order_no'),
        'plos.outcomes' => fn($q) => $q->orderBy('no'),
        'plos.outcomes.subClos' => fn($q) => $q->orderBy('no'),
        'contract',
    ]);

    // CPMK flat + sub-CPMK (dipakai di beberapa bagian view)
    $clos = $rps->outcomesFlat()
        ->with(['subClos' => fn($q) => $q->orderBy('no')])
        ->orderBy('no')
        ->get();

    // kategori assessment + matriks bobot
    $cats = RpsAssessmentCategory::orderBy('order_no')->get();

    $assessments = $rps->assessments()
        ->get()
        ->keyBy('assessment_category_id'); // id => RpsAssessment

    $matrix = RpsAssessmentMapping::where('rps_id', $rps->id)
        ->get()
        ->groupBy(['assessment_category_id', 'outcome_id']);
        // akses di blade: $matrix[$catId][$cloId]->first()->percent ?? 0

    // RPM (weekly plans) + referensi
    $plans = $rps->weeklyPlans()
        ->with([
            'subClo.outcome',
            'reference',
            'activities' => fn($q) => $q->orderBy('mode')->orderBy('order_no'),
        ])
        ->orderBy('week_no')
        ->orderBy('order_no')
        ->get();

    // susun per minggu untuk tampilan
    $weeks = $plans->groupBy('week_no')->map(function ($rows, $weekNo) {
        $first = $rows->first();

        return [
            'week_no'              => (int) $weekNo,
            'session_no'           => $first->session_no,
            'topic'                => $first->topic,
            'indicator'            => $first->indicator,
            'assessment_technique' => $first->assessment_technique,
            'assessment_criteria'  => $first->assessment_criteria,
            'learning_in'          => $first->learning_in,
            'learning_online'      => $first->learning_online,
            'reference'            => $first->reference,
            'items'                => $rows, // semua sub-CPMK per minggu
            'weight_total'         => (float) $rows->sum('weight_percent'),
        ];
    })->values();

    $contract = $rps->contract; // boleh null

    return view('rps.show', [
        'rps'         => $rps,
        'plos'        => $rps->plos,
        'clos'        => $clos,
        'cats'        => $cats,
        'assessments' => $assessments,
        'matrix'      => $matrix,
        'weeks'       => $weeks,
        'contract'    => $contract,
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
if ($step === 2) {
    if (!$rps) {
        return redirect()->route('rps.create.step', 1)
            ->with('error','Mulai dari Step 1 dulu.');
    }

    // Pastikan kategori assessment ada (PAR, PRO, dst.)
    if (\App\Models\RpsAssessmentCategory::count() === 0) {
        \App\Models\RpsAssessmentCategory::insert([
            ['code'=>'PAR','name'=>'Partisipasi/Attendance','order_no'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['code'=>'PRO','name'=>'Proyek Akhir','order_no'=>2,'created_at'=>now(),'updated_at'=>now()],
            ['code'=>'TG','name'=>'Tugas','order_no'=>3,'created_at'=>now(),'updated_at'=>now()],
            ['code'=>'QZ','name'=>'Kuis','order_no'=>4,'created_at'=>now(),'updated_at'=>now()],
            ['code'=>'UTS','name'=>'Ujian Tengah Semester','order_no'=>5,'created_at'=>now(),'updated_at'=>now()],
            ['code'=>'UAS','name'=>'Ujian Akhir Semester','order_no'=>6,'created_at'=>now(),'updated_at'=>now()],
        ]);
    }

    $cats    = \App\Models\RpsAssessmentCategory::orderBy('order_no')->get();
    $catById = $cats->keyBy('id'); // id => kategori (punya ->code)

    // Ambil mapping kategori × CPMK (hasil simpan Step 2 sebelumnya)
    $mappings = \App\Models\RpsAssessmentMapping::where('rps_id', $rps->id)
        ->get()
        ->groupBy('outcome_id'); // outcome_id => collection rows

    // Bangun seed dari data CPL → CPMK → subCPMK yang sudah ada di DB
    $plosSeed = $rps->plos()
        ->with(['outcomes.subClos' => fn($q) => $q->orderBy('no'),
                'outcomes'        => fn($q) => $q->orderBy('no')])
        ->orderBy('order_no')
        ->get()
        ->map(function ($plo) use ($mappings, $catById) {

            // bobot CPL global yg kita simpan di kolom weight_percent
            $cplGlobal = (float) ($plo->weight_percent ?? 0);

            return [
                'code'        => $plo->code,
                'description' => $plo->description,
                'order_no'    => $plo->order_no,
                // UI butuh weight_cpl (0–100) → kita isi dari global
                'weight_cpl'  => $cplGlobal > 0 ? $cplGlobal : null,

                'clos' => $plo->outcomes->map(function ($clo) use ($plo, $cplGlobal, $mappings, $catById) {
                    $cpmkGlobal = (float) ($clo->weight_percent ?? 0);

                    // convert ke bobot lokal CPMK dalam CPL (0–100)
                    $localCpmk = null;
                    if ($cplGlobal > 0 && $cpmkGlobal > 0) {
                        $localCpmk = $cpmkGlobal * 100.0 / $cplGlobal;
                    }

                    // sub-CPMK: convert bobot global → lokal dalam CPMK
                    $subs = $clo->subClos->map(function ($sub) use ($cpmkGlobal) {
                        $subGlobal = (float) ($sub->weight_percent ?? 0);
                        $localSub  = null;
                        if ($cpmkGlobal > 0 && $subGlobal > 0) {
                            $localSub = $subGlobal * 100.0 / $cpmkGlobal;
                        }

                        return [
                            'no'          => $sub->no,
                            'description' => $sub->description,
                            'order_no'    => $sub->order_no,
                            'weight_sub'  => $localSub,
                        ];
                    })->values();

                    // assessment per CPMK: ambil dari RpsAssessmentMapping
                    $assessArr = [];
                    $rows = $mappings[$clo->id] ?? collect();
                    foreach ($rows as $row) {
                        $cat  = $catById[$row->assessment_category_id] ?? null;
                        $code = $cat?->code;
                        if ($code) {
                            // percent di mapping = 0–100 lokal di CPMK
                            $assessArr[$code] = (float) $row->percent;
                        }
                    }

                    return [
                        'no'           => $clo->no,
                        'description'  => $clo->description,
                        'order_no'     => $clo->order_no,
                        'weight_cpmk'  => $localCpmk,
                        'subs'         => $subs,
                        'assess'       => $assessArr,
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

            // ambil CPMK + bobot global (weight_percent) dari Step 2
            $clos = $rps->outcomesFlat()
                ->select(
                    'rps_outcomes.id',
                    'rps_outcomes.no',
                    'rps_outcomes.description',
                    'rps_outcomes.weight_percent'
                )
                ->orderBy('rps_outcomes.no')
                ->get();

            // ambil baris assessment (bobot kategori + desc + due_week) kalau sudah pernah disimpan
            $assessmentRows = RpsAssessment::where('rps_id', $rps->id)->get([
                'assessment_category_id',
                'weight_percent',
                'desc',
                'due_week',
            ]);

            $catWeights = $assessmentRows
                ->pluck('weight_percent', 'assessment_category_id')
                ->toArray();

            $catDesc = $assessmentRows
                ->pluck('desc', 'assessment_category_id')
                ->toArray();

            $catDue = $assessmentRows
                ->pluck('due_week', 'assessment_category_id')
                ->toArray();

            // matriks kategori × CPMK (local percent di dalam CPMK)
            $weights = RpsAssessmentMapping::where('rps_id',$rps->id)
                ->get()
                ->groupBy(['assessment_category_id','outcome_id'])
                ->map(fn($g) => $g->map(fn($w) => $w->first()->percent))
                ->toArray();

            return view('rps.steps.assessments', [
                'rps'        => $rps,
                'cats'       => $cats,
                'clos'       => $clos,
                'weights'    => $weights,
                'catWeights' => $catWeights,
                'catDesc'    => $catDesc,
                'catDue'     => $catDue,
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
        /* ---------- STEP 5 (Rencana Penilaian) ---------- 
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
*/
/* ---------- STEP 5 (CREATE) ---------- */
if ($step === 5) {
    $rpsId = $request->session()->get('rps_id');
    $rps   = Rps::findOrFail($rpsId);

    // Semua Sub-CPMK untuk dropdown
    $subClos = \App\Models\RpsSubClo::where('rps_id', $rps->id)
        ->with(['outcome' => function($q){
            $q->select('id','no');
        }])
        ->orderBy('outcome_id')
        ->orderBy('no')
        ->get();

    // Referensi (Step 4) untuk dropdown
    $refs = $rps->references()
        ->orderBy('type')
        ->orderBy('order_no')
        ->get();

    // Ambil RPM lama: 1 row = 1 Sub-CPMK pada suatu minggu
    $plans = $rps->weeklyPlans()
        ->with(['subClo.outcome','reference'])
        ->orderBy('week_no')
        ->orderBy('order_no')
        ->get();

    // Grup per week_no
    $weeks = $plans->groupBy('week_no')->map(function($rows, $weekNo) {
        /** @var \App\Models\RpsWeeklyPlan $first */
        $first = $rows->first();

        return [
            'week_no'               => (int)$weekNo,
            'session_no'            => $first->session_no,
            'topic'                 => $first->topic,
            'indicator'             => $first->indicator,
            'assessment_technique'  => $first->assessment_technique,
            'assessment_criteria'   => $first->assessment_criteria,

            // teks mentah (sinkron ke DB lama)
            'learning_in'           => $first->learning_in,
            'learning_online'       => $first->learning_online,

            'reference_id'          => $first->reference_id,
            'weight_total'          => (float)$rows->sum('weight_percent'),

            // dropdown Sub-CPMK
            'sub_clos'              => $rows->pluck('sub_clo_id')->all(),

            // ⬇⬇ NEW: parse teks jadi array aktivitas
            'activities_in'         => $this->parseWeeklyActivities($first->learning_in),
            'activities_online'     => $this->parseWeeklyActivities($first->learning_online),
        ];
    })->values();

    return view('rps.steps.weekly', [
        'rps'     => $rps,
        'subClos' => $subClos,
        'refs'    => $refs,
        'weeks'   => $weeks,
    ]);
}



/* ---------- STEP 6 (CREATE: KONTRAK) ---------- */
if ($step === 6) {

    $rps = Rps::findOrFail($request->session()->get('rps_id'));
    $contract = $rps->contract; // bisa null

    // Placeholder saja, tidak disimpan
    $placeholderClassPolicy = <<<TXT
• Mahasiswa diharapkan mengikuti kegiatan perkuliahan sepenuhnya.
• Ketidakhadiran harus dikomunikasikan kepada dosen/PIC.
• Mahasiswa wajib mengikuti aturan kelas selama proses belajar.
TXT;

    $placeholderContract = <<<TXT
Saya menyatakan telah membaca dan memahami RPS, serta bersedia mengikuti
kontrak perkuliahan selama semester berlangsung.
TXT;

    return view('rps.steps.contract', [
        'rps'                  => $rps,
        'contract'             => $contract,
        'placeholderClass'     => $placeholderClassPolicy,
        'placeholderContract'  => $placeholderContract,
    ]);
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
        if ($request->boolean('exit_to_index')) {
    return redirect()
        ->route('rps.index')
        ->with('success','Pesan sukses sesuai step.');
}
        return redirect()->route('rps.create.step', 2)
            ->with('success','Identitas tersimpan. Lanjut ke Step 2.');
    }
        /* ---------- STEP 2 ---------- */
        /* ---------- STEP 2 ---------- */
/* ---------- STEP 2 ---------- */
if ($step === 2) {
    $rps = Rps::findOrFail($request->session()->get('rps_id'));

    // Pastikan kategori assessment ada
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

    $cats = RpsAssessmentCategory::orderBy('order_no')->get();
    $catByCode = $cats->keyBy('code'); // PAR, PRO, ...

    $data = $request->validate([
        'plos'                         => ['required','array','min:1'],

        'plos.*.code'                  => ['required','string','max:50'],
        'plos.*.description'           => ['required','string'],
        'plos.*.weight_cpl'            => ['required','numeric','min:0','max:100'],

        'plos.*.clos'                  => ['nullable','array'],

        'plos.*.clos.*.no'             => ['required_with:plos.*.clos','integer','min:1'],
        'plos.*.clos.*.description'    => ['required_with:plos.*.clos','string'],
        'plos.*.clos.*.weight_cpmk'    => ['required_with:plos.*.clos','numeric','min:0','max:100'],

        'plos.*.clos.*.subs'           => ['nullable','array'],
        'plos.*.clos.*.subs.*.no'      => ['required_with:plos.*.clos.*.subs','integer','min:1'],
        'plos.*.clos.*.subs.*.description' => ['required_with:plos.*.clos.*.subs','string'],
        'plos.*.clos.*.subs.*.weight_sub'  => ['nullable','numeric','min:0','max:100'],

        'plos.*.clos.*.assess'         => ['required','array'],
        'plos.*.clos.*.assess.*'       => ['nullable','numeric','min:0','max:100'],
    ]);

    $plosInput = $data['plos'];

    /*
     * 1) VALIDASI JUMLAH BOBOT
     */

    // 1.a total CPL
    $totalCpl = 0.0;
    foreach ($plosInput as $ploIn) {
        $totalCpl += (float) ($ploIn['weight_cpl'] ?? 0);
    }
    if (abs($totalCpl - 100) > 0.001) {
        return back()->withInput()->withErrors([
            'plos' => 'Total bobot semua CPL harus 100%. Sekarang: '.number_format($totalCpl,2).'%.',
        ]);
    }

    foreach ($plosInput as $idxPlo => $ploIn) {
        $clos = $ploIn['clos'] ?? [];
        if (!count($clos)) {
            continue;
        }

        // 1.b. per CPL: total CPMK = 100
        $sumCpmk = 0.0;
        foreach ($clos as $cloIn) {
            $sumCpmk += (float) ($cloIn['weight_cpmk'] ?? 0);
        }
        if (abs($sumCpmk - 100) > 0.001) {
            return back()->withInput()->withErrors([
                "plos.$idxPlo.clos" =>
                    "Total bobot CPMK di bawah CPL #".($idxPlo+1)." harus 100%. Sekarang: ".number_format($sumCpmk,2).'%.',
            ]);
        }

        // 1.c. per CPMK: total sub = 100 (jika ada)
        foreach ($clos as $idxClo => $cloIn) {
            $subs = $cloIn['subs'] ?? [];
            if (count($subs)) {
                $sumSub = 0.0;
                foreach ($subs as $s) {
                    $sumSub += (float) ($s['weight_sub'] ?? 0);
                }
                if (abs($sumSub - 100) > 0.001) {
                    return back()->withInput()->withErrors([
                        "plos.$idxPlo.clos.$idxClo.subs" =>
                            "Total bobot sub-CPMK di CPMK #".($cloIn['no'] ?? ($idxClo+1))." harus 100%. Sekarang: ".number_format($sumSub,2).'%.',
                    ]);
                }
            }

            // 1.d. per CPMK: total assessment = 100
            $assess = $cloIn['assess'] ?? [];
            $sumAss = 0.0;
            foreach ($catByCode as $code => $cat) {
                $sumAss += (float) ($assess[$code] ?? 0);
            }
            if (abs($sumAss - 100) > 0.001) {
                return back()->withInput()->withErrors([
                    "plos.$idxPlo.clos.$idxClo.assess" =>
                        "Total bobot kategori assessment di CPMK #".($cloIn['no'] ?? ($idxClo+1))." harus 100%. Sekarang: ".number_format($sumAss,2).'%.',
                ]);
            }
        }
    }

    /*
     * 2) RESET DATA LAMA
     */

    RpsAssessmentMapping::where('rps_id', $rps->id)->delete();
    //RpsAssessment::where('rps_id', $rps->id)->delete();

    $rps->plos()->each(function (RpsPlo $plo) {
        $plo->outcomes()->each(function (RpsOutcome $o) {
            $o->subClos()->delete();
        });
        $plo->outcomes()->delete();
    });
    $rps->plos()->delete();

    /*
     * 3) SIMPAN PLO/CLO/subCLO + HITUNG BOBOT GLOBAL
     */

    $closMeta = [];
    $catTotalsGlobal = [];

    foreach ($plosInput as $ploIn) {
        $cplWeight = (float) ($ploIn['weight_cpl'] ?? 0);

        $plo = $rps->plos()->create([
            'code'           => trim($ploIn['code']),
            'description'    => $ploIn['description'],
            'order_no'       => $ploIn['order_no'] ?? null,
            'weight_percent' => $cplWeight, // simpan bobot CPL global
        ]);

        foreach (($ploIn['clos'] ?? []) as $cloIn) {
            $cpmkLocal  = (float) ($cloIn['weight_cpmk'] ?? 0); // 0–100 dalam CPL
            $cpmkGlobal = $cplWeight * $cpmkLocal / 100.0;     // global

            $clo = $plo->outcomes()->create([
                'rps_id'         => $rps->id,
                'no'             => (int) $cloIn['no'],
                'clo'            => 'CPMK '.$cloIn['no'],
                'description'    => $cloIn['description'],
                'order_no'       => $cloIn['order_no'] ?? null,
                'weight_percent' => $cpmkGlobal, // bobot global CPMK
            ]);

            // sub-CPMK
            foreach (($cloIn['subs'] ?? []) as $subIn) {
                $subLocal  = (float) ($subIn['weight_sub'] ?? 0);
                $subGlobal = $cpmkGlobal * $subLocal / 100.0;

                $clo->subClos()->create([
                    'rps_id'         => $rps->id,
                    'no'             => (int) $subIn['no'],
                    'description'    => $subIn['description'],
                    'order_no'       => $subIn['order_no'] ?? null,
                    'weight_percent' => $subGlobal,
                ]);
            }

            $closMeta[] = [
                'id'                 => $clo->id,
                'weight_cpmk_global' => $cpmkGlobal,
                'assess'             => $cloIn['assess'] ?? [],
            ];

            foreach ($catByCode as $code => $cat) {
                $pctLocal = (float) (($cloIn['assess'][$code] ?? 0));
                if ($pctLocal <= 0) {
                    continue;
                }
                $contrib = $cpmkGlobal * $pctLocal / 100.0;
                $catTotalsGlobal[$cat->id] = ($catTotalsGlobal[$cat->id] ?? 0) + $contrib;
            }
        }
    }

    // validasi total kategori global ≈ 100
    $sumCats = array_sum($catTotalsGlobal);
    if (abs($sumCats - 100) > 0.001) {
        return back()->withInput()->withErrors([
            'assess_total' => 'Total bobot semua kategori assessment (PAR/PRO/TG/QZ/UTS/UAS) harus 100%. Sekarang: '.number_format($sumCats,2).'%.',
        ]);
    }

    /*
     * 4) SIMPAN RpsAssessment (TOTAL per KATEGORI)
     */
    foreach ($cats as $cat) {
        $wGlobal = $catTotalsGlobal[$cat->id] ?? 0.0;

        $row = RpsAssessment::firstOrCreate([
            'rps_id'                 => $rps->id,
            'assessment_category_id' => $cat->id,
        ]);

        $row->weight_percent = $wGlobal;
        // desc & due_week diisi di STEP 3
        $row->save();
    }

    /*
     * 5) SIMPAN RpsAssessmentMapping (kategori × CPMK)
     */
    foreach ($closMeta as $meta) {
        $outcomeId = $meta['id'];
        $assessRow = $meta['assess'] ?? [];

        foreach ($catByCode as $code => $cat) {
            $pctLocal = (float) ($assessRow[$code] ?? 0);
            if ($pctLocal <= 0) {
                continue;
            }

            RpsAssessmentMapping::create([
                'rps_id'                 => $rps->id,
                'assessment_category_id' => $cat->id,
                'outcome_id'             => $outcomeId,
                'percent'                => $pctLocal, // 0–100 di dalam CPMK
            ]);
        }
    }
if ($request->boolean('exit_to_index')) {
    return redirect()
        ->route('rps.index')
        ->with('success','Pesan sukses sesuai step.');
}
    return redirect()
        ->route('rps.create.step', 3)
        ->with('success','CPL → CPMK → sub-CPMK & bobot assessment tersimpan. Lanjut ke Step 3 (summary & deskripsi penilaian).');
}

        /* ---------- STEP 3 ---------- */
/* ---------- STEP 3 ---------- */
if ($step === 3) {
    $rps  = Rps::findOrFail($request->session()->get('rps_id'));
    $cats = RpsAssessmentCategory::orderBy('order_no')->get(['id']);

    // Ambil CPMK + bobot global dari Step 2
    $clos = $rps->outcomesFlat()
        ->select('rps_outcomes.id', 'rps_outcomes.no', 'rps_outcomes.weight_percent')
        ->orderBy('rps_outcomes.no')
        ->get();

    $data = $request->validate([
        'desc'        => ['sometimes','array'],
        'desc.*'      => ['nullable','string','max:255'],
        'due_week'    => ['sometimes','array'],
        'due_week.*'  => ['nullable','string','max:100'],
        'weights'     => ['sometimes','array'],
        'weights.*.*' => ['nullable','numeric','min:0','max:100'],
    ]);

    $weightsInput = $data['weights'] ?? [];

    // 1) Hitung bobot kategori otomatis
    $catWeightsComputed = [];
    foreach ($cats as $cat) {
        $row   = $weightsInput[$cat->id] ?? [];
        $total = 0.0;

        foreach ($clos as $clo) {
            $val = (float)($row[$clo->id] ?? 0); // 0–100 di dalam CPMK
            $total += ($val / 100.0) * (float)($clo->weight_percent ?? 0);
        }

        $catWeightsComputed[$cat->id] = $total;
    }

    // 2) Validasi total bobot kategori ≈ 100%
    $sum = array_sum($catWeightsComputed);
    if (abs($sum - 100) > 0.001) {
        return back()->withInput()->withErrors([
            'cat_weight' => 'Total bobot kategori (hasil perhitungan dari CPMK) harus = 100%. Sekarang: '
                . number_format($sum, 2) . '%.',
        ]);
    }

    // 3) Simpan bobot kategori + deskripsi + due week
    foreach ($cats as $cat) {
        $w       = $catWeightsComputed[$cat->id] ?? 0.0;
        $desc    = $data['desc'][$cat->id] ?? null;
        $dueWeek = $data['due_week'][$cat->id] ?? null;

        $row = RpsAssessment::firstOrCreate([
            'rps_id'                 => $rps->id,
            'assessment_category_id' => $cat->id,
        ]);

        $row->weight_percent = $w;
        $row->desc           = $desc;
        $row->due_week       = $dueWeek ?: null;
        $row->save();
    }

    // 4) Simpan matriks mapping kategori → CPMK
    RpsAssessmentMapping::where('rps_id', $rps->id)->delete();

    foreach ($weightsInput as $catId => $cols) {
        foreach (($cols ?? []) as $cloId => $pct) {
            $pct = (float) ($pct ?? 0);
            if ($pct <= 0) continue;

            RpsAssessmentMapping::create([
                'rps_id'                 => $rps->id,
                'assessment_category_id' => (int)$catId,
                'outcome_id'             => (int)$cloId,
                'percent'                => $pct,
            ]);
        }
    }
    if ($request->boolean('exit_to_index')) {
    return redirect()
        ->route('rps.index')
        ->with('success','Pesan sukses sesuai step.');
}
    return redirect()
        ->route('rps.create.step', 4)
        ->with('success', 'Matriks & bobot kategori (dihitung dari CPMK) tersimpan. Lanjut ke Step 4 (Referensi).');
}


        /* ---------- STEP 4 ---------- */
       /* ---------- STEP 4 (REFERENSI) ---------- */
if ($step === 4) {
    $rps = Rps::findOrFail($request->session()->get('rps_id'));

    // Validasi input
    $data = $request->validate([
        'refs'           => ['required','array','min:1'],
        'refs.*.type'    => ['required_with:refs.*.text', 'in:utama,pendukung,lainnya'],
        'refs.*.text'    => ['required_with:refs.*.type', 'string'],          // teks referensi bebas
        'refs.*.url'     => ['nullable', 'string','max:500'],
    ], [
        'refs.required'              => 'Minimal 1 referensi harus diisi.',
        'refs.*.type.required_with'  => 'Tipe referensi wajib diisi jika ada teks referensi.',
        'refs.*.text.required_with'  => 'Teks referensi tidak boleh kosong.',
    ]);

    $refsInput = $data['refs'] ?? [];

    // Ambil referensi existing, urut berdasar order_no
    $existing = $rps->references()
        ->orderBy('order_no')
        ->get()
        ->values(); // reindex 0,1,2,...

    $usedIds = [];
    $order   = 1;

    foreach ($refsInput as $idx => $ref) {
        $type = trim($ref['type'] ?? '');
        $text = trim($ref['text'] ?? '');
        $url  = trim($ref['url']  ?? '');

        // kalau semua kosong, skip
        if ($type === '' && $text === '' && $url === '') {
            continue;
        }

        // Kalau ada row existing di posisi ini → update saja (ID tetap)
        $model = $existing[$order - 1] ?? null;

        if ($model) {
            $model->type     = $type ?: 'utama';
            $model->title    = $text;
            $model->url      = $url ?: null;
            $model->order_no = $order;
            $model->save();
        } else {
            // Kalau belum ada → buat baru
            $model = $rps->references()->create([
                'type'     => $type ?: 'utama',
                'title'    => $text,
                'url'      => $url ?: null,
                'order_no' => $order,
            ]);
        }

        $usedIds[] = $model->id;
        $order++;
    }

    // Hapus referensi yang sudah tidak dipakai lagi (tidak ada di input baru)
    if (count($usedIds) > 0) {
        $rps->references()
            ->whereNotIn('id', $usedIds)
            ->delete();
    } else {
        // Kalau user benar-benar mengosongkan semua, boleh hapus semua
        $rps->references()->delete();
    }
    
    if ($request->boolean('exit_to_index')) {
    return redirect()
        ->route('rps.index')
        ->with('success','Pesan sukses sesuai step.');
}
    return redirect()->route('rps.create.step', 5)
        ->with('success','Referensi berhasil disimpan. Lanjut ke Step 5 (Rencana Pembelajaran Mingguan).');
}

        /* ---------- STEP 5 ---------- */
        /* ---------- STEP 5 (Rencana Penilaian) ---------- 
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
*/
/* ---------- STEP 5 (STORE: RENCANA PEMBELAJARAN MINGGUAN) ---------- */
// ---------- STEP 5 (STORE) ----------
if ($step === 5) {
    $rpsId = $request->session()->get('rps_id');
    $rps   = Rps::findOrFail($rpsId);

    // Validasi form
    $data = $request->validate([
        'weeks'                           => ['required','array','min:1'],

        'weeks.*.week_no'                 => ['required','integer','min:1','max:30'],
        'weeks.*.session_no'              => ['nullable','integer','min:1','max:10'],

        'weeks.*.topic'                   => ['required','string'],
        'weeks.*.indicator'               => ['nullable','string'],
        'weeks.*.assessment_technique'    => ['nullable','string','max:100'],
        'weeks.*.assessment_criteria'     => ['nullable','string'],

        // sinkron hasil JS (gabungan aktivitas luring/daring)
        'weeks.*.learning_in'             => ['nullable','string'],
        'weeks.*.learning_online'         => ['nullable','string'],

        'weeks.*.reference_id'            => ['nullable','integer','exists:rps_references,id'],

        // daftar Sub-CPMK per minggu (wajib minimal 1)
        'weeks.*.sub_clos'                => ['required','array','min:1'],
        'weeks.*.sub_clos.*'              => ['required','integer','exists:rps_sub_clos,id'],
    ]);

    // Hapus RPM lama (hanya dari rps_weekly_plans)
    $rps->weeklyPlans()->delete();

    $createdPlans = [];
    $orderNo      = 1;

    // Flatten: 1 minggu bisa punya banyak Sub-CPMK → 1 row per Sub-CPMK
    foreach (array_values($data['weeks']) as $week) {
        $weekNo    = (int)($week['week_no'] ?? 1);
        $sessionNo = isset($week['session_no']) ? (int)$week['session_no'] : null;

        $topic      = $week['topic'] ?? '';
        $indicator  = $week['indicator'] ?? null;
        $tech       = $week['assessment_technique'] ?? null;
        $criteria   = $week['assessment_criteria'] ?? null;

        // hasil sinkron JS (gabungan teks aktivitas per week)
        $learnIn    = $week['learning_in'] ?? null;
        $learnOn    = $week['learning_online'] ?? null;

        $refId      = !empty($week['reference_id']) ? (int)$week['reference_id'] : null;

        $subClosIds = $week['sub_clos'] ?? [];
        foreach ($subClosIds as $subId) {
            $subId = (int)$subId;
            if (!$subId) continue;

            $plan = $rps->weeklyPlans()->create([
                'week_no'              => $weekNo,
                'session_no'           => $sessionNo,
                'sub_clo_id'           => $subId,

                'topic'                => $topic,
                'indicator'            => $indicator,
                'assessment_technique' => $tech,
                'assessment_criteria'  => $criteria,
                'learning_in'          => $learnIn,
                'learning_online'      => $learnOn,

                'reference_id'         => $refId,
                'weight_percent'       => 0, // dihitung ulang di bawah
                'order_no'             => $orderNo++,
            ]);

            $createdPlans[] = $plan;
        }
    }

    // Hitung bobot otomatis per Sub-CPMK → sebar ke semua minggu yg pakai
    if (!empty($createdPlans)) {
        $bySub = [];
        foreach ($createdPlans as $plan) {
            if (!$plan->sub_clo_id) continue;
            $bySub[$plan->sub_clo_id][] = $plan;
        }

        if (!empty($bySub)) {
            $subIds = array_keys($bySub);

            $subWeights = \App\Models\RpsSubClo::whereIn('id', $subIds)
                ->pluck('weight_percent', 'id');

            foreach ($bySub as $subId => $plans) {
                $global = (float) ($subWeights[$subId] ?? 0);
                if ($global <= 0) continue;

                $count  = count($plans);
                $perRow = $global / $count;

                foreach ($plans as $plan) {
                    $plan->weight_percent = $perRow;
                    $plan->save();
                }
            }
        }
    }

    if ($request->boolean('exit_to_index')) {
        return redirect()
            ->route('rps.index')
            ->with('success','Rencana Pembelajaran Mingguan berhasil disimpan.');
    }

    return redirect()
        ->route('rps.create.step', 6)
        ->with('success','Rencana Pembelajaran Mingguan Berhasil Disimpan. Lanjut ke Step 6 Kontrak.');
}




// ---------- STEP 6 (STORE) ----------
// ---------- STEP 6 (STORE: KONTRAK) ----------
if ($step === 6) {

    $rps = Rps::findOrFail($request->session()->get('rps_id'));

    $data = $request->validate([
        'class_policy'   => ['nullable','string'],
        'contract_text'  => ['required','string'],
    ], [
        'contract_text.required' => 'Kontrak perkuliahan wajib diisi.',
    ]);

    $contract = $rps->contract ?: new \App\Models\RpsContract([
        'rps_id' => $rps->id,
    ]);

    $contract->class_policy  = $data['class_policy']   ?? null;
    $contract->contract_text = $data['contract_text']; // sudah required
    $contract->save();

    // Step 6 adalah step terakhir → update status RPS
if ($rps->status === 'need_revision') {
    // dosen baru saja submit revisi
    $rps->status = 'revision_submitted';
} elseif (in_array($rps->status, ['draft', 'revision_submitted', null])) {
    // submit pertama kali
    $rps->status = 'submitted';
}

$rps->save();



    return redirect()
        ->route('rps.index')
        ->with('success', 'Kontrak perkuliahan tersimpan.');
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

public function resumeAuto(Request $request, Rps $rps)
{
    // Simpan rps_id ke session (supaya createStep / storeStep tahu RPS mana)
    $request->session()->put('rps_id', $rps->id);

    // Default: mulai dari Step 1
    $step = 1;

    // Kalau sudah punya CPL/CPMK → minimal Step 2
    if ($rps->plos()->exists()) {
        $step = 2;
    }

    // Kalau sudah punya matriks assessment → minimal Step 3
    if ($rps->assessments()->exists()) {
        $step = 3;
    }

    // Kalau sudah punya referensi → minimal Step 4
    if ($rps->references()->exists()) {
        $step = 4;
    }

    // Kalau sudah punya RPM (weekly plans) → minimal Step 5
    if ($rps->weeklyPlans()->exists()) {
        $step = 5;
    }

    // Kalau sudah punya kontrak → Step 6 (final)
    if ($rps->contract()->exists()) {
        $step = 6;
    }

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

/**
 * Parse teks learning_in / learning_online menjadi array aktivitas
 * Format baris yang dikenali: [KM|PB|PT] durasi — deskripsi
 * Contoh: "[PB] 2×50' — Diskusi kasus"
 */
private function parseWeeklyActivities(?string $text): array
{
    if (!$text) {
        return [];
    }

    $lines = preg_split("/\r\n|\n|\r/", $text);
    $result = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        $type = '';
        $duration = '';
        $desc = $line;

        // Ambil kode di awal kalau formatnya [KM] / [PB] / [PT]
        if (preg_match('/^\[(KM|PB|PT)\]\s*/', $desc, $m)) {
            $type = $m[1]; // KM / PB / PT
            $desc = trim(substr($desc, strlen($m[0])));
        }

        // Pisahkan durasi dan deskripsi pakai "—" pertama
        if (strpos($desc, '—') !== false) {
            [$durationPart, $rest] = explode('—', $desc, 2);
            $duration = trim($durationPart);
            $desc     = trim($rest);
        }

        $result[] = [
            'type'        => $type,
            'duration'    => $duration,
            'description' => $desc,
        ];
    }

    return $result;
}

}
