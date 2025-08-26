@php($status = strtolower($status ?? 'submitted'))
@switch($status)
  @case('approved') <span class="badge bg-success">Approved</span> @break
  @case('reviewed') <span class="badge bg-info text-dark">Reviewed</span> @break
  @case('rejected') <span class="badge bg-danger">Rejected</span> @break
  @default <span class="badge bg-warning text-dark">Submitted</span>
@endswitch
