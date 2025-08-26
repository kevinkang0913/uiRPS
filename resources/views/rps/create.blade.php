@extends('layouts.app')

@section('content')
  @include('partials._page_header', [
    'title' => 'Submit RPS',
    'subtitle' => 'Form pengajuan RPS baru'
  ])

  <div class="card">
    <div class="card-body">
      <form>
        <div class="mb-3">
          <label class="form-label">Judul RPS</label>
          <input type="text" class="form-control" placeholder="Masukkan judul RPS">
        </div>

        <div class="mb-3">
          <label class="form-label">Deskripsi</label>
          <textarea class="form-control" rows="3" placeholder="Tuliskan deskripsi singkat"></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Mata Kuliah</label>
          <select class="form-select">
            <option>-- Pilih Mata Kuliah --</option>
            <option>SI1234 - Basis Data</option>
            <option>SI5678 - Web</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Semester</label>
          <select class="form-select">
            <option>-- Pilih Semester --</option>
            <option>2024/2025 - Ganjil</option>
            <option>2024/2025 - Genap</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Upload File</label>
          <input type="file" class="form-control" accept=".pdf,.doc,.docx">
        </div>

        <button class="btn btn-gold">Submit</button>
        <a href="/rps" class="btn btn-secondary">Batal</a>
      </form>
    </div>
  </div>
@endsection
