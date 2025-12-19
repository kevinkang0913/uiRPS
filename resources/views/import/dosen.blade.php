@extends('layouts.app')

@section('content')
<div class="container-xxl">

  <div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0">Import Dosen</h4>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
      ← Kembali ke User Management
    </a>
  </div>

  {{-- Flash success --}}
  @if(session('success'))
    <div class="alert alert-success">
      {{ session('success') }}
    </div>
  @endif

  {{-- Error --}}
  @if($errors->any())
    <div class="alert alert-danger">
      {{ $errors->first() }}
    </div>
  @endif

  {{-- ======================
       FORM UPLOAD CSV
       ====================== --}}
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
      <div class="text-muted small mb-2">
        Header yang didukung:
        <code>NAME_DISPLAY</code> dan <code>Email</code>
        (delimiter <code>;</code> atau <code>,</code> otomatis).
      </div>

      <form method="POST"
            action="{{ route('import.dosen.preview') }}"
            enctype="multipart/form-data">
        @csrf

        <div class="row g-2 align-items-end">
          <div class="col-md-8">
            <label class="form-label">File CSV</label>
            <input type="file" name="file" class="form-control" required>
          </div>
          <div class="col-md-4">
            <button class="btn btn-primary w-100">
              Preview Import
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- ======================
       PREVIEW + STATS
       ====================== --}}
  @isset($preview)

    {{-- Statistik --}}
    <div class="alert alert-info">
      <strong>Total baris:</strong> {{ $stats['total'] }} |
      <strong>Akan diimport:</strong> {{ $stats['will_import'] }} |
      <strong>Diskip:</strong> {{ $stats['skipped'] }}
    </div>

    {{-- Warning performa --}}
    <div class="alert alert-warning">
      Preview menampilkan <strong>SELURUH data</strong>.  
      Jika data sangat besar, halaman ini bisa terasa berat.
    </div>

    {{-- TABEL PREVIEW --}}
    <div class="card border-0 shadow-sm mb-3">
      <div class="table-responsive" style="max-height:60vh; overflow:auto;">
        <table class="table table-sm table-bordered align-middle mb-0">
          <thead class="table-light sticky-top">
            <tr>
              <th style="width:50px;">#</th>
              <th>Nama</th>
              <th>Email</th>
              <th style="width:120px;">Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($preview as $i => $row)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['email'] }}</td>
                <td>
                  @if($row['status'] === 'import')
                    <span class="badge bg-success">Import</span>
                  @elseif($row['status'] === 'exists')
                    <span class="badge bg-secondary">Exists</span>
                  @else
                    <span class="badge bg-danger">Invalid</span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    {{-- ======================
         FORM PROSES IMPORT
         ====================== --}}
    <form method="POST" action="{{ route('import.dosen.process') }}">
      @csrf

      {{-- Kirim seluruh preview ke controller --}}
      <input type="hidden" name="data" value='@json($preview)'>

      <button class="btn btn-success">
        Proses Import Sekarang
      </button>
    </form>

  @endisset

</div>
@endsection
