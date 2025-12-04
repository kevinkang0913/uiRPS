@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">User Management</h2>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @php $current = auth()->user(); @endphp

  <div class="card">
    <div class="table-responsive">
      <table class="table table-bordered table-striped mb-0">
        <thead class="table-light">
          <tr>
            <th style="width:60px;">ID</th>
            <th>Name</th>
            <th>Email</th>
            <th style="width:220px;">Roles</th>
            <th style="width:160px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($users as $user)
            @php
              $isSuperAdminUser = $user->hasRole('Super Admin');
              // boleh assign kalau:
              // - current adalah Super Admin, atau
              // - target user BUKAN Super Admin
              $canAssign = $current->hasRole('Super Admin') || ! $isSuperAdminUser;
            @endphp
            <tr>
              <td>{{ $user->id }}</td>
              <td>{{ $user->name }}</td>
              <td>{{ $user->email }}</td>
              <td>
                @forelse($user->roles as $role)
                  <span class="badge bg-primary me-1">{{ $role->name }}</span>
                @empty
                  <span class="text-muted">–</span>
                @endforelse
              </td>
              <td>
                @if($canAssign)
                  <a href="{{ route('users.roles.edit',$user->id) }}" class="btn btn-sm btn-warning">
                    Assign Roles
                  </a>
                @else
                  <span class="text-muted small">Restricted</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center text-muted py-4">
                No users found.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
      <small class="text-muted">
        Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }} users
      </small>
      {{ $users->links() }}
    </div>
  </div>
</div>
@endsection
