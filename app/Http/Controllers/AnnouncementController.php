<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        return view('pages.Announcement.Announcement');
    }
}
