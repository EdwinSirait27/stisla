<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardManagerController extends Controller
{
    public function index()
    {
        return view('pages.dashboardManager.dashboardManager');
    }
}
