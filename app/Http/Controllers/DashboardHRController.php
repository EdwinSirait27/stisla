<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Fingerprints;
use App\Models\Announcment;
use Illuminate\Http\Request;
use Carbon\Carbon;
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
//     public function getMonthlyData(Request $request)
// {
//     $month = $request->get('month', now()->format('Y-m'));
//     $monthDate = Carbon::createFromFormat('Y-m', $month);

//     // total karyawan aktif / pending
//     $totalEmployees = Employee::whereIn('status', ['Active', 'Pending'])->count();

//     // hitung scan per hari (distinct pin per tanggal)
//     $data = Fingerprints::selectRaw('DAY(scan_date) as day, COUNT(DISTINCT pin) as total')
//         ->whereMonth('scan_date', $monthDate->month)
//         ->whereYear('scan_date', $monthDate->year)
//         ->groupBy('day')
//         ->orderBy('day')
//         ->get();

//     // ambil array hari (1–31) dan total hadir
//     $days = $data->pluck('day');
//     $counts = $data->pluck('total');

//     return response()->json([
//         'days'           => $days,        // 1,2,3,... (tanggal dalam bulan)
//         'counts'         => $counts,      // jumlah hadir per hari
//         'totalEmployees' => $totalEmployees,
//     ]);
// }

    public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'publish_date' => 'nullable|date',
    ]);

    Announcment::create($request->all());

    return redirect()->route('pages.dashboardHR')
        ->with('success', 'Announcement successfully made.');
}
}
