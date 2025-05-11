<?php

namespace App\Http\Controllers;

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
}
