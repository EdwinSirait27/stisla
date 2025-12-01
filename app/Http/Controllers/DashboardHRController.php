<?php

namespace App\Http\Controllers;

use App\Mail\AnnouncementMail;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use App\Models\Fingerprints;
use App\Models\Submissions;
use App\Models\Announcement;
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
    public function index(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $monthDate = Carbon::createFromFormat('Y-m', $month);
        $startDate = $monthDate->copy()->startOfMonth();
        $endDate   = $monthDate->copy()->endOfMonth();
        $types = ['Annual Leave', 'Overtime', 'Cash Advances'];
        $statussubmissions = ['Cash', 'TOIL'];
        $totalEmployees = Employee::whereIn('status', ['Active', 'Pending', 'Mutation'])->count();
        $totalEmployeespending = Employee::whereIn('status', ['Pending'])->count();
        $totalEmployeesinactive = Employee::whereIn('status', ['Inactive', 'Resign'])
            ->where('end_date', '>=', now()->subWeek())
            ->count();
        // 🔹 Data kehadiran (fingerprint)
        $data = Fingerprints::selectRaw('DAY(scan_date) as day, COUNT(DISTINCT pin) as total')
            ->whereBetween('scan_date', [$startDate, $endDate])
            ->groupBy('day')
            ->orderBy('day')
            ->get();
        $days = $data->pluck('day');
        $percentages = $data->pluck('total')->map(function ($value) use ($totalEmployees) {
            return $totalEmployees > 0 ? round(($value / $totalEmployees) * 100, 2) : 0;
        });
        $announcements = Announcement::orderBy('created_at', 'desc')->get();
        // 🔹 Ambil user & employee
        $user = Auth::user();
        $employee = $user->employee ?? null;
        // Ambil type yang dipilih (bisa dari query string atau form)
        $selectedType = $request->get('type', null);
        // Default nilai
        $submissions = collect();
        $managedEmployees = $employee ? collect([$employee]) : collect();
        $canCreateOvertime = $employee && ($employee->is_manager == 1 || $employee->is_manager_store == 1);
        if ($employee && ($employee->is_manager == 1 || $employee->is_manager_store == 1)) {
            // 🔹 Ambil semua employee di department atau store yang sama
            $query = Employee::query();

            if ($employee->is_manager == 1) {
                $query->where('department_id', $employee->department_id);
            }

            if ($employee->is_manager_store == 1) {
                // gunakan orWhere supaya store manager bisa lihat berdasarkan store
                $query->orWhere('store_id', $employee->store_id);
            }

            $managedIds = $query->pluck('id')->toArray();
            $managedIds[] = $employee->id; // pastikan dirinya sendiri ikut

            $submissions = Submissions::with(['employee', 'approver'])
                ->whereIn('employee_id', $managedIds)
                ->latest()
                ->take(8)
                ->get();

            // 🔹 Ambil semua bawahannya
            $subordinates = Employee::whereIn('id', $managedIds)
                ->where('id', '!=', $employee->id)
                ->get();

            $managedEmployees = collect([$employee])
                ->merge($subordinates)
                ->unique('id')
                ->values();
        } else if ($employee) {
            // 🔹 Non-manager hanya bisa lihat submissions miliknya
            $submissions = Submissions::with(['employee', 'approver'])
                ->where('employee_id', $employee->id)
                ->latest()
                ->take(8)
                ->get();
        }
        // 🔹 Hitung durasi (Annual Leave dsb)
        foreach ($submissions as $submission) {
            if (!is_numeric($submission->duration)) {
                $from = Carbon::parse($submission->leave_date_from);
                $to   = Carbon::parse($submission->leave_date_to);
                $submission->duration = $from->diffInDays($to) + 1;
            }

            if (!isset($submission->formattedDuration)) {
                $submission->formattedDuration = $submission->duration . ' day(s)';
            }
        }
        $leaveData = null;
        if ($selectedType === 'Annual Leave' && $employee) {
            $total = $employee->total ?? $employee->total ?? $employee->total ?? 12;
            $pending = $employee->pending ?? $employee->pending ?? $employee->pending ?? 0;
            $remaining = $employee->remaining ?? $employee->remaining ?? $employee->remaining ?? 0;

            $leaveData = [
                'total' => $total ?? 12,
                'pending' => $pending ?? 0,
                'remaining' => $remaining ?? 0,
            ];
        }
        $today = now()->format('Y-m-d');
        $employeePins = Employee::pluck('pin')->toArray();

        $presentToday = Fingerprints::whereDate('scan_date', $today)
            ->whereIn('inoutmode', [1])
            ->whereIn('pin', $employeePins)
            ->distinct('pin')  // 1 orang dihitung 1 kali
            ->count('pin');
        $yesterday = now()->subDay()->toDateString();

        $presentYesterday = Fingerprints::whereDate('scan_date', $yesterday)
            ->whereIn('inoutmode', [1])
            ->whereIn('pin', $employeePins)
            ->distinct('pin')
            ->count('pin');

        $trend = $presentToday - $presentYesterday;



        return view('pages.dashboardHR.dashboardHR', [
            'month'              => $month,
            'days'               => $days,
            'types'              => $types,
            'statussubmissions'  => $statussubmissions,
            'percentages'        => $percentages,
            'totalEmployeesinactive'        => $totalEmployeesinactive,
            'pendingSubmissions' => $submissions,
            'totalEmployees'     => $totalEmployees,
            'trend'     => $trend,
            'announcements'      => $announcements,
            'presentToday'      => $presentToday,
            'presentYesterday'      => $presentYesterday,
            'canCreateOvertime'  => $canCreateOvertime,
            'totalEmployeespending'  => $totalEmployeespending,
            'managedEmployees'   => $managedEmployees,
            'selectedType'       => $selectedType,
            'leaveData'          => $leaveData,
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
                ->whereIn('status', ['Active', 'Pending', 'Mutation'])
                ->get();

            // Kumpulkan jobs
            $jobs = [];
            foreach ($employees as $employee) {
                $jobs[] = (new SendAnnouncementEmail($announcement, $employee))
                    ->onQueue('emailannouncement');
            }

            // Dispatch batch
            Bus::batch($jobs)
                ->name("Send Announcement {$announcement->id}")
                ->onQueue('emailannouncement')
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

    // public function index(Request $request)
    // {
    //     $month = $request->get('month', now()->format('Y-m'));
    //     $monthDate = Carbon::createFromFormat('Y-m', $month);

    //     $startDate = $monthDate->copy()->startOfMonth();
    //     $endDate   = $monthDate->copy()->endOfMonth();
    //     $types = ['Annual Leave', 'Overtime'];
    //     $statussubmissions = ['Cash', 'TOIL'];


    //     // Hitung jumlah karyawan aktif/pending
    //     $totalEmployees = Employee::whereIn('status', ['Active', 'Pending'])->count();

    //     // Ambil data fingerprint per hari dalam bulan terpilih
    //     $data = Fingerprints::selectRaw('DAY(scan_date) as day, COUNT(DISTINCT pin) as total')
    //         ->whereBetween('scan_date', [$startDate, $endDate])
    //         ->groupBy('day')
    //         ->orderBy('day')
    //         ->get();

    //     // Hitung presentase kehadiran
    //     $days = $data->pluck('day');
    //     $percentages = $data->pluck('total')->map(function ($value) use ($totalEmployees) {
    //         return $totalEmployees > 0 ? round(($value / $totalEmployees) * 100, 2) : 0;
    //     });

    //     $announcements = Announcment::orderBy('created_at', 'desc')->get();
    //     $user = Auth::user();
    //     $employee = $user->employee;

    //     if ($employee->is_manager) {
    //         // Manager → lihat semua submission di departemen yang sama
    //         $submissions = Submissions::with(['employee', 'approver'])
    //             ->whereHas('employee', function ($q) use ($employee) {
    //                 $q->where('department_id', $employee->department_id);
    //             })
    //             ->latest()
    //             ->take(8)
    //             ->get();
    //     } else {
    //         // Non-manager → lihat submission sendiri
    //         $submissions = Submissions::with(['employee', 'approver'])
    //             ->where('employee_id', $employee->id)
    //             ->latest()
    //             ->take(8)
    //             ->get();
    //     }
    //     // ✅ Hitung duration (hari) secara otomatis jika belum tersimpan dalam bentuk angka
    //     foreach ($submissions as $submission) {
    //         if (!is_numeric($submission->duration)) {
    //             $from = Carbon::parse($submission->leave_date_from);
    //             $to = Carbon::parse($submission->leave_date_to);
    //             $submission->duration = $from->diffInDays($to) + 1; // termasuk tanggal mulai
    //         }
    //     }
    //     return view('pages.dashboardHR.dashboardHR', [
    //         'month'          => $month,
    //         'days'            => $days,
    //         'types'          => $types,
    //         'statussubmissions' => $statussubmissions,
    //         'percentages'    => $percentages,
    //         'pendingSubmissions'         => $submissions,
    //         'totalEmployees' => $totalEmployees,
    //         'announcements'  => $announcements,
    //     ]);
    // }
//    public function index(Request $request)
// {
//     $month = $request->get('month', now()->format('Y-m'));
//     $monthDate = Carbon::createFromFormat('Y-m', $month);
//     $startDate = $monthDate->copy()->startOfMonth();
//     $endDate   = $monthDate->copy()->endOfMonth();

//     $types = ['Annual Leave', 'Overtime'];
//     $statussubmissions = ['Cash', 'TOIL'];

//     $totalEmployees = Employee::whereIn('status', ['Active', 'Pending'])->count();

//     // 🔹 Data kehadiran (fingerprint)
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

//     // 🔹 Ambil user & employee
//     $user = Auth::user();
//     $employee = $user->employee;

//     // Default nilai
//     $submissions = collect();
//     $managedEmployees = collect([$employee]);
//     $canCreateOvertime = $employee && $employee->is_manager == 1;

//     if ($employee && $employee->is_manager) {
//         // 🔹 Ambil semua employee di departemen yang sama
//         $departmentEmployeeIds = Employee::where('department_id', $employee->department_id)
//             ->pluck('id')
//             ->toArray();

//         // 🔹 Ambil submissions departemen itu
//         $submissions = Submissions::with(['employee', 'approver'])
//             ->whereIn('employee_id', $departmentEmployeeIds)
//             ->latest()
//             ->take(8)
//             ->get();

//         // 🔹 Ambil semua bawahan di departemen yang sama
//         $subordinates = Employee::where('department_id', $employee->department_id)
//             ->where('id', '!=', $employee->id)
//             ->get();

//         // 🔹 Gabungkan manager + bawahannya
//         $managedEmployees = collect([$employee])
//             ->merge($subordinates)
//             ->unique('id')
//             ->values();
//     } else {
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
//     ]);
// }
// public function index(Request $request)
// {
//     $month = $request->get('month', now()->format('Y-m'));
//     $monthDate = Carbon::createFromFormat('Y-m', $month);
//     $startDate = $monthDate->copy()->startOfMonth();
//     $endDate   = $monthDate->copy()->endOfMonth();

//     $types = ['Annual Leave', 'Overtime'];
//     $statussubmissions = ['Cash', 'TOIL'];

//     $totalEmployees = Employee::whereIn('status', ['Active', 'Pending'])->count();

//     // 🔹 Data kehadiran (fingerprint)
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

//     // 🔹 Ambil user & employee
//     $user = Auth::user();
//     $employee = $user->employee;

//     // Default nilai
//     $submissions = collect();
//     $managedEmployees = collect([$employee]);
//     $canCreateOvertime = $employee && ($employee->is_manager == 1 || $employee->is_manager_store == 1);

//     if ($employee && ($employee->is_manager == 1 || $employee->is_manager_store == 1)) {

//         // 🔹 Ambil semua employee di department atau store yang sama
//         $query = Employee::query();

//         if ($employee->is_manager == 1) {
//             $query->where('department_id', $employee->department_id);
//         }

//         if ($employee->is_manager_store == 1) {
//             $query->orWhere('store_id', $employee->store_id);
//         }

//         $managedIds = $query->pluck('id')->toArray();

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

//     } else {
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
//     ]);
// }
// public function store(Request $request)
// {
//         ini_set('max_execution_time', 360);
//     try {
//         $request->validate([
//             'title' => 'required|string|max:255',
//             'content' => 'required|string',
//             'publish_date' => 'required|date',
//             'end_date' => 'nullable|date',
//         ]);

//         // Simpan announcement
//         $announcement = Announcement::create([
//             'title'        => $request->title,
//             'content'      => $request->content,
//             'publish_date' => $request->publish_date,
//             'end_date'     => $request->end_date,
//             'user_id'      => auth()->id(),
//         ]);
//         Log::info('Announcement created successfully', [
//             'announcement_id' => $announcement->id,
//             'title'           => $announcement->title,
//             'created_by'      => auth()->id(),
//         ]);
//         // Ambil hanya employee yang allowed: Active, Pending, Mutation
//         $employees = Employee::whereNotNull('email')
//             ->whereIn('status', ['Active', 'Pending', 'Mutation'])
//             ->get();

//         // Buat batch jobs
//         $jobs = [];
//         foreach ($employees as $employee) {
//             $jobs[] = new SendAnnouncementEmail($announcement, $employee);
//         }
//         // Bus::batch($jobs)->dispatch();
//         Bus::batch($jobs)
//     ->name('Send Announcement: ' . $announcement->id)
//     ->dispatch();


//         Log::info('Email jobs dispatched successfully', [
//             'announcement_id'   => $announcement->id,
//             'total_recipients'  => $employees->count(),
//         ]);

//         return redirect()->route('pages.dashboardHR')
//             ->with('success', "Announcement created successfully. Emails queued for {$employees->count()} employees.");

//     } catch (\Exception $e) {

//         Log::error('Failed to create announcement', [
//             'error'   => $e->getMessage(),
//             'trace'   => $e->getTraceAsString(),
//             'user_id' => auth()->id(),
//         ]);
//         return redirect()->back()
//             ->withInput()
//             ->with('error', 'Failed to create announcement. Please try again.');
//     }
// }
// public function store(Request $request)
// {
//     ini_set('max_execution_time', 360);
//     try {
//         $request->validate([
//             'title' => 'required|string|max:255',
//             'content' => 'required|string',
//             'publish_date' => 'required|date',
//             'end_date' => 'nullable|date',
//         ]);

//         // Simpan announcement
//         $announcement = Announcement::create([
//             'title'        => $request->title,
//             'content'      => $request->content,
//             'publish_date' => $request->publish_date,
//             'end_date'     => $request->end_date,
//             'user_id'      => auth()->id(),
//         ]);

//         Log::info('Announcement created successfully', [
//             'announcement_id' => $announcement->id,
//             'title'           => $announcement->title,
//             'created_by'      => auth()->id(),
//         ]);

//         // Ambil employee dengan status tertentu
//         $employees = Employee::whereNotNull('email')
//             ->whereIn('status', ['Active', 'Pending', 'Mutation'])
//             ->get();

//         // Buat batch jobs
//         $jobs = [];
//         foreach ($employees as $employee) {
//             $jobs[] = (new SendAnnouncementEmail($announcement, $employee))
//                         ->onQueue('emailannouncement');
//         }
//         Bus::batch($jobs)
//             ->name('Send Announcement: ' . $announcement->id)
//             ->onQueue('emailannouncement')
//             ->dispatch();

//         Log::info('Email jobs dispatched successfully', [
//             'announcement_id'   => $announcement->id,
//             'total_recipients'  => $employees->count(),
//         ]);

//         return redirect()->route('pages.dashboardHR')
//             ->with('success', "Announcement created successfully. Emails queued for {$employees->count()} employees.");

//     } catch (\Exception $e) {

//         Log::error('Failed to create announcement', [
//             'error'   => $e->getMessage(),
//             'trace'   => $e->getTraceAsString(),
//             'user_id' => auth()->id(),
//         ]);

//         return redirect()->back()
//             ->withInput()
//             ->with('error', 'Failed to create announcement. Please try again.');
//     }
// }
// public function store(Request $request)
// {
//     try {
//         // Validasi
//         $request->validate([
//             'title' => 'required|string|max:255',
//             'content' => 'required|string',
//             'publish_date' => 'required|date',
//             'end_date' => 'nullable|date',
//         ]);

//         // Simpan announcement
//         $announcement = Announcement::create([
//             'title'        => $request->title,
//             'content'      => $request->content,
//             'publish_date' => $request->publish_date,
//             'end_date'     => $request->end_date,
//             'user_id'      => auth()->id(),
//         ]);

//         Log::info('Announcement created', [
//             'announcement_id' => $announcement->id,
//             'total_recipients' => 0,
//         ]);

      
//         $employees = Employee::whereNotNull('email')
//             ->whereIn('status', ['Active', 'Pending', 'Mutation'])
//             ->get();

//         // Pastikan queue bekerja optimal
//         $jobs = $employees->map(function ($employee) use ($announcement) {
//             return (new SendAnnouncementEmail($announcement, $employee))
//                 ->onQueue('emailannouncement');
//         });

//         // Gunakan chunking untuk menghindari batch terlalu besar
//         Bus::batch($jobs->toArray())
//             ->name("Send Announcement {$announcement->id}")
//             ->onQueue('emailannouncement')
//             ->allowFailures()
//             ->dispatch();

//         Log::info('Email batch dispatched', [
//             'announcement_id' => $announcement->id,
//             'total_recipients' => $employees->count(),
//         ]);

//         return redirect()->route('pages.dashboardHR')
//             ->with('success', "Announcement dibuat & email antrian untuk {$employees->count()} karyawan.");

//     } catch (\Throwable $e) {

//         Log::error('Announcement creation failed', [
//             'error' => $e->getMessage(),
//             'user_id' => auth()->id(),
//         ]);

//         return back()->withInput()->with('error', 'Failed to create announcement.');
//     }
// }