@extends('layouts.app')

@section('content')
<div class="container-xxl">
  <div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">Step 5 — Rencana Pembelajaran Mingguan (RPM)</h4>
    <div class="text-muted small">RPS #{{ $rps->id }}</div>
  </div>

  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
  @if($errors->any())
    <div class="alert alert-danger"><b>Periksa input:</b>
      <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('rps.store.step', 5) }}" class="card shadow-sm border-0">
    @csrf
    <div class="card-body p-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0">Mingguan</h6>
        <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddWeek">+ Tambah Minggu</button>
      </div>

      <div id="weeksWrap" class="vstack gap-3">
        {{-- render existing --}}
        @forelse($weeks as $i => $w)
          @include('rps.steps.partials.week-row', ['i'=>$i, 'w'=>$w, 'clos'=>$clos])
        @empty
          @include('rps.steps.partials.week-row', ['i'=>0, 'w'=>null, 'clos'=>$clos])
        @endforelse
      </div>
    </div>

    <div class="card-footer bg-light d-flex justify-content-between">
      <a href="{{ route('rps.create.step', 4) }}" class="btn btn-outline-secondary">← Kembali</a>
      <button class="btn btn-primary">Simpan</button>
    </div>
  </form>
</div>

<template id="tplWeek">
  @include('rps.steps.partials.week-row', ['i' => '__i__', 'w' => null, 'clos' => $clos])
</template>

<script>
const wrap = document.getElementById('weeksWrap');
document.getElementById('btnAddWeek').addEventListener('click', ()=>{
  const i = wrap.querySelectorAll('.week-item').length;
  const html = document.getElementById('tplWeek').innerHTML.replace(/__i__/g, i);
  const holder = document.createElement('div');
  holder.innerHTML = html.trim();
  wrap.appendChild(holder.firstElementChild);
});
</script>
@endsection
