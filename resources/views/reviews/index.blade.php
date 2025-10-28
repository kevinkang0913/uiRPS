@extends('layouts.app')
@section('content')
<div class="container">
  <h2>Review RPS (CTL)</h2>
  <table class="table table-bordered align-middle">
    <thead class="table-light">
      <tr>
        <th>Course</th><th>Dosen</th><th>Status</th><th>Aksi</th>
      </tr>
    </thead>
    <tbody>
    @forelse($rpsList as $rps)
      <tr>
        <td>{{ $rps->title }} — {{ $rps->classSection->course->code ?? '' }}</td>
        <td>{{ $rps->lecturer->name ?? '-' }}</td>
        <td><span class="badge bg-warning text-dark">{{ $rps->status }}</span></td>
        <td><a class="btn btn-sm btn-primary" href="{{ route('reviews.edit', $rps->id) }}">Review</a></td>
      </tr>
    @empty
      <tr><td colspan="4" class="text-center text-muted">Tidak ada RPS berstatus submitted</td></tr>
    @endforelse
    </tbody>
  </table>
</div>
@endsection
