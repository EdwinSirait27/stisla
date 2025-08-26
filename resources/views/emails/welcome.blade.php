<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome Email</title>
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
        <h2>Dear {{ $employee->employee_name }},</h2>
        <p>We are pleased to welcome you to <span class="highlight">PT. Mahendradata Jaya Mandiri</span>.</p>
        <p>Your Employee Identification Number is: <span class="highlight">{{ $employee->employee_pengenal }}</span>.</p>
        <p>We are confident that your skills and experience will contribute greatly to our company’s success.</p>
        <p>Here is your employment details below:</p>
        <div class="info-box">
            <p><strong>Company:</strong> {{ $employee->company->name ?? '-' }}</p>
            <p><strong>Location:</strong> {{ $employee->store->name ?? '-' }}</p>
            <p><strong>Department:</strong> {{ $employee->department->department_name ?? '-' }}</p>
            <p><strong>Position:</strong> {{ $employee->position->name ?? '-' }}</p>
            <p><strong>Daily Allowance:</strong> {{ $employee->daily_allowance ?? 'To be informed' }}</p>
        </div>
        <p>Best Regards,<br>
            <strong>HR Department<br>PT. Mahendradata Jaya Mandiri</strong>
        </p>
        <div class="note">
            This email was sent automatically. Please do not reply.<br><br>
            For further information, please contact our HR Department via WhatsApp:
            <a href="https://wa.me/6281138310552" style="color:#25D366; text-decoration:none; font-weight:bold;">
                HR Department Mahendradata Jaya Mandiri
            </a>
        </div>
        <hr style="margin:30px 0; border:0; border-top:1px solid #ddd;">
        <h2>Kepada Yth. {{ $employee->employee_name }},</h2>
        <p>Dengan senang hati kami menyambut anda di <span class="highlight">PT. Mahendradata Jaya Mandiri</span>.</p>
        <p>Nomor Induk Pegawai (NIP) anda adalah: <span class="highlight">{{ $employee->employee_pengenal }}</span>.</p>
        <p>Kami yakin keterampilan dan pengalaman anda akan memberikan kontribusi besar bagi kesuksesan perusahaan kami.
        </p>
        <p>Berikut detail informasi pekerjaan anda:</p>
        <div class="info-box">
            <p><strong>Perusahaan:</strong> {{ $employee->company->name ?? '-' }}</p>
            <p><strong>Lokasi:</strong> {{ $employee->store->name ?? '-' }}</p>
            <p><strong>Departemen:</strong> {{ $employee->department->department_name ?? '-' }}</p>
            <p><strong>Jabatan:</strong> {{ $employee->position->name ?? '-' }}</p>
            <p><strong>Tunjangan Harian:</strong> {{ $employee->daily_allowance ?? 'Akan diinformasikan' }}</p>
        </div>
        <p>Hormat kami,<br>
            <strong>Departemen HR<br>PT. Mahendradata Jaya Mandiri</strong>
        </p>
        <div class="note">
            Email ini dikirim secara otomatis. Mohon untuk tidak membalas.<br><br>
            Untuk bantuan lebih lanjut, silakan menghubungi HR Departemen melalui WhatsApp:
            <a href="https://wa.me/6281138310552" style="color:#25D366; text-decoration:none; font-weight:bold;">
                Departemen HR Mahendradata Jaya Mandiri
            </a>
        </div>
        <div class="footer">
            © {{ date('Y') }} Edwin Sirait.
        </div>
    </div>
</body>
</html>
