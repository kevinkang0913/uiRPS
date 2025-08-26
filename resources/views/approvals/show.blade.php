@extends('layouts.app')

@section('content')
  @include('partials._page_header', [
    'title' => 'Approval RPS',
    'subtitle' => 'Form approval Kaprodi untuk RPS'
  ])

  <div class="card">
    <div class="card-body">
      <h5>Judul: RPS Manajemen Strategis</h5>
      <p><strong>Mata Kuliah:</strong> SI4321 - Manajemen</p>
      <p><strong>Dosen:</strong> Dr. Budi</p>
      <p><strong>Semester:</strong> 2024/2025 - Ganjil</p>

      <hr>

      <form>
        <div class="mb-3">
          <label class="form-label">Catatan Approval</label>
          <textarea class="form-control" rows="4" placeholder="Tuliskan catatan persetujuan..."></textarea>
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-success"><i class="bi bi-check2-circle"></i> Approve</button>
          <button type="submit" class="btn btn-danger"><i class="bi bi-x-circle"></i> Reject</button>
          <a href="/approvals" class="btn btn-secondary">Kembali</a>
        </div>
      </form>
    </div>
  </div>
@endsection
