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

    /* status: draft */
    .status-badge-draft {
      background: #e0e4ec;
      color: #495057;
    }
    /* status: submitted */
    .status-badge-submitted {
      background: #003366; /* UPH navy */
      color: #ffffff;
    }
    /* status: reviewed (sudah diforward CTL, siap Kaprodi) */
    .status-badge-reviewed {
      background: #4da3ff; /* light blue */
      color: #083763;
    }
    /* status: need_revision (diminta revisi CTL / Kaprodi) */
    .status-badge-need_revision {
      background: #ffb347; /* gold-ish */
      color: #4a2b00;
    }
    /* status: revision_submitted (revisi sudah dikirim dosen) */
    .status-badge-revision_submitted {
      background: #b39ddb; /* ungu lembut, biar beda */
      color: #2e1a47;
    }
    /* status: approved (final kaprodi) */
    .status-badge-approved {
      background: #1b8f3a; /* deep green */
      color: #ffffff;
    }
    /* status: not_approved (ditolak kaprodi) */
    .status-badge-not_approved {
      background: #c62828; /* merah */
      color: #ffffff;
    }

    /* badge kecil penanda review CTL */
    .badge-ctl-flag {
      border-radius: 999px;
      font-size: 0.7rem;
      padding: 0.18rem 0.55rem;
      margin-left: 0.25rem;
      font-weight: 500;
    }
    .badge-ctl-reviewed {
      background: #e5f6ea;
      color: #1b8f3a;
      border: 1px solid #c7e6d0;
    }
    .badge-ctl-not-reviewed {
      background: #f1f3f5;
      color: #6c757d;
      border: 1px solid #dde2e6;
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
          @php $s = $filters['status'] ?? ''; @endphp
          <select name="status" class="form-select">
            <option value="">Semua</option>
            <option value="draft"              @selected($s==='draft')>Draft</option>
            <option value="submitted"          @selected($s==='submitted')>Submitted</option>
            <option value="reviewed"           @selected($s==='reviewed')>Reviewed (CTL)</option>
            <option value="need_revision"      @selected($s==='need_revision')>Need Revision</option>
            <option value="revision_submitted" @selected($s==='revision_submitted')>Revision Submitted</option>
            <option value="approved"           @selected($s==='approved')>Approved</option>
            <option value="not_approved"       @selected($s==='not_approved')>Not Approved</option>
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
              <th style="width:220px">Status</th>
              <th style="width:160px">Dibuat</th>
              <th class="text-end" style="width:160px">Aksi</th>
            </tr>
          </thead>
          <tbody>
          @foreach($rpsList as $item)
            @php
              $status = $item->status ?? 'draft';
              $statusClass = 'status-badge-' . $status;

              // label rapi (Draft, Need revision, dll)
              $statusLabel = ucfirst(str_replace('_', ' ', $status));
            @endphp
            <tr>
              <td>#{{ $item->id }}</td>

              <td>
                <div class="fw-semibold">
                  {{ $item->course->name ?? ($item->title ?? '-') }}
                </div>
                <div class="text-muted small">
                  {{ $item->course->code ?? '—' }}
                  {{-- Version badge --}}
                  <span class="ms-1 badge bg-secondary">
                    v{{ $item->version_no ?? 1 }}
                  </span>
                  @if($item->is_current)
                    <span class="ms-1 badge bg-success">Current</span>
                  @endif
                </div>
              </td>
              <td>
                <span class="status-badge {{ $statusClass }}">
                  {{ $statusLabel }}
                </span>

                {{-- Penanda sudah / belum direview CTL --}}
                @if($item->is_reviewed_by_ctl)
                  <span class="badge-ctl-flag badge-ctl-reviewed">
                    Reviewed CTL
                  </span>
                @else
                  <span class="badge-ctl-flag badge-ctl-not-reviewed">
                    Belum review CTL
                  </span>
                @endif
              </td>

              <td>{{ $item->created_at?->format('d M Y') }}</td>

              <td class="text-end">
              {{-- Tombol Show --}}
              <a href="{{ route('rps.show', $item) }}"
                class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-eye"></i> Lihat
              </a>
              {{-- Tombol Clone --}}
              @can('clone', $item)
                <a href="{{ route('rps.clone.form', $item) }}"
                  class="btn btn-sm btn-outline-primary">
                  <i class="bi bi-copy"></i> Clone
                </a>
              @endcan
              {{-- Tombol Lanjutkan (resume wizard) --}}
              <a href="{{ route('rps.resume.auto', $item) }}"
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
