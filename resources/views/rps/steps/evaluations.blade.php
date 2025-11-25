@extends('layouts.app')

@section('content')
<div class="container-xxl">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0">Rencana Penilaian</h4>
    <div class="text-muted small">RPS #{{ $rps->id }}</div>
  </div>

  <div class="progress mb-3" style="height:10px;">
    <div class="progress-bar bg-primary" style="width:66%"></div>
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

  <form method="POST" action="{{ route('rps.store.step', 5) }}" class="card shadow-sm border-0">
    @csrf
    <div class="table-responsive">
      <table class="table table-bordered align-middle mb-0">
        <thead class="table-light text-center">
          <tr>
            <th style="width:180px;">Komponen Penilaian</th>
            <th style="width:130px;">Bobot Penilaian (%)</th>
            <th>Deskripsi Teknik Penilaian dan Instrumen</th>
            <th style="width:140px;">Tenggat Waktu</th>
          </tr>
        </thead>
        <tbody>
          @php $total = 0; @endphp
          @forelse($cats as $cat)
            @php
              $w = (float) ($assess[$cat->id] ?? 0);
              $total += $w;
              $row = $existing[$cat->id] ?? null;
            @endphp
            <tr>
              <td class="fw-semibold">{{ $cat->code }} — {{ $cat->name }}</td>

              {{-- ✅ tampilan badge biru untuk bobot --}}
              <td class="text-center">
                <span class="badge text-bg-primary px-3 py-2">
                  {{ number_format($w, 2) }}%
                </span>
              </td>

              <td>
                <textarea name="evaluations[{{ $cat->id }}][method]" class="form-control mb-2"
                  rows="2"
                  placeholder="Contoh: keaktifan selama kuliah, proyek akhir (PBL/CBL/IBL), tugas artikel/portofolio/kuis, dsb.">{{ old("evaluations.$cat->id.method", $row->method ?? '') }}</textarea>

                <textarea name="evaluations[{{ $cat->id }}][criteria]" class="form-control"
                  rows="2"
                  placeholder="Kriteria/rubrik (ketepatan, kelengkapan, orisinalitas, ketercapaian CPMK, dsb.)">{{ old("evaluations.$cat->id.criteria", $row->criteria ?? '') }}</textarea>
              </td>

              <td class="text-center">
                <input type="number" min="1" max="30" class="form-control text-center"
                       name="evaluations[{{ $cat->id }}][due_week]"
                       value="{{ old("evaluations.$cat->id.due_week", $row->due_week ?? '') }}"
                       placeholder="Week">
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="text-center py-4 text-muted">
                Tidak ada kategori dengan bobot &gt; 0%. Silakan atur di Step 3.
              </td>
            </tr>
          @endforelse
        </tbody>
        <tfoot>
          <tr class="table-light">
            <th class="text-end">Total</th>
            <th class="text-center">
              <span class="badge text-bg-secondary px-3 py-2">
                {{ number_format($total,2) }}%
              </span>
            </th>
            <th colspan="2"></th>
          </tr>
        </tfoot>
      </table>
    </div>

    <div class="card-footer bg-light d-flex justify-content-between">
      <a href="{{ route('rps.create.step', 4) }}" class="btn btn-outline-secondary">← Kembali</a>
      <button type="submit" class="btn btn-primary">Simpan & Lanjut ke Step 6 (RPM)</button>
    </div>
  </form>
</div>
@endsection
