<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Leavebalance;
use App\Models\Leaverequest;
use App\Models\Announcment;
use Carbon\Carbon;

use Illuminate\Support\Facades\Auth;
class DashboardHumanController extends Controller
{
    //   public function index()
    // {
    //         $user = auth()->user();

    //     $leaveBalances = Leavebalance::with('leaves')
    //         ->where('employee_id', Auth::user()->employee_id)
    //         ->where('year', date('Y'))
    //         ->where('balance_days', '>', 0)
    //         ->get();

    //          $announcements = Announcment::with('user')
    //         ->where(function($query) {
    //             $query->whereNull('end_date')
    //                   ->orWhere('end_date', '>=', Carbon::today());
    //         })
    //         ->orderBy('created_at', 'desc')
    //         ->take(5)
    //         ->get();
    //         $submissions = collect();

    // if ($user->employee_id) {
    //     $leaveBalanceIds = Leavebalance::where('employee_id', $user->employee_id)
    //         ->pluck('id');

    //     $submissions = Leaverequest::with([
    //             'leavebalance.leaves',
    //             'approver',
    //         ])
    //         ->whereIn('leave_balance_id', $leaveBalanceIds)
    //         ->orderBy('created_at', 'desc')
    //         ->take(5)
    //         ->get();
    // }
           
    //     return view('pages.dashboardHuman.dashboardHuman', compact('leaveBalances','announcements','submissions'));
    // }
    public function index()
{
    $user = auth()->user();

    // Announcements
    $announcements = Announcment::with('user')
        ->where(function ($q) {
            $q->whereNull('end_date')
              ->orWhere('end_date', '>=', Carbon::today());
        })
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();

    // My Submissions
    $submissions = collect();

    if ($user->employee_id) {
        $leaveBalanceIds = Leavebalance::where('employee_id', $user->employee_id)
            ->pluck('id');

        $submissions = Leaverequest::with([
                'leavebalance.leaves',
                'approver',
            ])
            ->whereIn('leave_balance_id', $leaveBalanceIds)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($submission) {
                $leaveType = optional($submission->leavebalance)->leaves;
                $typeName  = optional($leaveType)->name ?? 'Leave';
                $typeSlug  = strtolower(str_replace(' ', '-', $typeName));

                $start    = Carbon::parse($submission->start_date);
                $end      = Carbon::parse($submission->end_date);
                $duration = $start->diffInDays($end) + 1;

                $typeIcons = [
                    'annual leave' => 'fa-umbrella-beach',
                    'sick leave'   => 'fa-hospital',
                    'overtime'     => 'fa-clock',
                    'maternity'    => 'fa-baby',
                    'paternity'    => 'fa-baby-carriage',
                ];

                $statusConfig = [
                    'Pending'  => ['class' => 'Pending',  'icon' => 'fa-clock',       'label' => 'Pending Review'],
                    'Approved' => ['class' => 'Approved', 'icon' => 'fa-check-circle', 'label' => 'Approved'],
                    'Rejected' => ['class' => 'Rejected', 'icon' => 'fa-times-circle', 'label' => 'Rejected'],
                    'Cancelled' => ['class' => 'Cancelled', 'icon' => 'fa-times-circle', 'label' => 'Cancelled'],
                ];

                $statusKey = $submission->status ?? 'Pending';

                return (object) [
                    'id'            => $submission->id,
                    'type_name'     => $typeName,
                    'type_slug'     => $typeSlug,
                    'type_icon'     => $typeIcons[strtolower($typeName)] ?? 'fa-file-alt',
                    'status_class'  => $statusConfig[$statusKey]['class'],
                    'status_icon'   => $statusConfig[$statusKey]['icon'],
                    'status_label'  => $statusConfig[$statusKey]['label'],
                    'status'        => $statusKey,
                    'start'         => $start->format('M d, Y'),
                    'end'           => $end->format('M d, Y'),
                    'is_same_day'   => $start->isSameDay($end),
                    'duration'      => $duration,
                    'duration_label'=> $duration > 1 ? "{$duration} days" : "{$duration} day",
                    'posted_ago'    => $submission->created_at->diffForHumans(),
                    'note'          => $submission->employee_reason ?? '-',
                    'reject_reason' => $submission->approver_reason ?? null,
                    'approver_name' => optional($submission->approver)->name,
                ];
            });
    }

    return view('pages.dashboardHuman.dashboardHuman', compact('announcements', 'submissions'));
}
}
