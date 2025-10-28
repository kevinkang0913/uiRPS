@extends('layouts.app')
@section('content')
<div class="container">
  <h2>Assign Roles to {{ $user->name }}</h2>
  <form method="POST" action="{{ route('users.roles.update',$user->id) }}">
    @csrf
    @foreach($roles as $role)
      <div class="form-check">
        <input class="form-check-input" type="checkbox" 
               name="roles[]" value="{{ $role->id }}"
               {{ $user->roles->contains($role->id) ? 'checked' : '' }}>
        <label class="form-check-label">{{ ucfirst($role->name) }}</label>
      </div>
    @endforeach
    <button type="submit" class="btn btn-primary mt-3">Update Roles</button>
  </form>
</div>
@endsection
