@extends('layouts.app')

@section('content')
<div class="container-xxl">

  {{-- Header --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
      <h3 class="mb-0">User Management</h3>
      <div class="text-muted small">Kelola user, role, dan scope.</div>
    </div>

    <div class="d-flex gap-2">
      @if(auth()->user()->hasRole('Super Admin'))
        <a href="{{ route('import.dosen.form') }}" class="btn btn-outline-primary">
          <i class="bi bi-upload me-1"></i> Import Dosen
        </a>
      @endif
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  {{-- Toolbar: Search & Filter --}}
  @php
    $q = request('q','');
    $role = request('role','');

    // Sorting: Super Admin & CTL di atas (tanpa ubah controller)
    $sortedUsers = $users->getCollection()->sortByDesc(function($u) {
      $roleNames = $u->roles->pluck('name')->map(fn($n)=>strtolower($n))->toArray();
      // bobot: Super Admin tertinggi, CTL kedua
      $w = 0;
      if (in_array('super admin', $roleNames)) $w += 2000;
      if (in_array('ctl', $roleNames)) $w += 1000;
      return $w;
    });

    // filter in-memory (tetap paging by controller, tapi cukup untuk UI)
    if ($q) {
      $needle = mb_strtolower($q);
      $sortedUsers = $sortedUsers->filter(function($u) use ($needle) {
        return str_contains(mb_strtolower($u->name ?? ''), $needle)
            || str_contains(mb_strtolower($u->email ?? ''), $needle);
      });
    }

    if ($role) {
      $roleNeedle = mb_strtolower($role);
      $sortedUsers = $sortedUsers->filter(function($u) use ($roleNeedle) {
        return $u->roles->pluck('name')->map(fn($n)=>mb_strtolower($n))->contains($roleNeedle);
      });
    }

    // roles untuk dropdown (ambil dari data yang sudah ada di halaman)
    $roleOptions = $users->getCollection()
      ->flatMap(fn($u) => $u->roles->pluck('name'))
      ->unique()
      ->sort()
      ->values();

    // nomor tabel: 1..n per page (mengikuti pagination)
    $startNo = ($users->currentPage() - 1) * $users->perPage();
  @endphp

  <form method="GET" class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
      <div class="row g-2 align-items-end">
        <div class="col-md-6">
          <label class="form-label">Search</label>
          <input type="text" name="q" class="form-control"
                 placeholder="Cari nama atau email..."
                 value="{{ $q }}">
        </div>

        <div class="col-md-3">
          <label class="form-label">Role</label>
          <select name="role" class="form-select">
            <option value="">Semua</option>
            @foreach($roleOptions as $opt)
              <option value="{{ $opt }}" @selected($role === $opt)>{{ $opt }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3 d-flex gap-2">
          <button class="btn btn-primary w-100">
            <i class="bi bi-search me-1"></i> Apply
          </button>
          <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
            Reset
          </a>
        </div>
      </div>

      <div class="mt-2 text-muted small">
        Menampilkan <strong>{{ $sortedUsers->count() }}</strong> user dari halaman ini.
        <span class="ms-2">Pinned: <span class="badge bg-dark">Super Admin</span> & <span class="badge bg-info text-dark">CTL</span> di atas.</span>
      </div>
    </div>
  </form>

  {{-- Table --}}
  <div class="card border-0 shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width:70px;">No</th>
            <th>User</th>
            <th style="width:240px;">Roles</th>
            <th style="width:220px;">Scope</th>
            <th class="text-end" style="width:280px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($sortedUsers as $i => $user)
            @php
              $roleNames = $user->roles->pluck('name');
              $lower = $roleNames->map(fn($n)=>strtolower($n))->toArray();
              $isSuper = in_array('super admin', $lower);
              $isCtl   = in_array('ctl', $lower);

              // highlight row pinned roles
              $rowClass = $isSuper ? 'table-dark' : ($isCtl ? 'table-info' : '');

              $faculty = optional($user->faculty)->name ?? '—';
              $program = optional($user->program)->name ?? '—';
            @endphp
            <tr class="{{ $rowClass }}">
              <td class="text-muted fw-semibold">
                {{ $startNo + $loop->iteration }}
              </td>

              <td>
                <div class="fw-semibold">{{ $user->name }}</div>
                <div class="{{ $rowClass ? 'text-white-50' : 'text-muted' }} small">
                  {{ $user->email }}
                </div>
              </td>

              <td>
                @forelse($roleNames as $r)
                  <span class="badge {{ strtolower($r)==='super admin' ? 'bg-light text-dark' : (strtolower($r)==='ctl' ? 'bg-primary' : 'bg-secondary') }} me-1">
                    {{ $r }}
                  </span>
                @empty
                  <span class="{{ $rowClass ? 'text-white-50' : 'text-muted' }}">–</span>
                @endforelse
              </td>

              <td>
                <div class="small">
                  <div><span class="{{ $rowClass ? 'text-white-50' : 'text-muted' }}">Faculty:</span> {{ $faculty }}</div>
                  <div><span class="{{ $rowClass ? 'text-white-50' : 'text-muted' }}">Program:</span> {{ $program }}</div>
                </div>
              </td>

              <td class="text-end">
                <div class="d-flex flex-wrap gap-2 justify-content-end">
                  <a href="{{ route('users.roles.edit',$user->id) }}"
                     class="btn btn-sm {{ $isSuper ? 'btn-outline-light' : 'btn-warning' }}">
                    <i class="bi bi-shield-lock me-1"></i> Roles
                  </a>

                  <a href="{{ route('users.scope.edit',$user->id) }}"
                     class="btn btn-sm {{ $isSuper ? 'btn-outline-light' : 'btn-outline-primary' }}">
                    <i class="bi bi-diagram-3 me-1"></i> Scope
                  </a>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center py-5">
                <div class="fw-semibold mb-1">Tidak ada user yang cocok.</div>
                <div class="text-muted small">Coba ubah keyword atau filter.</div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="card-footer bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div class="text-muted small">
        Page {{ $users->currentPage() }} / {{ $users->lastPage() }}
      </div>
      <div>
        {{ $users->withQueryString()->links() }}
      </div>
    </div>
  </div>

</div>
@endsection
