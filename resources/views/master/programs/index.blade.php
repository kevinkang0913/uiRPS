@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Programs</h3>
    <a href="{{ route('programs.create') }}" class="btn btn-primary">
      <i class="bi bi-plus-lg me-1"></i> New Program
    </a>
  </div>

  {{-- Filter Bar --}}
  <form method="GET" class="card mb-3">
    <div class="card-body">
      <div class="row g-2 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Search</label>
          <input type="text" name="q" value="{{ $q ?? request('q') }}" class="form-control" placeholder="Search code or name...">
        </div>

        <div class="col-md-4">
          <label class="form-label">Faculty</label>
          <select name="faculty_id" class="form-select">
            <option value="">— All Faculties —</option>
            @foreach($faculties as $f)
              <option value="{{ $f->id }}" {{ (string)($faculty ?? request('faculty_id'))===(string)$f->id ? 'selected':'' }}>
                {{ $f->name }} ({{ $f->code }})
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Sort</label>
          <div class="input-group">
            <select name="sort" class="form-select">
              <option value="name" {{ ($sort ?? request('sort','name'))==='name' ? 'selected':'' }}>Name</option>
              <option value="code" {{ ($sort ?? request('sort'))==='code' ? 'selected':'' }}>Code</option>
            </select>
            <select name="dir" class="form-select" style="max-width:120px">
              @php $d = $dir ?? request('dir','asc'); @endphp
              <option value="asc"  {{ $d==='asc' ? 'selected':'' }}>Asc</option>
              <option value="desc" {{ $d==='desc'? 'selected':'' }}>Desc</option>
            </select>
          </div>
        </div>

        <div class="col-md-2">
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
          <a href="{{ route('programs.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
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
            <th>Faculty</th>
            <th class="text-end" style="width:160px">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($items as $i => $p)
            <tr>
              <td>{{ $items->firstItem() + $i }}</td>
              <td><code>{{ $p->code }}</code></td>
              <td>{{ $p->name }}</td>
              <td>{{ $p->faculty?->name }}</td>
              <td class="text-end">
                <a href="{{ route('programs.edit', $p) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                <form action="{{ route('programs.destroy', $p) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this program?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted py-4">No data</td></tr>
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
  // optional auto-submit
  document.querySelectorAll('select[name="faculty_id"],select[name="per_page"]').forEach(el=>{
    el.addEventListener('change', ()=> el.form.submit());
  });
</script>
@endpush
