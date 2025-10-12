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
   public function index(Request $request)
{
    // Ambil bulan dari input, default ke bulan sekarang
    $month = $request->get('month', now()->format('Y-m'));
    $monthDate = Carbon::createFromFormat('Y-m', $month);

    $startDate = $monthDate->copy()->startOfMonth();
    $endDate   = $monthDate->copy()->endOfMonth();
        $types = ['Annual Leave', 'Overtime'];


    // Hitung jumlah karyawan aktif/pending
    $totalEmployees = Employee::whereIn('status', ['Active', 'Pending'])->count();

    // Ambil data fingerprint per hari dalam bulan terpilih
    $data = Fingerprints::selectRaw('DAY(scan_date) as day, COUNT(DISTINCT pin) as total')
        ->whereBetween('scan_date', [$startDate, $endDate])
        ->groupBy('day')
        ->orderBy('day')
        ->get();

    // Hitung presentase kehadiran
    $days = $data->pluck('day');
    $percentages = $data->pluck('total')->map(function ($value) use ($totalEmployees) {
        return $totalEmployees > 0 ? round(($value / $totalEmployees) * 100, 2) : 0;
    });

    $announcements = Announcment::orderBy('created_at', 'desc')->get();
     $user = Auth::user();
$employee = $user->employee;

if ($employee->is_manager) {
    // Manager → lihat semua submission di departemen yang sama
    $submissions = Submissions::with(['employee', 'approver'])
        ->whereHas('employee', function ($q) use ($employee) {
            $q->where('department_id', $employee->department_id);
        })
        ->latest()
        ->take(8)
        ->get();
} else {
    // Non-manager → lihat submission sendiri
    $submissions = Submissions::with(['employee', 'approver'])
        ->where('employee_id', $employee->id)
        ->latest()
        ->take(8)
        ->get();
}
// ✅ Hitung duration (hari) secara otomatis jika belum tersimpan dalam bentuk angka
foreach ($submissions as $submission) {
    if (!is_numeric($submission->duration)) {
        $from = Carbon::parse($submission->leave_date_from);
        $to = Carbon::parse($submission->leave_date_to);
        $submission->duration = $from->diffInDays($to) + 1; // termasuk tanggal mulai
    }
}

    return view('pages.dashboardHR.dashboardHR', [
        'month'          => $month,
        'days'           => $days,
        'types'           => $types,
        'percentages'    => $percentages,
        'pendingSubmissions'         => $submissions,
        'totalEmployees' => $totalEmployees,
        'announcements'  => $announcements,
    ]);
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

// public function getMonthlyData(Request $request)
// {
//     // Ambil tanggal mulai dan tanggal akhir dari request
//     $startDate = $request->get('start_date');
//     $endDate = $request->get('end_date');

//     // Jika user belum pilih periode, pakai default bulan ini
//     if (!$startDate || !$endDate) {
//         $monthDate = now();
//         $startDate = $monthDate->copy()->startOfMonth()->toDateString();
//         $endDate   = $monthDate->copy()->endOfMonth()->toDateString();
//     }

//     // Hitung total karyawan aktif
//     $totalEmployees = Employee::whereIn('status', ['Active', 'Pending'])->count();

//     // Ambil data fingerprint berdasarkan periode
//     $data = Fingerprints::selectRaw('DATE(scan_date) as scan_day, COUNT(DISTINCT pin) as total')
//         ->whereBetween('scan_date', [$startDate, $endDate])
//         ->groupBy('scan_day')
//         ->orderBy('scan_day')
//         ->get();

//     // Hitung persentase kehadiran
//     $result = $data->map(function ($item) use ($totalEmployees) {
//         $percentage = $totalEmployees > 0
//             ? round(($item->total / $totalEmployees) * 100, 2)
//             : 0;

//         return [
//             'date'       => $item->scan_day,
//             'percentage' => $percentage,
//         ];
//     });

//     return response()->json([
//         'period'         => [
//             'start' => $startDate,
//             'end'   => $endDate,
//         ],
//         'totalEmployees' => $totalEmployees,
//         'data'           => $result,
//     ]);
// }


// public function getMonthlyData(Request $request)
// {
//     // Ambil bulan dari request (Y-m)
//     $month = $request->get('month', now()->format('Y-m'));
//     $monthDate = Carbon::createFromFormat('Y-m', $month);

//     $startDate = $monthDate->copy()->startOfMonth();
//     $endDate   = $monthDate->copy()->endOfMonth();

//     $totalEmployees = Employee::whereIn('status', ['Active', 'Pending'])->count();

//     $data = Fingerprints::selectRaw('DATE(scan_date) as scan_day, COUNT(DISTINCT pin) as total')
//         ->whereBetween('scan_date', [$startDate, $endDate])
//         ->groupBy('scan_day')
//         ->orderBy('scan_day')
//         ->get();

//     $result = $data->map(function ($item) use ($totalEmployees) {
//         $percentage = $totalEmployees > 0
//             ? round(($item->total / $totalEmployees) * 100, 2)
//             : 0;

//         return [
//             'date'       => $item->scan_day,
//             'percentage' => $percentage,
//         ];
//     });

//     return response()->json([
//         'period'         => [
//             'start' => $startDate->toDateString(),
//             'end'   => $endDate->toDateString(),
//         ],
//         'totalEmployees' => $totalEmployees,
//         'data'           => $result,
//     ]);
// }

  
//     public function index(Request $request)
// {
//     $month = $request->get('month', Carbon::now()->format('Y-m'));
//     $monthDate = Carbon::createFromFormat('Y-m', $month);

//     $data = Fingerprints::select(
//             DB::raw('DAY(scan_date) as day'),
//             DB::raw('COUNT(DISTINCT pin) as total')
//         )
//         ->whereMonth('scan_date', $monthDate->month)
//         ->whereYear('scan_date', $monthDate->year)
//         ->groupBy('day')
//         ->orderBy('day')
//         ->get();

//     $days = $data->pluck('day');
//     $totals = $data->pluck('total');
//     $totalEmployees = Employee::whereIn('status', ['Active', 'Pending'])->count();
//     $announcements = Announcment::orderBy('created_at', 'desc')->get();

    

   

//     return view('pages.dashboardHR.dashboardHR', [
//         'month'          => $month,
//         'days'           => $days,
//         'totals'         => $totals,
//         'totalEmployees' => $totalEmployees,
//         'announcements'  => $announcements,
//     ]);
// }
// public function getMonthlyData(Request $request)
// {
//     // Ambil periode dari request (format: Y-m-d)
//     $startDate = Carbon::parse($request->get('start_date', now()->startOfMonth()));
//     $endDate   = Carbon::parse($request->get('end_date', now()->endOfMonth()));

//     // Total karyawan aktif/pending
//     $totalEmployees = Employee::whereIn('status', ['Active', 'Pending'])->count();

//     // Hitung scan per hari berdasarkan range tanggal
//     $data = Fingerprints::selectRaw('DATE(scan_date) as scan_day, COUNT(DISTINCT pin) as total')
//         ->whereBetween('scan_date', [$startDate, $endDate])
//         ->groupBy('scan_day')
//         ->orderBy('scan_day')
//         ->get();

//     // Hitung presentase per hari
//     $result = $data->map(function ($item) use ($totalEmployees) {
//         $percentage = $totalEmployees > 0
//             ? round(($item->total / $totalEmployees) * 100, 2)
//             : 0;

//         return [
//             'date'       => $item->scan_day,
//             'percentage' => $percentage,
//         ];
//     });

//     return response()->json([
//         'period'         => [
//             'start' => $startDate->toDateString(),
//             'end'   => $endDate->toDateString(),
//         ],
//         'totalEmployees' => $totalEmployees,
//         'data'           => $result,
//     ]);
// }
//  $user = Auth::user();
// $employee = $user->employee;

// if ($employee->is_manager) {
//     // Manager → lihat semua submission di departemen yang sama
//     $submissions = Submissions::with(['employee', 'approver'])
//         ->whereHas('employee', function ($q) use ($employee) {
//             $q->where('department_id', $employee->department_id);
//         })
//         ->latest()
//         ->get();
// } else {
//     // Non-manager → lihat submission sendiri
//     $submissions = Submissions::with(['employee', 'approver'])
//         ->where('employee_id', $employee->id)
//         ->latest()
//         ->get();
// }
//         'pendingSubmissions'         => $submissions,


// // ✅ Hitung duration (hari) secara otomatis jika belum tersimpan dalam bentuk angka
// foreach ($submissions as $submission) {
//     if (!is_numeric($submission->duration)) {
//         $from = Carbon::parse($submission->leave_date_from);
//         $to = Carbon::parse($submission->leave_date_to);
//         $submission->duration = $from->diffInDays($to) + 1; // termasuk tanggal mulai
//     }
// }



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
        ->select(['id', 'user_id', 'title', 'content', 'publish_date','end_date'])
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
        // ->addColumn('action', function ($announcement) {
        //     return '<button class="btn btn-sm btn-primary preview-btn"
        //                 data-id="'.$announcement->id.'"
        //                 data-title="'.$announcement->title.'"
        //                 data-content="'.e($announcement->content).'"
        //                 data-date="'.$announcement->publish_date.'"
        //                 data-employee="'.$announcement->user->Employee->employee_name ?? 'Empty'.'">
        //                 Preview
        //             </button>';
        // })
        ->addColumn('action', function ($announcement) {
    $employee = $announcement->user->Employee->department->department_name ?? 'Empty';
$date = Carbon::parse($announcement->publish_date)->locale('en')->isoFormat('D MMMM YYYY');
// $enddate = Carbon::parse($announcement->end_date)->locale('en')->isoFormat('D MMMM YYYY');
// $enddate = Carbon::parse($announcement->end_date)?->locale('en')->isoFormat('D MMMM YYYY') ?? 'Continuesly';
$enddate = $announcement->end_date
    ? Carbon::parse($announcement->end_date)->locale('en')->isoFormat('D MMMM YYYY')
    : 'Continuesly';

    return '<button class="btn btn-sm btn-primary preview-btn"
                data-id="'.$announcement->id.'"
                data-title="'.e($announcement->title).'"
                data-content="'.e($announcement->content).'"
                data-date="' . $date . '"
                data-enddate="' . $enddate . '"
                data-employee="'.$employee.'">
                Preview
            </button>';
})

        ->rawColumns(['action', 'employee_name'])
        ->make(true);
}

// public function getAnnouncements()
//     {
//         $announcements = Announcment::with('user.Employee')->select(['id', 'user_id','title','content','publish_date'])
//             ->get()
//             ->map(function ($announcement) {
//                 $announcement->id_hashed = substr(hash('sha256', $announcement->id . env('APP_KEY')), 0, 8);
//                 $announcement->action = '<input type="checkbox" class="announcement-checkbox" name="announcement_ids[]" value="' . $announcement->id_hashed . '">';
//                 return $announcement;
//             });
//         return DataTables::of($announcements)
//         ->addColumn('employee_name', function ($announcement) {
//             return !empty($announcement->user->Employee) && !empty($announcement->user->Employee->employee_name)
//                 ? $announcement->user->Employee->employee_name
//                 : 'Empty';
//         })
//             ->rawColumns(['action','employee_name'])
//             ->make(true);
//     }
}


