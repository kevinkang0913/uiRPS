@extends('layouts.app')

@section('content')
<div class="container-xxl">
  <div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">Step 4 — Referensi (Daftar Pustaka)</h4>
    <div class="text-muted small">RPS #{{ $rps->id }}</div>
  </div>

  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <b>Periksa input:</b>
      <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('rps.store.step', 4) }}" class="card shadow-sm border-0">
    @csrf
    <div class="card-body p-4">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0">Daftar Referensi</h6>
        <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddRef">+ Tambah Referensi</button>
      </div>

      <div id="refWrap" class="vstack gap-3">
        @foreach($refs as $i => $r)
          @include('rps.steps.partials.reference-row', ['i' => $i, 'r' => $r])
        @endforeach
        @if($refs->isEmpty())
          @include('rps.steps.partials.reference-row', ['i' => 0, 'r' => null])
        @endif
      </div>
    </div>

    <div class="card-footer bg-light d-flex justify-content-between">
      <a href="{{ route('rps.create.step', 3) }}" class="btn btn-outline-secondary">← Kembali</a>
      <button class="btn btn-primary">Simpan</button>
    </div>
  </form>
</div>

{{-- gunakan <template> agar HTML tidak di-escape --}}
<template id="tplRef">
  @include('rps.steps.partials.reference-row', ['i' => '__i__', 'r' => null])
</template>

<script>
  const wrap = document.getElementById('refWrap');
  document.getElementById('btnAddRef').addEventListener('click', ()=>{
    const i = wrap.querySelectorAll('.ref-item').length;
    const html = document.getElementById('tplRef').innerHTML.replace(/__i__/g, i);
    const holder = document.createElement('div');
    holder.innerHTML = html.trim();
    wrap.appendChild(holder.firstElementChild);
  });
</script>
@endsection
