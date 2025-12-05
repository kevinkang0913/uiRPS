{{-- resources/views/rps/steps/references.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-xxl">
  <div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">Step 4 — Referensi (Daftar Pustaka)</h4>
    <div class="text-muted small">RPS #{{ $rps->id }}</div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger">
      <b>Periksa input:</b>
      <ul class="mb-0">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('rps.store.step', 4) }}" class="card shadow-sm border-0">
    @csrf
    <div class="card-body p-4">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
          <h6 class="mb-0">Daftar Referensi</h6>
          <div class="text-muted small">
            Isi tipe referensi, teks referensi (format bebas), dan URL/DOI sumber.
          </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddRef">
          + Tambah Referensi
        </button>
      </div>

      @php
        // Kalau ada old('refs') dari validasi gagal, pakai itu dulu
        $oldRefs = old('refs');
      @endphp

      <div id="refWrap" class="vstack gap-3">
        @if(is_array($oldRefs) && count($oldRefs))
          @foreach($oldRefs as $i => $ref)
            @include('rps.steps.partials.reference-row', [
              'i' => $i,
              'r' => (object)[
                'type'  => $ref['type'] ?? null,
                'title' => $ref['text'] ?? null,
                'url'   => $ref['url'] ?? null,
              ],
            ])
          @endforeach
        @elseif($refs->isNotEmpty())
          @foreach($refs as $i => $r)
            @include('rps.steps.partials.reference-row', ['i' => $i, 'r' => $r])
          @endforeach
        @else
          @include('rps.steps.partials.reference-row', ['i' => 0, 'r' => null])
        @endif
      </div>
    </div>

    <div class="card-footer bg-light d-flex justify-content-between">
      <a href="{{ route('rps.create.step', 3) }}" class="btn btn-outline-secondary">
        ← Kembali
      </a>
      <button class="btn btn-primary">Simpan dan Lanjut ke Rencana Pembelajaran Mingguan</button>
    </div>
  </form>
</div>

{{-- template row untuk JS clone --}}
<template id="tplRef">
  @include('rps.steps.partials.reference-row', ['i' => '__i__', 'r' => null])
</template>

<script>
  const wrap = document.getElementById('refWrap');
  const btnAddRef = document.getElementById('btnAddRef');

  if (btnAddRef) {
    btnAddRef.addEventListener('click', () => {
      const i = wrap.querySelectorAll('.ref-item').length;
      const tpl = document.getElementById('tplRef').innerHTML.replace(/__i__/g, i);
      const holder = document.createElement('div');
      holder.innerHTML = tpl.trim();
      wrap.appendChild(holder.firstElementChild);
    });
  }
</script>
@endsection
