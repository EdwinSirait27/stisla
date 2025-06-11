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
        /* setiap bulan 25 sampe 26 ok */
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
        .logo {
            width: 70px;
            height: auto;
            margin-right: 10px;
            background-color: #000;
            padding: 5px;
        }
        .confidential {
            color: #FF0000;
            font-weight: bold;
        }
        .title-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .title-left {
            font-weight: bold;
            font-size: 16px;
        }
        .title-right {
            font-weight: bold;
            font-size: 18px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr 1fr 1.5fr;
            gap: 8px;
            margin-bottom: 20px;
        }

        .info-label {
            font-weight: normal;
        }

        .info-value {
            font-weight: bold;
        }

        .tables-container {
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }

        .tables-header {
            display: grid;
            grid-template-columns: 1fr 1fr;
            background-color: #f5f5f5;
        }

        .tables-header-cell {
            padding: 8px 12px;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
        }

        .tables-header-cell:first-child {
            border-right: 1px solid #ddd;
        }

        .tables-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .table-section {
            padding: 0;
        }

        .table-section:first-child {
            border-right: 1px solid #ddd;
        }

        .table-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            padding: 6px 12px;
            border-bottom: 1px solid #f0f0f0;
        }

        .table-cell-amount {
            text-align: right;
            font-family: monospace;
        }

        /* .totals-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-top: 1px solid #ddd;
            background-color: #f9f9f9;
        }

     .total-section {
    display: grid;
    grid-template-columns: 2fr 1fr;
    align-items: center; 
    padding: 8px 12px;
    font-weight: bold;
}

        .total-section:first-child {
            border-right: 1px solid #ddd;
        } */
.totals-row {
    display: flex;
    gap: 24px; /* jarak antar kolom income & outcome */
    margin-bottom: 1rem;
}

.total-section {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center; /* penting agar label & amount sejajar vertikal */
    padding: 8px 12px;
    font-weight: bold;
    border: 1px solid #ddd; /* opsional */
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
    {{-- periode tanggal harus masuk --}}
    <div class="watermark">CONFIDENTIAL</div>

    <div class="payslip-container" id="payslip-content" style="font-family: Arial, sans-serif; font-size: 14px;">

        <!-- Header with Logo and Confidential -->
        <div class="header"
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <table style="width: 100%; margin-bottom: 20px;">
                <tr>
                    <td style="text-align: left;"> 
                        @php
                        $foto = 'company/' . ($payroll->employee->company->foto ?? '');
                        $imageData = '';
                    
                        $fotoPath = public_path('storage/' . $foto);
                    
                        if (!empty($foto) && file_exists($fotoPath) && is_file($fotoPath)) {
                            $type = pathinfo($fotoPath, PATHINFO_EXTENSION);
                            $imageData = 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($fotoPath));
                        }
                    @endphp
                    
                    @if (!empty($imageData))
                        <div style="background-color: #000000; display: inline-block; padding: 5px;">
                            <img src="{{ $imageData }}" alt="Foto Perusahaan" width="70">
                        </div>
                    @else
                        <span>Foto tidak tersedia</span>
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
                <td class="text-align: left;"><strong>Payroll Month :</strong></td>
                <td>{{ $formattedMonthYear }}</td>
                <td><strong>Payroll Periode :</strong></td>
                <td>{{ $payroll->period }}</td>
                {{-- <td><strong>Email :</strong></td>
                <td>{{ $payroll->employee->email }}</td> --}}
            </tr>
            <tr>
                <td><strong>Name :</strong></td>
                <td>{{ $payroll->employee->employee_name }}</td>
                <td><strong>Status :</strong></td>
                <td>{{ $payroll->employee->status_employee }}</td>
            </tr>
            
            <tr>
                <td><strong>Job position :</strong></td>
                <td>{{ optional(optional($payroll->employee)->position)->name ?? '' }}</td>

                <td><strong>NPWP :</strong></td>
                {{-- <td>{{ $payroll->employee->npwp }}</td> --}}
                <td>{{ optional(optional($payroll)->employee)->npwp ?? '' }}</td>

            </tr>
            <tr>
                <td><strong>Department :</strong></td>
                {{-- <td>{{ $payroll->employee->department->department_name }}</td> --}}
                <td>{{ optional(optional($payroll->employee)->department)->department_name ?? '' }}</td>

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
                        <td class="text-align: left;">
                            Attendances: {{ $payroll->attendance }} days<br>
                            {{-- Daily Allowance: IDR {{ number_format($payroll->employee->daily_allowance, 0, ',', '.') }}<br> --}}
                            Daily Allowance: IDR {{ number_format($daily_allowance, 0, ',', '.') }}<br>

                            Overtime: IDR {{ number_format($overtime, 0, ',', '.') }}<br>
                            Bonuses: IDR {{ number_format($bonus, 0, ',', '.') }}<br>
                            House Allowance: IDR {{ number_format($house_allowance, 0, ',', '.') }}<br>
                            Meal Allowance: IDR {{ number_format($meal_allowance, 0, ',', '.') }}<br>
                            Transport Allowance: IDR {{ number_format($transport_allowance, 0, ',', '.') }}
                        </td>
                        <td>
                            
                            Late Fine: IDR {{ number_format($late_fine, 0, ',', '.') }}<br>
                            Punishment: IDR {{ number_format($punishment, 0, ',', '.') }}<br>
                            BPJS Ketenagakerjaan: IDR {{ number_format($bpjs_ket, 0, ',', '.') }}<br>
                            BPJS Kesehatan: IDR {{ number_format($bpjs_kes, 0, ',', '.') }}<br>
                            Tax: IDR {{ number_format($tax, 0, ',', '.') }}<br>
                            Debt: IDR {{ number_format($debt, 0, ',', '.') }}<br>
                        </td>
                    </tr>
                </tbody>
            </table>
            <!-- Totals Row -->
            <div class="totals-row">
                <div class="total-section">
                    <div>Total Incomes</div>
                    <div class="table-cell-amount">IDR {{ number_format($salary, 2, '.', ',') }}</div>
                </div>
                <div class="total-section">
                    <div>Total Outcomes (Deductions)</div>
                    <div class="table-cell-amount">IDR {{ number_format($deductions, 2, '.', ',') }}</div>
                </div>
            </div>
        </div>
        <!-- Take Home Pay -->
        <div class="take-home">
            <div>Take Home Pay</div>
            <div>IDR {{ number_format($take_home, 2, '.', ',') }}</div>
        </div>
        <!-- Transfer Information -->
        <div class="transfer-section">
          <div class="transfer-title">
    Transfer To {{$payroll->employee?->bank?->name ?? 'Bank'}}
</div>


            <div class="transfer-details">
                <div class="transfer-account">{{ $payroll->created_at ? $payroll->created_at->format('d-m-Y') : '-' }}
 {{$payroll->employee->bank_name}} - {{$payroll->employee->name_account_number}} a/n {{$payroll->employee->employee_name}}</div>
                {{-- <div class="transfer-account">{{ $monthYearHuman }} {{$payroll->employee->bank_name}} - {{$payroll->employee->name_account_number}} a/n {{$payroll->employee->employee_name}}</div> --}}
                {{-- <div class="table-cell-amount">IDR {{ number_format($takehome, 2, '.', ',') }}</div> --}}
            </div>
        </div>
    </div>
</body>
</html>