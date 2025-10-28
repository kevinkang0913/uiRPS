@extends('layouts.app')

@section('content')
<div class="container">
  <h2>User Management</h2>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Roles</th>
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
          @foreach($user->roles as $role)
            <span class="badge bg-primary">{{ $role->name }}</span>
          @endforeach
        </td>
        <td>
          <a href="{{ route('users.roles.edit',$user->id) }}" class="btn btn-sm btn-warning">
            Assign Roles
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
