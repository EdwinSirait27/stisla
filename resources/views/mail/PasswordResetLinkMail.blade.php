{{--
    Email link reset password. Tabel + inline style untuk klien email.
    Variabel: $resetUrl, $employeeName, $username, $companyName, $expiresText, $expireMinutes
--}}
@php
    $c = [
        'page' => '#f3f4f8', 'panel' => '#ffffff', 'rule' => '#e3e5ee', 'ink' => '#1f2433',
        'ink2' => '#4a5163', 'muted' => '#6b7183', 'accent' => '#4a4fc4', 'warn' => '#8a5a00', 'warnSoft' => '#fff6e0',
    ];
    $font = "'Segoe UI', 'Helvetica Neue', Arial, sans-serif";
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light only">
    <title>Reset password akun HRX</title>
</head>

<body style="margin:0;padding:0;background-color:{{ $c['page'] }};font-family:{{ $font }};color:{{ $c['ink'] }};">
    <div style="display:none;max-height:0;overflow:hidden;mso-hide:all;">
        Link reset password akun HRX Anda, berlaku {{ $expireMinutes }} menit dan hanya bisa dipakai sekali.
    </div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:{{ $c['page'] }};">
        <tr>
            <td align="center" style="padding:24px 12px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                    style="max-width:560px;background-color:{{ $c['panel'] }};border:1px solid {{ $c['rule'] }};border-radius:8px;">
                    <tr>
                        <td style="padding:24px 28px 20px;border-bottom:1px solid {{ $c['rule'] }};">
                            <p style="margin:0 0 4px;font-size:13px;color:{{ $c['muted'] }};">{{ $companyName }}</p>
                            <h1 style="margin:0;font-size:22px;line-height:1.25;font-weight:700;color:{{ $c['ink'] }};">Reset password akun HRX</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 28px 8px;font-size:15px;line-height:1.6;color:{{ $c['ink'] }};">
                            <p style="margin:0 0 12px;">Halo <strong>{{ $employeeName }}</strong>,</p>
                            <p style="margin:0;">
                                Admin HRX meminta reset password untuk akun dengan username <strong>{{ $username }}</strong>.
                                Klik tombol di bawah untuk membuat password baru.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 28px 8px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="border-radius:6px;background-color:{{ $c['accent'] }};">
                                        <a href="{{ $resetUrl }}" target="_blank"
                                            style="display:inline-block;padding:12px 24px;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;border-radius:6px;">
                                            Buat password baru
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:12px 28px 8px;font-size:13px;line-height:1.6;color:{{ $c['ink2'] }};">
                            <p style="margin:0 0 6px;">Link ini berlaku sampai <strong>{{ $expiresText }}</strong> ({{ $expireMinutes }} menit) dan hanya bisa dipakai <strong>satu kali</strong>.</p>
                            <p style="margin:0;">Kalau tombol tidak bisa diklik, salin alamat ini ke browser:</p>
                            <p style="margin:4px 0 0;word-break:break-all;"><a href="{{ $resetUrl }}" style="color:{{ $c['accent'] }};">{{ $resetUrl }}</a></p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 28px 24px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:{{ $c['warnSoft'] }};border-radius:6px;">
                                <tr>
                                    <td style="padding:12px 16px;font-size:13px;line-height:1.5;color:{{ $c['warn'] }};">
                                        Tidak merasa meminta reset password? Abaikan email ini, password lama Anda tetap berlaku, lalu kabari Departemen HR.
                                        Jangan teruskan email ini ke siapa pun.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px;">
                    <tr>
                        <td style="padding:16px 28px;font-size:12px;line-height:1.5;color:{{ $c['muted'] }};text-align:center;">
                            Email otomatis, mohon tidak dibalas.<br>&copy; {{ date('Y') }} {{ $companyName }}. Developed by Edwin Sirait
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
