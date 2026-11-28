@php
    $waiting = $waiting ?? null;
    $waitingSince = $waitingSince ?? null;
    $lead = [
        'approval_needed' => 'Ada berita acara yang menunggu persetujuan Anda.',
        'returned'        => 'Berita acara Anda dikembalikan untuk direvisi.',
        'approved'        => 'Berita acara Anda sudah disetujui semua approver dan sah.',
        'cc'              => 'Anda menerima tembusan berita acara yang sudah disetujui.',
        'reminder'        => 'Pengingat: berita acara ini masih menunggu persetujuan' . ($waiting ? ' ' . $waiting : '') . ($waitingSince ? ' sejak ' . \Carbon\Carbon::parse($waitingSince)->locale('id')->translatedFormat('d F Y') : '') . '.',
        'shared'          => 'Berikut berita acara yang dibagikan kepada Anda.',
    ][$event] ?? '';
    $action = [
        'approval_needed' => 'Buka dan setujui',
        'returned'        => 'Buka dan revisi',
    ][$event] ?? 'Buka berita acara';
@endphp
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>{{ $type }}</title></head>
<body style="margin:0;padding:24px;background:#f4f5f7;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:8px;border:1px solid #e5e7eb;">
        <tr><td style="padding:24px 24px 8px;">
            <p style="margin:0 0 12px;font-size:15px;">Halo {{ $name }},</p>
            <p style="margin:0 0 16px;font-size:15px;line-height:1.5;">{{ $lead }}</p>
            <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;font-size:14px;line-height:1.5;border-collapse:collapse;">
                <tr><td style="padding:4px 0;color:#6b7280;width:110px;">Jenis</td><td style="padding:4px 0;">{{ $type }}</td></tr>
                <tr><td style="padding:4px 0;color:#6b7280;">Nomor</td><td style="padding:4px 0;">{{ $document->document_number }}</td></tr>
                <tr><td style="padding:4px 0;color:#6b7280;">Perihal</td><td style="padding:4px 0;">{{ $subject }}</td></tr>
                <tr><td style="padding:4px 0;color:#6b7280;">Diajukan oleh</td><td style="padding:4px 0;">{{ $document->employee?->employee_name }}</td></tr>
                @if ($note)
                    <tr><td style="padding:4px 0;color:#6b7280;vertical-align:top;">Catatan</td><td style="padding:4px 0;">{{ $note }}</td></tr>
                @endif
            </table>
        </td></tr>
        <tr><td style="padding:16px 24px 24px;">
            <a href="{{ $url }}" style="display:inline-block;padding:10px 18px;background:#4f46e5;color:#ffffff;text-decoration:none;border-radius:6px;font-size:14px;font-weight:bold;">{{ $action }}</a>
            <p style="margin:16px 0 0;font-size:12px;color:#6b7280;">Email ini dikirim otomatis oleh HRX. Isi berita acara hanya bisa dibuka setelah masuk ke HRX.</p>
        </td></tr>
    </table>
</body>
</html>
