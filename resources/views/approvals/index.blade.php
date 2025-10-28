@extends('layouts.app')
@section('content')
<div class="container">
  <h2>Approval RPS (Kaprodi)</h2>
  <table class="table table-bordered align-middle">
    <thead class="table-light">
      <tr><th>Course</th><th>Dosen</th><th>Status</th><th>Aksi</th></tr>
    </thead>
    <tbody>
      @forelse($rpsList as $rps)
      <tr>
        <td>{{ $rps->title }} — {{ $rps->classSection->course->code ?? '' }}</td>
        <td>{{ $rps->lecturer->name ?? '-' }}</td>
        <td><span class="badge bg-info text-dark">{{ $rps->status }}</span></td>
        <td><a href="{{ route('approvals.edit',$rps->id) }}" class="btn btn-sm btn-primary">Beri Keputusan</a></td>
      </tr>
      @empty
      <tr><td colspan="4" class="text-center text-muted">Tidak ada RPS menunggu approval</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
