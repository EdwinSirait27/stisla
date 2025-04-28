<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Statement</title>
    <link rel="icon" type="image/png"
    href="{{ asset('img/1710675344-17-03-2024-iSZQk9yVubtJh31N46lxpnC7av5osrLW.ico') }}">
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }
    
        body {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        margin: 0;
        padding: 0;
        line-height: 1.5;
        color: #333;
    }
    .container {
        max-width: 800px;
        margin: 0 auto;
        border: 1px solid #ddd;
        padding: 30px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        .title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        .subtitle {
            font-size: 16px;
            color: #666;
        }
        .employee-info {
            margin-bottom: 25px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
            border-left: 4px solid #2c3e50;
        }
        .employee-info h3 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        .section {
            margin-bottom: 25px;
            padding: 15px;
            background-color: #fff;
            border: 1px solid #eee;
            border-radius: 5px;
        }
        .section-title {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
            color: #2c3e50;
        }
        .item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px dotted #eee;
        }
        .total {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #333;
            font-size: 16px;
            color: #2c3e50;
        }
        .grand-total {
            background-color: #f2f7ff;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
            font-size: 18px;
            color: #2c3e50;
        }
     
        .signature {
    display: flex;
    justify-content: space-between;
    margin-top: 50px;
    page-break-inside: avoid;
}

.signature-box {
    width: 45%; 
    text-align: center;
    position: relative;
}

.signature-line {
    border-top: 1px solid #000;
    width: 80%; 
    margin: 50px auto 0 auto; 
    padding-top: 10px;
}

.signature-text {
    position: absolute;
    bottom: -25px; 
    left: 0;
    right: 0;
    text-align: center;
    font-weight: bold;
}
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="title">SALARY STATEMENT</div>
            <div class="subtitle">Period: {{ $payroll->month_year->format('Y-m') }} </div>
        </div>
        
        <div class="employee-info">
            <h3>Employee Information</h3>
            <table>
                <tr>
                    <td width="30%"><strong>Name:</strong></td>
                    <td>{{$payroll->employee->employee_name}}</td>
                </tr>
                <tr>
                    <td><strong>Department:</strong></td>
                    <td>{{$payroll->employee->department->department_name}}</td>
                </tr>
                <tr>
                    <td><strong>Position:</strong></td>
                    <td>{{$payroll->employee->position->name}}</td>
                </tr>
                
            </table>
        </div>
        
        <div class="section">
            <div class="section-title">INCOME</div>
            <div class="item">
                <span>Attendances:</span>
                <span>{{$payroll->attendance}} Days</span>
                
            </div>
            <div class="item">
                <span>Daily Allowance:</span>
                <span>IDR {{ number_format($payroll->daily_allowance, 2, '.', ',') }}

            </div>
            <div class="item">
                <span>Overtime:</span>
                <span>IDR {{ number_format($payroll->overtime, 2, '.', ',') }}

            </div>
            <div class="item">
                <span>Bonuses:</span>
                <span>IDR {{ number_format($payroll->bonus, 2, '.', ',') }}

            </div>
            <div class="item">
                <span>House Allowance:</span>
                <span>IDR {{ number_format($payroll->house_allowance, 2, '.', ',') }}

            </div>
            <div class="item">
                <span>Meal Allowance:</span>
                <span>IDR {{ number_format($payroll->meal_allowance, 2, '.', ',') }}

            </div>
            <div class="item">
                <span>Transport Allowance:</span>
                <span>IDR {{ number_format($payroll->transport_allowance, 2, '.', ',') }}

            </div>
            <div class="total">
                <span>Total Income:</span>
                <span>IDR {{ number_format($salaryincome, 2, '.', ',') }}
                </span>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">DEDUCTIONS</div>
           
            <div class="item">
                <span>Late Fine:</span>
                <span>IDR {{ number_format($payroll->late_fine, 2, '.', ',') }}

            </div>
            <div class="item">
                <span>Punishment:</span>
                <span>IDR {{ number_format($payroll->punishment, 2, '.', ',') }}

            </div>
            <div class="item">
                <span>Mesh:</span>
                <span>IDR {{ number_format($payroll->mesh, 2, '.', ',') }}

            </div>
            <div class="item">
                <span>BPJS Ketenagakerjaan:</span>
                <span>IDR {{ number_format($payroll->bpjs_ket, 2, '.', ',') }}

            </div>
            <div class="item">
                <span>BPJS Kesehatan:</span>
                <span>IDR {{ number_format($payroll->bpjs_kes, 2, '.', ',') }}

            </div>
            <div class="total">
                <span>Total Deductions:</span>
                <span>IDR {{ number_format($salaryoutcome, 2, '.', ',') }}

            </div>
        </div>
        
        <div class="grand-total">
            <div class="total">
                <span>GRAND TOTAL SALARY:</span>
                <span>IDR {{ number_format($payroll->salary, 2, '.', ',') }}

            </div>
        </div>
        
        <div class="signature">
            {{-- <div class="signature-box">
                <div class="signature-line"></div>
                <strong>Approved By:</strong><br>
                Management<br>
                
            </div> --}}
            <br>
            <br>
            <br>
            <div class="signature-box">
                <div class="signature-line"></div>
                <strong>Received By:</strong><br>
                {{$payroll->employee->employee_name}}
            </div>
        </div>
        
        <div class="footer">
            This document is electronically generated and valid without signature.<br>
            For any inquiries regarding this salary statement, please contact HR Department.
        </div>
    </div>
</body>
</html>
