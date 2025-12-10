@extends('layouts.app')

@section('content')
<div class="container-xxl">

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
        Beri keputusan untuk RPS berikut
      </div>
    </div>
    <a href="{{ route('approvals.index') }}" class="btn btn-outline-secondary btn-sm">
      ← Kembali ke daftar approval
    </a>
  </div>

  @php
    $status      = $rps->status ?? 'draft';
    $statusClass = 'status-badge-' . $status;
    $statusLabel = ucfirst(str_replace('_',' ', $status));
  @endphp

  {{-- Info singkat RPS --}}
  <div class="card mb-3 border-0 shadow-sm">
    <div class="card-body">
      <div class="row g-3 align-items-center">
        <div class="col-md-6">
          <div class="fw-semibold">
            {{ $rps->course->name ?? ($rps->title ?? 'Tanpa judul') }}
          </div>
          <div class="text-muted small">
            Kode: {{ $rps->course->code ?? '—' }}<br>
            Program Studi: {{ $rps->course->program->name ?? '—' }}
          </div>
        </div>
        <div class="col-md-3">
          <div class="text-muted small mb-1">Status RPS</div>
          <span class="status-badge {{ $statusClass }}">
            {{ $statusLabel }}
          </span>
        </div>
        <div class="col-md-3">
          <div class="text-muted small mb-1">Flag alur</div>
          @if($rps->is_reviewed_by_ctl ?? false)
            <span class="badge-ctl-flag badge-ctl-reviewed">
              Via CTL (sudah direview)
            </span>
          @else
            <span class="badge-ctl-flag badge-ctl-not-reviewed">
              Direct (belum via CTL)
            </span>
          @endif
        </div>
      </div>

      <div class="mt-3">
        {{-- Tombol buka modal preview RPS --}}
        <button type="button"
                class="btn btn-sm btn-outline-secondary"
                data-bs-toggle="modal"
                data-bs-target="#modalRpsPreview">
          <i class="bi bi-eye"></i> Lihat RPS (Preview)
        </button>
      </div>
    </div>
  </div>

  {{-- Ringkasan review CTL (kalau ada) --}}
  @isset($lastReview)
    <div class="card mb-3 border-0 shadow-sm">
      <div class="card-header bg-light">
        <div class="d-flex justify-content-between align-items-center">
          <span class="fw-semibold">Ringkasan Review CTL Terakhir</span>
          <span class="text-muted small">
            Oleh: {{ $lastReview->reviewer?->name ?? 'CTL' }}
            · {{ $lastReview->created_at?->format('d M Y H:i') }}
          </span>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-3">
            <div class="text-muted small">Status Review</div>
            <div class="fw-semibold">
              {{ ucfirst(str_replace('_',' ', $lastReview->status ?? '-')) }}
            </div>
          </div>
          <div class="col-md-3">
            <div class="text-muted small">Skor Akhir</div>
            <div class="fw-semibold">
              {{ $lastReview->final_score ?? '-' }}
            </div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">Catatan Umum</div>
            <div class="small">
              {{ $lastReview->general_notes ?? '—' }}
            </div>
          </div>
        </div>
      </div>
    </div>
  @endisset

  {{-- Form approval --}}
  <div class="card border-0 shadow-sm">
    <form method="POST" action="{{ route('approvals.store', $rps) }}">
      @csrf
      <div class="card-body">

        @if($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0 small">
              @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <div class="mb-3">
          <label class="form-label">Keputusan Kaprodi <span class="text-danger">*</span></label>
          @php
            $currentDecision = old('status', $lastApproval->status ?? '');
          @endphp
          <select name="status" class="form-select @error('status') is-invalid @enderror" required>
            <option value="">-- Pilih keputusan --</option>
            <option value="approved"     @selected($currentDecision === 'approved')>Approved</option>
            <option value="not_approved" @selected($currentDecision === 'not_approved')>Not Approved</option>
          </select>
          @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="mb-3">
          <label class="form-label">Catatan Kaprodi (opsional)</label>
          <textarea name="notes"
                    rows="4"
                    class="form-control @error('notes') is-invalid @enderror"
                    placeholder="Tuliskan alasan / catatan tambahan untuk dosen">{{ old('notes', $lastApproval->notes ?? '') }}</textarea>
          @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <div class="form-text small">
            Catatan ini akan menjadi referensi dosen ketika melakukan revisi.
          </div>
        </div>

      </div>
      <div class="card-footer bg-light d-flex justify-content-between">
        <a href="{{ route('approvals.index') }}" class="btn btn-outline-secondary">
          Batal
        </a>
        <button type="submit" class="btn btn-primary">
          Simpan Keputusan
        </button>
      </div>
    </form>
  </div>

</div>

{{-- Modal preview RPS --}}
<div class="modal fade" id="modalRpsPreview" tabindex="-1" aria-labelledby="modalRpsPreviewLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalRpsPreviewLabel">
          Preview RPS — {{ $rps->course->code ?? '' }} {{ $rps->course->name ?? $rps->title ?? '' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body p-0">
        {{-- Pakai iframe untuk reuse halaman rps.show --}}
        <iframe
        src="{{ route('rps.show', $rps) }}?embed=1"
        style="border:0; width:100%; min-height:75vh;"
      ></iframe>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          Tutup
        </button>
      </div>
    </div>
  </div>
</div>

@endsection
