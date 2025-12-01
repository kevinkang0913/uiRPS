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

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <b>Periksa input:</b>
      <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  @php
    // Prefill untuk edit / lanjutkan
    $oldFaculty = old('faculty_id');
    $oldProgram = old('program_id');
    $oldCourse  = old('course_id');

    // kalau tidak ada old(), ambil dari relasi RPS → course → program → faculty
    $rpsCourse   = optional($rps)->course;
    $rpsProgram  = $rpsCourse ? $rpsCourse->program : null;

    $currentFacultyId = $oldFaculty ?: optional($rpsProgram)->faculty_id;
    $currentProgramId = $oldProgram ?: optional($rps)->program_id ?: optional($rpsCourse)->program_id;
    $currentCourseId  = $oldCourse  ?: optional($rps)->course_id;
  @endphp

  <form method="POST" action="{{ route('rps.store.step', 1) }}" class="card shadow-sm border-0">
    @csrf
    <div class="card-body p-4">
      <div class="row g-4">
        {{-- Kolom kiri --}}
        <div class="col-lg-6">
          <div class="mb-3">
            <label class="form-label">Facultas</label>
            <select id="faculty" name="faculty_id" class="form-select" required></select>
          </div>
          <div class="mb-3">
            <label class="form-label">Program Studi</label>
            <select id="program" name="program_id" class="form-select" required disabled></select>
          </div>
          <div class="mb-3">
            <label class="form-label">Mata Kuliah</label>
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

            {{-- NEW: Nomor kelas --}}
            <div class="col-md-3">
              <label class="form-label">Nomor Kelas</label>
              <input type="text" name="class_number" class="form-control"
                     placeholder="A / B / 01"
                     value="{{ old('class_number', optional($rps)->class_number) }}">
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

          {{-- NEW: Bentuk kegiatan & kategori MK --}}
          <div class="row g-3 mt-3">
            <div class="col-md-6">
              <label class="form-label">Bentuk Kegiatan Pembelajaran</label>
              @php $act = old('learning_activity_type', optional($rps)->learning_activity_type); @endphp
              <select name="learning_activity_type" class="form-select">
                <option value="">— Pilih —</option>
                <option value="Kuliah"           @selected($act === 'Kuliah')>Kuliah</option>
                <option value="Seminar"          @selected($act === 'Seminar')>Seminar</option>
                <option value="Praktikum"        @selected($act === 'Praktikum')>Praktikum</option>
                <option value="Merdeka Belajar"  @selected($act === 'Merdeka Belajar')>Merdeka Belajar</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Kategori Mata Kuliah</label>
              @php $cat = old('course_category', optional($rps)->course_category); @endphp
              <select name="course_category" class="form-select">
                <option value="">— Pilih —</option>
                <option value="MK wajib Universitas" @selected($cat === 'MK wajib Universitas')>MK wajib Universitas</option>
                <option value="MK wajib Fakultas"    @selected($cat === 'MK wajib Fakultas')>MK wajib Fakultas</option>
                <option value="MK wajib Prodi"       @selected($cat === 'MK wajib Prodi')>MK wajib Prodi</option>
                <option value="MK pilihan"           @selected($cat === 'MK pilihan')>MK pilihan</option>
              </select>
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

      {{-- NEW: Deskripsi, prasyarat, bahan kajian --}}
      <hr class="my-4">

      <div class="mb-3">
        <label class="form-label">Deskripsi Singkat Mata Kuliah</label>
        <textarea name="short_description" rows="3"
                  class="form-control"
                  placeholder="Jelaskan tujuan, ruang lingkup, dan fokus utama mata kuliah.">{{ old('short_description', optional($rps)->short_description) }}</textarea>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Mata Kuliah Prasyarat</label>
          <input type="text" name="prerequisite_courses" class="form-control"
                 placeholder="Contoh: MAT101, FIS102"
                 value="{{ old('prerequisite_courses', optional($rps)->prerequisite_courses) }}">
          <div class="form-text">
            Tulis kode / nama singkat MK yang harus diambil sebelum mata kuliah ini.
          </div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Menjadi Prasyarat Untuk Mata Kuliah</label>
          <input type="text" name="prerequisite_for_courses" class="form-control"
                 placeholder="Contoh: MK lanjutan yang bergantung pada mata kuliah ini"
                 value="{{ old('prerequisite_for_courses', optional($rps)->prerequisite_for_courses) }}">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Bahan Kajian</label>
        <textarea name="study_materials" rows="3"
                  class="form-control"
                  placeholder="Ringkas pokok bahasan utama atau tema besar mata kuliah.">{{ old('study_materials', optional($rps)->study_materials) }}</textarea>
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
  faculty_id: "{{ $currentFacultyId }}",
  program_id: "{{ $currentProgramId }}",
  course_id:  "{{ $currentCourseId }}",
};

function opt(v,t,selected=false){
  const o = document.createElement('option');
  o.value = v;
  o.text  = t;
  if (selected) o.selected = true;
  return o;
}

async function loadFaculties(){
  selFaculty.innerHTML = '';
  selProgram.innerHTML = ''; selProgram.disabled = true;
  selCourse.innerHTML  = ''; selCourse.disabled  = true;

  const res = await fetch(apiFaculties);
  const list = await res.json();

  selFaculty.appendChild(opt('','-- Select Faculty --'));
  list.forEach(f => {
    const selected = String(f.id) === String(current.faculty_id || '');
    selFaculty.appendChild(opt(f.id, f.name, selected));
  });

  // kalau sudah punya faculty di current, trigger loadPrograms
  if (current.faculty_id) {
    await loadPrograms(current.faculty_id, current.program_id || null);
    if (current.program_id) {
      await loadCourses(current.program_id, current.course_id || null);
    }
  }
}

async function loadPrograms(fid, preselectId = null){
  selProgram.innerHTML = '';
  selCourse.innerHTML=''; selCourse.disabled=true;
  selProgram.disabled = true;
  if(!fid) return;

  const res = await fetch(apiPrograms(fid));
  const list = await res.json();
  selProgram.appendChild(opt('','-- Select Program --'));
  list.forEach(p => {
    const selected = String(p.id) === String(preselectId || '');
    selProgram.appendChild(opt(p.id, p.name, selected));
  });
  selProgram.disabled = false;

  // kalau ada preselect program & current course, lanjut loadCourses
  if (preselectId && current.course_id) {
    await loadCourses(preselectId, current.course_id);
  }
}

async function loadCourses(pid, preselectId = null){
  selCourse.innerHTML = '';
  selCourse.disabled = true;
  if(!pid) return;

  const res = await fetch(apiCourses(pid));
  const list = await res.json();
  selCourse.appendChild(opt('','-- Select Course --'));
  list.forEach(c => {
    const selected = String(c.id) === String(preselectId || '');
    selCourse.appendChild(opt(c.id, `${c.name} — ${c.code}`, selected));
  });
  selCourse.disabled = false;
}

selFaculty.addEventListener('change', async (e) => {
  current.faculty_id = e.target.value || null;
  current.program_id = null;
  current.course_id  = null;
  await loadPrograms(e.target.value, null);
});

selProgram.addEventListener('change', async (e) => {
  current.program_id = e.target.value || null;
  current.course_id  = null;
  await loadCourses(e.target.value, null);
});

// ===== Lecturers dynamic =====
const lectWrap = document.getElementById('lecturersWrap');

// ambil lecturers dari RPS (array of {name,email,nidn}) atau old()
let lectInit = @json(old('lecturers', optional($rps)->lecturers ?? []));

// normalisasi lectInit supaya pasti array of object
if (!Array.isArray(lectInit)) {
  lectInit = [];
}

function addLecturer(item = {name:'',email:'',nidn:''}){
  const idx = lectWrap.children.length;
  const row = document.createElement('div');
  row.className = 'row g-2 align-items-end';
  row.innerHTML = `
    <div class="col-md-4">
      <label class="form-label">Nama</label>
      <input name="lecturers[${idx}][name]" class="form-control" value="${item.name || ''}" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Email</label>
      <input name="lecturers[${idx}][email]" class="form-control" value="${item.email || ''}" placeholder="nama@uph.edu">
    </div>
    <div class="col-md-3">
      <label class="form-label">NIDN</label>
      <input name="lecturers[${idx}][nidn]" class="form-control" value="${item.nidn || ''}">
    </div>
    <div class="col-md-1 d-grid">
      <button type="button" class="btn btn-outline-danger" onclick="this.closest('.row').remove()">×</button>
    </div>
  `;
  lectWrap.appendChild(row);
}

(async function init(){
  await loadFaculties();

  if (lectInit.length) {
    lectInit.forEach(l => {
      // jaga-jaga kalau strukturnya agak beda
      const item = {
        name:  l.name  || l.nama  || '',
        email: l.email || '',
        nidn:  l.nidn  || '',
      };
      addLecturer(item);
    });
  } else {
    addLecturer();
  }
})();
</script>
@endsection
