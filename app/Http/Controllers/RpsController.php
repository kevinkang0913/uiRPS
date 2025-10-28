<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Rps;
use App\Models\RpsPlo;
use App\Models\RpsOutcome;        // uses table rps_outcomes
use App\Models\RpsSubClo;         // uses table rps_sub_clos (FK: outcome_id)
use App\Models\RpsAssessment;
use App\Models\RpsLearningMaterial;
use App\Models\RpsPlanner;
use App\Models\RpsContract;

use App\Models\Faculty;

class RpsController extends Controller
{
    /**
     * List RPS
     */
    public function index()
    {
        $rps = Rps::with(['lecturer','classSection.course.program.faculty'])
            ->latest()
            ->get();

        return view('rps.index', compact('rps'));
    }

    /**
     * Redirect to step 1
     */
    public function create()
    {
        return redirect()->route('rps.create.step', ['step' => 1]);
    }

    /**
     * Render per step (with guard + progress info)
     */
    public function createStep($step)
    {
        $step = (int) $step;
        $data = session('rps_data', []);

        // hitung max step yang sudah terisi
        $maxStep = 0;
        foreach ($data as $k => $v) {
            if (preg_match('/step(\d+)/', $k, $m)) {
                $maxStep = max($maxStep, (int) $m[1]);
            }
        }

        // guard: tidak boleh lompat ke depan
        for ($i = 1; $i < $step; $i++) {
            if (!isset($data["step{$i}"])) {
                return redirect()->route('rps.create.step', ['step' => $i])
                    ->with('error', "Isi dulu Step {$i} sebelum lanjut ke Step {$step}");
            }
        }

        // data tambahan untuk step 1 (dropdown bertingkat)
        $faculties = ($step === 1)
            ? Faculty::with('programs.courses.classSections')->get()
            : collect();

        return view("rps.partials.step{$step}", [
            'data'        => $data,
            'faculties'   => $faculties,
            'currentStep' => $step,
            'maxStep'     => $maxStep,
        ]);
    }

    /**
     * Store per step to session; on final step, persist to DB
     */
    public function storeStep(Request $request, $step)
    {
        $step = (int) $step;

        // ---------- Validation per step ----------
        if ($step === 1) {
            $request->validate([
                'course_title'     => ['required','string','max:255'],
                'class_section_id' => ['required','exists:class_sections,id'],
                'description'      => ['nullable','string'],
            ]);
        }

        if ($step === 3) {
            // minimal 1 PLO -> 1 CLO -> 1 SubCLO
            $request->validate([
                'plos'                               => ['required','array','min:1'],
                'plos.*.description'                 => ['required','string'],
                'plos.*.clos'                        => ['required','array','min:1'],
                'plos.*.clos.*.description'          => ['required','string'],
                'plos.*.clos.*.subclos'              => ['required','array','min:1'],
                'plos.*.clos.*.subclos.*'            => ['required','string'],
            ]);
        }

        if ($step === 4) {
            // tiap assessment wajib pilih sub_key (pi.ci.si), type, weight
            $request->validate([
                'assessments'            => ['required','array','min:1'],
                'assessments.*.sub_key'  => ['required','string'],
                'assessments.*.type'     => ['required','string'],
                'assessments.*.weight'   => ['required','integer','min:1'],
            ]);
        }

        if ($step === 5) {
            // optional stricter check:
            $request->validate([
                'planner'                   => ['required','array','min:1'],
                'planner.*.week'            => ['required','integer','min:1','max:16'],
                'planner.*.topic'           => ['required','string'],
                'planner.*.method'          => ['required','string'],
                'planner.*.assessment'      => ['nullable','string'],
                'planner.*.learning_material_id' => ['nullable','integer'],
            ]);
        }

        if ($step === 6) {
            $request->validate([
                'attendance_policy'    => ['required','string'],
                'participation_policy' => ['nullable','string'],
                'late_policy'          => ['nullable','string'],
                'grading_policy'       => ['required','string'],
                'extra_rules'          => ['nullable','string'],
            ]);
        }

        // ---------- Save current step to session ----------
        $data = session('rps_data', []);
        $data["step{$step}"] = $request->all();
        session(['rps_data' => $data]);

        // ---------- If not last step, go next ----------
        if ($step < 6) {
            return redirect()->route('rps.create.step', ['step' => $step + 1]);
        }

        // ---------- Final save to DB (Step 6 submit) ----------
        $finalData = session('rps_data', []);
        if (!is_array($finalData)) {
            return redirect()->route('rps.create.step', ['step' => 1])
                ->with('error', 'Invalid RPS data format.');
        }

        DB::beginTransaction();
        try {
            // 1) Header RPS
            $rps = Rps::create([
                'lecturer_id'      => auth()->id(),
                'class_section_id' => $finalData['step1']['class_section_id'] ?? null,
                'title'            => $finalData['step1']['course_title'] ?? 'RPS Baru',
                'description'      => $finalData['step1']['description'] ?? null,
                'status'           => 'submitted',
            ]);

            // 2) Learning Materials (Step 2)
            if (!empty($finalData['step2']['materials']) && is_array($finalData['step2']['materials'])) {
                foreach ($finalData['step2']['materials'] as $mat) {
                    if (empty($mat['title'])) continue;
                    $rps->learningMaterials()->create([
                        'title'     => $mat['title'] ?? '',
                        'author'    => $mat['author'] ?? null,
                        'publisher' => $mat['publisher'] ?? null,
                        'year'      => $mat['year'] ?? null,
                        'notes'     => $mat['notes'] ?? null,
                    ]);
                }
            }

            // 3) PLO → CLO (rps_outcomes) → SubCLO (rps_sub_clos)
            //    plus build map: "pi.ci.si" => sub_clo_id
            $subKeyToId = [];

            if (!empty($finalData['step3']['plos']) && is_array($finalData['step3']['plos'])) {
                foreach ($finalData['step3']['plos'] as $pi => $ploArr) {
                    if (!is_array($ploArr)) continue;

                    $ploDesc = trim($ploArr['description'] ?? '');
                    if ($ploDesc === '') continue;

                    $ploModel = $rps->plos()->create(['description' => $ploDesc]);

                    $closArr = $ploArr['clos'] ?? [];
                    if (!is_array($closArr)) continue;

                    foreach ($closArr as $ci => $cloArr) {
                        if (!is_array($cloArr)) continue;

                        $cloDesc = trim($cloArr['description'] ?? '');
                        if ($cloDesc === '') continue;

                        // -> rps_outcomes
                        $cloModel = $ploModel->clos()->create([
                            'rps_id' => $rps->id,
                            'clo'    => $cloDesc,
                        ]);

                        $subs = $cloArr['subclos'] ?? [];
                        if (!is_array($subs)) continue;

                        foreach ($subs as $si => $subText) {
                            $subText = is_string($subText) ? trim($subText) : '';
                            if ($subText === '') continue;

                            // -> rps_sub_clos (FK: outcome_id)
                            $sub = $cloModel->subClos()->create(['description' => $subText]);

                            $key = "{$pi}.{$ci}.{$si}";
                            $subKeyToId[$key] = $sub->id;
                        }
                    }
                }
            }

            // 4) Assessments (Step 4) — map sub_key to sub_clo_id
            if (!empty($finalData['step4']['assessments']) && is_array($finalData['step4']['assessments'])) {
                foreach ($finalData['step4']['assessments'] as $a) {
                    $subKey = $a['sub_key'] ?? '';
                    $type   = $a['type'] ?? '';
                    $weight = (int)($a['weight'] ?? 0);

                    if ($subKey === '' || !isset($subKeyToId[$subKey])) continue;
                    if ($type === '' || $weight <= 0) continue;

                    $rps->assessments()->create([
                        'sub_clo_id' => $subKeyToId[$subKey],
                        'type'       => $type,
                        'weight'     => $weight,
                    ]);
                }
            }

            // 5) Planner (Step 5)
            if (!empty($finalData['step5']['planner']) && is_array($finalData['step5']['planner'])) {
                foreach ($finalData['step5']['planner'] as $plan) {
                    $rps->planners()->create([
                        'week'                 => $plan['week'] ?? null,
                        'topic'                => $plan['topic'] ?? null,
                        'method'               => $plan['method'] ?? null,
                        'assessment'           => $plan['assessment'] ?? null,
                        'learning_material_id' => $plan['learning_material_id'] ?? null,
                    ]);
                }
            }

            // 6) Contract (Step 6)
            if (!empty($finalData['step6'])) {
                $rps->contract()->create([
                    'attendance_policy'    => $finalData['step6']['attendance_policy'] ?? null,
                    'participation_policy' => $finalData['step6']['participation_policy'] ?? null,
                    'late_policy'          => $finalData['step6']['late_policy'] ?? null,
                    'grading_policy'       => $finalData['step6']['grading_policy'] ?? null,
                    'extra_rules'          => $finalData['step6']['extra_rules'] ?? null,
                ]);
            }

            DB::commit();

            // bersihkan session dan redirect
            session()->forget('rps_data');

            return redirect()->route('rps.index')->with('success', 'RPS berhasil disimpan ke database detail');

        } catch (\Throwable $e) {
            DB::rollBack();
            // simpan error untuk debug cepat (opsional: logger)
            report($e);

            return redirect()->route('rps.create.step', ['step' => 6])
                ->with('error', 'Gagal menyimpan RPS: '.$e->getMessage());
        }
    }
public function edit(\App\Models\Rps $rps)
{
    // (opsional) batasi siapa yang boleh edit
    if ($rps->lecturer_id !== auth()->id() && !optional(auth()->user())->hasRole('admin')) {
        return redirect()->route('rps.index')->with('error','Anda tidak berhak mengedit RPS ini.');
    }

    // muat relasi yang diperlukan untuk memetakan kembali ke struktur wizard
    $rps->load([
        'classSection.course.program.faculty',
        'learningMaterials',
        'plos.clos.subClos',   // PLO -> Outcome(CLO) -> SubCLO
        'assessments.subClo',
        'planners',
        'contract',
    ]);

    // isi ulang session wizard dari data DB
    $sessionData = $this->mapRpsToWizardSession($rps);
    session(['rps_data' => $sessionData]);

    // kirim user ke Step 1 (atau kamu bisa ganti ke step terakhir yang sudah terisi)
    return redirect()->route('rps.create.step', ['step' => 1])
        ->with('info', 'Data RPS dimuat untuk diedit.');
}

/**
 * Ubah data RPS (relasi DB) menjadi struktur session wizard:
 * step1..step6 agar form terisi otomatis.
 */
private function mapRpsToWizardSession(\App\Models\Rps $rps): array
{
    // ---------- STEP 1 ----------
    $step1 = [
        'course_title'     => $rps->title,
        'class_section_id' => $rps->class_section_id,
        'description'      => $rps->description,
    ];

    // ---------- STEP 2 ----------
    $materials = [];
    foreach ($rps->learningMaterials as $m) {
        $materials[] = [
            'title'     => $m->title,
            'author'    => $m->author,
            'publisher' => $m->publisher,
            'year'      => $m->year,
            'notes'     => $m->notes,
            'id'        => $m->id, // berguna bila ingin refer ke planner
        ];
    }
    $step2 = ['materials' => $materials];

    // ---------- STEP 3 (PLO -> CLO -> SubCLO) ----------
    $plos = [];
    // peta untuk membangun sub_key “pi.ci.si” per SubCLO ID
    $subCloIdToKey = [];

    foreach ($rps->plos as $pi => $plo) {
        $closArr = [];
        foreach ($plo->clos as $ci => $clo) {
            $subsArr = [];
            foreach ($clo->subClos as $si => $sub) {
                $subsArr[] = $sub->description;
                // simpan key untuk Step 4
                $subCloIdToKey[$sub->id] = "{$pi}.{$ci}.{$si}";
            }
            $closArr[] = [
                'description' => $clo->clo,
                'subclos'     => $subsArr,
            ];
        }
        $plos[] = [
            'description' => $plo->description,
            'clos'        => $closArr,
        ];
    }
    $step3 = ['plos' => $plos];

    // ---------- STEP 4 (Assessments) ----------
    $assessments = [];
    foreach ($rps->assessments as $a) {
        $key = $subCloIdToKey[$a->sub_clo_id] ?? null; // map ke “pi.ci.si”
        $assessments[] = [
            'sub_key' => $key,         // ini yang dipakai form
            'type'    => $a->type,
            'weight'  => $a->weight,
        ];
    }
    if (empty($assessments)) { $assessments = [['sub_key'=>'','type'=>'Quiz','weight'=>'']]; }
    $step4 = ['assessments' => $assessments];

    // ---------- STEP 5 (Planner) ----------
    $planner = [];
    foreach ($rps->planners as $p) {
        $planner[] = [
            'week'                 => $p->week,
            'topic'                => $p->topic,
            'method'               => $p->method,
            'assessment'           => $p->assessment,
            'learning_material_id' => $p->learning_material_id,
        ];
    }
    $step5 = ['planner' => $planner];

    // ---------- STEP 6 (Contract) ----------
    $contract = $rps->contract;
    $step6 = [
        'attendance_policy'    => $contract->attendance_policy ?? null,
        'participation_policy' => $contract->participation_policy ?? null,
        'late_policy'          => $contract->late_policy ?? null,
        'grading_policy'       => $contract->grading_policy ?? null,
        'extra_rules'          => $contract->extra_rules ?? null,
    ];

    return [
        'step1' => $step1,
        'step2' => $step2,
        'step3' => $step3,
        'step4' => $step4,
        'step5' => $step5,
        'step6' => $step6,
    ];
}

    /**
     * Show one RPS with relations
     */
    public function show(\App\Models\Rps $rps)
{
    $rps->load([
        'lecturer',
        'classSection.course.program.faculty',
        'learningMaterials',
        'plos.clos.subClos',     // PLO -> CLO (Outcome) -> SubCLO
        'assessments.subClo',    // Assessment -> SubCLO
        'planners.material',     // Planner -> LearningMaterial
        'contract',
    ]);
    return view('rps.show', compact('rps'));
}

}
