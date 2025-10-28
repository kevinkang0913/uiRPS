@extends('layouts.app')
@section('content')
<div class="container py-4" style="max-width:720px;">
  <h3 class="mb-3">{{ $faculty->exists ? 'Edit Faculty' : 'New Faculty' }}</h3>

  <div class="card">
    <div class="card-body">
      <form method="POST"
        action="{{ $faculty->exists ? route('faculties.update', $faculty) : route('faculties.store') }}">
        @csrf
        @if($faculty->exists) @method('PUT') @endif

        <div class="mb-3">
          <label class="form-label">Code</label>
          <input type="text" name="code" class="form-control" value="{{ old('code', $faculty->code) }}" required>
          @error('code') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" value="{{ old('name', $faculty->name) }}" required>
          @error('name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="d-flex gap-2">
          <button class="btn btn-success">Save</button>
          <a href="{{ route('faculties.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
