<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UserrnrController extends Controller
{
     public function index()
    {
    $user = Auth::user()->load([
    'employee.structuresnew',
    ]);
        return view('pages.rnr.index', compact('user'));
    }
}