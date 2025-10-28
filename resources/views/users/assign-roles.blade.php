@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Assign Roles to {{ $user->name }}</h2>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <form method="POST" action="{{ route('users.roles.update',$user->id) }}">
    @csrf
    <div class="card p-3">
      @foreach($roles as $role)
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" 
                 name="roles[]" value="{{ $role->id }}"
                 {{ $user->roles->contains($role->id) ? 'checked' : '' }}>
          <label class="form-check-label">
            {{ ucfirst($role->name) }}
          </label>
        </div>
      @endforeach
    </div>
    <button type="submit" class="btn btn-primary mt-3">Update Roles</button>
    <a href="{{ route('roles.index') }}" class="btn btn-secondary mt-3">Back</a>
  </form>
</div>
@endsection
