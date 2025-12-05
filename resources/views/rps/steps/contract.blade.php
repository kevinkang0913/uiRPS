@extends('layouts.app')

@section('content')
<div class="container-xxl">

  <div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">Step 6 — Kontrak Perkuliahan</h4>
    <div class="text-muted small">RPS #{{ $rps->id }}</div>
  </div>

  @if($errors->any())
    <div class="alert alert-danger">
      <b>Periksa input:</b>
      <ul class="mb-0">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @php
    $vClass = old('class_policy', $contract->class_policy ?? '');
    $vCont  = old('contract_text', $contract->contract_text ?? '');
  @endphp

  <form method="POST" action="{{ route('rps.store.step', 6) }}" class="card shadow-sm border-0">
    @csrf

    <div class="card-body p-4">

      <h6 class="fw-bold mb-2">Peraturan Kelas</h6>
      <textarea
        name="class_policy"
        rows="7"
        class="form-control"
        placeholder="{{ $placeholderClass }}"
      >{{ $vClass }}</textarea>

      <hr class="my-4">

      <h6 class="fw-bold mb-2">Kontrak Perkuliahan</h6>
      <textarea
        name="contract_text"
        rows="7"
        class="form-control"
        placeholder="{{ $placeholderContract }}"
      >{{ $vCont }}</textarea>

    </div>

    <div class="card-footer bg-light d-flex justify-content-between">
      <a href="{{ route('rps.create.step', 5) }}" class="btn btn-outline-secondary">
        ← Kembali
      </a>
      <button type="submit" class="btn btn-primary">Simpan</button>
    </div>

  </form>

</div>
@endsection
