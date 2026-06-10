@extends('layouts.app')
@section('title', 'Detail Payroll')

@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        .section-header h1 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 1.25rem;
        }

        .page-actions {
            display: flex;
            gap: 8px;
        }

        .page-actions .btn {
            height: 36px;
            font-size: .825rem;
            padding: 0 1rem;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            border-radius: .5rem;
        }

        /* Grid layout */
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }

        .detail-grid-full {
            grid-column: span 2;
        }

        /* Card */
        .detail-card {
            background: #fff;
            border-radius: .625rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .07);
            overflow: hidden;
        }

        .detail-card-header {
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
            padding: .75rem 1.25rem;
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .detail-card-icon {
            width: 26px;
            height: 26px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .75rem;
            flex-shrink: 0;
        }

        .detail-card-title {
            font-size: .85rem;
            font-weight: 600;
            color: #334155;
        }

        .detail-card-body {
            padding: 1rem 1.25rem;
        }

        /* Info rows */
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: .5rem 0;
            border-bottom: 1px solid #f8fafc;
            font-size: .82rem;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #64748b;
        }

        .info-value {
            font-weight: 600;
            color: #1e293b;
            text-align: right;
        }

        .info-value.green {
            color: #16a34a;
        }

        .info-value.red {
            color: #dc2626;
        }

        .info-value.blue {
            color: #1d4ed8;
        }

        .info-value.amber {
            color: #d97706;
        }

        .info-value.muted {
            color: #94a3b8;
            font-weight: 400;
        }

        /* Employee header */
        .emp-header {
            background: #fff;
            border-radius: .625rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .07);
            padding: 1.25rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .emp-header-avatar {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            font-size: 1.1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .emp-header-name {
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
        }

        .emp-header-sub {
            font-size: .78rem;
            color: #64748b;
            margin-top: 2px;
        }

        .emp-header-right {
            margin-left: auto;
            text-align: right;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .18rem .6rem;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 700;
        }

        /* Summary box */
        .summary-box {
            background: #f8fafc;
            border-radius: .5rem;
            padding: 1rem 1.25rem;
            margin-top: .75rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: .82rem;
            padding: .3rem 0;
        }

        .summary-row.total {
            border-top: 1px solid #e2e8f0;
            margin-top: .5rem;
            padding-top: .75rem;
            font-weight: 700;
            font-size: .9rem;
        }

        .summary-row.total .summary-value {
            color: #1d4ed8;
            font-size: 1rem;
        }

        /* Component table */
        .comp-table {
            width: 100%;
            font-size: .8rem;
            border-collapse: collapse;
        }

        .comp-table th {
            background: #f8fafc;
            color: #64748b;
            font-size: .68rem;
            font-weight: 700;
            padding: .6rem .75rem;
            text-align: left;
            border-bottom: 1px solid #f1f5f9;
        }

        .comp-table td {
            padding: .6rem .75rem;
            border-bottom: 1px solid #f8fafc;
            color: #334155;
            vertical-align: middle;
        }

        .comp-table tr:last-child td {
            border-bottom: none;
        }

        .num {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        /* Prorate badge */
        .prorate-badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            background: #fff7ed;
            color: #c2410c;
            border: 1px solid #fed7aa;
            border-radius: .375rem;
            padding: .25rem .6rem;
            font-size: .75rem;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }

            .detail-grid-full {
                grid-column: span 1;
            }
        }
    </style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">

            {{-- Header --}}
            <div class="section-header">
                <div>
                    <div style="font-size:.72rem;color:#94a3b8;margin-bottom:3px">
                        Dashboard /
                        <a href="{{ route('payrollperiod.index') }}" style="color:#64748b;text-decoration:none">Payroll
                            Periods</a> /
                        <a href="{{ route('payroll.index', $payroll->payroll_period_id) }}"
                            style="color:#64748b;text-decoration:none">{{ $payroll->period->period_label }}</a> /
                        <span style="color:#1e293b">Detail</span>
                    </div>
                    <h1>Detail Payroll</h1>
                </div>
                <div class="page-actions">
                    @if ($payroll->status === 'draft')
                        <a href="{{ route('payroll.edit', $payroll->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    @endif
                    <a href="{{ route('payroll.index', $payroll->payroll_period_id) }}" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>

            {{-- Employee Header --}}
            <div class="emp-header">
                @php
                    $emp = $payroll->employee;
                    $status = $emp->status_employee ?? '';
                    $initials = collect(explode(' ', $emp->employee_name ?? 'U'))
                        ->take(2)
                        ->map(fn($w) => strtoupper($w[0]))
                        ->implode('');
                    $avatarColors = [
                        'PKWT' => ['bg' => '#eff6ff', 'color' => '#1e40af'],
                        'On Job Training' => ['bg' => '#fdf4ff', 'color' => '#6b21a8'],
                        'DW' => ['bg' => '#fffbeb', 'color' => '#92400e'],
                    ];
                    $ac = $avatarColors[$status] ?? ['bg' => '#f1f5f9', 'color' => '#475569'];
                @endphp

                <div class="emp-header-avatar" style="background:{{ $ac['bg'] }};color:{{ $ac['color'] }}">
                    {{ $initials }}
                </div>
                <div>
                    <div class="emp-header-name">{{ $emp->employee_name ?? '-' }}</div>
                    <div class="emp-header-sub">
                        {{ $emp->employee_pengenal ?? '-' }}
                        &nbsp;·&nbsp; {{ $emp->store->name ?? '-' }}
                        &nbsp;·&nbsp; {{ $emp->position->position_name ?? '-' }}
                    </div>
                </div>
                <div class="emp-header-right">
                    @php
                        $statusBadge = match ($payroll->status) {
                            'draft' => ['bg' => '#f1f5f9', 'color' => '#475569', 'label' => 'Draft'],
                            'approved' => ['bg' => '#f0fdf4', 'color' => '#166534', 'label' => 'Approved'],
                            'paid' => ['bg' => '#eff6ff', 'color' => '#1e40af', 'label' => 'Paid'],
                            default => ['bg' => '#f1f5f9', 'color' => '#475569', 'label' => ucfirst($payroll->status)],
                        };
                    @endphp
                    <span class="status-badge"
                        style="background:{{ $statusBadge['bg'] }};color:{{ $statusBadge['color'] }}">
                        {{ $statusBadge['label'] }}
                    </span>
                    <div style="font-size:.72rem;color:#94a3b8;margin-top:4px">
                        {{ $payroll->period->period_label }}
                    </div>
                    @if ($payroll->is_prorate)
                        <div style="margin-top:4px">
                            <span class="prorate-badge">
                                <i class="fas fa-calculator"></i>
                                Prorate {{ round($payroll->prorate_ratio * 100, 2) }}%
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="detail-grid">

                {{-- Info Employee --}}
                <div class="detail-card">
                    <div class="detail-card-header">
                        <div class="detail-card-icon" style="background:#eff6ff;color:#1d4ed8"><i class="fas fa-user"></i>
                        </div>
                        <span class="detail-card-title">Info Employee</span>
                    </div>
                    <div class="detail-card-body">
                        <div class="info-row">
                            <span class="info-label">Status Karyawan</span>
                            <span class="info-value">{{ $emp->status_employee ?? '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Bank</span>
                            <span class="info-value">{{ $emp->bank->bank_name ?? '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">No. Rekening</span>
                            <span class="info-value">{{ $emp->bank_account_number ?? '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Join Date</span>
                            <span
                                class="info-value">{{ $emp->join_date ? \Carbon\Carbon::parse($emp->join_date)->format('d/m/Y') : '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Periode</span>
                            <span class="info-value">
                                {{ $payroll->period_start->format('d/m/Y') }} —
                                {{ $payroll->period_end->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Info Absensi --}}
                <div class="detail-card">
                    <div class="detail-card-header">
                        <div class="detail-card-icon" style="background:#f0fdf4;color:#16a34a"><i
                                class="fas fa-calendar-check"></i></div>
                        <span class="detail-card-title">Info Absensi</span>
                    </div>
                    <div class="detail-card-body">
                        <div class="info-row">
                            <span class="info-label">Working Days</span>
                            <span class="info-value">{{ $payroll->working_days }} hari</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Attendance Days</span>
                            <span class="info-value green">{{ $payroll->attendance_days }} hari</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Absent Days</span>
                            <span class="info-value {{ $payroll->absent_days > 0 ? 'red' : 'muted' }}">
                                {{ $payroll->absent_days }} hari
                            </span>
                        </div>
                        @if ($payroll->is_prorate)
                            <div class="info-row">
                                <span class="info-label">Prorate Days</span>
                                <span class="info-value amber">{{ $payroll->prorate_days }} hari</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Prorate Ratio</span>
                                <span class="info-value amber">{{ round($payroll->prorate_ratio * 100, 2) }}%</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Salary Breakdown --}}
                <div class="detail-card">
                    <div class="detail-card-header">
                        <div class="detail-card-icon" style="background:#fdf4ff;color:#6b21a8"><i
                                class="fas fa-money-bill"></i></div>
                        <span class="detail-card-title">Salary Breakdown</span>
                    </div>
                    <div class="detail-card-body">
                        @if (strtoupper($emp->status_employee) === 'DW')
                            <div class="info-row">
                                <span class="info-label">Daily Rate</span>
                                <span class="info-value">Rp {{ number_format($payroll->daily_rate, 0, ',', '.') }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Attendance Days</span>
                                <span class="info-value">{{ $payroll->attendance_days }} hari</span>
                            </div>
                        @else
                            <div class="info-row">
                                <span class="info-label">Basic Salary</span>
                                <span class="info-value">Rp {{ number_format($payroll->basic_salary, 0, ',', '.') }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Position Allowance</span>
                                <span class="info-value">Rp
                                    {{ number_format($payroll->position_allowance, 0, ',', '.') }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Allowance</span>
                                <span class="info-value">Rp {{ number_format($payroll->allowance, 0, ',', '.') }}</span>
                            </div>
                            @if ($payroll->is_prorate)
                                <div class="info-row">
                                    <span class="info-label">Prorate Ratio</span>
                                    <span class="info-value amber">× {{ round($payroll->prorate_ratio * 100, 2) }}%</span>
                                </div>
                            @endif
                        @endif
                        <div class="summary-box">
                            <div class="summary-row">
                                <span style="color:#64748b">Gross Salary</span>
                                <span style="font-weight:600">Rp
                                    {{ number_format($payroll->gross_salary, 0, ',', '.') }}</span>
                            </div>
                            @if ($payroll->overtime_amount > 0)
                                <div class="summary-row">
                                    <span style="color:#64748b">+ Overtime</span>
                                    <span style="color:#16a34a">Rp
                                        {{ number_format($payroll->overtime_amount, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            @if ($payroll->reimburse_amount > 0)
                                <div class="summary-row">
                                    <span style="color:#64748b">+ Reimburse</span>
                                    <span style="color:#16a34a">Rp
                                        {{ number_format($payroll->reimburse_amount, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Komponen BPJS & Potongan --}}
                <div class="detail-card">
                    <div class="detail-card-header">
                        <div class="detail-card-icon" style="background:#fef2f2;color:#dc2626"><i
                                class="fas fa-minus-circle"></i></div>
                        <span class="detail-card-title">Komponen & Potongan</span>
                    </div>
                    <div class="detail-card-body">
                        <table class="comp-table">
                            <thead>
                                <tr>
                                    <th>Komponen</th>
                                    <th>Tipe</th>
                                    <th class="num">Amount</th>
                                    <th>Beban</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payroll->details as $detail)
                                    <tr>
                                        <td>{{ $detail->component->component_name ?? '-' }}</td>
                                        <td>
                                            @if ($detail->type === 'Income')
                                                <span class="status-badge"
                                                    style="background:#f0fdf4;color:#166534;font-size:.65rem">Income</span>
                                            @else
                                                <span class="status-badge"
                                                    style="background:#fef2f2;color:#991b1b;font-size:.65rem">Deduction</span>
                                            @endif
                                        </td>
                                        <td class="num">Rp {{ number_format($detail->amount, 0, ',', '.') }}</td>
                                        <td>
                                            @if ($detail->component->is_employer_burden ?? false)
                                                <span style="font-size:.7rem;color:#d97706">Perusahaan</span>
                                            @else
                                                <span style="font-size:.7rem;color:#64748b">Karyawan</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="text-align:center;color:#94a3b8">Tidak ada komponen</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Net Salary Summary --}}
                <div class="detail-card detail-grid-full">
                    <div class="detail-card-header">
                        <div class="detail-card-icon" style="background:#eff6ff;color:#1d4ed8"><i
                                class="fas fa-calculator"></i></div>
                        <span class="detail-card-title">Ringkasan Gaji</span>
                    </div>
                    <div class="detail-card-body">
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem">
                            <div>
                                <div style="font-size:.7rem;color:#94a3b8;margin-bottom:4px">Gross Salary</div>
                                <div style="font-size:1.1rem;font-weight:600;color:#1e293b">
                                    Rp {{ number_format($payroll->gross_salary, 0, ',', '.') }}
                                </div>
                            </div>
                            <div>
                                <div style="font-size:.7rem;color:#94a3b8;margin-bottom:4px">Total Income Tambahan</div>
                                <div style="font-size:1.1rem;font-weight:600;color:#16a34a">
                                    + Rp {{ number_format($payroll->total_income, 0, ',', '.') }}
                                </div>
                            </div>
                            <div>
                                <div style="font-size:.7rem;color:#94a3b8;margin-bottom:4px">Total Potongan</div>
                                <div style="font-size:1.1rem;font-weight:600;color:#dc2626">
                                    - Rp {{ number_format($payroll->total_deduction, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        <div
                            style="background:#eff6ff;border-radius:.5rem;padding:1rem 1.25rem;margin-top:1rem;display:flex;justify-content:space-between;align-items:center">
                            <div>
                                <div style="font-size:.72rem;color:#3b82f6;font-weight:600">NET SALARY</div>
                                <div style="font-size:.72rem;color:#64748b;margin-top:2px">
                                    Gross + Income - Potongan Karyawan
                                </div>
                            </div>
                            <div style="font-size:1.5rem;font-weight:700;color:#1d4ed8">
                                Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}
                            </div>
                        </div>

                        @if ($payroll->approved_at)
                            <div style="margin-top:.75rem;font-size:.75rem;color:#64748b;text-align:right">
                                Approved by {{ $payroll->approvedBy->name ?? '-' }}
                                pada {{ $payroll->approved_at->format('d/m/Y H:i') }}
                            </div>
                        @endif
                        @if ($payroll->note)
                            <div
                                style="margin-top:.5rem;background:#fffbeb;border-radius:.375rem;padding:.5rem .75rem;font-size:.78rem;color:#92400e">
                                <i class="fas fa-note-sticky"></i> {{ $payroll->note }}
                            </div>
                        @endif
                    </div>
                </div>

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
