{{-- resources/views/rps/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-xxl">

  {{-- Custom style badge status pakai warna ala UPH --}}
  <style>
    .status-badge {
      border-radius: 999px;
      font-size: 0.78rem;
      font-weight: 600;
      padding: 0.3rem 0.85rem;
      text-transform: capitalize;
    }
    /* Palet kira-kira UPH: navy, gold, hijau, merah */
    .status-badge-draft {
      background: #e0e4ec;
      color: #495057;
    }
    .status-badge-submitted {
      background: #003366; /* UPH navy */
      color: #ffffff;
    }
    .status-badge-revisi {
      background: #ffb347; /* gold-ish */
      color: #4a2b00;
    }
    .status-badge-forwarded {
      background: #4da3ff; /* light blue */
      color: #083763;
    }
    .status-badge-approved {
      background: #1b8f3a; /* deep green */
      color: #ffffff;
    }
    .status-badge-rejected {
      background: #c62828; /* merah */
      color: #ffffff;
    }
  </style>

  <div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0">Daftar RPS</h4>
    <a href="{{ route('rps.start') }}" class="btn btn-primary">
      <i class="bi bi-plus-lg me-1"></i> Buat RPS Baru
    </a>
  </div>

  {{-- Toolbar: Search & Filter --}}
  <form method="GET" class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
      <div class="row g-2 align-items-end">
        <div class="col-md-6">
          <label class="form-label">Cari</label>
          <input type="text" name="q" class="form-control"
                 placeholder="Judul / Nama atau Kode Mata Kuliah"
                 value="{{ $filters['q'] ?? '' }}">
        </div>

        <div class="col-md-3">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            @php $s = $filters['status'] ?? ''; @endphp
            <option value="">Semua</option>
            <option value="draft"     @selected($s==='draft')>Draft</option>
            <option value="submitted" @selected($s==='submitted')>Submitted</option>
            <option value="revisi"    @selected($s==='revisi')>Revisi</option>
            <option value="forwarded" @selected($s==='forwarded')>Forwarded</option>
            <option value="approved"  @selected($s==='approved')>Approved</option>
            <option value="rejected"  @selected($s==='rejected')>Rejected</option>
          </select>
        </div>

        <div class="col-md-3 d-flex gap-2">
          <button class="btn btn-outline-primary w-100">
            <i class="bi bi-search me-1"></i> Terapkan
          </button>
          <a href="{{ route('rps.index') }}" class="btn btn-outline-secondary">
            Reset
          </a>
        </div>
      </div>
    </div>
  </form>

  <div class="card shadow-sm border-0">
    @if($rpsList->count())
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width:80px">ID</th>
              <th>Course</th>
              <th style="width:140px">Status</th>
              <th style="width:160px">Dibuat</th>
              <th class="text-end" style="width:160px">Aksi</th>
            </tr>
          </thead>
          <tbody>
          @foreach($rpsList as $item)
            @php
              $statusClass = 'status-badge-' . ($item->status ?? 'draft');
            @endphp
            <tr>
              <td>#{{ $item->id }}</td>

              <td>
                <div class="fw-semibold">
                  {{ $item->course->name ?? ($item->title ?? '-') }}
                </div>
                <div class="text-muted small">
                  {{ $item->course->code ?? '—' }}
                </div>
              </td>

              <td>
                <span class="status-badge {{ $statusClass }}">
                  {{ $item->status ?? 'draft' }}
                </span>
              </td>

              <td>{{ $item->created_at?->format('d M Y') }}</td>

              <td class="text-end">
                <a href="{{ route('rps.resume', [$item, 1]) }}"
                   class="btn btn-sm btn-outline-primary">
                  Lanjutkan
                </a>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>

      <div class="card-footer bg-light">
        {{ $rpsList->links() }}
      </div>
    @else
      <div class="card-body text-center py-5">
        <div class="mb-2 fs-5">Belum ada RPS.</div>
        <div class="text-muted mb-3">
          Mulai dengan membuat RPS pertama Anda.
        </div>
        <a href="{{ route('rps.start') }}" class="btn btn-primary">
          <i class="bi bi-plus-lg me-1"></i> Buat RPS Baru
        </a>
      </div>
    @endif
  </div>
</div>
@endsection
