<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RPS System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ url('/') }}">
            📘 RPS System
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav" aria-controls="navbarNav"
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            {{-- Left side --}}
            <ul class="navbar-nav me-auto">
                {{-- Semua role bisa akses RPS --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('rps*') ? 'active' : '' }}" href="{{ route('rps.index') }}">
                        📄 RPS
                    </a>
                </li>

                {{-- Hanya CTL --}}
                @if(Auth::check() && Auth::user()->role === 'ctl')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('reviews*') ? 'active' : '' }}" href="#">
                            📝 Review
                        </a>
                    </li>
                @endif

                {{-- Hanya Kaprodi --}}
                @if(Auth::check() && Auth::user()->role === 'kaprodi')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('approvals*') ? 'active' : '' }}" href="#">
                            ✅ Approval
                        </a>
                    </li>
                @endif

                {{-- Hanya Admin --}}
                @if(Auth::check() && Auth::user()->role === 'admin')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('users*') ? 'active' : '' }}" href="#">
                            ⚙️ Manage Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('master*') ? 'active' : '' }}" href="#">
                            📚 Master Data
                        </a>
                    </li>
                @endif
            </ul>

            {{-- Right side --}}
            <ul class="navbar-nav">
                @auth
                    <li class="nav-item">
                        <span class="nav-link">👤 {{ Auth::user()->name }} ({{ ucfirst(Auth::user()->role) }})</span>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-link nav-link">Logout</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>


    <main class="py-4">
        @yield('content')
    </main>
</body>
</html>
