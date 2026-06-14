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
    $employee = Employee::with('grading')->find($employeeId);

    if (!$employee) {
        return response()->json(['manager' => null], 404);
    }

    $atasan = $employee->atasan();

    if (!$atasan) {
        return response()->json(['manager' => null], 404);
    }

    return response()->json([
        'manager' => [
            'id'            => $atasan->id,
            'employee_name' => $atasan->employee_name,
            'company_email' => $atasan->company_email,
            'position'      => $atasan->primaryPosition()->first()?->name ?? null,
            'signature'     => $atasan->signature
                                ? url('storage/' . $atasan->signature)
                                : null,
        ]
    ]);
}
public function getPositionByEmployee($employeeId)
{
    $employee = Employee::with('grading')->find($employeeId);

    if (!$employee) {
        return response()->json(['manager' => null], 404);
    }

    $atasan = $employee->atasan();

    if (!$atasan) {
        return response()->json(['manager' => null], 404);
    }

    return response()->json([
        'manager' => [
            'id'            => $atasan->id,
            'employee_name' => $atasan->employee_name,
            'position'      => $atasan->primaryPosition()->first()?->name ?? null,
            'signature'     => $atasan->signature,
        ]
    ]);
}

// public function showsignature($id)
// {
//     $employee = Employee::findOrFail($id);
//     if (empty($employee->signature)) {
//         abort(404, 'Signature tidak ditemukan');
//     }
//     $path = $employee->signature;
//     if (!Storage::disk('public')->exists($path)) {
//         abort(404, 'File signature tidak ditemukan');
//     }
//     $file = Storage::disk('public')->get($path);
//     $mime = Storage::disk('public')->mimeType($path);

//     return response($file, 200)
//         ->header('Content-Type', $mime)
//         ->header('Cache-Control', 'public, max-age=86400')
//         ->header('Access-Control-Allow-Origin', '*')        // izinkan semua domain
//         ->header('Access-Control-Allow-Methods', 'GET');    // hanya GET
// }

}
