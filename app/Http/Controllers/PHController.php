<?php

namespace App\Http\Controllers;

use App\Imports\PHImport;
use App\Models\Ph;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Rules\NoXSSInput;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PHController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
    $user = auth()->user();

    if (!$user) {
        abort(403);
    }
     if ($user->hasanyPermission(['ManagePH','ManagePHSPVManager','ViewPH'])) {
     }
        return view('pages.Pubholi.Pubholi');
    }
    
    

public function getPubholidays(Request $request)
{
         /** @var \App\Models\User $user */
    $user = auth()->user();

    if (!$user) {
        abort(403);
    }
    if ($user->hasanyPermission(['ManagePH','ManagePHSPVManager','ViewPH'])) {
     }
    $query = Ph::select([
        'id',
        'type',
        'date',
        'remark'
    ]);
    // Filter tanggal awal dan akhir
    if ($request->filled('start_date') && $request->filled('end_date')) {
        $query->whereBetween('date', [
            $request->start_date,
            $request->end_date
        ]);
    }

    // Filter satu tanggal
    if ($request->filled('date')) {
        $query->whereDate('date', $request->date);
    }

    $pubholidays = $query->get();

    return DataTables::of($pubholidays)
        ->make(true);
}
    public function indexphs()
    {
               $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePH')) {
            abort(403, 'Unauthorized');
        }
        $files = Storage::disk('public')->files('templatephs');
        return view('pages.ImportPH.ImportPH', compact('files'));
    }
    public function downloadphs($filename)
    {
              $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePH')) {
            abort(403, 'Unauthorized');
        }
        $path = 'templatephs/' . $filename;

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->download($path);
        }
        abort(404);
    }
   
    public function importPhs(Request $request)
{
          $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManagePH')) {
            abort(403, 'Unauthorized');
        }
    $request->validate([
        'file' => 'required|mimes:xlsx,csv,xls'
    ], [
        'file.required' => 'Please select an Excel file to import.',
        'file.mimes'    => 'File format must be Excel (xlsx, xls) or CSV.',
    ]);

    $import = new PHImport();
    $import->import($request->file('file'));

    // Validation failures (format salah, kolom kosong, dll)
    if ($import->failures()->isNotEmpty()) {
        return back()->with('failures', $import->failures());
    }

    // Runtime errors (exception tak terduga)
    if ($import->errors()->isNotEmpty()) {
        return back()->with('import_errors', $import->errors());
    }

    return back()->with('success', 'Public Holidays imported successfully!');
}
}
