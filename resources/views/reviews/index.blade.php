@extends('layouts.app')

@section('content')
  @include('partials._page_header', [
    'title' => 'Review RPS',
    'subtitle' => 'Daftar RPS yang menunggu review',
  ])

  <div class="card">
    <div class="card-body p-0">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Judul</th>
            <th>Mata Kuliah</th>
            <th>Dosen</th>
            <th>Semester</th>
            <th>Status</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>RPS Pemrograman Web</td>
            <td>SI5678 - Web</td>
            <td>Dr. Clara</td>
            <td>2024/2025 - Ganjil</td>
            <td>@include('partials._status_badge', ['status' => 'submitted'])</td>
            <td class="text-end">
              <a href="/reviews/1" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Review</a>
            </td>
          </tr>
          <tr>
            <td>RPS IoT dan Smart Farming</td>
            <td>SI8765 - IoT</td>
            <td>Dr. Dani</td>
            <td>2024/2025 - Genap</td>
            <td>@include('partials._status_badge', ['status' => 'reviewed'])</td>
            <td class="text-end">
              <a href="/reviews/2" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i> Lihat</a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
@endsection
