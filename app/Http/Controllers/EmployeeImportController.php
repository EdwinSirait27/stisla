<?php

namespace App\Http\Controllers;

use App\Imports\PayrollsImport;
use Illuminate\Http\Request;
use App\Imports\EmployeeImport;
use App\Imports\UserImport;
use Maatwebsite\Excel\Facades\Excel;
class EmployeeImportController extends Controller
{
    public function index() 
    {
        return view('pages.Import.Import');
    }
    public function indexuser() 
    {
        return view('pages.Importuser.Importuser');
    }
    public function indexpayrolls() 
    {
        return view('pages.Importpayroll.Importpayroll');
    }
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);

        Excel::import(new EmployeeImport, $request->file('file'));

        return back()->with('success', 'Import berhasil!');
    }
    public function importuser(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);

        Excel::import(new UserImport, $request->file('file'));

        return back()->with('success', 'Import berhasil!');
    }

//     public function importpayroll(Request $request)
// {
//     $request->validate([
//         'file' => 'required|mimes:xlsx,csv,xls|max:10240', // Menambahkan batas ukuran file 10MB
//     ]);
//     try {
//         Excel::import(new PayrollsImport, $request->file('file'));

//         return back()->with('success', 'Import berhasil!');
//     } catch (\Exception $e) {
//         return back()->with('errorr', 'Terjadi kesalahan saat mengimpor file: ' . $e->getMessage());
//     }
// }
public function importpayroll(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,csv,xls|max:10240',
    ]);

    try {
        $errors = [];
        Excel::import(new PayrollsImport($errors), $request->file('file'));

        if (count($errors) > 0) {
            return back()->withErrors($errors);
        }
        return back()->with('success', 'Import berhasil!');
    } catch (\Exception $e) {
        return back()->with('errorr', 'Terjadi kesalahan saat mengimpor file: ' . $e->getMessage());
    }
}
}
