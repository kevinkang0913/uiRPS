@extends('layouts.app')

@section('content')
<div class="container-xxl">

  @php
    // fallback kalau view terpanggil tanpa data dari controller
    $rpsList = $rpsList ?? collect();
    $filters = $filters ?? [];
  @endphp

  <style>
    .status-badge {
      border-radius: 999px;
      font-size: 0.78rem;
      font-weight: 600;
      padding: 0.3rem 0.85rem;
      text-transform: capitalize;
    }
    .status-badge-draft {
      background:#e0e4ec;
      color:#495057;
    }
    .status-badge-submitted {
      background:#003366;
      color:#fff;
    }
    .status-badge-reviewed {
      background:#4da3ff;
      color:#083763;
    }
    .status-badge-revision_submitted {
      background:#ffb347;
      color:#4a2b00;
    }
    .status-badge-approved {
      background:#1b8f3a;
      color:#fff;
    }
    .status-badge-not_approved {
      background:#c62828;
      color:#fff;
    }

    .badge-ctl-flag {
      border-radius:999px;
      font-size:0.7rem;
      padding:0.18rem 0.55rem;
      font-weight:500;
    }
    .badge-ctl-reviewed {
      background:#e5f6ea;
      color:#1b8f3a;
      border:1px solid #c7e6d0;
    }
    .badge-ctl-not-reviewed {
      background:#f1f3f5;
      color:#6c757d;
      border:1px solid #dde2e6;
    }
  </style>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">Approval RPS — Kaprodi</h4>
      <div class="text-muted small">
        Lihat dan kelola approval RPS di prodi Anda
      </div>
    </div>
  </div>

  {{-- Filter --}}
  <form method="GET" class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
      <div class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label">Status RPS</label>
          @php $s = $filters['status'] ?? ''; @endphp
          <select name="status" class="form-select">
            <option value="">Semua</option>
            <option value="draft"              @selected($s==='draft')>Draft</option>
            <option value="submitted"          @selected($s==='submitted')>Submitted</option>
            <option value="reviewed"           @selected($s==='reviewed')>Reviewed (via CTL)</option>
            <option value="revision_submitted" @selected($s==='revision_submitted')>Revision Submitted</option>
            <option value="approved"           @selected($s==='approved')>Approved</option>
            <option value="not_approved"       @selected($s==='not_approved')>Not Approved</option>
          </select>
        </div>

        <div class="col-md-3 d-flex gap-2">
          <button class="btn btn-outline-primary w-100">
            <i class="bi bi-search me-1"></i> Terapkan
          </button>
          <a href="{{ route('approvals.index') }}" class="btn btn-outline-secondary">
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
              <th style="width:80px;">ID</th>
              <th>Mata Kuliah</th>
              <th style="width:160px;">Status</th>
              <th style="width:180px;">Flag CTL</th>
              <th style="width:160px;">Dibuat</th>
              <th class="text-end" style="width:200px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
          @foreach($rpsList as $item)
            @php
              $status       = $item->status ?? 'draft';
              $statusClass  = 'status-badge-' . $status;
              $statusLabel  = ucfirst(str_replace('_',' ', $status));
              $canApprove   = in_array($status, ['submitted','reviewed','revision_submitted']);
            @endphp
            <tr>
              <td>#{{ $item->id }}</td>
              <td>
                <div class="fw-semibold">
                  {{ $item->course->name ?? '-' }}
                </div>
                <div class="text-muted small">
                  {{ $item->course->code ?? '—' }}
                </div>
              </td>
              <td>
                <span class="status-badge {{ $statusClass }}">
                  {{ $statusLabel }}
                </span>
              </td>
              <td>
                @if($item->is_reviewed_by_ctl)
                  <span class="badge-ctl-flag badge-ctl-reviewed">
                    Via CTL
                  </span>
                @else
                  <span class="badge-ctl-flag badge-ctl-not-reviewed">
                    Direct (No CTL)
                  </span>
                @endif
              </td>
              <td>{{ $item->created_at?->format('d M Y') }}</td>
              <td class="text-end">
                <a href="{{ route('rps.show', $item) }}" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-eye"></i> Lihat RPS
                </a>

                @if($canApprove)
                  <a href="{{ route('approvals.edit', $item) }}" class="btn btn-sm btn-primary">
                    Approve / Not Approve
                  </a>
                @endif
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>

      {{-- Kalau pakai paginator --}}
      @if($rpsList instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="card-footer bg-light">
          {{ $rpsList->links() }}
        </div>
      @endif

    @else
      <div class="card-body text-center py-5">
        <div class="fs-5 mb-2">Belum ada RPS di prodi Anda untuk ditampilkan.</div>
      </div>
    @endif
  </div>
</div>
@endsection
