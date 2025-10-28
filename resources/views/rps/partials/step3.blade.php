@extends('layouts.app')
@section('content')
<div class="container">
  <h2>Step 3: Learning Outcomes (PLO → CLO → Sub-CLO)</h2>
  @include('rps.partials.progress', ['currentStep' => 3, 'maxStep' => $maxStep])

  {{-- Error bag --}}
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
    // Ambil dari session jika ada
    $plos = $data['step3']['plos'] ?? [
      ['description' => '', 'clos' => [
         ['description' => '', 'subclos' => ['']]
      ]]
    ];
  @endphp

  <form method="POST" action="{{ route('rps.store.step', 3) }}" id="step3Form">
    @csrf

    <div id="plos-wrapper" class="d-flex flex-column gap-3">
      @foreach($plos as $pi => $plo)
        <div class="card shadow-sm">
          <div class="card-header d-flex justify-content-between align-items-center">
            <strong>PLO #{{ $pi+1 }}</strong>
            <button class="btn btn-sm btn-outline-danger" type="button" onclick="removePlo(this)">Remove PLO</button>
          </div>
          <div class="card-body">
            <div class="mb-2">
              <label class="form-label">PLO Description</label>
              <input type="text" class="form-control" name="plos[{{ $pi }}][description]"
                     value="{{ $plo['description'] ?? '' }}" required>
            </div>

            <div class="table-responsive">
              <table class="table table-bordered table-sm align-middle mb-2">
                <thead class="table-light">
                  <tr>
                    <th style="width:45%">CLO</th>
                    <th style="width:55%">Sub-CLO(s)</th>
                  </tr>
                </thead>
                <tbody class="clos-wrapper">
                  @php $clos = $plo['clos'] ?? [['description'=>'','subclos'=>['']]]; @endphp
                  @foreach($clos as $ci => $clo)
                    <tr>
                      <td style="vertical-align: top;">
                        <input type="text" class="form-control mb-1"
                               name="plos[{{ $pi }}][clos][{{ $ci }}][description]"
                               value="{{ $clo['description'] ?? '' }}" required>
                        <button class="btn btn-sm btn-outline-danger" type="button" onclick="removeClo(this)">Remove CLO</button>
                      </td>
                      <td>
                        <div class="subclos-wrapper d-flex flex-column gap-2">
                          @php $subs = $clo['subclos'] ?? ['']; @endphp
                          @foreach($subs as $si => $sub)
                            <div class="input-group">
                              <input type="text" class="form-control"
                                     name="plos[{{ $pi }}][clos][{{ $ci }}][subclos][{{ $si }}]"
                                     value="{{ $sub ?? '' }}" required>
                              <button class="btn btn-outline-danger" type="button" onclick="removeSubClo(this)">×</button>
                            </div>
                          @endforeach
                        </div>
                        <button class="btn btn-sm btn-outline-secondary mt-2" type="button" onclick="addSubClo(this, {{ $pi }}, {{ $ci }})">+ Add Sub-CLO</button>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            <button class="btn btn-sm btn-outline-primary" type="button" onclick="addClo(this, {{ $pi }})">+ Add CLO</button>
          </div>
        </div>
      @endforeach
    </div>

    <div class="d-flex gap-2 mt-3">
      <button class="btn btn-outline-primary" type="button" onclick="addPlo()">+ Add PLO</button>
    </div>

    <div class="d-flex justify-content-between mt-4">
      <a href="{{ route('rps.create.step', 2) }}" class="btn btn-secondary">Back</a>
      <button type="submit" class="btn btn-primary">Next</button>
    </div>
  </form>
</div>

<script>
(function() {
  // Hitung indeks awal dari server
  let ploIndex = {{ count($plos) }};

  window.addPlo = function() {
    const wrapper = document.getElementById('plos-wrapper');
    const html = `
      <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
          <strong>PLO #<span class="plo-number"></span></strong>
          <button class="btn btn-sm btn-outline-danger" type="button" onclick="removePlo(this)">Remove PLO</button>
        </div>
        <div class="card-body">
          <div class="mb-2">
            <label class="form-label">PLO Description</label>
            <input type="text" class="form-control" name="plos[${ploIndex}][description]" required>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle mb-2">
              <thead class="table-light">
                <tr>
                  <th style="width:45%">CLO</th>
                  <th style="width:55%">Sub-CLO(s)</th>
                </tr>
              </thead>
              <tbody class="clos-wrapper">
                <tr>
                  <td style="vertical-align: top;">
                    <input type="text" class="form-control mb-1"
                           name="plos[${ploIndex}][clos][0][description]" required>
                    <button class="btn btn-sm btn-outline-danger" type="button" onclick="removeClo(this)">Remove CLO</button>
                  </td>
                  <td>
                    <div class="subclos-wrapper d-flex flex-column gap-2">
                      <div class="input-group">
                        <input type="text" class="form-control"
                               name="plos[${ploIndex}][clos][0][subclos][0]" required>
                        <button class="btn btn-outline-danger" type="button" onclick="removeSubClo(this)">×</button>
                      </div>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary mt-2" type="button" onclick="addSubClo(this, ${ploIndex}, 0)">+ Add Sub-CLO</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <button class="btn btn-sm btn-outline-primary" type="button" onclick="addClo(this, ${ploIndex})">+ Add CLO</button>
        </div>
      </div>`;
    wrapper.insertAdjacentHTML('beforeend', html);
    renumberPloCards();
    ploIndex++;
  };

  window.addClo = function(btn, pi) {
    const cloWrapper = btn.closest('.card-body').querySelector('.clos-wrapper');
    const current = cloWrapper.querySelectorAll('tr').length;
    const html = `
      <tr>
        <td style="vertical-align: top;">
          <input type="text" class="form-control mb-1"
                 name="plos[${pi}][clos][${current}][description]" required>
          <button class="btn btn-sm btn-outline-danger" type="button" onclick="removeClo(this)">Remove CLO</button>
        </td>
        <td>
          <div class="subclos-wrapper d-flex flex-column gap-2">
            <div class="input-group">
              <input type="text" class="form-control"
                     name="plos[${pi}][clos][${current}][subclos][0]" required>
              <button class="btn btn-outline-danger" type="button" onclick="removeSubClo(this)">×</button>
            </div>
          </div>
          <button class="btn btn-sm btn-outline-secondary mt-2" type="button" onclick="addSubClo(this, ${pi}, ${current})">+ Add Sub-CLO</button>
        </td>
      </tr>`;
    cloWrapper.insertAdjacentHTML('beforeend', html);
  };

  window.addSubClo = function(btn, pi, ci) {
    const subWrap = btn.parentElement.querySelector('.subclos-wrapper');
    const current = subWrap.querySelectorAll('.input-group').length;
    const html = `
      <div class="input-group">
        <input type="text" class="form-control"
               name="plos[${pi}][clos][${ci}][subclos][${current}]" required>
        <button class="btn btn-outline-danger" type="button" onclick="removeSubClo(this)">×</button>
      </div>`;
    subWrap.insertAdjacentHTML('beforeend', html);
  };

  window.removePlo = function(btn) {
    btn.closest('.card').remove();
    renumberPloCards();
  };

  window.removeClo = function(btn) {
    btn.closest('tr').remove();
  };

  window.removeSubClo = function(btn) {
    btn.closest('.input-group').remove();
  };

  function renumberPloCards() {
    document.querySelectorAll('#plos-wrapper .card').forEach((card, idx) => {
      const num = card.querySelector('.plo-number');
      if (num) num.textContent = (idx + 1);
    });
  }

  // initial numbering
  renumberPloCards();
})();
</script>
@endsection
