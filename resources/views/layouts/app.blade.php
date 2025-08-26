<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>{{ $title ?? 'UPH RPS System' }}</title>

  {{-- Bootstrap 5 --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  {{-- Bootstrap Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --uph-navy:#002b5c;  /* UPH Navy */
      --uph-gold:#c69214;  /* UPH Gold */
      --uph-light:#f6f8fc;
    }
    body{background:var(--uph-light)}
    .brandbar{background:var(--uph-navy); color:#fff}
    .brandbar .brand{
      font-weight:700; letter-spacing:.3px
    }
    .sidebar{
      width: 260px; background:#0b2a4a; min-height:100vh;
      position: fixed; top: 56px; left: 0; padding-bottom: 2rem;
    }
    .sidebar a{color:#cfe2ff}
    .sidebar .active, .sidebar a:hover{color:#fff}
    .content{
      margin-left: 260px; padding: 24px; margin-top:56px
    }
    .btn-gold{background:var(--uph-gold); border-color:var(--uph-gold)}
    .btn-gold:hover{filter:brightness(.95)}
    .badge-uph{background:var(--uph-navy)}
    .card{border:0; box-shadow:0 4px 18px rgba(0,0,0,.06)}
    .table thead th{background:#f1f3f9}
    .logo-circle{width:34px; height:34px; border-radius:50%; background:var(--uph-gold); display:inline-grid; place-items:center; color:#111; font-weight:700}
  </style>
</head>
<body>
  {{-- Top Nav --}}
  @include('layouts._topnav')

  {{-- Sidebar --}}
  @include('layouts._sidebar')

  <main class="content">
    @yield('content')
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
