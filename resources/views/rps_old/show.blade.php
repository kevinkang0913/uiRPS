@extends('layouts.app')

@section('content')
  @include('partials._page_header', [
    'title' => 'Detail RPS',
    'subtitle' => 'Informasi lengkap RPS'
  ])

  <div class="card">
    <div class="card-body">
      <dl class="row">
        <dt class="col-sm-3">Judul</dt>
        <dd class="col-sm-9">RPS Pemrograman Web</dd>

        <dt class="col-sm-3">Deskripsi</dt>
        <dd class="col-sm-9">Rencana pembelajaran untuk mata kuliah Pemrograman Web semester ganjil 2024/2025.</dd>

        <dt class="col-sm-3">Mata Kuliah</dt>
        <dd class="col-sm-9">SI5678 - Web</dd>

        <dt class="col-sm-3">Semester</dt>
        <dd class="col-sm-9">2024/2025 - Ganjil</dd>

        <dt class="col-sm-3">Status</dt>
        <dd class="col-sm-9">@include('partials._status_badge', ['status' => 'reviewed'])</dd>

        <dt class="col-sm-3">File</dt>
        <dd class="col-sm-9">
          <a href="#" class="btn btn-outline-primary btn-sm"><i class="bi bi-file-earmark-arrow-down"></i> Download</a>
        </dd>
      </dl>
    </div>
  </div>
@endsection
