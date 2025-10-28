@extends('layouts.app')
@section('content')
<div class="container">
  <h2>Step 4: Assessment</h2>
  @include('rps.partials.progress', ['currentStep' => 4, 'maxStep' => $maxStep])

  {{-- error bag --}}
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @php
    // Ambil SubCLO dari session Step 3 untuk dijadikan pilihan
    $plos = $data['step3']['plos'] ?? [];
    $assessments = $data['step4']['assessments'] ?? [
      ['sub_key' => '', 'type' => 'Quiz', 'weight' => '']
    ];

    // Bangun list option: key "pi.ci.si" -> label
    $subCloOptions = [];
    foreach ($plos as $pi => $plo) {
      $ploDesc = $plo['description'] ?? 'PLO';
      $clos = $plo['clos'] ?? [];
      foreach ($clos as $ci => $clo) {
        $cloDesc = $clo['description'] ?? 'CLO';
        $subs = $clo['subclos'] ?? [];
        foreach ($subs as $si => $sub) {
          $label = "PLO ".($pi+1)." → CLO ".($ci+1)." → ".($sub ?: 'Sub-CLO');
          $subCloOptions[] = ['key' => "$pi.$ci.$si", 'label' => $label, 'sub' => $sub];
        }
      }
    }
  @endphp

  <form method="POST" action="{{ route('rps.store.step', 4) }}">
    @csrf

    <table class="table table-bordered table-sm align-middle">
      <thead class="table-light">
        <tr>
          <th style="width:45%">Sub-CLO</th>
          <th style="width:25%">Type</th>
          <th style="width:15%">Weight (%)</th>
          <th style="width:15%">Action</th>
        </tr>
      </thead>
      <tbody id="assessment-wrapper">
        @foreach($assessments as $i => $row)
        <tr>
          <td>
            <select name="assessments[{{ $i }}][sub_key]" class="form-select" required>
              <option value="">-- Select Sub-CLO --</option>
              @foreach($subCloOptions as $opt)
                <option value="{{ $opt['key'] }}"
                  {{ (($row['sub_key'] ?? '') === $opt['key']) ? 'selected' : '' }}>
                  {{ $opt['label'] }} — {{ $opt['sub'] }}
                </option>
              @endforeach
            </select>
          </td>
          <td>
            <select name="assessments[{{ $i }}][type]" class="form-select" required>
              @foreach(['Quiz','Assignment','Project','UTS','UAS'] as $t)
                <option value="{{ $t }}" {{ (($row['type'] ?? '') === $t) ? 'selected' : '' }}>{{ $t }}</option>
              @endforeach
            </select>
          </td>
          <td><input type="number" name="assessments[{{ $i }}][weight]" class="form-control" value="{{ $row['weight'] ?? '' }}" required></td>
          <td><button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
        </tr>
        @endforeach
      </tbody>
    </table>

    <button type="button" class="btn btn-sm btn-secondary" onclick="addRow()">+ Add Row</button>

    <div class="d-flex justify-content-between mt-3">
      <a href="{{ route('rps.create.step', 3) }}" class="btn btn-secondary">Back</a>
      <button type="submit" class="btn btn-primary">Next</button>
    </div>
  </form>
</div>

<script>
const subCloOptions = @json($subCloOptions);
let assessIndex = {{ count($assessments) }};

function optionHtml(selected='') {
  let html = `<option value="">-- Select Sub-CLO --</option>`;
  subCloOptions.forEach(o => {
    const sel = (o.key === selected) ? 'selected' : '';
    html += `<option value="${o.key}" ${sel}>${o.label} — ${o.sub || ''}</option>`;
  });
  return html;
}

function addRow() {
  const wrapper = document.getElementById('assessment-wrapper');
  const row = document.createElement('tr');
  row.innerHTML = `
    <td>
      <select name="assessments[${assessIndex}][sub_key]" class="form-select" required>
        ${optionHtml('')}
      </select>
    </td>
    <td>
      <select name="assessments[${assessIndex}][type]" class="form-select" required>
        <option>Quiz</option><option>Assignment</option><option>Project</option>
        <option>UTS</option><option>UAS</option>
      </select>
    </td>
    <td><input type="number" name="assessments[${assessIndex}][weight]" class="form-control" required></td>
    <td><button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
  `;
  wrapper.appendChild(row);
  assessIndex++;
}

function removeRow(btn) {
  btn.closest('tr').remove();
}
</script>
@endsection
