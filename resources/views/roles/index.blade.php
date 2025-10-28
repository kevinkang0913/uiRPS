@extends('layouts.app')
@section('content')
<div class="container">
  <h2>Roles</h2>
  <a href="{{ route('roles.create') }}" class="btn btn-primary mb-3">+ New Role</a>
  <table class="table table-bordered">
    <tr><th>ID</th><th>Name</th><th>Actions</th></tr>
    @foreach($roles as $role)
      <tr>
        <td>{{ $role->id }}</td>
        <td>{{ $role->name }}</td>
        <td>
          <a href="{{ route('roles.edit',$role->id) }}" class="btn btn-sm btn-warning">Edit</a>
          <form action="{{ route('roles.destroy',$role->id) }}" method="POST" style="display:inline-block">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
          </form>
        </td>
      </tr>
    @endforeach
  </table>
</div>
@endsection
