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
    
        .ba-title { text-align: center; margin: 14px 0 4px; font-size: 13pt; font-weight: bold; text-decoration: underline; text-transform: uppercase; }
        .ba-number { text-align: center; margin-bottom: 14px; font-size: 10.5pt; }
        .ba-meta { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 10.5pt; }
        .ba-meta td { padding: 2px 0; vertical-align: top; }
        .ba-meta td.k { width: 120px; }
        .ba-meta td.s { width: 12px; }
        .ba-body { font-size: 11pt; line-height: 1.55; text-align: left; margin-bottom: 14px; }
        .ba-sign-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 10pt; page-break-inside: avoid; }
        .ba-sign-table td { width: 33%; padding: 6px 4px 14px; text-align: center; vertical-align: top; }
        .ba-sign-role { font-size: 9.5pt; margin-bottom: 4px; }
        .ba-sign-img { height: 60px; }
        .ba-sign-img img { height: 60px; width: auto; }
        .ba-sign-date { font-size: 8.5pt; color: #444; }
        .ba-cc { font-size: 10pt; margin-top: 8px; }
        .ba-att { font-size: 10pt; margin-bottom: 10px; }
        .ba-att ol { margin: 2px 0 0 18px; }
        .ba-att li { margin-bottom: 3px; }
        .ba-att a { color: #1a4fd6; font-size: 8.5pt; word-wrap: break-word; }
        .ba-cc ol { margin: 2px 0 0 18px; }
    </style>
</head>

<body>
    @php
        $bulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
        $tz = config('app.timezone');
        $tgl = function ($d) use ($bulan, $tz) {
            if (!$d) return '-';
            $d = \Carbon\Carbon::parse($d)->timezone($tz);
            return $d->day . ' ' . $bulan[$d->month] . ' ' . $d->year;
        };
        $jam = fn($d) => $d ? \Carbon\Carbon::parse($d)->timezone($tz)->format('H:i') : '';
        $recipients = $document->recipients;
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

        <div class="ba-title">{{ $type->document_name }}</div>
        <div class="ba-number">Nomor: {{ $document->document_number }}</div>

        <table class="ba-meta">
            <tr><td class="k">Perihal</td><td class="s">:</td><td>{{ $content->subject }}</td></tr>
            @if ($content->event_at)
                <tr><td class="k">Waktu</td><td class="s">:</td><td>{{ $tgl($content->event_at) }}, pukul {{ $jam($content->event_at) }}</td></tr>
            @endif
            @if ($content->location)
                <tr><td class="k">Tempat</td><td class="s">:</td><td>{{ $content->location }}</td></tr>
            @endif
            <tr><td class="k">Dibuat oleh</td><td class="s">:</td><td>{{ $submitter->employee_name }}@if ($titles[$submitter->id] ?? null), {{ $titles[$submitter->id] }}@endif</td></tr>
        </table>

        {{-- dompdf tidak andal dengan pre-wrap: baris baru dijadikan <br>, teks tetap di-escape --}}
        <div class="ba-body">{!! nl2br(e($content->body)) !!}</div>

        @if ($attachments->isNotEmpty())
            <div class="ba-att">
                Lampiran ({{ $attachments->count() }} berkas, buka lewat HRX):
                <ol>
                    @foreach ($attachments as $a)
                        <li>{{ $a['name'] }}<br><a href="{{ $a['url'] }}">{{ $a['url'] }}</a></li>
                    @endforeach
                </ol>
            </div>
        @endif

        <p style="font-size:11pt;margin-bottom:6px">Demikian berita acara ini dibuat dengan sebenarnya{{ $steps->contains('is_superior', true) ? ', diketahui,' : '' }} dan disetujui oleh pihak-pihak di bawah ini.</p>
        <p style="font-size:10.5pt">{{ $company->city ?? 'Denpasar' }}, {{ $tgl($content->completed_at ?? $document->issued_date) }}</p>

        {{-- Tanda tangan: pengaju, atasan pengaju ("Mengetahui"), lalu approver lain ("Disetujui (n)", dihitung setelah atasan), 3 per baris --}}
        @php
            $n = 0;
            $signers = collect([['role' => 'Dibuat oleh', 'name' => $submitter->employee_name, 'title' => $titles[$submitter->id] ?? null, 'img' => $submitterSignature, 'at' => $content->submitted_at]])
                ->concat($steps->map(function ($s) use (&$n, $titles) {
                    return ['role' => $s->is_superior ? 'Mengetahui' : 'Disetujui (' . (++$n) . ')', 'name' => $s->employee?->employee_name, 'title' => $titles[$s->employee_id] ?? null, 'img' => $s->signatureData, 'at' => $s->acted_at];
                }));
        @endphp
        <table class="ba-sign-table">
            @foreach ($signers->chunk(3) as $row)
                <tr>
                    @foreach ($row as $p)
                        <td>
                            <div class="ba-sign-role">{{ $p['role'] }}</div>
                            <div class="ba-sign-img">@if ($p['img'])<img src="{{ $p['img'] }}" alt="Tanda tangan">@endif</div>
                            <strong>{{ $p['name'] }}</strong><br>
                            @if ($p['title'])<span>{{ $p['title'] }}</span><br>@endif
                            @if ($p['img'])<span class="ba-sign-date">{{ $tgl($p['at']) }} {{ $jam($p['at']) }}</span>@endif
                        </td>
                    @endforeach
                    @for ($i = $row->count(); $i < 3; $i++)<td></td>@endfor
                </tr>
            @endforeach
        </table>

        @if ($recipients->isNotEmpty())
            <div class="ba-cc">
                Tembusan:
                <ol>
                    @foreach ($recipients as $r)
                        <li>{{ $r->employee?->employee_name }}@if ($titles[$r->employee_id] ?? null), {{ $titles[$r->employee_id] }}@endif</li>
                    @endforeach
                </ol>
            </div>
        @endif

        <div class="doc-footer">
            Dokumen ini diterbitkan secara elektronik oleh {{ $company->name }} &nbsp;|&nbsp;
            Nomor: {{ $document->document_number }} &nbsp;|&nbsp;
            Disetujui lengkap: {{ $tgl($content->completed_at) }}
        </div>
    </div>
</body>

</html>
