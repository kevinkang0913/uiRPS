@extends('layouts.app')
@section('content')
<div class="container">
  <h2>Create Role</h2>
  <form method="POST" action="{{ route('roles.store') }}">
    @csrf
    <input type="text" name="name" class="form-control mb-2" placeholder="Role name" required>
    <button class="btn btn-success">Save</button>
  </form>
</div>
@endsection
