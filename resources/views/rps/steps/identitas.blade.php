{{-- resources/views/rps/steps/identitas.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-xxl">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0">Buat RPS — Step 1: Identitas Mata Kuliah</h4>
    @isset($rps)
      <div class="text-muted small">RPS ID: #{{ $rps->id }}</div>
    @endisset
  </div>

  {{-- Progress --}}
  <div class="progress mb-3" style="height:10px;">
    <div class="progress-bar bg-primary" role="progressbar" style="width: 16%;" aria-valuenow="16" aria-valuemin="0" aria-valuemax="100"></div>
  </div>

  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <b>Periksa input:</b>
      <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('rps.store.step', 1) }}" class="card shadow-sm border-0">
    @csrf
    <div class="card-body p-4">
      <div class="row g-4">
        {{-- Kolom kiri --}}
        <div class="col-lg-6">
          <div class="mb-3">
            <label class="form-label">Faculty</label>
            <select id="faculty" name="faculty_id" class="form-select" required></select>
          </div>
          <div class="mb-3">
            <label class="form-label">Program</label>
            <select id="program" name="program_id" class="form-select" required disabled></select>
          </div>
          <div class="mb-3">
            <label class="form-label">Course</label>
            <select id="course" name="course_id" class="form-select" required disabled></select>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Tahun Ajaran</label>
              <input name="academic_year" class="form-control" placeholder="2025/2026"
                     value="{{ old('academic_year', optional($rps)->academic_year) }}" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Semester</label>
              <input type="number" name="semester" class="form-control" min="1" max="14"
                     value="{{ old('semester', optional($rps)->semester ?? 1) }}" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">SKS</label>
              <input type="number" name="sks" class="form-control" min="1" max="10"
                     value="{{ old('sks', optional($rps)->sks ?? 3) }}">
            </div>
          </div>
        </div>

        {{-- Kolom kanan --}}
        <div class="col-lg-6">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Mode Perkuliahan</label>
              @php $m = old('delivery_mode', optional($rps)->delivery_mode); @endphp
              <select name="delivery_mode" class="form-select">
                <option value="">—</option>
                <option value="Luring"  @selected($m==='Luring')>Luring</option>
                <option value="Daring"  @selected($m==='Daring')>Daring</option>
                <option value="Hybrid"  @selected($m==='Hybrid')>Hybrid</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Bahasa</label>
              <input name="language" class="form-control"
                     value="{{ old('language', optional($rps)->language) }}"
                     placeholder="Indonesia / English">
            </div>
          </div>

          <div class="mt-3">
            <label class="form-label d-flex align-items-center justify-content-between">
              <span>Dosen Pengampu</span>
              <button type="button" class="btn btn-sm btn-outline-primary" onclick="addLecturer()">+ Tambah Dosen</button>
            </label>
            <div id="lecturersWrap" class="vstack gap-2"></div>
            <div class="form-text">Isi minimal 1 dosen. Email & NIDN opsional.</div>
          </div>
        </div>
      </div>
    </div>

    <div class="card-footer bg-light d-flex justify-content-end gap-2">
      <a href="{{ route('rps.index') }}" class="btn btn-outline-secondary">Batal</a>
      <button class="btn btn-primary">Simpan & Lanjut ke Step 2</button>
    </div>
  </form>
</div>

{{-- JS --}}
<script>
const apiFaculties = "{{ route('api.faculties') }}";
const apiPrograms  = (fid) => "{{ route('api.programs.byFaculty', ':id') }}".replace(':id', fid);
const apiCourses   = (pid) => "{{ route('api.courses.byProgram', ':id') }}".replace(':id', pid);

const selFaculty = document.getElementById('faculty');
const selProgram = document.getElementById('program');
const selCourse  = document.getElementById('course');

const current = {
  program_id: "{{ optional($rps)->program_id }}",
  course_id:  "{{ optional($rps)->course_id }}",
};

function opt(v,t,selected=false){ const o=document.createElement('option');o.value=v;o.text=t;if(selected)o.selected=true;return o; }

async function loadFaculties(){
  selFaculty.innerHTML = '';
  selProgram.innerHTML = ''; selProgram.disabled = true;
  selCourse.innerHTML  = ''; selCourse.disabled  = true;

  const res = await fetch(apiFaculties); const list = await res.json();
  selFaculty.appendChild(opt('','-- Select Faculty --'));
  list.forEach(f => selFaculty.appendChild(opt(f.id, f.name)));
}

async function loadPrograms(fid, preselectId = null){
  selProgram.innerHTML = ''; selCourse.innerHTML=''; selCourse.disabled=true;
  selProgram.disabled = true;
  if(!fid) return;

  const res = await fetch(apiPrograms(fid)); const list = await res.json();
  selProgram.appendChild(opt('','-- Select Program --'));
  list.forEach(p => selProgram.appendChild(opt(p.id, p.name, String(p.id)===String(preselectId))));
  selProgram.disabled = false;
}

async function loadCourses(pid, preselectId = null){
  selCourse.innerHTML = '';
  selCourse.disabled = true;
  if(!pid) return;

  const res = await fetch(apiCourses(pid)); const list = await res.json();
  selCourse.appendChild(opt('','-- Select Course --'));
  list.forEach(c => selCourse.appendChild(opt(c.id, `${c.name} — ${c.code}`, String(c.id)===String(preselectId))));
  selCourse.disabled = false;
}

selFaculty.addEventListener('change', async (e) => {
  await loadPrograms(e.target.value, null);
});

selProgram.addEventListener('change', async (e) => {
  await loadCourses(e.target.value, null);
});

// ===== Lecturers dynamic =====
const lectWrap = document.getElementById('lecturersWrap');
const lectInit = @json(optional($rps)->lecturers ?? []);

function addLecturer(item={name:'',email:'',nidn:''}){
  const idx = lectWrap.children.length;
  const row = document.createElement('div');
  row.className = 'row g-2 align-items-end';
  row.innerHTML = `
    <div class="col-md-4">
      <label class="form-label">Nama</label>
      <input name="lecturers[${idx}][name]" class="form-control" value="${item.name||''}" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Email</label>
      <input name="lecturers[${idx}][email]" class="form-control" value="${item.email||''}" placeholder="nama@uph.edu">
    </div>
    <div class="col-md-3">
      <label class="form-label">NIDN</label>
      <input name="lecturers[${idx}][nidn]" class="form-control" value="${item.nidn||''}">
    </div>
    <div class="col-md-1 d-grid">
      <button type="button" class="btn btn-outline-danger" onclick="this.closest('.row').remove()">×</button>
    </div>
  `;
  lectWrap.appendChild(row);
}

(async function init(){
  await loadFaculties();
  // (Opsional) kalau mau auto-preselect berdasarkan data existing, tambahkan endpoint meta.
  if (lectInit.length){ lectInit.forEach(addLecturer); } else { addLecturer(); }
})();
</script>
@endsection
