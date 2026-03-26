<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\User;


class ApiController extends Controller
{
    public function getManagerByEmployee($employeeId)
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
            'signature_url' => $managerEmployee->signature
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
            'signature_url' => $managerEmployee->signature
                                   ? url('storage/' . $managerEmployee->signature)
                                   : null,
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
        'signature_url' => $financestaff->employee->signature
                               ? url('storage/' . $financestaff->employee->signature)
                               : null,
    ]);
}
}
