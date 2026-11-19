<?php

namespace App\Http\Controllers;

use App\Services\EmployeeDashboardData;
use App\Services\TeamDashboardData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardSupervisorController extends Controller
{
    public function index(Request $request, EmployeeDashboardData $dashboard, TeamDashboardData $teams)
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        if (!$user->hasPermissionTo('dashboardSupervisor')) {
            abort(403);
        }

        // Bagian pribadi: sama persis dengan dashboard karyawan
        $viewData = $dashboard->build($request);
        $viewData['calendarUrl'] = url('/dashboardSupervisor');

        if ($request->ajax()) {
            return view('pages.dashboardHuman._calendar', $viewData);
        }

        $team = $teams->subordinates(Auth::user()->employee);
        $today = $teams->today($team);
        $pending = $teams->pendingLeaves($team->pluck('id')->all());

        return view('pages.dashboardSupervisor.dashboardSupervisor', array_merge($viewData, [
            'teamToday'     => $today['rows'],
            'teamStats'     => $today['stats'],
            'pendingLeaves' => $pending['items'],
            'pendingCount'  => $pending['count'],
        ]));
    }
}
