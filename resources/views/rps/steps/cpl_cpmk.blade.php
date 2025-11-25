{{-- resources/views/rps/steps/cpl_cpmk.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-xxl">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Pembobotan CPL dan CPMK</h4>
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

  <form method="POST" action="{{ route('rps.cpl_cpmk.update', $rps) }}" class="card shadow-sm border-0">
    @csrf

    <div class="card-header bg-white">
      <h5 class="mb-0">Bobot CPL dan CPMK</h5>
      <div class="small text-muted">
        Isi persentase kontribusi CPMK terhadap tiap CPL. Total baris &amp; kolom akan dihitung otomatis.
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
          <thead class="table-light text-center">
            <tr>
              <th style="width:80px;">Kode CPMK</th>
              <th>Deskripsi CPMK</th>

              {{-- CPL sebagai kolom --}}
              @foreach($plos as $plo)
                <th>
                  {{ $plo->code }}
                  <div class="small text-muted">
                    {{ Str::limit($plo->description, 40) }}
                  </div>
                </th>
              @endforeach

              <th style="width:120px;">Total CPMK<br>(baris)</th>
            </tr>
          </thead>

          <tbody>
            @php
              // siapkan array untuk total per CPL (kolom)
              $colTotals = [];
              foreach ($plos as $plo) {
                  $colTotals[$plo->id] = 0;
              }
            @endphp

            @foreach($clos as $clo)
              @php $rowTotal = 0; @endphp
              <tr>
                <td class="fw-semibold">CPMK{{ $clo->no }}</td>
                <td>{{ $clo->description }}</td>

                @foreach($plos as $plo)
                  @php
                    $val = (float)($weights[$plo->id][$clo->id] ?? 0);
                    $rowTotal += $val;
                    $colTotals[$plo->id] += $val;
                  @endphp
                  <td>
                    <input
                      type="number" step="0.01" min="0" max="100"
                      name="weights[{{ $plo->id }}][{{ $clo->id }}]"
                      class="form-control form-control-sm text-end weight-input"
                      value="{{ $val > 0 ? $val : '' }}"
                      placeholder="–"
                    >
                  </td>
                @endforeach

                <td class="text-center fw-semibold">
                  <span class="row-total">{{ number_format($rowTotal, 2) }}</span>%
                </td>
              </tr>
            @endforeach
          </tbody>

          <tfoot class="table-light">
            <tr>
              <th colspan="2" class="text-end">Total per CPL (kolom)</th>
              @foreach($plos as $plo)
                <th class="text-center">
                  <span class="col-total"
                        data-plo="{{ $plo->id }}">{{ number_format($colTotals[$plo->id] ?? 0, 2) }}</span>%
                </th>
              @endforeach
              <th></th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <div class="card-footer bg-light d-flex justify-content-between">
      <a href="{{ route('rps.create.step', 2) }}" class="btn btn-outline-secondary">← Kembali ke Step 2</a>
      <button type="submit" class="btn btn-primary">Simpan Bobot CPL–CPMK</button>
    </div>
  </form>
</div>

<script>
function num(v){ return parseFloat(v) || 0; }

document.addEventListener('input', function(e){
  if (!e.target.classList.contains('weight-input')) return;

  const td   = e.target.closest('td');
  const tr   = td.closest('tr');
  const table = tr.closest('table');

  // update total baris
  let row = 0;
  tr.querySelectorAll('.weight-input').forEach(inp => row += num(inp.value));
  const rowSpan = tr.querySelector('.row-total');
  if (rowSpan) rowSpan.textContent = (Math.round(row * 100) / 100).toFixed(2);

  // update total kolom (per CPL)
  const colIndex = Array.from(tr.children).indexOf(td);
  // kolom pertama = kode, kedua = deskripsi, jadi offset 2
  const colOffset = colIndex - 2;

  const heads = table.querySelectorAll('thead tr th');
  const ploId = heads[colIndex]?.getAttribute('data-plo-id'); // kalau mau
  let col = 0;

  table.querySelectorAll('tbody tr').forEach(r => {
    const c = r.children[colIndex];
    const inp = c ? c.querySelector('.weight-input') : null;
    if (inp) col += num(inp.value);
  });

  const footerCell = table.querySelectorAll('tfoot .col-total')[colOffset];
  if (footerCell) {
    footerCell.textContent = (Math.round(col * 100) / 100).toFixed(2);
  }
});
</script>
@endsection
