@extends('layouts.app')

@section('content')
<div class="container-xxl py-3">

  {{-- Header --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
      <h4 class="mb-0">Dashboard</h4>
      <div class="text-muted small">
        Academic Control Center — Role: <span class="fw-semibold">{{ $role ?? 'User' }}</span>
      </div>
    </div>

    <div class="d-flex gap-2">
      <a href="{{ route('rps.index') }}" class="btn btn-outline-secondary btn-sm">Daftar RPS</a>

      @if(($role ?? '') === 'Dosen')
        <a href="{{ route('rps.start') }}" class="btn btn-primary btn-sm">+ Buat RPS</a>
      @elseif(($role ?? '') === 'CTL')
        <a href="{{ route('reviews.index') }}" class="btn btn-primary btn-sm">Buka Review Queue</a>
      @elseif(($role ?? '') === 'Kaprodi')
        <a href="{{ route('approvals.index') }}" class="btn btn-primary btn-sm">Buka Approval Queue</a>
      @else
        <a href="{{ route('rps.index') }}" class="btn btn-primary btn-sm">Monitoring</a>
      @endif
    </div>
  </div>

  {{-- KPI Cards --}}
  <div class="row g-3 mb-3">
    <div class="col-12 col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small">RPS Aktif</div>
          <div class="display-6 fw-semibold">{{ $totalCurrent ?? 0 }}</div>
          <div class="text-muted small">
            @if(($role ?? '') === 'Kaprodi')
              (Scoped ke prodi saya)
            @elseif(($role ?? '') === 'Dosen')
              (Mata kuliah yang saya pegang)
            @else
              is_current = 1
            @endif
          </div>
        </div>
      </div>
    </div>

    {{-- KPI #2: Admin/Super Admin => Workflow Alerts --}}
    <div class="col-12 col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          @if(in_array(($role ?? ''), ['Admin','Super Admin']))
            <div class="text-muted small">Workflow Alerts</div>
            <div class="display-6 fw-semibold">{{ $workflowAlertsCount ?? 0 }}</div>
            <div class="text-muted small">
              <span class="me-2">Overdue: <span class="fw-semibold">{{ $overdueCount ?? 0 }}</span></span>
              <span>Due Soon: <span class="fw-semibold">{{ $dueSoonCount ?? 0 }}</span></span>
            </div>
            <div class="text-muted small mt-1">
              Monitoring RPS yang mendekati / melewati SLA 14 hari (bukan tugas langsung Super Admin).
            </div>
          @else
            <div class="text-muted small">Butuh Tindakan</div>
            <div class="display-6 fw-semibold">{{ $needActionCount ?? 0 }}</div>
            <div class="text-muted small">
              @if(($role ?? '') === 'Dosen')
                Draft / Need Revision
              @elseif(($role ?? '') === 'CTL')
                Submitted / Revision Submitted (SLA 14 hari)
              @elseif(($role ?? '') === 'Kaprodi')
                Reviewed (SLA 14 hari, scoped prodi)
              @else
                Workflow queue (SLA 14 hari)
              @endif
            </div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-12 col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small mb-2">Distribusi Status</div>
          @php
            $statuses = ['draft','submitted','reviewed','need_revision','revision_submitted','approved','not_approved'];
          @endphp
          <div class="d-flex flex-wrap gap-2">
            @foreach($statuses as $s)
              <span class="badge rounded-pill text-bg-light border">
                {{ $s }}: <span class="fw-semibold">{{ $byStatus[$s] ?? 0 }}</span>
              </span>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">

    {{-- LEFT --}}
    <div class="col-12 col-lg-8">

      {{-- Tasks --}}
      <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
              <div class="fw-semibold">
                @if(($role ?? '') === 'Dosen') My Tasks
                @elseif(($role ?? '') === 'CTL') Review Queue
                @elseif(($role ?? '') === 'Kaprodi') Approval Queue
                @else Workflow Queue
                @endif
              </div>
              <div class="text-muted small">
                @if(($role ?? '') === 'Dosen') RPS yang perlu kamu lengkapi / revisi
                @elseif(($role ?? '') === 'CTL') RPS menunggu review CTL (target SLA 14 hari)
                @elseif(($role ?? '') === 'Kaprodi') RPS menunggu approval Kaprodi (scoped prodi, SLA 14 hari)
                @else Ringkasan antrian proses (SLA 14 hari)
                @endif
              </div>
            </div>

            <div class="d-flex gap-2">
              @if(($role ?? '') === 'CTL')
                <a class="btn btn-outline-primary btn-sm" href="{{ route('reviews.index') }}">Open Review</a>
              @elseif(($role ?? '') === 'Kaprodi')
                <a class="btn btn-outline-primary btn-sm" href="{{ route('approvals.index') }}">Open Approval</a>
              @else
                <a class="btn btn-outline-primary btn-sm" href="{{ route('rps.index') }}">Open List</a>
              @endif
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead class="text-muted small">
                <tr>
                  <th>RPS</th>
                  <th>Status</th>
                  <th class="text-end">Aksi</th>
                </tr>
              </thead>
              <tbody>
                @forelse(($tasks ?? []) as $t)
                  <tr>
                    <td class="small">
                      <div class="fw-semibold">{{ $t->code ?? '' }} {{ $t->name ?? '' }}</div>
                      @isset($t->program_name)
                        <div class="text-muted small">{{ $t->program_name }}</div>
                      @endisset
                      <div class="text-muted small">
                        @php $time = $t->submitted_at ?? $t->updated_at ?? null; @endphp
                        @if($time) {{ \Carbon\Carbon::parse($time)->diffForHumans() }} @endif
                      </div>
                    </td>
                    <td><span class="badge rounded-pill text-bg-light border">{{ $t->status ?? '-' }}</span></td>
                    <td class="text-end">
                      <a class="btn btn-sm btn-outline-secondary" href="{{ route('rps.show', $t->id) }}">Detail</a>

                      @if(($role ?? '') === 'Dosen' && in_array($t->status, ['draft','need_revision']))
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('rps.resume.auto', $t->id) }}">Lanjutkan</a>
                      @elseif(($role ?? '') === 'CTL')
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('reviews.edit', $t->id) }}">Review</a>
                      @elseif(($role ?? '') === 'Kaprodi')
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('approvals.edit', $t->id) }}">Approve</a>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="3" class="text-muted small">Tidak ada item.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {{-- Trend chart: tampil hanya jika ada data --}}
      @if(($show['showTrend'] ?? true) && ($trend ?? collect())->count() > 0)
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-body">
            <div class="fw-semibold">
              @if(($role ?? '') === 'Dosen') Tren Submit Saya (12 minggu) @else Tren Submit (12 minggu) @endif
            </div>
            <div class="text-muted small mb-2">Berdasarkan submitted_at</div>
            <canvas id="trendChart" height="115"></canvas>
          </div>
        </div>
      @endif

      {{-- Top chart: role tertentu --}}
      @if(($show['showTopPrograms'] ?? false))
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="fw-semibold">
              @if(($role ?? '') === 'Kaprodi')
                Distribusi Status RPS (Prodi saya)
              @else
                Top 5 Prodi (RPS aktif)
              @endif
            </div>
            <div class="text-muted small mb-2">
              @if(($role ?? '') === 'Kaprodi')
                Scoped ke program_id Kaprodi — pie chart status RPS
              @else
                Agregasi rps → courses → programs
              @endif
            </div>

            @if(($role ?? '') === 'Kaprodi')
              @if(($statusChart ?? collect())->count() > 0)
                <div class="d-flex justify-content-center">
                <canvas id="statusPieChart" style="max-width: 280px; max-height: 280px;"></canvas>
                </div>
              @else
                <div class="text-muted small">Tidak ada data status untuk ditampilkan.</div>
              @endif
            @else
              @if(($topPrograms ?? collect())->count() > 0)
                <canvas id="topProgramsChart" height="125"></canvas>
              @else
                <div class="text-muted small">Tidak ada data untuk ditampilkan.</div>
              @endif
            @endif
          </div>
        </div>
      @endif

    </div>

    {{-- RIGHT --}}
    <div class="col-12 col-lg-4">

      {{-- DOSEN: Calendar dulu --}}
      @if(($role ?? '') === 'Dosen')
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-body">
            <div class="fw-semibold mb-2">Calendar</div>
            <div id="calendar" style="min-height: 380px;"></div>
            <div class="text-muted small mt-2">Klik event untuk buka detail RPS.</div>
          </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
          <div class="card-body">
            <div class="fw-semibold mb-2">My Activity</div>
            <div class="list-group list-group-flush">
              @forelse(($notices ?? []) as $n)
                <div class="list-group-item px-0">
                  <div class="d-flex justify-content-between align-items-start">
                    <div class="fw-semibold">{{ $n->action ?? '-' }}</div>
                    <div class="text-muted small">{{ \Carbon\Carbon::parse($n->created_at ?? now())->diffForHumans() }}</div>
                  </div>
                  <div class="text-muted small">
                    RPS #{{ $n->rps_id ?? '-' }} — {{ \Illuminate\Support\Str::limit($n->notes ?? '-', 90) }}
                  </div>
                </div>
              @empty
                <div class="text-muted small">Belum ada activity logs.</div>
              @endforelse
            </div>
          </div>
        </div>

      @else
        {{-- SLA Alerts --}}
        @if(($show['showSla'] ?? false))
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
              <div class="fw-semibold mb-1">SLA Alerts</div>
              <div class="text-muted small mb-2">Overdue jika lewat 14 hari setelah submit</div>

              <div class="mb-2"><span class="badge rounded-pill text-bg-danger">Overdue</span></div>
              <div class="list-group list-group-flush mb-3">
                @forelse(($overdue ?? []) as $o)
                  <div class="list-group-item px-0">
                    <div class="d-flex justify-content-between">
                      <div class="fw-semibold small">{{ $o->code ?? '' }} {{ $o->name ?? '' }}</div>
                      <div class="text-muted small">{{ \Carbon\Carbon::parse($o->submitted_at)->diffForHumans() }}</div>
                    </div>
                    <div class="text-muted small">
                      RPS #{{ $o->id }} — status: {{ $o->status }}
                      • due: {{ \Carbon\Carbon::parse($o->submitted_at)->addDays(14)->toDateString() }}
                    </div>
                    <div class="mt-1">
                      <a class="btn btn-sm btn-outline-secondary" href="{{ route('rps.show', $o->id) }}">Detail</a>
                      @if(($role ?? '') === 'CTL')
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('reviews.edit', $o->id) }}">Review</a>
                      @elseif(($role ?? '') === 'Kaprodi')
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('approvals.edit', $o->id) }}">Approve</a>
                      @endif
                    </div>
                  </div>
                @empty
                  <div class="text-muted small">Tidak ada overdue.</div>
                @endforelse
              </div>

              <div class="mb-2"><span class="badge rounded-pill text-bg-warning">Due Soon</span></div>
              <div class="list-group list-group-flush">
                @forelse(($dueSoon ?? []) as $d)
                  <div class="list-group-item px-0">
                    <div class="d-flex justify-content-between">
                      <div class="fw-semibold small">{{ $d->code ?? '' }} {{ $d->name ?? '' }}</div>
                      <div class="text-muted small">
                        due: {{ \Carbon\Carbon::parse($d->submitted_at)->addDays(14)->diffForHumans() }}
                      </div>
                    </div>
                    <div class="text-muted small">
                      RPS #{{ $d->id }} — status: {{ $d->status }}
                    </div>
                  </div>
                @empty
                  <div class="text-muted small">Tidak ada yang mendekati deadline.</div>
                @endforelse
              </div>
            </div>
          </div>
        @endif

        {{-- Notices --}}
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-body">
            <div class="fw-semibold mb-2">Notices (Aktivitas Terbaru)</div>
            <div class="list-group list-group-flush">
              @forelse(($notices ?? []) as $n)
                <div class="list-group-item px-0">
                  <div class="d-flex justify-content-between align-items-start">
                    <div class="fw-semibold">{{ $n->action ?? '-' }}</div>
                    <div class="text-muted small">{{ \Carbon\Carbon::parse($n->created_at ?? now())->diffForHumans() }}</div>
                  </div>
                  <div class="text-muted small">
                    RPS #{{ $n->rps_id ?? '-' }} — {{ \Illuminate\Support\Str::limit($n->notes ?? '-', 90) }}
                  </div>
                </div>
              @empty
                <div class="text-muted small">Belum ada activity logs.</div>
              @endforelse
            </div>
          </div>
        </div>

        {{-- Calendar --}}
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="fw-semibold mb-2">Calendar</div>
            <div id="calendar" style="min-height: 380px;"></div>
            <div class="text-muted small mt-2">Klik event untuk buka detail RPS.</div>
          </div>
        </div>
      @endif

    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<script>
  const trendEl = document.getElementById('trendChart');
  if (trendEl) {
    const trendRaw = @json($trend ?? []);
    new Chart(trendEl, {
      type: 'line',
      data: {
        labels: trendRaw.map(x => x.year_week),
        datasets: [{ label: 'Submitted', data: trendRaw.map(x => x.total), tension: 0.35 }]
      },
      options: { responsive: true, plugins: { legend: { display: true } }, scales: { y: { beginAtZero: true } } }
    });
  }

  const topEl = document.getElementById('topProgramsChart');
  if (topEl) {
    const topRaw = @json($topPrograms ?? []);
    if (Array.isArray(topRaw) && topRaw.length) {
      new Chart(topEl, {
        type: 'bar',
        data: {
          labels: topRaw.map(x => x.name),
          datasets: [{ label: 'Total', data: topRaw.map(x => x.total) }]
        },
        options: { responsive: true, plugins: { legend: { display: true } }, scales: { y: { beginAtZero: true } } }
      });
    }
  }

  // Kaprodi: pie/doughnut distribusi status

  // Kaprodi: Status RPS Pie Chart (warna konsisten dengan status)
  const pieEl = document.getElementById('statusPieChart');
  if (pieEl) {
    const statusRaw = @json($statusChart ?? []);

    if (Array.isArray(statusRaw) && statusRaw.length) {

      // Mapping warna status
      const STATUS_COLORS = {
        draft: '#adb5bd',               // abu-abu
        submitted: '#0d6efd',           // biru
        revision_submitted: '#6ea8fe',  // biru muda
        reviewed: '#fd7e14',            // oranye
        approved: '#198754',            // hijau
        need_revision: '#dc3545',       // merah
        not_approved: '#343a40'         // gelap
      };

      const labels = statusRaw.map(x => x.name);
      const data   = statusRaw.map(x => x.total);
      const colors = labels.map(s => STATUS_COLORS[s] ?? '#6c757d');

      new Chart(pieEl, {
        type: 'doughnut',
        data: {
          labels: labels,
          datasets: [{
            data: data,
            backgroundColor: colors,
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '60%',
          plugins: {
            legend: {
              position: 'right',
              labels: {
                boxWidth: 14,
                usePointStyle: true,
                padding: 14,
                generateLabels(chart) {
                  const data = chart.data;
                  return data.labels.map((label, i) => ({
                    text: `${label} (${data.datasets[0].data[i]})`,
                    fillStyle: data.datasets[0].backgroundColor[i],
                    strokeStyle: data.datasets[0].backgroundColor[i],
                    lineWidth: 0,
                    hidden: false,
                    index: i
                  }));
                }
              }
            },
            tooltip: {
              callbacks: {
                label: function(ctx) {
                  return `${ctx.label}: ${ctx.raw} RPS`;
                }
              }
            }
          }
        }
      });
    }
  }
  document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('calendar');
    if (!el) return;

    const calendar = new FullCalendar.Calendar(el, {
      initialView: 'dayGridMonth',
      height: 380,
      events: @json($calendarEvents ?? []),
      eventClick: function(info) {
        if (info.event.url) { info.jsEvent.preventDefault(); window.location = info.event.url; }
      }
    });
    calendar.render();
  });
</script>
@endsection
