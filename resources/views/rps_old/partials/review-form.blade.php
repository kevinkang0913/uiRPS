<!-- Form Review RPS -->
<div class="modal fade" id="reviewModal-{{ $rps->id }}" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="{{ route('reviews.store', $rps->id) }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="reviewModalLabel">Review RPS: {{ $rps->title }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="comments" class="form-label">Komentar</label>
            <textarea name="comments" id="comments" rows="4" class="form-control"></textarea>
          </div>
          <div class="mb-3">
            <label for="status" class="form-label">Hasil Review</label>
            <select name="status" id="status" class="form-select" required>
              <option value="reviewed">Diterima (Lanjut ke Approve)</option>
              <option value="needs_revision">Butuh Revisi</option>
              <option value="rejected">Ditolak</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Simpan Review</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>
