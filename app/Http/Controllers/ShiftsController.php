<?php

namespace App\Http\Controllers;

use App\Models\Shifts;
use App\Models\Stores;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ShiftsController extends Controller
{
    public function index()
    {
        $stores = Stores::select('id', 'name')
            ->whereNotNull('name')
            ->orderBy('name')
            ->get();

        return view('pages.Shifts.Shifts', compact('stores'));
    }

    public function getData(Request $request)
    {
        $storeId = $request->input('store_id');

        $query = Shifts::with('store:id,name')
            ->select('id', 'store_id', 'shift_name', 'start_time', 'end_time');

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        return DataTables::of($query)
            ->addColumn('store_name', fn($r) => $r->store->name ?? '-')
            ->addColumn('start_time', fn($r) => substr($r->start_time, 0, 5))
            ->addColumn('end_time', fn($r) => substr($r->end_time, 0, 5))
            ->addColumn('action', function ($r) {
                return '
                    <button class="btn btn-sm btn-warning btn-edit"
                        data-id="' . $r->id . '"
                        data-name="' . $r->shift_name . '"
                        data-start="' . substr($r->start_time, 0, 5) . '"
                        data-end="' . substr($r->end_time, 0, 5) . '"
                        data-store="' . $r->store_id . '">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger btn-delete" data-id="' . $r->id . '">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'store_id'   => 'required|exists:stores_tables,id',
            'shift_name' => 'required|string|max:100',
            'start_time' => 'required',
            'end_time'   => 'required',
        ]);

        Shifts::create([
            'store_id'   => $request->store_id,
            'shift_name' => $request->shift_name,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
            'is_holiday' => 0,
        ]);

        return response()->json(['success' => true, 'message' => 'Shift created successfully.']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'store_id'   => 'required|exists:stores_tables,id',
            'shift_name' => 'required|string|max:100',
            'start_time' => 'required',
            'end_time'   => 'required',
        ]);

        $shift = Shifts::findOrFail($id);
        $shift->update([
            'store_id'   => $request->store_id,
            'shift_name' => $request->shift_name,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
        ]);

        return response()->json(['success' => true, 'message' => 'Shift updated successfully.']);
    }

    public function destroy($id)
    {
        Shifts::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Shift deleted successfully.']);
    }
}