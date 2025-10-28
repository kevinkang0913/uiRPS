<!-- Form Approval Kaprodi -->
<div class="modal fade" id="approvalModal-{{ $rps->id }}" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="{{ route('approvals.store', $rps->id) }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="approvalModalLabel">Approval RPS: {{ $rps->title }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="notes" class="form-label">Catatan Approval</label>
            <textarea name="notes" id="notes" rows="3" class="form-control"></textarea>
          </div>
          <div class="mb-3">
            <label for="status" class="form-label">Keputusan</label>
            <select name="status" id="status" class="form-select" required>
              <option value="approved">Disetujui</option>
              <option value="rejected">Ditolak</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Simpan Approval</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>
