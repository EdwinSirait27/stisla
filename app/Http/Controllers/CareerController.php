<?php

namespace App\Http\Controllers;

use App\Models\Structuresnew;
use Illuminate\Support\Facades\Auth;
use App\Models\Fingerprints;
use App\Models\Submissions;
use App\Models\Announcment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
class CareerController extends Controller
{
    public function index()
    {
    $types = Structuresnew::select('type')->distinct()->pluck('type');
        return view('pages.Career.Career');
    }
    public function indexabout()
    {
        return view('pages.About-us.About-us');
    }
}
