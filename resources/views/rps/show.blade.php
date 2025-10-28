@extends('layouts.app')

@section('content')
@php
  $cs   = $rps->classSection;
  $crs  = $cs->course ?? null;
  $prog = $crs->program ?? null;
  $fac  = $prog->faculty ?? null;
@endphp

<style>
  .card-sec{border:1px solid #e5e7eb;border-radius:.5rem;overflow:hidden;background:#fff}
  .card-sec .hdr{background:#1366e2;color:#fff;font-weight:600;padding:.6rem .9rem}
  .card-sec .bd{padding:1rem}
  .kv{display:grid;grid-template-columns:220px 1fr; row-gap:.35rem}
  .tbl{width:100%;border-collapse:collapse}
  .tbl th,.tbl td{border:1px solid #e5e7eb;padding:.55rem;vertical-align:top}
  .badge{padding:.2rem .5rem;border-radius:.5rem;font-size:.8rem}
  .badge-submitted{background:#e6f7ff;color:#096dd9}
  .badge-forwarded{background:#f0f0f0;color:#595959}
  .badge-approved{background:#f6ffed;color:#389e0d}
  .badge-rejected{background:#fff1f0;color:#cf1322}
  .muted{color:#8c8c8c}
</style>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Detail RPS: {{ $rps->title }}</h2>
    <span class="badge
      {{ $rps->status==='approved' ? 'badge-approved' :
         ($rps->status==='forwarded' ? 'badge-forwarded' :
         ($rps->status==='rejected' ? 'badge-rejected' : 'badge-submitted')) }}">
      {{ ucfirst($rps->status) }}
    </span>
  </div>

  {{-- Step 1: Title / Identitas --}}
  <div class="card-sec mb-3">
    <div class="hdr">Step 1: Title</div>
    <div class="bd">
      <div class="kv">
        <div>Faculty:</div>        <div>{{ $fac->name ?? '-' }}</div>
        <div>Program:</div>        <div>{{ $prog->name ?? '-' }}</div>
        <div>Course Title:</div>   <div>{{ $crs->title ?? '-' }}</div>
        <div>Course Code:</div>    <div>{{ $crs->code  ?? '-' }}</div>
        <div>Credits (SKS):</div>  <div>{{ $crs->credits ?? '-' }}</div>
        <div>Semester:</div>       <div>{{ $cs->semester ?? '-' }}</div>
        <div>Section:</div>        <div>{{ $cs->name ?? '-' }}</div>
        <div>Lecturer:</div>       <div>{{ $rps->lecturer->name ?? '-' }}</div>
        <div>Description:</div>    <div>{{ $rps->description ?? '-' }}</div>
      </div>
    </div>
  </div>

  {{-- Step 2: Learning Materials --}}
  <div class="card-sec mb-3">
    <div class="hdr">Step 2: Learning Materials</div>
    <div class="bd">
      @if($rps->learningMaterials->count())
        <table class="tbl">
          <thead>
            <tr>
              <th style="width:44px">#</th>
              <th>Title</th>
              <th style="width:18%">Author</th>
              <th style="width:18%">Publisher</th>
              <th style="width:10%">Year</th>
              <th>Notes</th>
            </tr>
          </thead>
          <tbody>
            @foreach($rps->learningMaterials as $i => $m)
              <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $m->title }}</td>
                <td>{{ $m->author ?? '-' }}</td>
                <td>{{ $m->publisher ?? '-' }}</td>
                <td>{{ $m->year ?? '-' }}</td>
                <td>{{ $m->notes ?? '-' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @else
        <div class="muted">– Belum ada learning materials –</div>
      @endif
    </div>
  </div>

  {{-- Step 3: Learning Outcomes (PLO → CLO → Sub-CLO) --}}
  <div class="card-sec mb-3">
    <div class="hdr">Step 3: Learning Outcomes</div>
    <div class="bd">
      @if($rps->plos->count())
        @foreach($rps->plos as $pIndex => $plo)
          <div class="mb-2"><strong>PLO {{ $pIndex+1 }}:</strong> {{ $plo->description }}</div>
          @if($plo->clos->count())
            <table class="tbl mb-3">
              <thead>
                <tr>
                  <th style="width:35%">CLO</th>
                  <th>Sub-CLOs</th>
                </tr>
              </thead>
              <tbody>
                @foreach($plo->clos as $clo)
                  <tr>
                    <td>{{ $clo->clo }}</td>
                    <td>
                      @if($clo->subClos->count())
                        <ol class="mb-0 ps-3">
                          @foreach($clo->subClos as $sub)
                            <li>{{ $sub->description }}</li>
                          @endforeach
                        </ol>
                      @else
                        <span class="muted">–</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif
        @endforeach
      @else
        <div class="muted">– Belum ada PLO/CLO/Sub-CLO –</div>
      @endif
    </div>
  </div>

  {{-- Step 4: Assessments --}}
  <div class="card-sec mb-3">
    <div class="hdr">Step 4: Assessment</div>
    <div class="bd">
      @if($rps->assessments->count())
        <table class="tbl">
          <thead>
            <tr>
              <th style="width:44px">#</th>
              <th>Sub-CLO</th>
              <th style="width:20%">Type</th>
              <th style="width:12%">Weight (%)</th>
            </tr>
          </thead>
          <tbody>
            @foreach($rps->assessments as $i => $a)
              <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $a->subClo->description ?? '-' }}</td>
                <td>{{ $a->type }}</td>
                <td>{{ $a->weight }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @else
        <div class="muted">– Belum ada assessment –</div>
      @endif
    </div>
  </div>

  {{-- Step 5: Learning Planner (Weeks) --}}
  <div class="card-sec mb-3">
    <div class="hdr">Step 5: Learning Planner</div>
    <div class="bd">
      @if($rps->planners->count())
        <table class="tbl">
          <thead>
            <tr>
              <th style="width:64px">Week</th>
              <th>Topic</th>
              <th style="width:22%">Method</th>
              <th style="width:18%">Assessment</th>
              <th style="width:22%">Learning Material</th>
            </tr>
          </thead>
          <tbody>
            @foreach($rps->planners->sortBy('week') as $plan)
              <tr>
                <td class="text-center">{{ $plan->week }}</td>
                <td>{{ $plan->topic ?? '-' }}</td>
                <td>{{ $plan->method ?? '-' }}</td>
                <td>{{ $plan->assessment ?? '-' }}</td>
                <td>{{ $plan->material->title ?? '-' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @else
        <div class="muted">– Belum ada planner –</div>
      @endif
    </div>
  </div>

  {{-- Step 6: Contract --}}
  <div class="card-sec mb-4">
    <div class="hdr">Step 6: Contract</div>
    <div class="bd">
      @if($rps->contract)
        <div class="kv">
          <div>Attendance Policy:</div>    <div>{!! nl2br(e($rps->contract->attendance_policy ?? '-')) !!}</div>
          <div>Participation Policy:</div> <div>{!! nl2br(e($rps->contract->participation_policy ?? '-')) !!}</div>
          <div>Late Policy:</div>          <div>{!! nl2br(e($rps->contract->late_policy ?? '-')) !!}</div>
          <div>Grading Policy:</div>       <div>{!! nl2br(e($rps->contract->grading_policy ?? '-')) !!}</div>
          <div>Extra Rules:</div>          <div>{!! nl2br(e($rps->contract->extra_rules ?? '-')) !!}</div>
        </div>
      @else
        <div class="muted">– Belum ada contract –</div>
      @endif
    </div>
  </div>

  <div class="d-flex gap-2">
    <a href="{{ route('rps.index') }}" class="btn btn-secondary">Kembali</a>
    @if($rps->lecturer_id === auth()->id() && in_array($rps->status, ['revisi','draft']))
      <a href="{{ route('rps.edit', $rps->id) }}" class="btn btn-warning">Edit</a>
    @endif
  </div>
</div>
@endsection
