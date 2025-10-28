@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 720px;">
  <h3 class="mb-3">{{ $program->exists ? 'Edit Program' : 'New Program' }}</h3>

  <div class="card">
    <div class="card-body">
      <form method="POST"
            action="{{ $program->exists ? route('programs.update', $program) : route('programs.store') }}">
        @csrf
        @if($program->exists) @method('PUT') @endif

        <div class="mb-3">
          <label class="form-label">Faculty</label>
          <select name="faculty_id" class="form-select" required>
            <option value="" hidden>— Choose Faculty —</option>
            @foreach($faculties as $f)
              <option value="{{ $f->id }}"
                {{ (string)old('faculty_id', $program->faculty_id) === (string)$f->id ? 'selected' : '' }}>
                {{ $f->name }} ({{ $f->code }})
              </option>
            @endforeach
          </select>
          @error('faculty_id') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
          <label class="form-label">Program Code</label>
          <input type="text" name="code" class="form-control"
                 value="{{ old('code', $program->code) }}" required maxlength="20">
          @error('code') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
          <label class="form-label">Program Name</label>
          <input type="text" name="name" class="form-control"
                 value="{{ old('name', $program->name) }}" required maxlength="255">
          @error('name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="d-flex gap-2">
          <button class="btn btn-success">Save</button>
          <a href="{{ route('programs.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
