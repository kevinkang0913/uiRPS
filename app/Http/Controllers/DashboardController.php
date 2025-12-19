<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Role utama untuk dashboard (prioritas)
        $role = $this->roleLabel($user);

        // Flag widget di blade
        $show = $this->widgetFlags($role);

        // ===== Default agar blade aman =====
        $totalCurrent = 0;
        $byStatus = collect();
        $needActionCount = 0;

        $tasks = collect();
        $trend = collect();
        $topPrograms = collect();
        $statusChart = collect();

        $notices = collect();
        $calendarEvents = [];

        $overdue = collect();
        $dueSoon = collect();

        $workflowAlertsCount = 0;
        $overdueCount = 0;
        $dueSoonCount = 0;

        // =========================
        // DOSEN (scoped by course_lecturers)
        // =========================
        if ($role === 'Dosen') {

            $courseIds = DB::table('course_lecturers')
                ->where('user_id', $user->id)
                ->pluck('course_id')
                ->all();

            $totalCurrent = DB::table('rps')
                ->where('is_current', 1)
                ->whereIn('course_id', $courseIds)
                ->count();

            $byStatus = DB::table('rps')
                ->select('status', DB::raw('COUNT(*) as total'))
                ->where('is_current', 1)
                ->whereIn('course_id', $courseIds)
                ->groupBy('status')
                ->pluck('total', 'status');

            // Need action dosen: draft + need_revision
            $needActionCount = ($byStatus['draft'] ?? 0) + ($byStatus['need_revision'] ?? 0);

            $tasks = DB::table('rps')
                ->join('courses', 'courses.id', '=', 'rps.course_id')
                ->select('rps.id','rps.status','rps.updated_at','courses.code','courses.name')
                ->where('rps.is_current', 1)
                ->whereIn('rps.course_id', $courseIds)
                ->whereIn('rps.status', ['draft','need_revision'])
                ->orderByRaw("FIELD(rps.status,'need_revision','draft')")
                ->orderByDesc('rps.updated_at')
                ->limit(10)
                ->get();

            $trend = DB::table('rps')
                ->select(DB::raw("DATE_FORMAT(submitted_at, '%Y-%u') as year_week"), DB::raw('COUNT(*) as total'))
                ->whereNotNull('submitted_at')
                ->where('submitted_at', '>=', now()->subWeeks(12))
                ->whereIn('course_id', $courseIds)
                ->groupBy('year_week')
                ->orderBy('year_week')
                ->get();

            // Dosen: tidak perlu SLA panel
            $overdue = collect();
            $dueSoon = collect();
            $workflowAlertsCount = 0;
            $overdueCount = 0;
            $dueSoonCount = 0;

            // Notices hanya untuk RPS yang ia pegang
            $notices = DB::table('activity_logs')
                ->join('rps', 'rps.id', '=', 'activity_logs.rps_id')
                ->whereIn('rps.course_id', $courseIds)
                ->orderByDesc('activity_logs.created_at')
                ->limit(8)
                ->get();

            $calendarEvents = $this->calendarForCourseIds($courseIds);

            return view('dashboard', compact(
                'role','show',
                'totalCurrent','byStatus','needActionCount',
                'workflowAlertsCount','overdueCount','dueSoonCount',
                'tasks','trend','topPrograms','statusChart','notices','calendarEvents',
                'overdue','dueSoon'
            ));
        }

        // =========================
        // CTL (GLOBAL)
        // =========================
        if ($role === 'CTL') {

            $totalCurrent = DB::table('rps')->where('is_current', 1)->count();

            $byStatus = DB::table('rps')
                ->select('status', DB::raw('COUNT(*) as total'))
                ->where('is_current', 1)
                ->groupBy('status')
                ->pluck('total', 'status');

            $needActionCount = ($byStatus['submitted'] ?? 0) + ($byStatus['revision_submitted'] ?? 0);

            $tasks = DB::table('rps')
                ->join('courses', 'courses.id', '=', 'rps.course_id')
                ->join('programs', 'programs.id', '=', 'courses.program_id')
                ->select('rps.id','rps.status','rps.submitted_at','courses.code','courses.name','programs.name as program_name')
                ->where('rps.is_current', 1)
                ->whereIn('rps.status', ['submitted','revision_submitted'])
                ->orderByDesc('rps.submitted_at')
                ->limit(12)
                ->get();

            // SLA 14 hari setelah submitted_at untuk status submitted & revision_submitted
            $overdue = DB::table('rps')
                ->join('courses', 'courses.id', '=', 'rps.course_id')
                ->select('rps.id','rps.status','rps.submitted_at','courses.code','courses.name')
                ->where('rps.is_current', 1)
                ->whereIn('rps.status', ['submitted','revision_submitted'])
                ->whereNotNull('rps.submitted_at')
                ->where('rps.submitted_at', '<=', now()->subDays(14))
                ->orderBy('rps.submitted_at')
                ->limit(10)
                ->get();

            $dueSoon = DB::table('rps')
                ->join('courses', 'courses.id', '=', 'rps.course_id')
                ->select('rps.id','rps.status','rps.submitted_at','courses.code','courses.name')
                ->where('rps.is_current', 1)
                ->whereIn('rps.status', ['submitted','revision_submitted'])
                ->whereNotNull('rps.submitted_at')
                ->whereBetween('rps.submitted_at', [now()->subDays(13), now()])
                ->orderBy('rps.submitted_at')
                ->limit(10)
                ->get();

            $overdueCount = $overdue->count();
            $dueSoonCount = $dueSoon->count();
            $workflowAlertsCount = $overdueCount + $dueSoonCount;

            $trend = DB::table('rps')
                ->select(DB::raw("DATE_FORMAT(submitted_at, '%Y-%u') as year_week"), DB::raw('COUNT(*) as total'))
                ->whereNotNull('submitted_at')
                ->where('submitted_at', '>=', now()->subWeeks(12))
                ->groupBy('year_week')
                ->orderBy('year_week')
                ->get();

            $topPrograms = DB::table('rps')
                ->join('courses', 'courses.id', '=', 'rps.course_id')
                ->join('programs', 'programs.id', '=', 'courses.program_id')
                ->select('programs.name', DB::raw('COUNT(*) as total'))
                ->where('rps.is_current', 1)
                ->groupBy('programs.id','programs.name')
                ->orderByDesc('total')
                ->limit(5)
                ->get();

            $notices = DB::table('activity_logs')->orderByDesc('created_at')->limit(8)->get();
            $calendarEvents = $this->calendarGlobalWithDue14();

            return view('dashboard', compact(
                'role','show',
                'totalCurrent','byStatus','needActionCount',
                'workflowAlertsCount','overdueCount','dueSoonCount',
                'tasks','trend','topPrograms','statusChart','notices','calendarEvents',
                'overdue','dueSoon'
            ));
        }

        // =========================
        // KAPRODI (SCOPED by program_id)
        // =========================
        if ($role === 'Kaprodi') {

            $programIds = $this->scopedProgramIds($user);

            // Safety: kalau belum set scope
            if (empty($programIds)) {
                return view('dashboard', compact(
                    'role','show',
                    'totalCurrent','byStatus','needActionCount',
                    'workflowAlertsCount','overdueCount','dueSoonCount',
                    'tasks','trend','topPrograms','statusChart','notices','calendarEvents',
                    'overdue','dueSoon'
                ));
            }

            $base = DB::table('rps')
                ->join('courses','courses.id','=','rps.course_id')
                ->where('rps.is_current', 1)
                ->whereIn('courses.program_id', $programIds);

            $totalCurrent = (clone $base)->count();

            $byStatus = (clone $base)
                ->select('rps.status', DB::raw('COUNT(*) as total'))
                ->groupBy('rps.status')
                ->pluck('total', 'status');

            // Need action Kaprodi: reviewed (menunggu approval)
            $needActionCount = $byStatus['reviewed'] ?? 0;

            $tasks = (clone $base)
                ->join('programs','programs.id','=','courses.program_id')
                ->select('rps.id','rps.status','rps.submitted_at','courses.code','courses.name','programs.name as program_name')
                ->whereIn('rps.status', ['reviewed'])
                ->orderByDesc('rps.submitted_at')
                ->limit(12)
                ->get();

            // SLA 14 hari berdasarkan submitted_at untuk status reviewed
            $overdue = (clone $base)
                ->select('rps.id','rps.status','rps.submitted_at','courses.code','courses.name')
                ->whereIn('rps.status', ['reviewed'])
                ->whereNotNull('rps.submitted_at')
                ->where('rps.submitted_at', '<=', now()->subDays(14))
                ->orderBy('rps.submitted_at')
                ->limit(10)
                ->get();

            $dueSoon = (clone $base)
                ->select('rps.id','rps.status','rps.submitted_at','courses.code','courses.name')
                ->whereIn('rps.status', ['reviewed'])
                ->whereNotNull('rps.submitted_at')
                ->whereBetween('rps.submitted_at', [now()->subDays(13), now()])
                ->orderBy('rps.submitted_at')
                ->limit(10)
                ->get();

            $overdueCount = $overdue->count();
            $dueSoonCount = $dueSoon->count();
            $workflowAlertsCount = $overdueCount + $dueSoonCount;

            $trend = (clone $base)
                ->select(DB::raw("DATE_FORMAT(rps.submitted_at, '%Y-%u') as year_week"), DB::raw('COUNT(*) as total'))
                ->whereNotNull('rps.submitted_at')
                ->where('rps.submitted_at', '>=', now()->subWeeks(12))
                ->groupBy('year_week')
                ->orderBy('year_week')
                ->get();

            // Pie status untuk Kaprodi
            $statusChart = (clone $base)
                ->select('rps.status as name', DB::raw('COUNT(*) as total'))
                ->groupBy('rps.status')
                ->orderByDesc('total')
                ->get();

            // Kaprodi: kita tidak pakai TopPrograms bar (biarkan kosong)
            $topPrograms = collect();

            $notices = DB::table('activity_logs')
                ->join('rps','rps.id','=','activity_logs.rps_id')
                ->join('courses','courses.id','=','rps.course_id')
                ->whereIn('courses.program_id', $programIds)
                ->orderByDesc('activity_logs.created_at')
                ->limit(8)
                ->get();

            $calendarEvents = $this->calendarForProgramIds($programIds);

            return view('dashboard', compact(
                'role','show',
                'totalCurrent','byStatus','needActionCount',
                'workflowAlertsCount','overdueCount','dueSoonCount',
                'tasks','trend','topPrograms','statusChart','notices','calendarEvents',
                'overdue','dueSoon'
            ));
        }

        // =========================
        // ADMIN (SCOPED by faculty_id)
        // =========================
        if ($role === 'Admin') {

            $facultyIds = $this->scopedFacultyIds($user);

            // Safety: kalau belum set scope fakultas
            if (empty($facultyIds)) {
                return view('dashboard', compact(
                    'role','show',
                    'totalCurrent','byStatus','needActionCount',
                    'workflowAlertsCount','overdueCount','dueSoonCount',
                    'tasks','trend','topPrograms','statusChart','notices','calendarEvents',
                    'overdue','dueSoon'
                ));
            }

            // base: rps -> courses -> programs (filter faculty)
            $base = DB::table('rps')
                ->join('courses','courses.id','=','rps.course_id')
                ->join('programs','programs.id','=','courses.program_id')
                ->where('rps.is_current', 1)
                ->whereIn('programs.faculty_id', $facultyIds);

            $totalCurrent = (clone $base)->count();

            $byStatus = (clone $base)
                ->select('rps.status', DB::raw('COUNT(*) as total'))
                ->groupBy('rps.status')
                ->pluck('total', 'status');

            $needActionCount =
                ($byStatus['submitted'] ?? 0)
                + ($byStatus['revision_submitted'] ?? 0)
                + ($byStatus['reviewed'] ?? 0);

            $tasks = (clone $base)
                ->select('rps.id','rps.status','rps.submitted_at','courses.code','courses.name')
                ->whereIn('rps.status', ['submitted','revision_submitted','reviewed'])
                ->orderByDesc('rps.submitted_at')
                ->limit(12)
                ->get();

            $monitorStatuses = ['submitted','revision_submitted','reviewed'];

            $overdue = (clone $base)
                ->select('rps.id','rps.status','rps.submitted_at','courses.code','courses.name')
                ->whereIn('rps.status', $monitorStatuses)
                ->whereNotNull('rps.submitted_at')
                ->where('rps.submitted_at', '<=', now()->subDays(14))
                ->orderBy('rps.submitted_at')
                ->limit(10)
                ->get();

            $dueSoon = (clone $base)
                ->select('rps.id','rps.status','rps.submitted_at','courses.code','courses.name')
                ->whereIn('rps.status', $monitorStatuses)
                ->whereNotNull('rps.submitted_at')
                ->whereBetween('rps.submitted_at', [now()->subDays(13), now()])
                ->orderBy('rps.submitted_at')
                ->limit(10)
                ->get();

            $overdueCount = $overdue->count();
            $dueSoonCount = $dueSoon->count();
            $workflowAlertsCount = $overdueCount + $dueSoonCount;

            $trend = (clone $base)
                ->select(DB::raw("DATE_FORMAT(rps.submitted_at, '%Y-%u') as year_week"), DB::raw('COUNT(*) as total'))
                ->whereNotNull('rps.submitted_at')
                ->where('rps.submitted_at', '>=', now()->subWeeks(12))
                ->groupBy('year_week')
                ->orderBy('year_week')
                ->get();

            // Top 5 Prodi dalam fakultas admin
            $topPrograms = (clone $base)
                ->select('programs.name', DB::raw('COUNT(*) as total'))
                ->groupBy('programs.id','programs.name')
                ->orderByDesc('total')
                ->limit(5)
                ->get();

            $notices = DB::table('activity_logs')
                ->join('rps','rps.id','=','activity_logs.rps_id')
                ->join('courses','courses.id','=','rps.course_id')
                ->join('programs','programs.id','=','courses.program_id')
                ->whereIn('programs.faculty_id', $facultyIds)
                ->orderByDesc('activity_logs.created_at')
                ->limit(8)
                ->get();

            $calendarEvents = $this->calendarForFacultyIds($facultyIds);

            return view('dashboard', compact(
                'role','show',
                'totalCurrent','byStatus','needActionCount',
                'workflowAlertsCount','overdueCount','dueSoonCount',
                'tasks','trend','topPrograms','statusChart','notices','calendarEvents',
                'overdue','dueSoon'
            ));
        }

        // =========================
        // SUPER ADMIN (GLOBAL monitoring)
        // =========================
        // $role === 'Super Admin' atau fallback user lain yang kamu izinkan global
        $totalCurrent = DB::table('rps')->where('is_current', 1)->count();

        $byStatus = DB::table('rps')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->where('is_current', 1)
            ->groupBy('status')
            ->pluck('total', 'status');

        $needActionCount =
            ($byStatus['submitted'] ?? 0)
            + ($byStatus['revision_submitted'] ?? 0)
            + ($byStatus['reviewed'] ?? 0);

        $tasks = DB::table('rps')
            ->join('courses', 'courses.id', '=', 'rps.course_id')
            ->select('rps.id','rps.status','rps.submitted_at','courses.code','courses.name')
            ->where('rps.is_current', 1)
            ->whereIn('rps.status', ['submitted','revision_submitted','reviewed'])
            ->orderByDesc('rps.submitted_at')
            ->limit(12)
            ->get();

        $monitorStatuses = ['submitted','revision_submitted','reviewed'];

        $overdue = DB::table('rps')
            ->join('courses', 'courses.id', '=', 'rps.course_id')
            ->select('rps.id','rps.status','rps.submitted_at','courses.code','courses.name')
            ->where('rps.is_current', 1)
            ->whereIn('rps.status', $monitorStatuses)
            ->whereNotNull('rps.submitted_at')
            ->where('rps.submitted_at', '<=', now()->subDays(14))
            ->orderBy('rps.submitted_at')
            ->limit(10)
            ->get();

        $dueSoon = DB::table('rps')
            ->join('courses', 'courses.id', '=', 'rps.course_id')
            ->select('rps.id','rps.status','rps.submitted_at','courses.code','courses.name')
            ->where('rps.is_current', 1)
            ->whereIn('rps.status', $monitorStatuses)
            ->whereNotNull('rps.submitted_at')
            ->whereBetween('rps.submitted_at', [now()->subDays(13), now()])
            ->orderBy('rps.submitted_at')
            ->limit(10)
            ->get();

        $overdueCount = $overdue->count();
        $dueSoonCount = $dueSoon->count();
        $workflowAlertsCount = $overdueCount + $dueSoonCount;

        $trend = DB::table('rps')
            ->select(DB::raw("DATE_FORMAT(submitted_at, '%Y-%u') as year_week"), DB::raw('COUNT(*) as total'))
            ->whereNotNull('submitted_at')
            ->where('submitted_at', '>=', now()->subWeeks(12))
            ->groupBy('year_week')
            ->orderBy('year_week')
            ->get();

        $topPrograms = DB::table('rps')
            ->join('courses', 'courses.id', '=', 'rps.course_id')
            ->join('programs', 'programs.id', '=', 'courses.program_id')
            ->select('programs.name', DB::raw('COUNT(*) as total'))
            ->where('rps.is_current', 1)
            ->groupBy('programs.id','programs.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $notices = DB::table('activity_logs')->orderByDesc('created_at')->limit(8)->get();
        $calendarEvents = $this->calendarGlobalWithDue14();

        return view('dashboard', compact(
            'role','show',
            'totalCurrent','byStatus','needActionCount',
            'workflowAlertsCount','overdueCount','dueSoonCount',
            'tasks','trend','topPrograms','statusChart','notices','calendarEvents',
            'overdue','dueSoon'
        ));
    }

    // =========================
    // HELPERS
    // =========================

    private function roleLabel($user): string
    {
        // PRIORITAS dashboard (yang tertinggi menang)
        foreach (['Super Admin','Admin','CTL','Kaprodi','Dosen'] as $r) {
            if ($user->hasRole($r)) return $r;
        }
        return 'User';
    }

    private function widgetFlags(string $role): array
    {
        return [
            'showTopPrograms' => in_array($role, ['CTL','Kaprodi','Admin','Super Admin']),
            'showTrend'       => true,
            'showNotices'     => true,
            'showCalendar'    => true,
            'showSla'         => in_array($role, ['CTL','Kaprodi','Admin','Super Admin']),
        ];
    }

    private function scopedProgramIds($user): array
    {
        if (DB::getSchemaBuilder()->hasTable('user_scopes')) {
            $ids = DB::table('user_scopes')
                ->where('user_id', $user->id)
                ->whereNotNull('program_id')
                ->pluck('program_id')
                ->unique()
                ->values()
                ->all();

            if (!empty($ids)) return array_map('intval', $ids);
        }

        if (isset($user->program_id) && $user->program_id) {
            return [(int) $user->program_id];
        }

        return [];
    }

    private function scopedFacultyIds($user): array
    {
        if (DB::getSchemaBuilder()->hasTable('user_scopes')) {
            $ids = DB::table('user_scopes')
                ->where('user_id', $user->id)
                ->whereNotNull('faculty_id')
                ->pluck('faculty_id')
                ->unique()
                ->values()
                ->all();

            if (!empty($ids)) return array_map('intval', $ids);
        }

        if (isset($user->faculty_id) && $user->faculty_id) {
            return [(int) $user->faculty_id];
        }

        return [];
    }

    // Calendar: dosen (course-based) + due 14 hari
    private function calendarForCourseIds(array $courseIds): array
    {
        $events = [];

        if (empty($courseIds)) return $events;

        $rows = DB::table('rps')
            ->select('id','submitted_at')
            ->where('is_current', 1)
            ->whereIn('course_id', $courseIds)
            ->whereNotNull('submitted_at')
            ->orderByDesc('submitted_at')
            ->limit(30)
            ->get();

        foreach ($rows as $r) {
            $submitDate = date('Y-m-d', strtotime($r->submitted_at));
            $dueDate    = date('Y-m-d', strtotime($r->submitted_at . ' +14 days'));

            $events[] = ['title' => "RPS #{$r->id} submitted", 'start' => $submitDate, 'url' => route('rps.show', $r->id)];
            $events[] = ['title' => "Due (RPS #{$r->id})",     'start' => $dueDate,   'url' => route('rps.show', $r->id)];
        }

        return $events;
    }

    // Calendar: kaprodi (program-based)
    private function calendarForProgramIds(array $programIds): array
    {
        $events = [];

        if (empty($programIds)) return $events;

        $rows = DB::table('rps')
            ->join('courses','courses.id','=','rps.course_id')
            ->select('rps.id','rps.submitted_at')
            ->where('rps.is_current', 1)
            ->whereIn('courses.program_id', $programIds)
            ->whereNotNull('rps.submitted_at')
            ->orderByDesc('rps.submitted_at')
            ->limit(40)
            ->get();

        foreach ($rows as $r) {
            $submitDate = date('Y-m-d', strtotime($r->submitted_at));
            $dueDate    = date('Y-m-d', strtotime($r->submitted_at . ' +14 days'));

            $events[] = ['title' => "RPS #{$r->id} submitted", 'start' => $submitDate, 'url' => route('rps.show', $r->id)];
            $events[] = ['title' => "Due (RPS #{$r->id})",     'start' => $dueDate,   'url' => route('rps.show', $r->id)];
        }

        return $events;
    }

    // Calendar: admin (faculty-based)
    private function calendarForFacultyIds(array $facultyIds): array
    {
        $events = [];

        if (empty($facultyIds)) return $events;

        $rows = DB::table('rps')
            ->join('courses','courses.id','=','rps.course_id')
            ->join('programs','programs.id','=','courses.program_id')
            ->select('rps.id','rps.submitted_at')
            ->where('rps.is_current', 1)
            ->whereIn('programs.faculty_id', $facultyIds)
            ->whereNotNull('rps.submitted_at')
            ->orderByDesc('rps.submitted_at')
            ->limit(40)
            ->get();

        foreach ($rows as $r) {
            $submitDate = date('Y-m-d', strtotime($r->submitted_at));
            $dueDate    = date('Y-m-d', strtotime($r->submitted_at . ' +14 days'));

            $events[] = ['title' => "RPS #{$r->id} submitted", 'start' => $submitDate, 'url' => route('rps.show', $r->id)];
            $events[] = ['title' => "Due (RPS #{$r->id})",     'start' => $dueDate,   'url' => route('rps.show', $r->id)];
        }

        return $events;
    }

    // Calendar: global submit + due 14 hari + approvals (jika ada)
    private function calendarGlobalWithDue14(): array
    {
        $events = [];

        $rows = DB::table('rps')
            ->select('id','submitted_at')
            ->where('is_current', 1)
            ->whereNotNull('submitted_at')
            ->orderByDesc('submitted_at')
            ->limit(40)
            ->get();

        foreach ($rows as $r) {
            $submitDate = date('Y-m-d', strtotime($r->submitted_at));
            $dueDate    = date('Y-m-d', strtotime($r->submitted_at . ' +14 days'));

            $events[] = ['title' => "RPS #{$r->id} submitted", 'start' => $submitDate, 'url' => route('rps.show', $r->id)];
            $events[] = ['title' => "Due (RPS #{$r->id})",     'start' => $dueDate,   'url' => route('rps.show', $r->id)];
        }

        if (DB::getSchemaBuilder()->hasTable('approvals')) {
            $approvals = DB::table('approvals')
                ->select('rps_id','status','created_at')
                ->orderByDesc('created_at')
                ->limit(30)
                ->get();

            foreach ($approvals as $a) {
                $events[] = [
                    'title' => "RPS #{$a->rps_id} {$a->status}",
                    'start' => date('Y-m-d', strtotime($a->created_at)),
                    'url'   => route('rps.show', $a->rps_id),
                ];
            }
        }

        return $events;
    }
}
