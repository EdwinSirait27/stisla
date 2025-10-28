<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CareerController extends Controller
{
    public function index()
    {
        return view('pages.Career.Career');
    }
    public function indexabout()
    {
        return view('pages.About-us.About-us');
    }
}
