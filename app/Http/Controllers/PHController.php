<?php

namespace App\Http\Controllers;

use App\Models\Ph;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Rules\NoXSSInput;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;


class PHController extends Controller
{
     public function index()
    {
        return view('pages.Pubholi.Pubholi');
    }
   public function getPubholidays()
{
    $pubholidays = Ph::select(['id', 'type', 'date','remark'])
        ->get()
        ->map(function ($pubholiday) {
            $pubholiday->id_hashed = substr(hash('sha256', $pubholiday->id . env('APP_KEY')), 0, 8);

            // Checkbox untuk bulk delete
            $pubholiday->checkbox = '
                <input type="checkbox" class="pubholiday-checkbox" value="' . $pubholiday->id . '">
            ';

            // Action button (edit)
            $pubholiday->action = '
                <a href="' . route('Pubholi.edit', $pubholiday->id_hashed) . '" 
                   class="mx-3" 
                   data-bs-toggle="tooltip" 
                   data-bs-original-title="Edit user"
                   title="Edit pubholiday: ' . e($pubholiday->remark) . '">
                    <i class="fas fa-user-edit text-secondary"></i>
                </a>';
            return $pubholiday;
        });

    return DataTables::of($pubholidays)
        ->rawColumns(['checkbox','action'])
        ->make(true);
}

    public function edit($hashedId)
    {
        $pubholi = Ph::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$pubholi) {
            abort(404, 'pubholi not found.');
        }
  $status_type = ['Hindu', 'Non Hindu', 'All'];
         return view('pages.Pubholi.edit', [
            'pubholi' => $pubholi,
            'hashedId' => $hashedId,
            'status_type' => $status_type,
        ]);
    }
    public function create()
    {
        $status_type = ['Hindu', 'Non Hindu', 'All'];
        
        return view('pages.Pubholi.create', compact('status_type'));
    }
    public function store(Request $request)
    {
        // dd($request->all());
        $validatedData = $request->validate([
            // 'type' => [
            //     'required',
            //     'string',
            //     'max:255',
            //     'unique:departments_tables,department_name',
            //     new NoXSSInput()
            // ],
             'remark' => ['required', 'string', 'max:255', 'unique:ph,remark', new NoXSSInput()],
            'type' => ['required', 'string', 'max:255', new NoXSSInput()],
            'date' => ['required', 'date_format:Y-m-d', new NoXSSInput()],

            

        ]);
        try {
            DB::beginTransaction();
            $pubholi = Ph::create([
                'remark' => $validatedData['remark'],
                'type' => $validatedData['type'],
                'date' => $validatedData['date'],
            ]);
            DB::commit();
            return redirect()->route('pages.Pubholi')->with('success', 'Public Holiday created Succesfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
                ->withInput();
        }
    }
    public function update(Request $request, $hashedId)
    {
        $pubholi = Ph::get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$pubholi) {
            return redirect()->route('pages.Pubholi')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([
            'remark' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ph')->ignore($pubholi->id),
                new NoXSSInput()
            ],
            'type' => ['required', 'string', 'max:255', new NoXSSInput()],
            'date' => ['required', 'date_format:Y-m-d', new NoXSSInput()],

       
        ]);
        $pubholiData = [
            'remark' => $validatedData['remark'],
            'type' => $validatedData['type'],
            'date' => $validatedData['date'],
        ];
        DB::beginTransaction();
        $pubholi->update($pubholiData);
        DB::commit();
        return redirect()->route('pages.Pubholi')->with('success', 'Public Holiday Updated Successfully.');
    }
}
