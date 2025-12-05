{{-- resources/views/rps/steps/weekly.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-xxl">

  <div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">Step 5 — Rencana Pembelajaran Mingguan (RPM)</h4>
    <div class="text-muted small">RPS #{{ $rps->id }}</div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger">
      <b>Periksa input:</b>
      <ul class="mb-0">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @php
    $assessmentOptions = [
        'Test Lisan',
        'Test Tertulis',
        'Partisipasi',
        'Presentasi',
        'Proyek',
        'Lainnya',
    ];

    // Susun data form dari old() kalau ada error, else dari $weeks (DB)
    $formWeeks = [];
    $oldWeeks  = old('weeks');

    if (is_array($oldWeeks)) {
        // CASE 1: balik dari validasi gagal → pakai old()
        foreach ($oldWeeks as $i => $w) {
            $formWeeks[] = [
                'week_no'              => $w['week_no']              ?? ($i+1),
                'session_no'           => $w['session_no']           ?? null,
                'topic'                => $w['topic']                ?? '',
                'indicator'            => $w['indicator']            ?? '',
                'assessment_technique' => $w['assessment_technique'] ?? '',
                'assessment_criteria'  => $w['assessment_criteria']  ?? '',
                'learning_in'          => $w['learning_in']          ?? '',
                'learning_online'      => $w['learning_online']      ?? '',
                'reference_id'         => $w['reference_id']         ?? null,
                'sub_clos'             => array_values($w['sub_clos'] ?? []),
                'weight_total'         => null, // backend akan hitung ulang setelah submit; di UI cukup kosong
            ];
        }
    } elseif(isset($weeks) && count($weeks)) {
        // CASE 2: load dari DB (hasil create controller)
        foreach ($weeks as $w) {
            // $w adalah array: week_no, session_no, topic, ..., reference_id, sub_clos, weight_total
            $formWeeks[] = [
                'week_no'              => $w['week_no']              ?? null,
                'session_no'           => $w['session_no']           ?? null,
                'topic'                => $w['topic']                ?? '',
                'indicator'            => $w['indicator']            ?? '',
                'assessment_technique' => $w['assessment_technique'] ?? '',
                'assessment_criteria'  => $w['assessment_criteria']  ?? '',
                'learning_in'          => $w['learning_in']          ?? '',
                'learning_online'      => $w['learning_online']      ?? '',
                'reference_id'         => $w['reference_id']         ?? null,
                'sub_clos'             => $w['sub_clos']             ?? [],
                'weight_total'         => $w['weight_total']         ?? null,
            ];
        }
    }

    // CASE 3: tidak ada apa-apa → default 1 minggu kosong
    if (empty($formWeeks)) {
        $formWeeks[] = [
            'week_no'              => 1,
            'session_no'           => null,
            'topic'                => '',
            'indicator'            => '',
            'assessment_technique' => '',
            'assessment_criteria'  => '',
            'learning_in'          => '',
            'learning_online'      => '',
            'reference_id'         => null,
            'sub_clos'             => [],
            'weight_total'         => null,
        ];
    }
  @endphp

  <form method="POST" action="{{ route('rps.store.step', 5) }}" class="card shadow-sm border-0">
    @csrf

    <div class="card-body p-3">

      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0">Rencana per Minggu / Sesi</h6>
        <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddWeek">
          + Tambah Minggu
        </button>
      </div>

      {{-- Tabs Minggu --}}
      <ul class="nav nav-tabs mb-3" id="weekTabs" role="tablist">
        @foreach($formWeeks as $wi => $w)
          <li class="nav-item" role="presentation">
            <button
              class="nav-link @if($wi === 0) active @endif"
              id="week-tab-{{ $wi }}"
              data-bs-toggle="tab"
              data-bs-target="#week-pane-{{ $wi }}"
              type="button"
              role="tab">
              Minggu {{ $w['week_no'] }}
            </button>
          </li>
        @endforeach
      </ul>

      {{-- Tab Content --}}
      <div class="tab-content" id="weekTabsContent">
        @foreach($formWeeks as $wi => $w)
          @php
            $subSelected = $w['sub_clos'] ?? [];
            if (empty($subSelected)) {
                // minimal 1 dropdown kosong
                $subSelected = [null];
            }
          @endphp

          <div class="tab-pane fade @if($wi === 0) show active @endif"
               id="week-pane-{{ $wi }}" role="tabpanel">

            <div class="border rounded p-3 mb-3 bg-light-subtle position-relative week-pane"
                 data-week-index="{{ $wi }}">

              {{-- Baris: Minggu, Sesi, Topik, Bobot total --}}
              <div class="row g-2 mb-2">
                <div class="col-md-2">
                  <label class="form-label small mb-1">Minggu *</label>
                  <input type="number"
                         class="form-control form-control-sm"
                         name="weeks[{{ $wi }}][week_no]"
                         value="{{ $w['week_no'] }}"
                         min="1"
                         required>
                </div>
                <div class="col-md-2">
                  <label class="form-label small mb-1">Sesi</label>
                  <input type="number"
                         class="form-control form-control-sm"
                         name="weeks[{{ $wi }}][session_no]"
                         value="{{ $w['session_no'] }}"
                         min="1">
                </div>
                <div class="col-md-5">
                  <label class="form-label small mb-1">Topik / Kemampuan Akhir *</label>
                  <input type="text"
                         class="form-control form-control-sm"
                         name="weeks[{{ $wi }}][topic]"
                         value="{{ $w['topic'] }}"
                         required>
                </div>
                <div class="col-md-3">
                  <label class="form-label small mb-1">Bobot Total Minggu (%)</label>
                  <input type="text"
                    class="form-control form-control-sm text-end week-total-weight"
                    value="{{ !is_null($w['weight_total']) ? number_format($w['weight_total'], 2) : '' }}"
                    readonly>
                </div>
              </div>

              {{-- Baris: Indikator, Teknik, Kriteria --}}
              <div class="row g-2 mb-2">
                <div class="col-md-4">
                  <label class="form-label small mb-1">Indikator Penilaian</label>
                  <textarea
                    name="weeks[{{ $wi }}][indicator]"
                    class="form-control form-control-sm"
                    rows="2">{{ $w['indicator'] }}</textarea>
                </div>
                <div class="col-md-3">
                  <label class="form-label small mb-1">Teknik Penilaian</label>
                  <select
                    name="weeks[{{ $wi }}][assessment_technique]"
                    class="form-select form-select-sm">
                    <option value="">— Pilih Teknik —</option>
                    @foreach($assessmentOptions as $opt)
                      <option value="{{ $opt }}" @selected($w['assessment_technique'] === $opt)>
                        {{ $opt }}
                      </option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-5">
                  <label class="form-label small mb-1">Kriteria Penilaian</label>
                  <textarea
                    name="weeks[{{ $wi }}][assessment_criteria]"
                    class="form-control form-control-sm"
                    rows="2">{{ $w['assessment_criteria'] }}</textarea>
                </div>
              </div>

              {{-- Baris: Luring, Daring --}}
              <div class="row g-2 mb-2">
                <div class="col-md-6">
                  <label class="form-label small mb-1">Aktivitas Luring</label>
                  <textarea
                    name="weeks[{{ $wi }}][learning_in]"
                    class="form-control form-control-sm"
                    rows="2">{{ $w['learning_in'] }}</textarea>
                </div>
                <div class="col-md-6">
                  <label class="form-label small mb-1">Aktivitas Daring</label>
                  <textarea
                    name="weeks[{{ $wi }}][learning_online]"
                    class="form-control form-control-sm"
                    rows="2">{{ $w['learning_online'] }}</textarea>
                </div>
              </div>

              {{-- Baris: Referensi --}}
              <div class="row g-2 mb-2">
                <div class="col-md-6">
                  <label class="form-label small mb-1">Referensi (dropdown dari Step 4)</label>
                  <select
                    name="weeks[{{ $wi }}][reference_id]"
                    class="form-select form-select-sm">
                    <option value="">— Pilih Referensi —</option>
                    @foreach($refs as $ref)
                      @php
                        $label = ($ref->type === 'utama' ? '[Utama] ' : '[Pendukung] ')
                                 . \Illuminate\Support\Str::limit($ref->title, 80);
                      @endphp
                      <option value="{{ $ref->id }}" @selected($w['reference_id'] == $ref->id)>
                        {{ $label }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>

              <hr class="my-2">

              {{-- Sub-CPMK list --}}
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Sub-CPMK</h6>
                <button type="button"
                        class="btn btn-sm btn-outline-success btnAddSubClo"
                        data-week-index="{{ $wi }}">
                  + Tambah Sub-CPMK
                </button>
              </div>

              <div class="vstack gap-2 subclos-wrap" id="subclos-wrap-{{ $wi }}">
                @foreach($subSelected as $si => $sid)
                  @include('rps.steps.partials.week-subclo-row', [
                    'wi'      => $wi,
                    'si'      => $si,
                    'subId'   => $sid,
                    'subClos' => $subClos,
                  ])
                @endforeach
              </div>

              {{-- tombol hapus minggu --}}
              @if(count($formWeeks) > 1)
                <button type="button"
                        class="btn btn-sm btn-outline-danger position-absolute btn-remove-week"
                        style="top:-10px; right:-10px;">
                  ×
                </button>
              @endif

            </div>
          </div>
        @endforeach
      </div>

    </div>

    <div class="card-footer bg-light d-flex justify-content-between">
      <a href="{{ route('rps.create.step', 4) }}" class="btn btn-outline-secondary">
        ← Kembali
      </a>
      <button class="btn btn-primary">Simpan dan Lanjut ke Final Step Kontrak</button>
    </div>
  </form>
</div>

{{-- TEMPLATE WEEK PANE --}}
<template id="tplWeekPane">
  @php $tplWi = '__WI__'; @endphp
  <div class="tab-pane fade" id="week-pane-__WI__" role="tabpanel">
    <div class="border rounded p-3 mb-3 bg-light-subtle position-relative week-pane"
         data-week-index="__WI__">

      <div class="row g-2 mb-2">
        <div class="col-md-2">
          <label class="form-label small mb-1">Minggu *</label>
          <input type="number"
                 class="form-control form-control-sm"
                 name="weeks[__WI__][week_no]"
                 value="__WEEK_NO__"
                 min="1"
                 required>
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Sesi</label>
          <input type="number"
                 class="form-control form-control-sm"
                 name="weeks[__WI__][session_no]"
                 value=""
                 min="1">
        </div>
        <div class="col-md-5">
          <label class="form-label small mb-1">Topik / Kemampuan Akhir *</label>
          <input type="text"
                 class="form-control form-control-sm"
                 name="weeks[__WI__][topic]"
                 value=""
                 required>
        </div>
        <div class="col-md-3">
          <label class="form-label small mb-1">Bobot Total Minggu (%)</label>
          <input type="text"
            class="form-control form-control-sm text-end week-total-weight"
            value=""
            readonly>
        </div>
      </div>

      <div class="row g-2 mb-2">
        <div class="col-md-4">
          <label class="form-label small mb-1">Indikator Penilaian</label>
          <textarea
            name="weeks[__WI__][indicator]"
            class="form-control form-control-sm"
            rows="2"></textarea>
        </div>
        <div class="col-md-3">
          <label class="form-label small mb-1">Teknik Penilaian</label>
          <select
            name="weeks[__WI__][assessment_technique]"
            class="form-select form-select-sm">
            <option value="">— Pilih Teknik —</option>
            @foreach($assessmentOptions as $opt)
              <option value="{{ $opt }}">{{ $opt }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-5">
          <label class="form-label small mb-1">Kriteria Penilaian</label>
          <textarea
            name="weeks[__WI__][assessment_criteria]"
            class="form-control form-control-sm"
            rows="2"></textarea>
        </div>
      </div>

      <div class="row g-2 mb-2">
        <div class="col-md-6">
          <label class="form-label small mb-1">Aktivitas Luring</label>
          <textarea
            name="weeks[__WI__][learning_in]"
            class="form-control form-control-sm"
            rows="2"></textarea>
        </div>
        <div class="col-md-6">
          <label class="form-label small mb-1">Aktivitas Daring</label>
          <textarea
            name="weeks[__WI__][learning_online]"
            class="form-control form-control-sm"
            rows="2"></textarea>
        </div>
      </div>

      <div class="row g-2 mb-2">
        <div class="col-md-6">
          <label class="form-label small mb-1">Referensi (dropdown dari Step 4)</label>
          <select
            name="weeks[__WI__][reference_id]"
            class="form-select form-select-sm">
            <option value="">— Pilih Referensi —</option>
            @foreach($refs as $ref)
              @php
                $label = ($ref->type === 'utama' ? '[Utama] ' : '[Pendukung] ')
                         . \Illuminate\Support\Str::limit($ref->title, 80);
              @endphp
              <option value="{{ $ref->id }}">{{ $label }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <hr class="my-2">

      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0">Sub-CPMK</h6>
        <button type="button"
                class="btn btn-sm btn-outline-success btnAddSubClo"
                data-week-index="__WI__">
          + Tambah Sub-CPMK
        </button>
      </div>

      <div class="vstack gap-2 subclos-wrap" id="subclos-wrap-__WI__">
        @include('rps.steps.partials.week-subclo-row', [
          'wi'      => '__WI__',
          'si'      => '__SI__',
          'subId'   => null,
          'subClos' => $subClos,
        ])
      </div>

      <button type="button"
              class="btn btn-sm btn-outline-danger position-absolute btn-remove-week"
              style="top:-10px; right:-10px;">
        ×
      </button>
    </div>
  </div>
</template>

{{-- TEMPLATE SUB-CPMK ROW --}}
<template id="tplSubCloRow">
  @include('rps.steps.partials.week-subclo-row', [
    'wi'      => '__WI__',
    'si'      => '__SI__',
    'subId'   => null,
    'subClos' => $subClos,
  ])
</template>

<script>
  // Mapping id Sub-CPMK -> bobot global (dari Step 2)
  const SUBCLO_WEIGHTS = @json(
    $subClos->mapWithKeys(fn($s) => [$s->id => (float)($s->weight_percent ?? 0)])
  );

  const weekTabs        = document.getElementById('weekTabs');
  const weekTabsContent = document.getElementById('weekTabsContent');
  const btnAddWeek      = document.getElementById('btnAddWeek');
  const tplWeekPane     = document.getElementById('tplWeekPane');
  const tplSubCloRow    = document.getElementById('tplSubCloRow');

  function getWeekCount() {
    return weekTabs.querySelectorAll('.nav-link').length;
  }

  function activateTab(index) {
    const tabs  = weekTabs.querySelectorAll('.nav-link');
    const panes = weekTabsContent.querySelectorAll('.tab-pane');

    tabs.forEach((t, i) => {
      t.classList.toggle('active', i === index);
    });
    panes.forEach((p, i) => {
      p.classList.toggle('show', i === index);
      p.classList.toggle('active', i === index);
    });
  }

  // Hitung total bobot per minggu berdasarkan pilihan Sub-CPMK
  function recalcAllWeekWeights() {
    // 1) Hitung usage global per sub_clo_id
    const usage = {};
    document.querySelectorAll('.subclo-item select').forEach(sel => {
      const v = sel.value;
      if (!v) return;
      usage[v] = (usage[v] || 0) + 1;
    });

    // 2) Hitung bobot per week-pane
    document.querySelectorAll('.week-pane').forEach(pane => {
      let total = 0.0;

      pane.querySelectorAll('.subclo-item select').forEach(sel => {
        const id = sel.value;
        if (!id) return;

        const globalW = SUBCLO_WEIGHTS[id] || 0;
        const count   = usage[id] || 1;
        if (globalW <= 0 || count <= 0) return;

        total += (globalW / count);
      });

      const input = pane.querySelector('.week-total-weight');
      if (input) {
        input.value = total ? total.toFixed(2) : '';
      }
    });
  }

  btnAddWeek.addEventListener('click', function () {
    let wi       = getWeekCount();
    let newWeekNo= wi + 1;

    // nav tab
    const li = document.createElement('li');
    li.className = 'nav-item';
    li.innerHTML = `
      <button class="nav-link" id="week-tab-${wi}"
              data-bs-toggle="tab"
              data-bs-target="#week-pane-${wi}"
              type="button" role="tab">
        Minggu ${newWeekNo}
      </button>
    `;
    weekTabs.appendChild(li);

    // pane
    let htmlPane = tplWeekPane.innerHTML
        .replace(/__WI__/g, wi)
        .replace(/__WEEK_NO__/g, newWeekNo)
        .replace(/__SI__/g, 0);

    const holder = document.createElement('div');
    holder.innerHTML = htmlPane.trim();
    weekTabsContent.appendChild(holder.firstElementChild);

    activateTab(wi);
    recalcAllWeekWeights();
  });

  document.addEventListener('click', function (e) {
    // tambah Sub-CPMK
    if (e.target.classList.contains('btnAddSubClo')) {
      const wi   = e.target.getAttribute('data-week-index');
      const wrap = document.getElementById('subclos-wrap-' + wi);
      const si   = wrap.querySelectorAll('.subclo-item').length;

      let htmlRow = tplSubCloRow.innerHTML
          .replace(/__WI__/g, wi)
          .replace(/__SI__/g, si);

      const holder = document.createElement('div');
      holder.innerHTML = htmlRow.trim();
      wrap.appendChild(holder.firstElementChild);

      recalcAllWeekWeights();
    }

    // hapus Sub-CPMK
    if (e.target.classList.contains('btn-remove-subclo')) {
      const item = e.target.closest('.subclo-item');
      if (item) {
        item.remove();
        recalcAllWeekWeights();
      }
    }

    // hapus minggu
    if (e.target.classList.contains('btn-remove-week')) {
      const pane = e.target.closest('.tab-pane');
      if (!pane) return;
      const paneId = pane.id;

      // hapus tab
      const tabBtn = weekTabs.querySelector('[data-bs-target="#' + paneId + '"]');
      if (tabBtn) {
        const li = tabBtn.closest('li');
        if (li) li.remove();
      }

      // hapus pane
      pane.remove();

      if (getWeekCount() > 0) {
        activateTab(0);
      }
      recalcAllWeekWeights();
    }
  });

  // Recalculate ketika dropdown Sub-CPMK berubah
  document.addEventListener('change', function(e){
    if (e.target.closest('.subclo-item') && e.target.tagName === 'SELECT') {
      recalcAllWeekWeights();
    }
  });

  // Initial calc (data dari DB / old)
  recalcAllWeekWeights();
</script>

@endsection
