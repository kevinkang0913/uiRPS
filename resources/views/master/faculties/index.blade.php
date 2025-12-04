@extends('layouts.app')

@section('content')
<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Faculties</h3>

    @php $current = auth()->user(); @endphp

    {{-- 🔒 Tombol New Faculty hanya untuk Super Admin --}}
    @if($current && $current->hasRole('Super Admin'))
      <a href="{{ route('faculties.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Faculty
      </a>
    @endif
  </div>

  {{-- Filter Bar --}}
  <form method="GET" class="card mb-3">
    <div class="card-body">
      <div class="row g-2 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Search</label>
          <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Search code or name...">
        </div>

        <div class="col-md-3">
          <label class="form-label">Sort by</label>
          <div class="input-group">
            <select name="sort" class="form-select">
              <option value="name"      {{ $sort==='name' ? 'selected' : '' }}>Name</option>
              <option value="code"      {{ $sort==='code' ? 'selected' : '' }}>Code</option>
              <option value="created_at"{{ $sort==='created_at' ? 'selected' : '' }}>Created</option>
            </select>
            <select name="dir" class="form-select" style="max-width:120px">
              <option value="asc"  {{ $dir==='asc' ? 'selected' : '' }}>Asc</option>
              <option value="desc" {{ $dir==='desc'? 'selected' : '' }}>Desc</option>
            </select>
          </div>
        </div>

        <div class="col-md-2">
          <label class="form-label">Per Page</label>
          <select name="per_page" class="form-select">
            @foreach([10,15,20,30,50,100] as $pp)
              <option value="{{ $pp }}" {{ $perPage==$pp ? 'selected' : '' }}>{{ $pp }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3 d-flex gap-2">
          <button class="btn btn-outline-primary flex-fill">
            <i class="bi bi-search me-1"></i> Filter
          </button>
          <a href="{{ route('faculties.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-x-circle"></i>
          </a>
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
            $flip  = $dir==='asc' ? 'desc' : 'asc';
            $link  = fn($col)=>request()->fullUrlWithQuery(['sort'=>$col,'dir'=>($sort===$col?$flip:'asc')]);
            $arrow = fn($col)=> $sort===$col ? ($dir==='asc'?'↑':'↓') : '';
          @endphp
          <tr>
            <th style="width:72px">#</th>
            <th><a class="link-underline link-underline-opacity-0" href="{{ $link('code') }}">Code {{ $arrow('code') }}</a></th>
            <th><a class="link-underline link-underline-opacity-0" href="{{ $link('name') }}">Name {{ $arrow('name') }}</a></th>
            <th class="text-end" style="width:160px">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($faculties as $i => $f)
            <tr>
              <td>{{ $faculties->firstItem() + $i }}</td>
              <td><code>{{ $f->code }}</code></td>
              <td>{{ $f->name }}</td>
              <td class="text-end">
                {{-- Edit boleh untuk Super Admin & Admin (backend sudah cek milik siapa) --}}
                <a href="{{ route('faculties.edit',$f) }}" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-pencil"></i>
                </a>

                {{-- 🔒 Delete hanya untuk Super Admin --}}
                @if($current && $current->hasRole('Super Admin'))
                  <form action="{{ route('faculties.destroy',$f) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Delete this faculty?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="text-center text-muted py-4">No data</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
      <small class="text-muted">
        Showing {{ $faculties->firstItem() }}–{{ $faculties->lastItem() }} of {{ $faculties->total() }} results
      </small>
      {{ $faculties->onEachSide(1)->links() }}
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('select[name="faculty_id"], select[name="program_id"], select[name="per_page"]').forEach(el=>{
  el.addEventListener('change', ()=> el.form.submit());
});
</script>
@endpush
