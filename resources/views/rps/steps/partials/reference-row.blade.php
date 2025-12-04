{{-- resources/views/rps/steps/partials/reference-row.blade.php --}}
@php
  // default values (ambil dari old() kalau ada, kalau tidak dari $r)
  $type = old("refs.$i.type", $r->type ?? 'utama');
  $text = old("refs.$i.text", $r->title ?? '');
  $url  = old("refs.$i.url",  $r->url   ?? '');
@endphp

<div class="ref-item border rounded p-3 position-relative bg-light-subtle">
  {{-- tombol hapus row --}}
  <button type="button"
          class="btn-close position-absolute top-0 end-0 m-2"
          aria-label="Hapus"
          onclick="this.closest('.ref-item').remove()">
  </button>

  <div class="row g-3">
    <div class="col-md-2">
      <label class="form-label mb-1">Tipe</label>
      <select name="refs[{{ $i }}][type]" class="form-select form-select-sm">
        <option value="utama"     {{ $type === 'utama' ? 'selected' : '' }}>Utama</option>
        <option value="pendukung" {{ $type === 'pendukung' ? 'selected' : '' }}>Pendukung</option>
        <option value="lainnya"   {{ $type === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
      </select>
    </div>

    <div class="col-md-6">
      <label class="form-label mb-1">Referensi</label>
      <textarea
        name="refs[{{ $i }}][text]"
        class="form-control form-control-sm"
        rows="2"
        placeholder="cth: Nama Penulis, Judul Buku/Jurnal, Tahun, Penerbit / Jurnal">{{ $text }}</textarea>
    </div>

    <div class="col-md-4">
      <label class="form-label mb-1">URL / DOI (opsional)</label>
      <input
        type="text"
        name="refs[{{ $i }}][url]"
        class="form-control form-control-sm"
        value="{{ $url }}"
        placeholder="https://... / DOI (opsional)">
    </div>
  </div>
</div>
