{{--
    Email slip gaji. Ditulis untuk klien email (Gmail, Outlook, iOS Mail):
    layout tabel + inline style, tanpa CSS variable / flex / grid karena banyak klien membuangnya.
    Nominal gaji sengaja tidak ditampilkan di badan email; ada di PDF ber-password.

    Variabel: $payroll (public property PayrollSlipMail), $slipPassword, $employeeName,
    $payrollPeriod, $companyName.
--}}
@php
    $emp = $payroll->employee;
    $start = \Carbon\Carbon::parse($payroll->period_start)->locale('id');
    $end = \Carbon\Carbon::parse($payroll->period_end)->locale('id');
    $startEn = \Carbon\Carbon::parse($payroll->period_start)->locale('en');
    $endEn = \Carbon\Carbon::parse($payroll->period_end)->locale('en');
    $periodEn = \Carbon\Carbon::parse($payroll->period_end)->locale('en')->translatedFormat('F Y');
    $isDw = strtoupper($emp->status_employee ?? '') === 'DW';
    $attachment = $payroll->slipFilename();

    // Palet: brand indigo Stisla + netral. Hex, karena klien email tidak membaca oklch/var().
    $c = [
        'page' => '#f3f4f8',
        'panel' => '#ffffff',
        'soft' => '#f6f7fb',
        'rule' => '#e3e5ee',
        'ink' => '#1f2433',
        'ink2' => '#4a5163',
        'muted' => '#6b7183',
        'accent' => '#4a4fc4',
        'accentSoft' => '#eef0ff',
        'warn' => '#8a5a00',
        'warnSoft' => '#fff6e0',
    ];
    $font = "'Segoe UI', 'Helvetica Neue', Arial, sans-serif";
    $mono = "'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace";
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light only">
    <meta name="supported-color-schemes" content="light only">
    <title>Slip gaji {{ $payrollPeriod }}</title>
</head>

<body style="margin:0;padding:0;background-color:{{ $c['page'] }};font-family:{{ $font }};color:{{ $c['ink'] }};-webkit-text-size-adjust:100%;">
    {{-- Preheader: teks pratinjau di daftar inbox --}}
    <div style="display:none;max-height:0;overflow:hidden;mso-hide:all;">
        Slip gaji {{ $payrollPeriod }} terlampir sebagai PDF. Password ada di email ini.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:{{ $c['page'] }};">
        <tr>
            <td align="center" style="padding:24px 12px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                    style="max-width:600px;background-color:{{ $c['panel'] }};border:1px solid {{ $c['rule'] }};border-radius:8px;">

                    {{-- Head --}}
                    <tr>
                        <td style="padding:24px 28px 20px;border-bottom:1px solid {{ $c['rule'] }};">
                            <p style="margin:0 0 4px;font-size:13px;line-height:1.4;color:{{ $c['muted'] }};">{{ $companyName }}</p>
                            <h1 style="margin:0;font-size:22px;line-height:1.25;font-weight:700;color:{{ $c['ink'] }};">
                                Slip gaji {{ $payrollPeriod }}
                            </h1>
                            <p style="margin:4px 0 0;font-size:14px;line-height:1.4;color:{{ $c['ink2'] }};">
                                Salary statement {{ $periodEn }}
                            </p>
                        </td>
                    </tr>

                    {{-- Greeting --}}
                    <tr>
                        <td style="padding:24px 28px 8px;font-size:15px;line-height:1.6;color:{{ $c['ink'] }};">
                            <p style="margin:0 0 12px;">Halo <strong>{{ $employeeName }}</strong>,</p>
                            <p style="margin:0;">
                                Slip gaji Anda untuk periode {{ $payrollPeriod }} terlampir di email ini sebagai file PDF.
                                Rincian gaji, tunjangan, dan potongan ada di dalam file tersebut.
                            </p>
                        </td>
                    </tr>

                    {{-- Facts --}}
                    <tr>
                        <td style="padding:16px 28px 8px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="border:1px solid {{ $c['rule'] }};border-radius:6px;font-size:14px;line-height:1.4;">
                                <tr>
                                    <td style="padding:10px 14px;color:{{ $c['ink2'] }};border-bottom:1px solid {{ $c['rule'] }};width:42%;">Periode</td>
                                    <td style="padding:10px 14px;color:{{ $c['ink'] }};font-weight:600;border-bottom:1px solid {{ $c['rule'] }};">
                                        {{ $start->translatedFormat('d M Y') }} sampai {{ $end->translatedFormat('d M Y') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 14px;color:{{ $c['ink2'] }};border-bottom:1px solid {{ $c['rule'] }};">NIP</td>
                                    <td style="padding:10px 14px;color:{{ $c['ink'] }};font-weight:600;border-bottom:1px solid {{ $c['rule'] }};">
                                        {{ $emp->employee_pengenal ?? '-' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 14px;color:{{ $c['ink2'] }};border-bottom:1px solid {{ $c['rule'] }};">Kehadiran</td>
                                    <td style="padding:10px 14px;color:{{ $c['ink'] }};font-weight:600;border-bottom:1px solid {{ $c['rule'] }};">
                                        @if ($isDw)
                                            {{ $payroll->attendance_days }} hari hadir
                                        @else
                                            {{ $payroll->attendance_days }} dari {{ $payroll->working_days }} hari kerja
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 14px;color:{{ $c['ink2'] }};">Lampiran</td>
                                    <td style="padding:10px 14px;color:{{ $c['ink'] }};font-weight:600;word-break:break-all;">
                                        {{ $attachment }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Password --}}
                    <tr>
                        <td style="padding:16px 28px 8px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="background-color:{{ $c['accentSoft'] }};border-radius:6px;">
                                <tr>
                                    <td style="padding:18px 20px;">
                                        <p style="margin:0 0 8px;font-size:14px;line-height:1.4;font-weight:600;color:{{ $c['ink'] }};">
                                            Password untuk membuka PDF
                                        </p>
                                        <p style="margin:0 0 10px;font-family:{{ $mono }};font-size:26px;line-height:1.2;font-weight:700;letter-spacing:4px;color:{{ $c['accent'] }};">
                                            {{ $slipPassword }}
                                        </p>
                                        <p style="margin:0;font-size:13px;line-height:1.5;color:{{ $c['ink2'] }};">
                                            Ketik persis seperti di atas: 8 karakter, huruf besar semua, tanpa spasi.
                                            Password ini hanya berlaku untuk slip periode {{ $payrollPeriod }}.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Steps --}}
                    <tr>
                        <td style="padding:16px 28px 8px;font-size:14px;line-height:1.6;color:{{ $c['ink'] }};">
                            <p style="margin:0 0 6px;font-weight:600;">Cara membuka slip</p>
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="font-size:14px;line-height:1.6;color:{{ $c['ink'] }};">
                                <tr>
                                    <td valign="top" style="padding:0 10px 4px 0;color:{{ $c['accent'] }};font-weight:700;">1</td>
                                    <td style="padding:0 0 4px;">Unduh lampiran {{ $attachment }}.</td>
                                </tr>
                                <tr>
                                    <td valign="top" style="padding:0 10px 4px 0;color:{{ $c['accent'] }};font-weight:700;">2</td>
                                    <td style="padding:0 0 4px;">Buka dengan aplikasi PDF di HP atau komputer.</td>
                                </tr>
                                <tr>
                                    <td valign="top" style="padding:0 10px 0 0;color:{{ $c['accent'] }};font-weight:700;">3</td>
                                    <td>Masukkan password di atas saat diminta.</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Security --}}
                    <tr>
                        <td style="padding:16px 28px 8px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="background-color:{{ $c['warnSoft'] }};border-radius:6px;">
                                <tr>
                                    <td style="padding:12px 16px;font-size:13px;line-height:1.5;color:{{ $c['warn'] }};">
                                        Jaga kerahasiaan password dan slip ini. Jangan teruskan email ini ke orang lain.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Contact + sign-off --}}
                    <tr>
                        <td style="padding:16px 28px 24px;font-size:14px;line-height:1.6;color:{{ $c['ink'] }};">
                            <p style="margin:0 0 12px;">
                                Ada pertanyaan tentang slip ini? Hubungi Departemen HR {{ $companyName }}.
                            </p>
                            <p style="margin:0;">
                                Terima kasih,<br>
                                Departemen HR<br>
                                {{ $companyName }}
                            </p>
                        </td>
                    </tr>

                    {{-- English --}}
                    <tr>
                        <td style="padding:20px 28px 24px;border-top:1px solid {{ $c['rule'] }};background-color:{{ $c['soft'] }};border-radius:0 0 8px 8px;font-size:13px;line-height:1.6;color:{{ $c['ink2'] }};">
                            <p style="margin:0 0 8px;font-size:14px;font-weight:600;color:{{ $c['ink'] }};">In English</p>
                            <p style="margin:0 0 8px;">
                                Dear {{ $employeeName }}, your pay slip for {{ $periodEn }}
                                ({{ $startEn->translatedFormat('d M Y') }} to {{ $endEn->translatedFormat('d M Y') }})
                                is attached as {{ $attachment }}.
                                Open it with the password shown above: 8 characters, all capitals, no spaces.
                            </p>
                            <p style="margin:0 0 8px;">
                                Please keep this password and pay slip confidential and do not forward this email.
                                For questions, contact the HR Department of {{ $companyName }}.
                            </p>
                            <p style="margin:0;">Thank you, HR Department, {{ $companyName }}</p>
                        </td>
                    </tr>
                </table>

                {{-- Footer --}}
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;">
                    <tr>
                        <td style="padding:16px 28px;font-size:12px;line-height:1.5;color:{{ $c['muted'] }};text-align:center;">
                            <p style="margin:0 0 4px;">Email otomatis, mohon tidak dibalas. This is an automated email, please do not reply.</p>
                            <p style="margin:0 0 4px;">Tandai pengirim ini sebagai penting agar slip bulan berikutnya tidak masuk spam.</p>
                            <p style="margin:0;">&copy; {{ date('Y') }} {{ $companyName }}. Developed by Edwin Sirait</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
