@extends('layouts.app')

@section('content')
  @include('partials._page_header', [
    'title' => 'Daftar RPS',
    'subtitle' => 'Semua RPS yang telah disubmit',
    'slot' => '<a href="/rps/create" class="btn btn-gold"><i class="bi bi-plus-circle"></i> Submit RPS</a>'
  ])

  @include('partials._search_filter')

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
            <td>@include('partials._status_badge', ['status' => 'reviewed'])</td>
            <td class="text-end">
              <a href="/rps/3" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
              <a href="#" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
              <a href="#" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></a>
            </td>
          </tr>
          <tr>
            <td>RPS IoT dan Smart Farming</td>
            <td>SI8765 - IoT</td>
            <td>Dr. Dani</td>
            <td>2024/2025 - Genap</td>
            <td>@include('partials._status_badge', ['status' => 'approved'])</td>
            <td class="text-end">
              <a href="/rps/4" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
              <a href="#" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
              <a href="#" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
@endsection
