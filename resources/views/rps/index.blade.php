@extends('layouts.app')

@section('content')
<div class="container">
  <h1 class="mb-4">Daftar RPS</h1>

  {{-- Tombol Buat RPS Baru --}}
  <a href="{{ route('rps.create') }}" class="btn btn-primary mb-3">
    + Buat RPS Baru
  </a>

  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>#</th>
        <th>Title</th>
        <th>Dosen</th>
        <th>Status</th>
        <th>Created At</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rps as $i => $item)
        <tr>
          <td>{{ $i+1 }}</td>
          <td>{{ $item->title }}</td>
          <td>{{ $item->lecturer->name ?? '-' }}</td>
          <td>
            @if($item->status === 'submitted')
              <span class="badge bg-info">Submitted</span>
            @elseif($item->status === 'approved')
              <span class="badge bg-success">Approved</span>
            @elseif($item->status === 'rejected')
              <span class="badge bg-danger">Rejected</span>
            @else
              <span class="badge bg-secondary">{{ $item->status }}</span>
            @endif
          </td>
          <td>{{ $item->created_at?->format('d M Y') }}</td>
          <td>
            <a href="{{ route('rps.show', $item->id) }}" class="btn btn-info btn-sm">Lihat</a>
            <a href="{{ route('rps.edit', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>
            <form action="{{ route('rps.destroy', $item->id) }}" method="POST" class="d-inline">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus RPS ini?')">Hapus</button>
            </form>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="text-center">Belum ada RPS</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
