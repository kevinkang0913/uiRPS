@extends('layouts.app')

@section('content')
  @include('partials._page_header', ['title' => 'Dashboard', 'subtitle' => 'Overview Sistem RPS'])

  <div class="row g-4">
    <div class="col-md-3">
      <div class="card p-3 text-center">
        <i class="bi bi-journals fs-1 text-primary"></i>
        <h5 class="mt-2">Total RPS</h5>
        <h3>24</h3>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3 text-center">
        <i class="bi bi-person-video3 fs-1 text-success"></i>
        <h5 class="mt-2">Dosen Aktif</h5>
        <h3>12</h3>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3 text-center">
        <i class="bi bi-calendar2-week fs-1 text-warning"></i>
        <h5 class="mt-2">Semester Berjalan</h5>
        <h3>2024/2025 Ganjil</h3>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3 text-center">
        <i class="bi bi-check2-circle fs-1 text-info"></i>
        <h5 class="mt-2">Approved</h5>
        <h3>16</h3>
      </div>
    </div>
  </div>

  <div class="card mt-4">
    <div class="card-body">
      <h5 class="card-title mb-3">RPS Terbaru</h5>
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th>Judul</th>
            <th>Mata Kuliah</th>
            <th>Dosen</th>
            <th>Semester</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>RPS Sistem Basis Data</td>
            <td>SI1234 - Basis Data</td>
            <td>Dr. Andi</td>
            <td>2024/2025 - Ganjil</td>
            <td>@include('partials._status_badge', ['status' => 'submitted'])</td>
            <td><a href="/rps/1" class="btn btn-sm btn-outline-primary">Detail</a></td>
          </tr>
          <tr>
            <td>RPS Manajemen Strategis</td>
            <td>SI4321 - Manajemen</td>
            <td>Dr. Budi</td>
            <td>2024/2025 - Ganjil</td>
            <td>@include('partials._status_badge', ['status' => 'approved'])</td>
            <td><a href="/rps/2" class="btn btn-sm btn-outline-primary">Detail</a></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
@endsection
