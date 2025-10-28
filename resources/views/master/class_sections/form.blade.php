@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 800px;">
  <h3 class="mb-3">{{ $section->exists ? 'Edit Class Section' : 'New Class Section' }}</h3>

  <div class="card">
    <div class="card-body">
      <form method="POST"
            action="{{ $section->exists ? route('class-sections.update', $section) : route('class-sections.store') }}">
        @csrf
        @if($section->exists) @method('PUT') @endif

        <div class="mb-3">
          <label class="form-label">Course</label>
          <select name="course_id" class="form-select" required>
            <option value="" hidden>— Choose Course —</option>
            @foreach($courses as $c)
              <option value="{{ $c->id }}"
                {{ (string)old('course_id', $section->course_id) === (string)$c->id ? 'selected' : '' }}>
                {{ $c->name }} — {{ $c->program?->name }} ({{ $c->program?->faculty?->name }})
              </option>
            @endforeach
          </select>
          @error('course_id') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Class</label>
            <input type="text" name="class_number" class="form-control"
                   value="{{ old('class_number', $section->class_number) }}" required maxlength="10">
            @error('class_number') <small class="text-danger">{{ $message }}</small> @enderror
          </div>
          <div class="col-md-3">
            <label class="form-label">Semester</label>
            <input type="number" name="semester" class="form-control"
                   value="{{ old('semester', $section->semester) }}" required min="1" max="14">
            @error('semester') <small class="text-danger">{{ $message }}</small> @enderror
          </div>
          <div class="col-md-3">
            <label class="form-label">Year</label>
            <input type="number" name="year" class="form-control"
                   value="{{ old('year', $section->year) }}" required min="2000" max="2100">
            @error('year') <small class="text-danger">{{ $message }}</small> @enderror
          </div>
        </div>

        <div class="d-flex gap-2 mt-3">
          <button class="btn btn-success">Save</button>
          <a href="{{ route('class-sections.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
