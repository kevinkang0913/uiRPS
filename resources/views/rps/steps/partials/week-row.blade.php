@php
  // Pastikan index numerik aman
  $rawIndex = isset($i) && is_numeric($i) ? (int)$i : null;

  // Default week number:
  // - Jika ada $w (edit), pakai $w->week_no
  // - Jika index numerik, pakai ($rawIndex + 1)
  // - Jika tidak, fallback 1 (nanti bisa diubah user)
  $defaultWeekNo = isset($w) && $w ? (int)$w->week_no : ( ($rawIndex !== null) ? ($rawIndex + 1) : 1 );

  // Pastikan array clos valid
  $selectedClos = [];
  if (isset($w) && $w && method_exists($w, 'outcomes')) {
      $selectedClos = $w->outcomes->pluck('id')->all();
  }

  $vals = [
    'week_no'         => $defaultWeekNo,
    'topic'           => $w->topic ?? '',
    'sub_topics'      => $w->sub_topics ?? '',
    'learning_method' => $w->learning_method ?? '',
    'student_activity'=> $w->student_activity ?? '',
    'media_tools'     => $w->media_tools ?? '',
    'weight_percent'  => $w->weight_percent ?? 0,
    'references'      => $w->references ?? '',
    'clos'            => $selectedClos,
  ];
@endphp

<div class="border rounded p-3 week-item position-relative">
  <div class="row g-2">
    <div class="col-md-1">
      <label class="form-label small mb-1">Minggu</label>
      <input
        type="number"
        class="form-control form-control-sm"
        name="weeks[{{ $i }}][week_no]"
        value="{{ $vals['week_no'] }}"
        min="1"
        required>
    </div>

    <div class="col-md-4">
      <label class="form-label small mb-1">Topik *</label>
      <input
        class="form-control form-control-sm"
        name="weeks[{{ $i }}][topic]"
        value="{{ $vals['topic'] }}"
        required>
    </div>

    <div class="col-md-2">
      <label class="form-label small mb-1">Bobot (%)</label>
      <input
        type="number"
        step="0.01"
        min="0"
        max="100"
        class="form-control form-control-sm"
        name="weeks[{{ $i }}][weight_percent]"
        value="{{ $vals['weight_percent'] }}">
    </div>

    <div class="col-md-5">
      <label class="form-label small mb-1">Metode</label>
      <input
        class="form-control form-control-sm"
        name="weeks[{{ $i }}][learning_method]"
        value="{{ $vals['learning_method'] }}"
        placeholder="Ceramah, diskusi, PBL, praktikum...">
    </div>
  </div>

  <div class="row g-2 mt-1">
    <div class="col-md-6">
      <label class="form-label small mb-1">Subtopik/Rincian</label>
      <textarea
        class="form-control form-control-sm"
        name="weeks[{{ $i }}][sub_topics]"
        rows="2">{{ $vals['sub_topics'] }}</textarea>
    </div>
    <div class="col-md-6">
      <label class="form-label small mb-1">Aktivitas Mahasiswa</label>
      <textarea
        class="form-control form-control-sm"
        name="weeks[{{ $i }}][student_activity]"
        rows="2">{{ $vals['student_activity'] }}</textarea>
    </div>
  </div>

  <div class="row g-2 mt-1">
    <div class="col-md-6">
      <label class="form-label small mb-1">Media/Tools</label>
      <input
        class="form-control form-control-sm"
        name="weeks[{{ $i }}][media_tools]"
        value="{{ $vals['media_tools'] }}">
    </div>
    <div class="col-md-6">
      <label class="form-label small mb-1">Referensi (opsional)</label>
      <textarea
        class="form-control form-control-sm"
        name="weeks[{{ $i }}][references]"
        rows="2"
        placeholder="Tulis sitasi singkat atau cuplikan dari daftar referensi">{{ $vals['references'] }}</textarea>
    </div>
  </div>

  <div class="mt-2">
    <label class="form-label small mb-1">CPMK terkait (multi pilih)</label>
    <div class="row g-1">
      @foreach($clos as $clo)
        @php $checked = in_array($clo->id, $vals['clos'], true); @endphp
        <div class="col-md-6">
          <div class="input-group input-group-sm">
            <div class="input-group-text">
              <input
                class="form-check-input mt-0"
                type="checkbox"
                name="weeks[{{ $i }}][clos][{{ $loop->index }}][id]"
                value="{{ $clo->id }}"
                @checked($checked)>
            </div>
            <input
              class="form-control"
              value="CPMK {{ $clo->no }} — {{ $clo->description }}"
              readonly>
            <input
              type="number"
              step="0.01"
              min="0"
              max="100"
              name="weeks[{{ $i }}][clos][{{ $loop->index }}][percent]"
              class="form-control"
              placeholder="% (opsional)">
          </div>
        </div>
      @endforeach
    </div>
  </div>

  <button
    type="button"
    class="btn btn-sm btn-outline-danger position-absolute"
    style="top:-10px; right:-10px;"
    onclick="this.closest('.week-item').remove()">×</button>
</div>
