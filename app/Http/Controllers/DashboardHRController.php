<?php

namespace App\Http\Controllers;

use App\Mail\AnnouncementMail;
use App\Models\Employee;
use App\Models\Departments;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use App\Models\Fingerprints;
use App\Models\Contract;
use App\Models\Submissions;
use App\Models\Announcement;
use App\Models\Submissionposition;
use App\Models\Roster;
use App\Models\Leaverequest;
use App\Models\PayrollPeriod;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendAnnouncementEmailsJob;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendAnnouncementEmail;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

class DashboardHRController extends Controller
{
    /** Status karyawan yang dihitung sebagai headcount aktif. */
    private const ACTIVE_STATUSES = ['Active', 'Pending', 'Mutation', 'On Leave'];

    public function index(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();

        // ── Headcount ──
        $headcount = [
            'active'   => Employee::whereIn('status', self::ACTIVE_STATUSES)->count(),
            'pending'  => Employee::where('status', 'Pending')->count(),
            'joined'   => Employee::whereIn('status', self::ACTIVE_STATUSES)
                ->whereBetween('join_date', [$monthStart, $monthEnd])->count(),
            'resigned' => Employee::whereIn('status', ['Resign', 'Inactive'])
                ->whereBetween('end_date', [$monthStart, $monthEnd])->count(),
        ];

        // ── Kehadiran hari ini: dibandingkan dengan jadwal roster hari ini ──
        $activePins = Employee::whereIn('status', self::ACTIVE_STATUSES)->whereNotNull('pin')->pluck('pin')->all();
        $rosterToday = Roster::whereDate('date', $today)
            ->selectRaw('day_type, COUNT(*) as total')->groupBy('day_type')->pluck('total', 'day_type');
        $scheduledPins = Roster::whereDate('date', $today)->where('day_type', 'Work')
            ->join('employees_tables as e', 'e.id', '=', 'roster.employee_id')
            ->whereIn('e.status', self::ACTIVE_STATUSES)->pluck('e.pin')->filter()->all();
        $presentPins = Fingerprints::whereDate('scan_date', $today)
            ->whereIn('pin', $activePins)->distinct()->pluck('pin')->all();
        $presentYesterday = Fingerprints::whereDate('scan_date', $today->copy()->subDay())
            ->whereIn('pin', $activePins)->distinct()->count('pin');

        $scheduled = count($scheduledPins);
        $attendance = [
            'scheduled'        => $scheduled,
            'present'          => count($presentPins),
            'present_scheduled'=> count(array_intersect($scheduledPins, $presentPins)),
            'not_in_yet'       => count(array_diff($scheduledPins, $presentPins)),
            'yesterday'        => $presentYesterday,
            'leave'            => (int) ($rosterToday['Leave'] ?? 0) + (int) ($rosterToday['Cuti Melahirkan'] ?? 0),
            'sick'             => (int) ($rosterToday['Sick'] ?? 0),
            'off'              => (int) ($rosterToday['Off'] ?? 0) + (int) ($rosterToday['TOIL Off'] ?? 0),
            'holiday'          => (int) ($rosterToday['Public Holiday'] ?? 0),
            'has_roster'       => $rosterToday->isNotEmpty(),
            'last_scan'        => Fingerprints::max('scan_date'),
            'last_roster'      => Roster::max('date'),
        ];
        $attendance['rate'] = $scheduled > 0 ? round($attendance['present_scheduled'] / $scheduled * 100) : null;

        // ── Perlu tindakan ──
        $contractsQuery = Contract::with('employee:id,employee_name,employee_pengenal')
            ->whereDate('end_date', '>=', $today)
            ->whereDate('end_date', '<=', $today->copy()->addDays(30));
        $contractsExpiringCount = (clone $contractsQuery)->count();
        $expiringContracts = $contractsQuery->orderBy('end_date')->limit(6)->get()
            ->each(fn($c) => $c->days_left = $today->diffInDays(Carbon::parse($c->end_date), false));

        $positionRequests = Submissionposition::with('submitter')->where('status', 'Sent')->latest()->limit(6)->get();

        $actions = [
            'leave'    => Leaverequest::where('status', 'Pending')->count(),
            'position' => Submissionposition::where('status', 'Sent')->count(),
            'contract' => $contractsExpiringCount,
        ];

        $payrollPeriods = collect();
        if ($user->can('payroll.period.view') || $user->can('payroll.view')) {
            $payrollPeriods = PayrollPeriod::whereIn('status', ['open', 'closed'])
                ->withPayrollSummary()
                ->orderByDesc('period_year')->orderByDesc('period_month')
                ->get();
        }

        $activeAnnouncements = Announcement::whereDate('publish_date', '<=', $today)
            ->where(fn($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today))
            ->count();

        return view('pages.dashboardHR.dashboardHR', [
            'headcount'              => $headcount,
            'attendance'             => $attendance,
            'actions'                => $actions,
            'expiringContracts'      => $expiringContracts,
            'contractsExpiringCount' => $contractsExpiringCount,
            'positionRequests'       => $positionRequests,
            'payrollPeriods'         => $payrollPeriods,
            'activeAnnouncements'    => $activeAnnouncements,
        ]);
    }
 public function employeeByDepartment()
{
    $statuses = ['Active', 'Pending', 'Mutation', 'On Leave'];

    $data = Departments::query()
        ->withCount(collect($statuses)->mapWithKeys(function ($status) {
            $key = strtolower(str_replace(' ', '_', $status));
            return [
                "employees as {$key}_count" => fn($q) => $q->where('status', $status),
            ];
        })->toArray())
        ->orderBy('department_name')
        ->get()
        ->map(function ($dept) use ($statuses) {
            $counts = collect($statuses)->mapWithKeys(function ($status) use ($dept) {
                $key = strtolower(str_replace(' ', '_', $status));
                return [$key => (int) $dept->{"{$key}_count"}];
            });

            return [
                'department' => $dept->department_name,
                ...$counts->toArray(),
                'total' => $counts->sum(),
            ];
        })
        ->filter(fn($dept) => $dept['total'] > 0) // skip department kosong
        ->values();

    return response()->json($data);
}
 public function employeeByCompany()
{
    $statuses = ['Active', 'Pending', 'Mutation', 'On Leave'];

    $data = Company::query()
        ->withCount(collect($statuses)->mapWithKeys(function ($status) {
            $key = strtolower(str_replace(' ', '_', $status));
            return [
                "employees as {$key}_count" => fn($q) => $q->where('status', $status),
            ];
        })->toArray())
        ->orderBy('name')
        ->get()
        ->map(function ($comp) use ($statuses) {
            $counts = collect($statuses)->mapWithKeys(function ($status) use ($comp) {
                $key = strtolower(str_replace(' ', '_', $status));
                return [$key => (int) $comp->{"{$key}_count"}];
            });

            return [
                'company' => $comp->name,
                ...$counts->toArray(),
                'total' => $counts->sum(),
            ];
        })
        ->filter(fn($comp) => $comp['total'] > 0) // skip department kosong
        ->values();

    return response()->json($data);
}

public function employeeByLengthOfService()
{
    $data = DB::table('employees_tables')
        ->selectRaw("
            CASE
                WHEN TIMESTAMPDIFF(MONTH, join_date, CURDATE()) < 12 THEN '< 1 Year'
                WHEN TIMESTAMPDIFF(MONTH, join_date, CURDATE()) BETWEEN 12 AND 35 THEN '1-3 Years'
                WHEN TIMESTAMPDIFF(MONTH, join_date, CURDATE()) BETWEEN 36 AND 59 THEN '3-5 Years'
                WHEN TIMESTAMPDIFF(MONTH, join_date, CURDATE()) BETWEEN 60 AND 119 THEN '5-10 Years'
                ELSE '> 10 Years'
            END as range_label,
            COUNT(*) as total
        ")
        ->whereNotNull('join_date')
        ->whereIn('status', ['Active', 'Pending', 'Mutation', 'On Leave'])
        ->groupBy('range_label')
        ->orderByRaw("
            FIELD(range_label, '< 1 Year', '1-3 Years', '3-5 Years', '5-10 Years', '> 10 Years')
        ")
        ->get();

    return response()->json($data);
}

    public function getMonthlyData(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        if (!$startDate || !$endDate) {
            $monthDate = now();
            $startDate = $monthDate->copy()->startOfMonth()->toDateString();
            $endDate   = $monthDate->copy()->endOfMonth()->toDateString();
        }

        // Ambil semua pin karyawan aktif/pending
        $employeePins = Employee::whereIn('status', ['Active', 'Pending'])->pluck('pin')->toArray();
        $totalEmployees = count($employeePins);

        // Ambil data fingerprint per hari, hitung satu karyawan hanya 1 kali
        $data = Fingerprints::selectRaw('DATE(scan_date) as scan_day, COUNT(DISTINCT pin) as total')
            ->whereBetween('scan_date', [$startDate, $endDate])
            ->whereIn('pin', $employeePins) // pastikan hanya hitung pin valid
            ->groupBy('scan_day')
            ->orderBy('scan_day')
            ->get();

        $result = $data->map(function ($item) use ($totalEmployees) {
            $percentage = $totalEmployees > 0
                ? round(($item->total / $totalEmployees) * 100, 2)
                : 0;

            return [
                'date'       => $item->scan_day,
                'percentage' => $percentage,
            ];
        });

        return response()->json([
            'period'         => ['start' => $startDate, 'end' => $endDate],
            'totalEmployees' => $totalEmployees,
            'data'           => $result,
        ]);
    }
    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'publish_date' => 'required|date',
                'end_date' => 'nullable|date',
            ]);

            // Simpan announcement
            $announcement = Announcement::create([
                'title'        => $request->title,
                'content'      => $request->content,
                'publish_date' => $request->publish_date,
                'end_date'     => $request->end_date,
                'user_id'      => auth()->id(),
            ]);

            Log::info('Announcement created', [
                'announcement_id' => $announcement->id,
            ]);

            // Ambil employee
            $employees = Employee::whereNotNull('email')
    ->where('email', '!=', '')
    ->whereIn('status', ['Active', 'Pending', 'Mutation', 'On Leave'])
    ->get();

            // Kumpulkan jobs
           $jobs = [];
foreach ($employees as $employee) {
    $jobs[] = new SendAnnouncementEmail($announcement, $employee);
    // hapus ->onQueue() di sini, sudah di-set di constructor job
}

            // Dispatch batch
           Bus::batch($jobs)
    ->name("Send Announcement {$announcement->id}")
    ->onQueue('sendannouncement') // samakan dengan constructor job
    ->allowFailures()
    ->dispatch();

            Log::info('Email batch dispatched', [
                'announcement_id'  => $announcement->id,
                'total_recipients' => $employees->count(),
            ]);

            return redirect()->route('pages.dashboardHR')
                ->with('success', "Announcement created & emails are queued for {$employees->count()} employees.");
        } catch (\Throwable $e) {

            Log::error('Announcement creation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->withInput()->with('error', 'Failed to create announcement.');
        }
    }



    public function getAnnouncements()
    {
        $announcements = Announcement::with('user.Employee.department')
            ->select(['id', 'user_id', 'title', 'content', 'publish_date', 'end_date'])
            ->orderByDesc('publish_date')
            ->get()
            ->map(function ($announcement) {
                $announcement->id_hashed = substr(hash('sha256', $announcement->id . env('APP_KEY')), 0, 8);
                return $announcement;
            });

        return DataTables::of($announcements)

            ->addColumn('employee_name', function ($announcement) {
                return !empty($announcement->user->Employee->department) && !empty($announcement->user->Employee->department->first()->department_name)
                    ? $announcement->user->Employee->department->first()->department_name
                    : 'Empty';
            })
            ->addColumn('state', function ($announcement) {
                $today = Carbon::today();
                if (Carbon::parse($announcement->publish_date)->gt($today)) {
                    return 'scheduled';
                }
                return $announcement->end_date && Carbon::parse($announcement->end_date)->lt($today) ? 'ended' : 'active';
            })
            ->addColumn('publish_fmt', fn($a) => Carbon::parse($a->publish_date)->locale('id')->translatedFormat('d M Y'))
            ->addColumn('end_fmt', fn($a) => $a->end_date ? Carbon::parse($a->end_date)->locale('id')->translatedFormat('d M Y') : 'Tanpa batas')
            ->addColumn('action', function ($announcement) {
                $employee = $announcement->user->Employee->department->first()->department_name ?? 'Empty';
                $date = Carbon::parse($announcement->publish_date)->locale('en')->isoFormat('D MMMM YYYY');
                $enddate = $announcement->end_date
                    ? Carbon::parse($announcement->end_date)->locale('en')->isoFormat('D MMMM YYYY')
                    : 'Continuesly';

                return '<button type="button" class="pr-btn pr-btn-sm preview-btn"
                data-id="' . $announcement->id . '"
                data-title="' . e($announcement->title) . '"
                data-content="' . e($announcement->content) . '"
                data-date="' . $date . '"
                data-enddate="' . $enddate . '"
                data-employee="' . $employee . '">
                Lihat
            </button>';
            })

            ->rawColumns(['action', 'employee_name'])
            ->make(true);
    }
}
