<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Company Announcement</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 650px;
            margin: 35px auto;
            background: #ffffff;
            border-radius: 10px;
            padding: 35px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
        }

        h2 {
            color: #2f3d4a;
            margin-bottom: 18px;
            font-size: 20px;
            font-weight: bold;
        }

        p {
            color: #4a4a4a;
            line-height: 1.7;
            font-size: 14px;
        }

        .section-title {
            font-weight: bold;
            color: #2f3d4a;
            font-size: 15px;
            margin-top: 20px;
            margin-bottom: 8px;
        }

        .info-box {
            background: #f0f4f8;
            border-radius: 8px;
            padding: 18px;
            margin: 20px 0;
        }

        .info-box p {
            margin: 8px 0;
            font-size: 14px;
        }

        .footer {
            margin-top: 35px;
            font-size: 12px;
            color: #8b8b8b;
            border-top: 1px solid #e5e5e5;
            padding-top: 15px;
            text-align: center;
        }

        .note {
            font-size: 12px;
            color: #9a9a9a;
            margin-top: 20px;
            text-align: center;
        }

        a {
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Dear {{ $employee->employee_name }},</h2>
        <p>
            We hereby inform you of the following official company announcement:
        </p>
        <div class="info-box">
            <p><strong>Announcement Title:</strong> {{ $announcement->title }}</p>
            <p><strong>Details:</strong><br> {!! $announcement->content !!}
            </p>
            <p><strong>Effective Date:</strong>
                {{ \Carbon\Carbon::parse($announcement->publish_date)->format('d M Y') }}</p>
            {{-- @if ($announcement->end_date)
            <p><strong>Valid Until:</strong> {{ \Carbon\Carbon::parse($announcement->end_date)->format('d M Y') }}</p>
            @endif --}}
            <p><strong>Valid Until:</strong>
                @if ($announcement->end_date)
                    {{ \Carbon\Carbon::parse($announcement->end_date)->format('d M Y') }}
                @else
                    Continuously
                @endif
            </p>
        </div>
        <p>
            Should you have any questions regarding this announcement, please contact our HR Department.
        </p>
        <p>
            Sincerely,<br>
            <strong>Human Resources Department<br>PT. Mahendradata Jaya Mandiri</strong>
        </p>
        <div class="note">
            This email is generated automatically. Please do not reply.<br>
            For assistance, contact HR via WhatsApp:<br>
            <a href="https://wa.me/6281138310552" style="color:#25D366; font-weight:bold;">
                HR Department - PT Mahendradata Jaya Mandiri
            </a>
        </div>
        <hr style="margin:30px 0; border:0; border-top:1px solid #dcdcdc;">
        <h2>Kepada Yth. {{ $employee->employee_name }},</h2>
        <p>
            Dengan ini kami menyampaikan informasi mengenai pengumuman resmi perusahaan sebagai berikut:
        </p>
        <div class="info-box">
            <p><strong>Judul Pengumuman:</strong> {{ $announcement->title }}</p>
            <p><strong>Isi Pengumuman:</strong><br> {!! $announcement->content !!}</p>
            <p><strong>Tanggal Berlaku:</strong>
                {{ \Carbon\Carbon::parse($announcement->publish_date)->format('d M Y') }}</p>
           
            <p><strong>Berlaku Hingga:</strong>
                @if ($announcement->end_date)
                    {{ \Carbon\Carbon::parse($announcement->end_date)->format('d M Y') }}
                @else
                    Seterusnya
                @endif
            </p>
        </div>
        <p>
            Jika terdapat pertanyaan atau membutuhkan klarifikasi lebih lanjut, silakan menghubungi Departemen HR.
        </p>
        <p>
            Hormat kami,<br>
            <strong>Departemen Human Resources<br>PT. Mahendradata Jaya Mandiri</strong>
        </p>
        <div class="note">
            Email ini dikirim secara otomatis. Mohon untuk tidak membalas email ini.<br>
            Untuk bantuan, hubungi HR melalui WhatsApp:<br>
            <a href="https://wa.me/6281138310552" style="color:#25D366; font-weight:bold;">
                Departemen HR - PT Mahendradata Jaya Mandiri
            </a>
        </div>
        <div class="footer">
            © {{ date('Y') }} HRX — PT Mahendradata Jaya Mandiri.
        </div>
    </div>
</body>
</html>
