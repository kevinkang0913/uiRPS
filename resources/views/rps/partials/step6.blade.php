@extends('layouts.app')
@section('content')
<div class="container">
  <h2>Step 6: Course Contract</h2>
  @include('rps.partials.progress', ['currentStep' => 6, 'maxStep' => $maxStep])

  <form method="POST" action="{{ route('rps.store.step', 6) }}">
    @csrf
    <table class="table table-bordered table-sm align-middle">
      <tbody>
        <tr>
          <th style="width:30%">Attendance Policy</th>
          <td><textarea name="attendance_policy" class="form-control" required>{{ $data['step6']['attendance_policy'] ?? '' }}</textarea></td>
        </tr>
        <tr>
          <th>Participation Policy</th>
          <td><textarea name="participation_policy" class="form-control">{{ $data['step6']['participation_policy'] ?? '' }}</textarea></td>
        </tr>
        <tr>
          <th>Late Policy</th>
          <td><textarea name="late_policy" class="form-control">{{ $data['step6']['late_policy'] ?? '' }}</textarea></td>
        </tr>
        <tr>
          <th>Grading Policy</th>
          <td><textarea name="grading_policy" class="form-control" required>{{ $data['step6']['grading_policy'] ?? '' }}</textarea></td>
        </tr>
        <tr>
          <th>Extra Rules</th>
          <td><textarea name="extra_rules" class="form-control">{{ $data['step6']['extra_rules'] ?? '' }}</textarea></td>
        </tr>
      </tbody>
    </table>
    <div class="d-flex justify-content-between mt-3">
      <a href="{{ route('rps.create.step', 5) }}" class="btn btn-secondary">Back</a>
      <button type="submit" class="btn btn-success">Finish & Submit</button>
    </div>
  </form>
</div>
@endsection
