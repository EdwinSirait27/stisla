@extends('layouts.app')
@section('title', 'Detail Payroll')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand · redesign: full
         * paper: oklch(98.4% 0.004 265) · accent: oklch(61% 0.17 276) · display: Nunito · body: Nunito
         * nav: kept (Stisla shell) · signature: the net salary calculation, read top to bottom like a slip · states: full, negative net, recalculation mismatch, no components
         * axes: light/rounded-sans/chromatic-indigo · posture: app · contrast: pass · v2.0.0 */
        /* Hallmark · pre-emit critique: P4 H4 E4 S4 R4 V4 · weakest: E, the employee facts are many, answered with one compact definition list in the side column */

        :root {
            --pr-paper: oklch(98.4% 0.004 265);
            --pr-panel: oklch(100% 0 0);
            --pr-paper-2: oklch(96.6% 0.006 265);
            --pr-ink: oklch(27% 0.03 265);
            --pr-ink-2: oklch(42% 0.03 265);
            --pr-muted: oklch(55% 0.025 265);
            --pr-rule: oklch(91.5% 0.008 265);
            --pr-accent: oklch(61% 0.17 276);
            --pr-accent-strong: oklch(52% 0.18 276);
            --pr-accent-deep: oklch(45% 0.17 276);
            --pr-accent-soft: oklch(95% 0.03 276);
            --pr-accent-ink: oklch(100% 0 0);
            --pr-focus: oklch(61% 0.17 276);
            --pr-success: oklch(50% 0.12 150);
            --pr-success-soft: oklch(96% 0.035 150);
            --pr-warn: oklch(52% 0.12 70);
            --pr-warn-soft: oklch(96.5% 0.045 85);
            --pr-error: oklch(52% 0.19 27);
            --pr-error-soft: oklch(96.5% 0.025 27);
            --pr-font: "Nunito", "Segoe UI", system-ui, sans-serif;

            --pr-text-xs: 0.75rem;
            --pr-text-sm: 0.8125rem;
            --pr-text-md: 0.9375rem;
            --pr-text-lg: 1.125rem;
            --pr-text-xl: 1.35rem;
            --pr-text-2xl: 1.6rem;
            --pr-space-2xs: 4px;
            --pr-space-xs: 8px;
            --pr-space-sm: 12px;
            --pr-space-md: 16px;
            --pr-space-lg: 24px;
            --pr-radius: 8px;
            --pr-radius-sm: 6px;
            --pr-pill: 999px;
            --pr-dur-micro: 120ms;
            --pr-ease: cubic-bezier(0.16, 1, 0.3, 1);
        }

        html,
        body {
            overflow-x: clip;
        }

        .pr {
            font-family: var(--pr-font);
            color: var(--pr-ink);
        }

        .pr [hidden] {
            display: none !important;
        }

        .pr :focus-visible {
            outline: 2px solid var(--pr-focus);
            outline-offset: 2px;
        }

        .pr .num {
            text-align: right;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        /* ── Head ── */
        .pr-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: var(--pr-space-md);
            margin-bottom: var(--pr-space-lg);
            padding: var(--pr-space-md) var(--pr-space-lg);
            background: var(--pr-panel);
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius);
            position: relative;
            z-index: 2;
        }

        .pr-crumbs {
            display: flex;
            flex-wrap: wrap;
            gap: var(--pr-space-xs);
            font-size: var(--pr-text-sm);
            color: var(--pr-muted);
            margin: 0 0 var(--pr-space-2xs);
            padding: 0;
            list-style: none;
        }

        .pr-crumbs li+li::before {
            content: "/";
            margin-right: var(--pr-space-xs);
            color: var(--pr-rule);
        }

        .pr-crumbs a {
            color: var(--pr-ink-2);
            text-decoration: none;
        }

        .pr-crumbs a:hover {
            color: var(--pr-accent-strong);
            text-decoration: underline;
        }

        .pr-title {
            font-size: var(--pr-text-2xl);
            font-weight: 700;
            line-height: 1.15;
            color: var(--pr-ink);
            margin: 0;
            overflow-wrap: break-word;
            min-width: 0;
            text-wrap: balance;
        }

        .pr-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: var(--pr-space-xs) var(--pr-space-sm);
            margin-top: var(--pr-space-xs);
            font-size: var(--pr-text-sm);
            color: var(--pr-ink-2);
        }

        .pr-actions {
            display: flex;
            flex-wrap: wrap;
            gap: var(--pr-space-xs);
        }

        .pr-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--pr-space-xs);
            min-height: 36px;
            padding: 0 var(--pr-space-md);
            border-radius: var(--pr-radius-sm);
            border: 1px solid var(--pr-rule);
            background: var(--pr-panel);
            color: var(--pr-ink);
            font-family: var(--pr-font);
            font-size: var(--pr-text-sm);
            font-weight: 600;
            line-height: 1;
            text-decoration: none;
            white-space: nowrap;
            cursor: pointer;
            transition: background-color var(--pr-dur-micro) var(--pr-ease), border-color var(--pr-dur-micro) var(--pr-ease), color var(--pr-dur-micro) var(--pr-ease);
        }

        .pr-btn:hover {
            background: var(--pr-paper-2);
            color: var(--pr-ink);
            text-decoration: none;
        }

        .pr-btn:active {
            background: var(--pr-rule);
        }

        .pr-btn i {
            font-size: .85em;
        }

        .pr-btn-primary {
            background: var(--pr-accent-strong);
            border-color: var(--pr-accent-strong);
            color: var(--pr-accent-ink);
        }

        .pr-btn-primary:hover,
        .pr-btn-primary:active {
            background: var(--pr-accent-deep);
            border-color: var(--pr-accent-deep);
            color: var(--pr-accent-ink);
        }

        /* Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: var(--pr-pill);
            font-size: var(--pr-text-xs);
            font-weight: 700;
            white-space: nowrap;
            background: var(--pr-paper-2);
            color: var(--pr-ink-2);
        }

        .status-badge.is-approved {
            background: var(--pr-success-soft);
            color: var(--pr-success);
        }

        .status-badge.is-paid {
            background: var(--pr-accent-soft);
            color: var(--pr-accent-strong);
        }

        .status-badge.is-prorate {
            background: var(--pr-warn-soft);
            color: var(--pr-warn);
        }

        .status-badge.is-type {
            background: transparent;
            border: 1px solid var(--pr-rule);
            color: var(--pr-ink-2);
        }

        /* ── Notice ── */
        .pr-notice {
            display: flex;
            gap: var(--pr-space-sm);
            align-items: flex-start;
            padding: var(--pr-space-sm) var(--pr-space-lg);
            margin-bottom: var(--pr-space-lg);
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius);
            font-size: var(--pr-text-sm);
            color: var(--pr-ink);
        }

        .pr-notice.is-error {
            background: var(--pr-error-soft);
        }

        .pr-notice.is-warn {
            background: var(--pr-warn-soft);
        }

        .pr-notice strong {
            display: block;
            margin-bottom: 2px;
        }

        .pr-notice.is-error strong {
            color: var(--pr-error);
        }

        .pr-notice.is-warn strong {
            color: var(--pr-warn);
        }

        .pr-notice>i {
            margin-top: var(--pr-space-2xs);
        }

        .pr-notice.is-error>i {
            color: var(--pr-error);
        }

        .pr-notice.is-warn>i {
            color: var(--pr-warn);
        }

        /* ── Layout ── */
        .pr-layout {
            display: grid;
            grid-template-columns: minmax(0, 8fr) minmax(0, 4fr);
            gap: var(--pr-space-lg);
            align-items: start;
        }

        .pr-panel {
            background: var(--pr-panel);
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius);
            margin-bottom: var(--pr-space-lg);
        }

        .pr-panel-head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: var(--pr-space-xs);
            padding: var(--pr-space-md) var(--pr-space-lg);
            border-bottom: 1px solid var(--pr-rule);
        }

        .pr-panel-title {
            font-size: var(--pr-text-md);
            font-weight: 700;
            color: var(--pr-ink);
            margin: 0;
        }

        .pr-panel-sub {
            font-size: var(--pr-text-sm);
            color: var(--pr-muted);
        }

        /* ── Calculation (signature) ── */
        .pr-calc {
            width: 100%;
            border-collapse: collapse;
            font-size: var(--pr-text-md);
        }

        .pr-calc th,
        .pr-calc td {
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-bottom: 1px solid var(--pr-rule);
            vertical-align: top;
        }

        .pr-calc th {
            font-weight: 400;
            text-align: left;
            color: var(--pr-ink);
        }

        .pr-calc .pr-sign {
            width: 1%;
            padding-right: 0;
            color: var(--pr-muted);
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        .pr-calc .pr-hint {
            display: block;
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
            margin-top: 2px;
        }

        .pr-calc-panel {
            overflow: hidden;
        }

        .pr-calc tr.is-group td {
            border-bottom: 0;
        }

        .pr-calc tr.is-group th {
            padding-top: var(--pr-space-md);
            padding-bottom: var(--pr-space-2xs);
            border-bottom: 0;
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-ink-2);
        }

        .pr-calc tr.is-item th,
        .pr-calc tr.is-item td {
            padding-top: var(--pr-space-xs);
            padding-bottom: var(--pr-space-xs);
            border-bottom: 0;
            font-size: var(--pr-text-sm);
        }

        .pr-calc tr.is-item.is-zero th,
        .pr-calc tr.is-item.is-zero td {
            color: var(--pr-muted);
        }

        .pr-calc tr.is-subtotal th,
        .pr-calc tr.is-subtotal td {
            font-weight: 700;
            border-top: 1px solid var(--pr-rule);
        }

        .pr-calc tr.is-earned th,
        .pr-calc tr.is-earned td {
            background: var(--pr-paper);
            font-weight: 700;
        }

        .pr-calc .is-plus {
            color: var(--pr-success);
        }

        .pr-calc .is-minus {
            color: var(--pr-error);
        }

        .pr-calc tr.is-net th,
        .pr-calc tr.is-net td {
            border-bottom: 0;
            padding-top: var(--pr-space-md);
            padding-bottom: var(--pr-space-md);
            background: var(--pr-accent-soft);
            font-size: var(--pr-text-lg);
            font-weight: 700;
        }

        .pr-calc tr.is-net td.num {
            font-size: var(--pr-text-xl);
            color: var(--pr-accent-strong);
        }

        .pr-calc tr.is-net.is-negative td.num {
            color: var(--pr-error);
        }

        .pr-meter {
            display: block;
            width: 160px;
            max-width: 100%;
            height: 6px;
            margin-top: var(--pr-space-xs);
            border-radius: var(--pr-pill);
            background: var(--pr-paper-2);
            overflow: hidden;
        }

        .pr-meter span {
            display: block;
            height: 100%;
            background: var(--pr-success);
        }

        /* ── Component table ── */
        .pr-table {
            width: 100%;
            border-collapse: collapse;
            font-size: var(--pr-text-sm);
        }

        .pr-table th {
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-bottom: 1px solid var(--pr-rule);
            text-align: left;
            font-size: var(--pr-text-xs);
            font-weight: 700;
            color: var(--pr-ink-2);
            white-space: nowrap;
        }

        .pr-table td {
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-bottom: 1px solid var(--pr-rule);
            color: var(--pr-ink);
        }

        .pr-table tr:last-child td {
            border-bottom: 0;
        }

        .pr-table .pr-empty-row {
            text-align: center;
            color: var(--pr-muted);
        }

        .pr-tag {
            font-size: var(--pr-text-xs);
            font-weight: 700;
        }

        .pr-tag.is-income {
            color: var(--pr-success);
        }

        .pr-tag.is-deduction {
            color: var(--pr-error);
        }

        .pr-burden {
            font-size: var(--pr-text-xs);
            color: var(--pr-ink-2);
        }

        .pr-burden.is-company {
            color: var(--pr-warn);
            font-weight: 700;
        }

        /* ── Side ── */
        .pr-net {
            padding: var(--pr-space-lg);
        }

        .pr-net-label {
            font-size: var(--pr-text-sm);
            font-weight: 600;
            color: var(--pr-ink-2);
        }

        .pr-net-value {
            display: block;
            margin-top: var(--pr-space-2xs);
            font-size: var(--pr-text-2xl);
            font-weight: 700;
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
            color: var(--pr-accent-strong);
            overflow-wrap: break-word;
        }

        .pr-net-value.is-negative {
            color: var(--pr-error);
        }

        .pr-net-value small {
            font-size: var(--pr-text-md);
            color: var(--pr-muted);
            margin-right: 2px;
        }

        .pr-net-foot {
            margin-top: var(--pr-space-md);
            padding-top: var(--pr-space-sm);
            border-top: 1px solid var(--pr-rule);
            font-size: var(--pr-text-sm);
            color: var(--pr-ink-2);
        }

        .pr-note {
            margin-top: var(--pr-space-sm);
            padding: var(--pr-space-xs) var(--pr-space-sm);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-warn-soft);
            font-size: var(--pr-text-sm);
            color: var(--pr-ink);
        }

        .pr-facts {
            margin: 0;
            padding: var(--pr-space-xs) var(--pr-space-lg) var(--pr-space-md);
        }

        .pr-facts>div {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(0, 3fr);
            gap: var(--pr-space-sm);
            padding: var(--pr-space-xs) 0;
            border-bottom: 1px solid var(--pr-rule);
        }

        .pr-facts>div:last-child {
            border-bottom: 0;
        }

        .pr-facts dt {
            font-size: var(--pr-text-sm);
            font-weight: 400;
            color: var(--pr-ink-2);
        }

        .pr-facts dd {
            margin: 0;
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-ink);
            text-align: right;
            overflow-wrap: anywhere;
        }

        .pr-facts dd.is-empty {
            font-weight: 400;
            color: var(--pr-muted);
        }

        @media (max-width: 75rem) {
            .pr-layout {
                grid-template-columns: minmax(0, 1fr);
            }

            .pr-side {
                order: -1;
            }
        }

        @media (max-width: 40rem) {

            .pr-head,
            .pr-notice {
                padding: var(--pr-space-md);
            }

            .pr-panel-head,
            .pr-calc th,
            .pr-calc td,
            .pr-table th,
            .pr-table td,
            .pr-net,
            .pr-facts {
                padding-left: var(--pr-space-md);
                padding-right: var(--pr-space-md);
            }

            .pr-actions,
            .pr-actions .pr-btn {
                width: 100%;
            }

            .pr-actions .pr-btn {
                flex: 1 1 0;
                min-height: 44px;
                font-size: 16px;
            }

            .pr-table-wrap {
                overflow-x: auto;
            }

            .pr-calc {
                table-layout: fixed;
                font-size: var(--pr-text-sm);
            }

            .pr-calc .pr-sign {
                width: 28px;
                padding-left: var(--pr-space-sm);
            }

            .pr-calc td.num {
                width: 46%;
                padding-left: var(--pr-space-xs);
            }

            .pr-calc tr.is-net td.num {
                font-size: var(--pr-text-lg);
            }

            .pr-meter {
                width: 100%;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .pr *,
            .pr *::before,
            .pr *::after {
                transition-duration: 0.01ms !important;
            }
        }
    </style>
@endpush
@section('main')
    @php
        $emp = $payroll->employee;
        $b = $breakdown;
        $rp = fn($n) => number_format((float) $n, 0, ',', '.');
        $statusClass = ['draft' => 'is-draft', 'approved' => 'is-approved', 'paid' => 'is-paid'][$payroll->status] ?? 'is-draft';
        $typeLabel = ['On Job Training' => 'OJT', 'DW' => 'Daily Worker'][$emp->status_employee ?? ''] ?? ($emp->status_employee ?? '-');
        $isNegative = (float) $payroll->net_salary < 0;
        $attPct = $b['working_days'] > 0 ? min(100, round($b['counted_days'] / $b['working_days'] * 100, 1)) : 0;
        $positionName = $emp->position?->first()?->name;
        $facts = [
            'Company' => $emp->company?->name,
            'Department' => $emp->department?->first()?->department_name,
            'Location' => $emp->store?->first()?->name,
            'Position' => $positionName,
            'Grading' => $emp->grading?->grading_name,
            'Tipe karyawan' => $emp->status_employee,
            'Join date' => $emp->join_date ? \Carbon\Carbon::parse($emp->join_date)->format('d M Y') : null,
            'NPWP' => $emp->npwp,
            'Bank' => $emp->bank?->name,
            'No. rekening' => $emp->bank_account_number,
        ];
    @endphp
    <div class="main-content pr">
        <section class="section">

            {{-- Head --}}
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs" aria-label="Breadcrumb">
                        <li>Dashboard</li>
                        <li><a href="{{ route('payrollperiod.index') }}">Payroll Periods</a></li>
                        <li><a href="{{ route('payroll.index', $payroll->payroll_period_id) }}">{{ $payroll->period->period_label }}</a></li>
                        <li aria-current="page">Detail</li>
                    </ol>
                    <h1 class="pr-title">{{ $emp->employee_name ?? '-' }}</h1>
                    <div class="pr-meta">
                        <span>{{ $emp->employee_pengenal ?? '-' }}</span>
                        @if ($positionName)
                            <span>{{ $positionName }}</span>
                        @endif
                        <span class="status-badge {{ $statusClass }}">{{ ucfirst($payroll->status) }}</span>
                        <span class="status-badge is-type">{{ $typeLabel }}</span>
                        @if ($payroll->is_prorate)
                            <span class="status-badge is-prorate">Prorate {{ round($payroll->prorate_ratio * 100, 2) }}%</span>
                        @endif
                    </div>
                </div>
                <div class="pr-actions">
                    <a href="{{ route('payroll.index', $payroll->payroll_period_id) }}" class="pr-btn">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali
                    </a>
                    @if ($payroll->status === 'draft' && auth()->user()->can('payroll.update'))
                        <a href="{{ route('payroll.edit', $payroll->id) }}" class="pr-btn pr-btn-primary">
                            <i class="fas fa-edit" aria-hidden="true"></i> Edit payroll
                        </a>
                    @endif
                </div>
            </header>

            {{-- Notices --}}
            @if ($isNegative)
                <div class="pr-notice is-error" role="status">
                    <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                    <div>
                        @if ($payroll->status === 'draft')
                            <strong>Net salary minus, payroll ini tidak bisa di-approve</strong>
                        @else
                            <strong>Payroll ini sudah {{ $payroll->status }} dengan net salary minus</strong>
                        @endif
                        @if ($b['attendance'] === 0)
                            Attendance 0 hari, jadi gaji sesuai kehadiran Rp 0 sementara potongan tetap jalan.
                            @if ($payroll->status === 'draft')
                                Sync data fingerprint periode ini, hapus draft, lalu generate ulang.
                            @endif
                        @else
                            Potongan lebih besar dari gaji dan tunjangan. Cek attendance dan potongan sebelum approve.
                        @endif
                    </div>
                </div>
            @endif
            @if ($b['mismatch'])
                <div class="pr-notice is-warn" role="status">
                    <i class="fas fa-info-circle" aria-hidden="true"></i>
                    <div>
                        <strong>Net tersimpan berbeda dengan hasil hitung ulang</strong>
                        Tersimpan Rp {{ $rp($payroll->net_salary) }}, hitung ulang dari data di bawah Rp {{ $rp($b['computed_net']) }}. Data payroll mungkin berubah setelah net terakhir dihitung.
                    </div>
                </div>
            @endif

            <div class="pr-layout">
                {{-- Main --}}
                <div>
                    <section class="pr-panel pr-calc-panel" aria-labelledby="calc-title">
                        <div class="pr-panel-head">
                            <h2 class="pr-panel-title" id="calc-title">Perhitungan net salary</h2>
                            <span class="pr-panel-sub">{{ $payroll->period_start->format('d M Y') }} sampai {{ $payroll->period_end->format('d M Y') }}</span>
                        </div>
                        <table class="pr-calc">
                            <tbody>
                                @if ($b['is_dw'])
                                    <tr>
                                        <td class="pr-sign"></td>
                                        <th scope="row">Daily rate</th>
                                        <td class="num">Rp {{ $rp($payroll->daily_rate) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="pr-sign">&times;</td>
                                        <th scope="row">
                                            Hari hadir
                                            <span class="pr-hint">Daily worker dibayar per hari hadir</span>
                                        </th>
                                        <td class="num">{{ $b['attendance'] }} hari</td>
                                    </tr>
                                @else
                                    <tr class="is-item">
                                        <td class="pr-sign"></td>
                                        <th scope="row">Basic salary</th>
                                        <td class="num">Rp {{ $rp($payroll->basic_salary) }}</td>
                                    </tr>
                                    <tr class="is-item">
                                        <td class="pr-sign">+</td>
                                        <th scope="row">Position allowance</th>
                                        <td class="num">Rp {{ $rp($payroll->position_allowance) }}</td>
                                    </tr>
                                    <tr class="is-subtotal">
                                        <td class="pr-sign"></td>
                                        <th scope="row">Gross salary (sebulan penuh)</th>
                                        <td class="num">Rp {{ $rp($payroll->gross_salary) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="pr-sign">&times;</td>
                                        <th scope="row">
                                            Kehadiran {{ $b['counted_days'] }} dari {{ $b['working_days'] }} hari kerja
                                            <span class="pr-meter" aria-hidden="true"><span style="width: {{ $attPct }}%"></span></span>
                                            @if ($b['capped'])
                                                <span class="pr-hint">Attendance {{ $b['attendance'] }} hari dibatasi ke {{ $b['working_days'] }} hari kerja, jadi gaji pokok maksimal 100%</span>
                                            @endif
                                        </th>
                                        <td class="num">{{ $attPct }}%</td>
                                    </tr>
                                @endif
                                <tr class="is-earned">
                                    <td class="pr-sign">=</td>
                                    <th scope="row">Gaji sesuai kehadiran</th>
                                    <td class="num">Rp {{ $rp($b['earned']) }}</td>
                                </tr>

                                <tr class="is-group">
                                    <td class="pr-sign is-plus">+</td>
                                    <th scope="row" colspan="2">Tunjangan dan tambahan</th>
                                </tr>
                                @foreach ($b['income'] as $row)
                                    <tr class="is-item {{ $row['amount'] == 0 ? 'is-zero' : '' }}">
                                        <td class="pr-sign"></td>
                                        <th scope="row">{{ $row['label'] }}</th>
                                        <td class="num">Rp {{ $rp($row['amount']) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="is-subtotal">
                                    <td class="pr-sign"></td>
                                    <th scope="row">Total tambahan</th>
                                    <td class="num is-plus">Rp {{ $rp($b['income_total']) }}</td>
                                </tr>

                                <tr class="is-group">
                                    <td class="pr-sign is-minus">&minus;</td>
                                    <th scope="row" colspan="2">Potongan karyawan</th>
                                </tr>
                                @foreach ($b['deduction'] as $row)
                                    <tr class="is-item {{ $row['amount'] == 0 ? 'is-zero' : '' }}">
                                        <td class="pr-sign"></td>
                                        <th scope="row">{{ $row['label'] }}</th>
                                        <td class="num">Rp {{ $rp($row['amount']) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="is-subtotal">
                                    <td class="pr-sign"></td>
                                    <th scope="row">Total potongan</th>
                                    <td class="num is-minus">Rp {{ $rp($b['deduction_total']) }}</td>
                                </tr>

                                <tr class="is-net {{ $isNegative ? 'is-negative' : '' }}">
                                    <td class="pr-sign">=</td>
                                    <th scope="row">Net salary</th>
                                    <td class="num">Rp {{ $rp($payroll->net_salary) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </section>

                    <section class="pr-panel" aria-labelledby="comp-title">
                        <div class="pr-panel-head">
                            <h2 class="pr-panel-title" id="comp-title">Komponen payroll</h2>
                            <span class="pr-panel-sub">{{ $payroll->details->count() }} komponen dari data gaji</span>
                        </div>
                        <div class="pr-table-wrap">
                            <table class="pr-table">
                                <thead>
                                    <tr>
                                        <th>Component</th>
                                        <th>Type</th>
                                        <th class="num">Amount</th>
                                        <th>Ditanggung</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($payroll->details as $detail)
                                        <tr>
                                            <td>{{ $detail->component->component_name ?? '-' }}</td>
                                            <td>
                                                @if ($detail->type === 'Income')
                                                    <span class="pr-tag is-income">Income</span>
                                                @else
                                                    <span class="pr-tag is-deduction">Deduction</span>
                                                @endif
                                            </td>
                                            <td class="num">Rp {{ $rp($detail->amount) }}</td>
                                            <td>
                                                @if ($detail->component->is_employer_burden ?? false)
                                                    <span class="pr-burden is-company">Perusahaan, tidak masuk net</span>
                                                @else
                                                    <span class="pr-burden">Karyawan</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="pr-empty-row">
                                                Tidak ada komponen. Tunjangan dan BPJS diambil dari data gaji karyawan saat generate.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

                {{-- Side --}}
                <aside class="pr-side">
                    <section class="pr-panel pr-net" aria-labelledby="net-title">
                        <h2 class="pr-net-label" id="net-title">Net salary {{ $payroll->period->period_label }}</h2>
                        <span class="pr-net-value {{ $isNegative ? 'is-negative' : '' }}"><small>Rp</small>{{ $rp($payroll->net_salary) }}</span>
                        <div class="pr-net-foot">
                            @if ($payroll->approved_at)
                                Approved oleh {{ $payroll->approvedBy?->employee_name ?? '-' }}
                                pada {{ $payroll->approved_at->format('d M Y, H:i') }}
                            @elseif ($payroll->status === 'draft')
                                Belum di-approve.
                            @endif
                            @if ($payroll->note)
                                <div class="pr-note">{{ $payroll->note }}</div>
                            @endif
                        </div>
                    </section>

                    <section class="pr-panel" aria-labelledby="emp-title">
                        <div class="pr-panel-head">
                            <h2 class="pr-panel-title" id="emp-title">Data karyawan</h2>
                        </div>
                        <dl class="pr-facts">
                            @foreach ($facts as $label => $value)
                                <div>
                                    <dt>{{ $label }}</dt>
                                    <dd class="{{ filled($value) ? '' : 'is-empty' }}">{{ filled($value) ? $value : 'Belum diisi' }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </section>
                </aside>
            </div>
        </section>
    </div>
@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#1d4ed8',
                    timer: 3000,
                    timerProgressBar: true,
                });
            @endif
        });
    </script>
@endpush
