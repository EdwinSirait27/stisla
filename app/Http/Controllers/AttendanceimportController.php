<?php

namespace App\Http\Controllers;

use App\Imports\AttendaceImport;
use Illuminate\Support\Facades\Storage;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
class AttendanceimportController extends Controller
{
 public function indexattendances()
    {
        $files = Storage::disk('public')->files('templateattendance');
        return view('pages.Importattendance.Importattendance', compact('files'));
    }   
     public function downloadattendance($filename)
    {
        $path = 'templateattendance/' . $filename;

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->download($path);
        }
        abort(404);
    }
   
    public function importattendance(Request $request)
{

    $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv'
    ]);

   $import = new AttendaceImport();
Excel::import($import, $request->file('file'));

if (!empty($import->failures())) {
    return back()->with([
        'success' => 'Data berhasil diimpor sebagian.',
        'failures' => $import->failures()
    ]);
} else {
    return back()->with('success', 'Semua data berhasil diimpor.');
}

}
}
