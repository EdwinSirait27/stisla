@extends('layouts.app')
@section('title', 'Dokumen karyawan')
@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand · redesign: full
         * paper: oklch(98.4% 0.004 265) · accent: oklch(61% 0.17 276) · display: Nunito · body: Nunito
         * nav: kept (Stisla shell) · signature: which documents never reached the employee, and why · states: no documents, no email, draft, sent, failed, filtered empty, selection across pages
         * axes: light/rounded-sans/chromatic-indigo · posture: app · contrast: pass · v2.0.0 */
        /* Hallmark · pre-emit critique: P4 H4 E4 S4 R4 V4 · weakest: E, delivery state only exists from now on (no history before this release) */
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

        /* Row actions (HTML from PayrollPeriodController::getPayrollPeriod) */
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

        .pr-period {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 160px), 1fr));
            margin: 0;
        }

        .pr-period>div {
            padding: var(--pr-space-md) var(--pr-space-lg);
            border-right: 1px solid var(--pr-rule);
        }

        .pr-period>div:last-child {
            border-right: 0;
        }

        .pr-period dt {
            font-size: var(--pr-text-sm);
            font-weight: 600;
            color: var(--pr-ink-2);
        }

        .pr-period dd {
            margin: var(--pr-space-2xs) 0 0;
            font-size: var(--pr-text-xl);
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            color: var(--pr-ink);
        }

        .pr-period dd small {
            font-size: var(--pr-text-sm);
            font-weight: 600;
            color: var(--pr-muted);
        }

        .pr-period-note {
            display: block;
            margin-top: 2px;
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
        }

        .status-badge.is-pending {
            background: var(--pr-warn-soft);
            color: var(--pr-warn);
        }

        .status-badge.is-approved {
            background: var(--pr-success-soft);
            color: var(--pr-success);
        }

        .status-badge.is-rejected {
            background: var(--pr-error-soft);
            color: var(--pr-error);
        }

        .status-badge.is-draft {
            background: var(--pr-paper-2);
            color: var(--pr-ink-2);
        }
        .pr-btn-sm {
            min-height: 32px;
            padding: 0 var(--pr-space-sm);
            font-size: var(--pr-text-xs);
        }
        .pr-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: var(--pr-space-sm);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-bottom: 1px solid var(--pr-rule);
        }

        .pr-views {
            display: inline-flex;
            padding: var(--pr-space-2xs);
            gap: var(--pr-space-2xs);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-paper-2);
        }

        .pr-views button {
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

        .pr-views button:hover {
            color: var(--pr-ink);
        }

        .pr-views button[aria-pressed="true"] {
            background: var(--pr-panel);
            color: var(--pr-ink);
            box-shadow: 0 1px 2px var(--pr-shadow-pressed);
        }

        .pr-toolbar-right {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: var(--pr-space-xs);
        }

        .pr-search {
            position: relative;
            display: inline-flex;
            align-items: center;
        }

        .pr-search i {
            position: absolute;
            left: var(--pr-space-sm);
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
            pointer-events: none;
        }

        .pr-search input {
            width: 240px;
            min-height: 32px;
            padding: 0 var(--pr-space-sm) 0 32px;
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-panel);
            font-family: var(--pr-font);
            font-size: var(--pr-text-sm);
            color: var(--pr-ink);
        }

        .pr-search input:focus-visible {
            outline-offset: -1px;
        }

        .pr-chip {
            display: inline-flex;
            align-items: center;
            gap: var(--pr-space-xs);
            min-height: 28px;
            padding: 0 4px 0 var(--pr-space-sm);
            border-radius: var(--pr-pill);
            background: var(--pr-warn-soft);
            color: var(--pr-ink);
            font-size: var(--pr-text-xs);
            font-weight: 600;
        }

        .pr-chip button {
            width: 22px;
            height: 22px;
            border: 0;
            border-radius: 50%;
            background: transparent;
            color: var(--pr-ink-2);
            cursor: pointer;
        }

        .pr-chip button:hover {
            background: var(--pr-panel);
        }

        .pr-filter-count {
            min-width: 18px;
            height: 18px;
            padding: 0 var(--pr-space-2xs);
            border-radius: var(--pr-pill);
            background: var(--pr-accent-strong);
            color: var(--pr-accent-ink);
            font-size: 11px;
            line-height: 18px;
            text-align: center;
        }

        .pr-filter-body {
            padding: var(--pr-space-md) var(--pr-space-lg);
            border-bottom: 1px solid var(--pr-rule);
            background: var(--pr-paper);
        }

        .pr-filter-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: var(--pr-space-sm) var(--pr-space-md);
        }

        .pr-field label {
            display: block;
            font-size: var(--pr-text-xs);
            font-weight: 600;
            color: var(--pr-ink-2);
            margin-bottom: var(--pr-space-2xs);
        }

        .pr-field select {
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

        .pr-field select:focus-visible {
            outline-offset: -1px;
        }

        .pr-filter-actions {
            display: flex;
            justify-content: flex-end;
            gap: var(--pr-space-xs);
            margin-top: var(--pr-space-md);
        }

        /* ── Bulk bar ── */
        .bulk-bar {
            display: none;
            position: sticky;
            top: 70px;
            z-index: 20;
            align-items: center;
            flex-wrap: wrap;
            gap: var(--pr-space-xs);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            background: var(--pr-accent-soft);
            border-bottom: 1px solid var(--pr-rule);
        }

        .bulk-bar.show {
            display: flex;
        }

        .bulk-bar-count {
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-ink);
            margin-right: var(--pr-space-xs);
        }

        /* ── Dokumen ── */
        .pr-period dd.is-warn-text {
            color: var(--pr-warn);
        }

        .doc-quick {
            margin-top: var(--pr-space-2xs);
            padding: 0;
            border: 0;
            background: none;
            font-family: var(--pr-font);
            font-size: var(--pr-text-xs);
            font-weight: 700;
            color: var(--pr-accent-strong);
            cursor: pointer;
        }

        .doc-quick:hover {
            text-decoration: underline;
        }

        .doc-quick:focus-visible {
            outline: 2px solid var(--pr-focus);
            outline-offset: 2px;
        }

        .doc-filters .pr-field {
            flex: 1 1 180px;
            max-width: 260px;
        }

        .doc-col-check {
            width: 40px;
            text-align: center !important;
        }

        .doc-col-check input,
        .doc-checkbox {
            width: 16px;
            height: 16px;
            accent-color: var(--pr-accent-strong);
            cursor: pointer;
        }

        .doc-checkbox:disabled {
            cursor: not-allowed;
            opacity: .4;
        }

        #users-table .pr-cell-name .status-badge {
            margin-left: 4px;
            vertical-align: 1px;
        }

        #users-table td .status-badge + .pr-cell-sub {
            margin-top: 2px;
        }

        #users-table td:nth-child(4) .pr-cell-name {
            white-space: nowrap;
        }

        .act-btn:disabled {
            opacity: .4;
            cursor: not-allowed;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        @media (max-width: 40rem) {
            .doc-filters .pr-field {
                max-width: none;
                flex-basis: 100%;
            }

            .pr-panel-head .pr-search {
                width: 100%;
            }
        }
    </style>
@endpush

@section('main')
    @php
        $sendable = $stats['total'] - $stats['draft'] - $stats['no_email'];
        $neverSent = max(0, $sendable - $stats['sent']);
    @endphp
    <div class="main-content pr">
        <section class="section">

            <header class="pr-head">
                <div>
                    <h1 class="pr-title">Dokumen karyawan</h1>
                    <p class="pr-lede">Surat yang diterbitkan untuk karyawan. Unduh PDF-nya atau kirim ke email karyawan.</p>
                </div>
            </header>

            {{-- Ringkasan (signature: apa yang belum sampai ke karyawan) --}}
            <section class="pr-panel" aria-labelledby="doc-sum-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="doc-sum-title">Ringkasan</h2>
                        <span class="pr-panel-sub">{{ number_format($stats['total'], 0, ',', '.') }} dokumen, {{ $stats['by_type']->map(fn($t) => $t->c . ' ' . ($t->nickname ?: $t->document_name))->implode(', ') ?: 'belum ada' }}</span>
                    </div>
                </div>
                <dl class="pr-period">
                    <div>
                        <dt>Belum pernah dikirim</dt>
                        <dd class="{{ $neverSent ? 'is-warn-text' : '' }}">{{ number_format($neverSent, 0, ',', '.') }} <small>dokumen</small></dd>
                        <button type="button" class="doc-quick" data-send="never">Tampilkan</button>
                    </div>
                    <div>
                        <dt>Sudah dikirim</dt>
                        <dd>{{ number_format($stats['sent'], 0, ',', '.') }} <small>dokumen</small></dd>
                        <button type="button" class="doc-quick" data-send="sent">Tampilkan</button>
                    </div>
                    <div>
                        <dt>Karyawan tanpa email</dt>
                        <dd class="{{ $stats['no_email'] ? 'is-warn-text' : '' }}">{{ number_format($stats['no_email'], 0, ',', '.') }} <small>dokumen</small></dd>
                        <button type="button" class="doc-quick" data-send="no_email">Tampilkan</button>
                    </div>
                    <div>
                        <dt>Milik karyawan nonaktif</dt>
                        <dd>{{ number_format($stats['inactive'], 0, ',', '.') }} <small>dokumen</small></dd>
                        <span class="pr-period-note">resign, mutasi, atau nonaktif</span>
                    </div>
                </dl>
            </section>

            {{-- Tabel dokumen --}}
            <section class="pr-panel" aria-labelledby="doc-list-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="doc-list-title">Daftar dokumen</h2>
                        <span class="pr-panel-sub" id="doc-count">Memuat</span>
                    </div>
                    <div class="pr-search">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <label for="doc-search" class="sr-only">Cari nama, NIP, atau nomor dokumen</label>
                        <input type="search" id="doc-search" placeholder="Cari nama, NIP, atau nomor dokumen">
                    </div>
                </div>
                <div class="pr-toolbar doc-filters">
                    <div class="pr-field">
                        <label for="filter-document-name">Jenis dokumen</label>
                        <select id="filter-document-name">
                            <option value="">Semua jenis</option>
                            @foreach ($documentNames as $documentName)
                                <option value="{{ $documentName }}">{{ $documentName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="pr-field">
                        <label for="filter-send">Pengiriman</label>
                        <select id="filter-send">
                            <option value="">Semua</option>
                            <option value="never">Belum pernah dikirim</option>
                            <option value="sent">Sudah dikirim</option>
                            <option value="failed">Pernah gagal</option>
                            <option value="no_email">Tanpa email</option>
                        </select>
                    </div>
                    <div class="pr-field">
                        <label for="filter-status">Status dokumen</label>
                        <select id="filter-status">
                            <option value="">Semua status</option>
                            <option value="draft">Draft</option>
                            <option value="issued">Terbit</option>
                            <option value="revoked">Dicabut</option>
                            <option value="expired">Kedaluwarsa</option>
                        </select>
                    </div>
                    <div class="pr-field">
                        <label for="filter-grading">Grading</label>
                        <select id="filter-grading">
                            <option value="">Semua grading</option>
                            @foreach ($gradings as $grading)
                                <option value="{{ $grading }}">{{ $grading }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" id="btn-reset-filter" class="pr-btn" hidden>Hapus filter</button>
                </div>

                <div class="bulk-bar" id="bulk-bar" role="region" aria-live="polite" aria-label="Aksi untuk dokumen terpilih">
                    <span class="bulk-bar-count"><span id="bulk-count">0</span> dokumen dipilih</span>
                    <button type="button" class="pr-btn pr-btn-sm" id="btn-clear-selection">Batal pilih</button>
                    <button type="button" class="pr-btn pr-btn-sm pr-btn-primary" id="btn-bulk-send">
                        <i class="fas fa-paper-plane" aria-hidden="true"></i> Kirim ke email
                    </button>
                </div>

                <div class="pr-table-wrap">
                    <table class="table" id="users-table">
                        <thead>
                            <tr>
                                <th class="doc-col-check">
                                    <input type="checkbox" id="check-all" aria-label="Pilih semua dokumen yang bisa dikirim di halaman ini">
                                </th>
                                <th>Karyawan</th>
                                <th>Dokumen</th>
                                <th>Terbit</th>
                                <th>Pengiriman</th>
                                <th>Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </section>

            {{-- Log aktivitas --}}
            <section class="pr-panel" aria-labelledby="doc-log-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="doc-log-title">Log aktivitas</h2>
                        <span class="pr-panel-sub">Pembuatan, perubahan, dan pengiriman dokumen</span>
                    </div>
                </div>
                <div class="pr-table-wrap">
                    <table class="table" id="activity-table">
                        <thead>
                            <tr>
                                <th style="width:48px">#</th>
                                <th style="width:120px">Aksi</th>
                                <th>Keterangan</th>
                                <th>Oleh</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </section>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        jQuery(function($) {
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            const filters = ['#filter-document-name', '#filter-send', '#filter-status', '#filter-grading'];
            const selected = new Set();
            let searchTimer = null;

            const table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                dom: 'rt<"pr-dt-bottom"<"pr-dt-left"li>p>',
                order: [[3, 'desc']],
                ajax: {
                    url: '{{ route('documents.documents') }}',
                    type: 'GET',
                    data: function(d) {
                        d.filter_grading = $('#filter-grading').val();
                        d.filter_document_name = $('#filter-document-name').val();
                        d.filter_status = $('#filter-status').val();
                        d.filter_send = $('#filter-send').val();
                    }
                },
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                pageLength: 25,
                language: {
                    processing: 'Memuat',
                    lengthMenu: 'Tampilkan _MENU_',
                    info: '_START_ sampai _END_ dari _TOTAL_',
                    infoEmpty: 'Tidak ada data',
                    infoFiltered: '',
                    zeroRecords: 'Tidak ada dokumen yang cocok dengan filter ini.',
                    emptyTable: 'Belum ada dokumen.',
                    paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                },
                columns: [
                    { data: 'checkbox', orderable: false, searchable: false, className: 'doc-col-check' },
                    { data: 'employee_name', name: 'employee_name' },
                    { data: 'document_name', name: 'document_name' },
                    { data: 'issued', name: 'issued', searchable: false },
                    { data: 'delivery', name: 'delivery', searchable: false },
                    { data: 'status', name: 'status', searchable: false },
                    { data: 'action', orderable: false, searchable: false, className: 'text-right' },
                ],
            });

            table.on('xhr', function() {
                const json = table.ajax.json();
                if (json) $('#doc-count').text(`${Number(json.recordsFiltered).toLocaleString('id-ID')} dokumen ditampilkan`);
            });

            // ── Filter & pencarian ──
            function syncReset() {
                const active = filters.some(f => $(f).val()) || $('#doc-search').val();
                $('#btn-reset-filter').prop('hidden', !active);
            }
            $(filters.join(',')).on('change', function() { table.ajax.reload(); syncReset(); });
            $('#doc-search').on('input', function() {
                clearTimeout(searchTimer);
                const v = this.value;
                searchTimer = setTimeout(() => { table.search(v).draw(); syncReset(); }, 350);
            });
            $('#btn-reset-filter').on('click', function() {
                filters.forEach(f => $(f).val(''));
                $('#doc-search').val('');
                table.search('').ajax.reload();
                syncReset();
            });
            $('.doc-quick').on('click', function() {
                $('#filter-send').val(this.dataset.send).trigger('change');
                document.getElementById('doc-list-title').scrollIntoView({ behavior: 'smooth', block: 'start' });
            });

            // ── Pilihan (bertahan antar halaman tabel) ──
            function updateSelectionUI() {
                $('#bulk-count').text(selected.size);
                $('#bulk-bar').toggleClass('show', selected.size > 0);
                const $boxes = $('#users-table tbody .doc-checkbox:not(:disabled)');
                const checked = $boxes.filter(':checked').length;
                $('#check-all').prop('checked', $boxes.length > 0 && checked === $boxes.length)
                    .prop('indeterminate', checked > 0 && checked < $boxes.length);
            }
            table.on('draw', function() {
                $('#users-table tbody .doc-checkbox').each(function() { this.checked = selected.has(this.value); });
                updateSelectionUI();
            });
            $('#users-table').on('change', '.doc-checkbox', function() {
                this.checked ? selected.add(this.value) : selected.delete(this.value);
                updateSelectionUI();
            });
            $('#check-all').on('change', function() {
                const on = this.checked;
                $('#users-table tbody .doc-checkbox:not(:disabled)').each(function() {
                    this.checked = on;
                    on ? selected.add(this.value) : selected.delete(this.value);
                });
                updateSelectionUI();
            });
            $('#btn-clear-selection').on('click', function() {
                selected.clear();
                $('#users-table tbody .doc-checkbox').prop('checked', false);
                updateSelectionUI();
            });

            const errText = (xhr, fallback) => xhr.responseJSON?.message || fallback;

            // ── Kirim satu dokumen ──
            $('#users-table').on('click', '.btn-send-document', function() {
                const $btn = $(this);
                const { url, number, email } = this.dataset;
                Swal.fire({
                    icon: 'question',
                    title: 'Kirim dokumen ini?',
                    text: `${number} akan dikirim sebagai lampiran PDF ke ${email}.`,
                    showCancelButton: true,
                    confirmButtonText: 'Kirim',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#4f46e5'
                }).then(result => {
                    if (!result.isConfirmed) return;
                    $btn.prop('disabled', true);
                    $.post(url)
                        .done(res => Swal.fire({ icon: 'success', title: 'Masuk antrian', text: res.message }))
                        .fail(xhr => Swal.fire({ icon: 'error', title: 'Tidak terkirim', text: errText(xhr, 'Gagal memasukkan dokumen ke antrian kirim.') }))
                        .always(() => $btn.prop('disabled', false));
                });
            });

            // ── Kirim massal ──
            $('#btn-bulk-send').on('click', function() {
                const ids = [...selected];
                if (!ids.length) return;
                const $btn = $(this);
                Swal.fire({
                    icon: 'question',
                    title: `Kirim ${ids.length} dokumen?`,
                    text: 'Setiap dokumen dikirim sebagai lampiran PDF ke email karyawannya masing-masing.',
                    showCancelButton: true,
                    confirmButtonText: 'Kirim semua',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#4f46e5'
                }).then(result => {
                    if (!result.isConfirmed) return;
                    $btn.prop('disabled', true);
                    $.post('{{ route('documents.bulk-send') }}', { document_ids: ids })
                        .done(res => {
                            Swal.fire({ icon: res.skipped ? 'info' : 'success', title: 'Masuk antrian', text: res.message });
                            selected.clear();
                            table.ajax.reload(null, false);
                        })
                        .fail(xhr => Swal.fire({ icon: 'error', title: 'Tidak terkirim', text: errText(xhr, 'Gagal memasukkan dokumen ke antrian kirim.') }))
                        .always(() => { $btn.prop('disabled', false); updateSelectionUI(); });
                });
            });

            // ── Log aktivitas ──
            $('#activity-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                dom: 'rt<"pr-dt-bottom"<"pr-dt-left"li>p>',
                ajax: '{{ route('documents.activities') }}',
                lengthMenu: [[10, 25, 50], [10, 25, 50]],
                language: {
                    processing: 'Memuat',
                    lengthMenu: 'Tampilkan _MENU_',
                    info: '_START_ sampai _END_ dari _TOTAL_',
                    infoEmpty: 'Tidak ada data',
                    emptyTable: 'Belum ada aktivitas.',
                    paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'event_badge', name: 'event', orderable: false, searchable: false },
                    { data: 'description', name: 'description' },
                    { data: 'causer', name: 'causer', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at', searchable: false },
                ],
                order: [[4, 'desc']],
            });

            @if (session('success'))
                Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('success')) });
            @endif
        });
    </script>
@endpush
