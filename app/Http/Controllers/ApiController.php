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

}
