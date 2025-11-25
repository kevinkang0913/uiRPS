<div class="border rounded p-3 ref-item position-relative">
  <div class="row g-2">
    <div class="col-md-2">
      <label class="form-label small mb-1">Tipe</label>
      <select name="references[{{ $i }}][type]" class="form-select form-select-sm">
        <option value="utama"     @selected(($r->type ?? '') === 'utama')>Utama</option>
        <option value="pendukung" @selected(($r->type ?? '') === 'pendukung')>Pendukung</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label small mb-1">Penulis</label>
      <input name="references[{{ $i }}][author]" class="form-control form-control-sm"
             value="{{ $r->author ?? '' }}" placeholder="Nama Penulis">
    </div>
    <div class="col-md-1">
      <label class="form-label small mb-1">Tahun</label>
      <input name="references[{{ $i }}][year]" class="form-control form-control-sm"
             value="{{ $r->year ?? '' }}" placeholder="2023">
    </div>
    <div class="col-md-4">
      <label class="form-label small mb-1">Judul / Title *</label>
      <input name="references[{{ $i }}][title]" class="form-control form-control-sm"
             value="{{ $r->title ?? '' }}" required>
    </div>
    <div class="col-md-2">
      <label class="form-label small mb-1">Kota</label>
      <input name="references[{{ $i }}][city]" class="form-control form-control-sm"
             value="{{ $r->city ?? '' }}">
    </div>
  </div>

  <div class="row g-2 mt-1">
    <div class="col-md-3">
      <label class="form-label small mb-1">Penerbit</label>
      <input name="references[{{ $i }}][publisher]" class="form-control form-control-sm"
             value="{{ $r->publisher ?? '' }}">
    </div>
    <div class="col-md-3">
      <label class="form-label small mb-1">ISBN / ISSN</label>
      <input name="references[{{ $i }}][isbn_issn]" class="form-control form-control-sm"
             value="{{ $r->isbn_issn ?? '' }}">
    </div>
    <div class="col-md-6">
      <label class="form-label small mb-1">URL / DOI</label>
      <input name="references[{{ $i }}][url]" class="form-control form-control-sm"
             value="{{ $r->url ?? '' }}" placeholder="https://...">
    </div>
  </div>

  <button type="button" class="btn btn-sm btn-outline-danger position-absolute"
          style="top:-10px; right:-10px;"
          onclick="this.closest('.ref-item').remove()">×</button>
</div>
