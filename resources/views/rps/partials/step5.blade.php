@extends('layouts.app')
@section('content')
<div class="container">
  <h2>Step 5: Planner (16 Weeks)</h2>
  @include('rps.partials.progress', ['currentStep' => 5, 'maxStep' => $maxStep])

  <form method="POST" action="{{ route('rps.store.step', 5) }}">
    @csrf
    <table class="table table-bordered table-sm align-middle">
      <thead class="table-light">
        <tr>
          <th>Week</th><th>Topic</th><th>Method</th><th>Assessment</th>
        </tr>
      </thead>
      <tbody>
        @for($i=1; $i<=16; $i++)
        <tr>
          <td>{{ $i }} <input type="hidden" name="planner[{{ $i }}][week]" value="{{ $i }}"></td>
          <td><input type="text" name="planner[{{ $i }}][topic]" class="form-control" required></td>
          <td><input type="text" name="planner[{{ $i }}][method]" class="form-control" required></td>
          <td><input type="text" name="planner[{{ $i }}][assessment]" class="form-control"></td>
        </tr>
        @endfor
      </tbody>
    </table>
    <div class="d-flex justify-content-between mt-3">
      <a href="{{ route('rps.create.step', 4) }}" class="btn btn-secondary">Back</a>
      <button type="submit" class="btn btn-primary">Next</button>
    </div>
  </form>
</div>
@endsection
