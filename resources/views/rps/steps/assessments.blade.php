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

  {{-- ================= TABLE 1: RINGKASAN CPMK & BOBOT ================= --}}
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
      <h5 class="mb-0">Ringkasan CPMK dan Bobot (dari Step 2)</h5>
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
      <span>Total bobot CPMK (semua CPL dianggap 100%):</span>
      <span class="fw-semibold">
        {{ number_format($clos->sum('weight_percent'), 2) }}%
      </span>
    </div>
  </div>

  {{-- ================= TABLE 2: ASSESSMENT × CPMK ================= --}}
  <form method="POST" action="{{ route('rps.store.step', 3) }}" class="card shadow-sm border-0">
    @csrf

    <div class="table-responsive">
      <table class="table table-bordered align-middle mb-0">
        <thead class="table-light text-center">
          <tr>
            <th style="width:220px">Assessment Category</th>
            <th style="width:110px">Bobot Kategori (%)</th>
            <th style="width:260px">Deskripsi</th>

            {{-- Header CPMK --}}
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

            <th style="width:160px">
              Total CPMK<br>(≈ 100%)<br>
              <small class="text-muted">+ bobot kategori dari CPMK</small>
            </th>
          </tr>
        </thead>

        <tbody>
          @foreach($cats as $cat)
            @php
              $currWeight     = (float)($catWeights[$cat->id] ?? 0);  // bobot kategori (manual)
              $rowTotal       = 0;                                   // total kontribusi CPMK di baris ini
              $weightedByCpmk = 0;                                   // Σ (kontribusi × bobot CPMK)
            @endphp

            <tr>
              {{-- Nama kategori --}}
              <td>
                <div class="fw-semibold">{{ $cat->code }}</div>
                <div class="text-muted small">{{ $cat->name }}</div>
              </td>

              {{-- Bobot kategori (%) --}}
              <td>
                <input
                  type="number" step="0.01" min="0" max="100"
                  name="cat_weight[{{ $cat->id }}]"
                  class="form-control form-control-sm text-end"
                  value="{{ old('cat_weight.'.$cat->id, $currWeight) }}"
                >
              </td>

              {{-- Deskripsi kategori (opsional) --}}
              <td>
                <input
                  name="desc[{{ $cat->id }}]"
                  class="form-control form-control-sm"
                  placeholder="Deskripsi singkat (opsional)"
                  value="{{ old('desc.'.$cat->id) }}"
                >
              </td>

              {{-- Matriks CPMK × kategori --}}
              @foreach($clos as $clo)
                @php
                  $val = (float)($weights[$cat->id][$clo->id] ?? 0);        // kontribusi (0–100)
                  $rowTotal += $val;

                  // implementasi rumus: Σ (kontribusi × bobot CPMK)
                  $weightedByCpmk += ($val / 100) * (float)($clo->weight_percent ?? 0);
                @endphp
                <td>
                  <input
                    type="number" step="0.01" min="0" max="100"
                    name="weights[{{ $cat->id }}][{{ $clo->id }}]"
                    class="form-control form-control-sm text-end"
                    value="{{ old("weights.$cat->id.$clo->id", $val > 0 ? $val : '') }}"
                    placeholder="–"
                  >
                </td>
              @endforeach

              {{-- Total CPMK di baris + bobot kategori hasil dari CPMK --}}
              <td class="text-center fw-semibold">
                <div>
                  <span class="row-total">{{ number_format($rowTotal, 2) }}</span>%
                  @if($weightedByCpmk > 0)
                    <div class="small text-muted">
                      ≈ {{ number_format($weightedByCpmk, 2) }}% bobot kategori ini
                    </div>
                  @endif
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

            {{-- Total vertikal per CPMK --}}
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
      <a href="{{ route('rps.create.step', 2) }}" class="btn btn-outline-secondary">← Kembali</a>
      <button type="submit" class="btn btn-primary">Simpan & Lanjut ke Step 4</button>
    </div>
  </form>
</div>

{{-- ===== JS kecil: update total real-time ===== --}}
<script>
function num(v){ return parseFloat(v) || 0; }

document.addEventListener('input', function(e){
  // update matriks CPMK × kategori
  if (e.target.matches('input[name^="weights["]')) {
    const td = e.target.closest('td');
    const tr = td.closest('tr');
    const table = tr.closest('table');

    // total baris
    let row = 0;
    tr.querySelectorAll('input[name^="weights["]').forEach(i => row += num(i.value));
    const rowSpan = tr.querySelector('.row-total');
    if (rowSpan) rowSpan.textContent = (Math.round(row * 100) / 100).toFixed(2);

    // total kolom CPMK (index kolom sekarang)
    const colIndex = Array.from(tr.children).indexOf(td);
    let col = 0;
    table.querySelectorAll('tbody tr').forEach(r => {
      const cell = r.children[colIndex];
      const inp = cell ? cell.querySelector('input') : null;
      if (inp) col += num(inp.value);
    });
    const footerCells = table.querySelectorAll('tfoot .col-total');
    const footerIndex = colIndex - 3; // 3 kolom pertama: kategori, bobot, deskripsi
    if (footerCells[footerIndex]) {
      footerCells[footerIndex].textContent = (Math.round(col * 100) / 100).toFixed(2);
    }
  }

  // update total bobot kategori
  if (e.target.name && e.target.name.startsWith('cat_weight[')) {
    let s = 0;
    document.querySelectorAll('input[name^="cat_weight["]').forEach(i => s += num(i.value));
    const sumCats = document.getElementById('sum-cats');
    if (sumCats) sumCats.textContent = (Math.round(s * 100) / 100).toFixed(2);
  }
});
</script>
@endsection
