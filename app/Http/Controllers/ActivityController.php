<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Activity;
use Yajra\DataTables\DataTables;
class ActivityController extends Controller
{
    public function index()
    {
        return view('pages.Activity.Activity');
    }
    public function getActivity()
    {
        $activity = Activity::with('user.employee')->select(['id', 'user_id'])->get()
            ->map(function ($activity) {
                $activity->id_hashed = substr(hash('sha256', $activity->id . env('APP_KEY')), 0, 8);
                $activity->user_name = $activity->user ? $activity->user->name : 'Unknown';
                $activity->action = '
            <a href="' . route('Activity.show', $activity->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="See Activity">
                <i class="fas fa-user-edit text-secondary"></i>
            </a>';
                return $activity;
            })
            ->unique('user_id'); 
        return DataTables::of($activity)
            ->addColumn('employee_name', function ($activity) {
                return $activity->user->employee->employee_name;
            })
            ->addColumn('username', function ($activity) {
                return $activity->user->username;
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    public function getActivity1(Request $request)
    {
        $query = Activity::with('user')
            ->select(['activity_time', 'activity_type', 'device_wifi_mac', 'device_lan_mac'])
            ->orderBy('activity_time', 'desc');

        if ($request->has('activity_type') && in_array($request->activity_type, ['Login', 'Logout'])) {
            $query->where('activity_type', $request->activity_type);
        }
        $activity = $query->get()
            ->map(function ($activity) {
                return $activity;
            });
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
