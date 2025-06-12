<?php

namespace App\Http\Controllers;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\User;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use App\Models\Terms;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use App\Rules\NoXSSInput;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DashboardHRController extends Controller
{
    public function index()
    {
        $totalEmployees = Employee::whereIn('status', ['Active', 'Pending'])->count();

        
        return view('pages.dashboardHR.dashboardHR', compact('totalEmployees'));
    }
   














}
