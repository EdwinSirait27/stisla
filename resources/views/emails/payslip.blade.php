<!DOCTYPE html>
<html>
<head>
    <title>Slip Gaji</title>
</head>
<body>
    <h2>Slip Gaji Periode {{ date('F Y', strtotime($payroll->month_year)) }}</h2>
    
    <p>Kepada {{ $payroll->employee->employee_name }},</p>
    
    <p>Terlampir slip gaji Anda untuk periode {{ date('F Y', strtotime($payroll->month_year)) }}.</p>
    
    <p>untuk password pdf gunakan password dengan tanggal lahir anda contoh tanggal lahir anda 12-12-2012, passwordnya menjadi 121212
    <p>Terima kasih.</p>
    
    <p>Hormat kami,<br>
    HR Department</p>
</body>
</html>