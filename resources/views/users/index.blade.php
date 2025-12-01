@extends('layouts.app')

@section('content')
<div class="container">
  <h2>User Management</h2>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <table class="table table-bordered table-striped align-middle">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Roles</th>
        <th>Faculty</th>
        <th>Program Studi</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach($users as $user)
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
        <td>{{ optional($user->faculty)->name ?? '—' }}</td>
        <td>{{ optional($user->program)->name ?? '—' }}</td>
        <td class="d-flex gap-1">
          <a href="{{ route('users.roles.edit',$user->id) }}" class="btn btn-sm btn-warning">
            Assign Roles
          </a>
          <a href="{{ route('users.scope.edit',$user->id) }}" class="btn btn-sm btn-outline-primary">
            Assign Scope Fakultas
          </a>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="mt-3">
    {{ $users->links() }}
  </div>
</div>
@endsection
