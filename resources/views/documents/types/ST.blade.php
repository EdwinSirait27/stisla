<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $document->document_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #000;
            background: #fff;
        }

        .page {
            padding: 12mm 15mm 12mm 15mm;
            position: relative;
            overflow: hidden;
            min-height: 270mm;
        }

        /* ── Watermark logo ── */
        .watermark-logo {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.07;
            pointer-events: none;
            z-index: 0;
        }

        .watermark-logo img {
            max-height: 350px;
            max-width: 380px;
            object-fit: contain;
        }

        .page>*:not(.watermark-logo) {
            position: relative;
            z-index: 1;
        }

        /* ── Kop Surat ── */
        .kop {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .kop-inner {
            width: 100%;
            border-collapse: collapse;
        }

        .kop-logo {
            width: 75px;
            text-align: center;
            vertical-align: middle;
        }

        .kop-logo img {
            max-height: 80px;
            max-width: 110px;
            object-fit: contain;
            display: block;
        }

        .kop-text {
            text-align: center;
            vertical-align: middle;
            padding: 0 8px;
        }

        .kop-company {
            font-size: 15pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .kop-address {
            font-size: 8.5pt;
            color: #222;
            margin-top: 3px;
            line-height: 1.4;
        }

        .kop-contact {
            font-size: 11px;
            margin-top: 2px;
        }

        /* ── Judul ── */
        .doc-title {
            text-align: center;
            margin: 14px 0 4px 0;
        }

        .doc-title-text {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
        }

        .doc-number {
            text-align: center;
            font-size: 10.5pt;
            margin-bottom: 14px;
        }

        /* ── Body teks ── */
        .body-text {
            font-size: 10.5pt;
            line-height: 1.6;
            margin-bottom: 10px;
            text-align: justify;
        }

        /* ── Data table ── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 12px 30px;
            font-size: 10.5pt;
        }

        .data-table td {
            padding: 2px 0;
            vertical-align: top;
            line-height: 1.5;
        }

        .data-table .col-label {
            width: 160px;
        }

        .data-table .col-sep {
            width: 20px;
        }

        /* ── Perpindahan table ── */
        .move-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 14px 0;
            font-size: 10.5pt;
        }

        .move-table th,
        .move-table td {
            border: 1px solid #000;
            padding: 5px 8px;
            vertical-align: top;
        }

        .move-table th {
            background: #f0f0f0;
            text-align: center;
            font-size: 10pt;
        }

        /* ── Tanda Tangan ── */
        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .ttd-table td {
            text-align: center;
            vertical-align: top;
            padding: 0 2px;
            width: 33%;
        }

        .ttd-role {
            font-size: 9.5pt;
            margin-bottom: 55px;
            font-style: italic;
        }

        .ttd-signature {
            margin-bottom: 4px;
            height: 60px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }

        .ttd-signature img {
            max-height: 60px;
            object-fit: contain;
        }

        .ttd-line {
            border-top: 1px solid #000;
            padding-top: 4px;
            margin: 0 10px;
        }

        .ttd-name {
            font-weight: bold;
            font-size: 10pt;
        }

        .ttd-position {
            font-size: 9.5pt;
            color: #333;
            margin-top: 2px;
        }

        .doc-footer {
            position: fixed;
            bottom: 20px;
            left: 40px;
            right: 40px;
            text-align: center;
            font-size: 8pt;
            color: #555;
            border-top: 1px solid #ccc;
            padding-top: 5px;
            line-height: 1.4;
        }
    </style>
</head>

<body>
    @php
        $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $formatTgl = function ($dateStr) use ($bulan) {
            if (!$dateStr) {
                return '-';
            }
            $d = \Carbon\Carbon::parse($dateStr);
            return $d->day . ' ' . $bulan[$d->month] . ' ' . $d->year;
        };

        $typeLabels = [
            'department' => 'Departemen',
            'store'      => 'Store / Lokasi',
            'position'   => 'Posisi / Jabatan',
        ];

        $assignments = $document->assignments->whereNull('reverted_at');
        if ($assignments->isEmpty()) {
            // Already expired (and reverted) — show the full set of moves
            // (a type like "store" can have more than one row) from the
            // last revert batch, instead of collapsing to one per type.
            $lastRevertedAt = $document->assignments->max('reverted_at');
            $assignments = $document->assignments->filter(
                fn($assignment) => $assignment->reverted_at == $lastRevertedAt
            );
        }
    @endphp

    <div class="page">

        @if ($company->foto)
            <div class="watermark-logo">
                <img src="{{ public_path('storage/' . $company->foto) }}" alt="Watermark">
            </div>
        @endif

        {{-- ── Kop Surat ── --}}
        <div class="kop">
            <table class="kop-inner">
                <tr>
                    <td class="kop-logo">
                        @if ($company->foto)
                            <img src="{{ public_path('storage/' . $company->foto) }}" alt="Logo">
                        @endif
                    </td>
                    <td class="kop-text">
                        <div class="kop-company">{{ $company->name }}</div>
                        <div class="kop-address">{{ $company->address }}</div>
                        @if ($company->email)
                            <div class="kop-contact">
                                Email : {{ $company->email }} Website : {{ $company->website }}
                            </div>
                        @endif
                    </td>
                    <td style="width:75px;"></td>
                </tr>
            </table>
        </div>

        {{-- ── Judul ── --}}
        <div class="doc-title">
            <span class="doc-title-text">Surat Tugas</span>
        </div>
        <div class="doc-number">Nomor: {{ $document->document_number }}</div>

        <p class="body-text">
            Yang bertanda tangan di bawah ini menugaskan karyawan dengan identitas sebagai berikut:
        </p>
        <table class="data-table">
            <tr>
                <td class="col-label">Nama</td>
                <td class="col-sep">:</td>
                <td><strong>{{ $employee->employee_name }}</strong></td>
            </tr>
            <tr>
                <td class="col-label">Status Karyawan</td>
                <td class="col-sep">:</td>
                <td>{{ $employee->status_employee ?? '-' }}</td>
            </tr>
            <tr>
                <td class="col-label">Masa Berlaku Tugas</td>
                <td class="col-sep">:</td>
                <td>{{ $formatTgl($document->issued_date) }} s/d {{ $formatTgl($document->expired_date) }}</td>
            </tr>
        </table>

        <p class="body-text">
            Untuk dipindahtugaskan sementara sebagai berikut, dan wajib kembali ke penempatan semula setelah
            masa tugas berakhir:
        </p>

        <table class="move-table">
            <tr>
                <th>Jenis Penugasan</th>
                <th>Dari</th>
                <th>Ke</th>
            </tr>
            @forelse ($assignments as $assignment)
                <tr>
                    <td>{{ $typeLabels[$assignment->type] ?? ucfirst($assignment->type) }}</td>
                    <td>{{ $assignment->previous_name ?? '-' }}</td>
                    <td>{{ $assignment->new_name }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align:center;">-</td>
                </tr>
            @endforelse
        </table>

        <p class="body-text">
            Demikian Surat Tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab. Atas perhatian dan
            kerja samanya, kami ucapkan terima kasih.
        </p>

        <table style="width: 100%; margin-top: 20px; margin-bottom: 90px; font-size: 10.5pt;">
            <tr>
                <td style="width: 60%; vertical-align: top;">
                    <br>
                    Ditetapkan di &nbsp;:
                    {{ $company->city ?? 'Denpasar' }}
                    <br>
                    Pada tanggal &nbsp;&nbsp;:
                    {{ $formatTgl($document->issued_date) }}
                </td>
                <td style="width: 40%; text-align: center; vertical-align: bottom;">
                    @if ($signatureData)
                        <img src="{{ $signatureData }}" alt="Signature"
                            style="height: 70px; width: auto; display: block; margin: 0 auto 4px 50px;">
                    @else
                        <div style="height: 70px;"></div>
                    @endif
                    <div style="padding-top: 4px; margin: 0 10px;">
                        <strong>{{ $issued->employee_name ?? '-' }}</strong><br>
                        <span style="font-size: 9.5pt;">
                            {{ $issued->position->first()->name ?? '-' }}
                        </span>
                    </div>
                </td>
            </tr>
        </table>

        {{-- ── Footer ── --}}
        <div class="doc-footer">
            Dokumen ini diterbitkan secara resmi oleh {{ $company->name }} &nbsp;|&nbsp;
            Nomor: {{ $document->document_number }} &nbsp;|&nbsp;
            Tanggal: {{ $formatTgl($document->issued_date) }}
        </div>
    </div>
</body>

</html>
