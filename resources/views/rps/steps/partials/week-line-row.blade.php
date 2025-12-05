{{-- resources/views/rps/steps/partials/week-line-row.blade.php --}}
@php
  $subCloId = $line['sub_clo_id'] ?? null;
  $topic    = $line['topic'] ?? '';
  $indicator= $line['indicator'] ?? '';
  $tech     = $line['assessment_technique'] ?? '';
  $criteria = $line['assessment_criteria'] ?? '';
  $learnIn  = $line['learning_in'] ?? '';
  $learnOn  = $line['learning_online'] ?? '';
  $refId    = $line['reference_id'] ?? null;
  $weight   = $line['weight_percent'] ?? null;
@endphp

<div class="border rounded p-2 bg-white week-line-item position-relative">
  <div class="row g-2">
    <div class="col-md-4">
      <label class="form-label small mb-1">Sub-CPMK *</label>
      <select
        name="weeks[{{ $wi }}][lines][{{ $li }}][sub_clo_id]"
        class="form-select form-select-sm"
        required>
        <option value="">— Pilih Sub-CPMK —</option>
        @foreach($subClos as $s)
          @php
            $label = 'Sub CPMK '.
                     ($s->outcome->no ?? '?').
                     '.'.$s->no.' — '.$s->description;
          @endphp
          <option value="{{ $s->id }}" @selected($subCloId == $s->id)>
            {{ $label }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="col-md-4">
      <label class="form-label small mb-1">Topik / Kemampuan Akhir *</label>
      <input
        type="text"
        name="weeks[{{ $wi }}][lines][{{ $li }}][topic]"
        class="form-control form-control-sm"
        value="{{ $topic }}"
        placeholder="Topik atau kemampuan akhir sesi"
        required>
    </div>

    <div class="col-md-2">
      <label class="form-label small mb-1">Bobot (%)</label>
      <input
        type="text"
        class="form-control form-control-sm text-end"
        value="{{ $weight !== null ? number_format($weight, 2) : '' }}"
        readonly
      >
    </div>
  </div>

  <div class="row g-2 mt-1">
    <div class="col-md-6">
      <label class="form-label small mb-1">Indikator Penilaian</label>
      <textarea
        name="weeks[{{ $wi }}][lines][{{ $li }}][indicator]"
        class="form-control form-control-sm"
        rows="2">{{ $indicator }}</textarea>
    </div>
    <div class="col-md-6">
      <label class="form-label small mb-1">Kriteria Penilaian</label>
      <textarea
        name="weeks[{{ $wi }}][lines][{{ $li }}][assessment_criteria]"
        class="form-control form-control-sm"
        rows="2">{{ $criteria }}</textarea>
    </div>
  </div>

  <div class="row g-2 mt-1">
    <div class="col-md-4">
      <label class="form-label small mb-1">Teknik Penilaian</label>
      <select
        name="weeks[{{ $wi }}][lines][{{ $li }}][assessment_technique]"
        class="form-select form-select-sm">
        <option value="">— Pilih Teknik —</option>
        @foreach($assessmentOptions as $opt)
          <option value="{{ $opt }}" @selected($tech === $opt)>
            {{ $opt }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="col-md-4">
      <label class="form-label small mb-1">Aktivitas Luring</label>
      <textarea
        name="weeks[{{ $wi }}][lines][{{ $li }}][learning_in]"
        class="form-control form-control-sm"
        rows="2">{{ $learnIn }}</textarea>
    </div>

    <div class="col-md-4">
      <label class="form-label small mb-1">Aktivitas Daring</label>
      <textarea
        name="weeks[{{ $wi }}][lines][{{ $li }}][learning_online]"
        class="form-control form-control-sm"
        rows="2">{{ $learnOn }}</textarea>
    </div>
  </div>

  <div class="row g-2 mt-1">
    <div class="col-md-6">
      <label class="form-label small mb-1">Referensi (dropdown dari Step 4)</label>
      <select
        name="weeks[{{ $wi }}][lines][{{ $li }}][reference_id]"
        class="form-select form-select-sm">
        <option value="">— Pilih Referensi —</option>
        @foreach($refs as $ref)
          @php
            $label = ($ref->type === 'utama' ? '[Utama] ' : '[Pendukung] ')
                     . \Illuminate\Support\Str::limit($ref->title, 80);
          @endphp
          <option value="{{ $ref->id }}" @selected($refId == $ref->id)>
            {{ $label }}
          </option>
        @endforeach
      </select>
    </div>
  </div>

  <button type="button"
          class="btn btn-sm btn-outline-danger position-absolute"
          style="top:-8px; right:-8px;"
          onclick="this.closest('.week-line-item').remove()">
    ×
  </button>
</div>
