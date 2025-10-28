@extends('layouts.app')
@section('content')
<div class="container">
  <h2>Step 1: Course Info</h2>

  @include('rps.partials.progress', ['currentStep' => 1])

  <form method="POST" action="{{ route('rps.store.step', 1) }}">
    @csrf

    {{-- (Opsional) Kalau kamu mau isi otomatis dari dropdown course, field ini bisa dihapus --}}
    <div class="mb-3">
      <label class="form-label">Course Title (optional)</label>
      <input type="text" name="course_title" class="form-control" placeholder="(otomatis dari course, atau isi manual)">
    </div>

    <!-- Faculty -->
    <div class="mb-3">
      <label class="form-label">Faculty</label>
      <select id="faculty" class="form-select" required>
        <option value="">-- Select Faculty --</option>
      </select>
    </div>

    <!-- Program -->
    <div class="mb-3">
      <label class="form-label">Program</label>
      <select id="program" class="form-select" disabled required>
        <option value="">-- Select Program --</option>
      </select>
    </div>

    <!-- Course -->
    <div class="mb-3">
      <label class="form-label">Course</label>
      <select id="course" class="form-select" disabled required>
        <option value="">-- Select Course --</option>
      </select>
    </div>

    <!-- Section -->
    <div class="mb-3">
      <label class="form-label">Class Section</label>
      <select name="class_section_id" id="section" class="form-select" disabled required>
        <option value="">-- Select Section --</option>
      </select>
    </div>

    <!-- Description -->
    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="3"></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Next</button>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Load faculties
    fetch('{{ route('api.faculties') }}')
        .then(res => res.json())
        .then(data => {
            const facultySel = document.getElementById('faculty');
            facultySel.innerHTML = '<option value="">-- Select Faculty --</option>';
            data.forEach(f => {
                facultySel.innerHTML += `<option value="${f.id}">${f.name}</option>`;
            });
        });

    // Faculty → Program
    document.getElementById('faculty').addEventListener('change', function() {
        const id = this.value;
        const progSel = document.getElementById('program');
        const courseSel = document.getElementById('course');
        const secSel = document.getElementById('section');

        progSel.innerHTML = '<option value="">-- Select Program --</option>';
        courseSel.innerHTML = '<option value="">-- Select Course --</option>';
        secSel.innerHTML = '<option value="">-- Select Section --</option>';

        progSel.disabled = true; courseSel.disabled = true; secSel.disabled = true;

        if (!id) return;

        fetch('{{ url('/api/programs') }}/' + id)
            .then(res => res.json())
            .then(data => {
                progSel.disabled = false;
                data.forEach(p => {
                    progSel.innerHTML += `<option value="${p.id}">${p.name}</option>`;
                });
            });
    });

    // Program → Course
    document.getElementById('program').addEventListener('change', function() {
        const id = this.value;
        const courseSel = document.getElementById('course');
        const secSel = document.getElementById('section');

        courseSel.innerHTML = '<option value="">-- Select Course --</option>';
        secSel.innerHTML = '<option value="">-- Select Section --</option>';
        courseSel.disabled = true; secSel.disabled = true;

        if (!id) return;

        fetch('{{ url('/api/courses') }}/' + id)
            .then(res => res.json())
            .then(data => {
                courseSel.disabled = false;
                data.forEach(c => {
                    // 🔁 gunakan c.name (BUKAN title)
                    courseSel.innerHTML += `<option value="${c.id}">${c.code} - ${c.name}</option>`;
                });
            });
    });

    // Course → Section
    document.getElementById('course').addEventListener('change', function() {
        const id = this.value;
        const secSel = document.getElementById('section');

        secSel.innerHTML = '<option value="">-- Select Section --</option>';
        secSel.disabled = true;

        if (!id) return;

        fetch('{{ url('/api/sections') }}/' + id)
            .then(res => res.json())
            .then(data => {
                secSel.disabled = false;
                data.forEach(s => {
                    // 🔁 gunakan s.class_section (BUKAN name)
                    secSel.innerHTML += `<option value="${s.id}">${s.class_section} (${s.semester ?? '-'})</option>`;
                });
            });
    });
});
</script>
@endsection
