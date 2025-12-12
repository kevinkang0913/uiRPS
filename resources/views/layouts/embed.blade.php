{{-- resources/views/layouts/embed.blade.php --}}
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'Preview RPS' }}</title>

  {{-- Bootstrap CSS via CDN (biar embed pasti ada formatting) --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    /* khusus embed: rapihin tampilan di modal/iframe */
    html, body { background: #f6f8fb; }
    .container-md { max-width: 980px; }
    .card { border-radius: 12px; }
    .table th, .table td { vertical-align: middle; }
    .table thead th { white-space: nowrap; }
    .table-responsive { border-radius: 12px; overflow: hidden; }
  </style>
</head>

<body class="py-3">
  @yield('content')

  {{-- Bootstrap JS (opsional, tapi bagus kalau ada komponen) --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
