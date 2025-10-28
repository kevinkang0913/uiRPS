@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 800px;">
  <h3 class="mb-3">{{ $course->exists ? 'Edit Course' : 'New Course' }}</h3>

  <div class="card">
    <div class="card-body">
      <form method="POST"
            action="{{ $course->exists ? route('courses.update', $course) : route('courses.store') }}">
        @csrf
        @if($course->exists) @method('PUT') @endif

        <div class="mb-3">
          <label class="form-label">Program</label>
          <select name="program_id" class="form-select" required>
            <option value="" hidden>— Choose Program —</option>
            @foreach($programs as $p)
              <option value="{{ $p->id }}"
                {{ (string)old('program_id', $course->program_id) === (string)$p->id ? 'selected' : '' }}>
                {{ $p->name }} — {{ $p->faculty?->name }}
              </option>
            @endforeach
          </select>
          @error('program_id') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Course ID (CRSE_ID)</label>
            <input type="text" name="course_id" class="form-control"
                   value="{{ old('course_id', $course->course_id) }}" required maxlength="50">
            @error('course_id') <small class="text-danger">{{ $message }}</small> @enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">Catalog Nbr</label>
            <input type="text" name="catalog_nbr" class="form-control"
                   value="{{ old('catalog_nbr', $course->catalog_nbr) }}" maxlength="50">
            @error('catalog_nbr') <small class="text-danger">{{ $message }}</small> @enderror
          </div>
        </div>

        <div class="mb-3 mt-3">
          <label class="form-label">Course Name</label>
          <input type="text" name="name" class="form-control"
                 value="{{ old('name', $course->name) }}" required maxlength="255">
          @error('name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="d-flex gap-2">
          <button class="btn btn-success">Save</button>
          <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
