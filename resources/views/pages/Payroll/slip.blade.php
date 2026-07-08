<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip MJM</title>
    <link rel="icon" type="image/png"
        href="{{ asset('img/1710675344-17-03-2024-iSZQk9yVubtJh31N46lxpnC7av5osrLW.ico') }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
            color: #333;
            font-size: 12px;
        }

        .payslip-container {
            border: 1px solid #ddd;
            padding: 15px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .confidential {
            color: #FF0000;
            font-weight: bold;
        }

        .tables-container {
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }

        .table-cell-amount {
            text-align: right;
            font-family: monospace;
        }

        .totals-row {
            display: flex;
            gap: 24px;
            margin-bottom: 1rem;
        }

        .total-section {
            flex: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            font-weight: bold;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .table-cell-amount {
            font-weight: bold;
            white-space: nowrap;
        }

        .take-home {
            display: flex;
            justify-content: space-between;
            padding: 12px;
            background-color: #f5f5f5;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }

        .transfer-section {
            border-top: 1px solid #ddd;
            padding-top: 12px;
        }

        .transfer-title {
            margin-bottom: 8px;
            font-weight: bold;
        }

        .transfer-details {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }

        .transfer-account {
            color: #555;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 80px;
            color: rgba(200, 200, 200, 0.15);
            white-space: nowrap;
            pointer-events: none;
            z-index: 0;
        }
    </style>
</head>

<body>
    <div class="watermark">CONFIDENTIAL</div>

    @php
        $statusEmp = strtoupper($payroll->employee->status_employee ?? '');
        $isDW = $statusEmp === 'DW';

        // Ambil amount dari PayrollDetail (BPJS, MEAL/HOUSE/TRANSPORT allowance)
        $mealAllowance = $payroll->details->firstWhere('component.component_name', 'MEAL ALLOWANCE')?->amount ?? 0;
        $houseAllowance = $payroll->details->firstWhere('component.component_name', 'HOUSE ALLOWANCE')?->amount ?? 0;
        $transportAllowance =
            $payroll->details->firstWhere('component.component_name', 'TRANSPORT ALLOWANCE')?->amount ?? 0;
        $bpjsKesehatan = $payroll->details->firstWhere('component.component_name', 'BPJS KESEHATAN')?->amount ?? 0;
        $bpjsKetenagakerjaan =
            $payroll->details->firstWhere('component.component_name', 'BPJS KETENAGAKERJAAN')?->amount ?? 0;

        $basicOrDaily = $isDW
            ? $payroll->daily_rate * $payroll->attendance_days
            : ($payroll->working_days > 0
                ? floor(
                    (($payroll->basic_salary + $payroll->position_allowance) / $payroll->working_days) *
                        $payroll->attendance_days,
                )
                : 0);
        $basic = $payroll->basic_salary;
        $positional = $payroll->position_allowance;
    @endphp

    <div class="payslip-container" id="payslip-content" style="font-family: Arial, sans-serif; font-size: 14px;">

        <!-- Header with Logo and Confidential -->
        <div class="header"
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <table style="width: 100%; margin-bottom: 20px;">
                <tr>
                    <td style="text-align: left;">
                        @php
                            $imageData = null;
                            $fotoPath = $payroll->employee->company->foto ?? null;

                            if ($fotoPath) {
                                $fullPath = storage_path('app/public/' . $fotoPath);

                                if (is_file($fullPath)) {
                                    $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                                    $imageData =
                                        'data:image/' .
                                        $type .
                                        ';base64,' .
                                        base64_encode(file_get_contents($fullPath));
                                }
                            }
                        @endphp

                        @if ($imageData)
                            <div style="background-color: #000000; display: inline-block; padding: 5px;">
                                <img src="{{ $imageData }}" alt="Foto Perusahaan" width="70">
                            </div>
                        @else
                            <span style="color: #888;">Foto tidak tersedia</span>
                        @endif
                    </td>
                    <td style="text-align: right; color: red; font-weight: bold; font-size: 18px;">
                        *CONFIDENTIAL
                    </td>
                </tr>
            </table>
        </div>
        <!-- Employee Information -->
        
        <table width="100%" style="margin-bottom: 20px; font-size: 12px; text-align: left;">
    <tr>
        <td width="15%"><strong>Payroll :</strong></td>
        <td width="35%">{{ \Carbon\Carbon::parse($payroll->period_start)->translatedFormat('F Y') }}</td>
        <td width="15%"><strong>Periode :</strong></td>
        <td width="35%">{{ \Carbon\Carbon::parse($payroll->period_start)->format('d/m/Y') }} —
            {{ \Carbon\Carbon::parse($payroll->period_end)->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <td><strong>Name :</strong></td>
        <td>{{ $payroll->employee->employee_name }}</td>
        <td><strong>NIP :</strong></td>
        <td>{{ $payroll->employee->employee_pengenal }}</td>
    </tr>
    <tr>
        <td><strong>Company Name :</strong></td>
        <td>{{ $payroll->employee->company->name }}</td>
        <td><strong>Status :</strong></td>
        <td>{{ $payroll->employee->status_employee }}</td>
    </tr>
    <tr>
        <td><strong>Department :</strong></td>
        <td>{{ optional($payroll->employee->department()->wherePivot('is_primary', true)->first())->department_name ?? '' }}</td>
        <td><strong>Location :</strong></td>
        <td>{{ optional($payroll->employee->store()->wherePivot('is_primary', true)->first())->name ?? '' }}</td>
    </tr>
    <tr>
        <td><strong>Job Position :</strong></td>
        <td>{{ optional($payroll->employee->position()->wherePivot('is_primary', true)->first())->name ?? '' }}</td>
        <td><strong>Grading :</strong></td>
        <td>{{ $payroll->employee->grading->grading_name ?? 'Empty' }}</td>
    </tr>
    <tr>
        <td><strong>NPWP :</strong></td>
        <td>{{ $payroll->employee->npwp ?? '' }}</td>
        <td><strong>Email :</strong></td>
        <td>{{ $payroll->employee->email }}</td>
    </tr>
</table>
        <!-- Earnings and Deductions Tables -->
        <div class="tables-container">
            <table width="100%" border="1" cellspacing="0" cellpadding="5"
                style="border-collapse: collapse; margin-bottom: 20px;">
                <thead>
                    <tr style="background-color: #f5f5f5;">
                        <th width="50%">Income</th>
                        <th width="50%">Outcome (Deductions)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align: left;">
                            Attendances: {{ $payroll->attendance_days }} days<br>
                            @if ($isDW)
                            @else
                                Working Days : {{ $payroll->working_days }} days<br>
                            @endif
                            @if ($isDW)
                                Daily Rate: IDR {{ number_format($payroll->daily_rate, 0, ',', '.') }}<br>
                            @else
                                Basic Salary : IDR {{ number_format($basic, 0, ',', '.') }}<br>
                                Position Allowance: IDR {{ number_format($positional, 0, ',', '.') }}<br>
                            @endif
                            @if ($payroll->overtime_amount > 0)
                                Overtime: IDR {{ number_format($payroll->overtime_amount, 0, ',', '.') }}<br>
                            @endif
                                House Allowance: IDR {{ number_format($houseAllowance, 0, ',', '.') }}<br>
                                Meal Allowance: IDR {{ number_format($mealAllowance, 0, ',', '.') }}<br>
                                Transport Allowance: IDR {{ number_format($transportAllowance, 0, ',', '.') }}<br>
                            @if ($payroll->reimburse_amount > 0)
                                Reimburse: IDR {{ number_format($payroll->reimburse_amount, 0, ',', '.') }}
                            @endif
                        </td>
                        <td>
                            @if ($payroll->punishment > 0)
                                Punishment: IDR {{ number_format($payroll->punishment, 0, ',', '.') }}<br>
                            @endif
                            @if ($payroll->punishment_so > 0)
                                SO Punishment: IDR {{ number_format($payroll->punishment_so, 0, ',', '.') }}<br>
                            @endif
                                BPJS Ketenagakerjaan: IDR {{ number_format($bpjsKetenagakerjaan, 0, ',', '.') }}<br>
                                BPJS Kesehatan: IDR {{ number_format($bpjsKesehatan, 0, ',', '.') }}<br>
                            @if ($payroll->tax > 0)
                                Tax: IDR {{ number_format($payroll->tax, 0, ',', '.') }}<br>
                            @endif
                            @if ($payroll->debt > 0)
                                Debt: IDR {{ number_format($payroll->debt, 0, ',', '.') }}<br>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
            <!-- Totals Row -->
            <div class="totals-row">
                <div class="total-section">
                    <div>Total Incomes</div>
                    <div class="table-cell-amount">IDR
                        {{ number_format($basicOrDaily + $payroll->total_income, 0, ',', '.') }}</div>
                </div>
                <div class="total-section">
                    <div>Total Outcomes (Deductions)</div>
                    <div class="table-cell-amount">IDR {{ number_format($payroll->total_deduction, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
        <!-- Take Home Pay -->
        <div class="take-home">
            <div>Net Salary</div>
            <div>IDR {{ number_format($payroll->net_salary, 0, ',', '.') }}</div>
        </div>
        <!-- Transfer Information -->
        <div class="transfer-section">
            <div class="transfer-title">
                Transfer To {{ optional($payroll->employee->bank)->name ?? 'Bank' }}
            </div>
            <div class="transfer-details">
                <div class="transfer-account">
                    {{ $payroll->created_at ? $payroll->created_at->format('d-m-Y') : '-' }}
                    {{ optional($payroll->employee->bank)->name ?? '-' }} -
                    {{ $payroll->employee->bank_account_number ?? '-' }} a/n
                    {{ $payroll->employee->employee_name }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
