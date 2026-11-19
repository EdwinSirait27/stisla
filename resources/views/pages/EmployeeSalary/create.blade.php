@extends('layouts.app')
@section('title', 'Tambah gaji karyawan')
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand · redesign: full
         * paper: oklch(98.4% 0.004 265) · accent: oklch(61% 0.17 276) · display: Nunito · body: Nunito
         * nav: kept (Stisla shell) · signature: the take-home this salary produces, and how it compares to the one in force · states: no employee, DW, PKWT/OJT, first salary, same-date clash, scheduled, older than current
         * axes: light/rounded-sans/chromatic-indigo · posture: app · contrast: pass · v2.0.0 */
        /* Hallmark · pre-emit critique: P4 H4 E4 S4 R4 V4 · weakest: E, the estimate excludes overtime/tax/other deductions and says so */
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

        .pr-btn-sm {
            min-height: 32px;
            padding: 0 var(--pr-space-sm);
            font-size: var(--pr-text-xs);
        }
        .pr-empty-note {
            margin: 0;
            padding: var(--pr-space-md) var(--pr-space-lg);
            font-size: var(--pr-text-sm);
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


        /* ── Form gaji ── */
        .status-badge.is-draft {
            background: var(--pr-paper-2);
            color: var(--pr-ink-2);
        }

        .sal-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 360px);
            gap: var(--pr-space-lg);
            align-items: start;
        }

        .sal-grid .pr-panel {
            margin-bottom: 0;
        }

        .sal-side {
            display: flex;
            flex-direction: column;
            gap: var(--pr-space-lg);
            position: sticky;
            top: 90px;
        }

        .sal-section {
            margin: 0;
            padding: var(--pr-space-md) var(--pr-space-lg);
            border: 0;
            border-bottom: 1px solid var(--pr-rule);
            min-width: 0;
        }

        .sal-section legend {
            float: left;
            width: 100%;
            margin: 0 0 var(--pr-space-sm);
            font-size: var(--pr-text-md);
            font-weight: 800;
            color: var(--pr-ink);
        }

        .sal-section legend span {
            display: block;
            font-size: var(--pr-text-xs);
            font-weight: 500;
            color: var(--pr-muted);
        }

        .sal-section legend + * {
            clear: both;
        }

        .sal-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: var(--pr-space-md);
        }

        .sal-row.is-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .sal-field {
            min-width: 0;
        }

        .sal-field[hidden] {
            display: none;
        }

        .sal-field.is-wide {
            grid-column: span 1;
        }

        .sal-field label {
            display: block;
            margin-bottom: 4px;
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-ink);
        }

        .sal-input {
            width: 100%;
            min-height: 40px;
            padding: 8px 12px;
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-panel);
            font-family: var(--pr-font);
            font-size: var(--pr-text-md);
            color: var(--pr-ink);
            transition: border-color var(--pr-dur-micro) var(--pr-ease);
        }

        .sal-input:hover {
            border-color: var(--pr-muted);
        }

        .sal-input:focus-visible {
            outline: 2px solid var(--pr-focus);
            outline-offset: 0;
            border-color: var(--pr-accent);
        }

        .sal-input.is-invalid {
            border-color: var(--pr-error);
        }

        .sal-money {
            position: relative;
        }

        .sal-money span {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-muted);
            pointer-events: none;
        }

        .sal-money .sal-input {
            padding-left: 38px;
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .sal-help {
            display: block;
            margin-top: 4px;
            font-size: var(--pr-text-xs);
            line-height: 1.45;
            color: var(--pr-muted);
        }

        .sal-help:empty {
            display: none;
        }

        .sal-help.is-warn {
            font-weight: 700;
            color: var(--pr-warn);
        }

        .sal-help.is-error,
        .sal-error {
            display: block;
            margin-top: 4px;
            font-size: var(--pr-text-xs);
            font-weight: 700;
            color: var(--pr-error);
        }

        .sal-type-note {
            margin: var(--pr-space-sm) 0 0;
            padding: var(--pr-space-xs) var(--pr-space-sm);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-accent-soft);
            font-size: var(--pr-text-sm);
            line-height: 1.5;
            color: var(--pr-accent-deep);
        }

        .sal-emp {
            padding: 8px 12px;
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-paper);
        }

        .sal-emp-name {
            display: block;
            font-weight: 700;
            color: var(--pr-ink);
        }

        .sal-emp-sub {
            display: block;
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
        }

        .sal-errors {
            margin: var(--pr-space-md) var(--pr-space-lg) 0;
            padding: var(--pr-space-sm) var(--pr-space-md);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-error-soft);
            font-size: var(--pr-text-sm);
            color: var(--pr-ink);
        }

        .sal-errors ul {
            margin: 4px 0 0;
            padding-left: 1.2em;
        }

        .sal-foot {
            display: flex;
            align-items: center;
            gap: var(--pr-space-xs);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            background: var(--pr-paper);
            border-radius: 0 0 var(--pr-radius) var(--pr-radius);
        }

        .sal-foot-gap {
            flex: 1;
        }

        .sal-sum {
            margin: 0;
            padding: var(--pr-space-sm) var(--pr-space-lg);
        }

        .sal-sum > div {
            display: flex;
            justify-content: space-between;
            gap: var(--pr-space-sm);
            padding: 6px 0;
        }

        .sal-sum dt {
            font-size: var(--pr-text-sm);
            font-weight: 600;
            color: var(--pr-ink-2);
        }

        .sal-sum dd {
            margin: 0;
            font-size: var(--pr-text-sm);
            font-weight: 700;
            text-align: right;
            font-variant-numeric: tabular-nums;
            color: var(--pr-ink);
        }

        .sal-sum .is-total {
            margin-top: 4px;
            padding-top: var(--pr-space-sm);
            border-top: 1px solid var(--pr-rule);
        }

        .sal-sum .is-total dt {
            font-weight: 800;
            color: var(--pr-ink);
        }

        .sal-sum .is-total dd {
            font-size: var(--pr-text-lg);
            font-weight: 800;
            color: var(--pr-accent-deep);
        }

        .sal-compare {
            margin: 0;
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-top: 1px solid var(--pr-rule);
            font-size: var(--pr-text-xs);
            line-height: 1.5;
            color: var(--pr-ink-2);
        }

        .sal-compare.is-up {
            color: var(--pr-success);
            font-weight: 700;
        }

        .sal-compare.is-down {
            color: var(--pr-warn);
            font-weight: 700;
        }

        .sal-hist {
            margin: 0;
            padding: 0;
            list-style: none;
            max-height: 320px;
            overflow-y: auto;
        }

        .sal-hist li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--pr-space-sm);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-top: 1px solid var(--pr-rule);
        }

        .sal-hist li:first-child {
            border-top: 0;
        }

        .sal-hist li.is-self {
            background: var(--pr-accent-soft);
        }

        .sal-hist-date {
            display: block;
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-ink);
        }

        .sal-hist-sub {
            display: block;
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
            font-variant-numeric: tabular-nums;
        }

        .pr-empty-note {
            line-height: 1.55;
        }

        /* Select2 disamakan dengan input */
        .select2-container--default .select2-selection--single {
            height: 40px;
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius-sm);
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
            padding-left: 12px;
            font-family: var(--pr-font);
            color: var(--pr-ink);
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px;
        }

        @media (max-width: 64rem) {
            .sal-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .sal-side {
                position: static;
            }
        }

        @media (max-width: 40rem) {
            .sal-row,
            .sal-row.is-3 {
                grid-template-columns: minmax(0, 1fr);
            }

            .sal-section,
            .sal-foot,
            .sal-sum,
            .sal-hist li,
            .sal-compare {
                padding-left: var(--pr-space-md);
                padding-right: var(--pr-space-md);
            }

            .sal-foot {
                flex-wrap: wrap;
            }

            .sal-input {
                font-size: 16px;
            }
        }
    </style>
@endpush

@section('main')
    @include('pages.EmployeeSalary._form')
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            const IS_EDIT = @json(isset($salary));
            const EDIT_ID = @json(isset($salary) ? $salary->id : null);
            const HISTORY = IS_EDIT ? { [@json($salary->employee_id ?? null)]: @json($history) } : @json($history ?? []);
            const TODAY = @json(now()->toDateString());
            const MONEY = ['basic_salary', 'position_allowance', 'daily_rate', 'meal_allowance', 'house_allowance', 'transport_allowance', 'bpjs_kesehatan', 'bpjs_ketenagakerjaan'];
            const KEY = { basic_salary: 'basic', position_allowance: 'position', daily_rate: 'daily', meal_allowance: 'meal', house_allowance: 'house', transport_allowance: 'transport', bpjs_kesehatan: 'bpjs_kes', bpjs_ketenagakerjaan: 'bpjs_tk' };

            // ── Format rupiah (1.500.000 atau 1.500.000,50) ──
            const parse = s => {
                s = String(s ?? '').replace(/[^\d,]/g, '');
                const [i, f = ''] = s.split(',');
                return Number((i || '0') + (f ? '.' + f.slice(0, 2) : '')) || 0;
            };
            const fmtInput = n => Number(n).toLocaleString('id-ID', { maximumFractionDigits: 2 });
            const rp = n => 'Rp' + Math.round(n).toLocaleString('id-ID');
            const dateId = d => new Date(d + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });

            document.querySelectorAll('.js-money').forEach(input => {
                const v = input.value.trim();
                if (/^\d+(\.\d+)?$/.test(v)) input.value = fmtInput(Number(v));
                input.addEventListener('input', function() {
                    // Pertahankan posisi kursor berdasarkan jumlah digit di kirinya
                    const pos = this.selectionStart;
                    const digitsLeft = this.value.slice(0, pos).replace(/[^\d,]/g, '').length;
                    let raw = this.value.replace(/[^\d,]/g, '');
                    const parts = raw.split(',');
                    let intPart = parts[0].replace(/^0+(?=\d)/, '');
                    let out = intPart ? Number(intPart).toLocaleString('id-ID') : '';
                    if (parts.length > 1) out += ',' + parts.slice(1).join('').slice(0, 2);
                    this.value = out;
                    let seen = 0, i = 0;
                    while (i < out.length && seen < digitsLeft) { if (/[\d,]/.test(out[i])) seen++; i++; }
                    this.setSelectionRange(i, i);
                    update();
                });
                input.addEventListener('blur', function() { if (this.value === '') this.value = '0'; update(); });
            });

            // ── Karyawan ──
            const $emp = $('#employee_id');
            if (!IS_EDIT) {
                $emp.select2({ width: '100%', placeholder: 'Cari nama atau NIP', allowClear: true });
                $emp.on('change', () => { applyEmployee(); update(); });
            }
            const empId = () => $emp.val();
            const status = () => (IS_EDIT ? $emp.data('status') : $emp.find(':selected').data('status')) || '';
            const isDw = () => String(status()).toUpperCase() === 'DW';
            const history = () => (HISTORY[empId()] ?? []);
            const others = () => history().filter(h => h.id !== EDIT_ID);
            const current = () => others().filter(h => h.date <= TODAY).sort((a, b) => b.date.localeCompare(a.date))[0] ?? null;

            function applyEmployee() {
                const has = !!empId();
                $('#salary-fields').prop('hidden', !has);
                $('[data-kind="monthly"]').prop('hidden', isDw());
                $('[data-kind="dw"]').prop('hidden', !isDw());
                $('#type-note').prop('hidden', !has).text(isDw()
                    ? 'Daily worker: dibayar tarif harian dikali hari hadir. Gaji pokok dan tunjangan jabatan tidak dipakai dan disimpan 0.'
                    : `${status() || 'Karyawan'}: gaji pokok dan tunjangan jabatan dibagi hari kerja lalu dikali hari hadir. Tarif harian tidak dipakai dan disimpan 0.`);
                $('#copy-current').prop('hidden', IS_EDIT || !current());
                renderHistory();
            }

            function renderHistory() {
                const list = $('#hist-list').empty();
                const rows = history();
                if (!empId()) { $('#hist-sub').text('Pilih karyawan dulu'); $('#hist-empty').prop('hidden', true); return; }
                $('#hist-sub').text(rows.length ? `${rows.length} data` : 'Belum ada');
                $('#hist-empty').prop('hidden', rows.length > 0);
                const cur = current();
                rows.forEach(h => {
                    const fixed = isDw() ? `${rp(h.daily)}/hari` : rp(h.basic + h.position);
                    let state = h.date > TODAY ? ['is-pending', 'Terjadwal'] : (cur && h.id === cur.id ? ['is-approved', 'Berlaku sekarang'] : ['is-draft', 'Lama']);
                    if (h.id === EDIT_ID) state = ['is-open', 'Sedang diubah'];
                    list.append(`<li class="${h.id === EDIT_ID ? 'is-self' : ''}">
                        <span><span class="sal-hist-date">${dateId(h.date)}</span><span class="sal-hist-sub">${fixed} + tunjangan ${rp(h.meal + h.house + h.transport)}</span></span>
                        <span class="status-badge ${state[0]}">${state[1]}</span></li>`);
                });
            }

            function val(name) { return parse(document.getElementById(name)?.value); }

            function update() {
                const dw = isDw();
                const fixed = dw ? val('daily_rate') : val('basic_salary') + val('position_allowance');
                const allow = val('meal_allowance') + val('house_allowance') + val('transport_allowance');
                const bpjs = val('bpjs_kesehatan') + val('bpjs_ketenagakerjaan');
                $('#sum-fixed-label').text(dw ? 'Tarif harian' : 'Gaji tetap');
                $('#sum-fixed').text(dw ? rp(fixed) + '/hari' : rp(fixed));
                $('#sum-allow').text(rp(allow));
                $('#sum-bpjs').text('−' + rp(bpjs));
                if (dw) {
                    $('#sum-net-label').text('Diterima');
                    $('#sum-net').text(`${rp(fixed)} × hari hadir + ${rp(allow - bpjs)}`);
                    $('#sum-sub').text('Daily worker, sebelum lembur, pajak, dan potongan lain');
                } else {
                    $('#sum-net-label').text('Perkiraan diterima');
                    $('#sum-net').text(rp(fixed + allow - bpjs));
                    $('#sum-sub').text('Kehadiran penuh, sebelum lembur, pajak, dan potongan lain');
                }
                // Bandingkan dengan gaji yang berlaku sekarang
                const cur = current();
                const $cmp = $('#sum-compare');
                if (cur && empId()) {
                    const curNet = dw ? cur.daily : cur.basic + cur.position + cur.meal + cur.house + cur.transport - cur.bpjs_kes - cur.bpjs_tk;
                    const newNet = dw ? fixed : fixed + allow - bpjs;
                    const diff = newNet - curNet;
                    const what = dw ? 'tarif harian' : 'perkiraan diterima';
                    const ref = IS_EDIT ? `gaji sebelumnya (berlaku sejak ${dateId(cur.date)})` : `gaji yang berlaku sejak ${dateId(cur.date)}`;
                    $cmp.prop('hidden', false).toggleClass('is-up', diff > 0).toggleClass('is-down', diff < 0).text(
                        diff === 0 ? `Sama dengan ${ref}.`
                            : `${what[0].toUpperCase() + what.slice(1)} ${diff > 0 ? 'naik' : 'turun'} ${rp(Math.abs(diff))} (${(Math.abs(diff) / (curNet || 1) * 100).toFixed(1).replace('.', ',')}%) dibanding ${ref}.`);
                } else {
                    $cmp.prop('hidden', true);
                }
                dateNote();
            }

            function dateNote() {
                const d = $('#effective_date').val();
                const $n = $('#date-note').removeClass('is-warn is-error');
                if (!d || !empId()) { $n.text(''); return; }
                const clash = others().find(h => h.date === d);
                const cur = current();
                if (clash) {
                    IS_EDIT
                        ? $n.addClass('is-error').text('Sudah ada data gaji lain di tanggal ini. Pilih tanggal lain.')
                        : $n.addClass('is-warn').text('Sudah ada gaji di tanggal ini. Menyimpan akan menimpa data tersebut.');
                } else if (d > TODAY) {
                    $n.text('Terjadwal: mulai dipakai di payroll periode yang mencakup tanggal ini.');
                } else if (cur && d < cur.date) {
                    $n.addClass('is-warn').text(`Lebih lama dari gaji yang berlaku sejak ${dateId(cur.date)}, jadi tidak dipakai untuk payroll berikutnya.`);
                } else {
                    $n.text('Langsung berlaku untuk payroll yang belum digenerate.');
                }
            }
            $('#effective_date').on('input change', update);

            $('#copy-current').on('click', function() {
                const cur = current();
                if (!cur) return;
                MONEY.forEach(f => { const el = document.getElementById(f); if (el) el.value = fmtInput(cur[KEY[f]]); });
                update();
            });

            applyEmployee();
            update();

            @if (session('error'))
                Swal.fire({ icon: 'error', title: 'Gagal menyimpan', text: @json(session('error')) });
            @endif
        });
    </script>
@endpush
