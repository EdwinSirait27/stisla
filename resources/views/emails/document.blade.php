<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your Document</title>
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
        <h2>Dear {{ $employee->employee_name ?? '-' }},</h2>
        <p>Please find your document from <span class="highlight">{{ $companyName }}</span> attached to this email.</p>
        <div class="info-box">
            <p><strong>Document Number:</strong> {{ $documentNumber }}</p>
            <p><strong>Document Type:</strong> {{ $documentName }}</p>
        </div>
        <p>The document is attached as a PDF file. If it is password protected, please contact HR for the password.</p>
        <p>Best Regards,<br>
            <strong>HR Department<br>PT. Asian Bay Development</strong>
        </p>
        <div class="note">
            This email was sent automatically. Please do not reply.<br>
            For further information, please contact our HR Department via WhatsApp:
            <a href="https://wa.me/6281138310552" style="color:#25D366; text-decoration:none; font-weight:bold;">
                HR Department Asian Bay Development
            </a>
        </div>
        <hr style="margin:30px 0; border:0; border-top:1px solid #ddd;">
        <h2>Kepada Yth. {{ $employee->employee_name ?? '-' }},</h2>
        <p>Bersama email ini terlampir dokumen anda dari <span class="highlight">{{ $companyName }}</span>.</p>
        <div class="info-box">
            <p><strong>Nomor Dokumen:</strong> {{ $documentNumber }}</p>
            <p><strong>Jenis Dokumen:</strong> {{ $documentName }}</p>
        </div>
        <p>Dokumen terlampir dalam bentuk PDF. Apabila dilindungi kata sandi, silakan hubungi HR untuk mendapatkan kata sandinya.</p>
        <p>Hormat kami,<br>
            <strong>Departemen HR<br>PT. Asian Bay Development</strong>
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
