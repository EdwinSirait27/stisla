{{-- <!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $skLetter->sk_number }}</title>
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

        .sk-page {
            padding: 12mm 15mm 12mm 15mm;
            position: relative;
            overflow: hidden;
            page-break-after: always;
            page-break-inside: avoid;
            min-height: 270mm;
        }

        .sk-page:last-child {
            page-break-after: avoid;
        }


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

        .watermark-confidential {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .watermark-confidential span {
            position: absolute;
            font-size: 16pt;
            font-weight: bold;
            color: #888;
            opacity: 0.15;
            transform: rotate(-35deg);
            white-space: nowrap;
            letter-spacing: 3px;
            text-transform: uppercase;
            font-family: 'Times New Roman', Times, serif;
        }

        /* Konten di atas semua watermark */
        .sk-page>*:not(.watermark-logo):not(.watermark-confidential) {
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
        .sk-title {
            text-align: center;
            margin: 14px 0 4px 0;
        }

        .sk-title-text {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
        }

        .sk-subtitle {
            font-size: 11pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 4px;
        }

        .sk-number {
            text-align: center;
            font-size: 10.5pt;
            margin-bottom: 12px;
        }

        /* ── Divider ── */
        .divider-single {
            border: none;
            border-top: 1px solid #000;
            margin: 8px 0;
        }

        /* ── Section label ── */
        .sk-label {
            font-weight: bold;
            font-size: 10.5pt;
            margin: 10px 0 6px 0;
            display: block;
        }

        /* ── List menimbang/mengingat/keputusan ── */
        .sk-list {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .sk-list td {
            vertical-align: top;
            padding: 2px 0;
            font-size: 10.5pt;
            line-height: 1.5;
        }

        .sk-list td.num {
            width: 22px;
            white-space: nowrap;
        }

        /* ── Menetapkan ── */
        .sk-menetapkan {
            font-size: 10.5pt;
            line-height: 1.6;
            margin: 4px 0 8px 0;
        }

        /* ── Data karyawan ── */
        .emp-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 10px 0;
        }

        .emp-table td {
            padding: 3px 0;
            vertical-align: top;
            font-size: 10.5pt;
            line-height: 1.5;
        }

        .emp-table .col-label {
            width: 42%;
        }

        .emp-table .col-sep {
            width: 4%;
            text-align: center;
        }

        .emp-table .col-value {
            width: 54%;
        }

        /* ── Tempat & tanggal ── */
        .sk-place {
            font-size: 10.5pt;
            line-height: 1.8;
            margin-top: 12px;
        }

        /* ── Tanda Tangan ── */
        .ttd-wrap {
            margin-top: 20px;
            width: 100%;
        }

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
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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

        .ttd-date {
            font-size: 9.5pt;
            color: #333;
            margin-top: 2px;
        }

        .ttd-pending {
            font-size: 9pt;
            color: #999;
            font-style: italic;
            margin-top: 2px;
        }

        /* ── Footer ── */
        .sk-footer {
            margin-top: 18px;
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
        $formatTgl = function ($date) use ($bulan) {
            if (!$date) {
                return '-';
            }
            return $date->day . ' ' . $bulan[$date->month] . ' ' . $date->year;
        };
        $formatTglStr = function ($dateStr) use ($bulan) {
            if (!$dateStr) {
                return '-';
            }
            $d = \Carbon\Carbon::parse($dateStr);
            return $d->day . ' ' . $bulan[$d->month] . ' ' . $d->year;
        };
    @endphp

    @foreach ($skLetter->employees as $employee)
        @php $pivot = $employee->pivot; @endphp

        <div class="sk-page">

           
            @if ($skLetter->company->foto)
                <div class="watermark-logo">
                    <img src="{{ public_path('storage/' . $skLetter->company->foto) }}" alt="Watermark Logo">
                </div>
            @endif

            <div class="watermark-confidential">
                @php
                    $positions = [
                        // Baris 1
                        ['top' => '8%', 'left' => '5%'],
                        ['top' => '8%', 'left' => '50%'],
                        // Baris 2
                        ['top' => '20%', 'left' => '27%'],
                        ['top' => '20%', 'left' => '72%'],
                        // Baris 3
                        ['top' => '32%', 'left' => '5%'],
                        ['top' => '32%', 'left' => '50%'],
                        // Baris 4
                        ['top' => '44%', 'left' => '27%'],
                        ['top' => '44%', 'left' => '72%'],
                        // Baris 5
                        ['top' => '56%', 'left' => '5%'],
                        ['top' => '56%', 'left' => '50%'],
                        // Baris 6
                        ['top' => '68%', 'left' => '27%'],
                        ['top' => '68%', 'left' => '72%'],
                        // Baris 7
                        ['top' => '80%', 'left' => '5%'],
                        ['top' => '80%', 'left' => '50%'],
                        // Baris 8
                        ['top' => '92%', 'left' => '27%'],
                        ['top' => '92%', 'left' => '72%'],
                    ];
                @endphp
                @foreach ($positions as $pos)
                    <span style="top: {{ $pos['top'] }}; left: {{ $pos['left'] }};">CONFIDENTIAL</span>
                @endforeach
            </div>

            <div class="kop">
                <table class="kop-inner">
                    <tr>
                        <td class="kop-logo">
                            @if ($skLetter->company->foto)
                                <img src="{{ public_path('storage/' . $skLetter->company->foto) }}" alt="Logo">
                            @endif
                        </td>
                        <td class="kop-text">
                            <div class="kop-company">{{ $skLetter->company->name }}</div>
                            <div class="kop-address">{{ $skLetter->company->address }}</div>
                            @if ($skLetter->company->email)
                                <div class="kop-contact">
                                    {{ $skLetter->company->phone }} | {{ $skLetter->company->email }} |
                                    {{ $skLetter->company->website }}
                                </div>
                            @endif
                        </td>
                        <td style="width:75px;"></td>
                    </tr>
                </table>
            </div>

            <div class="sk-title">
                <span class="sk-title-text">Surat Keputusan</span>
            </div>
            @if ($skLetter->title)
                <div class="sk-subtitle">{{ $skLetter->title }}</div>
            @endif
            <div class="sk-number">Nomor: {{ $skLetter->sk_number }}</div>

            @if ($skLetter->menimbang->count() > 0)
                <div class="sk-label">Menimbang :</div>
                <table class="sk-list">
                    @foreach ($skLetter->menimbang as $i => $item)
                        <tr>
                            <td class="num">{{ $i + 1 }}.</td>
                            <td>{{ $item->content_menimbang }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif

            @if ($skLetter->mengingat->count() > 0)
                <div class="sk-label">Mengingat :</div>
                <table class="sk-list">
                    @foreach ($skLetter->mengingat as $i => $item)
                        <tr>
                            <td class="num">{{ $i + 1 }}.</td>
                            <td>{{ $item->content_mengingat }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif

            <div class="sk-label">Menetapkan :</div>
            @if ($skLetter->menetapkan_text)
                <div class="sk-menetapkan">{!! $skLetter->menetapkan_text !!}</div>
            @endif

            <div class="sk-label">Kepada :</div>
            <table class="emp-table">
                <tr>
                    <td class="col-label">Nama Karyawan</td>
                    <td class="col-sep">:</td>
                    <td class="col-value"><strong>{{ $employee->employee_name }}</strong></td>
                </tr>
                <tr>
                    <td class="col-label">NIK / ID Karyawan</td>
                    <td class="col-sep">:</td>
                    <td class="col-value">{{ $employee->employee_pengenal ?? ($employee->employee_code ?? '-') }}</td>
                </tr>
                @if ($pivot->previous_structure_id)
                    @php
                        $prevStructure = \App\Models\Structuresnew::with('submissionposition.positionRelation')->find(
                            $pivot->previous_structure_id,
                        );
                    @endphp
                    <tr>
                        <td class="col-label">Jabatan Sebelumnya</td>
                        <td class="col-sep">:</td>
                        <td class="col-value">
                            {{ $prevStructure?->submissionposition?->positionRelation?->name ?? '-' }}</td>
                    </tr>
                @endif
                @if ($pivot->new_structure_id)
                    @php
                        $newStructure = \App\Models\Structuresnew::with('submissionposition.positionRelation')->find(
                            $pivot->new_structure_id,
                        );
                    @endphp
                    <tr>
                        <td class="col-label">Jabatan Baru</td>
                        <td class="col-sep">:</td>
                        <td class="col-value">
                            <strong>{{ $newStructure?->submissionposition?->positionRelation?->name ?? '-' }}</strong>
                        </td>
                    </tr>
                @endif
                @if ($pivot->basic_salary)
                    <tr>
                        <td class="col-label">Gaji Pokok</td>
                        <td class="col-sep">:</td>
                        <td class="col-value">Rp {{ number_format($pivot->basic_salary, 0, ',', '.') }}</td>
                    </tr>
                @endif
                @if ($pivot->positional_allowance)
                    <tr>
                        <td class="col-label">Tunjangan Jabatan</td>
                        <td class="col-sep">:</td>
                        <td class="col-value">Rp {{ number_format($pivot->positional_allowance, 0, ',', '.') }}</td>
                    </tr>
                @endif
                @if ($pivot->daily_rate)
                    <tr>
                        <td class="col-label">Daily Rate</td>
                        <td class="col-sep">:</td>
                        <td class="col-value">Rp {{ number_format($pivot->daily_rate, 0, ',', '.') }}</td>
                    </tr>
                @endif
                <tr>
                    <td class="col-label">Tanggal Efektif</td>
                    <td class="col-sep">:</td>
                    <td class="col-value">{{ $formatTgl($skLetter->effective_date) }}</td>
                </tr>
                @if ($skLetter->inactive_date)
                    <tr>
                        <td class="col-label">Berlaku Sampai</td>
                        <td class="col-sep">:</td>
                        <td class="col-value">{{ $formatTgl($skLetter->inactive_date) }}</td>
                    </tr>
                @endif
                @if ($pivot->notes)
                    <tr>
                        <td class="col-label">Keterangan</td>
                        <td class="col-sep">:</td>
                        <td class="col-value">{{ $pivot->notes }}</td>
                    </tr>
                @endif
            </table>

            @if ($skLetter->keputusan->count() > 0)
                <div class="sk-label">Memutuskan :</div>
                <table class="sk-list">
                    @foreach ($skLetter->keputusan as $i => $item)
                        <tr>
                            <td class="num">{{ $i + 1 }}.</td>
                            <td>{{ $item->content_keputusan }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif

            <div class="sk-place">
                Ditetapkan di &nbsp;: {{ $skLetter->location ?? 'Denpasar' }}<br>
                Pada tanggal &nbsp;&nbsp;: {{ $formatTgl($skLetter->effective_date) }}
            </div>

            <div class="ttd-wrap">
                <table class="ttd-table">
                    <tr>
                        <td>
                            <div class="ttd-role">{{ $skLetter->approver1?->position->name }}</div>
                            @if ($skLetter->approver_1_at && $skLetter->approver1?->signature)
                                <div>
                                    <img src="{{ asset('storage/' . $skLetter->approver1->signature) }}"
                                        alt="Signature" style="max-height:80px; object-fit:contain;">
                                </div>
                            @endif
                            <div class="ttd-line">
                                <div class="ttd-name">
                                    {{ $skLetter->approver1?->employee_name ?? '( _________________ )' }}</div>
                                @if ($skLetter->approver_1_at)
                                    <div class="ttd-date">{{ $formatTglStr($skLetter->approver_1_at) }}</div>
                                @else
                                    <div class="ttd-pending">Belum disetujui</div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="ttd-role">Director</div>
                            <div class="ttd-line">
                                <div class="ttd-name">
                                    {{ $skLetter->approver2?->employee_name ?? '( _________________ )' }}</div>
                                @if ($skLetter->approver_2_at)
                                    <div class="ttd-date">{{ $formatTglStr($skLetter->approver_2_at) }}</div>
                                @else
                                    <div class="ttd-pending">Belum disetujui</div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="ttd-role">Managing Director</div>
                            <div class="ttd-line">
                                <div class="ttd-name">
                                    {{ $skLetter->approver3?->employee_name ?? '( _________________ )' }}</div>
                                @if ($skLetter->approver_3_at)
                                    <div class="ttd-date">{{ $formatTglStr($skLetter->approver_3_at) }}</div>
                                @else
                                    <div class="ttd-pending">Belum disetujui</div>
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="sk-footer">
                Dokumen ini diterbitkan secara resmi oleh {{ $skLetter->company->name }} &nbsp;|&nbsp;
                SK Nomor: {{ $skLetter->sk_number }} &nbsp;|&nbsp;
            </div>

        </div>
    @endforeach
</body>

</html> --}}
{{-- <!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $skLetter->sk_number }}</title>
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

        /* ── Setiap halaman fisik (page break) ── */
        .sk-physical-page {
            padding: 12mm 15mm 12mm 15mm;
            position: relative;
            overflow: hidden;
            page-break-after: always;
            page-break-inside: avoid;
            min-height: 270mm;
        }

        .sk-physical-page:last-child {
            page-break-after: avoid;
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

        /* ── Watermark CONFIDENTIAL ── */
        .watermark-confidential {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .watermark-confidential span {
            position: absolute;
            font-size: 16pt;
            font-weight: bold;
            color: #888;
            opacity: 0.15;
            transform: rotate(-35deg);
            white-space: nowrap;
            letter-spacing: 3px;
            text-transform: uppercase;
            font-family: 'Times New Roman', Times, serif;
        }

        /* Konten di atas semua watermark */
        .sk-physical-page > *:not(.watermark-logo):not(.watermark-confidential) {
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

        /* ── Kop lanjutan (halaman 2+) — lebih ringkas ── */
        .kop-continuation {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }

        .kop-continuation-inner {
            width: 100%;
            border-collapse: collapse;
        }

        .kop-continuation .kop-logo img {
            max-height: 55px;
            max-width: 80px;
        }

        .kop-continuation .kop-company {
            font-size: 13pt;
        }

        .kop-continuation .kop-address {
            font-size: 8pt;
        }

        /* ── Label lanjutan ── */
        .continuation-label {
            font-size: 9.5pt;
            font-style: italic;
            color: #444;
            text-align: right;
            margin-bottom: 6px;
        }

        /* ── Page number ── */
        .page-number {
            font-size: 9pt;
            color: #555;
            text-align: center;
            margin-bottom: 6px;
        }

        /* ── Judul ── */
        .sk-title {
            text-align: center;
            margin: 14px 0 4px 0;
        }

        .sk-title-text {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
        }

        .sk-subtitle {
            font-size: 11pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 4px;
        }

        .sk-number {
            text-align: center;
            font-size: 10.5pt;
            margin-bottom: 12px;
        }

        /* ── Divider ── */
        .divider-single {
            border: none;
            border-top: 1px solid #000;
            margin: 8px 0;
        }

        /* ── Section label ── */
        .sk-label {
            font-weight: bold;
            font-size: 10.5pt;
            margin: 10px 0 6px 0;
            display: block;
        }

        /* ── List menimbang/mengingat/keputusan ── */
        .sk-list {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .sk-list td {
            vertical-align: top;
            padding: 2px 0;
            font-size: 10.5pt;
            line-height: 1.5;
        }

        .sk-list td.num {
            width: 22px;
            white-space: nowrap;
        }

        /* ── Menetapkan ── */
        .sk-menetapkan {
            font-size: 10.5pt;
            line-height: 1.6;
            margin: 4px 0 8px 0;
        }

        /* ── Data karyawan ── */
        .emp-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 10px 0;
        }

        .emp-table td {
            padding: 3px 0;
            vertical-align: top;
            font-size: 10.5pt;
            line-height: 1.5;
        }

        .emp-table .col-label {
            width: 42%;
        }

        .emp-table .col-sep {
            width: 4%;
            text-align: center;
        }

        .emp-table .col-value {
            width: 54%;
        }

        /* ── Tempat & tanggal ── */
        .sk-place {
            font-size: 10.5pt;
            line-height: 1.8;
            margin-top: 12px;
        }

        /* ── Tanda Tangan ── */
        .ttd-wrap {
            margin-top: 20px;
            width: 100%;
        }

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
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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

        .ttd-date {
            font-size: 9.5pt;
            color: #333;
            margin-top: 2px;
        }

        .ttd-pending {
            font-size: 9pt;
            color: #999;
            font-style: italic;
            margin-top: 2px;
        }

        /* ── Footer ── */
        .sk-footer {
            margin-top: 18px;
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
            1  => 'Januari',  2  => 'Februari', 3  => 'Maret',
            4  => 'April',    5  => 'Mei',       6  => 'Juni',
            7  => 'Juli',     8  => 'Agustus',   9  => 'September',
            10 => 'Oktober',  11 => 'November',  12 => 'Desember',
        ];
        $formatTgl = function ($date) use ($bulan) {
            if (!$date) return '-';
            return $date->day . ' ' . $bulan[$date->month] . ' ' . $date->year;
        };
        $formatTglStr = function ($dateStr) use ($bulan) {
            if (!$dateStr) return '-';
            $d = \Carbon\Carbon::parse($dateStr);
            return $d->day . ' ' . $bulan[$d->month] . ' ' . $d->year;
        };

        /* ── Helper: render watermark di setiap halaman fisik ── */
        $watermarkPositions = [
            ['top' => '8%',  'left' => '5%'],
            ['top' => '8%',  'left' => '50%'],
            ['top' => '20%', 'left' => '27%'],
            ['top' => '20%', 'left' => '72%'],
            ['top' => '32%', 'left' => '5%'],
            ['top' => '32%', 'left' => '50%'],
            ['top' => '44%', 'left' => '27%'],
            ['top' => '44%', 'left' => '72%'],
            ['top' => '56%', 'left' => '5%'],
            ['top' => '56%', 'left' => '50%'],
            ['top' => '68%', 'left' => '27%'],
            ['top' => '68%', 'left' => '72%'],
            ['top' => '80%', 'left' => '5%'],
            ['top' => '80%', 'left' => '50%'],
            ['top' => '92%', 'left' => '27%'],
            ['top' => '92%', 'left' => '72%'],
        ];

        /* Estimasi tinggi konten agar tahu kapan perlu page break.
           Pendekatan: hitung baris, setiap baris ~6mm, threshold ~220mm tersisa setelah kop. */
        $PAGE_HEIGHT_MM   = 270;
        $KOP_HEIGHT_MM    = 32;   // kop penuh halaman pertama
        $KOP_CONT_MM      = 22;   // kop ringkas halaman lanjutan
        $FOOTER_MM        = 14;
        $TTD_MM           = 55;
        $PLACE_MM         = 18;
        $LINE_MM          = 6;    // tinggi rata-rata satu baris teks
    @endphp

    @foreach ($skLetter->employees as $empIdx => $employee)
        @php
            $pivot = $employee->pivot;

            /* ─── Kumpulkan semua blok konten untuk SK ini ─── */
            $blocks = [];

            /* JUDUL (selalu di halaman pertama) */
            $blocks[] = ['type' => 'title'];

            /* MENIMBANG */
            if ($skLetter->menimbang->count() > 0) {
                $blocks[] = ['type' => 'label',     'text' => 'Menimbang :'];
                foreach ($skLetter->menimbang as $i => $item) {
                    $blocks[] = ['type' => 'list_item', 'num' => ($i+1).'.', 'text' => $item->content_menimbang];
                }
            }

            /* MENGINGAT */
            if ($skLetter->mengingat->count() > 0) {
                $blocks[] = ['type' => 'label',     'text' => 'Mengingat :'];
                foreach ($skLetter->mengingat as $i => $item) {
                    $blocks[] = ['type' => 'list_item', 'num' => ($i+1).'.', 'text' => $item->content_mengingat];
                }
            }

            /* MENETAPKAN */
            $blocks[] = ['type' => 'label', 'text' => 'Menetapkan :'];
            if ($skLetter->menetapkan_text) {
                $blocks[] = ['type' => 'menetapkan', 'text' => $skLetter->menetapkan_text];
            }

            /* KEPADA – data karyawan */
            $blocks[] = ['type' => 'label', 'text' => 'Kepada :'];
            $blocks[] = ['type' => 'emp_table', 'employee' => $employee, 'pivot' => $pivot];

            /* MEMUTUSKAN */
            if ($skLetter->keputusan->count() > 0) {
                $blocks[] = ['type' => 'label',     'text' => 'Memutuskan :'];
                foreach ($skLetter->keputusan as $i => $item) {
                    $blocks[] = ['type' => 'list_item', 'num' => ($i+1).'.', 'text' => $item->content_keputusan];
                }
            }

            /* TEMPAT & TTD – selalu satu kesatuan, ditaruh di akhir */
            $blocks[] = ['type' => 'place_ttd'];

            /* ─── Hitung distribusi blok ke halaman ─── */
            /* Estimasi tinggi tiap blok dalam mm */
            $estimateHeight = function ($block) use ($LINE_MM) {
                switch ($block['type']) {
                    case 'title':      return 28;   // judul + nomor SK
                    case 'label':      return 8;
                    case 'list_item':  return max(1, ceil(mb_strlen($block['text']) / 90)) * $LINE_MM + 2;
                    case 'menetapkan': return max(1, ceil(mb_strlen(strip_tags($block['text'])) / 90)) * $LINE_MM + 4;
                    case 'emp_table':  return 60;   // estimasi tabel karyawan
                    case 'place_ttd':  return 75;   // tempat + ttd + footer
                    default:           return $LINE_MM;
                }
            };

            /* Distribusikan blok ke halaman */
            $pages        = [];
            $pageIdx      = 0;
            $usedMm       = $KOP_HEIGHT_MM;  // halaman pertama: kop penuh

            foreach ($blocks as $block) {
                $h = $estimateHeight($block);
                $available = $PAGE_HEIGHT_MM - $usedMm - $FOOTER_MM;

                /* Jika tidak cukup, mulai halaman baru */
                if ($usedMm > $KOP_HEIGHT_MM && $h > $available) {
                    $pageIdx++;
                    $usedMm = $KOP_CONT_MM;
                }
                $pages[$pageIdx][] = $block;
                $usedMm += $h;
            }

            $totalPages = count($pages);
        @endphp

        @foreach ($pages as $pgNum => $pageBlocks)
            @php $isFirstPage = ($pgNum === 0); @endphp

            <div class="sk-physical-page">

                @if ($skLetter->company->foto)
                    <div class="watermark-logo">
                        <img src="{{ public_path('storage/' . $skLetter->company->foto) }}" alt="Watermark Logo">
                    </div>
                @endif

                <div class="watermark-confidential">
                    @foreach ($watermarkPositions as $pos)
                        <span style="top: {{ $pos['top'] }}; left: {{ $pos['left'] }};">CONFIDENTIAL</span>
                    @endforeach
                </div>

                @if ($isFirstPage)
                    <div class="kop">
                        <table class="kop-inner">
                            <tr>
                                <td class="kop-logo">
                                    @if ($skLetter->company->foto)
                                        <img src="{{ public_path('storage/' . $skLetter->company->foto) }}" alt="Logo">
                                    @endif
                                </td>
                                <td class="kop-text">
                                    <div class="kop-company">{{ $skLetter->company->name }}</div>
                                    <div class="kop-address">{{ $skLetter->company->address }}</div>
                                    @if ($skLetter->company->email)
                                        <div class="kop-contact">
                                            {{ $skLetter->company->phone }} | {{ $skLetter->company->email }} |
                                            {{ $skLetter->company->website }}
                                        </div>
                                    @endif
                                </td>
                                <td style="width:75px;"></td>
                            </tr>
                        </table>
                    </div>
                @else
                    <div class="kop kop-continuation">
                        <table class="kop-inner kop-continuation-inner">
                            <tr>
                                <td class="kop-logo">
                                    @if ($skLetter->company->foto)
                                        <img src="{{ public_path('storage/' . $skLetter->company->foto) }}" alt="Logo" style="max-height:55px;max-width:80px;">
                                    @endif
                                </td>
                                <td class="kop-text">
                                    <div class="kop-company" style="font-size:13pt;">{{ $skLetter->company->name }}</div>
                                    <div class="kop-address" style="font-size:8pt;">{{ $skLetter->company->address }}</div>
                                </td>
                                <td style="width:75px;"></td>
                            </tr>
                        </table>
                    </div>

                    <div class="continuation-label">
                        Lanjutan — SK Nomor: {{ $skLetter->sk_number }}
                        @if ($skLetter->title) &nbsp;|&nbsp; {{ $skLetter->title }} @endif
                    </div>
                @endif

                @if ($totalPages > 1)
                    <div class="page-number">
                        Halaman {{ $pgNum + 1 }} dari {{ $totalPages }}
                    </div>
                @endif

                @foreach ($pageBlocks as $block)

                    @if ($block['type'] === 'title')
                        <div class="sk-title">
                            <span class="sk-title-text">Surat Keputusan</span>
                        </div>
                        @if ($skLetter->title)
                            <div class="sk-subtitle">{{ $skLetter->title }}</div>
                        @endif
                        <div class="sk-number">Nomor: {{ $skLetter->sk_number }}</div>

                    @elseif ($block['type'] === 'label')
                        <div class="sk-label">{{ $block['text'] }}</div>

                    @elseif ($block['type'] === 'list_item')
                        <table class="sk-list">
                            <tr>
                                <td class="num">{{ $block['num'] }}</td>
                                <td>{{ $block['text'] }}</td>
                            </tr>
                        </table>

                    @elseif ($block['type'] === 'menetapkan')
                        <div class="sk-menetapkan">{!! $block['text'] !!}</div>

                    @elseif ($block['type'] === 'emp_table')
                        @php
                            $emp   = $block['employee'];
                            $pvt   = $block['pivot'];
                        @endphp
                        <table class="emp-table">
                            <tr>
                                <td class="col-label">Nama Karyawan</td>
                                <td class="col-sep">:</td>
                                <td class="col-value"><strong>{{ $emp->employee_name }}</strong></td>
                            </tr>
                            <tr>
                                <td class="col-label">NIK / ID Karyawan</td>
                                <td class="col-sep">:</td>
                                <td class="col-value">{{ $emp->employee_pengenal ?? ($emp->employee_code ?? '-') }}</td>
                            </tr>
                            @if ($pvt->previous_structure_id)
                                @php
                                    $prevStructure = \App\Models\Structuresnew::with('submissionposition.positionRelation')
                                        ->find($pvt->previous_structure_id);
                                @endphp
                                <tr>
                                    <td class="col-label">Jabatan Sebelumnya</td>
                                    <td class="col-sep">:</td>
                                    <td class="col-value">{{ $prevStructure?->submissionposition?->positionRelation?->name ?? '-' }}</td>
                                </tr>
                            @endif
                            @if ($pvt->new_structure_id)
                                @php
                                    $newStructure = \App\Models\Structuresnew::with('submissionposition.positionRelation')
                                        ->find($pvt->new_structure_id);
                                @endphp
                                <tr>
                                    <td class="col-label">Jabatan Baru</td>
                                    <td class="col-sep">:</td>
                                    <td class="col-value"><strong>{{ $newStructure?->submissionposition?->positionRelation?->name ?? '-' }}</strong></td>
                                </tr>
                            @endif
                            @if ($pvt->basic_salary)
                                <tr>
                                    <td class="col-label">Gaji Pokok</td>
                                    <td class="col-sep">:</td>
                                    <td class="col-value">Rp {{ number_format($pvt->basic_salary, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                            @if ($pvt->positional_allowance)
                                <tr>
                                    <td class="col-label">Tunjangan Jabatan</td>
                                    <td class="col-sep">:</td>
                                    <td class="col-value">Rp {{ number_format($pvt->positional_allowance, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                            @if ($pvt->daily_rate)
                                <tr>
                                    <td class="col-label">Daily Rate</td>
                                    <td class="col-sep">:</td>
                                    <td class="col-value">Rp {{ number_format($pvt->daily_rate, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="col-label">Tanggal Efektif</td>
                                <td class="col-sep">:</td>
                                <td class="col-value">{{ $formatTgl($skLetter->effective_date) }}</td>
                            </tr>
                            @if ($skLetter->inactive_date)
                                <tr>
                                    <td class="col-label">Berlaku Sampai</td>
                                    <td class="col-sep">:</td>
                                    <td class="col-value">{{ $formatTgl($skLetter->inactive_date) }}</td>
                                </tr>
                            @endif
                            @if ($pvt->notes)
                                <tr>
                                    <td class="col-label">Keterangan</td>
                                    <td class="col-sep">:</td>
                                    <td class="col-value">{{ $pvt->notes }}</td>
                                </tr>
                            @endif
                        </table>

                    @elseif ($block['type'] === 'place_ttd')
                        <div class="sk-place">
                            Ditetapkan di &nbsp;: {{ $skLetter->location ?? 'Denpasar' }}<br>
                            Pada tanggal &nbsp;&nbsp;: {{ $formatTgl($skLetter->effective_date) }}
                        </div>

                        <div class="ttd-wrap">
                            <table class="ttd-table">
                                <tr>
                                    <td>
                                        <div class="ttd-role">{{ $skLetter->approver1?->position->name }}</div>
                                        @if ($skLetter->approver_1_at && $skLetter->approver1?->signature)
                                            <div>
                                                <img src="{{ asset('storage/' . $skLetter->approver1->signature) }}"
                                                    alt="Signature" style="max-height:80px; object-fit:contain;">
                                            </div>
                                        @endif
                                        <div class="ttd-line">
                                            <div class="ttd-name">{{ $skLetter->approver1?->employee_name ?? '( _________________ )' }}</div>
                                            @if ($skLetter->approver_1_at)
                                                <div class="ttd-date">{{ $formatTglStr($skLetter->approver_1_at) }}</div>
                                            @else
                                                <div class="ttd-pending">Belum disetujui</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="ttd-role">Director</div>
                                        <div class="ttd-line">
                                            <div class="ttd-name">{{ $skLetter->approver2?->employee_name ?? '( _________________ )' }}</div>
                                            @if ($skLetter->approver_2_at)
                                                <div class="ttd-date">{{ $formatTglStr($skLetter->approver_2_at) }}</div>
                                            @else
                                                <div class="ttd-pending">Belum disetujui</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="ttd-role">Managing Director</div>
                                        <div class="ttd-line">
                                            <div class="ttd-name">{{ $skLetter->approver3?->employee_name ?? '( _________________ )' }}</div>
                                            @if ($skLetter->approver_3_at)
                                                <div class="ttd-date">{{ $formatTglStr($skLetter->approver_3_at) }}</div>
                                            @else
                                                <div class="ttd-pending">Belum disetujui</div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="sk-footer">
                            Dokumen ini diterbitkan secara resmi oleh {{ $skLetter->company->name }} &nbsp;|&nbsp;
                            SK Nomor: {{ $skLetter->sk_number }} &nbsp;|&nbsp;
                        </div>

                    @endif

                @endforeach

            </div>

        @endforeach

    @endforeach
</body>

</html> --}}
{{-- <!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $skLetter->sk_number }}</title>
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

        /* ── Setiap halaman fisik (page break) ── */
        .sk-physical-page {
            padding: 12mm 15mm 12mm 15mm;
            position: relative;
            overflow: hidden;
            page-break-after: always;
            page-break-inside: avoid;
            min-height: 270mm;
        }

        .sk-physical-page:last-child {
            page-break-after: avoid;
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

        /* ── Watermark CONFIDENTIAL ── */
        .watermark-confidential {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .watermark-confidential span {
            position: absolute;
            font-size: 16pt;
            font-weight: bold;
            color: #888;
            opacity: 0.15;
            transform: rotate(-35deg);
            white-space: nowrap;
            letter-spacing: 3px;
            text-transform: uppercase;
            font-family: 'Times New Roman', Times, serif;
        }

        /* Konten di atas semua watermark */
        .sk-physical-page > *:not(.watermark-logo):not(.watermark-confidential) {
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

        /* ── Kop lanjutan (halaman 2+) — lebih ringkas ── */
        .kop-continuation {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }

        .kop-continuation-inner {
            width: 100%;
            border-collapse: collapse;
        }

        .kop-continuation .kop-logo img {
            max-height: 55px;
            max-width: 80px;
        }

        .kop-continuation .kop-company {
            font-size: 13pt;
        }

        .kop-continuation .kop-address {
            font-size: 8pt;
        }

        /* ── Label lanjutan ── */
        .continuation-label {
            font-size: 9.5pt;
            font-style: italic;
            color: #444;
            text-align: right;
            margin-bottom: 6px;
        }

        /* ── Page number ── */
        .page-number {
            font-size: 9pt;
            color: #555;
            text-align: center;
            margin-bottom: 6px;
        }

        /* ── Judul ── */
        .sk-title {
            text-align: center;
            margin: 14px 0 4px 0;
        }

        .sk-title-text {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
        }

        .sk-subtitle {
            font-size: 11pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 4px;
        }

        .sk-number {
            text-align: center;
            font-size: 10.5pt;
            margin-bottom: 12px;
        }

        /* ── Divider ── */
        .divider-single {
            border: none;
            border-top: 1px solid #000;
            margin: 8px 0;
        }

        /* ── Section label ── */
        .sk-label {
            font-weight: bold;
            font-size: 10.5pt;
            margin: 10px 0 6px 0;
            display: block;
        }

        /* ── List menimbang/mengingat/keputusan ── */
        .sk-list {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .sk-list td {
            vertical-align: top;
            padding: 2px 0;
            font-size: 10.5pt;
            line-height: 1.5;
        }

        .sk-list td.num {
            width: 22px;
            white-space: nowrap;
        }

        /* ── Menetapkan ── */
        .sk-menetapkan {
            font-size: 10.5pt;
            line-height: 1.6;
            margin: 4px 0 8px 0;
        }

        /* ── Data karyawan ── */
        .emp-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 10px 0;
        }

        .emp-table td {
            padding: 3px 0;
            vertical-align: top;
            font-size: 10.5pt;
            line-height: 1.5;
        }

        .emp-table .col-label {
            width: 42%;
        }

        .emp-table .col-sep {
            width: 4%;
            text-align: center;
        }

        .emp-table .col-value {
            width: 54%;
        }

        /* ── Tempat & tanggal ── */
        .sk-place {
            font-size: 10.5pt;
            line-height: 1.8;
            margin-top: 12px;
        }

        /* ── Tanda Tangan ── */
        .ttd-wrap {
            margin-top: 20px;
            width: 100%;
        }

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
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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

        .ttd-date {
            font-size: 9.5pt;
            color: #333;
            margin-top: 2px;
        }

        .ttd-pending {
            font-size: 9pt;
            color: #999;
            font-style: italic;
            margin-top: 2px;
        }

        /* ── Footer ── */
        .sk-footer {
            margin-top: 18px;
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
            1  => 'Januari',  2  => 'Februari', 3  => 'Maret',
            4  => 'April',    5  => 'Mei',       6  => 'Juni',
            7  => 'Juli',     8  => 'Agustus',   9  => 'September',
            10 => 'Oktober',  11 => 'November',  12 => 'Desember',
        ];
        $formatTgl = function ($date) use ($bulan) {
            if (!$date) return '-';
            return $date->day . ' ' . $bulan[$date->month] . ' ' . $date->year;
        };
        $formatTglStr = function ($dateStr) use ($bulan) {
            if (!$dateStr) return '-';
            $d = \Carbon\Carbon::parse($dateStr);
            return $d->day . ' ' . $bulan[$d->month] . ' ' . $d->year;
        };

        /* ── Helper: render watermark di setiap halaman fisik ── */
        $watermarkPositions = [
            ['top' => '8%',  'left' => '5%'],
            ['top' => '8%',  'left' => '50%'],
            ['top' => '20%', 'left' => '27%'],
            ['top' => '20%', 'left' => '72%'],
            ['top' => '32%', 'left' => '5%'],
            ['top' => '32%', 'left' => '50%'],
            ['top' => '44%', 'left' => '27%'],
            ['top' => '44%', 'left' => '72%'],
            ['top' => '56%', 'left' => '5%'],
            ['top' => '56%', 'left' => '50%'],
            ['top' => '68%', 'left' => '27%'],
            ['top' => '68%', 'left' => '72%'],
            ['top' => '80%', 'left' => '5%'],
            ['top' => '80%', 'left' => '50%'],
            ['top' => '92%', 'left' => '27%'],
            ['top' => '92%', 'left' => '72%'],
        ];

        /* Estimasi tinggi konten (mm) — dikalibrasi dari hasil cetak nyata.
           A4 = 297mm, padding atas+bawah = 24mm → konten bersih = 273mm */
        $PAGE_HEIGHT_MM   = 273;  // tinggi bersih A4 setelah padding 12mm atas+bawah
        $KOP_HEIGHT_MM    = 30;   // kop penuh halaman pertama (logo 80px ≈ 28mm + border + margin)
        $KOP_CONT_MM      = 20;   // kop ringkas halaman lanjutan
        $FOOTER_MM        = 10;   // footer + sedikit buffer
        $LINE_MM          = 5.5;  // tinggi 1 baris teks 10.5pt Times New Roman
    @endphp

    @foreach ($skLetter->employees as $empIdx => $employee)
        @php
            $pivot = $employee->pivot;

            /* ─── Kumpulkan semua blok konten untuk SK ini ─── */
            $blocks = [];

            /* JUDUL (selalu di halaman pertama) */
            $blocks[] = ['type' => 'title'];

            /* MENIMBANG */
            if ($skLetter->menimbang->count() > 0) {
                $blocks[] = ['type' => 'label',     'text' => 'Menimbang :'];
                foreach ($skLetter->menimbang as $i => $item) {
                    $blocks[] = ['type' => 'list_item', 'num' => ($i+1).'.', 'text' => $item->content_menimbang];
                }
            }

            /* MENGINGAT */
            if ($skLetter->mengingat->count() > 0) {
                $blocks[] = ['type' => 'label',     'text' => 'Mengingat :'];
                foreach ($skLetter->mengingat as $i => $item) {
                    $blocks[] = ['type' => 'list_item', 'num' => ($i+1).'.', 'text' => $item->content_mengingat];
                }
            }

            /* MENETAPKAN */
            $blocks[] = ['type' => 'label', 'text' => 'Menetapkan :'];
            if ($skLetter->menetapkan_text) {
                $blocks[] = ['type' => 'menetapkan', 'text' => $skLetter->menetapkan_text];
            }

            /* KEPADA – data karyawan */
            $blocks[] = ['type' => 'label', 'text' => 'Kepada :'];
            $blocks[] = ['type' => 'emp_table', 'employee' => $employee, 'pivot' => $pivot];

            /* MEMUTUSKAN */
            if ($skLetter->keputusan->count() > 0) {
                $blocks[] = ['type' => 'label',     'text' => 'Memutuskan :'];
                foreach ($skLetter->keputusan as $i => $item) {
                    $blocks[] = ['type' => 'list_item', 'num' => ($i+1).'.', 'text' => $item->content_keputusan];
                }
            }

            /* TEMPAT & TTD – selalu satu kesatuan, ditaruh di akhir */
            $blocks[] = ['type' => 'place_ttd'];

            /* ─── Hitung distribusi blok ke halaman ─── */
            /* Estimasi tinggi tiap blok dalam mm — dikalibrasi dari hasil cetak */
            $estimateHeight = function ($block) use ($LINE_MM, $skLetter) {
                switch ($block['type']) {
                    case 'title':
                        /* "SURAT KEPUTUSAN" + subtitle (jika ada) + nomor SK + margin */
                        $h = 8 + $LINE_MM * 2; // judul underline + nomor
                        if ($skLetter->title) $h += $LINE_MM;
                        return $h + 4; // margin bawah

                    case 'label':
                        return $LINE_MM + 4; // teks bold + margin atas/bawah

                    case 'list_item':
                        /* Lebar konten ≈ 150mm, Times 10.5pt ≈ 2.2mm/karakter → ~68 char/baris */
                        $chars = mb_strlen($block['text']);
                        $rows  = max(1, ceil($chars / 68));
                        return $rows * $LINE_MM + 2;

                    case 'menetapkan':
                        $chars = mb_strlen(strip_tags($block['text']));
                        $rows  = max(1, ceil($chars / 68));
                        return $rows * $LINE_MM + 4;

                    case 'emp_table':
                        /* Hitung baris dinamis dari pivot yang ada */
                        $pvt = $block['pivot'];
                        $rows = 4; // nama, NIK, tanggal efektif — selalu ada
                        if ($pvt->previous_structure_id) $rows++;
                        if ($pvt->new_structure_id)      $rows++;
                        if ($pvt->basic_salary)          $rows++;
                        if ($pvt->positional_allowance)  $rows++;
                        if ($pvt->daily_rate)            $rows++;
                        if ($skLetter->inactive_date)    $rows++;
                        if ($pvt->notes)                 $rows++;
                        return $rows * ($LINE_MM + 1) + 6; // padding tabel

                    case 'place_ttd':
                        /* ditetapkan di (2 baris) + margin + ttd-role+space(55px≈19mm) + nama + status + footer */
                        return $LINE_MM * 2 + 4 + 19 + $LINE_MM * 2 + 10;

                    default:
                        return $LINE_MM;
                }
            };

            /* Distribusikan blok ke halaman */
            $pages        = [];
            $pageIdx      = 0;
            $usedMm       = $KOP_HEIGHT_MM;  // halaman pertama: kop penuh

            foreach ($blocks as $block) {
                $h         = $estimateHeight($block);
                $available = $PAGE_HEIGHT_MM - $usedMm - $FOOTER_MM;

                /* Jika tidak muat DAN sudah ada konten di halaman ini, mulai halaman baru.
                   Tambahkan buffer 8mm agar tidak terlalu agresif pindah halaman. */
                if ($usedMm > $KOP_HEIGHT_MM && ($h + 8) > $available) {
                    $pageIdx++;
                    $usedMm = $KOP_CONT_MM;
                }
                $pages[$pageIdx][] = $block;
                $usedMm += $h;
            }

            $totalPages = count($pages);
        @endphp

        @foreach ($pages as $pgNum => $pageBlocks)
            @php $isFirstPage = ($pgNum === 0); @endphp

            <div class="sk-physical-page">
                @if ($skLetter->company->foto)
                    <div class="watermark-logo">
                        <img src="{{ public_path('storage/' . $skLetter->company->foto) }}" alt="Watermark Logo">
                    </div>
                @endif

                <div class="watermark-confidential">
                    @foreach ($watermarkPositions as $pos)
                        <span style="top: {{ $pos['top'] }}; left: {{ $pos['left'] }};">CONFIDENTIAL</span>
                    @endforeach
                </div>

                @if ($isFirstPage)
                    <div class="kop">
                        <table class="kop-inner">
                            <tr>
                                <td class="kop-logo">
                                    @if ($skLetter->company->foto)
                                        <img src="{{ public_path('storage/' . $skLetter->company->foto) }}" alt="Logo">
                                    @endif
                                </td>
                                <td class="kop-text">
                                    <div class="kop-company">{{ $skLetter->company->name }}</div>
                                    <div class="kop-address">{{ $skLetter->company->address }}</div>
                                    @if ($skLetter->company->email)
                                        <div class="kop-contact">
                                            {{ $skLetter->company->phone }} | Email : {{ $skLetter->company->email }} |
                                            Website : {{ $skLetter->company->website }}
                                        </div>
                                    @endif
                                </td>
                                <td style="width:75px;"></td>
                            </tr>
                        </table>
                    </div>
                @else
                    <div class="kop kop-continuation">
                        <table class="kop-inner kop-continuation-inner">
                            <tr>
                                <td class="kop-logo">
                                    @if ($skLetter->company->foto)
                                        <img src="{{ public_path('storage/' . $skLetter->company->foto) }}" alt="Logo" style="max-height:55px;max-width:80px;">
                                    @endif
                                </td>
                                <td class="kop-text">
                                    <div class="kop-company" style="font-size:13pt;">{{ $skLetter->company->name }}</div>
                                    <div class="kop-address" style="font-size:8pt;">{{ $skLetter->company->address }}</div>
                                </td>
                                <td style="width:75px;"></td>
                            </tr>
                        </table>
                    </div>

                    <div class="continuation-label">
                        Lanjutan — SK Nomor: {{ $skLetter->sk_number }}
                        @if ($skLetter->title) &nbsp;|&nbsp; {{ $skLetter->title }} @endif
                    </div>
                @endif

                @if ($totalPages > 1)
                    <div class="page-number">
                        Halaman {{ $pgNum + 1 }} dari {{ $totalPages }}
                    </div>
                @endif

                @foreach ($pageBlocks as $block)

                    @if ($block['type'] === 'title')
                        <div class="sk-title">
                            <span class="sk-title-text">Surat Keputusan</span>
                        </div>
                        @if ($skLetter->title)
                            <div class="sk-subtitle">{{ $skLetter->title }}</div>
                        @endif
                        <div class="sk-number">Nomor: {{ $skLetter->sk_number }}</div>

                    @elseif ($block['type'] === 'label')
                        <div class="sk-label">{{ $block['text'] }}</div>

                    @elseif ($block['type'] === 'list_item')
                        <table class="sk-list">
                            <tr>
                                <td class="num">{{ $block['num'] }}</td>
                                <td>{{ $block['text'] }}</td>
                            </tr>
                        </table>

                    @elseif ($block['type'] === 'menetapkan')
                        <div class="sk-menetapkan">{!! $block['text'] !!}</div>

                    @elseif ($block['type'] === 'emp_table')
                        @php
                            $emp   = $block['employee'];
                            $pvt   = $block['pivot'];
                        @endphp
                        <table class="emp-table">
                            <tr>
                                <td class="col-label">Nama Karyawan</td>
                                <td class="col-sep">:</td>
                                <td class="col-value"><strong>{{ $emp->employee_name }}</strong></td>
                            </tr>
                            <tr>
                                <td class="col-label">NIK / ID Karyawan</td>
                                <td class="col-sep">:</td>
                                <td class="col-value">{{ $emp->employee_pengenal ?? ($emp->employee_code ?? '-') }}</td>
                            </tr>
                            @if ($pvt->previous_structure_id)
                                @php
                                    $prevStructure = \App\Models\Structuresnew::with('submissionposition.positionRelation')
                                        ->find($pvt->previous_structure_id);
                                @endphp
                                <tr>
                                    <td class="col-label">Jabatan Sebelumnya</td>
                                    <td class="col-sep">:</td>
                                    <td class="col-value">{{ $prevStructure?->submissionposition?->positionRelation?->name ?? '-' }}</td>
                                </tr>
                            @endif
                            @if ($pvt->new_structure_id)
                                @php
                                    $newStructure = \App\Models\Structuresnew::with('submissionposition.positionRelation')
                                        ->find($pvt->new_structure_id);
                                @endphp
                                <tr>
                                    <td class="col-label">Jabatan Baru</td>
                                    <td class="col-sep">:</td>
                                    <td class="col-value"><strong>{{ $newStructure?->submissionposition?->positionRelation?->name ?? '-' }}</strong></td>
                                </tr>
                            @endif
                            @if ($pvt->basic_salary)
                                <tr>
                                    <td class="col-label">Gaji Pokok</td>
                                    <td class="col-sep">:</td>
                                    <td class="col-value">Rp {{ number_format($pvt->basic_salary, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                            @if ($pvt->positional_allowance)
                                <tr>
                                    <td class="col-label">Tunjangan Jabatan</td>
                                    <td class="col-sep">:</td>
                                    <td class="col-value">Rp {{ number_format($pvt->positional_allowance, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                            @if ($pvt->daily_rate)
                                <tr>
                                    <td class="col-label">Daily Rate</td>
                                    <td class="col-sep">:</td>
                                    <td class="col-value">Rp {{ number_format($pvt->daily_rate, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="col-label">Tanggal Efektif</td>
                                <td class="col-sep">:</td>
                                <td class="col-value">{{ $formatTgl($skLetter->effective_date) }}</td>
                            </tr>
                            @if ($skLetter->inactive_date)
                                <tr>
                                    <td class="col-label">Berlaku Sampai</td>
                                    <td class="col-sep">:</td>
                                    <td class="col-value">{{ $formatTgl($skLetter->inactive_date) }}</td>
                                </tr>
                            @endif
                            @if ($pvt->notes)
                                <tr>
                                    <td class="col-label">Keterangan</td>
                                    <td class="col-sep">:</td>
                                    <td class="col-value">{{ $pvt->notes }}</td>
                                </tr>
                            @endif
                        </table>

                    @elseif ($block['type'] === 'place_ttd')
                        <div class="sk-place">
                            Ditetapkan di &nbsp;: {{ $skLetter->location ?? 'Denpasar' }}<br>
                            Pada tanggal &nbsp;&nbsp;: {{ $formatTgl($skLetter->effective_date) }}
                        </div>

                        <div class="ttd-wrap">
                            <table class="ttd-table">
                                <tr>
                                    <td>
                                        <div class="ttd-role">{{ $skLetter->approver1?->position->name }}</div>
                                        @if ($skLetter->approver_1_at && $skLetter->approver1?->signature)
                                            <div>
                                                <img src="{{ asset('storage/' . $skLetter->approver1->signature) }}"
                                                    alt="Signature" style="max-height:80px; object-fit:contain;">
                                            </div>
                                        @endif
                                        <div class="ttd-line">
                                            <div class="ttd-name">{{ $skLetter->approver1?->employee_name ?? '( _________________ )' }}</div>
                                            @if ($skLetter->approver_1_at)
                                                <div class="ttd-date">{{ $formatTglStr($skLetter->approver_1_at) }}</div>
                                            @else
                                                <div class="ttd-pending">Belum disetujui</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="ttd-role">Director</div>
                                        <div class="ttd-line">
                                            <div class="ttd-name">{{ $skLetter->approver2?->employee_name ?? '( _________________ )' }}</div>
                                            @if ($skLetter->approver_2_at)
                                                <div class="ttd-date">{{ $formatTglStr($skLetter->approver_2_at) }}</div>
                                            @else
                                                <div class="ttd-pending">Belum disetujui</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="ttd-role">Managing Director</div>
                                        <div class="ttd-line">
                                            <div class="ttd-name">{{ $skLetter->approver3?->employee_name ?? '( _________________ )' }}</div>
                                            @if ($skLetter->approver_3_at)
                                                <div class="ttd-date">{{ $formatTglStr($skLetter->approver_3_at) }}</div>
                                            @else
                                                <div class="ttd-pending">Belum disetujui</div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="sk-footer">
                            Dokumen ini diterbitkan secara resmi oleh {{ $skLetter->company->name }} &nbsp;|&nbsp;
                            SK Nomor: {{ $skLetter->sk_number }} &nbsp;|&nbsp;
                        </div>

                    @endif

                @endforeach

            </div>

        @endforeach

    @endforeach
</body>

</html> --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $skLetter->sk_number }}</title>
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

        /* ── Setiap halaman fisik (page break) ── */
        .sk-physical-page {
            padding: 12mm 15mm 12mm 15mm;
            position: relative;
            overflow: hidden;
            page-break-after: always;
            page-break-inside: avoid;
            min-height: 270mm;
        }

        .sk-physical-page:last-child {
            page-break-after: avoid;
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

        /* ── Watermark CONFIDENTIAL ── */
        .watermark-confidential {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .watermark-confidential span {
            position: absolute;
            font-size: 16pt;
            font-weight: bold;
            color: #888;
            opacity: 0.15;
            transform: rotate(-35deg);
            white-space: nowrap;
            letter-spacing: 3px;
            text-transform: uppercase;
            font-family: 'Times New Roman', Times, serif;
        }

        /* Konten di atas semua watermark */
        .sk-physical-page>*:not(.watermark-logo):not(.watermark-confidential) {
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

        /* ── Kop lanjutan (halaman 2+) — identik dengan halaman pertama ── */

        /* ── Label lanjutan ── */
        .continuation-label {
            font-size: 9.5pt;
            font-style: italic;
            color: #444;
            text-align: right;
            margin-bottom: 6px;
        }

        /* ── Page number ── */
        .page-number {
            font-size: 9pt;
            color: #555;
            text-align: center;
            margin-bottom: 6px;
        }

        /* ── Judul ── */
        .sk-title {
            text-align: center;
            margin: 14px 0 4px 0;
        }

        .sk-title-text {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
        }

        .sk-subtitle {
            font-size: 11pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 4px;
        }

        .sk-number {
            text-align: center;
            font-size: 10.5pt;
            margin-bottom: 12px;
        }

        /* ── Divider ── */
        .divider-single {
            border: none;
            border-top: 1px solid #000;
            margin: 8px 0;
        }

        /* ── Section label ── */
        .sk-label {
            font-weight: bold;
            font-size: 10.5pt;
            margin: 10px 0 6px 0;
            display: block;
        }

        /* ── List menimbang/mengingat/keputusan ── */
        .sk-list {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .sk-list td {
            vertical-align: top;
            padding: 2px 0;
            font-size: 10.5pt;
            line-height: 1.5;
        }

        .sk-list td.num {
            width: 22px;
            white-space: nowrap;
        }

        /* ── Menetapkan ── */
        .sk-menetapkan {
            font-size: 10.5pt;
            line-height: 1.6;
            margin: 4px 0 8px 0;
        }

        /* ── Data karyawan ── */
        .emp-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 10px 0;
        }

        .emp-table td {
            padding: 3px 0;
            vertical-align: top;
            font-size: 10.5pt;
            line-height: 1.5;
        }

        .emp-table .col-label {
            width: 42%;
        }

        .emp-table .col-sep {
            width: 4%;
            text-align: center;
        }

        .emp-table .col-value {
            width: 54%;
        }

        /* ── Tempat & tanggal ── */
        .sk-place {
            font-size: 10.5pt;
            line-height: 1.8;
            margin-top: 12px;
        }

        /* ── Tanda Tangan ── */
        .ttd-wrap {
            margin-top: 20px;
            width: 100%;
        }

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
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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

        .ttd-date {
            font-size: 9.5pt;
            color: #333;
            margin-top: 2px;
        }

        .ttd-pending {
            font-size: 9pt;
            color: #999;
            font-style: italic;
            margin-top: 2px;
        }

        /* ── Footer ── */
        .sk-footer {
            margin-top: 18px;
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
        $formatTgl = function ($date) use ($bulan) {
            if (!$date) {
                return '-';
            }
            return $date->day . ' ' . $bulan[$date->month] . ' ' . $date->year;
        };
        $formatTglStr = function ($dateStr) use ($bulan) {
            if (!$dateStr) {
                return '-';
            }
            $d = \Carbon\Carbon::parse($dateStr);
            return $d->day . ' ' . $bulan[$d->month] . ' ' . $d->year;
        };

        /* ── Helper: render watermark di setiap halaman fisik ── */
        $watermarkPositions = [
            ['top' => '8%', 'left' => '5%'],
            ['top' => '8%', 'left' => '50%'],
            ['top' => '20%', 'left' => '27%'],
            ['top' => '20%', 'left' => '72%'],
            ['top' => '32%', 'left' => '5%'],
            ['top' => '32%', 'left' => '50%'],
            ['top' => '44%', 'left' => '27%'],
            ['top' => '44%', 'left' => '72%'],
            ['top' => '56%', 'left' => '5%'],
            ['top' => '56%', 'left' => '50%'],
            ['top' => '68%', 'left' => '27%'],
            ['top' => '68%', 'left' => '72%'],
            ['top' => '80%', 'left' => '5%'],
            ['top' => '80%', 'left' => '50%'],
            ['top' => '92%', 'left' => '27%'],
            ['top' => '92%', 'left' => '72%'],
        ];

        /* Estimasi tinggi konten (mm) — dikalibrasi dari hasil cetak nyata.
 A4 = 297mm, padding atas+bawah = 24mm → konten bersih = 273mm */
        $PAGE_HEIGHT_MM = 273; // tinggi bersih A4 setelah padding 12mm atas+bawah
        $KOP_HEIGHT_MM = 30; // kop penuh halaman pertama (logo 80px ≈ 28mm + border + margin)
        $KOP_CONT_MM = 30; // kop halaman lanjutan — sama dengan halaman pertama
        $FOOTER_MM = 10; // footer + sedikit buffer
        $LINE_MM = 5.5; // tinggi 1 baris teks 10.5pt Times New Roman
    @endphp

    @foreach ($skLetter->employees as $empIdx => $employee)
        @php
            $pivot = $employee->pivot;

            /* ─── Kumpulkan semua blok konten untuk SK ini ─── */
            $blocks = [];

            /* JUDUL (selalu di halaman pertama) */
            $blocks[] = ['type' => 'title'];

            /* MENIMBANG */
            if ($skLetter->menimbang->count() > 0) {
                $blocks[] = ['type' => 'label', 'text' => 'Menimbang :'];
                foreach ($skLetter->menimbang as $i => $item) {
                    $blocks[] = ['type' => 'list_item', 'num' => $i + 1 . '.', 'text' => $item->content_menimbang];
                }
            }

            /* MENGINGAT */
            if ($skLetter->mengingat->count() > 0) {
                $blocks[] = ['type' => 'label', 'text' => 'Mengingat :'];
                foreach ($skLetter->mengingat as $i => $item) {
                    $blocks[] = ['type' => 'list_item', 'num' => $i + 1 . '.', 'text' => $item->content_mengingat];
                }
            }

            /* MENETAPKAN */
            $blocks[] = ['type' => 'label', 'text' => 'Menetapkan :'];
            if ($skLetter->menetapkan_text) {
                $blocks[] = ['type' => 'menetapkan', 'text' => $skLetter->menetapkan_text];
            }

            /* KEPADA – data karyawan */
            $blocks[] = ['type' => 'label', 'text' => 'Kepada :'];
            $blocks[] = ['type' => 'emp_table', 'employee' => $employee, 'pivot' => $pivot];

            /* MEMUTUSKAN */
            if ($skLetter->keputusan->count() > 0) {
                $blocks[] = ['type' => 'label', 'text' => 'Memutuskan :'];
                foreach ($skLetter->keputusan as $i => $item) {
                    $blocks[] = ['type' => 'list_item', 'num' => $i + 1 . '.', 'text' => $item->content_keputusan];
                }
            }

            /* TEMPAT & TTD – selalu satu kesatuan, ditaruh di akhir */
            $blocks[] = ['type' => 'place_ttd'];

            /* ─── Hitung distribusi blok ke halaman ─── */
            /* Estimasi tinggi tiap blok dalam mm — dikalibrasi dari hasil cetak */
            $estimateHeight = function ($block) use ($LINE_MM, $skLetter) {
                switch ($block['type']) {
                    case 'title':
                        /* "SURAT KEPUTUSAN" + subtitle (jika ada) + nomor SK + margin */
                        $h = 8 + $LINE_MM * 2; // judul underline + nomor
                        if ($skLetter->title) {
                            $h += $LINE_MM;
                        }
                        return $h + 4; // margin bawah

                    case 'label':
                        return $LINE_MM + 4; // teks bold + margin atas/bawah

                    case 'list_item':
                        /* Lebar konten ≈ 150mm, Times 10.5pt ≈ 2.2mm/karakter → ~68 char/baris */
                        $chars = mb_strlen($block['text']);
                        $rows = max(1, ceil($chars / 68));
                        return $rows * $LINE_MM + 2;

                    case 'menetapkan':
                        $chars = mb_strlen(strip_tags($block['text']));
                        $rows = max(1, ceil($chars / 68));
                        return $rows * $LINE_MM + 4;

                    case 'emp_table':
                        /* Hitung baris dinamis dari pivot yang ada */
                        $pvt = $block['pivot'];
                        $rows = 4; // nama, NIK, tanggal efektif — selalu ada
                        if ($pvt->previous_structure_id) {
                            $rows++;
                        }
                        if ($pvt->new_structure_id) {
                            $rows++;
                        }
                        if ($pvt->basic_salary) {
                            $rows++;
                        }
                        if ($pvt->positional_allowance) {
                            $rows++;
                        }
                        if ($pvt->daily_rate) {
                            $rows++;
                        }
                        if ($skLetter->inactive_date) {
                            $rows++;
                        }
                        if ($pvt->notes) {
                            $rows++;
                        }
                        return $rows * ($LINE_MM + 1) + 6; // padding tabel

                    case 'place_ttd':
                        /* ditetapkan di (2 baris) + margin + ttd-role+space(55px≈19mm) + nama + status + footer */
                        return $LINE_MM * 2 + 4 + 19 + $LINE_MM * 2 + 10;

                    default:
                        return $LINE_MM;
                }
            };

            /* Distribusikan blok ke halaman */
            $pages = [];
            $pageIdx = 0;
            $usedMm = $KOP_HEIGHT_MM; // halaman pertama: kop penuh

            foreach ($blocks as $block) {
                $h = $estimateHeight($block);
                $available = $PAGE_HEIGHT_MM - $usedMm - $FOOTER_MM;

                if ($usedMm > $KOP_HEIGHT_MM && $h + 8 > $available) {
                    $pageIdx++;
                    $usedMm = $KOP_CONT_MM;
                }
                $pages[$pageIdx][] = $block;
                $usedMm += $h;
            }

            $totalPages = count($pages);
        @endphp

        @foreach ($pages as $pgNum => $pageBlocks)
            @php $isFirstPage = ($pgNum === 0); @endphp

            <div class="sk-physical-page">

                {{-- ── Watermark logo ── --}}
                @if ($skLetter->company->foto)
                    <div class="watermark-logo">
                        <img src="{{ public_path('storage/' . $skLetter->company->foto) }}" alt="Watermark Logo">
                    </div>
                @endif

                {{-- ── Watermark CONFIDENTIAL ── --}}
                <div class="watermark-confidential">
                    @foreach ($watermarkPositions as $pos)
                        <span style="top: {{ $pos['top'] }}; left: {{ $pos['left'] }};">CONFIDENTIAL</span>
                    @endforeach
                </div>

                {{-- ── KOP SURAT ── --}}
                @if ($isFirstPage)
                    {{-- Kop penuh di halaman pertama --}}
                    <div class="kop">
                        <table class="kop-inner">
                            <tr>
                                <td class="kop-logo">
                                    @if ($skLetter->company->foto)
                                        <img src="{{ public_path('storage/' . $skLetter->company->foto) }}"
                                            alt="Logo">
                                    @endif
                                </td>
                                <td class="kop-text">
                                    <div class="kop-company">{{ $skLetter->company->name }}</div>
                                    <div class="kop-address">{{ $skLetter->company->address }}</div>
                                    @if ($skLetter->company->email)
                                        <div class="kop-contact">
                                            {{ $skLetter->company->phone }} | {{ $skLetter->company->email }} |
                                            {{ $skLetter->company->website }}
                                        </div>
                                    @endif
                                </td>
                                <td style="width:75px;"></td>
                            </tr>
                        </table>
                    </div>
                @else
                    {{-- Kop penuh di halaman lanjutan — identik dengan halaman pertama --}}
                    <div class="kop">
                        <table class="kop-inner">
                            <tr>
                                <td class="kop-logo">
                                    @if ($skLetter->company->foto)
                                        <img src="{{ public_path('storage/' . $skLetter->company->foto) }}"
                                            alt="Logo">
                                    @endif
                                </td>
                                <td class="kop-text">
                                    <div class="kop-company">{{ $skLetter->company->name }}</div>
                                    <div class="kop-address">{{ $skLetter->company->address }}</div>
                                    @if ($skLetter->company->email)
                                        <div class="kop-contact">
                                            {{ $skLetter->company->phone }} | Email : {{ $skLetter->company->email }}
                                            |
                                            Website : {{ $skLetter->company->website }}
                                        </div>
                                    @endif
                                </td>
                                <td style="width:75px;"></td>
                            </tr>
                        </table>
                    </div>

                    {{-- Label lanjutan + nomor SK --}}
                    <div class="continuation-label">
                        Lanjutan — SK Nomor: {{ $skLetter->sk_number }}
                        @if ($skLetter->title)
                            &nbsp;|&nbsp; {{ $skLetter->title }}
                        @endif
                    </div>
                @endif

                {{-- ── Nomor Halaman (per SK) ── --}}
                @if ($totalPages > 1)
                    <div class="page-number">
                        Halaman {{ $pgNum + 1 }} dari {{ $totalPages }}
                    </div>
                @endif

                {{-- ── Render blok konten ── --}}
                @foreach ($pageBlocks as $block)
                    @if ($block['type'] === 'title')
                        <div class="sk-title">
                            <span class="sk-title-text">Surat Keputusan</span>
                        </div>
                        @if ($skLetter->title)
                            <div class="sk-subtitle">{{ $skLetter->title }}</div>
                        @endif
                        <div class="sk-number">Nomor: {{ $skLetter->sk_number }}</div>
                    @elseif ($block['type'] === 'label')
                        <div class="sk-label">{{ $block['text'] }}</div>
                    @elseif ($block['type'] === 'list_item')
                        <table class="sk-list">
                            <tr>
                                <td class="num">{{ $block['num'] }}</td>
                                <td>{{ $block['text'] }}</td>
                            </tr>
                        </table>
                    @elseif ($block['type'] === 'menetapkan')
                        <div class="sk-menetapkan">{!! $block['text'] !!}</div>
                    @elseif ($block['type'] === 'emp_table')
                        @php
                            $emp = $block['employee'];
                            $pvt = $block['pivot'];
                        @endphp
                        <table class="emp-table">
                            <tr>
                                <td class="col-label">Nama Karyawan</td>
                                <td class="col-sep">:</td>
                                <td class="col-value"><strong>{{ $emp->employee_name }}</strong></td>
                            </tr>
                            <tr>
                                <td class="col-label">NIK / ID Karyawan</td>
                                <td class="col-sep">:</td>
                                <td class="col-value">{{ $emp->employee_pengenal ?? ($emp->employee_code ?? '-') }}
                                </td>
                            </tr>
                            @if ($pvt->previous_structure_id)
                                @php
                                    $prevStructure = \App\Models\Structuresnew::with(
                                        'submissionposition.positionRelation',
                                    )->find($pvt->previous_structure_id);
                                @endphp
                                <tr>
                                    <td class="col-label">Jabatan Sebelumnya</td>
                                    <td class="col-sep">:</td>
                                    <td class="col-value">
                                        {{ $prevStructure?->submissionposition?->positionRelation?->name ?? '-' }}</td>
                                </tr>
                            @endif
                            @if ($pvt->new_structure_id)
                                @php
                                    $newStructure = \App\Models\Structuresnew::with(
                                        'submissionposition.positionRelation',
                                    )->find($pvt->new_structure_id);
                                @endphp
                                <tr>
                                    <td class="col-label">Jabatan Baru</td>
                                    <td class="col-sep">:</td>
                                    <td class="col-value">
                                        <strong>{{ $newStructure?->submissionposition?->positionRelation?->name ?? '-' }}</strong>
                                    </td>
                                </tr>
                            @endif
                            @if ($pvt->basic_salary)
                                <tr>
                                    <td class="col-label">Gaji Pokok</td>
                                    <td class="col-sep">:</td>
                                    <td class="col-value">Rp {{ number_format($pvt->basic_salary, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                            @if ($pvt->positional_allowance)
                                <tr>
                                    <td class="col-label">Tunjangan Jabatan</td>
                                    <td class="col-sep">:</td>
                                    <td class="col-value">Rp
                                        {{ number_format($pvt->positional_allowance, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                            @if ($pvt->daily_rate)
                                <tr>
                                    <td class="col-label">Daily Rate</td>
                                    <td class="col-sep">:</td>
                                    <td class="col-value">Rp {{ number_format($pvt->daily_rate, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="col-label">Tanggal Efektif</td>
                                <td class="col-sep">:</td>
                                <td class="col-value">{{ $formatTgl($skLetter->effective_date) }}</td>
                            </tr>
                            @if ($skLetter->inactive_date)
                                <tr>
                                    <td class="col-label">Berlaku Sampai</td>
                                    <td class="col-sep">:</td>
                                    <td class="col-value">{{ $formatTgl($skLetter->inactive_date) }}</td>
                                </tr>
                            @endif
                            @if ($pvt->notes)
                                <tr>
                                    <td class="col-label">Keterangan</td>
                                    <td class="col-sep">:</td>
                                    <td class="col-value">{{ $pvt->notes }}</td>
                                </tr>
                            @endif
                        </table>
                    @elseif ($block['type'] === 'place_ttd')
                        <div class="sk-place">
                            Ditetapkan di &nbsp;: {{ $skLetter->location ?? 'Denpasar' }}<br>
                            Pada tanggal &nbsp;&nbsp;: {{ $formatTgl($skLetter->effective_date) }}
                        </div>

                        <div class="ttd-wrap">
                            <table class="ttd-table">
                                <tr>
                                 
                    {{-- <td>
    <div class="ttd-role">{{ $skLetter->approver1?->position->name }}</div>

    @if ($skLetter->approver_1_at && $skLetter->approver1?->signature)
        <div style="display:flex; justify-content:center; align-items:flex-end; height:80px; margin-bottom:-5px;">
            <img src="{{ public_path('storage/' . $skLetter->approver1->signature) }}"
                alt="Signature" style="max-height:80px; object-fit:contain;">
        </div>
    @endif
    <div class="ttd-line">
        <div class="ttd-name">
            {{ $skLetter->approver1?->employee_name ?? '( _________________ )' }}
        </div>
        @if ($skLetter->approver_1_at)
            <div class="ttd-date">{{ $formatTglStr($skLetter->approver_1_at) }}</div>
        @else
            <div class="ttd-pending">Belum disetujui</div>
        @endif
    </div>
</td> --}}
<td>
    <div class="ttd-role" style="margin-bottom: 0;">
        {{ $skLetter->approver1?->position->name }}
    </div>

    @if ($skLetter->approver_1_at && $skLetter->approver1?->signature)
        <div style="display:flex; justify-content:center; align-items:center; height:55px;">
            <img src="{{ public_path('storage/' . $skLetter->approver1->signature) }}"
                alt="Signature" style="max-height:55px; object-fit:contain;">
        </div>
    @else
        <div style="height:55px;"></div>
    @endif

    <div class="ttd-line">
        <div class="ttd-name">
            {{ $skLetter->approver1?->employee_name ?? '( _________________ )' }}
        </div>
        @if ($skLetter->approver_1_at)
            <div class="ttd-date">{{ $formatTglStr($skLetter->approver_1_at) }}</div>
        @else
            <div class="ttd-pending">Belum disetujui</div>
        @endif
    </div>
</td>
                               
                                    <td>
                                        <div class="ttd-role">Director</div>
                                        <div class="ttd-line">
                                            <div class="ttd-name">
                                                {{ $skLetter->approver2?->employee_name ?? '( _________________ )' }}
                                            </div>
                                            @if ($skLetter->approver_2_at)
                                                <div class="ttd-date">{{ $formatTglStr($skLetter->approver_2_at) }}
                                                </div>
                                            @else
                                                <div class="ttd-pending">Belum disetujui</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="ttd-role">Managing Director</div>
                                        <div class="ttd-line">
                                            <div class="ttd-name">
                                                {{ $skLetter->approver3?->employee_name ?? '( _________________ )' }}
                                            </div>
                                            @if ($skLetter->approver_3_at)
                                                <div class="ttd-date">{{ $formatTglStr($skLetter->approver_3_at) }}
                                                </div>
                                            @else
                                                <div class="ttd-pending">Belum disetujui</div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="sk-footer">
                            Dokumen ini diterbitkan secara resmi oleh {{ $skLetter->company->name }} &nbsp;|&nbsp;
                            SK Nomor: {{ $skLetter->sk_number }} &nbsp;|&nbsp;
                        </div>
                    @endif
                @endforeach

            </div>{{-- end .sk-physical-page --}}
        @endforeach
    @endforeach
</body>

</html>
