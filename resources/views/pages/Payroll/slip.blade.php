{{--
    PDF slip gaji (dompdf). dompdf hanya mendukung CSS 2.1: layout pakai tabel,
    tanpa flex/grid/CSS variable. Warna ditulis sebagai hex di blok token PHP di bawah.
    Angka diambil dari Payroll::salaryBreakdown(), rumus yang sama dengan halaman detail
    dan PayrollService::recalculateNet.
--}}
@php
    $emp = $payroll->employee;
    $b = $payroll->salaryBreakdown();
    $rp = fn($n) => number_format((float) $n, 0, ',', '.');

    // Nama periode dari period_month + period_year (PayrollPeriod::period_label)
    $periodLabel = $payroll->periodLabel();
    $start = \Carbon\Carbon::parse($payroll->period_start)->locale('id');
    $end = \Carbon\Carbon::parse($payroll->period_end)->locale('id');

    $department = optional($emp->department()->wherePivot('is_primary', true)->first())->department_name;
    $location = optional($emp->store()->wherePivot('is_primary', true)->first())->name;
    $position = optional($emp->position()->wherePivot('is_primary', true)->first())->name;
    $bankName = optional($emp->bank)->name;

    // Baris pendapatan: allowance selalu tampil, overtime/reimburse hanya jika ada
    $alwaysShow = ['MEAL ALLOWANCE', 'HOUSE ALLOWANCE', 'TRANSPORT ALLOWANCE', 'BPJS KESEHATAN', 'BPJS KETENAGAKERJAAN'];
    $incomeRows = array_values(array_filter($b['income'], fn($r) => $r['amount'] > 0 || in_array($r['label'], $alwaysShow, true)));
    $deductionRows = array_values(array_filter($b['deduction'], fn($r) => $r['amount'] > 0 || in_array($r['label'], $alwaysShow, true)));
    $totalIncome = $b['earned'] + $b['income_total'];
    $isNegative = (float) $payroll->net_salary < 0;

    $logo = null;
    $fotoPath = $emp->company->foto ?? null;
    if ($fotoPath) {
        $fullPath = storage_path('app/public/' . $fotoPath);
        if (is_file($fullPath)) {
            $logo = 'data:image/' . pathinfo($fullPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($fullPath));
        }
    }

    $c = [
        'ink' => '#1f2433',
        'ink2' => '#4a5163',
        'muted' => '#6b7183',
        'rule' => '#d9dce6',
        'soft' => '#f4f5f9',
        'accent' => '#3f44b5',
        'accentSoft' => '#eceefe',
        'plus' => '#1d6b3a',
        'minus' => '#a3261b',
        'confidential' => '#b42318',
    ];
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Slip gaji {{ $periodLabel }} {{ $emp->employee_name }}</title>
    <style>
        @page {
            margin: 28px 32px;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: {{ $c['ink'] }};
            margin: 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td,
        th {
            vertical-align: top;
            padding: 0;
        }

        .num {
            text-align: right;
            white-space: nowrap;
        }

        .muted {
            color: {{ $c['muted'] }};
        }

        .watermark {
            position: fixed;
            top: 42%;
            left: 8%;
            font-size: 72px;
            font-weight: bold;
            color: #000000;
            opacity: 0.04;
            transform: rotate(-30deg);
            z-index: -1;
        }

        /* Head */
        .head td {
            vertical-align: middle;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }

        .subtitle {
            font-size: 11px;
            color: {{ $c['ink2'] }};
            margin: 2px 0 0;
        }

        .confidential {
            display: inline-block;
            padding: 3px 8px;
            border: 1px solid {{ $c['confidential'] }};
            color: {{ $c['confidential'] }};
            font-weight: bold;
            font-size: 9px;
        }

        .rule {
            border-bottom: 1px solid {{ $c['rule'] }};
            margin: 12px 0;
        }

        /* Facts */
        .facts td {
            padding: 3px 0;
        }

        .facts .k {
            width: 16%;
            color: {{ $c['ink2'] }};
        }

        .facts .v {
            width: 34%;
            font-weight: bold;
            padding-right: 12px;
        }

        /* Attendance strip */
        .strip td {
            background: {{ $c['soft'] }};
            padding: 8px 10px;
            border-right: 2px solid #ffffff;
        }

        .strip .k {
            display: block;
            font-size: 9px;
            color: {{ $c['ink2'] }};
        }

        .strip .v {
            display: block;
            font-size: 13px;
            font-weight: bold;
            margin-top: 2px;
        }

        /* Ledger */
        .ledger {
            border: 1px solid {{ $c['rule'] }};
        }

        .ledger th {
            text-align: left;
            font-size: 11px;
            padding: 7px 10px;
            border-bottom: 1px solid {{ $c['rule'] }};
            background: {{ $c['soft'] }};
        }

        .ledger td {
            padding: 5px 10px;
        }

        .ledger .hint {
            display: block;
            font-size: 8.5px;
            color: {{ $c['muted'] }};
        }

        .ledger tr.total td {
            border-top: 1px solid {{ $c['rule'] }};
            font-weight: bold;
            padding-top: 7px;
            padding-bottom: 7px;
        }

        .ledger tr.earned td {
            font-weight: bold;
        }

        .plus {
            color: {{ $c['plus'] }};
        }

        .minus {
            color: {{ $c['minus'] }};
        }

        /* Net */
        .net td {
            background: {{ $c['accentSoft'] }};
            padding: 12px 14px;
        }

        .net .k {
            font-size: 12px;
            font-weight: bold;
        }

        .net .formula {
            display: block;
            font-size: 9px;
            font-weight: normal;
            color: {{ $c['ink2'] }};
            margin-top: 2px;
        }

        .net .v {
            font-size: 18px;
            font-weight: bold;
            color: {{ $isNegative ? $c['minus'] : $c['accent'] }};
        }

        /* Transfer */
        .transfer td {
            padding: 3px 0;
        }

        .transfer .k {
            width: 22%;
            color: {{ $c['ink2'] }};
        }

        .transfer .v {
            font-weight: bold;
        }

        .foot {
            margin-top: 16px;
            font-size: 8.5px;
            color: {{ $c['muted'] }};
        }
    </style>
</head>

<body>
    <div class="watermark">CONFIDENTIAL</div>

    {{-- Head --}}
    <table class="head">
        <tr>
            <td style="width:60%;">
                @if ($logo)
                    <div style="background:#000000;display:inline-block;padding:5px;margin-bottom:6px;">
                        <img src="{{ $logo }}" alt="" width="64">
                    </div>
                @endif
                <p class="title">Slip gaji {{ $periodLabel }}</p>
                <p class="subtitle">Payslip, {{ $emp->company->name ?? '-' }}</p>
            </td>
            <td class="num" style="width:40%;">
                <span class="confidential">RAHASIA / CONFIDENTIAL</span>
                <p class="subtitle" style="margin-top:6px;">
                    {{ $start->translatedFormat('d M Y') }} sampai {{ $end->translatedFormat('d M Y') }}
                </p>
            </td>
        </tr>
    </table>

    <div class="rule"></div>

    {{-- Employee facts --}}
    <table class="facts">
        <tr>
            <td class="k">Name</td>
            <td class="v">{{ $emp->employee_name }}</td>
            <td class="k">NIP</td>
            <td class="v">{{ $emp->employee_pengenal ?? '-' }}</td>
        </tr>
        <tr>
            <td class="k">Company</td>
            <td class="v">{{ $emp->company->name ?? '-' }}</td>
            <td class="k">Status</td>
            <td class="v">{{ $emp->status_employee ?? '-' }}</td>
        </tr>
        <tr>
            <td class="k">Department</td>
            <td class="v">{{ $department ?: '-' }}</td>
            <td class="k">Location</td>
            <td class="v">{{ $location ?: '-' }}</td>
        </tr>
        <tr>
            <td class="k">Job position</td>
            <td class="v">{{ $position ?: '-' }}</td>
            <td class="k">Grading</td>
            <td class="v">{{ $emp->grading->grading_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="k">NPWP</td>
            <td class="v">{{ $emp->npwp ?: '-' }}</td>
            <td class="k">Email</td>
            <td class="v">{{ $emp->email ?: '-' }}</td>
        </tr>
    </table>

    <div class="rule"></div>

    {{-- Attendance --}}
    <table class="strip">
        <tr>
            @if (!$b['is_dw'])
                <td style="width:50%;"><span class="k">Working days</span><span class="v">{{ $b['working_days'] }} hari</span></td>
                <td style="width:50%;border-right:0;"><span class="k">Attendance</span><span class="v">{{ $b['attendance'] }} hari</span></td>
            @else
                <td style="width:50%;"><span class="k">Attendance</span><span class="v">{{ $b['attendance'] }} hari</span></td>
                <td style="width:50%;border-right:0;"><span class="k">Daily rate</span><span class="v">IDR {{ $rp($payroll->daily_rate) }}</span></td>
            @endif
        </tr>
    </table>

    {{-- Income + deductions --}}
    <table style="margin-top:12px;">
        <tr>
            <td style="width:50%;padding-right:6px;">
                <table class="ledger">
                    <tr>
                        <th colspan="2">Income</th>
                    </tr>
                    @if ($b['is_dw'])
                        <tr class="earned">
                            <td>
                                Daily rate &times; attendance
                                <span class="hint">IDR {{ $rp($payroll->daily_rate) }} &times; {{ $b['attendance'] }} hari</span>
                            </td>
                            <td class="num">{{ $rp($b['earned']) }}</td>
                        </tr>
                    @else
                        <tr>
                            <td>Basic salary</td>
                            <td class="num muted">{{ $rp($payroll->basic_salary) }}</td>
                        </tr>
                        <tr>
                            <td>Position allowance</td>
                            <td class="num muted">{{ $rp($payroll->position_allowance) }}</td>
                        </tr>
                        <tr class="earned">
                            <td>
                                Salary for attendance
                                <span class="hint">{{ $rp($payroll->gross_salary) }} &times; {{ $b['counted_days'] }}/{{ $b['working_days'] }} hari</span>
                            </td>
                            <td class="num">{{ $rp($b['earned']) }}</td>
                        </tr>
                    @endif
                    @foreach ($incomeRows as $row)
                        <tr>
                            <td>{{ ucwords(strtolower($row['label'])) }}</td>
                            <td class="num">{{ $rp($row['amount']) }}</td>
                        </tr>
                    @endforeach
                    <tr class="total">
                        <td>Total income</td>
                        <td class="num plus">IDR {{ $rp($totalIncome) }}</td>
                    </tr>
                </table>
            </td>
            <td style="width:50%;padding-left:6px;">
                <table class="ledger">
                    <tr>
                        <th colspan="2">Deductions</th>
                    </tr>
                    @foreach ($deductionRows as $row)
                        <tr>
                            <td>{{ $row['label'] === 'BPJS KETENAGAKERJAAN' ? 'BPJS Ketenagakerjaan' : ($row['label'] === 'BPJS KESEHATAN' ? 'BPJS Kesehatan' : ucfirst(strtolower($row['label']))) }}</td>
                            <td class="num">{{ $rp($row['amount']) }}</td>
                        </tr>
                    @endforeach
                    <tr class="total">
                        <td>Total deductions</td>
                        <td class="num minus">IDR {{ $rp($b['deduction_total']) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Net --}}
    <table class="net" style="margin-top:12px;">
        <tr>
            <td>
                <span class="k">Net salary</span>
                <span class="formula">Total income {{ $rp($totalIncome) }} dikurangi total deductions {{ $rp($b['deduction_total']) }}</span>
            </td>
            <td class="num" style="vertical-align:middle;"><span class="v">IDR {{ $rp($payroll->net_salary) }}</span></td>
        </tr>
    </table>

    {{-- Transfer --}}
    <table class="transfer" style="margin-top:14px;">
        <tr>
            <td class="k">Transfer ke</td>
            <td class="v">{{ $bankName ?: '-' }} {{ $emp->bank_account_number ?? '-' }}</td>
        </tr>
        <tr>
            <td class="k">Atas nama</td>
            <td class="v">{{ $emp->employee_name }}</td>
        </tr>
    </table>

    <p class="foot">
        Dicetak {{ now()->locale('id')->translatedFormat('d M Y, H:i') }}. Slip ini bersifat rahasia dan hanya untuk karyawan yang bersangkutan.
    </p>
</body>

</html>
