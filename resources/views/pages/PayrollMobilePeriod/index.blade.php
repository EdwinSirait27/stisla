@extends('layouts.app')
@section('title', 'Payroll Absensi Mobile')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand · redesign: full
         * paper: oklch(98.4% 0.004 265) · accent: oklch(61% 0.17 276) · display: Nunito · body: Nunito
         * nav: kept (Stisla shell) · signature: the running-period card, payroll progress beside the next step it unlocks · states: loading, empty, full, duplicate period
         * axes: light/rounded-sans/chromatic-indigo · posture: app · contrast: pass · v2.0.0 */
        /* Hallmark · pre-emit critique: P4 H4 E4 S4 R4 V4 · weakest: P, the close and lock rules lived only in the controller, answered with a next-step line per period */

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
            --pr-shadow-pressed: oklch(27% 0.03 265 / .12);
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

        .pr-lede {
            margin: var(--pr-space-xs) 0 0;
            line-height: 1.5;
            font-size: var(--pr-text-sm);
            color: var(--pr-ink-2);
            max-width: 72ch;
        }

        /* ── Buttons ── */
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

        .pr-btn:disabled {
            opacity: .55;
            cursor: not-allowed;
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

        .pr-btn-primary:disabled:hover {
            background: var(--pr-accent-strong);
        }

        .pr-btn-ghost {
            border-color: transparent;
            background: transparent;
            color: var(--pr-ink-2);
        }

        .pr-btn-warn {
            color: var(--pr-warn);
        }

        .pr-btn-warn:hover {
            background: var(--pr-warn-soft);
            border-color: var(--pr-warn);
            color: var(--pr-warn);
        }

        .pr-btn-danger {
            color: var(--pr-error);
        }

        .pr-btn-danger:hover {
            background: var(--pr-error-soft);
            border-color: var(--pr-error);
            color: var(--pr-error);
        }

        /* ── Badges ── */
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

        .status-badge.is-open {
            background: var(--pr-success-soft);
            color: var(--pr-success);
        }

        .status-badge.is-closed {
            background: var(--pr-warn-soft);
            color: var(--pr-warn);
        }

        .status-badge.is-locked {
            background: var(--pr-paper-2);
            color: var(--pr-ink-2);
        }

        /* ── Panels ── */
        .pr-panel {
            background: var(--pr-panel);
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius);
            margin-bottom: var(--pr-space-lg);
        }

        .pr-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: var(--pr-space-sm);
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
            display: block;
            font-size: var(--pr-text-sm);
            color: var(--pr-muted);
        }

        .pr-section-title {
            font-size: var(--pr-text-md);
            font-weight: 700;
            color: var(--pr-ink);
            margin: 0 0 var(--pr-space-sm);
        }

        /* ── Running periods (signature) ── */
        .pr-running {
            display: grid;
            /* auto-fit: kolom kosong dilipat, jadi 1 kartu mengisi penuh dan 2+ kartu berbagi rata */
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 420px), 1fr));
            gap: var(--pr-space-lg);
            margin-bottom: var(--pr-space-lg);
        }

        .pr-run {
            margin-bottom: 0;
            display: flex;
            flex-direction: column;
        }

        .pr-run-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: var(--pr-space-sm);
            padding: var(--pr-space-md) var(--pr-space-lg) 0;
        }

        .pr-run-name {
            font-size: var(--pr-text-xl);
            font-weight: 700;
            line-height: 1.15;
            margin: 0;
        }

        .pr-run-range {
            display: block;
            font-size: var(--pr-text-sm);
            color: var(--pr-ink-2);
            margin-top: 2px;
        }

        .pr-run-note {
            display: block;
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
            margin-top: 2px;
        }

        .pr-run-body {
            display: grid;
            grid-template-columns: minmax(0, 3fr) minmax(0, 2fr);
            gap: var(--pr-space-lg);
            padding: var(--pr-space-md) var(--pr-space-lg);
        }

        .pr-bar {
            display: flex;
            height: 8px;
            border-radius: var(--pr-pill);
            overflow: hidden;
            background: var(--pr-paper-2);
            margin: var(--pr-space-xs) 0;
        }

        .pr-bar span {
            display: block;
            height: 100%;
        }

        .pr-bar .is-draft,
        .pr-counts .is-draft .pr-swatch {
            background: var(--pr-muted);
        }

        .pr-bar .is-approved,
        .pr-counts .is-approved .pr-swatch {
            background: var(--pr-success);
        }

        .pr-bar .is-paid,
        .pr-counts .is-paid .pr-swatch {
            background: var(--pr-accent);
        }

        .pr-progress-label {
            font-size: var(--pr-text-sm);
            color: var(--pr-ink-2);
        }

        .pr-progress-label strong {
            color: var(--pr-ink);
            font-variant-numeric: tabular-nums;
        }

        .pr-counts {
            display: flex;
            flex-wrap: wrap;
            gap: var(--pr-space-xs) var(--pr-space-md);
            margin: 0;
            padding: 0;
            list-style: none;
            font-size: var(--pr-text-sm);
            color: var(--pr-ink-2);
        }

        .pr-counts li {
            display: inline-flex;
            align-items: center;
            gap: var(--pr-space-xs);
        }

        .pr-counts strong {
            color: var(--pr-ink);
            font-variant-numeric: tabular-nums;
        }

        .pr-swatch {
            width: 8px;
            height: 8px;
            border-radius: 2px;
            flex-shrink: 0;
        }

        .pr-run-net dt {
            font-size: var(--pr-text-sm);
            font-weight: 600;
            color: var(--pr-ink-2);
        }

        .pr-run-net dd {
            margin: var(--pr-space-2xs) 0 0;
            font-size: var(--pr-text-xl);
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
            color: var(--pr-ink);
        }

        .pr-run-net dd small {
            font-size: var(--pr-text-sm);
            color: var(--pr-muted);
            margin-right: 2px;
        }

        .pr-run-foot {
            margin-top: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: var(--pr-space-sm);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-top: 1px solid var(--pr-rule);
            background: var(--pr-paper);
            border-radius: 0 0 var(--pr-radius) var(--pr-radius);
        }

        .pr-run-actions {
            display: flex;
            flex-wrap: wrap;
            gap: var(--pr-space-xs);
        }

        .pr-step {
            display: inline-flex;
            align-items: center;
            gap: var(--pr-space-xs);
            font-size: var(--pr-text-sm);
            font-weight: 700;
        }

        .pr-step.is-todo {
            color: var(--pr-warn);
        }

        .pr-step.is-ready {
            color: var(--pr-success);
        }

        .pr-step.is-done {
            color: var(--pr-ink-2);
        }

        .pr-empty {
            padding: var(--pr-space-lg);
        }

        .pr-empty h3 {
            font-size: var(--pr-text-lg);
            font-weight: 700;
            margin: 0 0 var(--pr-space-2xs);
            color: var(--pr-ink);
        }

        .pr-empty p {
            margin: 0;
            font-size: var(--pr-text-sm);
            color: var(--pr-ink-2);
            max-width: 60ch;
        }

        /* ── Layout ── */
        .pr-layout {
            display: grid;
            grid-template-columns: minmax(0, 8fr) minmax(0, 4fr);
            gap: var(--pr-space-lg);
            align-items: start;
        }

        /* ── Create form ── */
        .pr-form {
            padding: var(--pr-space-md) var(--pr-space-lg) var(--pr-space-lg);
        }

        .pr-field {
            margin-bottom: var(--pr-space-md);
        }

        .pr-field label {
            display: block;
            font-size: var(--pr-text-sm);
            font-weight: 600;
            color: var(--pr-ink-2);
            margin-bottom: var(--pr-space-2xs);
        }

        .pr-field label span {
            font-weight: 400;
            color: var(--pr-muted);
        }

        .pr-field select,
        .pr-field input {
            width: 100%;
            min-height: 36px;
            padding: 0 var(--pr-space-sm);
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-panel);
            font-family: var(--pr-font);
            font-size: var(--pr-text-sm);
            color: var(--pr-ink);
        }

        .pr-field select:focus-visible,
        .pr-field input:focus-visible {
            outline-offset: -1px;
        }

        .pr-field-row {
            display: grid;
            grid-template-columns: minmax(0, 3fr) minmax(0, 2fr);
            gap: var(--pr-space-sm);
        }

        .pr-preview {
            margin: 0 0 var(--pr-space-md);
            padding: var(--pr-space-sm);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-accent-soft);
            font-size: var(--pr-text-sm);
            color: var(--pr-ink);
        }

        .pr-preview.is-duplicate {
            background: var(--pr-error-soft);
            color: var(--pr-error);
            font-weight: 600;
        }

        .pr-form-hint {
            margin: var(--pr-space-sm) 0 0;
            line-height: 1.5;
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
        }

        .pr-form .pr-btn {
            width: 100%;
        }

        /* ── Toolbar ── */
        .pr-toolbar {
            display: flex;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: var(--pr-space-sm);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-bottom: 1px solid var(--pr-rule);
        }

        .pr-toolbar .pr-field {
            margin: 0;
            min-width: 140px;
        }

        .pr-toolbar-actions {
            display: flex;
            gap: var(--pr-space-xs);
        }

        .pr-tabs {
            display: inline-flex;
            padding: var(--pr-space-2xs);
            gap: var(--pr-space-2xs);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-paper-2);
        }

        .pr-tabs button {
            min-height: 30px;
            padding: 0 var(--pr-space-sm);
            border: 0;
            border-radius: 4px;
            background: transparent;
            font-family: var(--pr-font);
            font-size: var(--pr-text-sm);
            font-weight: 600;
            color: var(--pr-ink-2);
            cursor: pointer;
            transition: background-color var(--pr-dur-micro) var(--pr-ease), color var(--pr-dur-micro) var(--pr-ease);
        }

        .pr-tabs button:hover {
            color: var(--pr-ink);
        }

        .pr-tabs button[aria-pressed="true"] {
            background: var(--pr-panel);
            color: var(--pr-ink);
            box-shadow: 0 1px 2px var(--pr-shadow-pressed);
        }

        .pr-tabs .pr-tab-count {
            margin-left: var(--pr-space-2xs);
            color: var(--pr-muted);
            font-variant-numeric: tabular-nums;
        }

        /* ── Table ── */
        .pr-table-wrap {
            padding: var(--pr-space-xs) var(--pr-space-lg) var(--pr-space-md);
            overflow-x: auto;
        }

        #period-table {
            width: 100% !important;
            font-size: var(--pr-text-sm);
            border-collapse: collapse;
        }

        #period-table thead th {
            background: var(--pr-panel);
            color: var(--pr-ink-2);
            font-size: var(--pr-text-xs);
            font-weight: 700;
            padding: var(--pr-space-sm) var(--pr-space-xs);
            border: 0;
            border-bottom: 1px solid var(--pr-rule);
            white-space: nowrap;
        }

        #period-table thead th.sorting,
        #period-table thead th.sorting_asc,
        #period-table thead th.sorting_desc {
            padding-right: var(--pr-space-lg);
        }

        #period-table tbody td {
            padding: var(--pr-space-sm) var(--pr-space-xs);
            vertical-align: middle;
            border: 0;
            border-bottom: 1px solid var(--pr-rule);
            color: var(--pr-ink);
        }

        #period-table tbody tr:hover td {
            background: var(--pr-paper);
        }

        .pr-cell-name {
            display: block;
            font-weight: 700;
        }

        .pr-cell-sub {
            display: block;
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
            white-space: nowrap;
        }

        .pr-mini {
            min-width: 140px;
        }

        .pr-mini .pr-bar {
            height: 6px;
            margin: 0 0 var(--pr-space-2xs);
        }

        .pr-muted {
            color: var(--pr-muted);
        }

        /* Row actions (HTML from PayrollMobilePeriodController::getPayrollPeriod) */
        .action-wrap {
            display: flex;
            gap: var(--pr-space-2xs);
            justify-content: center;
        }

        .act-btn {
            width: 32px;
            height: 32px;
            border-radius: var(--pr-radius-sm);
            border: 1px solid var(--pr-rule);
            background: var(--pr-panel);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--pr-ink-2);
            font-size: var(--pr-text-xs);
            text-decoration: none;
            transition: background-color var(--pr-dur-micro) var(--pr-ease), color var(--pr-dur-micro) var(--pr-ease), border-color var(--pr-dur-micro) var(--pr-ease);
        }

        .act-btn:hover {
            background: var(--pr-paper-2);
            color: var(--pr-ink);
            text-decoration: none;
        }

        .act-btn.act-warning:hover {
            background: var(--pr-warn-soft);
            border-color: var(--pr-warn);
            color: var(--pr-warn);
        }

        .act-btn.act-danger:hover {
            background: var(--pr-error-soft);
            border-color: var(--pr-error);
            color: var(--pr-error);
        }

        /* DataTables chrome */
        .pr-dt-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: var(--pr-space-sm);
            padding-top: var(--pr-space-md);
        }

        .pr .dataTables_wrapper .dataTables_info,
        .pr .dataTables_wrapper .dataTables_paginate {
            float: none;
            padding: 0;
            font-size: var(--pr-text-sm);
            color: var(--pr-ink-2);
        }

        .pr .dataTables_wrapper .dataTables_paginate .paginate_button {
            min-width: 32px;
            min-height: 32px;
            margin: 0 0 0 var(--pr-space-2xs) !important;
            padding: 0 var(--pr-space-sm) !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--pr-rule) !important;
            border-radius: var(--pr-radius-sm);
            background: var(--pr-panel) !important;
            color: var(--pr-ink) !important;
            font-weight: 600;
        }

        .pr .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .pr .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: var(--pr-accent-soft) !important;
            border-color: var(--pr-accent) !important;
            color: var(--pr-accent-strong) !important;
        }

        .pr .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        .pr .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
            opacity: .55;
            cursor: not-allowed;
        }

        .pr .dataTables_wrapper .dataTables_processing {
            background: var(--pr-panel);
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            color: var(--pr-ink-2);
            font-size: var(--pr-text-sm);
        }

        @media (max-width: 75rem) {
            .pr-layout {
                grid-template-columns: minmax(0, 1fr);
            }

            .pr-create {
                margin-bottom: var(--pr-space-lg);
            }
        }

        @media (max-width: 40rem) {

            .pr-head,
            .pr-panel-head,
            .pr-run-top,
            .pr-run-body,
            .pr-run-foot,
            .pr-form,
            .pr-toolbar,
            .pr-table-wrap {
                padding-left: var(--pr-space-md);
                padding-right: var(--pr-space-md);
            }

            .pr-run-body {
                grid-template-columns: minmax(0, 1fr);
                gap: var(--pr-space-md);
            }

            .pr-toolbar .pr-field,
            .pr-toolbar-actions,
            .pr-tabs {
                width: 100%;
            }

            .pr-tabs button,
            .pr-toolbar-actions .pr-btn {
                flex: 1 1 0;
            }

            .pr-field select,
            .pr-field input,
            .pr-btn {
                min-height: 44px;
                font-size: 16px;
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
        $rp = fn($n) => number_format((float) $n, 0, ',', '.');
        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $statusBadge = ['open' => 'is-open', 'closed' => 'is-closed', 'locked' => 'is-locked'];
        $totalPeriods = $stats['open'] + $stats['closed'] + $stats['locked'];
    @endphp
    <div class="main-content pr">
        <section class="section">

            {{-- Head --}}
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs" aria-label="Breadcrumb">
                        <li>Dashboard</li>
                        <li>Payroll Absensi Mobile</li>
                        <li aria-current="page">Payroll Periods</li>
                    </ol>
                    <h1 class="pr-title">Payroll absensi mobile</h1>
                    <p class="pr-lede">
                        Hari hadir dihitung dari absensi aplikasi mobile: check-in dan check-out yang sudah diverifikasi
                        (approved supervisor atau HR) pada hari roster kerja, ditambah PH, TOIL, cuti, dan sakit berbayar.
                        Terpisah dari payroll fingerprint. Satu periode berjalan dari tanggal 26 sampai 25 bulan berikutnya;
                        di-close setelah semua payroll approved, lalu di-lock setelah semua paid.
                    </p>
                </div>
            </header>

            <div class="pr-layout">
                <div>
                {{-- Running periods --}}
                <h2 class="pr-section-title">Periode berjalan</h2>
                <div class="pr-running">
                    @forelse ($active as $p)
                        @php
                            $total = (int) $p->payrolls_count;
                            $pct = fn($n) => $total > 0 ? round($n / $total * 100, 2) : 0;
                            $step = $p->nextStep();
                        @endphp
                        <section class="pr-panel pr-run" aria-labelledby="run-{{ $p->id }}">
                            <div class="pr-run-top">
                                <div>
                                    <h3 class="pr-run-name" id="run-{{ $p->id }}">{{ $p->period_label }}</h3>
                                    <span class="pr-run-range">{{ $p->period_start->locale('id')->translatedFormat('d M Y') }} sampai {{ $p->period_end->locale('id')->translatedFormat('d M Y') }}</span>
                                    @if ($p->note)
                                        <span class="pr-run-note">{{ $p->note }}</span>
                                    @endif
                                </div>
                                <span class="status-badge {{ $statusBadge[$p->status] ?? '' }}">{{ ucfirst($p->status) }}</span>
                            </div>
                            <div class="pr-run-body">
                                <div>
                                    <span class="pr-progress-label"><strong>{{ $total }}</strong> payroll</span>
                                    <div class="pr-bar" role="img"
                                        aria-label="{{ $p->draft_count }} draft, {{ $p->approved_count }} approved, {{ $p->paid_count }} paid">
                                        <span class="is-paid" style="width: {{ $pct($p->paid_count) }}%"></span>
                                        <span class="is-approved" style="width: {{ $pct($p->approved_count) }}%"></span>
                                        <span class="is-draft" style="width: {{ $pct($p->draft_count) }}%"></span>
                                    </div>
                                    <ul class="pr-counts">
                                        <li class="is-draft"><span class="pr-swatch"></span>Draft <strong>{{ $p->draft_count }}</strong></li>
                                        <li class="is-approved"><span class="pr-swatch"></span>Approved <strong>{{ $p->approved_count }}</strong></li>
                                        <li class="is-paid"><span class="pr-swatch"></span>Paid <strong>{{ $p->paid_count }}</strong></li>
                                    </ul>
                                </div>
                                <dl class="pr-run-net">
                                    <dt>Total net salary</dt>
                                    <dd><small>Rp</small>{{ $rp($p->net_total) }}</dd>
                                </dl>
                            </div>
                            <div class="pr-run-foot">
                                <span class="pr-step is-{{ $step['tone'] }}">
                                    <i class="fas {{ $step['tone'] === 'ready' ? 'fa-check-circle' : 'fa-hourglass-half' }}" aria-hidden="true"></i>
                                    {{ $step['text'] }}
                                </span>
                                <div class="pr-run-actions">
                                    @can('payroll.mobile.view')
                                        <a href="{{ route('payroll.mobile.index', $p->id) }}" class="pr-btn">
                                            <i class="fas fa-eye" aria-hidden="true"></i> Lihat payroll
                                        </a>
                                    @endcan
                                    @if ($p->isOpen() && auth()->user()->can('payroll.mobile.period.close'))
                                        <a href="{{ route('payrollmobileperiod.close', $p->id) }}" class="pr-btn pr-btn-warn"
                                            onclick="return confirm('Close periode ini?')">
                                            <i class="fas fa-lock-open" aria-hidden="true"></i> Close
                                        </a>
                                    @elseif ($p->isClosed() && auth()->user()->can('payroll.mobile.period.lock'))
                                        <a href="{{ route('payrollmobileperiod.lock', $p->id) }}" class="pr-btn pr-btn-danger"
                                            onclick="return confirm('Lock periode ini? Tidak bisa dibuka kembali.')">
                                            <i class="fas fa-lock" aria-hidden="true"></i> Lock
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </section>
                    @empty
                        <section class="pr-panel pr-empty">
                            <h3>Tidak ada periode yang berjalan</h3>
                            <p>Semua periode sudah di-lock. Buat periode baru di form samping untuk mulai generate payroll bulan berikutnya.</p>
                        </section>
                    @endforelse
                </div>
                </div>

                @can('payroll.mobile.period.create')
                {{-- Create --}}
                <section class="pr-panel pr-create" aria-labelledby="create-title">
                    <div class="pr-panel-head">
                        <h2 class="pr-panel-title" id="create-title">Buat periode baru</h2>
                    </div>
                    <form action="{{ route('payrollmobileperiod.store') }}" method="POST" class="pr-form">
                        @csrf
                        <div class="pr-field-row">
                            <div class="pr-field">
                                <label for="period_month">Bulan</label>
                                <select name="period_month" id="period_month" required>
                                    <option value="">Pilih bulan</option>
                                    @foreach ($months as $i => $bulan)
                                        <option value="{{ $i + 1 }}" {{ old('period_month') == $i + 1 ? 'selected' : '' }}>{{ $bulan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="pr-field">
                                <label for="period_year">Tahun</label>
                                <select name="period_year" id="period_year" required>
                                    @for ($y = now()->year - 1; $y <= now()->year + 1; $y++)
                                        <option value="{{ $y }}" {{ old('period_year', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="pr-field">
                            <label for="period_note">Catatan <span>(opsional)</span></label>
                            <input type="text" name="note" id="period_note" value="{{ old('note') }}" maxlength="255"
                                placeholder="Misalnya: gaji Mei sampai Juni" autocomplete="off">
                        </div>

                        <p class="pr-preview" id="period-preview" hidden aria-live="polite">
                            <span id="preview-text">Periode: <strong id="preview-start"></strong> sampai <strong id="preview-end"></strong></span>
                        </p>

                        <button type="submit" class="pr-btn pr-btn-primary" id="btn-create">
                            <i class="fas fa-plus" aria-hidden="true"></i> Buat periode
                        </button>
                        <p class="pr-form-hint">Periode baru langsung berstatus open dan siap di-generate payroll-nya.</p>
                    </form>
                </section>
                @endcan
            </div>

            {{-- Table --}}
            <section class="pr-panel" aria-labelledby="table-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="table-title">Semua periode</h2>
                        <span class="pr-panel-sub" id="record-count">Memuat data</span>
                    </div>
                    <div class="pr-tabs" role="group" aria-label="Filter status periode">
                        <button type="button" data-status="" aria-pressed="true">Semua<span class="pr-tab-count">{{ $totalPeriods }}</span></button>
                        <button type="button" data-status="open" aria-pressed="false">Open<span class="pr-tab-count">{{ $stats['open'] }}</span></button>
                        <button type="button" data-status="closed" aria-pressed="false">Closed<span class="pr-tab-count">{{ $stats['closed'] }}</span></button>
                        <button type="button" data-status="locked" aria-pressed="false">Locked<span class="pr-tab-count">{{ $stats['locked'] }}</span></button>
                    </div>
                </div>

                <div class="pr-toolbar">
                    <div class="pr-field">
                        <label for="filter-year">Tahun</label>
                        <select id="filter-year">
                            <option value="">Semua tahun</option>
                            @for ($y = now()->year - 1; $y <= now()->year + 1; $y++)
                                <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="pr-field">
                        <label for="filter-status">Status</label>
                        <select id="filter-status">
                            <option value="">Semua status</option>
                            <option value="open">Open</option>
                            <option value="closed">Closed</option>
                            <option value="locked">Locked</option>
                        </select>
                    </div>
                    <div class="pr-toolbar-actions">
                        <button type="button" class="pr-btn" id="btn-search"><i class="fas fa-search" aria-hidden="true"></i> Cari</button>
                        <button type="button" class="pr-btn pr-btn-ghost" id="btn-reset"><i class="fas fa-redo" aria-hidden="true"></i> Reset</button>
                    </div>
                </div>

                <div class="pr-table-wrap">
                    <table id="period-table">
                        <thead>
                            <tr>
                                <th>Periode</th>
                                <th>Status</th>
                                <th>Payroll</th>
                                <th class="num">Total net</th>
                                <th>Langkah berikutnya</th>
                                <th>Dibuat oleh</th>
                                <th>Locked oleh</th>
                                <th style="text-align:center">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </section>

        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('partials.datatables-error', ['permission' => 'payroll.mobile.period.view'])

    <script>
        $(function() {
            const esc = v => $('<div>').text(v ?? '').html();

            // ── DataTable ──
            const table = $('#period-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                dom: 'rt<"pr-dt-bottom"ip>',
                ajax: {
                    url: '{{ route('payrollmobileperiod.data') }}',
                    data: function(d) {
                        d.year = $('#filter-year').val();
                        d.status = $('#filter-status').val();
                    }
                },
                columns: [{
                        data: 'period_label',
                        name: 'period_label',
                        render: (d, t, row) => t !== 'display' ? d :
                            `<span class="pr-cell-name">${esc(d)}</span>` +
                            `<span class="pr-cell-sub">${esc(row.period_range)}</span>` +
                            (row.note ? `<span class="pr-cell-sub">${esc(row.note)}</span>` : ''),
                    },
                    {
                        data: 'status_badge',
                        orderable: false
                    },
                    {
                        data: 'payroll_total',
                        orderable: false,
                        searchable: false,
                        render: (d, t, row) => {
                            if (t !== 'display') return d;
                            if (!row.payroll_total) return '<span class="pr-muted">Belum ada payroll</span>';
                            const pct = n => (n / row.payroll_total * 100).toFixed(2);
                            return `<div class="pr-mini">
                                <div class="pr-bar" aria-hidden="true">
                                    <span class="is-paid" style="width:${pct(row.paid_count)}%"></span>
                                    <span class="is-approved" style="width:${pct(row.approved_count)}%"></span>
                                    <span class="is-draft" style="width:${pct(row.draft_count)}%"></span>
                                </div>
                                <span class="pr-cell-sub">${row.payroll_total} payroll: ${row.draft_count} draft, ${row.approved_count} approved, ${row.paid_count} paid</span>
                            </div>`;
                        },
                    },
                    {
                        data: 'net_total_fmt',
                        className: 'num',
                        orderable: false,
                        searchable: false,
                        render: (d, t) => t !== 'display' ? d : `Rp ${d}`,
                    },
                    {
                        data: 'next_step',
                        orderable: false,
                        searchable: false,
                        render: (d, t) => t !== 'display' ? (d?.text ?? '') :
                            `<span class="pr-step is-${esc(d.tone)}">${esc(d.text)}</span>`,
                    },
                    {
                        data: 'created_by_name',
                        defaultContent: '-',
                        orderable: false
                    },
                    {
                        data: 'locked_by_name',
                        defaultContent: '-',
                        orderable: false
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                    },
                ],
                drawCallback: function() {
                    const info = this.api().page.info();
                    $('#record-count').text(`${info.recordsDisplay} periode`);
                },
                language: {
                    processing: 'Memuat data...',
                    emptyTable: 'Belum ada periode. Buat periode pertama di form samping.',
                    zeroRecords: 'Tidak ada periode yang cocok dengan filter.',
                    info: '_START_ sampai _END_ dari _TOTAL_',
                    infoEmpty: '0 periode',
                    infoFiltered: '',
                    paginate: {
                        previous: 'Sebelumnya',
                        next: 'Berikutnya'
                    },
                },
                order: [
                    [0, 'desc']
                ],
                pageLength: 25,
            });

            // ── Filter ──
            function syncTabs() {
                const st = $('#filter-status').val();
                $('.pr-tabs button').each(function() {
                    $(this).attr('aria-pressed', String($(this).data('status')) === st ? 'true' : 'false');
                });
            }

            $('.pr-tabs').on('click', 'button', function() {
                $('#filter-status').val($(this).data('status'));
                syncTabs();
                table.ajax.reload();
            });

            $('#btn-search').on('click', () => {
                syncTabs();
                table.ajax.reload();
            });
            $('#btn-reset').on('click', () => {
                $('#filter-year').val('{{ now()->year }}');
                $('#filter-status').val('');
                syncTabs();
                table.ajax.reload();
            });

            // ── Preview periode + cek duplikat saat pilih bulan/tahun ──
            const existing = new Set(@json($existing));

            function updatePreview() {
                const month = $('select[name=period_month]').val();
                const year = $('select[name=period_year]').val();

                if (!month || !year) {
                    $('#period-preview').prop('hidden', true);
                    $('#btn-create').prop('disabled', false);
                    return;
                }

                const m = parseInt(month);
                const y = parseInt(year);
                const end = new Date(y, m - 1, 25);
                const start = new Date(y, m - 2, 26);
                const fmt = d => d.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric'
                });

                const dup = existing.has(`${y}-${m}`);
                $('#period-preview').toggleClass('is-duplicate', dup).prop('hidden', false);
                if (dup) {
                    $('#preview-text').text('Periode ini sudah ada. Pilih bulan atau tahun lain.');
                } else {
                    $('#preview-text').html('Periode: <strong id="preview-start"></strong> sampai <strong id="preview-end"></strong>');
                    $('#preview-start').text(fmt(start));
                    $('#preview-end').text(fmt(end));
                }
                $('#btn-create').prop('disabled', dup);
            }

            $('select[name=period_month], select[name=period_year]').on('change', updatePreview);
            updatePreview();

            // ── Flash messages ──
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: @json(session('success')),
                    confirmButtonColor: '#1d4ed8',
                    timer: 3000,
                    timerProgressBar: true,
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: @json(session('error')),
                    confirmButtonColor: '#dc2626',
                });
            @endif
        });
    </script>
@endpush
