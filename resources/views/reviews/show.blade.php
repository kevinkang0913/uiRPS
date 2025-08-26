@extends('layouts.app')

@section('content')
  @include('partials._page_header', [
    'title' => 'Review RPS',
    'subtitle' => 'Form review untuk RPS tertentu'
  ])

  <div class="card">
    <div class="card-body">
      <h5>Judul: RPS Pemrograman Web</h5>
      <p><strong>Mata Kuliah:</strong> SI5678 - Web</p>
      <p><strong>Dosen:</strong> Dr. Clara</p>
      <p><strong>Semester:</strong> 2024/2025 - Ganjil</p>

      <hr>

      <form>
        <div class="mb-3">
          <label class="form-label">Komentar Review</label>
          <textarea class="form-control" rows="4" placeholder="Tuliskan masukan atau catatan..."></textarea>
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-success"><i class="bi bi-check2"></i> Mark as Reviewed</button>
          <button type="submit" class="btn btn-danger"><i class="bi bi-x-circle"></i> Reject</button>
          <a href="/reviews" class="btn btn-secondary">Kembali</a>
        </div>
      </form>
    </div>
  </div>
@endsection
