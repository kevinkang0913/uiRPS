@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Assign Fakultas / Program — {{ $user->name }}</h2>

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

  <form method="POST" action="{{ route('users.scope.update',$user->id) }}">
    @csrf

    <div class="card shadow-sm p-3">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Fakultas</label>
          <select name="faculty_id" id="facultySelect" class="form-select @error('faculty_id') is-invalid @enderror">
            <option value="">— Tidak dibatasi fakultas (global) —</option>
            @foreach($faculties as $fac)
              <option value="{{ $fac->id }}"
                @selected(old('faculty_id', $user->faculty_id) == $fac->id)>
                {{ $fac->name }}
              </option>
            @endforeach
          </select>
          @error('faculty_id')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <div class="form-text">
            Untuk Admin Fakultas → pilih satu fakultas.  
            Untuk Super Admin / user global → boleh dikosongkan.
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Program Studi</label>
          <select name="program_id" id="programSelect" class="form-select @error('program_id') is-invalid @enderror">
            <option value="">— Tidak dibatasi program —</option>
            @foreach($programs as $prog)
              <option value="{{ $prog->id }}"
                      data-faculty="{{ $prog->faculty_id }}"
                      @selected(old('program_id', $user->program_id) == $prog->id)>
                {{ $prog->name }}
              </option>
            @endforeach
          </select>
          @error('program_id')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <div class="form-text">
            Untuk Kaprodi / Admin Prodi → pilih program tertentu.  
            Jika hanya level fakultas, biarkan kosong.
          </div>
        </div>
      </div>

      <div class="mt-4">
        <button type="submit" class="btn btn-primary">Update Scope</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Back</a>
      </div>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const facultySelect = document.getElementById('facultySelect');
    const programSelect = document.getElementById('programSelect');

    function filterPrograms() {
        const fid = facultySelect.value;

        [...programSelect.options].forEach(opt => {
            if (!opt.value) return; // skip placeholder
            const pf = opt.getAttribute('data-faculty');
            opt.hidden = (fid && pf !== fid);
        });
    }

    facultySelect.addEventListener('change', filterPrograms);
    filterPrograms();
});
</script>
@endsection
