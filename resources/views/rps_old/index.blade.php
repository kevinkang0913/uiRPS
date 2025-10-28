@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Daftar RPS</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-3">
        <a href="{{ route('rps.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Submit RPS
        </a>
    </div>

    <table class="table table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>Mata Kuliah</th>
                <th>Dosen</th>
                <th>Judul</th>
                <th>Status</th>
                <th>File</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rps as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->classSection->course->name ?? '-' }}</td>
                <td>{{ $item->lecturer->name ?? '-' }}</td>
                <td>{{ $item->title }}</td>
                <td>
                    <span class="badge bg-{{ $item->status == 'approved' ? 'success' : ($item->status == 'rejected' ? 'danger' : 'warning') }}">
                        {{ ucfirst($item->status) }}
                    </span>
                </td>
                <td>
                    @if($item->file_path)
                        <a href="{{ asset('storage/' . $item->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                            Lihat File
                        </a>
                    @else
                        <em>-</em>
                    @endif
                </td>
                <td>
                    @include('rps.partials.action', ['rps' => $item])
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Belum ada RPS</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
