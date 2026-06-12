<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UserrnrController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        $user->load([
            'employee',
        ]);
        return view('pages.rnr.index', compact('user'));
    }
}
