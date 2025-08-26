<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
  <div>
    <h3 class="mb-0">{{ $title }}</h3>
    @isset($subtitle)
      <div class="text-muted small">{{ $subtitle }}</div>
    @endisset
  </div>
  
  <div class="d-flex gap-2">
    {!! $slot ?? '' !!} {{-- penting: pakai {!! !!} agar HTML tombol dirender --}}
  </div>
</div>

</div>
