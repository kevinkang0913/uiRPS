{{-- Aksi berdasarkan role --}}
@if(auth()->check())
    {{-- Jika user adalah CTL --}}
    @if(auth()->user()->role === 'ctl' && $rps->status === 'submitted')
        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#reviewModal-{{ $rps->id }}">
            Review
        </button>
        @include('rps.partials.review-form', ['rps' => $rps])
    @endif

    {{-- Jika user adalah Kaprodi --}}
    @if(auth()->user()->role === 'kaprodi' && $rps->status === 'reviewed')
        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approvalModal-{{ $rps->id }}">
            Approve / Reject
        </button>
        @include('rps.partials.approval-form', ['rps' => $rps])
    @endif
@endif
