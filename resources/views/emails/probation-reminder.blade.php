<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Probation Reminder</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f6f9fc;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        p {
            color: #555555;
            line-height: 1.6;
            font-size: 14px;
        }
        .highlight {
            font-weight: bold;
            color: #2c3e50;
        }
        .info-box {
            background: #f0f4f8;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .info-box p {
            margin: 6px 0;
            font-size: 14px;
        }
        .badge {
            display: inline-block;
            background: #e8f5e9;
            color: #2e7d32;
            border-radius: 6px;
            padding: 4px 12px;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .footer {
            margin-top: 30px;
            font-size: 13px;
            color: #888888;
            border-top: 1px solid #eee;
            padding-top: 15px;
            text-align: center;
        }
        .note {
            font-size: 12px;
            color: #999999;
            margin-top: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">

        {{-- ===== ENGLISH ===== --}}
        <span class="badge">Probation Reminder — 3 Month</span>
        <h2>Dear {{ $headHR->name }},</h2>
        <p>This is an automated reminder that the following employee has completed <span class="highlight">3 months</span> of employment as of today, <span class="highlight">{{ now()->format('d F Y') }}</span>.</p>
        <p>Please review their probation status and take the necessary action.</p>

        <div class="info-box">
            <p><strong>Employee Name:</strong> {{ $employee->employee_name }}</p>
            <p><strong>Employee ID:</strong> {{ $employee->employee_pengenal }}</p>
            <p><strong>Company:</strong> {{ $employee->company->name ?? '-' }}</p>
            <p><strong>Location:</strong> {{ $employee->store->name ?? '-' }}</p>
            <p><strong>Department:</strong> {{ $employee->department->department_name ?? '-' }}</p>
            <p><strong>Position:</strong> {{ $employee->position->name ?? '-' }}</p>
            <p><strong>Join Date:</strong> {{ \Carbon\Carbon::parse($employee->join_date)->format('d F Y') }}</p>
            <p><strong>Status:</strong> {{ $employee->status }}</p>
        </div>

        <p>Kindly ensure this employee's probation evaluation is completed promptly.</p>
        <p>Best Regards,<br>
            <strong>HRX<br>PT. Asian Bay Development</strong>
        </p>

        <div class="note">
            This email was sent automatically. Please do not reply.<br>
            For further information, please contact our HR Department via WhatsApp:
            <a href="https://wa.me/6281138310552" style="color:#25D366; text-decoration:none; font-weight:bold;">
                HR Department Asian Bay Development
            </a>
        </div>

        <hr style="margin:30px 0; border:0; border-top:1px solid #ddd;">

        {{-- ===== INDONESIA ===== --}}
        <span class="badge">Pengingat Masa Percobaan — 3 Bulan</span>
        <h2>Kepada Yth. {{ $headHR->name }},</h2>
        <p>Ini adalah pengingat otomatis bahwa karyawan berikut telah menyelesaikan <span class="highlight">3 bulan</span> masa kerja terhitung hari ini, <span class="highlight">{{ now()->locale('id')->isoFormat('D MMMM Y') }}</span>.</p>
        <p>Mohon tinjau status masa percobaan karyawan tersebut dan ambil tindakan yang diperlukan.</p>
        <div class="info-box">
            <p><strong>Nama Karyawan:</strong> {{ $employee->employee_name }}</p>
            <p><strong>NIP:</strong> {{ $employee->employee_pengenal }}</p>
            <p><strong>Perusahaan:</strong> {{ $employee->company->name ?? '-' }}</p>
            <p><strong>Lokasi:</strong> {{ $employee->store->name ?? '-' }}</p>
            <p><strong>Departemen:</strong> {{ $employee->department->department_name ?? '-' }}</p>
            <p><strong>Jabatan:</strong> {{ $employee->position->name ?? '-' }}</p>
            <p><strong>Tanggal Bergabung:</strong> {{ \Carbon\Carbon::parse($employee->join_date)->locale('id')->isoFormat('D MMMM Y') }}</p>
            <p><strong>Status:</strong> {{ $employee->status }}</p>
        </div>

        <p>Mohon pastikan evaluasi masa percobaan karyawan ini diselesaikan tepat waktu.</p>
        <p>Hormat kami,<br>
            <strong>HRX<br>PT. Asian Bay Development</strong>
        </p>

        <div class="note">
            Email ini dikirim secara otomatis. Mohon untuk tidak membalas.<br>
            Untuk bantuan lebih lanjut, silakan menghubungi HR Departemen melalui WhatsApp:
            <a href="https://wa.me/6281138310552" style="color:#25D366; text-decoration:none; font-weight:bold;">
                Departemen HR Asian Bay Development
            </a>
        </div>

        <div class="footer">
            © {{ date('Y') }} HRX. PT Asian Bay Development Created by Edwin Sirait.
        </div>

    </div>
</body>
</html>