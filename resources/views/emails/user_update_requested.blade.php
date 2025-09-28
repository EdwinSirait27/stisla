<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ ucfirst($field) }} Update Request</title>
</head>
<body>
    <h2>Hello HR Department PT. Mahendradata Jaya Mandiri,</h2>

    <p>
        User <strong>{{ $user->employee->employee_name }}</strong> 
        telah mengajukan perubahan pada <strong>{{ $field }}</strong>.
    </p>

    <p>
        Nilai baru yang diajukan: <strong>{{ $newValue }}</strong>
    </p>

    <p>
        Silakan login ke dashboard HR untuk approve/reject.
    </p>

    <p>
        <a href="https://hr.unclejo.xyz" target="_blank">Dashboard System</a>
    </p>

    <p>Terima kasih.</p>
</body>
</html>
