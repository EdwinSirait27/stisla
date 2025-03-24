<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;
use App\Rules\NoXSSInput;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;


class ActivityController extends Controller
{
    public function index()
    {
        return view('pages.Activity.Activity');
    }
    // public function getActivity()
    // {
    //     $activity = Activity::with('user')->select(['id', 'user_id'])->get()
    //         ->map(function ($activity) {
    //             $activity->id_hashed = substr(hash('sha256', $activity->id . env('APP_KEY')), 0, 8);
    //             $activity->user_name = $activity->user ? $activity->user->name : 'Unknown'; // Ambil nama dari relasi user

    //             $activity->action = '
    //             <a href="' . route('Activity.show', $activity->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="See Activity">
    //                 <i class="fas fa-user-edit text-secondary"></i>
    //             </a>';
    //             return $activity;
    //         });

    //     return DataTables::of($activity)
    //     ->addColumn('name', function ($activity) {
    //         return $activity->user_name; // Menampilkan name di tabel DataTables
    //     })
    //         ->rawColumns(['action'])
    //         ->make(true);
    // }
    public function getActivity()
{
    $activity = Activity::with('user')->select(['id', 'user_id'])->get()
        ->map(function ($activity) {
            $activity->id_hashed = substr(hash('sha256', $activity->id . env('APP_KEY')), 0, 8);
            $activity->user_name = $activity->user ? $activity->user->name : 'Unknown'; // Ambil nama dari relasi user

            $activity->action = '
            <a href="' . route('Activity.show', $activity->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="See Activity">
                <i class="fas fa-user-edit text-secondary"></i>
            </a>';
            return $activity;
        })
        ->unique('user_id'); // Menghapus duplikat berdasarkan user_id

    return DataTables::of($activity)
        ->addColumn('name', function ($activity) {
            return $activity->user_name; // Menampilkan name di tabel DataTables
        })
        ->rawColumns(['action'])
        ->make(true);
}
    public function getActivity1()
{
    $activity = Activity::with('user')->select(['activity_time','activity_type'])->get()
        ->map(function ($activity) {
            return $activity;
        }); // Menghapus duplikat berdasarkan user_id
    return DataTables::of($activity)
        ->make(true);
}
    

    

    public function show($hashedId)
    {
        $activity = Activity::with('user')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$activity) {
            abort(404, 'User not found.');
        }
        return view('pages.Activity.show', compact('activity', 'hashedId'));
    }

}
