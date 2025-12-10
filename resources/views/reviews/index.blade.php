@extends('layouts.app')

@section('content')
<div class="container-xxl">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Review RPS — CTL</h4>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width:70px;">ID</th>
              <th>Mata Kuliah</th>
              <th>Prodi / Fakultas</th>
              <th>Dosen Pengampu</th>
              <th style="width:130px;">Status</th>
              <th style="width:120px;" class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($rpsList as $rps)
              <tr>
                <td>#{{ $rps->id }}</td>
                <td>
                  <div class="fw-semibold">{{ $rps->course->code ?? '' }} {{ $rps->course->name ?? '' }}</div>
                  <div class="small text-muted">
                    Tahun {{ $rps->academic_year }} • Semester {{ $rps->semester }}
                  </div>
                </td>
                <td class="small">
                  {{ $rps->course->program->name ?? '-' }}<br>
                  <span class="text-muted">{{ $rps->course->program->faculty->name ?? '-' }}</span>
                </td>
                <td class="small">
                  {{ $rps->owner->name ?? '-' }}
                </td>
                <td>
                  <span class="badge bg-info-subtle text-info">
                    {{ strtoupper($rps->status) }}
                  </span>
                </td>
                <td class="text-end">
                  <a href="{{ route('reviews.edit', $rps) }}" class="btn btn-sm btn-primary">
                    Review
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-muted small py-4">
                  Belum ada RPS yang menunggu review.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    @if($rpsList instanceof \Illuminate\Pagination\LengthAwarePaginator)
      <div class="card-footer">
        {{ $rpsList->links() }}
      </div>
    @endif
  </div>

</div>
@endsection
