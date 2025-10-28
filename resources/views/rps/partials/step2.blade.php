@extends('layouts.app')
@section('content')
<div class="container">
  <h2>Step 2: Learning Materials</h2>
  @include('rps.partials.progress', ['currentStep' => 2, 'maxStep' => $maxStep])

  <form method="POST" action="{{ route('rps.store.step', 2) }}">
    @csrf
    @php $materials = $data['step2']['materials'] ?? [[]]; @endphp

    <table class="table table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th style="width:25%">Title</th>
          <th style="width:20%">Author</th>
          <th style="width:20%">Publisher</th>
          <th style="width:10%">Year</th>
          <th style="width:25%">Notes</th>
        </tr>
      </thead>
      <tbody id="materials-wrapper">
        @foreach($materials as $i => $mat)
        <tr>
          <td><input type="text" class="form-control" name="materials[{{ $i }}][title]" value="{{ $mat['title'] ?? '' }}" required></td>
          <td><input type="text" class="form-control" name="materials[{{ $i }}][author]" value="{{ $mat['author'] ?? '' }}"></td>
          <td><input type="text" class="form-control" name="materials[{{ $i }}][publisher]" value="{{ $mat['publisher'] ?? '' }}"></td>
          <td><input type="text" class="form-control" name="materials[{{ $i }}][year]" value="{{ $mat['year'] ?? '' }}"></td>
          <td><input type="text" class="form-control" name="materials[{{ $i }}][notes]" value="{{ $mat['notes'] ?? '' }}"></td>
        </tr>
        @endforeach
      </tbody>
    </table>

    <button type="button" class="btn btn-sm btn-secondary" onclick="addMaterial()">+ Add Row</button>

    <div class="d-flex justify-content-between mt-3">
      <a href="{{ route('rps.create.step', 1) }}" class="btn btn-secondary">Back</a>
      <button type="submit" class="btn btn-primary">Next</button>
    </div>
  </form>
</div>

<script>
let matIndex = {{ count($materials) }};
function addMaterial() {
  const wrapper = document.getElementById('materials-wrapper');
  const html = `
    <tr>
      <td><input type="text" class="form-control" name="materials[${matIndex}][title]" required></td>
      <td><input type="text" class="form-control" name="materials[${matIndex}][author]"></td>
      <td><input type="text" class="form-control" name="materials[${matIndex}][publisher]"></td>
      <td><input type="text" class="form-control" name="materials[${matIndex}][year]"></td>
      <td><input type="text" class="form-control" name="materials[${matIndex}][notes]"></td>
    </tr>`;
  wrapper.insertAdjacentHTML('beforeend', html);
  matIndex++;
}
</script>
@endsection
