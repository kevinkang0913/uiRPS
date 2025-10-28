@extends('layouts.app')
@section('content')
<div class="container">
  <h2>Edit Role</h2>
  <form method="POST" action="{{ route('roles.update',$role->id) }}">
    @csrf @method('PUT')
    <input type="text" name="name" class="form-control mb-2" value="{{ $role->name }}" required>
    <button class="btn btn-success">Update</button>
  </form>
</div>
@endsection
