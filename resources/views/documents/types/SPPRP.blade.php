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

        /* Konten di atas watermark */
        .page > *:not(.watermark-logo) {
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

        /* ── Info surat ── */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 10.5pt;
        }

        .info-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .info-table .col-label { width: 80px; }
        .info-table .col-sep   { width: 20px; }

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

        .data-table .col-label { width: 160px; }
        .data-table .col-sep   { width: 20px; }

        /* ── Detail bank ── */
        .bank-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 12px 30px;
            font-size: 10.5pt;
        }

        .bank-table td {
            padding: 2px 0;
            vertical-align: top;
            line-height: 1.5;
        }

        .bank-table .col-label { width: 160px; }
        .bank-table .col-sep   { width: 20px; }

        /* ── PIC list ── */
        .pic-list {
            margin: 6px 0 12px 30px;
            font-size: 10.5pt;
            line-height: 1.8;
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
            1  => 'Januari',  2  => 'Februari', 3  => 'Maret',
            4  => 'April',    5  => 'Mei',       6  => 'Juni',
            7  => 'Juli',     8  => 'Agustus',   9  => 'September',
            10 => 'Oktober',  11 => 'November',  12 => 'Desember',
        ];

        $formatTgl = function ($dateStr) use ($bulan) {
            if (!$dateStr) return '-';
            $d = \Carbon\Carbon::parse($dateStr);
            return $d->day . ' ' . $bulan[$d->month] . ' ' . $d->year;
        };
    @endphp

    <div class="page">

        {{-- ── Watermark logo ── --}}
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

        {{-- ── Info Surat ── --}}
        <table class="info-table">
            <tr>
                <td class="col-label">Tanggal</td>
                <td class="col-sep">:</td>
                <td>{{ $formatTgl($document->issued_date) }}</td>
            </tr>
            <tr>
                <td class="col-label">Perihal</td>
                <td class="col-sep">:</td>
                <td>
                    Surat Pengantar Pembukaan Rekening Payroll Karyawan<br>
                    {{ $company->name }}
                </td>
            </tr>
        </table>

        {{-- ── Judul ── --}}
        <div class="doc-title">
            <span class="doc-title-text">Surat Pengantar Pembukaan Rekening Payroll</span>
        </div>
        <div class="doc-number">Nomor: {{ $document->document_number }}</div>

        {{-- ── Kepada ── --}}
        <p class="body-text">
            Kepada Yth,<br>
            Service Advisor/Operation Manager/Area Service Manager {{ $config->bank_name }}
        </p>

        <p class="body-text">Saya yang bertandatangan di bawah ini:</p>

        <table class="data-table">
            <tr>
                <td class="col-label">Nama</td>
                <td class="col-sep">:</td>
                <td>{{ $issued->employee_name }}</td>
            </tr>
            <tr>
                <td class="col-label">Jabatan</td>
                <td class="col-sep">:</td>
                <td>{{ $issued->position->name ?? '-' }}</td>
            </tr>
        </table>

        <p class="body-text">
            Selaku Perwakilan Perusahaan {{ $company->name }} dengan ini memberitahukan bahwa:
        </p>
        <table class="data-table">
    @if ($employee->status_employee === 'DW' && $employee->company_id === '0196ba4f-5c58-7022-9eb2-ba407eaf4753')
        <tr>
            <td class="col-label">Nama</td>
            <td class="col-sep">:</td>
            <td><strong>{{ $employee->employee_name }}</strong></td>
        </tr>
        <tr>
            <td class="col-label">NIK</td>
            <td class="col-sep">:</td>
            <td>{{ $employee->nik ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-label">Alamat</td>
            <td class="col-sep">:</td>
            <td>{{ $employee->current_address ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-label">Jabatan</td>
            <td class="col-sep">:</td>
            <td>{{ $employee->position->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="col-label">NIP</td>
            <td class="col-sep">:</td>
            <td>{{ $employee->employee_pengenal ?? '-' }}</td>
        </tr>
    @else
        <tr>
            <td class="col-label">Nama</td>
            <td class="col-sep">:</td>
            <td><strong>{{ $employee->employee_name }}</strong></td>
        </tr>
        <tr>
            <td class="col-label">Jabatan</td>
            <td class="col-sep">:</td>
            <td>{{ $employee->position->name ?? '-' }}</td>
        </tr>
       
    @endif
</table>
  
@if ($employee->status_employee === 'DW' && $employee->company_id === '0196ba4f-5c58-7022-9eb2-ba407eaf4753')

    <p class="body-text">
        Dengan ini kami menyatakan bahwa data yang tercatat di atas adalah memang
        <strong><u>BENAR</u></strong> Karyawan/Karyawati kami yang akan mengajukan
        Pembukaan Rekening Payroll.
    </p>

    <p class="body-text">
        Demikian Surat Pengantar ini kami sampaikan agar dapat digunakan sebagaimana mestinya.
        Atas perhatian dan kerjasamanya, kami ucapkan terima kasih.
    </p>

    <table style="width: 100%; margin-bottom: 100px; font-size: 10.5pt;">
        <tr>
            {{-- Tempat & Tanggal --}}
            <td style="width: 60%; vertical-align: top;">
                <br><br>

                Ditetapkan di &nbsp;:
                {{ $company->city ?? 'Denpasar' }}
                <br>

                Pada tanggal &nbsp;&nbsp;:
                {{ $formatTgl($document->issued_date) }}
            </td>

            {{-- Tanda Tangan --}}
            {{-- <td style="width: 40%; text-align: center; vertical-align: top;">

                <div style="font-size: 9.5pt; font-style: italic; margin-bottom: 50px;">
                    {{ $issued->position->name ?? 'HR Manager' }}
                </div>

                @if ($issued->signature)
                    <img
                        src="{{ public_path('storage/' . $issued->signature) }}"
                        alt="Signature"
                        style="max-height: 50px; object-fit: contain; display: block; margin: 0 auto 4px;"
                    >
                @endif

                <div style="border-top: 1px solid #000; padding-top: 4px; margin: 0 10px;">
                    <strong>{{ $issued->employee_name }}</strong><br>

                    <span style="font-size: 9.5pt;">
                        {{ $issued->position->name ?? '-' }}
                    </span>
                </div>

            </td> --}}
            <td style="width: 40%; text-align: center; vertical-align: bottom;">
    @if ($issued->signature)
        <img
            src="{{ public_path('storage/' . $issued->signature) }}"
            alt="Signature"
            style="
                height: 70px;
                width: auto;
                display: block;
                margin: 0 auto 4px 50px;
            "
        >
    @else
        <div style="height: 70px;"></div>
    @endif
    <div style="padding-top: 4px; margin: 0 10px;">
        <strong>{{ $issued->employee_name }}</strong><br>
        <span style="font-size: 9.5pt;">
            {{ $issued->position->name ?? '-' }}
        </span>
    </div>
</td>
        </tr>
    </table>

@else

    <p class="body-text">
        Adalah <strong><u>BENAR</u></strong> karyawan perusahaan kami dan merupakan
        nasabah payroll Workplace Banking {{ $config->bank_name }}
        yang akan melakukan pembukaan rekening payroll di cabang Bapak/Ibu.

        Untuk detail ketentuan Jenis Produk Tabungan, Kode Promosi,
        dan layanan adalah sebagai berikut:
    </p>

    <table class="bank-table">
        <tr>
            <td class="col-label">Jenis Tabungan</td>
            <td class="col-sep">:</td>
            <td>{{ $config->savings_type }}</td>
        </tr>

        <tr>
            <td class="col-label">Kode Promosi</td>
            <td class="col-sep">:</td>
            <td>{{ $config->promo_code }}</td>
        </tr>

        <tr>
            <td class="col-label">Kode Komunitas</td>
            <td class="col-sep">:</td>
            <td>{{ $config->community_code }}</td>
        </tr>

        <tr>
            <td class="col-label">Layanan</td>
            <td class="col-sep">:</td>
            <td>{{ $config->service_name }}</td>
        </tr>
    </table>

    <p class="body-text">
        Jika membutuhkan informasi lebih lanjut, dapat menghubungi:
    </p>

    <div class="pic-list">
        <ol>
            <li>
                {{ $config->pic_name }}
                &nbsp;|&nbsp;
                email: {{ $config->pic_email }}
            </li>

            @if ($config->pic_name_2)
                <li>
                    {{ $config->pic_name_2 }}
                    &nbsp;|&nbsp;
                    email: {{ $config->pic_email_2 }}
                </li>
            @endif
        </ol>
    </div>

                <p class="body-text">
                    Demikian Surat Pengantar ini kami sampaikan agar dapat digunakan
                    sebagaimana mestinya. Atas perhatian dan kerjasamanya,
                    kami ucapkan terima kasih.
                </p>
    {{-- <table style="width: 100%; margin-bottom: 20px; font-size: 10.5pt;"> --}}
    <table style="width: 100%; margin-bottom: 90px; font-size: 10.5pt;">
        
        <tr>

            {{-- Keterangan --}}
            <td style="width: 60%; vertical-align: top;">


                <br>

                Ditetapkan di &nbsp;:
                {{ $company->city ?? 'Denpasar' }}
                <br>

                Pada tanggal &nbsp;&nbsp;:
                {{ $formatTgl($document->issued_date) }}

            </td>

            {{-- Tanda Tangan --}}
            {{-- <td style="width: 40%; text-align: center; vertical-align: top;">

                {{-- <div style="font-size: 9.5pt; font-style: italic; margin-bottom: 10px;">
                    {{ $issued->position->name ?? 'HR Manager' }}
                </div> --}}

                {{-- @if ($issued->signature)
                    <img
                        src="{{ public_path('storage/' . $issued->signature) }}"
                        alt="Signature"
                        style="max-height: 25px; object-fit: contain; display: block; margin: 0 auto 4px;"
                    >
                @endif --}}
              {{-- @if ($issued->signature)
    <img
        src="{{ public_path('storage/' . $issued->signature) }}"
        alt="Signature"
        style="
            height: 90px;
            width: auto;
            display: block;
            margin: -20px auto -10px auto;
        "
    >
@endif --}}
                {{-- <div style="border-top: 1px solid #000; padding-top: 4px; margin: 0 10px;"> --}}
                {{-- <div style="margin: 0 10px;">

                    <strong>{{ $issued->employee_name }}</strong><br>

                    <span style="font-size: 9.5pt;">
                        {{ $issued->position->name ?? '-' }}
                    </span>
                </div> --}}

            {{-- </td>  --}}
            {{-- Tanda Tangan --}}
<td style="width: 40%; text-align: center; vertical-align: bottom;">
    @if ($issued->signature)
        <img
            src="{{ public_path('storage/' . $issued->signature) }}"
            alt="Signature"
            style="
                height: 70px;
                width: auto;
                display: block;
                margin: 0 auto 4px 50px;
            "
        >
    @else
        <div style="height: 70px;"></div>
    @endif
    <div style="padding-top: 4px; margin: 0 10px;">
        <strong>{{ $issued->employee_name }}</strong><br>
        <span style="font-size: 9.5pt;">
            {{ $issued->position->name ?? '-' }}
        </span>
    </div>
</td>
        </tr>
    </table>
@endif
        {{-- ── Footer ── --}}
        <div class="doc-footer">
            Dokumen ini diterbitkan secara resmi oleh {{ $company->name }} &nbsp;|&nbsp;
            Nomor: {{ $document->document_number }} &nbsp;|&nbsp;
            Tanggal: {{ $formatTgl($document->issued_date) }}
        </div>
    </div>
</body>
</html>