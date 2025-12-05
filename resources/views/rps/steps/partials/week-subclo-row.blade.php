{{-- resources/views/rps/steps/partials/week-subclo-row.blade.php --}}
@php
  $subCloId = $subId ?? null;
@endphp

<div class="subclo-item d-flex align-items-center gap-2">
  <div class="flex-grow-1">
    <select
      name="weeks[{{ $wi }}][sub_clos][{{ $si }}]"
      class="form-select form-select-sm subclo-select"
      required>
      <option value="">— Pilih Sub-CPMK —</option>
      @foreach($subClos as $s)
        @php
          $label = 'Sub CPMK '
                 . ($s->outcome->no ?? '?')
                 . '.' . $s->no
                 . ' — ' . $s->description
                 . ' (' . number_format((float)($s->weight_percent ?? 0), 2) . '%)';
        @endphp
        <option value="{{ $s->id }}"
          @selected((string)$subCloId === (string)$s->id)>
          {{ $label }}
        </option>
      @endforeach
    </select>
  </div>

  <button type="button"
          class="btn btn-sm btn-outline-danger btn-remove-subclo">
    ×
  </button>
</div>
