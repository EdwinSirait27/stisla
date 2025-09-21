<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Fingerprints;
use App\Models\Announcment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;

use Illuminate\Support\Facades\DB;

class DashboardHRController extends Controller
{
   
    // public function index(Request $request)
    // {
    //     $month = $request->get('month', Carbon::now()->format('Y-m'));
    //     $monthDate = Carbon::createFromFormat('Y-m', $month);

    //     // ambil jumlah scan per hari
    //     $data = Fingerprints::select(
    //         DB::raw('DAY(scan_date) as day'),
    //         DB::raw('COUNT(*) as total')
    //     )
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
    //         'month' => $month,
    //         'days' => $days,
    //         'totals' => $totals,
    //         'totalEmployees' => $totalEmployees, 
    //         'announcements' => $announcements, 
    //     ]);
    // }
    public function index(Request $request)
{
    $month = $request->get('month', Carbon::now()->format('Y-m'));
    $monthDate = Carbon::createFromFormat('Y-m', $month);

    // ambil jumlah scan per hari (distinct pin per tanggal)
    $data = Fingerprints::select(
            DB::raw('DAY(scan_date) as day'),
            DB::raw('COUNT(DISTINCT pin) as total')
        )
        ->whereMonth('scan_date', $monthDate->month)
        ->whereYear('scan_date', $monthDate->year)
        ->groupBy('day')
        ->orderBy('day')
        ->get();

    $days = $data->pluck('day');
    $totals = $data->pluck('total');
    $totalEmployees = Employee::whereIn('status', ['Active', 'Pending'])->count();
    $announcements = Announcment::orderBy('created_at', 'desc')->get();

    return view('pages.dashboardHR.dashboardHR', [
        'month'          => $month,
        'days'           => $days,
        'totals'         => $totals,
        'totalEmployees' => $totalEmployees,
        'announcements'  => $announcements,
    ]);
}

    // public function getMonthlyData(Request $request)
    // {
    //     $month = $request->get('month', now()->format('Y-m'));
    //     $monthDate = Carbon::createFromFormat('Y-m', $month);

    //     // total karyawan aktif / pending
    //     $totalEmployees = Employee::whereIn('status', ['Active', 'Pending'])->count();

    //     // hitung scan per hari (group by DAYNAME)
    //     $data = Fingerprints::selectRaw('DAYNAME(scan_date) as day_name, COUNT(DISTINCT pin) as total')
    //         ->whereMonth('scan_date', $monthDate->month)
    //         ->whereYear('scan_date', $monthDate->year)
    //         ->groupBy('day_name')
    //         ->get();

    //     // urutkan manual sesuai Senin-Sabtu
    //     $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    //     $days = [];
    //     $counts = []; // langsung jumlah hadir

    //     foreach ($weekDays as $day) {
    //         $found = $data->firstWhere('day_name', $day);
    //         $present = $found ? $found->total : 0;

    //         $days[]   = $day;
    //         $counts[] = $present; // bukan persen lagi
    //     }

    //     return response()->json([
    //         'days'   => $days,    // Monday ... Saturday
    //         'counts' => $counts,  // jumlah hadir
    //         'totalEmployees' => $totalEmployees, // opsional kalau mau ditampilkan
    //     ]);
    // }
    public function getMonthlyData(Request $request)
{
    $month = $request->get('month', now()->format('Y-m'));
    $monthDate = Carbon::createFromFormat('Y-m', $month);

    // total karyawan aktif / pending
    $totalEmployees = Employee::whereIn('status', ['Active', 'Pending'])->count();

    // hitung scan per hari (distinct pin per tanggal)
    $data = Fingerprints::selectRaw('DAY(scan_date) as day, COUNT(DISTINCT pin) as total')
        ->whereMonth('scan_date', $monthDate->month)
        ->whereYear('scan_date', $monthDate->year)
        ->groupBy('day')
        ->orderBy('day')
        ->get();

    // ambil array hari (1–31) dan total hadir
    $days = $data->pluck('day');
    $counts = $data->pluck('total');

    return response()->json([
        'days'           => $days,        // 1,2,3,... (tanggal dalam bulan)
        'counts'         => $counts,      // jumlah hadir per hari
        'totalEmployees' => $totalEmployees,
    ]);
}


    public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
      
        'publish_date' => 'required|date',
    ]);

     Announcment::create([
        'title'        => $request->title,
        'content'      => $request->content,
        'publish_date' => $request->publish_date,
        'user_id'      => auth()->id(), 
    ]);

    return redirect()->route('pages.dashboardHR')
        ->with('success', 'Announcement successfully made.');
}
public function getAnnouncements()
{
    $announcements = Announcment::with('user.Employee.department')
        ->select(['id', 'user_id', 'title', 'content', 'publish_date'])
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
    return '<button class="btn btn-sm btn-primary preview-btn"
                data-id="'.$announcement->id.'"
                data-title="'.e($announcement->title).'"
                data-content="'.e($announcement->content).'"
                data-date="' . $date . '"
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
