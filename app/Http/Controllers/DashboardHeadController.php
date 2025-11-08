<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Submissionposition;
use App\Models\Stores;
use App\Models\Position;
use App\Rules\NoXSSInput;
use Illuminate\Support\Facades\DB;

class DashboardHeadController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.dashboardDirector.dashboardDirector');
    }
  
}
