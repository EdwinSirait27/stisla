<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Payrollcomponents;

class PayrollcomponentsController extends Controller
{
    public function getPayrollcomponents(Request $request)
    {
        $query = Payrollcomponents::select('id',  'component_name', 'type', 'is_fixed');
        return DataTables::eloquent($query)
            ->addColumn('action', function ($payroll) {
                $id =  $payroll->id;
                $editBtn = '
                <a href="' . route('editpayrollcomponents', $id) . '"
                   class="inline-flex items-center justify-center p-2 
                          text-slate-500 hover:text-indigo-600 
                          hover:bg-indigo-50 rounded-full transition"
                   title="Edit Member: ' . $payroll->component_name . '">
                    <i class="fas fa-user-edit text-secondary"></i>

                   
                </a>
            ';
                return $editBtn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function update(Request $request, $id)
    {
        $payroll = Payrollcomponents::findOrFail($id);

        // ✅ validasi
        $validated = $request->validate([
            'component_name'   => 'required|string|max:255|unique:payroll_components,component_name' . $payroll->id,
            'type'        => 'required|in:Income,Deduction',
            'is_fixed'       => 'nullable|boolean',
        ]);
        // ✅ update
        $payroll->update($validated);

        return redirect()
            ->route('payrollcomponents')
            ->with('success', 'Payroll Components Updated Successfully');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'component_name'   => 'required|string|max:255|unique:payroll_components,component_name',
            'type'        => 'required|in:Income,Deduction',
            'is_fixed'       => 'nullable|boolean',
        ]);
        $payrolls = Payrollcomponents::create($validated);
        return redirect()
            ->route('payrollcomponents')
            ->with('success', 'Payroll Components Created Successfully');
    }
    public function edit($id)
    {
        $payrolls = Payrollcomponents::findOrFail($id);
        $types = Payrollcomponents::getTypeOptions();
        return view('pages.payrollcomponents.editpayrollcomponents', compact('payrolls', 'types'));
    }
    public function index()
    {
        return view('pages.payrollcomponents.payrollcomponents');
    }
    public function create()
    {
        $types = Payrollcomponents::getTypeOptions();
        return view('pages.payrollcomponents.createpayrollcomponents', compact('types'));
    }
}
