@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Class Sections</h3>
    <a href="{{ route('class-sections.create') }}" class="btn btn-primary">
      <i class="bi bi-plus-lg me-1"></i> New Section
    </a>
  </div>

  {{-- Filter Bar --}}
  <form method="GET" class="card mb-3" id="filterForm">
    <div class="card-body">
      <div class="row g-2 align-items-end">
        <div class="col-lg-3">
          <label class="form-label">Faculty</label>
          <select name="faculty_id" class="form-select" id="facultySelect">
            <option value="">— All Faculties —</option>
            @foreach($faculties as $f)
              <option value="{{ $f->id }}" {{ (string)($facultyId ?? request('faculty_id'))===(string)$f->id?'selected':'' }}>
                {{ $f->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-lg-3">
          <label class="form-label">Program</label>
          <select name="program_id" class="form-select" id="programSelect">
            <option value="">— All Programs —</option>
            @foreach($programs as $p)
              <option value="{{ $p->id }}" data-faculty="{{ $p->faculty_id }}"
                {{ (string)($programId ?? request('program_id'))===(string)$p->id?'selected':'' }}>
                {{ $p->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-lg-3">
          <label class="form-label">Course</label>
          <select name="course_id" class="form-select" id="courseSelect">
            <option value="">— All Courses —</option>
            @foreach($courses as $c)
              <option value="{{ $c->id }}" data-program="{{ $c->program_id }}"
                {{ (string)($courseId ?? request('course_id'))===(string)$c->id?'selected':'' }}>
                {{ $c->name }} — {{ $c->code }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-lg-3">
          <label class="form-label">Search</label>
          <input type="text" name="q" value="{{ $q ?? request('q') }}" class="form-control" placeholder="Class, semester, year...">
        </div>

        <div class="col-lg-2">
          <label class="form-label">Semester</label>
          <input type="number" name="semester" class="form-control" min="1" max="14" value="{{ $semester ?? request('semester') }}">
        </div>

        <div class="col-lg-2">
          <label class="form-label">Year</label>
          <input type="number" name="year" class="form-control" min="2000" max="2100" value="{{ $year ?? request('year') }}">
        </div>

        <div class="col-lg-3">
          <label class="form-label">Sort</label>
          @php $sortNow = $sort ?? request('sort','year'); $d = $dir ?? request('dir','desc'); @endphp
          <div class="input-group">
            <select name="sort" class="form-select">
              <option value="year" {{ $sortNow==='year' ? 'selected':'' }}>Year</option>
              <option value="semester" {{ $sortNow==='semester' ? 'selected':'' }}>Semester</option>
              <option value="class_number" {{ $sortNow==='class_number' ? 'selected':'' }}>Class</option>
            </select>
            <select name="dir" class="form-select" style="max-width:120px">
              <option value="asc"  {{ $d==='asc' ? 'selected':'' }}>Asc</option>
              <option value="desc" {{ $d==='desc'? 'selected':'' }}>Desc</option>
            </select>
          </div>
        </div>

        <div class="col-lg-2">
          <label class="form-label">Per Page</label>
          @php $pp = (int)($perPage ?? request('per_page',15)); @endphp
          <select name="per_page" class="form-select">
            @foreach([10,15,20,30,50,100] as $n)
              <option value="{{ $n }}" {{ $pp===$n ? 'selected':'' }}>{{ $n }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-12 d-flex gap-2 mt-2">
          <button class="btn btn-outline-primary"><i class="bi bi-filter me-1"></i> Apply</button>
          <a href="{{ route('class-sections.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
        </div>
      </div>
    </div>
  </form>

  {{-- Table --}}
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width:72px">#</th>
            <th>Course</th>
            <th>Program</th>
            <th>Faculty</th>
            <th>Class</th>
            <th>Semester</th>
            <th>Year</th>
            <th class="text-end" style="width:160px">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($sections as $i => $s)
            <tr>
              <td>{{ $sections->firstItem() + $i }}</td>
              <td>{{ $s->course?->name }}</td>
              <td>{{ $s->course?->program?->name }}</td>
              <td>{{ $s->course?->program?->faculty?->name }}</td>
              <td>{{ $s->class_number }}</td>
              <td>{{ $s->semester }}</td>
              <td>{{ $s->year }}</td>
              <td class="text-end">
                <a href="{{ route('class-sections.edit', $s) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                <form action="{{ route('class-sections.destroy', $s) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this section?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center text-muted py-4">No data</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer d-flex justify-content-between">
      <small class="text-muted">Showing {{ $sections->firstItem() }}–{{ $sections->lastItem() }} of {{ $sections->total() }}</small>
      {{ $sections->onEachSide(1)->links() }}
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (function() {
    const fSel = document.getElementById('facultySelect');
    const pSel = document.getElementById('programSelect');
    const cSel = document.getElementById('courseSelect');

    function filterPrograms() {
      const fid = fSel.value;
      [...pSel.options].forEach(opt=>{
        if(!opt.value) return;
        opt.hidden = fid && opt.getAttribute('data-faculty') !== fid;
      });
      if (pSel.selectedOptions[0]?.hidden) pSel.value = '';
      filterCourses(); // re-filter courses when program changes by side effect
    }

    function filterCourses() {
      const pid = pSel.value;
      [...cSel.options].forEach(opt=>{
        if(!opt.value) return;
        opt.hidden = pid && opt.getAttribute('data-program') !== pid;
      });
      if (cSel.selectedOptions[0]?.hidden) cSel.value = '';
    }

    if (fSel && pSel && cSel) {
      filterPrograms();
      filterCourses();
      fSel.addEventListener('change', filterPrograms);
      pSel.addEventListener('change', filterCourses);
    }

    // optional auto-submit
    document.querySelectorAll('select[name="faculty_id"],select[name="program_id"],select[name="course_id"],select[name="per_page"]').forEach(el=>{
      el.addEventListener('change', ()=> document.getElementById('filterForm').submit());
    });
  })();
</script>
@endpush
