{{-- resources/views/rps/steps/outcomes.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-xxl">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0">Buat RPS — Step 2: CPL (PLO) → CPMK (CLO) → sub-CPMK</h4>
    @isset($rps)
      <div class="text-muted small">RPS ID: #{{ $rps->id }}</div>
    @endisset
  </div>

  <div class="progress mb-3" style="height:10px;">
    <div class="progress-bar bg-primary" style="width:33%"></div>
  </div>

  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <b>Periksa input:</b>
      <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('rps.store.step', 2) }}" class="card shadow-sm border-0">
    @csrf
    <div class="card-body p-4">
      <div id="ploWrap" class="vstack gap-3"></div>

      <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="btnAddPlo">
        + Tambah CPL (PLO)
      </button>

      <div class="form-text mt-2">
        Isi minimal 1 CPL. Di bawah tiap CPL, tambahkan CPMK dan sub-CPMK sesuai kebutuhan.
      </div>

      <div class="mt-3 small">
        Total bobot CPMK (semua CPL): 
        <span id="cpmk-weight-total" class="fw-semibold">0% dari 100%</span>
      </div>
    </div>

    <div class="card-footer bg-light d-flex justify-content-between">
      <a href="{{ route('rps.create.step', 1) }}" class="btn btn-outline-secondary">← Kembali</a>
      <button type="submit" class="btn btn-primary">Simpan & Lanjut ke Step 3</button>
    </div>
  </form>
</div>

<script>
const base = @json($plosSeed ?? []);

const wrap = document.getElementById('ploWrap');
document.getElementById('btnAddPlo').addEventListener('click', () => addPloCard({}));

function addPloCard(item) { // ← nama aman, tidak bentrok
  const i = wrap.children.length;
  const card = document.createElement('div');
  card.className = 'card border-0 shadow-sm';
  card.innerHTML = `
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Kode CPL (PLO)</label>
          <input name="plos[${i}][code]" class="form-control" placeholder="CPL-1"
                 value="${item.code ?? ''}" required>
        </div>
        <div class="col-md-8">
          <label class="form-label">Deskripsi CPL</label>
          <input name="plos[${i}][description]" class="form-control"
                 value="${item.description ?? ''}" required>
        </div>
        <div class="col-md-1">
          <label class="form-label">Urut</label>
          <input type="number" min="1" name="plos[${i}][order_no]"
                 class="form-control" value="${item.order_no ?? (i+1)}">
        </div>
      </div>

      <div class="mt-3">
        <label class="form-label d-flex justify-content-between align-items-center">
          <span>CPMK (CLO) di bawah CPL ini</span>
          <div class="d-flex gap-2">
            <button type="button"
                    class="btn btn-sm btn-outline-secondary"
                    onclick="autoDistributeCpmkWeights(${i})">
              Bagi rata bobot (100%)
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

      <div class="text-end mt-2">
        <button type="button"
                class="btn btn-outline-danger btn-sm"
                onclick="this.closest('.card').remove(); recalcCpmkTotal()">
          Hapus CPL
        </button>
      </div>
    </div>
  `;
  wrap.appendChild(card);

  const box = document.getElementById('clo-' + i);
  const clos = (item.clos && Array.isArray(item.clos)) ? item.clos : [];
  if (clos.length) {
    clos.forEach((o, idx) => box.insertAdjacentHTML('beforeend', cloRow(i, idx, o)));
  } else {
    addClo(i);
  }
  recalcCpmkTotal();
}

function cloRow(i, j, o) {
  o = o || {no: '', description: '', order_no: '', weight_percent: '', subs: []};
  const subs = (o.subs || []).map((s, k) => subRow(i, j, k, s)).join('');
  return `
  <div class="card p-2 clo-card">
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
        <label class="form-label">Bobot CPMK (%)</label>
        <input type="number" step="0.1" min="0" max="100"
               name="plos[${i}][clos][${j}][weight_percent]"
               class="form-control clo-weight"
               value="${o.weight_percent ?? ''}"
               oninput="recalcCpmkTotal()">
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
                onclick="this.closest('.card').remove(); recalcCpmkTotal()">
          ×
        </button>
      </div>
    </div>
    <div class="mt-2">
      <label class="form-label d-flex justify-content-between">
        <span>sub-CPMK (sub-CLO)</span>
        <button type="button"
                class="btn btn-sm btn-outline-secondary"
                onclick="addSub(${i}, ${j})">
          + Tambah sub
        </button>
      </label>
      <div id="subs-${i}-${j}" class="vstack gap-2">${subs}</div>
    </div>
  </div>`;
}

function subRow(i, j, k, s) {
  s = s || {no: '', description: '', order_no: ''};
  return `
  <div class="row g-2">
    <div class="col-md-2">
      <input type="number" min="1"
             name="plos[${i}][clos][${j}][subs][${k}][no]"
             class="form-control"
             placeholder="No"
             value="${s.no ?? ''}">
    </div>
    <div class="col-md-9">
      <input name="plos[${i}][clos][${j}][subs][${k}][description]"
             class="form-control"
             placeholder="Deskripsi sub-CPMK"
             value="${s.description ?? ''}">
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

function addClo(i) {
  const box = document.getElementById('clo-' + i);
  const j = box.children.length;
  box.insertAdjacentHTML('beforeend', cloRow(i, j, {}));
  recalcCpmkTotal();
}

function addSub(i, j) {
  const box = document.getElementById(`subs-${i}-${j}`);
  const k = box.children.length;
  box.insertAdjacentHTML('beforeend', subRow(i, j, k, {}));
}

function autoDistributeCpmkWeights(ploIndex) {
  const wrapPlo = document.getElementById('clo-' + ploIndex);
  if (!wrapPlo) return;

  const inputs = wrapPlo.querySelectorAll('input.clo-weight');
  const n = inputs.length;
  if (!n) return;

  const val = (100 / n).toFixed(1);
  inputs.forEach(inp => inp.value = val);
  recalcCpmkTotal();
}

function recalcCpmkTotal() {
  let total = 0;
  document.querySelectorAll('input.clo-weight').forEach(inp => {
    const v = parseFloat(inp.value);
    if (!isNaN(v)) {
      total += v;
    }
  });

  const el = document.getElementById('cpmk-weight-total');
  if (!el) return;

  el.textContent = total.toFixed(1) + '% dari 100%';
  el.classList.toggle('text-danger', Math.abs(total - 100) > 0.1);
}

// init
if (Array.isArray(base) && base.length) {
  base.forEach(addPloCard);
} else {
  addPloCard({});
}
recalcCpmkTotal();
</script>
@endsection
