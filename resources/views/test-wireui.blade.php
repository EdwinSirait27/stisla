<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WireUI Test</title>

    {{-- Load script utama WireUI --}}
    @wireUiScripts

    {{-- Ganti Vite ke Laravel Mix --}}
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <script src="{{ mix('js/app.js') }}" defer></script>
</head>
<body class="p-10">
    <x-button label="Klik Aku" primary />
</body>
</html>
