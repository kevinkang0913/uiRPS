@php
    $steps = [
        1 => 'Course Info',
        2 => 'Learning Materials',
        3 => 'Outcomes',
        4 => 'Assessment',
        5 => 'Planner',
        6 => 'Contract',
    ];
@endphp

<div class="progress mb-4" style="height: 30px;">
    @foreach ($steps as $num => $label)
        @php
            $isActive = ($num == $currentStep);
            $isAllowed = ($num <= $maxStep + 1); // step yg boleh diakses
        @endphp

        @if($isAllowed)
            <a href="{{ route('rps.create.step', $num) }}" 
               class="progress-bar {{ $num <= $currentStep ? 'bg-primary' : 'bg-info text-dark' }}"
               style="width: {{ 100 / count($steps) }}%">
                Step {{ $num }} <br><small>{{ $label }}</small>
            </a>
        @else
            <div class="progress-bar bg-light text-dark"
                 style="width: {{ 100 / count($steps) }}%">
                Step {{ $num }} <br><small>{{ $label }}</small>
            </div>
        @endif
    @endforeach
</div>
