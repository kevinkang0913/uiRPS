@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Submit RPS Baru</h2>

    <form action="{{ route('rps.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="class_section_id" class="form-label">Pilih Kelas</label>
            <select name="class_section_id" id="class_section_id" class="form-select" required>
                <option value="">-- Pilih Kelas --</option>
                @foreach($classSections as $section)
                    <option value="{{ $section->id }}">
                        {{ $section->course->name }} (Kelas {{ $section->class_number }}, Semester {{ $section->semester }})
                    </option>
                @endforeach
            </select>
            @error('class_section_id') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label for="title" class="form-label">Judul RPS</label>
            <input type="text" name="title" id="title" class="form-control" required>
            @error('title') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Deskripsi</label>
            <textarea name="description" id="description" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label for="file" class="form-label">Upload File (PDF/DOC)</label>
            <input type="file" name="file" id="file" class="form-control">
            @error('file') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <button type="submit" class="btn btn-success">Submit</button>
        <a href="{{ route('rps.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
