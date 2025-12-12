@extends('layouts.app')

@section('content')
<div class="container-xxl py-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">Clone RPS</h4>
      <div class="text-muted small">
        RPS #{{ $rps->id }} — {{ $rps->course->code ?? '' }} {{ $rps->course->name ?? '' }}
      </div>
    </div>
    <a href="{{ route('rps.show', $rps) }}" class="btn btn-outline-secondary btn-sm">← Back</a>
  </div>

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <form method="POST" action="{{ route('rps.clone.store', $rps) }}" class="row g-3">
        @csrf

        <div class="col-md-4">
          <label class="form-label">Academic Year</label>
          <input type="text" name="academic_year" class="form-control"
                 value="{{ old('academic_year', $rps->academic_year) }}" required>
        </div>

        <div class="col-md-2">
          <label class="form-label">Semester</label>
          <input type="number" name="semester" class="form-control"
                 value="{{ old('semester', $rps->semester) }}" min="1" max="14" required>
        </div>

        <div class="col-12">
          <button class="btn btn-primary">
            <i class="bi bi-copy"></i> Clone
          </button>
        </div>
      </form>

      <div class="text-muted small mt-3">
        Clone akan membuat RPS baru status <b>draft</b>, menyalin semua isi (CPL/CPMK, assessment, referensi, RPM, kontrak),
        lalu kamu akan diarahkan ke wizard untuk lanjut edit.
      </div>
    </div>
  </div>

</div>
@endsection
