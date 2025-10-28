@extends('layouts.app')
@section('content')
<div class="container">
  <h2>Approval: {{ $rps->title }}</h2>
  <form method="POST" action="{{ route('approvals.store', $rps->id) }}">
    @csrf
    <div class="mb-3">
      <label class="form-label">Keputusan</label>
      <select name="status" class="form-select" required>
        <option value="approved">Approve</option>
        <option value="rejected">Reject</option>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Catatan (opsional)</label>
      <textarea name="notes" class="form-control" rows="3"></textarea>
    </div>
    <div class="d-flex justify-content-between">
      <a href="{{ route('approvals.index') }}" class="btn btn-secondary">Batal</a>
      <button class="btn btn-success">Simpan Keputusan</button>
    </div>
  </form>
</div>
@endsection
