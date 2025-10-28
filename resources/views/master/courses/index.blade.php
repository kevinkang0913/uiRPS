@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Courses</h3>
    <a href="{{ route('courses.create') }}" class="btn btn-primary">
      <i class="bi bi-plus-lg me-1"></i> New Course
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
          <label class="form-label">Search</label>
          <input type="text" name="q" value="{{ $q ?? request('q') }}" class="form-control" placeholder="Code, name or catalog...">
        </div>

        <div class="col-lg-3">
          <label class="form-label">Sort</label>
          <div class="input-group">
            @php $sortNow = $sort ?? request('sort','name'); $d = $dir ?? request('dir','asc'); @endphp
            <select name="sort" class="form-select">
              <option value="name" {{ $sortNow==='name' ? 'selected':'' }}>Name</option>
              <option value="code" {{ $sortNow==='code' ? 'selected':'' }}>Code</option>
              <option value="catalog_nbr" {{ $sortNow==='catalog_nbr' ? 'selected':'' }}>Catalog</option>
            </select>
            <select name="dir" class="form-select" style="max-width:120px">
              <option value="asc"  {{ $d==='asc' ? 'selected':'' }}>Asc</option>
              <option value="desc" {{ $d==='desc'? 'selected':'' }}>Desc</option>
            </select>
          </div>
        </div>

        <div class="col-lg-2 mt-2">
          <label class="form-label">Per Page</label>
          @php $pp = (int)($perPage ?? request('per_page',15)); @endphp
          <select name="per_page" class="form-select">
            @foreach([10,15,20,30,50,100] as $n)
              <option value="{{ $n }}" {{ $pp===$n ? 'selected':'' }}>{{ $n }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-lg-2 d-flex gap-2 mt-4">
          <button class="btn btn-outline-primary flex-fill"><i class="bi bi-filter me-1"></i> Apply</button>
          <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
        </div>
      </div>
    </div>
  </form>

  {{-- Table --}}
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          @php
            $sortNow = $sort ?? request('sort','name');
            $dirNow  = strtolower($dir ?? request('dir','asc'))==='asc'?'asc':'desc';
            $flip    = $dirNow==='asc' ? 'desc' : 'asc';
            $link = fn($col)=>request()->fullUrlWithQuery(['sort'=>$col,'dir'=>($sortNow===$col?$flip:'asc')]);
            $arrow = fn($col)=> $sortNow===$col ? ($dirNow==='asc'?'↑':'↓') : '';
          @endphp
          <tr>
            <th style="width:72px">#</th>
            <th><a class="link-underline link-underline-opacity-0" href="{{ $link('code') }}">Code {{ $arrow('code') }}</a></th>
            <th><a class="link-underline link-underline-opacity-0" href="{{ $link('name') }}">Name {{ $arrow('name') }}</a></th>
            <th><a class="link-underline link-underline-opacity-0" href="{{ $link('catalog_nbr') }}">Catalog {{ $arrow('catalog_nbr') }}</a></th>
            <th>Program</th>
            <th>Faculty</th>
            <th class="text-end" style="width:160px">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($items as $i => $c)
            <tr>
              <td>{{ $items->firstItem() + $i }}</td>
              <td><code>{{ $c->code }}</code></td>
              <td>{{ $c->name }}</td>
              <td>{{ $c->catalog_nbr ?? '—' }}</td>
              <td>{{ $c->program?->name }}</td>
              <td>{{ $c->program?->faculty?->name }}</td>
              <td class="text-end">
                <a href="{{ route('courses.edit', $c) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                <form action="{{ route('courses.destroy', $c) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this course?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted py-4">No data</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer d-flex justify-content-between">
      <small class="text-muted">Showing {{ $items->firstItem() }}–{{ $items->lastItem() }} of {{ $items->total() }}</small>
      {{ $items->onEachSide(1)->links() }}
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // filter Program by selected Faculty (client-side)
  (function() {
    const fSel = document.getElementById('facultySelect');
    const pSel = document.getElementById('programSelect');
    function filterPrograms() {
      const fid = fSel.value;
      [...pSel.options].forEach(opt=>{
        if(!opt.value) return; // skip placeholder
        const ok = !fid || opt.getAttribute('data-faculty') === fid;
        opt.hidden = !ok;
      });
      // jika program terpilih tidak match fakultas, reset
      const cur = pSel.selectedOptions[0];
      if (cur && cur.hidden) pSel.value = '';
    }
    if (fSel && pSel) {
      filterPrograms();
      fSel.addEventListener('change', filterPrograms);
    }

    // optional auto-submit
    document.querySelectorAll('select[name="faculty_id"],select[name="program_id"],select[name="per_page"]').forEach(el=>{
      el.addEventListener('change', ()=> document.getElementById('filterForm').submit());
    });
  })();
</script>
@endpush
