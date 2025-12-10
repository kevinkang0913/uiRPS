@extends('layouts.app')

@section('content')
<div class="container-xxl">

  <style>
    .rubric-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .rubric-table th,
    .rubric-table td {
        border: 1px solid #dee2e6;
        padding: .4rem .5rem;
        vertical-align: top;
    }
    .rubric-table thead th {
        background: #f8f9fa;
        text-align: center;
    }
    .indicator-row {
        background: #e3f2fd;
        font-weight: 600;
    }
    .scale-cell {
        cursor: pointer;
        user-select: none;
        min-width: 70px;
        text-align: center;
        font-size: 12px;
        padding: .3rem .4rem;
    }
    .scale-cell span.level-badge {
        display: inline-block;
        border-radius: 999px;
        padding: 2px 8px;
        font-weight: 600;
        border: 1px solid #dee2e6;
        background: #f9f9f9;
    }
    .scale-cell.active span.level-badge {
        background: #2e7d32;
        color: #fff;
        border-color: #2e7d32;
    }
    .scale-desc {
        display: block;
        margin-top: 2px;
        font-size: 11px;
        color: #555;
        text-align: left;
    }
    .notes-input {
        font-size: 12px;
    }
  </style>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">Review RPS</h4>
      <div class="text-muted small">
        RPS #{{ $rps->id }} — {{ $rps->course->code ?? '' }} {{ $rps->course->name ?? '' }}
      </div>
    </div>
    <a href="{{ route('rps.show', $rps->id) }}" class="btn btn-sm btn-outline-secondary">
      ← Kembali ke Detail RPS
    </a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <b>Periksa input:</b>
      <ul class="mb-0">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('reviews.store', $rps->id) }}">
    @csrf

    <div class="card mb-3 shadow-sm border-0">
      <div class="card-body">
        <div class="mb-2 small text-muted">
          Rubrik versi: <strong>{{ $rubric['version'] ?? '-' }}</strong>
        </div>

        <table class="rubric-table">
          <thead>
            <tr>
              <th style="width:40px;">No</th>
              <th style="width:220px;">Indikator</th>
              <th>Kriteria</th>
              <th style="width:90px;">Skor 5</th>
              <th style="width:90px;">Skor 4</th>
              <th style="width:90px;">Skor 3</th>
              <th style="width:90px;">Skor 2</th>
              <th style="width:90px;">Skor 1</th>
              <th style="width:200px;">Catatan</th>
            </tr>
          </thead>
          <tbody>
          @foreach($rubric['indicators'] as $ind)
            <tr class="indicator-row">
              <td colspan="9">
                {{ $ind['no'] ?? '' }}. {{ $ind['title'] ?? '' }}
              </td>
            </tr>

            @foreach($ind['criteria'] as $crit)
              @php
                $key      = $crit['key'];
                $label    = $crit['label'];
                $scale    = $crit['scale'] ?? [];
                // existing choice (kalau review sudah pernah diisi)
                $existingItem = $itemsByKey[$key] ?? null;
                $selectedLevel = old("scores.$key", $existingItem->level_index ?? null);
                $notesVal      = old("notes.$key", $existingItem->notes ?? '');
              @endphp

              <tr data-criterion="{{ $key }}">
                <td class="text-center align-middle">{{ $key }}</td>
                <td class="align-middle small">
                  {{ $ind['title'] ?? '' }}
                </td>
                <td class="small">
                  {!! nl2br(e($label)) !!}
                  @if(!empty($crit['notes_hint']))
                    <div class="text-muted small mt-1">
                      <em>Hint: {!! nl2br(e($crit['notes_hint'])) !!}</em>
                    </div>
                  @endif
                </td>

                @for ($lvl = 5; $lvl >= 1; $lvl--)
                  @php
                    $desc = $scale[$lvl] ?? '';
                    $isActive = ((int)$selectedLevel === $lvl);
                  @endphp
                  <td class="scale-cell {{ $isActive ? 'active' : '' }}"
                      data-level="{{ $lvl }}"
                      onclick="selectLevel('{{ $key }}', {{ $lvl }}, this)">
                    @if($desc || true)
                      <span class="level-badge">{{ $lvl }}</span>
                      @if($desc)
                        <span class="scale-desc">{!! nl2br(e($desc)) !!}</span>
                      @endif
                    @endif
                  </td>
                @endfor

                <td>
                  <textarea name="notes[{{ $key }}]"
                            rows="3"
                            class="form-control form-control-sm notes-input"
                            placeholder="Catatan untuk kriteria ini">{{ $notesVal }}</textarea>
                </td>

                {{-- input hidden utk menyimpan skor terpilih --}}
                <input type="hidden"
                       name="scores[{{ $key }}]"
                       value="{{ $selectedLevel }}">
              </tr>
            @endforeach
          @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <div class="card mb-3 shadow-sm border-0">
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label">Komentar umum untuk RPS ini</label>
          <textarea name="general_comment" rows="3" class="form-control">{{ old('general_comment', $existing->comments ?? '') }}</textarea>
        </div>

        <div class="mb-2">
          <label class="form-label">Keputusan</label>
          <div class="d-flex flex-wrap gap-3 small">
            <div class="form-check">
              <input class="form-check-input"
                    type="radio"
                    name="status"
                    id="status_revisi"
                    value="revisi"
                    required>
              <label class="form-check-label" for="status_revisi">
                  Perlu revisi (kembali ke Dosen)
              </label>
          </div>
            <div class="form-check">
              <input class="form-check-input"
                    type="radio"
                    name="status"
                    id="status_forwarded"
                    value="forwarded"
                    required>
                <label class="form-check-label" for="status_forwarded">
                    Layak diteruskan ke Kaprodi
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card-footer d-flex justify-content-between bg-light">
        <a href="{{ route('rps.show', $rps->id) }}" class="btn btn-outline-secondary">
          ← Batal
        </a>
        <button type="submit" class="btn btn-primary">
          Simpan Review
        </button>
      </div>
    </div>
  </form>
</div>

<script>
function selectLevel(criterionKey, level, cell) {
  // hapus active di semua cell baris ini
  const row = cell.closest('tr[data-criterion]');
  row.querySelectorAll('.scale-cell').forEach(td => td.classList.remove('active'));

  // set active di cell yg diklik
  cell.classList.add('active');

  // set value hidden input
  const hidden = row.querySelector('input[type="hidden"][name^="scores["]');
  if (hidden) {
    hidden.value = level;
  }
}
</script>
@endsection
