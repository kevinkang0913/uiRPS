@extends('layouts.app')

@section('content')
<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="mb-0">Assign Dosen</h3>
      <div class="text-muted small">{{ $course->code }} — {{ $course->name }}</div>
    </div>
    <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary">
      <i class="bi bi-arrow-left"></i> Back
    </a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <div class="card mb-3">
    <div class="card-body">
      <form method="POST" action="{{ route('courses.lecturers.store', $course) }}" class="row g-2 align-items-end">
        @csrf

        <div class="col-md-6">
          <label class="form-label">Dosen</label>
          <select name="user_id" class="form-select" required>
            <option value="">-- pilih dosen --</option>
            @foreach($lecturers as $lec)
              <option value="{{ $lec->id }}">{{ $lec->name }} ({{ $lec->email }})</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label d-block">Hak</label>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="can_edit" value="1" id="can_edit">
            <label class="form-check-label" for="can_edit">Editing Lecturer</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_responsible" value="1" id="is_pic">
            <label class="form-check-label" for="is_pic">PIC (Penanggung jawab)</label>
          </div>
        </div>

        <div class="col-md-3">
          <button class="btn btn-primary w-100">
            <i class="bi bi-save"></i> Save
          </button>
        </div>
      </form>

      <div class="text-muted small mt-2">
        * PIC hanya 1 per course. Jika set PIC baru, PIC lama otomatis dilepas.
      </div>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th class="text-center" style="width:120px">Editing</th>
            <th class="text-center" style="width:120px">PIC</th>
            <th class="text-end" style="width:120px">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($assigned as $a)
            <tr>
              <td>{{ $a->name }}</td>
              <td class="text-muted">{{ $a->email }}</td>
              <td class="text-center">
                {!! ($a->pivot->can_edit ?? 0) ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}
              </td>
              <td class="text-center">
                {!! ($a->pivot->is_responsible ?? 0) ? '<span class="badge bg-primary">PIC</span>' : '—' !!}
              </td>
              <td class="text-end">
                <form method="POST" action="{{ route('courses.lecturers.destroy', [$course, $a]) }}"
                      class="d-inline" onsubmit="return confirm('Remove this lecturer from course?')">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-x-lg"></i>
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted py-4">No assigned lecturers</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection
