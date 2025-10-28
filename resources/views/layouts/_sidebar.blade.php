<aside class="sidebar text-white">
  <div class="p-3 small text-uppercase text-white-50">Navigation</div>
  <ul class="nav nav-pills flex-column px-2 gap-1">
    <li class="nav-item"><a href="/dashboard" class="nav-link text-start"><i class="bi bi-grid-1x2 me-2"></i>Dashboard</a></li>
    <li class="nav-item"><a href="/rps" class="nav-link text-start"><i class="bi bi-journals me-2"></i>RPS</a></li>
    <li class="nav-item"><a href="/reviews" class="nav-link text-start"><i class="bi bi-chat-dots me-2"></i>Review</a></li>
    <li class="nav-item"><a href="/approvals" class="nav-link text-start"><i class="bi bi-check2-circle me-2"></i>Approval</a></li>
    <li class="nav-item"><a href="#" class="nav-link text-start"><i class="bi bi-upload me-2"></i>Import Courses</a></li>
    <li class="nav-item"><a href="#" class="nav-link text-start"><i class="bi bi-download me-2"></i>Export Laporan</a></li>
  </ul>

  <div class="p-3 small text-uppercase text-white-50 mt-3">Master Data</div>
<ul class="nav nav-pills flex-column px-2 gap-1">
    <li class="nav-item">
        <a href="{{ route('roles.index') }}" 
           class="nav-link text-start {{ request()->routeIs('roles.*') ? 'active' : '' }}">
            <i class="bi bi-shield-lock me-2"></i> Roles
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('users.roles.edit', Auth::user()->id) }}" 
           class="nav-link text-start">
            <i class="bi bi-person-gear me-2"></i> Assign Roles
        </a>
    </li>
</ul>

</aside>
