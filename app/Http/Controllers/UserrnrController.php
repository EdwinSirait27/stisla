<?php

namespace App\Http\Controllers;

use App\Services\TeamDashboardData;

class UserrnrController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        $user->load([
            'employee.position.responsibilities',
            'employee.position.qualifications',
            'employee.department',
            'employee.store',
            'employee.company',
            'employee.grading',
            'employee.atasanList' => fn($q) => $q->whereIn('employees_tables.status', TeamDashboardData::ACTIVE_STATUSES)
                ->with(['position' => fn($p) => $p->wherePivot('is_primary', true)]),
            'employee.bawahanList' => fn($q) => $q->whereIn('employees_tables.status', TeamDashboardData::ACTIVE_STATUSES)
                ->orderBy('employee_name')
                ->with(['position' => fn($p) => $p->wherePivot('is_primary', true)]),
        ]);

        $employee = $user->employee;
        // Jabatan utama dulu, lalu sisanya
        $positions = $employee?->position->sortByDesc(fn($p) => (int) $p->pivot->is_primary)->values() ?? collect();

        return view('pages.rnr.index', compact('user', 'employee', 'positions'));
    }
}
