@extends('layouts.app')

@section('content')
<div class="container-xxl py-4">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-7">
      <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
          <h4 class="mb-2">Akun Anda Menunggu Aktivasi Role</h4>
          <p class="text-muted mb-3">
            Terima kasih sudah mendaftar. Saat ini akun Anda belum memiliki role (Dosen/CTL/Kaprodi/Admin),
            sehingga belum bisa mengakses modul RPS.
          </p>

          <div class="alert alert-info mb-3">
            Silakan hubungi Admin/Super Admin untuk melakukan assign role.
          </div>

          <div class="d-flex gap-2">
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="btn btn-outline-secondary">Logout</button>
            </form>

            <a href="{{ route('dashboard') }}" class="btn btn-primary">Refresh</a>
          </div>

          <hr class="my-4">

          <div class="text-muted small">
            Jika Anda merasa ini kesalahan, kirimkan email/WA ke Admin dengan informasi akun Anda (nama & email).
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
