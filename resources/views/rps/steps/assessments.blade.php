{{-- resources/views/rps/steps/assessments.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-xxl">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Buat RPS — Step 3: Bobot CPMK dan Assessment Category</h4>
    <div class="text-muted small">RPS ID: #{{ $rps->id }}</div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <b>Periksa input:</b>
      <ul class="mb-0">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  {{-- ============== TABEL 1: Ringkasan CPMK & Bobot dari Step 2 ============== --}}
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
      <h5 class="mb-0">Ringkasan CPMK dan Bobot (Step 2)</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0">
          <thead class="table-light">
            <tr>
              <th style="width:80px;">CPMK</th>
              <th>Deskripsi CPMK (CLO)</th>
              <th style="width:140px;" class="text-end">Bobot CPMK (%)</th>
            </tr>
          </thead>
          <tbody>
            @forelse($clos as $clo)
              <tr>
                <td>CPMK{{ $clo->no }}</td>
                <td>{{ $clo->description }}</td>
                <td class="text-end">
                  @if(!is_null($clo->weight_percent))
                    {{ number_format($clo->weight_percent, 2) }}%
                  @else
                    <span class="text-muted">–</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="text-center text-muted small">
                  Belum ada CPMK yang diinput di Step 2.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer small text-muted d-flex justify-content-between">
      <span>Total bobot CPMK (target 100%):</span>
      <span class="fw-semibold">
        {{ number_format($clos->sum('weight_percent'), 2) }}%
      </span>
    </div>
  </div>

  {{-- ============== TABEL 2: Matriks Assessment × CPMK ============== --}}
  <form method="POST" action="{{ route('rps.store.step', 3) }}" class="card shadow-sm border-0">
    @csrf

    <div class="table-responsive">
      <table class="table table-bordered align-middle mb-0">
        <thead class="table-light text-center">
          <tr>
            <th style="width:220px">Assessment Category</th>
            <th style="width:130px">Bobot Kategori (%)<br><small class="text-muted">auto dari CPMK</small></th>
            <th style="width:260px">Deskripsi</th>

            @foreach($clos as $clo)
              <th>
                CPMK{{ $clo->no }}
                @if(!is_null($clo->weight_percent))
                  <div class="small text-muted">
                    {{ number_format($clo->weight_percent, 2) }}%
                  </div>
                @endif
              </th>
            @endforeach

            <th style="width:170px">
              Total CPMK baris<br>(≈ 100%)<br>
              <small class="text-muted">+ bobot kategori hasil CP MK</small>
            </th>
          </tr>
        </thead>

        <tbody>
          @foreach($cats as $cat)
            @php
              $currWeight     = (float)($catWeights[$cat->id] ?? 0);
              $rowTotal       = 0;
              $weightedByCpmk = 0;
            @endphp
            <tr data-cat-id="{{ $cat->id }}">
              {{-- Nama kategori --}}
              <td>
                <div class="fw-semibold">{{ $cat->code }}</div>
                <div class="text-muted small">{{ $cat->name }}</div>
              </td>

              {{-- Bobot kategori (readonly, diisi otomatis) --}}
              <td>
                <input
                  type="number" step="0.01" min="0" max="100"
                  name="cat_weight[{{ $cat->id }}]"
                  class="form-control form-control-sm text-end cat-weight-input"
                  value="{{ number_format($currWeight, 2) }}"
                  readonly
                >
              </td>

              {{-- Deskripsi kategori (opsional) --}}
              <td>
                <input
                  name="desc[{{ $cat->id }}]"
                  class="form-control form-control-sm"
                  placeholder="Deskripsi singkat (opsional)"
                  value="{{ old('desc.'.$cat->id, $cat->desc ?? '') }}"
                >
              </td>

              {{-- Matriks CPMK × kategori --}}
              @foreach($clos as $clo)
                @php
                  $val = (float)($weights[$cat->id][$clo->id] ?? 0);
                  $rowTotal       += $val;
                  $weightedByCpmk += ($val / 100) * (float)($clo->weight_percent ?? 0);
                @endphp
                <td>
                  <input
                    type="number" step="0.01" min="0" max="100"
                    name="weights[{{ $cat->id }}][{{ $clo->id }}]"
                    class="form-control form-control-sm text-end weight-input"
                    data-clo-id="{{ $clo->id }}"
                    value="{{ old("weights.$cat->id.$clo->id", $val > 0 ? $val : '') }}"
                    placeholder="–"
                  >
                </td>
              @endforeach

              <td class="text-center fw-semibold">
                <div>
                  <span class="row-total">{{ number_format($rowTotal, 2) }}</span>% 
                  <div class="small text-muted">
                    ≈ <span class="cat-bobot-display">{{ number_format($weightedByCpmk, 2) }}</span>% bobot kategori ini
                  </div>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>

        <tfoot class="table-light">
          <tr>
            <th class="text-end">Total Bobot Kategori</th>
            <th class="text-center">
              <span id="sum-cats">
                {{ number_format(array_sum($catWeights ?? []), 2) }}
              </span>%
            </th>
            <th class="text-end">Total per CPMK</th>

            @foreach($clos as $clo)
              @php
                $colSum = 0;
                foreach($cats as $cat){
                  $colSum += (float)($weights[$cat->id][$clo->id] ?? 0);
                }
              @endphp
              <th class="text-center">
                <span class="col-total">{{ number_format($colSum, 2) }}</span>%
              </th>
            @endforeach

            <th></th>
          </tr>
        </tfoot>
      </table>
    </div>

    <div class="card-footer bg-light d-flex justify-content-between">
      <a href="{{ route('rps.cpl_cpmk.edit', $rps) }}" class="btn btn-outline-secondary">← Kembali ke Pembobotan CPL–CPMK</a>
      <button type="submit" class="btn btn-primary">Simpan & Lanjut ke Step 4</button>
    </div>
  </form>
</div>

{{-- ==== JS: hitung bobot kategori otomatis dari matriks + bobot CPMK ==== --}}
<script>
function num(v){ return parseFloat(v) || 0; }

// bobot CPMK dari backend (id => weight_percent)
const CPMK_WEIGHTS = @json($clos->pluck('weight_percent','id'));

document.addEventListener('input', function(e){
  // kalau yang diubah adalah matriks weights[cat][clo]
  if (e.target.classList.contains('weight-input')) {
    const inp   = e.target;
    const td    = inp.closest('td');
    const tr    = inp.closest('tr');
    const table = tr.closest('table');

    // ----- hitung total baris + bobot kategori (rumus Excel) -----
    let rowTotal = 0;
    let weighted = 0;

    tr.querySelectorAll('.weight-input').forEach(i => {
      const val   = num(i.value);
      const cloId = i.dataset.cloId;
      rowTotal += val;

      if (CPMK_WEIGHTS[cloId] !== undefined && CPMK_WEIGHTS[cloId] !== null) {
        weighted += (val / 100.0) * num(CPMK_WEIGHTS[cloId]);
      }
    });

    const rowSpan  = tr.querySelector('.row-total');
    const bobotEl  = tr.querySelector('.cat-bobot-display');
    const catInput = tr.querySelector('.cat-weight-input');

    if (rowSpan)  rowSpan.textContent  = rowTotal.toFixed(2);
    if (bobotEl)  bobotEl.textContent  = weighted.toFixed(2);
    if (catInput) catInput.value       = weighted.toFixed(2);

    // ----- hitung total per CPMK (kolom) -----
    const colIndex = Array.from(tr.children).indexOf(td);
    let col = 0;
    table.querySelectorAll('tbody tr').forEach(r => {
      const cell = r.children[colIndex];
      const i    = cell ? cell.querySelector('.weight-input') : null;
      if (i) col += num(i.value);
    });

    const footerCells = table.querySelectorAll('tfoot .col-total');
    const footerIndex = colIndex - 3; // offset: 3 kolom pertama (kategori, bobot, desc)
    if (footerCells[footerIndex]) {
      footerCells[footerIndex].textContent = col.toFixed(2);
    }

    // ----- hitung total bobot kategori (sum-cats) -----
    let sumCats = 0;
    document.querySelectorAll('.cat-weight-input').forEach(i => {
      sumCats += num(i.value);
    });
    const sumCatsEl = document.getElementById('sum-cats');
    if (sumCatsEl) sumCatsEl.textContent = sumCats.toFixed(2);
  }
});
</script>
@endsection
