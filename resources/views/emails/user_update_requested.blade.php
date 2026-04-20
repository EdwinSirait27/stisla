<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Employee Data Update Request</title>
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
    </style>
</head>

<body>
    <div class="email-container">
        <div class="email-header">
            <h2>Employee Data Update Request</h2>
        </div>

        <div class="email-body">
            <p>To <strong>HR Department of PT. Asian Bay Developement</strong>,</p>

            <p>
                Employee named <strong>{{ $user->employee->employee_name }}</strong>
                has submitted a request to update their personal data.
            </p>

            <table class="changes-table">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>New Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($changes as $field => $value)
                        <tr>
                            <td>{{ ucfirst(str_replace('_', ' ', $field)) }}</td>
                            <td>{{ $value }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <p>
                Please review this request in the HR system and take the appropriate action
                (<em>approve</em> or <em>reject</em>) according to company policy.
            </p>

            <p style="margin:20px 0;">
                <a href="https://hr.unclejo.xyz" target="_blank" class="btn">
                    Open HR Dashboard
                </a>
            </p>

            <p>Thank you for your attention and cooperation.</p>
            <br>
            <p>Best regards,<br>
                <strong>HRX PT. Asian Bay Developement</strong>
            </p>
            <div class="note">
                This email was sent automatically. Please do not reply.<br>
                For further information, please contact our HR Department via WhatsApp:
                <a href="https://wa.me/6281138310552" style="color:#25D366; text-decoration:none; font-weight:bold;">
                    HR Department Asian Bay Developement
                </a>
            </div>
        </div>

        <div class="email-footer">
            © {{ date('Y') }} HRX. PT Asian Bay Developement Created by Edwin Sirait.
        </div>
    </div>
</body>

</html>
