@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <h1 class="mb-4">Buat RPS Baru</h1>

  <form action="{{ route('rps.store') }}" method="POST">
    @csrf

    {{-- Step 1 --}}
    @include('rps.partials.step1')

    {{-- Step 2 --}}
    @include('rps.partials.step2')

    {{-- Step 3 --}}
    @include('rps.partials.step3')

    {{-- Step 4 --}}
    @include('rps.partials.step4')

    {{-- Step 5 --}}
    @include('rps.partials.step5')

    {{-- Step 6 --}}
    @include('rps.partials.step6')

    <div class="mt-3">
      <button type="submit" class="btn btn-primary">Simpan</button>
      <a href="{{ route('rps.index') }}" class="btn btn-secondary">Batal</a>
    </div>
  </form>
</div>
@endsection
