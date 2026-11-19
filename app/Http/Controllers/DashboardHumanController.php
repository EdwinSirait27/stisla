<?php

namespace App\Http\Controllers;

use App\Services\EmployeeDashboardData;
use Illuminate\Http\Request;

class DashboardHumanController extends Controller
{
    public function index(Request $request, EmployeeDashboardData $dashboard)
    {
        $viewData = $dashboard->build($request);
        $viewData['calendarUrl'] = url('/dashboardHuman');

        if ($request->ajax()) {
            return view('pages.dashboardHuman._calendar', $viewData);
        }

        return view('pages.dashboardHuman.dashboardHuman', $viewData);
    }
}
