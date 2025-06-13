<!DOCTYPE html>
<html>
<head>
    <title>Payrolls</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-bottom: 2px solid #ddd;
        }
        .content {
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Salary Statement  - {{ $payrollPeriod }}</h2>
    </div>
    <div class="content">
        <p>Dear,<br>{{ $employeeName }}</p>
        <p>Here is your pay slip for period <strong>{{ $payrollPeriod }}</strong> .</p>
        <table>
            <tr>
                <th>Information</th>
                <th>Amount</th>
            </tr>
            <tr>
                <td>Total Salary</td>
                <td>View PDF Details</td>
                {{-- <td>Rp {{ number_format(floatval($basicSalary), 0, ',', '.') }}</td> --}}
            </tr>
            <tr>
                <td>Total Deductions</td>
                <td>View PDF Details</td>
                {{-- <td>Rp {{ number_format(floatval($grossSalary), 0, ',', '.') }}</td> --}}
            </tr>
        </table>
        <p>For full details, please download the pay slip attachment in PDF format.</p>
        <p>If you have any questions regarding pay slips, please contact the HR Department of PT. Mahendradata Jaya Mandiri.</p>
        <p>To open a PDF file, use your date of birth in the format yyyymmdd. For example, you were born on August 6, 2000, so the format is 20000606.</p>
        
        <p>Thank you, God bless<br>
        HR Department PT. Mahendradata Jaya Mandiri</p>
    </div>
    
    <div class="footer">
        <p>This email was sent automatically, please do not reply to this email.</p>
        <p>Mark important for the sender of this email to get the pay slip notification for the next month.</p>
        <p>&copy;edwinsirait</p>
    </div>
</body>
</html>