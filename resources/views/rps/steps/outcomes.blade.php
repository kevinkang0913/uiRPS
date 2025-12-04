{{-- resources/views/rps/steps/outcomes.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-xxl">

  <style>
    /* ========== VISUAL HIERARCHY ========== */

    /* CPL CARD */
    .plo-card {
        border-left: 5px solid #003366;
        background: #f6f9fc;
    }
    .plo-header {
        background: #003366;
        color: white;
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 600;
        margin-bottom: 12px;
    }

    /* CPMK CARD */
    .clo-card {
        border-left: 4px solid #2e7d32 !important;
        background: #f3fff5;
    }
    .clo-header {
        background: #2e7d32;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 6px;
        display: inline-block;
    }

    /* SUB-CPMK label */
    .sub-header {
        background: #444;
        color: #fff;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 6px;
        display: inline-block;
    }

    .assessment-header {
        background: #ff9800;
        color: #fff;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 6px;
        display: inline-block;
    }

    .small-hint {
        font-size: 11px;
        color: #666;
    }
  </style>

  <div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0">Buat RPS — Step 2: CPL (PLO) → CPMK (CLO) → sub-CPMK & Assessment</h4>
    @isset($rps)
      <div class="text-muted small">RPS ID: #{{ $rps->id }}</div>
    @endisset
  </div>

  <div class="progress mb-3" style="height:10px;">
    <div class="progress-bar bg-primary" style="width:33%"></div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <b>Periksa input:</b>
      <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('rps.store.step', 2) }}" class="card shadow-sm border-0">
    @csrf
    <div class="card-body p-4">
      <p class="small mb-3 text-muted">
        Atur hierarki <strong>CPL → CPMK → sub-CPMK</strong> dan pembobotan kategori
        <strong>assessment</strong> per CPMK. Pastikan:
        <br>
        1) Σ bobot <strong>CPL = 100%</strong>,
        2) Σ bobot <strong>CPMK dalam tiap CPL = 100%</strong>,
        3) Σ bobot <strong>sub-CPMK dalam tiap CPMK = 100%</strong> (jika ada),
        4) Σ bobot <strong>kategori assessment per CPMK = 100%</strong>.
      </p>

      <div class="mb-2 small d-flex align-items-center flex-wrap gap-2">
        <span>
          Total bobot CPL (PLO):
          <span id="cpl-weight-total" class="fw-semibold">0% dari 100%</span>
        </span>
        <button type="button"
                class="btn btn-sm btn-outline-secondary ms-2"
                onclick="autoDistributeCplWeights()">
          Bagi rata bobot CPL (100%)
        </button>
      </div>

      <div id="ploWrap" class="vstack gap-3"></div>

      <button type="button" class="btn btn-sm btn-outline-primary mt-3" id="btnAddPlo">
        + Tambah CPL (PLO)
      </button>

      <div class="form-text mt-2">
        Isi minimal 1 CPL. Di bawah tiap CPL, tambahkan CPMK dan sub-CPMK sesuai kebutuhan.
      </div>
    </div>

    <div class="card-footer bg-light d-flex justify-content-between">
      <a href="{{ route('rps.create.step', 1) }}" class="btn btn-outline-secondary">← Kembali</a>
      <button type="submit" class="btn btn-primary">
        Simpan & Lanjut ke Step 3 (Assessment Summary)
      </button>
    </div>
  </form>
</div>

<script>
const base = @json($plosSeed ?? []);

// kategori assessment (fix)
const ASSESS_CATEGORIES = [
  { code: 'PAR', label: 'PAR (Partisipasi/Attendance)' },
  { code: 'PRO', label: 'PRO (Proyek Akhir)' },
  { code: 'TG',  label: 'TG (Tugas)' },
  { code: 'QZ',  label: 'QZ (Kuis)' },
  { code: 'UTS', label: 'UTS' },
  { code: 'UAS', label: 'UAS' },
];

const wrap = document.getElementById('ploWrap');
document.getElementById('btnAddPlo').addEventListener('click', () => addPloCard({}));

function addPloCard(item){
  const i = wrap.children.length;
  const card = document.createElement('div');
  card.className = 'card border-0 shadow-sm plo-card';
  card.innerHTML = `
    <div class="plo-header d-flex justify-content-between align-items-center">
      <span>CPL (PLO)</span>
      <button type="button"
              class="btn btn-sm btn-outline-light"
              onclick="this.closest('.card').remove(); recalcAll()">
        Hapus CPL
      </button>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Kode CPL (PLO)</label>
          <input name="plos[${i}][code]" class="form-control" placeholder="CPL-1"
                 value="${item.code ?? ''}" required>
        </div>
        <div class="col-md-5">
          <label class="form-label">Deskripsi CPL</label>
          <input name="plos[${i}][description]" class="form-control"
                 value="${item.description ?? ''}" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Bobot CPL (%)</label>
          <input type="number" step="0.1" min="0" max="100"
                 name="plos[${i}][weight_cpl]"
                 class="form-control cpl-weight"
                 value="${item.weight_cpl ?? ''}"
                 oninput="recalcAll()">
        </div>
        <div class="col-md-2">
          <label class="form-label">Urut</label>
          <input type="number" min="1" name="plos[${i}][order_no]"
                 class="form-control" value="${item.order_no ?? (i+1)}">
        </div>
      </div>

      <div class="mt-2 text-end small">
        Total bobot CPMK di CPL ini:
        <span class="badge bg-light text-dark" data-plo-cpmk-total="${i}">0%</span>
      </div>

      <div class="mt-3">
        <label class="form-label d-flex justify-content-between align-items-center">
          <span>CPMK (CLO) di bawah CPL ini</span>
          <div class="d-flex gap-2">
            <button type="button"
                    class="btn btn-sm btn-outline-secondary"
                    onclick="autoDistributeCpmkWeights(${i})">
              Bagi rata bobot CPMK (100%)
            </button>
            <button type="button"
                    class="btn btn-sm btn-outline-secondary"
                    onclick="addClo(${i})">
              + Tambah CPMK (CLO)
            </button>
          </div>
        </label>
        <div id="clo-${i}" class="vstack gap-2"></div>
      </div>
    </div>
  `;
  wrap.appendChild(card);

  const box = document.getElementById('clo-' + i);
  const clos = (item.clos && Array.isArray(item.clos)) ? item.clos : [];
  if (clos.length){
    clos.forEach((o,idx)=> box.insertAdjacentHTML('beforeend', cloRow(i, idx, o)));
  } else {
    addClo(i);
  }
  recalcAll();
}

function cloRow(i, j, o){
  o = o || {no:'',description:'',order_no:'',weight_cpmk:'',subs:[],assess:{}};
  const subs   = (o.subs || []).map((s,k)=>subRow(i,j,k,s)).join('');
  const assess = o.assess || {};

  const assessHtml = ASSESS_CATEGORIES.map(cat => {
    const val = (assess && typeof assess === 'object') ? (assess[cat.code] ?? '') : '';
    return `
      <div class="col-6 col-md-4 col-lg-2">
        <div class="small-hint mb-1">${cat.label}</div>
        <input type="number" step="0.1" min="0" max="100"
               name="plos[${i}][clos][${j}][assess][${cat.code}]"
               class="form-control form-control-sm assess-input"
               value="${val}">
      </div>
    `;
  }).join('');

  return `
  <div class="card p-2 clo-card">
    <div class="clo-header">CPMK (CLO)</div>

    <div class="row g-2 align-items-end">
      <div class="col-md-2">
        <label class="form-label">No</label>
        <input type="number" min="1"
               name="plos[${i}][clos][${j}][no]"
               class="form-control"
               placeholder="No"
               value="${o.no ?? ''}" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Deskripsi CPMK (CLO)</label>
        <input name="plos[${i}][clos][${j}][description]"
               class="form-control"
               placeholder="Deskripsi CPMK (CLO)"
               value="${o.description ?? ''}" required>
      </div>
      <div class="col-md-2">
        <label class="form-label">
          Bobot CPMK (%)<br><span class="small-hint">(dalam CPL ini)</span>
        </label>
        <input type="number" step="0.1" min="0" max="100"
               name="plos[${i}][clos][${j}][weight_cpmk]"
               class="form-control cpmk-weight"
               data-plo-index="${i}"
               value="${o.weight_cpmk ?? ''}"
               oninput="recalcAll()">
      </div>
      <div class="col-md-1">
        <label class="form-label">Urut</label>
        <input type="number" min="1"
               name="plos[${i}][clos][${j}][order_no]"
               class="form-control"
               value="${o.order_no ?? (j+1)}">
      </div>
      <div class="col-md-1 d-grid">
        <button type="button"
                class="btn btn-outline-danger"
                onclick="this.closest('.card').remove(); recalcAll()">
          ×
        </button>
      </div>
    </div>

    <div class="mt-2">
      <label class="form-label d-flex justify-content-between align-items-center">
        <span class="sub-header">sub-CPMK</span>
        <div class="d-flex gap-2">
          <button type="button"
                  class="btn btn-sm btn-outline-secondary"
                  onclick="autoDistributeSubWeights(${i},${j})">
            Bagi rata bobot sub-CPMK (100%)
          </button>
          <button type="button"
                  class="btn btn-sm btn-outline-secondary"
                  onclick="addSub(${i},${j})">
            + Tambah sub-CPMK
          </button>
        </div>
      </label>
      <div id="subs-${i}-${j}" class="vstack gap-2">${subs}</div>
      <div class="small small-hint">
        Total bobot sub-CPMK per CPMK sebaiknya 100% (akan dicek di backend).
      </div>
    </div>

    <div class="mt-3">
      <span class="assessment-header">Assessment untuk CPMK ini</span>
      <div class="small small-hint mb-2">
        Isi distribusi bobot kategori assessment untuk CPMK ini. Jumlahnya harus 100%
        (dicek di backend).
      </div>
      <div class="row g-2 align-items-end">
        ${assessHtml}
      </div>
    </div>
  </div>`;
}

function subRow(i,j,k,s){
  s = s || {no:'',description:'',order_no:'',weight_sub:''};
  return `
  <div class="row g-2 align-items-end">
    <div class="col-md-2">
      <input type="number" min="1"
             name="plos[${i}][clos][${j}][subs][${k}][no]"
             class="form-control"
             placeholder="No"
             value="${s.no ?? ''}">
    </div>
    <div class="col-md-6">
      <input name="plos[${i}][clos][${j}][subs][${k}][description]"
             class="form-control"
             placeholder="Deskripsi sub-CPMK"
             value="${s.description ?? ''}">
    </div>
    <div class="col-md-3">
      <input type="number" min="0" max="100" step="0.1"
             name="plos[${i}][clos][${j}][subs][${k}][weight_sub]"
             class="form-control sub-weight"
             placeholder="Bobot sub (%)"
             value="${s.weight_sub ?? ''}">
    </div>
    <div class="col-md-1 d-grid">
      <button type="button"
              class="btn btn-outline-danger"
              onclick="this.closest('.row').remove()">
        ×
      </button>
    </div>
  </div>`;
}

function addClo(i){
  const box = document.getElementById('clo-'+i);
  const j = box.children.length;
  box.insertAdjacentHTML('beforeend', cloRow(i,j,{}));
  recalcAll();
}

function addSub(i,j){
  const box = document.getElementById(`subs-${i}-${j}`);
  const k = box.children.length;
  box.insertAdjacentHTML('beforeend', subRow(i,j,k,{}));
}

/* ===== TOMBOL BAGI RATA ===== */

// bagi rata bobot CPL untuk semua CPL (Σ=100)
function autoDistributeCplWeights() {
  const inputs = document.querySelectorAll('input.cpl-weight');
  const n = inputs.length;
  if (!n) return;

  const val = (100 / n).toFixed(1);
  inputs.forEach(inp => { inp.value = val; });
  recalcAll();
}

// bagi rata CPMK di satu CPL (pakai tombol di CPL)
function autoDistributeCpmkWeights(ploIndex){
  const wrapPlo = document.getElementById('clo-' + ploIndex);
  if (!wrapPlo) return;

  const inputs = wrapPlo.querySelectorAll('input.cpmk-weight');
  const n = inputs.length;
  if (!n) return;

  const val = (100 / n).toFixed(1);
  inputs.forEach(inp => { inp.value = val; });
  recalcAll();
}

// bagi rata sub-CPMK di satu CPMK (pakai tombol di CPMK)
function autoDistributeSubWeights(ploIndex, cloIndex){
  const box = document.getElementById(`subs-${ploIndex}-${cloIndex}`);
  if (!box) return;

  const inputs = box.querySelectorAll('input.sub-weight');
  const n = inputs.length;
  if (!n) return;

  const val = (100 / n).toFixed(1);
  inputs.forEach(inp => { inp.value = val; });
}

/* ===== REKALKULASI TOTAL UNTUK LABEL ===== */

function recalcAll(){
  // 1) Total CPL
  let cplTotal = 0;
  document.querySelectorAll('input.cpl-weight').forEach(inp => {
    const v = parseFloat(inp.value);
    if (!isNaN(v)) cplTotal += v;
  });
  const elCpl = document.getElementById('cpl-weight-total');
  if (elCpl) {
    elCpl.textContent = cplTotal.toFixed(1) + '% dari 100%';
    elCpl.classList.toggle('text-danger', Math.abs(cplTotal - 100) > 0.1);
  }

  // 2) Total CPMK per CPL
  const perPlo = {};
  document.querySelectorAll('input.cpmk-weight').forEach(inp => {
    const idx = inp.dataset.ploIndex;
    const v = parseFloat(inp.value);
    if (idx === undefined) return;
    if (!perPlo[idx]) perPlo[idx] = 0;
    if (!isNaN(v)) perPlo[idx] += v;
  });
  Object.keys(perPlo).forEach(i => {
    const span = document.querySelector(`[data-plo-cpmk-total="${i}"]`);
    if (span) {
      span.textContent = perPlo[i].toFixed(1) + '%';
      span.classList.toggle('text-bg-danger', Math.abs(perPlo[i] - 100) > 0.1);
    }
  });
}

/* ===== INIT ===== */

if (Array.isArray(base) && base.length){
  base.forEach(addPloCard);
} else {
  addPloCard({});
}
recalcAll();
</script>
@endsection
