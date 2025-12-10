<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? config('app.name', 'RPS Preview') }}</title>

    {{-- Pakai asset yang sama seperti layout utama --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body class="bg-light">
    <main class="py-3">
        @yield('content')
    </main>
</body>
</html>
