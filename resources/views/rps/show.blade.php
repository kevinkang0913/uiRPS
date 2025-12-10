{{-- resources/views/rps/show.blade.php --}}
@extends(request()->boolean('embed') ? 'layouts.embed' : 'layouts.app')

@section('content')
@php
    $embed = request()->boolean('embed');
@endphp

<div class="{{ $embed ? 'container-md' : 'container-xxl' }}">

  {{-- HEADER --}}
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h{{ $embed ? 5 : 4 }} class="mb-0">Detail RPS</h{{ $embed ? 5 : 4 }}>
      <div class="text-muted small">
        RPS #{{ $rps->id }} —
        {{ $rps->course->code ?? '' }} {{ $rps->course->name ?? '' }}
      </div>
    </div>

    @unless($embed)
      <div class="d-flex gap-2">
        <a href="{{ route('rps.index') }}" class="btn btn-outline-secondary btn-sm">
          ← Kembali ke Daftar
        </a>
        <button type="button" class="btn btn-sm btn-outline-primary"
                onclick="window.print()">
          Cetak / Simpan PDF
        </button>
      </div>
    @endunless
  </div>

  {{-- INFO MATA KULIAH (STEP 1) --}}
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white">
      <h5 class="mb-0">Identitas Mata Kuliah</h5>
    </div>
    <div class="card-body">
      <div class="row small gy-2">
        <div class="col-md-6">
          <div><strong>Program Studi:</strong>
            {{ $rps->course->program->name ?? '-' }}
          </div>
          <div><strong>Fakultas:</strong>
            {{ $rps->course->program->faculty->name ?? '-' }}
          </div>
          <div><strong>Kode / Nama MK:</strong>
            {{ $rps->course->code ?? '-' }} — {{ $rps->course->name ?? '-' }}
          </div>
          <div><strong>Kategori MK:</strong>
            {{ $rps->course_category ?? '-' }}
          </div>
          <div><strong>Jenis Aktivitas Pembelajaran:</strong>
            {{ $rps->learning_activity_type ?? '-' }}
          </div>
        </div>
        <div class="col-md-6">
          <div><strong>Tahun Akademik:</strong> {{ $rps->academic_year ?? '-' }}</div>
          <div><strong>Semester:</strong> {{ $rps->semester ?? '-' }}</div>
          <div><strong>SKS:</strong> {{ $rps->sks ?? '-' }}</div>
          <div><strong>Mode Pembelajaran:</strong> {{ $rps->delivery_mode ?? '-' }}</div>
          <div><strong>Bahasa Pengantar:</strong> {{ $rps->language ?? '-' }}</div>
        </div>
      </div>

      @if(
        !empty($rps->short_description) ||
        !empty($rps->study_materials) ||
        !empty($rps->prerequisite_courses) ||
        !empty($rps->prerequisite_for_courses)
      )
        <hr class="my-3">
      @endif

      @if(!empty($rps->short_description))
        <div class="small mb-3">
          <strong>Deskripsi Singkat Mata Kuliah</strong>
          <div class="mt-1">{!! nl2br(e($rps->short_description)) !!}</div>
        </div>
      @endif

      @if(!empty($rps->study_materials))
        <div class="small mb-3">
          <strong>Bahan Kajian / Materi Pokok</strong>
          <div class="mt-1">{!! nl2br(e($rps->study_materials)) !!}</div>
        </div>
      @endif

      <div class="row small gy-2">
        @if(!empty($rps->prerequisite_courses))
          <div class="col-md-6">
            <strong>Mata Kuliah Pra-syarat</strong>
            <div class="mt-1">{!! nl2br(e($rps->prerequisite_courses)) !!}</div>
          </div>
        @endif

        @if(!empty($rps->prerequisite_for_courses))
          <div class="col-md-6">
            <strong>Menjadi Pra-syarat untuk MK</strong>
            <div class="mt-1">{!! nl2br(e($rps->prerequisite_for_courses)) !!}</div>
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- CPL → CPMK → sub-CPMK (STEP 2) --}}
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white">
      <h5 class="mb-0">CPL (PLO) — CPMK (CLO) — sub-CPMK</h5>
    </div>
    <div class="card-body">
      @php
        $plos = $rps->plos()->with(['outcomes.subClos' => function($q){
          $q->orderBy('no');
        }])->orderBy('order_no')->get();
      @endphp

      @forelse($plos as $plo)
        <div class="mb-3 p-3 border rounded-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <div>
              <strong>{{ $plo->code }}</strong> — {{ $plo->description }}
            </div>
            @if(!is_null($plo->weight_percent))
              <span class="badge bg-primary-subtle text-primary">
                CPL Weight: {{ number_format($plo->weight_percent, 2) }}%
              </span>
            @endif
          </div>

          {{-- CPMK & sub --}}
          <div class="table-responsive mt-2">
            <table class="table table-sm table-bordered mb-0 align-middle">
              <thead class="table-light small">
                <tr>
                  <th style="width:80px;">CPMK</th>
                  <th>Deskripsi CPMK</th>
                  <th style="width:120px;" class="text-end">Bobot CPMK (%)</th>
                  <th style="width:260px;">Sub-CPMK</th>
                </tr>
              </thead>
              <tbody class="small">
                @foreach($plo->outcomes->sortBy('no') as $clo)
                  <tr>
                    <td>CPMK {{ $clo->no }}</td>
                    <td>{{ $clo->description }}</td>
                    <td class="text-end">
                      {{ !is_null($clo->weight_percent)
                          ? number_format($clo->weight_percent, 2)
                          : '-' }}
                    </td>
                    <td>
                      @if($clo->subClos->isEmpty())
                        <span class="text-muted">—</span>
                      @else
                        <ul class="mb-0 ps-3">
                          @foreach($clo->subClos->sortBy('no') as $sub)
                            <li>
                              <strong>{{ $clo->no }}.{{ $sub->no }}</strong> —
                              {{ $sub->description }}
                              @if(!is_null($sub->weight_percent))
                                <span class="text-muted">
                                  ({{ number_format($sub->weight_percent, 2) }}%)
                                </span>
                              @endif
                            </li>
                          @endforeach
                        </ul>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @empty
        <p class="text-muted small mb-0">
          Belum ada CPL/CPMK yang diisi di Step 2.
        </p>
      @endforelse
    </div>
  </div>

  {{-- MATRiks CPMK / sub-CPMK × Kategori Assessment (STEP 3 - GLOBAL DISPLAY) --}}
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white">
      <h5 class="mb-0">Matriks CPMK / sub-CPMK × Kategori Assessment</h5>
      <div class="text-muted small">
        Angka pada tabel ini adalah <strong>bobot global sesungguhnya</strong> terhadap nilai akhir.
        Perhitungan berasal dari Step 2 (CPL → CPMK → sub-CPMK × kategori).
      </div>
    </div>

    <div class="card-body small">

      @php
        $cats = \App\Models\RpsAssessmentCategory::orderBy('order_no')->get();

        $clos = $rps->outcomesFlat()
          ->select('rps_outcomes.id','rps_outcomes.no','rps_outcomes.description','rps_outcomes.weight_percent')
          ->orderBy('rps_outcomes.no')
          ->get();

        $subsByOutcome = \App\Models\RpsSubClo::where('rps_id',$rps->id)
          ->orderBy('outcome_id')->orderBy('no')
          ->get()
          ->groupBy('outcome_id');

        // nilai lokal (0—100) kategori × CPMK
        $map = \App\Models\RpsAssessmentMapping::where('rps_id',$rps->id)->get();
        $local = [];
        foreach ($map as $m) {
          $local[$m->assessment_category_id][$m->outcome_id] = $m->percent;
        }

        // Bobot global kategori (harus total 100)
        $assRows   = $rps->assessments()->get()->keyBy('assessment_category_id');
        $catTotals = $assRows->pluck('weight_percent','assessment_category_id');
      @endphp

      <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0">
          <thead class="table-light text-center small">
            <tr>
              <th style="width:190px;">CPMK / Sub-CPMK<br><span class="fw-normal fst-italic">* dalam global %</span></th>

              @foreach($cats as $cat)
                <th>
                  <div class="fw-semibold">{{ $cat->code }}</div>
                  <div class="small text-muted">{{ $cat->name }}</div>
                </th>
              @endforeach

              <th style="width:130px;">TOTAL PER CPMK</th>
            </tr>
          </thead>

          <tbody>
          @foreach($clos as $clo)

            @php
              // prepare local values per category
              $localRow = [];
              foreach ($cats as $cat) {
                $localRow[$cat->id] = $local[$cat->id][$clo->id] ?? 0;
              }
            @endphp

            {{-- CPMK ROW (GLOBAL VALUES) --}}
            <tr class="fw-semibold bg-light">
              <td>CPMK {{ $clo->no }}</td>

              @foreach($cats as $cat)
                @php
                  $localPct   = $localRow[$cat->id]; // 0–100 dalam CPMK
                  $cpmkGlobal = (float)($clo->weight_percent ?? 0); // CPMK global %

                  $globalVal = ($cpmkGlobal * $localPct) / 100;
                @endphp

                <td class="text-center">
                  {{ $globalVal > 0 ? number_format($globalVal, 2) : '' }}
                </td>
              @endforeach

              <td class="text-end">
                {{ number_format($clo->weight_percent, 2) }}%
              </td>
            </tr>

            {{-- SUB-CPMK GLOBAL --}}
            @foreach(($subsByOutcome[$clo->id] ?? collect()) as $sub)

              @php
                // bobot sub-CPMK global
                $subGlobal  = (float)($sub->weight_percent ?? 0);
                $cpmkGlobal = (float)($clo->weight_percent ?? 0);

                // rasio: porsi sub terhadap CPMK
                $ratio = ($cpmkGlobal > 0) ? ($subGlobal / $cpmkGlobal) : 0;
              @endphp

              <tr>
                <td class="ps-4">— Sub CPMK {{ $clo->no }}.{{ $sub->no }}</td>

                @foreach($cats as $cat)
                  @php
                    $localPct  = $localRow[$cat->id];
                    $cpmkValue = ($cpmkGlobal * $localPct) / 100;
                    $subValue  = $ratio > 0 ? $cpmkValue * $ratio : 0;
                  @endphp

                  <td class="text-center">
                    {{ $subValue > 0 ? number_format($subValue, 2) : '' }}
                  </td>
                @endforeach

                <td class="text-end">
                  {{ number_format($subGlobal, 2) }}%
                </td>
              </tr>
            @endforeach

          @endforeach

          {{-- TOTAL PER KATEGORI (GLOBAL) --}}
          <tr class="table-light fw-semibold">
            <td class="text-end">TOTAL BOBOT KATEGORI</td>

            @foreach($cats as $cat)
              <td class="text-center">
                {{ number_format($catTotals[$cat->id] ?? 0, 2) }}%
              </td>
            @endforeach

            <td class="text-end">{{ number_format($catTotals->sum(), 2) }}%</td>
          </tr>

          {{-- Deskripsi --}}
          <tr>
            <td class="fw-semibold">Deskripsi</td>

            @foreach($cats as $cat)
              <td>{!! nl2br(e($assRows[$cat->id]->desc ?? '')) !!}</td>
            @endforeach

            <td></td>
          </tr>

          {{-- Due Date --}}
          <tr>
            <td class="fw-semibold">Due Date</td>

            @foreach($cats as $cat)
              <td>{{ $assRows[$cat->id]->due_week ?? '' }}</td>
            @endforeach

            <td></td>
          </tr>

          </tbody>
        </table>
      </div>

    </div>
  </div>

  {{-- RINGKASAN PENILAIAN (VERSI SEDERHANA) --}}
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white">
      <h5 class="mb-0">Ringkasan Penilaian</h5>
    </div>
    <div class="card-body">
      @php
        $assessments = $rps->assessments()
          ->with('category')
          ->get()
          ->sortBy(fn($row) => $row->category->order_no ?? 999)
          ->values();
      @endphp

      @if($assessments->isEmpty())
        <p class="text-muted small mb-0">
          Belum ada data assessment (Step 3).
        </p>
      @else
        <div class="table-responsive">
          <table class="table table-sm table-bordered align-middle mb-0 small">
            <thead class="table-light">
              <tr>
                <th style="width:80px;">Kode</th>
                <th>Kategori</th>
                <th style="width:130px;" class="text-end">Bobot (%)</th>
                <th style="width:260px;">Deskripsi</th>
                <th style="width:120px;">Due (Minggu)</th>
              </tr>
            </thead>
            <tbody>
              @foreach($assessments as $row)
                <tr>
                  <td>{{ $row->category->code ?? '-' }}</td>
                  <td>{{ $row->category->name ?? '-' }}</td>
                  <td class="text-end">
                    {{ number_format($row->weight_percent ?? 0, 2) }}%
                  </td>
                  <td>{!! nl2br(e($row->desc)) !!}</td>
                  <td>{{ $row->due_week ?? '-' }}</td>
                </tr>
              @endforeach
            </tbody>
            <tfoot class="table-light">
              <tr>
                <th colspan="2" class="text-end">TOTAL</th>
                <th class="text-end">
                  {{ number_format($assessments->sum('weight_percent'), 2) }}%
                </th>
                <th colspan="2"></th>
              </tr>
            </tfoot>
          </table>
        </div>
      @endif
    </div>
  </div>

  {{-- REFERENSI (STEP 4) --}}
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Daftar Referensi</h5>
    </div>
    <div class="card-body small">
      @php
        $refs = $rps->references()
          ->orderBy('type')
          ->orderBy('order_no')
          ->get();
      @endphp

      @if($refs->isEmpty())
        <p class="text-muted mb-0">Belum ada referensi (Step 4).</p>
      @else
        <ol class="mb-0 ps-3">
          @foreach($refs as $ref)
            <li class="mb-1">
              <span class="badge rounded-pill
                {{ $ref->type === 'utama' ? 'bg-primary-subtle text-primary' :
                   ($ref->type === 'pendukung' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary') }}">
                {{ ucfirst($ref->type) }}
              </span>
              <span class="ms-1">
                {!! nl2br(e($ref->title)) !!}
              </span>
              @if($ref->url)
                <div>
                  <a href="{{ $ref->url }}" target="_blank" class="small">
                    {{ $ref->url }}
                  </a>
                </div>
              @endif
            </li>
          @endforeach
        </ol>
      @endif
    </div>
  </div>

  {{-- RPM (Rencana Pembelajaran Mingguan - STEP 5) --}}
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white">
      <h5 class="mb-0">Rencana Pembelajaran Mingguan (RPM)</h5>
    </div>
    <div class="card-body small">
      @php
        $plans = $rps->weeklyPlans()
          ->with(['subClo.outcome','reference'])
          ->orderBy('week_no')->orderBy('order_no')
          ->get();

        $weeks = $plans->groupBy('week_no');
      @endphp

      @if($weeks->isEmpty())
        <p class="text-muted mb-0">Belum ada RPM (Step 5).</p>
      @else
        @foreach($weeks as $weekNo => $rows)
          @php $first = $rows->first(); @endphp
          <div class="mb-3 border rounded-3 p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div>
                <strong>Minggu {{ $weekNo }}</strong>
                @if($first->session_no)
                  <span class="text-muted"> • Sesi {{ $first->session_no }}</span>
                @endif
              </div>
              <span class="badge bg-primary-subtle text-primary">
                Total Bobot: {{ number_format($rows->sum('weight_percent'), 2) }}%
              </span>
            </div>

            <div class="mb-2">
              <strong>Topik / Kemampuan Akhir:</strong>
              <div>{!! nl2br(e($first->topic)) !!}</div>
            </div>

            @if($first->indicator || $first->assessment_technique || $first->assessment_criteria)
              <div class="row gy-2 mb-2">
                <div class="col-md-4">
                  <strong>Indikator Penilaian:</strong>
                  <div>{!! nl2br(e($first->indicator)) !!}</div>
                </div>
                <div class="col-md-3">
                  <strong>Teknik Penilaian:</strong>
                  <div>{{ $first->assessment_technique ?: '-' }}</div>
                </div>
                <div class="col-md-5">
                  <strong>Kriteria Penilaian:</strong>
                  <div>{!! nl2br(e($first->assessment_criteria)) !!}</div>
                </div>
              </div>
            @endif

            <div class="row gy-2 mb-2">
              <div class="col-md-6">
                <strong>Aktivitas Luring:</strong>
                <div class="mt-1">
                  {!! $first->learning_in
                      ? nl2br(e($first->learning_in))
                      : '<span class="text-muted">—</span>' !!}
                </div>
              </div>
              <div class="col-md-6">
                <strong>Aktivitas Daring:</strong>
                <div class="mt-1">
                  {!! $first->learning_online
                      ? nl2br(e($first->learning_online))
                      : '<span class="text-muted">—</span>' !!}
                </div>
              </div>
            </div>

            <div class="row gy-2">
              <div class="col-md-6">
                <strong>Sub-CPMK:</strong>
                @php
                  $subs = $rows->pluck('subClo')->filter();
                @endphp
                @if($subs->isEmpty())
                  <div class="text-muted">—</div>
                @else
                  <ul class="mb-1 ps-3">
                    @foreach($subs as $sub)
                      <li>
                        <strong>Sub CPMK {{ $sub->outcome->no ?? '?' }}.{{ $sub->no }}</strong>
                        — {{ $sub->description }}
                        @if(!is_null($sub->weight_percent))
                          <span class="text-muted">
                            (Bobot global {{ number_format($sub->weight_percent,2) }}%)
                          </span>
                        @endif
                      </li>
                    @endforeach
                  </ul>
                @endif
              </div>
              <div class="col-md-6">
                <strong>Referensi:</strong>
                @if($first->reference)
                  <div class="mt-1">
                    <span class="badge rounded-pill
                      {{ $first->reference->type === 'utama' ? 'bg-primary-subtle text-primary' :
                         ($first->reference->type === 'pendukung' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary') }}">
                      {{ ucfirst($first->reference->type) }}
                    </span>
                    <span class="ms-1">
                      {!! nl2br(e(\Illuminate\Support\Str::limit($first->reference->title, 200))) !!}
                    </span>
                  </div>
                @else
                  <div class="text-muted">—</div>
                @endif
              </div>
            </div>
          </div>
        @endforeach
      @endif
    </div>
  </div>

  {{-- KONTRAK PERKULIAHAN (STEP 6) --}}
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
      <h5 class="mb-0">Kontrak Perkuliahan</h5>
    </div>
    <div class="card-body small">
      @php $contract = $rps->contract; @endphp

      <div class="mb-3">
        <strong>Peraturan Kelas</strong>
        <div class="mt-1">
          {!! $contract && $contract->class_policy
              ? nl2br(e($contract->class_policy))
              : '<span class="text-muted">Belum diisi.</span>' !!}
        </div>
      </div>

      <div>
        <strong>Kontrak Perkuliahan</strong>
        <div class="mt-1">
          {!! $contract && $contract->contract_text
              ? nl2br(e($contract->contract_text))
              : '<span class="text-muted">Belum diisi.</span>' !!}
        </div>
      </div>
    </div>
  </div>

</div>

{{-- Styling untuk print --}}
<style>
  @media print {
    .navbar, .btn, .alert, .nav-tabs {
      display: none !important;
    }
    body {
      background: #fff !important;
    }
    .card {
      box-shadow: none !important;
      border: 1px solid #ddd !important;
      page-break-inside: avoid;
    }
  }
</style>
@endsection
