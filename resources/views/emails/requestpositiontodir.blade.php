<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Position Request / Permintaan Posisi</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #333333;
            line-height: 1.6;
            background-color: #f4f6f8;
            margin: 0;
            padding: 20px;
        }

        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e0e6eb;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .email-header {
            border-bottom: 2px solid #000000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .email-header h2 {
            color: #2c3e50;
            margin: 0;
            font-size: 20px;
        }

        .email-body p {
            margin: 15px 0;
            font-size: 14px;
        }

        .changes-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .changes-table th,
        .changes-table td {
            border: 1px solid #e0e6eb;
            padding: 10px;
            font-size: 14px;
            text-align: left;
        }

        .changes-table th {
            background-color: #f9fbfc;
            color: #2c3e50;
        }

        .btn {
            display: inline-block;
            background-color: #1abc9c;
            color: #ffffff !important;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color .2s ease;
        }

        .btn:hover {
            background-color: #16a085;
        }

        .email-footer {
            margin-top: 30px;
            font-size: 13px;
            color: #888888;
            border-top: 1px solid #eee;
            padding-top: 15px;
            text-align: center;
        }

        strong {
            color: #2c3e50;
        }

        .note {
            font-size: 12px;
            color: #999999;
            margin-top: 15px;
            text-align: center;
        }

        .divider {
            margin: 40px 0;
            border-top: 2px dashed #ccc;
        }
    </style>
</head>

<body>
    <div class="email-container">
        {{-- ENGLISH VERSION --}}
        <div class="email-header">
            <h2>Position Request</h2>
        </div>
        <div class="email-body">
            <p>To <strong>Directors of PT. Asian Bay Development</strong>,</p>
            <p>
                Manager named <strong>{{ $submission->submitter->employee_name }}</strong> has submitted a new position
                request.
            </p>
            <h3>Position Request Details</h3>
            <ul>
                <li><strong>Company:</strong> {{ $submission->positionRelation->name ?? 'empty' }}</li>
                <li><strong>Department:</strong> {{ $submission->department->department_name ?? 'empty' }}</li>
                <li><strong>Position Request:</strong> {{ $submission->positionRelation->name ?? 'empty' }}</li>
                <li><strong>Location:</strong> {{ $submission->store->name ?? 'empty' }}</li>
                <li><strong>Role Summary:</strong> {!! $submission->role_summary ?? '<em>Empty</em>' !!}</li>
                <li><strong>Key Responsibility:</strong> {!! $submission->key_respon ?? '<em>Empty</em>' !!}</li>
                <li><strong>Qualifications:</strong> {!! $submission->qualifications ?? '<em>Empty</em>' !!}</li>
                <li><strong>Manager's Note:</strong> {{ $submission->notes ?? 'empty notes' }}</li>
                <li><strong>HRD Verifier:</strong> {{ $submission->approver1->employee_name ?? 'empty notes' }}</li>
                <li><strong>Salary Request:</strong> {{ number_format($submission->salary_hr, 0, ',', '.') }} -
                    {{ number_format($submission->salary_hr_end, 0, ',', '.') }}</li>
                <li><strong>HRD Notes:</strong> {{ $submission->notes_hr ?? 'empty notes' }}</li>
            </ul>
            <p>
                Please review this request in the HR system and take the appropriate action (<em>approve</em> or
                <em>reject</em>) according to company policy.
            </p>

            <p style="margin:20px 0;">
                <a href="https://hr.unclejo.xyz" target="_blank" class="btn">Open Dashboard</a>
            </p>

            <p>Thank you for your attention and cooperation.</p>
            <br>
            <p>Best regards,<br>
                <strong>HRX PT. Asian Bay Development</strong>
            </p>

         
            <div class="note">
                This email was sent automatically. Please do not reply.<br>
                For further information, please contact our HR Department via WhatsApp:
                <a href="https://wa.me/6281138310552" style="color:#25D366; text-decoration:none; font-weight:bold;">
                    HR Department
                </a>

                <a href="https://asianbay.co.id" style="color:#242830; text-decoration:none; font-weight:bold;">
                    PT Asian Bay Development
                </a>
            </div>

        </div>
        <div class="divider"></div>
        {{-- INDONESIAN VERSION --}}
        <div class="email-header">
            <h2>Permintaan Posisi</h2>
        </div>

        <div class="email-body">
            <p>Kepada <strong>Departemen HR PT. Asian Bay Development</strong>,</p>
            <p>
                Manajer bernama <strong>{{ $submission->submitter->employee_name }}</strong> telah mengajukan
                permintaan posisi baru.
            </p>

            <h3>Detail Permintaan Posisi</h3>
            <ul>
                <li><strong>Perusahaan:</strong> {{ $submission->positionRelation->name ?? 'Tidak ada' }}</li>
                <li><strong>Departemen:</strong> {{ $submission->department->department_name ?? 'Tidak ada' }}</li>
                <li><strong>Posisi yang Diajukan:</strong> {{ $submission->positionRelation->name ?? 'Tidak ada' }}
                </li>
                <li><strong>Lokasi:</strong> {{ $submission->store->name ?? 'Tidak ada' }}</li>
                <li><strong>Ringkasan Peran:</strong> {!! $submission->role_summary ?? '<em>Tidak ada</em>' !!}</li>
                <li><strong>Tanggung Jawab Utama:</strong> {!! $submission->key_respon ?? '<em>Tidak ada</em>' !!}</li>
                <li><strong>Kualifikasi:</strong> {!! $submission->qualifications ?? '<em>Tidak ada</em>' !!}</li>
                <li><strong>Catatan Manajer:</strong> {{ $submission->notes ?? 'tidak ada catatan' }}</li>
                <li><strong>HRD Verifikator:</strong> {{ $submission->approver1->employee_name ?? 'empty notes' }}</li>
                <li><strong>Permintaan Gaji:</strong> {{ number_format($submission->salary_hr, 0, ',', '.') }} -
                    {{ number_format($submission->salary_hr_end, 0, ',', '.') }}</li>
            </ul>

            <p>
                Mohon tinjau permintaan ini di sistem HR dan lakukan tindakan yang sesuai (<em>setujui</em> atau
                <em>tolak</em>) sesuai dengan kebijakan perusahaan.
            </p>

            <p style="margin:20px 0;">
                <a href="https://hr.unclejo.xyz" target="_blank" class="btn">Buka Dashboard HR</a>
            </p>

            <p>Terima kasih atas perhatian dan kerja samanya.</p>
            <br>
            <p>Hormat kami,<br>
                <strong>HRX PT. Asian Bay Development</strong>
            </p>

            {{-- <div class="note">
                Email ini dikirim secara otomatis. Mohon untuk tidak membalas email ini.<br>
                Untuk informasi lebih lanjut, silakan hubungi Departemen HR melalui WhatsApp:
                <a href="https://wa.me/6281138310552" style="color:#25D366; text-decoration:none; font-weight:bold;">
                    Departemen HR PT Asian Bay Development
                </a>
            </div> --}}
            <div class="note">
                Email ini dikirim secara otomatis. Mohon untuk tidak membalas email ini.<br>
                Untuk informasi lebih lanjut, silakan hubungi Departemen HR melalui WhatsApp:
                <a href="https://wa.me/6281138310552" style="color:#25D366; text-decoration:none; font-weight:bold;">
                    HR Departemen
                </a>
                <a href="https://asianbay.co.id" style="color:#242830; text-decoration:none; font-weight:bold;">
                    PT Asian Bay Development
                </a>
            </div>
        </div>

        <div class="email-footer">
            © {{ date('Y') }} HRX. PT Asian Bay Development — Developed by Edwin Sirait.
        </div>
    </div>
</body>

</html>
