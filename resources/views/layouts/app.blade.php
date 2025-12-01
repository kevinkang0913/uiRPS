<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'RPS System') }}</title>

    {{-- Vite (Breeze) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Bootstrap & Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Custom Styles --}}
    <style>
        body { background: #f8f9fa; }
        .brandbar { background-color: #002147; } /* Biru UPH */
        .logo-circle {
            width: 36px; height: 36px; border-radius: 50%;
            background: #fff; color: #002147; display: flex;
            align-items: center; justify-content: center; font-weight: bold;
        }
        .sidebar {
            width: 240px; height: 100vh; position: fixed; top: 56px; left: 0;
            background-color: #004080; padding-top: 1rem; overflow-y: auto;
        }
        .sidebar .nav-link { color: #fff; border-radius: .375rem; }
        .sidebar .nav-link.active, .sidebar .nav-link:hover { background-color: #0056b3; }
        main { margin-left: 240px; padding: 80px 20px 20px; }
        .avatar-circle {
            width: 32px; height: 32px; border-radius: 50%;
            background: #fff; color: #002147; font-weight: bold;
            display: flex; align-items: center; justify-content: center; font-size: 14px;
        }
    </style>
</head>
<body>
    {{-- Topbar --}}
    <nav class="navbar navbar-expand-lg navbar-dark brandbar fixed-top shadow-sm" style="background-color: #003366;">
        <div class="container-fluid px-4">
            {{-- Brand --}}
            <div class="d-flex align-items-center gap-3">
                <!-- Logo UPH -->
                <img src="{{ asset('images/logo-uph.png') }}" alt="UPH Logo" style="height:40px; width:auto;">
                <!-- Teks Brand -->
                <span class="brand fw-bold text-white">UPH — RPS Management</span>
            </div>

            {{-- User dropdown --}}
            <ul class="navbar-nav ms-auto">
                @auth
                    @php
                        $user = auth()->user();
                        // gabung semua nama role, misal: "Dosen, Kaprodi"
                        $roleLabel = $user->roles->pluck('name')->join(', ');
                    @endphp
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                            <div class="avatar-circle me-2">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                            <span>
                                {{ $user->name }}
                                @if($roleLabel)
                                    ({{ $roleLabel }})
                                @endif
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="bi bi-gear me-2"></i> Profile
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a href="{{ route('login') }}" class="nav-link">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Login
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </nav>

    {{-- Sidebar --}}
    <aside class="sidebar text-white">
        <div class="p-3 small text-uppercase text-white-50">Navigation</div>
        <ul class="nav nav-pills flex-column px-2 gap-1">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}"
                   class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2 me-2"></i> Dashboard
                </a>
            </li>

            @auth
                @php $user = auth()->user(); @endphp

                {{-- RPS: Dosen & Super Admin --}}
                @if($user->hasAnyRole(['Dosen','Super Admin']))
                    <li class="nav-item">
                        <a href="{{ route('rps.index') }}"
                           class="nav-link {{ request()->routeIs('rps.*') ? 'active' : '' }}">
                            <i class="bi bi-journals me-2"></i> RPS
                        </a>
                    </li>
                @endif

                {{-- CTL + Super Admin: Review --}}
                @if($user->hasAnyRole(['CTL','Super Admin']))
                    <li class="nav-item">
                        <a href="{{ route('reviews.index') }}"
                           class="nav-link {{ request()->routeIs('reviews.*') ? 'active' : '' }}">
                            <i class="bi bi-chat-dots me-2"></i> Review
                        </a>
                    </li>
                @endif

                {{-- Kaprodi + Super Admin: Approval --}}
                @if($user->hasAnyRole(['Kaprodi','Super Admin']))
                    <li class="nav-item">
                        <a href="{{ route('approvals.index') }}"
                           class="nav-link {{ request()->routeIs('approvals.*') ? 'active' : '' }}">
                            <i class="bi bi-check2-circle me-2"></i> Approval
                        </a>
                    </li>
                @endif

                {{-- Admin Prodi/Fakultas + Super Admin: Admin Area --}}
                @if($user->hasAnyRole(['Admin','Super Admin']))
                    <li class="nav-item">
                        <a href="{{ route('admin.import.courses.form') }}"
                           class="nav-link {{ request()->routeIs('admin.import.courses.*') ? 'active' : '' }}">
                            <i class="bi bi-upload me-2"></i> Import Courses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('reports.export') }}"
                           class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <i class="bi bi-download me-2"></i> Export Laporan
                        </a>
                    </li>

                    <div class="p-3 small text-uppercase text-white-50 mt-3">Master Data</div>

                    <li class="nav-item">
                        <a href="{{ route('faculties.index') }}"
                           class="nav-link {{ request()->is('faculties*') ? 'active' : '' }}">
                            <i class="bi bi-buildings me-2"></i> Faculties
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('programs.index') }}"
                           class="nav-link {{ request()->is('programs*') ? 'active' : '' }}">
                            <i class="bi bi-diagram-3 me-2"></i> Programs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('courses.index') }}"
                           class="nav-link {{ request()->is('courses*') ? 'active' : '' }}">
                            <i class="bi bi-journal-text me-2"></i> Courses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('class-sections.index') }}"
                           class="nav-link {{ request()->is('class-sections*') ? 'active' : '' }}">
                            <i class="bi bi-collection me-2"></i> Class Sections
                        </a>
                    </li>

                    {{-- Users / Roles --}}
                    <li class="nav-item">
                        <a href="{{ route('users.index') }}"
                           class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="bi bi-people me-2"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('roles.index') }}"
                           class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                            <i class="bi bi-shield-lock me-2"></i> Roles
                        </a>
                    </li>

                    {{-- Activity Logs --}}
                    <li class="nav-item">
                        <a href="{{ route('activity-logs.index') }}"
                           class="nav-link {{ request()->is('activity-logs*') ? 'active' : '' }}">
                            <i class="bi bi-activity me-2"></i> Activity Logs
                        </a>
                    </li>
                @endif
            @endauth
        </ul>
    </aside>

    {{-- Main Content --}}
    <main>
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @yield('content')
        </div>
    </main>
</body>
</html>
