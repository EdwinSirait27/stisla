<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $document->document_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; color: #000; background: #fff; }
        .page { padding: 12mm 15mm 12mm 15mm; position: relative; overflow: hidden; min-height: 270mm; }
        .watermark-logo { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); opacity: 0.07; pointer-events: none; z-index: 0; }
        .watermark-logo img { max-height: 350px; max-width: 380px; object-fit: contain; }
        .page>*:not(.watermark-logo) { position: relative; z-index: 1; }
        .kop { width: 100%; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 12px; }
        .kop-inner { width: 100%; border-collapse: collapse; }
        .kop-logo { width: 75px; text-align: center; vertical-align: middle; }
        .kop-logo img { max-height: 80px; max-width: 110px; object-fit: contain; display: block; }
        .kop-text { text-align: center; vertical-align: middle; padding: 0 8px; }
        .kop-company { font-size: 15pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .kop-sub { font-size: 12pt; font-weight: bold; text-transform: uppercase; }
        .kop-address { font-size: 8.5pt; color: #222; margin-top: 3px; line-height: 1.4; }
        .kop-contact { font-size: 11px; margin-top: 2px; }
        .doc-title { text-align: center; margin: 14px 0 4px 0; }
        .doc-title-text { font-size: 13pt; font-weight: bold; text-decoration: underline; text-transform: uppercase; }
        .doc-number { text-align: center; font-size: 10.5pt; margin-bottom: 14px; }
        .body-text { font-size: 10.5pt; line-height: 1.6; margin-bottom: 10px; text-align: justify; }
        .data-table { width: 100%; border-collapse: collapse; margin: 6px 0 12px 0; font-size: 10.5pt; }
        .data-table td { padding: 3px 0; vertical-align: top; line-height: 1.5; }
        .data-table .col-no { width: 25px; }
        .data-table .col-label { width: 160px; }
        .data-table .col-sep { width: 20px; }
        .salary-table { width: 100%; border-collapse: collapse; margin: 6px 0 6px 25px; font-size: 10.5pt; }
        .salary-table td { padding: 2px 0; vertical-align: top; line-height: 1.5; }
        .salary-table .col-label { width: 200px; }
        .salary-table .col-sep { width: 20px; }
        .salary-total { border-top: 1px solid #000; font-weight: bold; }
        .bullet-list { margin: 4px 0 8px 25px; font-size: 10.5pt; line-height: 1.8; }
        .pola-list { margin: 0; padding-left: 16px; line-height: 1.8; }
        .doc-footer { position: fixed; bottom: 20px; left: 40px; right: 40px; text-align: center; font-size: 8pt; color: #555; border-top: 1px solid #ccc; padding-top: 5px; line-height: 1.4; }
    </style>
</head>
<body>
    @php
        $bulan = [
            1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
            5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
            9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
        ];
        $formatTgl = function ($dateStr) use ($bulan) {
            if (!$dateStr) return '-';
            $d = \Carbon\Carbon::parse($dateStr);
            return $d->day . ' ' . $bulan[$d->month] . ' ' . $d->year;
        };
        $formatRupiah = function ($amount) {
            return 'Rp' . number_format($amount, 0, ',', '.');
        };

        $position           = $employee->primaryPosition->first();
        $department         = $employee->primaryDepartment->first();
        $store              = $employee->primaryStore->first();
        $grading            = $employee->grading;
        $basicSalary        = $salary?->basic_salary ?? 0;
        $positionAllowance  = $salary?->position_allowance ?? 0;
        $overtimeAllowance  = 10000;
        $totalGross         = $basicSalary + $positionAllowance;
    @endphp

    <div class="page">

        {{-- Watermark --}}
        @if ($company->foto)
            <div class="watermark-logo">
                <img src="{{ public_path('storage/' . $company->foto) }}" alt="Watermark">
            </div>
        @endif

        {{-- Kop Surat --}}
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
                        <div class="kop-sub">{{ $company->header }}</div>
                        <div class="kop-address">{{ $company->address }}</div>
                        @if ($company->email)
                            <div class="kop-contact">
                                website: {{ $company->website ?? '-' }}, email: {{ $company->email }}
                            </div>
                        @endif
                    </td>
                    <td style="width:75px;"></td>
                </tr>
            </table>
        </div>

        {{-- Judul --}}
        <div class="doc-title">
            <span class="doc-title-text">Offering Letter</span>
        </div>
        <div class="doc-number">Nomor: {{ $document->document_number }}</div>

        {{-- Kepada --}}
        <p class="body-text">
            Kepada Yth.<br>
            <strong>Sdr. {{ $employee->employee_name }}</strong><br>
            Di Tempat
        </p>

        <p class="body-text">Dengan hormat,</p>
        <p class="body-text">
            Mengacu dari rangkaian hasil proses seleksi, maka bersama ini kami Management
            {{ $company->name }} memberikan Surat Penawaran Kerja (Offering Letter) sebagai berikut:
        </p>

        {{-- Data Karyawan --}}
        <table class="data-table">
            <tr>
                <td class="col-no">1.</td>
                <td class="col-label">Department</td>
                <td class="col-sep">:</td>
                <td>
    {{ $employee->department->isNotEmpty() 
        ? $employee->department->pluck('department_name')->implode(', ') 
        : '-' 
    }}
</td>
            </tr>
            <tr>
                <td class="col-no">2.</td>
                <td class="col-label">Posisi</td>
                <td class="col-sep">:</td>
                 <td>
    {{ $employee->position->isNotEmpty() 
        ? $employee->position->pluck('name')->implode(', ') 
        : '-' 
    }}
</td>
            </tr>
            <tr>
                <td class="col-no">3.</td>
                <td class="col-label">Grade Jabatan</td>
                <td class="col-sep">:</td>
                                <td>{{ $grading->grading_name ?? '-' }}</td>

            </tr>
            <tr>
                <td class="col-no">4.</td>
                <td class="col-label">Atasan Langsung</td>
                <td class="col-sep">:</td>
                <td>Manager/Asst. Manager</td>
            </tr>
            <tr>
                <td class="col-no">5.</td>
                <td class="col-label">Lokasi Kerja</td>
                <td class="col-sep">:</td>
                 <td>
    {{ $employee->store->isNotEmpty() 
        ? $employee->store->pluck('name')->implode(', ') 
        : '-' 
    }}
</td>
            </tr>
            <tr>
                <td class="col-no">6.</td>
                <td class="col-label">Point Of Hire</td>
                <td class="col-sep">:</td>
                <td>{{ $employee->point_of_hire ?? '-' }}</td>
            </tr>
            <tr>
                <td class="col-no">7.</td>
                <td class="col-label" colspan="3">
                    Komponen Pengupahan <strong>Gross</strong> sebelum dipotong iuran kepersertaan
                    BPJS Kesehatan dan BPJS Ketenagakerjaan sebagai berikut:
                </td>
            </tr>
        </table>

        {{-- Komponen Gaji OJT --}}
        <table class="salary-table">
            <tr>
                <td class="col-label">Gaji Pokok</td>
                <td class="col-sep">:</td>
                <td>RP. {{ $formatRupiah($basicSalary) }}</td>
            </tr>
            <tr>
                <td class="col-label">Tunjangan Jabatan</td>
                <td class="col-sep">:</td>
                <td>RP. {{ $formatRupiah($positionAllowance) }}</td>
            </tr>
            <tr>
                <td class="col-label">Tunjangan Lembur/jam</td>
                <td class="col-sep">:</td>
                <td>RP. {{ $formatRupiah($overtimeAllowance) }} / jam</td>
            </tr>
            <tr>
                <td class="col-label">Tunjangan Perumahan</td>
                <td class="col-sep">:</td>
                <td>-</td>
            </tr>
            <tr>
                <td class="col-label">Tunjangan Makanan</td>
                <td class="col-sep">:</td>
                <td>-</td>
            </tr>
            <tr>
                <td class="col-label">Tunjangan Transportasi</td>
                <td class="col-sep">:</td>
                <td>-</td>
            </tr>
            <tr class="salary-total">
                <td class="col-label">Total Gaji</td>
                <td class="col-sep">:</td>
                <td><strong>RP. {{ $formatRupiah($totalGross) }}</strong></td>
            </tr>
        </table>

        {{-- Pola Kerja & Detail --}}
        <table class="data-table">
            <tr>
                <td class="col-no">8.</td>
                <td class="col-label">Pola Kerja</td>
                <td class="col-sep">:</td>
                <td>
                    <ul class="pola-list">
                        <li><s>5 Hari Kerja : 2 Hari Libur ( Sabtu Kondisional & Minggu )</s></li>
                        <li>6 Hari Kerja : 1 Hari Libur (Minggu – Kondisional)</li>
                        <li><s>Hari Kerja Dinamis /Mobilisasi Lainnya………………………</s></li>
                        <li>Karyawan wajib bersedia bekerja dan ataupun menyelesaikan bagian
                            dari tugas dan tanggung jawabnya pada hari sabtu/libur apabila
                            diperlukan diatur di area kerja masing-masing</li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td class="col-no">9.</td>
                <td class="col-label">Status Pekerja</td>
                <td class="col-sep">:</td>
                <td>{{$employee->status_employee}} selama 3 ( tiga ) bulan</td>
            </tr>
            <tr>
                <td class="col-no">10.</td>
                <td class="col-label">Pola Kerja</td>
                <td class="col-sep">:</td>
                <td>6 Hari Kerja : 1 Hari Libur (jadwal libur akan diatur di area kerja masing-masing)</td>
            </tr>
            <tr>
                <td class="col-no">11.</td>
                <td class="col-label">Jam Kerja</td>
                <td class="col-sep">:</td>
                <td>Mengikuti jadwal kerja /shift yang telah ditentukan di Area kerja masing masing</td>
            </tr>
            <tr>
                <td class="col-no">12.</td>
                <td class="col-label">Pakaian Kerja</td>
                <td class="col-sep">:</td>
                <td>Menggunakan seragam kerja yang telah disediakan dan Memakai sepatu</td>
            </tr>
            <tr>
                <td class="col-no">13.</td>
                <td class="col-label">Mulai Kerja</td>
                <td class="col-sep">:</td>
                <td><strong>{{ $formatTgl($employee->join_date) }}</strong></td>
            </tr>
            <tr>
                <td class="col-no">14.</td>
                <td class="col-label">Fasilitas lain</td>
                <td class="col-sep">:</td>
                <td></td>
            </tr>
        </table>

        <ul class="bullet-list">
            <li>BPJS TK & BPJS KES diberikan setelah selesai masa OJT</li>
            <li>THR ( sesuai gaji pokok ) akan diberikan sesuai masa kerja dan waktu
                pemberiannya disesuaikan dengan ketentuan Perusahaan</li>
        </ul>

        {{-- Penutup --}}
        <p class="body-text">
            Demikian Penawaran Kerja ini dibuat sebagai dasar kesepakatan bersama dan akan mempunyai
            kekuatan hukum yang sama untuk Para Pihak pada hari, tanggal, tahun yang telah disepakati dan
            saling berkewajiban melanjutkan dalam Perjanjian Kerja yang merupakan bagian yang tidak terpisahkan.
        </p>

        {{-- TTD --}}
        <table style="width: 100%; margin-top: 16px; font-size: 10.5pt;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    {{ $formatTgl($document->issued_date) }}<br>
                    {{ $company->name }}
                    <br><br><br><br>
                    @if ($signatureData)
                        <img src="{{ $signatureData }}" alt="Signature"
                            style="height: 60px; width: auto; display: block; margin-bottom: 4px;">
                    @else
                        <div style="height: 60px;"></div>
                    @endif
                    <strong>{{ $issued->employee_name }}</strong>
                </td>
                <td style="width: 50%; text-align: center; vertical-align: top;">
                    Diterima dan Disetujui Oleh
                    <br><br><br><br><br>
                    <strong>{{ $employee->employee_name }}</strong>
                </td>
            </tr>
        </table>

        {{-- Footer --}}
        <div class="doc-footer">
            Dokumen ini diterbitkan secara resmi oleh {{ $company->name }} &nbsp;|&nbsp;
            Nomor: {{ $document->document_number }} &nbsp;|&nbsp;
            Tanggal: {{ $formatTgl($document->issued_date) }}
        </div>
    </div>
</body>
</html>