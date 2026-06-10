<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\User;

use Illuminate\Support\Facades\Storage;

class ApiController extends Controller
{
    public function getManagerByEmployee($employeeId)
{
    $employee = Employee::with([
        'structuresnew.parent' 
    ])->find($employeeId);

    if (!$employee || !$employee->structuresnew) {
        return response()->json(['manager' => null], 404);
    }

    $current = $employee->structuresnew->parent;
    $managerStructure = null;

    while ($current) {
        if ($current->is_manager) {
            $managerStructure = $current;
            break;
        }
        $current->load('parent');
        $current = $current->parent;
    }
    if (!$managerStructure) {
        return response()->json(['manager' => null], 404);
    }
    $managerEmployee = Employee::with([
        'structuresnew.submissionposition.positionRelation'
    ])->where('structure_id', $managerStructure->id)->first();
    if (!$managerEmployee) {
        return response()->json(['manager' => null], 404);
    }
    return response()->json([
        'manager' => [
            'id'            => $managerEmployee->id,
            'employee_name' => $managerEmployee->employee_name,
            'company_email' => $managerEmployee->company_email,
            'position'      => optional(
                                   optional($managerEmployee->structuresnew?->submissionposition)
                                   ->positionRelation
                               )->name ?? null,
            'signature' => $managerEmployee->signature
                                   ? url('storage/' . $managerEmployee->signature)
                                   : null,
        ]
    ]);
}
public function getPositionByEmployee($employeeId)
{
    $employee = Employee::with([
        'structuresnew.parent' // load satu level parent dulu
    ])->find($employeeId);

    if (!$employee || !$employee->structuresnew) {
        return response()->json(['manager' => null], 404);
    }

    // Naik ke atas sampai ketemu is_manager = true
    $current = $employee->structuresnew->parent;
    $managerStructure = null;

    while ($current) {
        if ($current->is_manager) {
            $managerStructure = $current;
            break;
        }
        // Load parent berikutnya secara eksplisit
        $current->load('parent');
        $current = $current->parent;
    }

    if (!$managerStructure) {
        return response()->json(['manager' => null], 404);
    }

    $managerEmployee = Employee::with([
        'structuresnew.submissionposition.positionRelation'
    ])->where('structure_id', $managerStructure->id)->first();

    if (!$managerEmployee) {
        return response()->json(['manager' => null], 404);
    }
    return response()->json([
    'manager' => [
        'id'            => $managerEmployee->id,
        'employee_name' => $managerEmployee->employee_name,
        'position'      => optional(
                               optional($managerEmployee->structuresnew?->submissionposition)
                               ->positionRelation
                           )->name ?? null,
        'signature'     => $managerEmployee->signature, // ✅ langsung value DB: "signatures/69c4a4aea50c3.png"
    ]
]);
}
public function show($id)
{
    $financestaff = User::with(['employee.position', 'roles'])->findOrFail($id);

    return response()->json([
        'id' => $financestaff->id,
        'employee_name' => $financestaff->employee->employee_name,
        'position' => $financestaff->employee->position->name,
        'roles' => $financestaff->user->roles
                        ->where('name', 'finance')
                        ->pluck('name')
                        ->first(),
        'signature' => $financestaff->employee->signature
                               ? url('storage/' . $financestaff->employee->signature)
                               : null,
    ]);
}
//  public function showsignature($id)
//     {
//         $employee = Employee::findOrFail($id);

//         // cek signature kosong
//         if (empty($employee->signature)) {
//             abort(404, 'Signature tidak ditemukan');
//         }

//         $path = $employee->signature;

//         // cek file ada
//         if (!Storage::disk('public')->exists($path)) {
//             abort(404, 'File signature tidak ditemukan');
//         }

//         // ambil file
//         $file = Storage::disk('public')->get($path);

//         // mime type
//         $mime = Storage::disk('public')->mimeType($path);

//         return response($file, 200)
//             ->header('Content-Type', $mime)
//             ->header('Cache-Control', 'public, max-age=86400');
//     }
public function showsignature($id)
{
    $employee = Employee::findOrFail($id);
    if (empty($employee->signature)) {
        abort(404, 'Signature tidak ditemukan');
    }
    $path = $employee->signature;
    if (!Storage::disk('public')->exists($path)) {
        abort(404, 'File signature tidak ditemukan');
    }
    $file = Storage::disk('public')->get($path);
    $mime = Storage::disk('public')->mimeType($path);

    return response($file, 200)
        ->header('Content-Type', $mime)
        ->header('Cache-Control', 'public, max-age=86400')
        ->header('Access-Control-Allow-Origin', '*')        // izinkan semua domain
        ->header('Access-Control-Allow-Methods', 'GET');    // hanya GET
}

}
