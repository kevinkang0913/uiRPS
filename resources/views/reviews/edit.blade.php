@extends('layouts.app')
@section('content')
<style>
  .rubric-table { table-layout: fixed; }
  .rubric-table th, .rubric-table td { vertical-align: middle; }
  .scale-cell {
    cursor: pointer;
    user-select: none;
    border: 1px solid #dee2e6;
    padding: .5rem;
    min-height: 56px;
  }
  .scale-cell.active {
    background: #2e7d32 !important; /* hijau */
    color: #fff !important;
    font-weight: 600;
  }
  .col-no { width: 48px; }
  .col-indikator { width: 220px; }
  .col-kriteria { width: 360px; }
  .col-skor { width: 64px; text-align:center; }
  .col-catatan { width: 280px; }
  .col-keterangan { width: 280px; }
  .col-versi { width: 90px; }
  .col-verifikasi { width: 110px; }
  .header-rotate { text-align:center; }
</style>

@php
  $rubric = config('rps_rubric');
  $indicators = $rubric['indicators'] ?? [];
@endphp

<div class="container-fluid">
  <h2 class="mb-3">Review RPS: {{ $rps->title }}</h2>

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('reviews.store', $rps->id) }}" id="reviewForm">
    @csrf

    <div class="mb-2 small text-muted">
      Rubric versi: <strong>{{ $rubric['version'] ?? '-' }}</strong>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered rubric-table align-middle">
        <thead class="table-light">
          <tr>
            <th class="col-no text-center">No</th>
            <th class="col-indikator">Indikator</th>
            <th class="col-kriteria">Kriteria</th>
            <th class="text-center" colspan="5">SKALA</th>
            <th class="col-skor">SKOR</th>
            <th class="col-catatan">CATATAN</th>
            <th class="col-keterangan">Keterangan</th>
            <th class="col-versi">Versi RPS</th>
            <th class="col-verifikasi">Verifikasi</th>
          </tr>
          <tr class="table-light">
            <th></th><th></th><th></th>
            <th class="text-center">5</th>
            <th class="text-center">4</th>
            <th class="text-center">3</th>
            <th class="text-center">2</th>
            <th class="text-center">1</th>
            <th></th><th></th><th></th><th></th>
          </tr>
        </thead>

        <tbody id="rubricBody">
          @foreach($indicators as $ind)
            @php $rowspan = count($ind['criteria']); @endphp

            @foreach($ind['criteria'] as $rowIdx => $crit)
              @php
                $key   = $crit['key'];
                $sel   = old("selections.$key"); // restore pilihan jika validasi gagal
                $scale = $crit['scale'];
              @endphp
              <tr data-key="{{ $key }}">
                @if ($rowIdx === 0)
                  <td class="text-center" rowspan="{{ $rowspan }}">{{ $ind['no'] }}</td>
                  <td rowspan="{{ $rowspan }}">{{ $ind['title'] }}</td>
                @endif

                <td>{{ $crit['label'] }}</td>

                {{-- SKALA 5..1 --}}
                @foreach([5,4,3,2,1] as $level)
                  <td class="scale-cell text-wrap {{ ($sel == $level) ? 'active' : '' }}"
                      data-level="{{ $level }}"
                      onclick="selectScale(this)">
                    {{ $scale[$level] ?? '' }}
                  </td>
                @endforeach

                {{-- SKOR --}}
                <td class="col-skor">
                  <span class="score-val fw-bold">{{ $sel ? $sel : '' }}</span>
                  <input type="hidden" name="selections[{{ $key }}]" value="{{ $sel ?? '' }}">
                </td>

                {{-- CATATAN --}}
                <td>
                  <input type="text" class="form-control form-control-sm"
                         name="notes[{{ $key }}]"
                         value="{{ old("notes.$key") }}"
                         placeholder="Catatan reviewer (opsional)">
                </td>

                {{-- Keterangan (hint dari config) --}}
                <td class="small text-muted">{{ $crit['notes_hint'] ?? '' }}</td>

                {{-- Versi RPS --}}
                <td>
                  <input type="text" class="form-control form-control-sm"
                         name="rps_version[{{ $key }}]" value="{{ old("rps_version.$key") }}">
                </td>

                {{-- Verifikasi (checkbox) --}}
                <td class="text-center">
                  <input class="form-check-input" type="checkbox"
                         name="verified[{{ $key }}]" value="1"
                         {{ old("verified.$key") ? 'checked' : '' }}>
                </td>
              </tr>
            @endforeach
          @endforeach
        </tbody>

        {{-- Total skor (opsional penjumlahan sederhana 5..1, kalau mau bobot silakan aktivasi di controller) --}}
        <tfoot>
          <tr class="table-light">
            <th colspan="8" class="text-end">Total Skor (penjumlahan level 5..1):</th>
            <th class="text-center"><span id="totalScore">0</span></th>
            <th colspan="4"></th>
          </tr>
        </tfoot>
      </table>
    </div>

    <div class="mb-3">
      <label class="form-label">Komentar Umum</label>
      <textarea name="overall_comment" class="form-control" rows="3">{{ old('overall_comment') }}</textarea>
    </div>

    <div class="d-flex justify-content-between">
      <a href="{{ route('reviews.index') }}" class="btn btn-secondary">Batal</a>
      <div class="d-flex gap-2">
        <input type="hidden" name="decision" id="decisionInput" value="revisi">
        <button type="button" class="btn btn-warning" onclick="submitDecision('revisi')">Minta Revisi</button>
        <button type="button" class="btn btn-primary" onclick="submitDecision('forwarded')">Teruskan ke Kaprodi</button>
      </div>
    </div>
  </form>
</div>

<script>
function selectScale(td) {
  const tr = td.closest('tr');
  tr.querySelectorAll('.scale-cell').forEach(c => c.classList.remove('active'));
  td.classList.add('active');

  const level = td.dataset.level;
  tr.querySelector('.score-val').textContent = level;
  tr.querySelector('input[type=hidden][name^="selections"]').value = level;

  recalcTotal();
}

function recalcTotal() {
  let total = 0;
  document.querySelectorAll('#rubricBody tr').forEach(tr => {
    const v = tr.querySelector('.score-val')?.textContent || '';
    const n = parseInt(v, 10);
    if (!isNaN(n)) total += n;
  });
  document.getElementById('totalScore').textContent = total;
}

function submitDecision(dec) {
  document.getElementById('decisionInput').value = dec;
  document.getElementById('reviewForm').submit();
}

// init total on load
document.addEventListener('DOMContentLoaded', recalcTotal);
</script>
@endsection
