<?php
namespace App\Http\Controllers;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use App\Models\Fingerprints;
use App\Models\Submissions;
use App\Models\Announcment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
class DashboardHRController extends Controller
{
// public function index(Request $request)
// {
//     $month = $request->get('month', now()->format('Y-m'));
//     $monthDate = Carbon::createFromFormat('Y-m', $month);
//     $startDate = $monthDate->copy()->startOfMonth();
//     $endDate   = $monthDate->copy()->endOfMonth();
//     $types = ['Annual Leave', 'Overtime','Cash Advances'];
//     $statussubmissions = ['Cash', 'TOIL'];
//     $totalEmployees = Employee::whereIn('status', ['Active', 'Pending'])->count();
//     $data = Fingerprints::selectRaw('DAY(scan_date) as day, COUNT(DISTINCT pin) as total')
//         ->whereBetween('scan_date', [$startDate, $endDate])
//         ->groupBy('day')
//         ->orderBy('day')
//         ->get();
//     $days = $data->pluck('day');
//     $percentages = $data->pluck('total')->map(function ($value) use ($totalEmployees) {
//         return $totalEmployees > 0 ? round(($value / $totalEmployees) * 100, 2) : 0;
//     });

//     $announcements = Announcment::orderBy('created_at', 'desc')->get();
//     $user = Auth::user();
//     $employee = $user->employee ?? null;
//     $selectedType = $request->get('type', null);
//     $submissions = collect();
//     $managedEmployees = $employee ? collect([$employee]) : collect();
//     $canCreateOvertime = $employee && ($employee->is_manager == 1 || $employee->is_manager_store == 1);
//     if ($employee && ($employee->is_manager == 1 || $employee->is_manager_store == 1)) {
// $query = Employee::query();

//         if ($employee->is_manager == 1) {
//             $query->where('department_id', $employee->department_id);
//         }

//         if ($employee->is_manager_store == 1) {
//             $query->orWhere('store_id', $employee->store_id);
//         }

//         $managedIds = $query->pluck('id')->toArray();
//         $managedIds[] = $employee->id; // pastikan dirinya sendiri ikut

//         $submissions = Submissions::with(['employee', 'approver'])
//             ->whereIn('employee_id', $managedIds)
//             ->latest()
//             ->take(8)
//             ->get();

//         // 🔹 Ambil semua bawahannya
//         $subordinates = Employee::whereIn('id', $managedIds)
//             ->where('id', '!=', $employee->id)
//             ->get();

//         $managedEmployees = collect([$employee])
//             ->merge($subordinates)
//             ->unique('id')
//             ->values();

//     } else if ($employee) {
//         // 🔹 Non-manager hanya bisa lihat submissions miliknya
//         $submissions = Submissions::with(['employee', 'approver'])
//             ->where('employee_id', $employee->id)
//             ->latest()
//             ->take(8)
//             ->get();
//     }

//     // 🔹 Hitung durasi (Annual Leave dsb)
//     foreach ($submissions as $submission) {
//         if (!is_numeric($submission->duration)) {
//             $from = Carbon::parse($submission->leave_date_from);
//             $to   = Carbon::parse($submission->leave_date_to);
//             $submission->duration = $from->diffInDays($to) + 1;
//         }

//         if (!isset($submission->formattedDuration)) {
//             $submission->formattedDuration = $submission->duration . ' day(s)';
//         }
//     }

//     $leaveData = null;
//     if ($selectedType === 'Annual Leave' && $employee) {
//         $total = $employee->total ?? $employee->total ?? $employee->total ?? 12;
//         $pending = $employee->pending ?? $employee->pending ?? $employee->pending ?? 0;
//         $remaining = $employee->remaining ?? $employee->remaining ?? $employee->remaining ?? 0;

//         $leaveData = [
//             'total' => $total ?? 12,
//             'pending' => $pending ?? 0,
//             'remaining' => $remaining ?? 0,
//         ];
//     }
//     return view('pages.dashboardHR.dashboardHR', [
//         'month'              => $month,
//         'days'               => $days,
//         'types'              => $types,
//         'statussubmissions'  => $statussubmissions,
//         'percentages'        => $percentages,
//         'pendingSubmissions' => $submissions,
//         'totalEmployees'     => $totalEmployees,
//         'announcements'      => $announcements,
//         'canCreateOvertime'  => $canCreateOvertime,
//         'managedEmployees'   => $managedEmployees,
//         'selectedType'       => $selectedType,
//         'leaveData'          => $leaveData,
//     ]);
// }
public function index(Request $request)
{
    $month = $request->get('month', now()->format('Y-m'));
    $monthDate = Carbon::createFromFormat('Y-m', $month);
    $startDate = $monthDate->startOfMonth();
    $endDate   = $monthDate->endOfMonth();

    $types = ['Annual Leave', 'Overtime','Cash Advances'];
    $statussubmissions = ['Cash', 'TOIL'];
    $totalEmployees = Employee::whereIn('status', ['Active', 'Pending'])->count();

    $data = Fingerprints::selectRaw('DAY(scan_date) as day, COUNT(DISTINCT pin) as total')
        ->whereBetween('scan_date', [$startDate, $endDate])
        ->groupBy('day')
        ->orderBy('day')
        ->get();

    $days = $data->pluck('day');
    $percentages = $data->pluck('total')->map(fn ($value) =>
        $totalEmployees > 0 ? round(($value / $totalEmployees) * 100, 2) : 0
    );

    $announcements = Announcment::orderBy('created_at', 'desc')->get();

    $user = Auth::user();
    $employee = $user->employee ?? null;

    $selectedType = $request->get('type', null);
    $submissions = collect();
    $managedEmployees = collect();
    $canCreateOvertime = false;

    // Jika ada employee login
    if ($employee) {
        $canCreateOvertime = ($employee->structuresnew->is_manager == 1);

        // Jika manager atau manager store
        if ($canCreateOvertime) {

            $query = Employee::query();
            $query->where(function($q) use ($employee) {

                if ($employee->structuresnew->is_manager == 1) {
                    $q->where('department_id', $employee->structuresnew->submissionposition->department_id);
                }

                if ($employee->is_manager_store == 1) {
                    $q->orWhere('store_id', $employee->store_id);
                }

            });

            $managedIds = $query->pluck('id')->toArray();
            $managedIds[] = $employee->id;

            $submissions = Submissions::with(['employee', 'approver'])
                ->whereIn('employee_id', $managedIds)
                ->latest()
                ->take(8)
                ->get();

            $subordinates = Employee::whereIn('id', $managedIds)
                ->where('id', '!=', $employee->id)
                ->get();

            $managedEmployees = collect([$employee])
                ->merge($subordinates)
                ->unique('id')
                ->values();

        } else {
            // Non manager: hanya lihat submission sendiri
            $submissions = Submissions::with(['employee', 'approver'])
                ->where('employee_id', $employee->id)
                ->latest()
                ->take(8)
                ->get();
        }
    }

    // Hitung durasi
    foreach ($submissions as $submission) {
        if (!is_numeric($submission->duration)) {
            $from = Carbon::parse($submission->leave_date_from);
            $to   = Carbon::parse($submission->leave_date_to);
            $submission->duration = $from->diffInDays($to) + 1;
        }

        $submission->formattedDuration = "{$submission->duration} day(s)";
    }

    // Annual leave details
    $leaveData = null;
    if ($selectedType === 'Annual Leave' && $employee) {
        $leaveData = [
            'total'     => $employee->total ?? 12,
            'pending'   => $employee->pending ?? 0,
            'remaining' => $employee->remaining ?? 0,
        ];
    }

    return view('pages.dashboardHR.dashboardHR', compact(
        'month', 'days', 'types', 'statussubmissions', 'percentages',
        'submissions', 'totalEmployees', 'announcements', 'canCreateOvertime',
        'managedEmployees', 'selectedType', 'leaveData'
    ));
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
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'publish_date' => 'required|date',
            'end_date' => 'nullable|date',
        ]);

        Announcment::create([
            'title'        => $request->title,
            'content'      => $request->content,
            'publish_date' => $request->publish_date,
            'end_date' => $request->end_date,
            'user_id'      => auth()->id(),
        ]);

        return redirect()->route('pages.dashboardHR')
            ->with('success', 'Announcement successfully made.');
    }
    public function getAnnouncements()
    {
        $announcements = Announcment::with('user.Employee.department')
            ->select(['id', 'user_id', 'title', 'content', 'publish_date', 'end_date'])
            ->get()
            ->map(function ($announcement) {
                $announcement->id_hashed = substr(hash('sha256', $announcement->id . env('APP_KEY')), 0, 8);
                return $announcement;
            });

        return DataTables::of($announcements)
            ->addColumn('employee_name', function ($announcement) {
                return !empty($announcement->user->Employee->department) && !empty($announcement->user->Employee->department->department_name)
                    ? $announcement->user->Employee->department->department_name
                    : 'Empty';
            })
            ->addColumn('action', function ($announcement) {
                $employee = $announcement->user->Employee->department->department_name ?? 'Empty';
                $date = Carbon::parse($announcement->publish_date)->locale('en')->isoFormat('D MMMM YYYY');
                // $enddate = Carbon::parse($announcement->end_date)->locale('en')->isoFormat('D MMMM YYYY');
                // $enddate = Carbon::parse($announcement->end_date)?->locale('en')->isoFormat('D MMMM YYYY') ?? 'Continuesly';
                $enddate = $announcement->end_date
                    ? Carbon::parse($announcement->end_date)->locale('en')->isoFormat('D MMMM YYYY')
                    : 'Continuesly';

                return '<button class="btn btn-sm btn-primary preview-btn"
                data-id="' . $announcement->id . '"
                data-title="' . e($announcement->title) . '"
                data-content="' . e($announcement->content) . '"
                data-date="' . $date . '"
                data-enddate="' . $enddate . '"
                data-employee="' . $employee . '">
                Preview
            </button>';
            })

            ->rawColumns(['action', 'employee_name'])
            ->make(true);
    }
}
