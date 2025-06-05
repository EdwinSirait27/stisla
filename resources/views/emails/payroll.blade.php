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
        <p>Kepada Yth,<br>{{ $employeeName }}</p>
        <p>Berikut adalah slip gaji Anda untuk periode <strong>{{ $payrollPeriod }}</strong> yang telah dibayarkan pada tanggal <strong>{{ $payrollDate }}</strong>.</p>
        <table>
            <tr>
                <th>Keterangan</th>
                <th>Jumlah</th>
            </tr>
            <tr>
                <td>Total Gaji</td>
               
                <td>Lihat Detail PDF</td>
                {{-- <td>Rp {{ number_format(floatval($basicSalary), 0, ',', '.') }}</td> --}}

            </tr>
            <tr>
                <td>Potongan</td>
                <td>Lihat Detail PDF</td>
                {{-- <td>Rp {{ number_format(floatval($grossSalary), 0, ',', '.') }}</td> --}}

            </tr>
        </table>
        <p>Untuk detail lengkap, silakan mengunduh lampiran slip gaji dalam format PDF.</p>
        
        <p>Jika Anda memiliki pertanyaan terkait slip gaji, silakan hubungi Departemen HR Mahendradata Jaya Mandiri.</p>
        <p>Untuk membuka file pdf gunakan tanggal lahir anda formatnya yyyymmdd contoh 19450817.</p>
        
        <p>Terima kasih, Tuhan Berkati Selalu<br>
        Departemen HR Mahendradata Jaya Mandiri</p>
    </div>
    
    <div class="footer">
        <p>Email ini dikirim secara otomatis, mohon untuk tidak membalas email ini.</p>
        <p>Mark important untuk pengirim email ini untuk mendapatkan notifikasi slip gaji untuk bulan berikutnya .</p>
    </div>
</body>
</html>