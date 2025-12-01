@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Assign Roles to {{ $user->name }}</h2>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger">
      <strong>Periksa input:</strong>
      <ul class="mb-0">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('users.roles.update',$user->id) }}">
    @csrf
    <div class="card p-3">
      @foreach($roles as $role)
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" 
                 name="roles[]" value="{{ $role->id }}"
                 id="role_{{ $role->id }}"
                 {{ $user->roles->contains($role->id) ? 'checked' : '' }}>
          <label class="form-check-label" for="role_{{ $role->id }}">
            {{ ucfirst($role->name) }}
          </label>
        </div>
      @endforeach
    </div>
    <button type="submit" class="btn btn-primary mt-3">Update Roles</button>
    <a href="{{ route('users.index') }}" class="btn btn-secondary mt-3">Back</a>
  </form>
</div>
@endsection
