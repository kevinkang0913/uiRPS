@extends('layouts.app')
@section('content')
<div class="container py-4">
  <h3 class="mb-3">Import Course Catalog</h3>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card mb-4">
    <div class="card-body">
      <form action="{{ route('admin.import.courses.real') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
          <label class="form-label">Upload Excel File (.xlsx)</label>
          <input type="file" name="file" class="form-control" required>
          @error('file') <small class="text-danger">{{ $message }}</small> @enderror
        </div>
        <button class="btn btn-primary"><i class="bi bi-upload"></i> Import Sekarang</button>
      </form>
    </div>
  </div>

  @if($summary)
  <div class="card">
    <div class="card-body">
      <h5 class="mb-3">Hasil Import:</h5>
      <ul>
        <li>Rows scanned: <b>{{ $summary['rows_scanned'] ?? '-' }}</b></li>
        <li>Faculties imported: <b>{{ $summary['faculties'] }}</b></li>
        <li>Programs imported: <b>{{ $summary['programs'] }}</b></li>
        <li>Courses imported: <b>{{ $summary['courses'] }}</b></li>
      </ul>
      @if(!empty($summary['note']))
        <div class="alert alert-warning mt-2">{{ $summary['note'] }}</div>
      @endif
    </div>
  </div>
@endif

</div>
@endsection
