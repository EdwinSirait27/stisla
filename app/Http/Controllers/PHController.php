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
        return view('pages.Pubholi.Pubholi');
    }
    public function getPubholidays()
    {
        $pubholidays = Ph::select(['id', 'type', 'date', 'remark'])
            ->get()
            ->map(function ($pubholiday) {
                return $pubholiday;
            });

        return DataTables::of($pubholidays)
            ->make(true);
    }
    public function indexphs()
    {
        $files = Storage::disk('public')->files('templatephs');
        return view('pages.ImportPH.ImportPH', compact('files'));
    }
    public function downloadphs($filename)
    {
        $path = 'templatephs/' . $filename;

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->download($path);
        }
        abort(404);
    }
   
    public function importPhs(Request $request)
{
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
