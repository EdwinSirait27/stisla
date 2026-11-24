@extends('layouts.app')
@section('title', 'Absensi fingerprint')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand · redesign: full
         * paper: oklch(98.4% 0.004 265) · accent: oklch(61% 0.17 276) · display: Nunito · body: Nunito
         * nav: kept (Stisla shell) · signature: the pending-approval count for the viewer's role, one click filters the table to it · states: HR, SPV, employee-only, no scans, bulk select
         * axes: light/rounded-sans/chromatic-indigo · posture: app · contrast: pass · v2.0.0 */
        /* Hallmark · pre-emit critique: P4 H4 E4 S4 R4 V4 · weakest: E, dense scan tables; answered by folding device and roster under each cell */
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

        .pr-actions {
            display: flex;
            flex-wrap: wrap;
            gap: var(--pr-space-xs);
        }

        .pr-hello {
            display: flex;
            align-items: center;
            gap: var(--pr-space-md);
        }

        .pr-avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
            border: 1px solid var(--pr-rule);
        }

        .pr-hello-date {
            margin: 0 0 2px;
            font-size: var(--pr-text-sm);
            color: var(--pr-muted);
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
        .pr-note {
            margin: auto 0 0;
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-top: 1px solid var(--pr-rule);
            font-size: var(--pr-text-xs);
            line-height: 1.5;
            color: var(--pr-ink-2);
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


        /* ── Karyawan ── */
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

        .status-badge.is-type { background: var(--pr-accent-soft); color: var(--pr-accent-deep); }
        .status-badge.is-draft { background: var(--pr-paper-2); color: var(--pr-ink-2); }
        .status-badge.is-open { background: var(--pr-accent-soft); color: var(--pr-accent-deep); }

        /* Stat strip */
        .emp-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 170px), 1fr));
            margin: 0;
        }

        .emp-stats > div {
            padding: var(--pr-space-md) var(--pr-space-lg);
            border-right: 1px solid var(--pr-rule);
        }

        .emp-stats > div:last-child {
            border-right: 0;
        }

        .emp-stats dt {
            font-size: var(--pr-text-sm);
            font-weight: 600;
            color: var(--pr-ink-2);
        }

        .emp-stats dd {
            margin: 2px 0 0;
            font-size: var(--pr-text-2xl);
            font-weight: 800;
            font-variant-numeric: tabular-nums;
            color: var(--pr-ink);
        }

        .emp-stats button {
            margin-top: 2px;
            padding: 0;
            border: 0;
            background: none;
            font-family: var(--pr-font);
            font-size: var(--pr-text-xs);
            font-weight: 700;
            color: var(--pr-accent-strong);
            cursor: pointer;
        }

        .emp-stats button:hover {
            text-decoration: underline;
        }

        .emp-stats button:focus-visible {
            outline: 2px solid var(--pr-focus);
            outline-offset: 2px;
        }

        /* Toolbar & select */
        .emp-filters {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: var(--pr-space-sm);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-bottom: 1px solid var(--pr-rule);
        }

        .emp-filters .pr-field {
            flex: 1 1 150px;
            max-width: 220px;
            min-width: 0;
        }

        .emp-filters .pr-field label {
            display: block;
            margin-bottom: 4px;
            font-size: var(--pr-text-xs);
            font-weight: 600;
            color: var(--pr-ink-2);
        }

        .emp-select,
        .emp-input {
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

        .emp-select:focus-visible,
        .emp-input:focus-visible {
            outline: 2px solid var(--pr-focus);
            outline-offset: 0;
        }

        .emp-filter-actions {
            display: flex;
            flex-wrap: wrap;
            gap: var(--pr-space-xs);
            margin-left: auto;
        }

        .select2-container--default .select2-selection--single,
        .select2-container--default .select2-selection--multiple {
            min-height: 36px;
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            font-family: var(--pr-font);
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 34px;
            padding-left: 12px;
            font-size: var(--pr-text-sm);
            color: var(--pr-ink);
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 34px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            border: 0;
            border-radius: var(--pr-pill);
            background: var(--pr-accent-soft);
            font-size: var(--pr-text-xs);
            font-weight: 700;
            color: var(--pr-accent-deep);
        }

        /* Tabel */
        .emp-cell {
            display: flex;
            align-items: center;
            gap: var(--pr-space-sm);
            min-width: 200px;
        }

        .emp-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--pr-accent-soft);
            font-size: var(--pr-text-xs);
            font-weight: 800;
            color: var(--pr-accent-deep);
        }

        .emp-los {
            font-size: var(--pr-text-xs);
            color: var(--pr-ink-2);
            white-space: nowrap;
        }

        /* Bagan */
        .emp-chart-tools {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: var(--pr-space-sm);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-bottom: 1px solid var(--pr-rule);
        }

        .emp-chart-tools .pr-field {
            flex: 0 1 220px;
        }

        .emp-chart-tools .pr-field label {
            display: block;
            margin-bottom: 4px;
            font-size: var(--pr-text-xs);
            font-weight: 600;
            color: var(--pr-ink-2);
        }

        .emp-zoom {
            display: flex;
            align-items: center;
            gap: var(--pr-space-2xs);
            margin-left: auto;
        }

        .emp-zoom span {
            min-width: 44px;
            text-align: center;
            font-size: var(--pr-text-xs);
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            color: var(--pr-ink-2);
        }

        #chart-area {
            position: relative;
            height: 600px;
            overflow: hidden;
            background: var(--pr-paper);
            cursor: grab;
            border-radius: 0 0 var(--pr-radius) var(--pr-radius);
        }

        #chart-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: var(--pr-space-lg);
            text-align: center;
            font-size: var(--pr-text-sm);
            color: var(--pr-muted);
        }

        #svg-chart {
            position: absolute;
            top: 0;
            left: 0;
            transform-origin: 0 0;
        }

        /* Profil (show) */
        .emp-avatar-lg {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
            border: 1px solid var(--pr-rule);
            background: var(--pr-accent-soft);
        }

        .emp-initials-lg {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: var(--pr-text-2xl);
            font-weight: 800;
            color: var(--pr-accent-deep);
        }

        .emp-chips {
            display: flex;
            flex-wrap: wrap;
            gap: var(--pr-space-2xs);
            margin-top: var(--pr-space-xs);
        }

        .emp-chip {
            display: inline-block;
            padding: 2px 10px;
            border-radius: var(--pr-pill);
            background: var(--pr-paper-2);
            font-size: var(--pr-text-xs);
            font-weight: 600;
            color: var(--pr-ink-2);
        }

        .emp-facts {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 180px), 1fr));
            margin: 0;
        }

        .emp-facts > div {
            padding: var(--pr-space-md) var(--pr-space-lg);
            border-right: 1px solid var(--pr-rule);
        }

        .emp-facts > div:last-child {
            border-right: 0;
        }

        .emp-facts dt {
            font-size: var(--pr-text-xs);
            font-weight: 600;
            color: var(--pr-ink-2);
        }

        .emp-facts dd {
            margin: 2px 0 0;
            font-size: var(--pr-text-md);
            font-weight: 800;
            color: var(--pr-ink);
        }

        .emp-facts dd small {
            display: block;
            font-size: var(--pr-text-xs);
            font-weight: 500;
            color: var(--pr-muted);
        }

        .emp-facts dd.is-warn {
            color: var(--pr-warn);
        }

        .emp-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: var(--pr-space-lg);
            align-items: start;
        }

        .emp-col {
            display: flex;
            flex-direction: column;
            gap: var(--pr-space-lg);
            min-width: 0;
        }

        .emp-col .pr-panel {
            margin-bottom: 0;
        }

        .emp-dl {
            margin: 0;
            padding: var(--pr-space-xs) var(--pr-space-lg) var(--pr-space-md);
        }

        .emp-dl > div {
            display: grid;
            grid-template-columns: minmax(0, 40%) minmax(0, 1fr);
            gap: var(--pr-space-sm);
            padding: 5px 0;
        }

        .emp-dl dt {
            font-size: var(--pr-text-sm);
            font-weight: 600;
            color: var(--pr-ink-2);
        }

        .emp-dl dd {
            margin: 0;
            font-size: var(--pr-text-sm);
            font-weight: 600;
            color: var(--pr-ink);
            overflow-wrap: anywhere;
        }

        .emp-dl dd.is-empty {
            font-weight: 500;
            color: var(--pr-muted);
        }

        .emp-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .emp-list li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--pr-space-sm);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-top: 1px solid var(--pr-rule);
        }

        .emp-list li:first-child {
            border-top: 0;
        }

        .emp-list-name {
            display: block;
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-ink);
        }

        .emp-list-sub {
            display: block;
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
        }

        .emp-list a {
            color: var(--pr-ink);
        }

        .emp-placement {
            padding: var(--pr-space-sm) var(--pr-space-lg) var(--pr-space-md);
        }

        .emp-placement + .emp-placement {
            border-top: 1px solid var(--pr-rule);
        }

        .emp-placement h3 {
            margin: 0 0 var(--pr-space-xs);
            font-size: var(--pr-text-xs);
            font-weight: 700;
            color: var(--pr-ink-2);
        }

        .emp-pills {
            display: flex;
            flex-wrap: wrap;
            gap: var(--pr-space-2xs);
        }

        .emp-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-pill);
            font-size: var(--pr-text-sm);
            font-weight: 600;
            color: var(--pr-ink);
        }

        .emp-pill.is-primary {
            border-color: var(--pr-accent);
            background: var(--pr-accent-soft);
            color: var(--pr-accent-deep);
        }

        .emp-photos {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: var(--pr-space-sm);
            padding: var(--pr-space-md) var(--pr-space-lg);
        }

        .emp-photo span {
            display: block;
            margin-bottom: 4px;
            font-size: var(--pr-text-xs);
            font-weight: 700;
            color: var(--pr-ink-2);
        }

        .emp-photo-box {
            display: flex;
            align-items: center;
            justify-content: center;
            aspect-ratio: 4 / 3;
            border: 1px dashed var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-paper);
            overflow: hidden;
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
        }

        .emp-photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .emp-photo-box a {
            display: block;
            width: 100%;
            height: 100%;
        }

        .emp-money {
            font-variant-numeric: tabular-nums;
        }

        /* Form */
        .emp-form-grid {
            display: grid;
            grid-template-columns: minmax(0, 220px) minmax(0, 1fr);
            gap: var(--pr-space-lg);
            align-items: start;
        }

        .emp-toc {
            position: sticky;
            top: 90px;
            margin: 0;
            padding: var(--pr-space-sm);
            list-style: none;
        }

        .emp-toc a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--pr-space-xs);
            padding: 8px 12px;
            border-radius: var(--pr-radius-sm);
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-ink-2);
            text-decoration: none;
        }

        .emp-toc a:hover {
            background: var(--pr-paper-2);
            color: var(--pr-ink);
            text-decoration: none;
        }

        .emp-toc a:focus-visible {
            outline: 2px solid var(--pr-focus);
            outline-offset: 1px;
        }

        .emp-toc .emp-toc-err {
            min-width: 20px;
            padding: 0 6px;
            border-radius: var(--pr-pill);
            background: var(--pr-error-soft);
            font-size: var(--pr-text-xs);
            text-align: center;
            color: var(--pr-error);
        }

        .emp-sections {
            display: flex;
            flex-direction: column;
            gap: var(--pr-space-lg);
            min-width: 0;
        }

        .emp-sections > .pr-panel {
            margin-bottom: 0;
            scroll-margin-top: 90px;
        }

        .emp-fields {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: var(--pr-space-md) var(--pr-space-lg);
            padding: var(--pr-space-md) var(--pr-space-lg) var(--pr-space-lg);
        }

        .emp-fields.is-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .emp-field {
            min-width: 0;
        }

        .emp-field.is-wide {
            grid-column: 1 / -1;
        }

        .emp-field > label,
        .emp-field > .emp-label {
            display: block;
            margin-bottom: 4px;
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-ink);
        }

        .emp-req {
            color: var(--pr-error);
        }

        .emp-field .emp-input,
        .emp-field .emp-select,
        .emp-field textarea.emp-input {
            min-height: 40px;
            padding: 8px 12px;
            font-size: var(--pr-text-md);
        }

        .emp-field textarea.emp-input {
            min-height: 72px;
            resize: vertical;
        }

        .emp-input.is-invalid,
        .emp-select.is-invalid {
            border-color: var(--pr-error);
        }

        .emp-help {
            display: block;
            margin-top: 4px;
            font-size: var(--pr-text-xs);
            line-height: 1.45;
            color: var(--pr-muted);
        }

        .emp-error {
            display: block;
            margin-top: 4px;
            font-size: var(--pr-text-xs);
            font-weight: 700;
            color: var(--pr-error);
        }

        .emp-check {
            display: flex;
            align-items: flex-start;
            gap: var(--pr-space-xs);
            margin: 0;
            padding: var(--pr-space-sm);
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            cursor: pointer;
        }

        .emp-check:has(input:checked) {
            border-color: var(--pr-accent);
            background: var(--pr-accent-soft);
        }

        .emp-check input {
            margin-top: 3px;
            accent-color: var(--pr-accent-strong);
        }

        .emp-check strong {
            display: block;
            font-size: var(--pr-text-sm);
            color: var(--pr-ink);
        }

        .emp-check small {
            display: block;
            font-size: var(--pr-text-xs);
            line-height: 1.45;
            color: var(--pr-ink-2);
        }

        .emp-inline-check {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 6px;
            font-size: var(--pr-text-xs);
            font-weight: 600;
            color: var(--pr-ink-2);
            cursor: pointer;
        }

        .emp-upload {
            display: flex;
            flex-direction: column;
            gap: var(--pr-space-xs);
        }

        .emp-upload .emp-photo-box img {
            cursor: zoom-in;
        }

        .emp-file {
            font-size: var(--pr-text-sm);
        }

        .emp-errors {
            margin: 0 0 var(--pr-space-lg);
            padding: var(--pr-space-sm) var(--pr-space-md);
            border-radius: var(--pr-radius);
            background: var(--pr-error-soft);
            font-size: var(--pr-text-sm);
            color: var(--pr-ink);
        }

        .emp-errors ul {
            margin: 4px 0 0;
            padding-left: 1.2em;
        }

        .emp-note {
            margin: 0;
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-top: 1px solid var(--pr-rule);
            background: var(--pr-accent-soft);
            font-size: var(--pr-text-sm);
            line-height: 1.5;
            color: var(--pr-accent-deep);
        }

        .emp-savebar {
            position: sticky;
            bottom: 0;
            z-index: 20;
            display: flex;
            align-items: center;
            gap: var(--pr-space-xs);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius);
            background: var(--pr-panel);
            box-shadow: 0 -4px 16px var(--pr-shadow-pressed);
        }

        .emp-savebar span {
            flex: 1;
            font-size: var(--pr-text-xs);
            color: var(--pr-ink-2);
        }

        .pr-empty-note,
        .pr-note {
            line-height: 1.55;
        }

        @media (max-width: 64rem) {
            .emp-grid,
            .emp-form-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .emp-toc {
                position: static;
                display: flex;
                flex-wrap: wrap;
                gap: var(--pr-space-2xs);
            }

            .emp-fields.is-3 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 40rem) {
            .emp-stats > div,
            .emp-facts > div {
                border-right: 0;
                border-bottom: 1px solid var(--pr-rule);
                padding-left: var(--pr-space-md);
                padding-right: var(--pr-space-md);
            }

            .emp-filters,
            .emp-chart-tools,
            .emp-dl,
            .emp-list li,
            .emp-placement,
            .emp-photos,
            .emp-fields,
            .emp-savebar {
                padding-left: var(--pr-space-md);
                padding-right: var(--pr-space-md);
            }

            .emp-filters .pr-field {
                max-width: none;
                flex-basis: 100%;
            }

            .emp-fields,
            .emp-fields.is-3,
            .emp-photos {
                grid-template-columns: minmax(0, 1fr);
            }

            .emp-dl > div {
                grid-template-columns: minmax(0, 1fr);
                gap: 0;
            }

            .emp-zoom {
                margin-left: 0;
            }

            .emp-input,
            .emp-select {
                font-size: 16px;
            }
        }

        /* ── TOIL ── */
        .tl-type {
            display: inline-block;
            padding: 1px 8px;
            border-radius: var(--pr-pill);
            font-size: 11px;
            font-weight: 700;
        }

        .tl-type.is-cash { background: var(--pr-success-soft); color: var(--pr-success); }
        .tl-type.is-toil { background: var(--pr-accent-soft); color: var(--pr-accent-deep); }

        .tl-days { font-size: var(--pr-text-sm); font-weight: 700; color: var(--pr-ink-2); white-space: nowrap; }
        .tl-days.is-warn { color: var(--pr-warn); }
        .tl-days.is-danger { color: var(--pr-error); }

        .tl-num {
            font-variant-numeric: tabular-nums;
            font-weight: 700;
            white-space: nowrap;
        }

        .tl-hero {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(0, 2fr);
        }

        .tl-hero-main {
            padding: var(--pr-space-lg);
            border-right: 1px solid var(--pr-rule);
            background: var(--pr-accent-soft);
        }

        .tl-hero-label {
            margin: 0;
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-accent-deep);
        }

        .tl-hero-value {
            margin: 2px 0 0;
            font-size: 2.4rem;
            font-weight: 800;
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
            color: var(--pr-ink);
        }

        .tl-hero-value small {
            font-size: var(--pr-text-md);
            font-weight: 700;
            color: var(--pr-ink-2);
        }

        .tl-hero-sub {
            margin: var(--pr-space-xs) 0 0;
            font-size: var(--pr-text-sm);
            line-height: 1.5;
            color: var(--pr-ink-2);
        }

        .tl-hero-sub.is-warn { color: var(--pr-warn); font-weight: 700; }

        .tl-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 160px), 1fr));
            margin: 0;
        }

        .tl-stats > div {
            padding: var(--pr-space-md) var(--pr-space-lg);
            border-right: 1px solid var(--pr-rule);
            border-bottom: 1px solid var(--pr-rule);
        }

        .tl-stats > div.is-warn { background: var(--pr-warn-soft); }

        .tl-stats dt {
            font-size: var(--pr-text-sm);
            font-weight: 600;
            color: var(--pr-ink-2);
        }

        .tl-stats dd {
            margin: 2px 0 0;
            font-size: var(--pr-text-xl);
            font-weight: 800;
            font-variant-numeric: tabular-nums;
            color: var(--pr-ink);
        }

        .tl-stats dd small {
            font-size: var(--pr-text-sm);
            font-weight: 600;
            color: var(--pr-muted);
        }

        .tl-stats > div > span {
            display: block;
            font-size: var(--pr-text-xs);
            line-height: 1.45;
            color: var(--pr-ink-2);
        }

        .tl-rules {
            margin: 0;
            padding: var(--pr-space-sm) var(--pr-space-lg) var(--pr-space-sm) calc(var(--pr-space-lg) + 1.1em);
            border-top: 1px solid var(--pr-rule);
            font-size: var(--pr-text-xs);
            line-height: 1.6;
            color: var(--pr-ink-2);
        }

        .tl-rules strong { color: var(--pr-ink); }

        .tl-reason {
            display: block;
            max-width: 46ch;
            font-size: var(--pr-text-sm);
            line-height: 1.45;
            color: var(--pr-ink);
            overflow-wrap: anywhere;
        }

        .tl-form-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: var(--pr-space-xs);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-top: 1px solid var(--pr-rule);
            background: var(--pr-paper);
            border-radius: 0 0 var(--pr-radius) var(--pr-radius);
        }

        .tl-form-actions span {
            flex: 1;
            font-size: var(--pr-text-xs);
            color: var(--pr-ink-2);
        }

        .tl-box {
            display: flex;
            align-items: center;
            min-height: 40px;
            padding: 8px 12px;
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-paper);
            font-size: var(--pr-text-sm);
            line-height: 1.45;
            color: var(--pr-ink-2);
        }

        .tl-box strong { color: var(--pr-ink); }
        .tl-box.is-ok { border-color: var(--pr-accent); background: var(--pr-accent-soft); }
        .tl-box.is-bad { border-color: var(--pr-error); background: var(--pr-error-soft); color: var(--pr-error); }

        .emp-help.is-ok { color: var(--pr-success); font-weight: 700; }
        .emp-help.is-bad { color: var(--pr-error); font-weight: 700; }

        .tl-choice {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: var(--pr-space-xs);
        }

        #balance-table td, #history-table td, #all-balances-table td, #approval-table td, #assignment-table td { vertical-align: top; }

        .modal-content { border: 0; border-radius: var(--pr-radius); font-family: var(--pr-font); }
        .modal-header { border-bottom: 1px solid var(--pr-rule); }
        .modal-title { font-size: var(--pr-text-lg); font-weight: 800; color: var(--pr-ink); }
        .modal-footer { border-top: 1px solid var(--pr-rule); }

        @media (max-width: 64rem) {
            .tl-hero { grid-template-columns: minmax(0, 1fr); }
            .tl-hero-main { border-right: 0; border-bottom: 1px solid var(--pr-rule); }
        }

        @media (max-width: 40rem) {
            .tl-hero-main, .tl-stats > div, .tl-rules, .tl-form-actions { padding-left: var(--pr-space-md); padding-right: var(--pr-space-md); }
            .tl-choice { grid-template-columns: minmax(0, 1fr); }
            .tl-hero-value { font-size: 2rem; }
        }

        .lv-link { padding: 0; border: 0; background: none; font-family: var(--pr-font); font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-accent-strong); cursor: pointer; }
        .lv-link:hover { text-decoration: underline; }
        .lv-link:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 2px; }
        .act-group { display: inline-flex; align-items: center; justify-content: flex-end; gap: var(--pr-space-xs); }

        /* ── Fingerprint ── */
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

        .bulk-bar.show { display: flex; }
        .bulk-bar-count { margin-right: var(--pr-space-xs); font-size: var(--pr-text-sm); font-weight: 700; color: var(--pr-ink); }

        .fp-time { font-variant-numeric: tabular-nums; font-weight: 700; color: var(--pr-ink); white-space: nowrap; }
        .fp-time.is-late { color: var(--pr-error); }
        .fp-time.is-edited { color: var(--pr-accent-deep); }
        .fp-miss { font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-warn); white-space: nowrap; }
        .fp-late { display: block; font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-error); white-space: nowrap; }
        .fp-dev { display: block; max-width: 16ch; font-size: var(--pr-text-xs); color: var(--pr-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        .fp-day {
            display: inline-block;
            padding: 1px 8px;
            border-radius: var(--pr-pill);
            background: var(--pr-paper-2);
            font-size: var(--pr-text-xs);
            font-weight: 700;
            color: var(--pr-ink-2);
        }

        .fp-day.is-work { background: var(--pr-accent-soft); color: var(--pr-accent-deep); }
        .fp-day.is-off { background: var(--pr-warn-soft); color: var(--pr-warn); }

        .fp-check { width: 18px; height: 18px; accent-color: var(--pr-accent); cursor: pointer; }
        .fp-check:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 2px; }

        .fp-changes { margin: 0; padding-left: 1.1em; font-size: var(--pr-text-xs); line-height: 1.55; color: var(--pr-ink-2); }
        .fp-changes del { color: var(--pr-error); }
        .fp-changes ins { color: var(--pr-success); text-decoration: none; font-weight: 700; }

        .fp-compare {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .fp-compare > div { padding: var(--pr-space-md) var(--pr-space-lg); border-right: 1px solid var(--pr-rule); }
        .fp-compare > div:last-child { border-right: 0; }
        .fp-compare > div.is-new { background: var(--pr-accent-soft); }
        .fp-compare h2 { margin: 0 0 var(--pr-space-xs); font-size: var(--pr-text-sm); font-weight: 800; color: var(--pr-ink); }

        .fp-kv { display: grid; grid-template-columns: minmax(0, 9rem) minmax(0, 1fr); gap: 6px var(--pr-space-sm); margin: 0; }
        .fp-kv dt { font-size: var(--pr-text-sm); font-weight: 600; color: var(--pr-ink-2); }
        .fp-kv dd { margin: 0; font-size: var(--pr-text-sm); font-weight: 700; color: var(--pr-ink); overflow-wrap: anywhere; }
        .fp-kv dd small { display: block; font-weight: 500; color: var(--pr-muted); }
        .fp-kv dd.is-change { color: var(--pr-accent-deep); }

        .fp-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 170px), 1fr));
            margin: 0;
            border-bottom: 1px solid var(--pr-rule);
        }

        .fp-meta > div { padding: var(--pr-space-sm) var(--pr-space-lg); border-right: 1px solid var(--pr-rule); }
        .fp-meta dt { font-size: var(--pr-text-xs); font-weight: 600; color: var(--pr-ink-2); }
        .fp-meta dd { margin: 2px 0 0; font-size: var(--pr-text-sm); font-weight: 700; color: var(--pr-ink); overflow-wrap: anywhere; }

        .fp-scans { margin: 0; padding: 0; list-style: none; }
        .fp-scans li { display: flex; gap: var(--pr-space-sm); padding: 4px 0; font-size: var(--pr-text-sm); color: var(--pr-ink-2); border-bottom: 1px dashed var(--pr-rule); }
        .fp-scans li:last-child { border-bottom: 0; }
        .fp-scans b { min-width: 5.5em; font-variant-numeric: tabular-nums; color: var(--pr-ink); }

        .fp-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 140px), 1fr)); gap: var(--pr-space-sm); padding: var(--pr-space-md) var(--pr-space-lg); }
        .fp-thumb { position: relative; margin: 0; border: 1px solid var(--pr-rule); border-radius: var(--pr-radius-sm); overflow: hidden; background: var(--pr-paper); }
        .fp-thumb button.fp-open { display: block; width: 100%; padding: 0; border: 0; background: none; cursor: zoom-in; }
        .fp-thumb button.fp-open:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: -2px; }
        .fp-thumb img { display: block; width: 100%; aspect-ratio: 4 / 3; object-fit: cover; }
        .fp-thumb figcaption { padding: 4px 8px; font-size: var(--pr-text-xs); color: var(--pr-ink-2); }
        .fp-thumb .fp-del { position: absolute; top: 6px; right: 6px; }

        .fp-note {
            margin: 0;
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-left: 3px solid var(--pr-error);
            background: var(--pr-error-soft);
            font-size: var(--pr-text-sm);
            line-height: 1.5;
            color: var(--pr-ink);
        }

        .fp-note.is-info { border-left-color: var(--pr-accent); background: var(--pr-accent-soft); }

        .fp-timeline { margin: 0; padding: var(--pr-space-sm) var(--pr-space-lg) var(--pr-space-md); list-style: none; }
        .fp-timeline li { padding: var(--pr-space-xs) 0; border-bottom: 1px solid var(--pr-rule); font-size: var(--pr-text-sm); color: var(--pr-ink); }
        .fp-timeline li:last-child { border-bottom: 0; }
        .fp-timeline small { display: block; color: var(--pr-muted); }

        .fp-drop {
            display: grid;
            place-items: center;
            gap: 4px;
            padding: var(--pr-space-md);
            border: 2px dashed var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-paper);
            text-align: center;
            font-size: var(--pr-text-sm);
            color: var(--pr-ink-2);
            cursor: pointer;
        }

        .fp-drop.dragover, .fp-drop:hover { border-color: var(--pr-accent); background: var(--pr-accent-soft); }
        .fp-drop:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 2px; }
        .evidence-file-list { display: grid; gap: 6px; margin-top: var(--pr-space-xs); }
        .evidence-file-item { display: flex; align-items: center; gap: var(--pr-space-xs); padding: 6px 10px; border: 1px solid var(--pr-rule); border-radius: var(--pr-radius-sm); font-size: var(--pr-text-sm); }
        .evidence-file-info { flex: 1; min-width: 0; }
        .evidence-file-name { font-weight: 700; color: var(--pr-ink); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .evidence-file-size { font-size: var(--pr-text-xs); color: var(--pr-muted); }
        .evidence-file-icon { color: var(--pr-accent-deep); }
        .evidence-file-remove { padding: 2px 8px; border: 1px solid var(--pr-rule); border-radius: var(--pr-radius-sm); background: var(--pr-panel); color: var(--pr-error); cursor: pointer; }
        .evidence-file-remove:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 2px; }

        .fp-section-gap { margin-top: var(--pr-space-lg); }
        #fingerprint-table td, #manual-added-table td, #log-table td { vertical-align: top; }

        @media (max-width: 48rem) {
            .fp-compare { grid-template-columns: minmax(0, 1fr); }
            .fp-compare > div { border-right: 0; border-bottom: 1px solid var(--pr-rule); }
            .fp-kv { grid-template-columns: minmax(0, 1fr); gap: 0; }
            .fp-kv dd { margin-bottom: var(--pr-space-xs); }
        }
    </style>
@endpush


@section('main')
    @php
        $statusOptions = $canManage
            ? ['draft' => 'Draft, belum dicek SPV', 'approved_spv' => 'Disetujui SPV, menunggu HR', 'rejected_spv' => 'Ditolak SPV', 'approved_hr' => 'Disetujui HR', 'rejected_hr' => 'Ditolak HR']
            : ['draft' => 'Draft, belum dicek SPV', 'approved_spv' => 'Disetujui SPV', 'rejected_spv' => 'Ditolak SPV'];
        $pendingStatus = $canManage ? 'approved_spv' : 'draft';
        $isEmployeeOnly = !$canManage && !$canSpvManager;
    @endphp
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <h1 class="pr-title">{{ $isEmployeeOnly ? 'Absensi fingerprint saya' : 'Absensi fingerprint' }}</h1>
                    <p class="pr-lede">
                        @if ($isEmployeeOnly)
                            Scan masuk dan pulang Anda dari mesin. Scan yang tidak lengkap bisa diklarifikasi dengan bukti foto.
                        @elseif ($canManage)
                            Scan mentah dari mesin, klarifikasi karyawan, dan rekap absensi per periode.
                        @else
                            Scan tim Anda. Klarifikasi berstatus draft menunggu persetujuan Anda.
                        @endif
                    </p>
                </div>
                @can('ManageFingerspot')
                    <div class="pr-actions">
                        <button type="button" id="addRecapBtn" class="pr-btn" disabled><i class="fas fa-plus" aria-hidden="true"></i> Add recap manual</button>
                        <button type="button" id="recapBtn" class="pr-btn pr-btn-primary" disabled><i class="fas fa-rotate-right" aria-hidden="true"></i> Rekap absensi</button>
                    </div>
                @endcan
            </header>

            <section class="pr-panel" aria-label="Ringkasan periode">
                <dl class="tl-stats">
                    @unless ($isEmployeeOnly)
                        <div id="pending-box"><dt>{{ $canManage ? 'Menunggu persetujuan HR' : 'Menunggu persetujuan Anda' }}</dt>
                            <dd id="stat-pending">–</dd>
                            <button type="button" class="lv-link" id="btn-pending" hidden>Tampilkan</button>
                            <span>{{ $canManage ? 'sudah disetujui SPV' : 'klarifikasi draft dari tim' }}</span></div>
                    @endunless
                    <div><dt>Hari tercatat</dt><dd id="stat-total">–</dd><span>baris scan di periode ini</span></div>
                    <div><dt>Tepat waktu</dt><dd id="stat-ontime">–</dd><span>masuk sebelum batas toleransi</span></div>
                    <div><dt>Terlambat</dt><dd id="stat-late">–</dd><span>lewat toleransi {{ $canManage ? '5 menit (10 menit untuk HO, Holding, DC)' : 'jam masuk' }}</span></div>
                    <div id="missing-box"><dt>Tanpa scan pulang</dt><dd id="stat-missing">–</dd><span>{{ $isEmployeeOnly ? 'bisa Anda klarifikasi' : 'belum ada scan pulang' }}</span></div>
                    <div><dt>Sudah diklarifikasi</dt><dd id="stat-updated">–</dd><span>ada koreksi jam</span></div>
                </dl>
            </section>

            <section class="pr-panel" aria-labelledby="fp-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="fp-title">Scan per hari</h2>
                        <span class="pr-panel-sub" id="fp-count">Memuat</span>
                    </div>
                    <div class="pr-search">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <label for="manualSearch" class="sr-only">Cari nama atau PIN</label>
                        <input type="search" id="manualSearch" placeholder="Cari nama atau PIN">
                    </div>
                </div>
                <div class="emp-filters">
                    @if ($canManage || $canSpvManager)
                        <div class="pr-field"><label for="store_name">Lokasi</label>
                            <select id="store_name" class="emp-select js-s2"><option value="">Semua lokasi</option>
                                @foreach ($stores as $store)<option value="{{ $store }}">{{ $store }}</option>@endforeach
                            </select></div>
                        <div class="pr-field"><label for="filter_status">Klarifikasi</label>
                            <select id="filter_status" class="emp-select"><option value="">Semua</option>
                                @foreach ($statusOptions as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                            </select></div>
                    @endif
                    <div class="pr-field"><label for="startDate">Dari</label>
                        <input type="date" id="startDate" class="emp-input" value="{{ $defaultStartDate }}" @unless ($canManage) min="{{ $minAllowed }}" max="{{ $maxAllowed }}" @endunless></div>
                    <div class="pr-field"><label for="endDate">Sampai</label>
                        <input type="date" id="endDate" class="emp-input" value="{{ $defaultEndDate }}" @unless ($canManage) min="{{ $minAllowed }}" max="{{ $maxAllowed }}" @endunless></div>
                    <div class="emp-filter-actions">
                        <button type="button" class="pr-btn pr-btn-sm" id="resetBtn">Reset</button>
                        <button type="button" class="pr-btn pr-btn-sm pr-btn-primary" id="filterBtn">Terapkan</button>
                        @canany(['ManageFingerspot', 'ManageFingerspotSPVManager'])
                            <button type="button" class="pr-btn pr-btn-sm js-export" data-type="excel"><i class="fas fa-file-excel" aria-hidden="true"></i> Excel</button>
                            <button type="button" class="pr-btn pr-btn-sm js-export" data-type="csv">CSV</button>
                        @endcanany
                    </div>
                </div>

                @canany(['ManageFingerspot', 'ManageFingerspotSPVManager'])
                    <div class="bulk-bar" id="bulk-action-bar" role="region" aria-label="Aksi massal">
                        <span class="bulk-bar-count" id="selected-count">0 dipilih</span>
                        @if ($canManage)
                            <button type="button" class="pr-btn pr-btn-sm pr-btn-primary btn-bulk-status" data-status="approved_hr">Setujui (HR)</button>
                            <button type="button" class="pr-btn pr-btn-sm pr-btn-danger btn-bulk-status" data-status="rejected_hr">Tolak (HR)</button>
                        @else
                            <button type="button" class="pr-btn pr-btn-sm pr-btn-primary btn-bulk-status" data-status="approved_spv">Setujui</button>
                            <button type="button" class="pr-btn pr-btn-sm pr-btn-danger btn-bulk-status" data-status="rejected_spv">Tolak</button>
                        @endif
                        <button type="button" class="pr-btn pr-btn-sm" id="bulk-clear">Batal pilih</button>
                    </div>
                @endcanany

                <div class="pr-table-wrap">
                    <table class="table" id="fingerprint-table">
                        <thead>
                            <tr>
                                <th><span class="sr-only">Pilih</span>@canany(['ManageFingerspot', 'ManageFingerspotSPVManager'])<input type="checkbox" id="check-all" class="fp-check" aria-label="Pilih semua yang bisa diproses">@endcanany</th>
                                <th>Karyawan</th>
                                <th>Lokasi</th>
                                <th>Tanggal</th>
                                <th>Masuk</th>
                                <th>Pulang</th>
                                <th>Istirahat</th>
                                <th>Lembur</th>
                                <th>Durasi</th>
                                <th>Klarifikasi</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </section>

            @can('ManageFingerspot')
                <section class="pr-panel" aria-labelledby="ma-title">
                    <div class="pr-panel-head">
                        <div>
                            <h2 class="pr-panel-title" id="ma-title">Recap manual</h2>
                            <span class="pr-panel-sub" id="ma-count">Absensi yang ditambahkan HR lewat Add recap, di periode yang sama</span>
                        </div>
                    </div>
                    <div class="pr-table-wrap">
                        <table class="table" id="manual-added-table">
                            <thead>
                                <tr>
                                    <th>Karyawan</th>
                                    <th>Lokasi</th>
                                    <th>Tanggal</th>
                                    <th>Masuk</th>
                                    <th>Pulang</th>
                                    <th>Durasi</th>
                                    <th class="text-right">Bukti</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </section>

                <section class="pr-panel" aria-labelledby="log-title">
                    <div class="pr-panel-head">
                        <div>
                            <h2 class="pr-panel-title" id="log-title">Riwayat perubahan</h2>
                            <span class="pr-panel-sub">Siapa mengubah apa pada klarifikasi fingerprint</span>
                        </div>
                    </div>
                    <div class="pr-table-wrap">
                        <table class="table" id="log-table">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Karyawan</th>
                                    <th>Aksi</th>
                                    <th>Perubahan</th>
                                    <th>Oleh</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </section>
            @endcan
        </section>
    </div>

    @can('ManageFingerspot')
        <div class="modal fade" id="addRecapFormModal" tabindex="-1" role="dialog" aria-labelledby="ar-title">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="ar-title">Add recap manual</h2>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body" style="max-height:72vh;overflow-y:auto">
                        <p class="fp-note is-info" style="margin-bottom:var(--pr-space-md)">Data ini dikirim ke email dan grup WhatsApp HR dan IT sebagai pertanggungjawaban. Pastikan karyawan sudah punya roster di tanggal tersebut.</p>
                        <div class="emp-fields" style="padding:0">
                            <div class="emp-field is-wide">
                                <label for="manualEmpIds">Karyawan <span class="emp-req" aria-hidden="true">*</span></label>
                                <select id="manualEmpIds" class="emp-select" multiple style="width:100%"></select>
                                <span class="emp-help">Bisa pilih lebih dari satu.</span>
                            </div>
                            <div class="emp-field">
                                <label for="manualScanDate">Tanggal mulai <span class="emp-req" aria-hidden="true">*</span></label>
                                <input type="date" id="manualScanDate" class="emp-input">
                            </div>
                            <div class="emp-field">
                                <label for="manualEndDate">Tanggal selesai <span class="emp-req" aria-hidden="true">*</span></label>
                                <input type="date" id="manualEndDate" class="emp-input">
                                <span class="emp-help">Sama dengan tanggal mulai kalau hanya 1 hari.</span>
                            </div>
                            <div class="emp-field is-wide">
                                <label for="manualShiftId">Shift</label>
                                <select id="manualShiftId" class="emp-select" style="width:100%"><option value="">Pakai shift dari roster karyawan</option></select>
                            </div>
                            <div class="emp-field is-wide">
                                <span class="emp-label">Bukti pendukung <span class="emp-req" aria-hidden="true">*</span></span>
                                <div class="fp-drop" id="evidenceDropzone" tabindex="0" role="button" aria-label="Pilih file bukti">
                                    <i class="fas fa-cloud-arrow-up" aria-hidden="true"></i>
                                    <span><strong>Klik</strong> atau tarik file ke sini</span>
                                    <small>JPG, PNG, GIF, PDF, DOC, DOCX, XLS, XLSX, maksimal 5 MB per file</small>
                                    <input type="file" id="evidenceFiles" data-no-compress multiple accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx" hidden>
                                </div>
                                <div id="evidenceFileList" class="evidence-file-list"></div>
                            </div>
                            <div class="emp-field is-wide">
                                <label for="manualReason">Alasan</label>
                                <textarea id="manualReason" class="emp-input" rows="3" maxlength="1000" placeholder="Mis. karyawan sudah klarifikasi masuk kerja tapi mesin fingerprint rusak"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="pr-btn" data-dismiss="modal">Batal</button>
                        <button type="button" class="pr-btn pr-btn-primary" onclick="submitManualRecap()" id="submitManualBtn"><i class="fas fa-paper-plane" aria-hidden="true"></i> Simpan dan rekap</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="evidenceModal" tabindex="-1" role="dialog" aria-labelledby="ev-title">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="ev-title">Bukti recap manual</h2>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <p class="fp-note is-info" id="evidenceReason" style="margin:0"></p>
                    <div id="evidenceList" class="fp-gallery"></div>
                    <div class="modal-footer"><button type="button" class="pr-btn" data-dismiss="modal">Tutup</button></div>
                </div>
            </div>
        </div>
    @endcan
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('partials.datatables-error', ['permission' => 'ManageFingerspot, ManageFingerspotSPVManager atau ViewFingerspot'])
    <script>
        window.FP = (function() {
            const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const STATUS = {
                draft: ['Draft', 'is-pending', 'menunggu SPV'], approved_spv: ['Disetujui SPV', 'is-open', 'menunggu HR'],
                rejected_spv: ['Ditolak SPV', 'is-rejected', ''], approved_hr: ['Disetujui HR', 'is-approved', 'final'],
                rejected_hr: ['Ditolak HR', 'is-rejected', 'final'],
            };
            const badge = s => { if (!s) return ''; const [l, c, sub] = STATUS[s] ?? [s, 'is-draft', '']; return `<span class="status-badge ${c}">${esc(l)}</span>${sub ? `<span class="pr-cell-sub">${sub}</span>` : ''}`; };
            const dur = d => {
                if (!d || d === 'invalid') return '<span class="fp-miss">tidak lengkap</span>';
                if (d === 'overnight') return '<span class="pr-cell-sub">shift malam, pulang besok</span>';
                if (d === 'Manual') return '<span class="pr-cell-sub">manual</span>';
                const m = String(d).match(/(\d+)\s*hour\w*\s*(\d+)\s*minute/);
                return m ? `<span class="fp-time">${m[1]}j ${m[2]}m</span>` : esc(d);
            };
            const fmtDate = s => { if (!s) return '-'; const d = new Date(s + 'T00:00:00'); return d.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' }); };
            const lang = empty => ({
                processing: 'Memuat', lengthMenu: 'Tampilkan _MENU_', info: '_START_ sampai _END_ dari _TOTAL_', infoEmpty: 'Tidak ada data',
                infoFiltered: '', zeroRecords: 'Tidak ada data yang cocok dengan filter ini.', emptyTable: empty,
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }, search: '',
            });
            return { esc, badge, dur, fmtDate, lang };
        })();
    </script>
    <script>
        $(function() {
            const F = window.FP;
            const canManage = @json($canManage), canBulk = @json($canManage || $canSpvManager);
            const DEFAULTS = { start: @json($defaultStartDate), end: @json($defaultEndDate) };
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
            $('.js-s2').each(function() { $(this).select2({ width: '100%' }); });

            function toggleHeaderButtons(enable) { $('#addRecapBtn, #recapBtn').prop('disabled', !enable); }

            const timeCell = (val, dev, extra) => {
                if (!val) return '<span class="pr-muted">-</span>';
                const t = String(val).split(', ');
                return `<span class="fp-time ${extra || ''}">${F.esc(t[0])}</span>${t.length > 1 ? `<span class="pr-cell-sub">+${t.length - 1} scan lagi</span>` : ''}${dev ? `<span class="fp-dev" title="${F.esc(dev)}">${F.esc(String(dev).split(', ')[0])}</span>` : ''}`;
            };
            const pair = (a, b) => (!a && !b) ? '<span class="pr-muted">-</span>'
                : `<span class="fp-time">${F.esc((a || '?').split(', ')[0])}</span><span class="pr-cell-sub">sampai ${F.esc((b || '?').split(', ')[0])}</span>`;

            var table = $('#fingerprint-table').DataTable({
                processing: true, serverSide: true, autoWidth: false,
                dom: 'rt<"pr-dt-bottom"<"pr-dt-left"li>p>',
                pageLength: 25, lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [[3, 'desc']],
                language: F.lang('Tidak ada scan di periode ini. Ubah tanggal atau lokasi lalu klik Terapkan.'),
                ajax: {
                    url: '{{ route('fingerprints.fingerprints') }}', type: 'POST',
                    data: d => { d.start_date = $('#startDate').val(); d.end_date = $('#endDate').val(); d.store_name = $('#store_name').val(); d.status = $('#filter_status').val(); },
                },
                columns: [
                    { data: 'edited_fingerprint_id', orderable: false, searchable: false, render: (d, t, r) => canBulk && r.can_select
                        ? `<input type="checkbox" class="fp-check fingerprint-checkbox" value="${F.esc(d)}" aria-label="Pilih ${F.esc(r.employee_name)} ${F.esc(r.scan_date)}">` : '' },
                    { data: 'employee_name', render: (d, t, r) => t !== 'display' ? d
                        : `<span class="pr-cell-name">${F.esc(d)}</span><span class="pr-cell-sub">${F.esc([r.employee_pengenal !== '-' ? r.employee_pengenal : null, 'PIN ' + r.pin, r.status_employee !== '-' ? r.status_employee : null].filter(Boolean).join(', '))}</span>` },
                    { data: 'name', render: (d, t, r) => `<span class="pr-cell-name">${F.esc(d)}</span><span class="pr-cell-sub">${F.esc(r.position_name)}</span>` },
                    { data: 'scan_date', render: (d, t, r) => {
                        if (t !== 'display') return d;
                        const work = r.day_type === 'Work';
                        const roster = r.roster_name && r.roster_name !== '-'
                            ? `<span class="fp-day ${work ? 'is-work' : 'is-off'}">${F.esc(r.roster_name)}${r.roster_time ? ' ' + F.esc(r.roster_time) : ''}</span>`
                            : '<span class="pr-cell-sub">belum ada roster</span>';
                        return `<span class="pr-cell-name">${F.esc(F.fmtDate(d))}</span>${roster}`;
                    } },
                    { data: 'combine_1', render: (d, t, r) => t !== 'display' ? (d || '')
                        : (r.in_1 ? timeCell(r.in_1, r.device_1, r.is_late_in ? 'is-late' : '') + (r.is_late_in ? `<span class="fp-late">telat ${r.late_minutes} menit</span>` : '')
                            : (r.edited_in_1 ? `<span class="fp-time is-edited">${F.esc(r.edited_in_1)}</span><span class="pr-cell-sub">dari klarifikasi</span>` : '<span class="fp-miss">tidak ada scan</span>')) },
                    { data: 'combine_2', render: (d, t, r) => t !== 'display' ? (d || '')
                        : (r.in_2 ? timeCell(r.in_2, r.device_2)
                            : (r.edited_in_2 ? `<span class="fp-time is-edited">${F.esc(r.edited_in_2)}</span><span class="pr-cell-sub">dari klarifikasi</span>`
                                : (r.duration === 'overnight' ? '<span class="pr-cell-sub">besok pagi</span>' : '<span class="fp-miss">tidak ada scan</span>'))) },
                    { data: 'combine_3', render: (d, t, r) => t !== 'display' ? (d || '') : pair(r.in_3, r.in_4) },
                    { data: 'combine_5', render: (d, t, r) => t !== 'display' ? (d || '') : pair(r.in_5, r.in_6) },
                    { data: 'duration', render: (d, t) => t !== 'display' ? d : F.dur(d) },
                    { data: 'edited_status', orderable: false, render: (d, t, r) => r.is_updated ? F.badge(d) || '<span class="status-badge is-draft">Diklarifikasi</span>' : '<span class="pr-muted">-</span>' },
                    { data: 'action', orderable: false, searchable: false, className: 'text-right', render: (d, t, r) => {
                        let h = '';
                        if (r.show_url) h += `<a href="${F.esc(r.show_url)}" class="act-btn" aria-label="Lihat klarifikasi ${F.esc(r.employee_name)}" title="Lihat"><i class="fas fa-eye" aria-hidden="true"></i></a>`;
                        if (r.edit_url) h += `<a href="${F.esc(r.edit_url)}" class="pr-btn pr-btn-sm" title="Klarifikasi">${r.is_updated ? 'Ubah' : 'Klarifikasi'}</a>`;
                        return h ? `<div class="act-group">${h}</div>` : '<span class="pr-muted">-</span>';
                    } },
                ],
                drawCallback: function(settings) {
                    const json = settings.json; if (!json) return;
                    const n = v => v === undefined ? '–' : Number(v).toLocaleString('id-ID');
                    $('#fp-count').text(`${n(json.recordsFiltered ?? json.recordsTotal)} hari scan`);
                    if (json.stats) {
                        const s = json.stats;
                        $('#stat-total').text(n(s.total)); $('#stat-ontime').text(n(s.on_time)); $('#stat-late').text(n(s.late));
                        $('#stat-updated').text(n(s.updated)); $('#stat-missing').text(n(s.missing)); $('#stat-pending').text(n(s.pending));
                        $('#pending-box').toggleClass('is-warn', s.pending > 0); $('#btn-pending').prop('hidden', !(s.pending > 0));
                        $('#missing-box').toggleClass('is-warn', s.missing > 0);
                    }
                    toggleHeaderButtons((json.recordsTotal ?? 0) > 0);
                    $('#check-all').prop('checked', false); refreshBulk();
                },
            });

            let st; $('#manualSearch').on('input', function() { clearTimeout(st); const v = this.value; st = setTimeout(() => table.search(v).draw(), 400); });
            $('#filter_status').on('change', () => table.ajax.reload());
            $('#btn-pending').on('click', () => { $('#filter_status').val(@json($pendingStatus)); table.ajax.reload(); document.getElementById('fp-title').scrollIntoView({ behavior: 'smooth' }); });

            function refreshBulk() {
                const c = $('.fingerprint-checkbox:checked').length;
                $('#selected-count').text(`${c} dipilih`);
                $('#bulk-action-bar').toggleClass('show', c > 0);
            }
            $('#check-all').on('change', function() { $('.fingerprint-checkbox').prop('checked', this.checked); refreshBulk(); });
            $(document).on('change', '.fingerprint-checkbox', function() {
                const t = $('.fingerprint-checkbox').length, c = $('.fingerprint-checkbox:checked').length;
                $('#check-all').prop('checked', t > 0 && c === t); refreshBulk();
            });
            $('#bulk-clear').on('click', () => { $('.fingerprint-checkbox, #check-all').prop('checked', false); refreshBulk(); });

            $(document).on('click', '.btn-bulk-status', function() {
                const status = this.dataset.status, reject = status.startsWith('rejected');
                const ids = $('.fingerprint-checkbox:checked').map((i, el) => el.value).get();
                if (!ids.length) return;
                Swal.fire({
                    icon: reject ? 'warning' : 'question',
                    title: reject ? `Tolak ${ids.length} klarifikasi?` : `Setujui ${ids.length} klarifikasi?`,
                    text: reject ? 'Karyawan bisa memperbaiki dan mengirim ulang klarifikasinya.' : (canManage ? 'Jam hasil klarifikasi akan dipakai saat rekap absensi.' : 'Klarifikasi diteruskan ke HR.'),
                    input: reject ? 'textarea' : undefined, inputPlaceholder: 'Alasan penolakan, wajib diisi',
                    inputValidator: reject ? v => (!v || v.trim().length < 5) ? 'Tulis alasan minimal 5 karakter.' : undefined : undefined,
                    showCancelButton: true, confirmButtonText: reject ? 'Tolak' : 'Setujui', cancelButtonText: 'Batal',
                    confirmButtonColor: reject ? '#dc2626' : '#4f46e5',
                }).then(res => {
                    if (!res.isConfirmed) return;
                    $.post('{{ route('fingerprints.bulkStatus') }}', { ids, status, rejection_note: reject ? res.value : null })
                        .done(r => {
                            table.ajax.reload(null, false); if (window.logTable) window.logTable.ajax.reload(null, false);
                            Swal.fire({ icon: r.skipped?.length ? 'info' : 'success', title: r.message,
                                html: r.skipped?.length ? '<div style="text-align:left;font-size:13px">' + r.skipped.map(F.esc).join('<br>') + '</div>' : undefined });
                        })
                        .fail(x => Swal.fire({ icon: 'error', title: 'Gagal', text: x.responseJSON?.message ?? 'Terjadi kesalahan.' }));
                });
            });

            $('.js-export').on('click', function() {
                const p = new URLSearchParams({ start_date: $('#startDate').val(), end_date: $('#endDate').val(), store_name: $('#store_name').val() || '', status: $('#filter_status').val() || '', export: this.dataset.type });
                window.location.href = `{{ route('fingerprints.exportfingerprints') }}?${p}`;
            });

            var tableManual = null;
            @can('ManageFingerspot')
                tableManual = $('#manual-added-table').DataTable({
                    processing: true, serverSide: true, autoWidth: false,
                    dom: 'rt<"pr-dt-bottom"<"pr-dt-left"li>p>', pageLength: 10,
                    order: [[2, 'desc']],
                    language: F.lang('Belum ada recap manual di periode ini.'),
                    ajax: { url: '{{ route('fingerprints.manual-added') }}', type: 'POST', data: d => { d.start_date = $('#startDate').val(); d.end_date = $('#endDate').val(); d.store_name = $('#store_name').val(); } },
                    columns: [
                        { data: 'employee_name', render: (d, t, r) => t !== 'display' ? d : `<span class="pr-cell-name">${F.esc(d)}</span><span class="pr-cell-sub">${F.esc([r.employee_pengenal, 'PIN ' + r.pin].join(', '))}</span>` },
                        { data: 'name', render: (d, t, r) => `<span class="pr-cell-name">${F.esc(d)}</span><span class="pr-cell-sub">${F.esc(r.position_name)}</span>` },
                        { data: 'scan_date', render: (d, t, r) => t !== 'display' ? d : `<span class="pr-cell-name">${F.esc(F.fmtDate(d))}</span>${r.roster_name && r.roster_name !== '-' ? `<span class="pr-cell-sub">${F.esc(r.roster_name)} ${F.esc(r.roster_time || '')}</span>` : ''}` },
                        { data: 'combine_1', render: (d, t, r) => t !== 'display' ? (d || '') : (r.in_1 ? `<span class="fp-time">${F.esc(r.in_1)}</span>` : '<span class="pr-muted">-</span>') },
                        { data: 'combine_2', render: (d, t, r) => t !== 'display' ? (d || '') : (r.in_2 ? `<span class="fp-time">${F.esc(r.in_2)}</span>` : '<span class="pr-muted">-</span>') },
                        { data: 'duration', render: (d, t) => t !== 'display' ? d : F.dur(d) },
                        { data: 'evidences', orderable: false, searchable: false, className: 'text-right', render: (d, t, r) => (d && d.length)
                            ? `<button type="button" class="pr-btn pr-btn-sm btn-show-evidence" data-idx="${F.esc(r.pin + '_' + r.scan_date)}">${d.length} bukti</button>` : '<span class="pr-muted">-</span>' },
                    ],
                    drawCallback: s => { if (s.json) $('#ma-count').text(`${Number(s.json.recordsFiltered ?? 0).toLocaleString('id-ID')} hari, ditambahkan HR lewat Add recap`); },
                });

                window.logTable = $('#log-table').DataTable({
                    processing: true, serverSide: true, autoWidth: false, searching: false,
                    dom: 'rt<"pr-dt-bottom"<"pr-dt-left"li>p>', pageLength: 10,
                    order: [[0, 'desc']],
                    language: F.lang('Belum ada perubahan.'),
                    ajax: '{{ route('fingerprints.log') }}',
                    columns: [
                        { data: 'created_at', name: 'created_at', render: d => `<span class="pr-cell-sub">${F.esc(d)}</span>` },
                        { data: 'employee_name', orderable: false, render: (d, t, r) => `<span class="pr-cell-name">${F.esc(d)}</span><span class="pr-cell-sub">PIN ${F.esc(r.pin)}, ${F.esc(r.scan_date)}</span>` },
                        { data: 'action_key', orderable: false, render: d => ({ created: '<span class="status-badge is-open">Klarifikasi baru</span>', updated: '<span class="status-badge is-pending">Diubah</span>',
                            bulk_status: '<span class="status-badge is-approved">Status massal</span>', status: '<span class="status-badge is-approved">Status</span>' })[d] ?? '<span class="pr-muted">-</span>' },
                        { data: 'changes', orderable: false },
                        { data: 'causer_name', orderable: false, render: d => F.esc(d) },
                    ],
                });
            @endcan

            $('#filterBtn').on('click', () => { toggleHeaderButtons(false); table.ajax.reload(); tableManual?.ajax.reload(); });
            $('#resetBtn').on('click', () => {
                $('#startDate').val(DEFAULTS.start); $('#endDate').val(DEFAULTS.end);
                $('#store_name').val('').trigger('change.select2'); $('#filter_status').val(''); $('#manualSearch').val(''); table.search('');
                toggleHeaderButtons(false); table.ajax.reload(); tableManual?.ajax.reload();
            });

            // Scan baru masuk dari mesin: muat ulang diam-diam tiap 2 menit (tidak saat sedang mencari atau memilih)
            setInterval(() => {
                if (!$('#manualSearch').val().trim() && !$('.fingerprint-checkbox:checked').length && document.visibilityState === 'visible') table.ajax.reload(null, false);
            }, 120000);

            @can('ManageFingerspot')
                $('#recapBtn').on('click', function() {
                    const s = $('#startDate').val(), e = $('#endDate').val(), store = $('#store_name').val();
                    if (!s || !e) return Swal.fire({ icon: 'warning', title: 'Pilih tanggal dulu' });
                    Swal.fire({
                        icon: 'question', title: 'Rekap absensi?',
                        html: `Scan <strong>${F.esc(s)}</strong> sampai <strong>${F.esc(e)}</strong>${store ? ', lokasi <strong>' + F.esc(store) + '</strong>' : ', semua lokasi'} akan direkap jadi hari kerja.<br><small>Pastikan roster sudah benar. Recap manual tidak ditimpa.</small>`,
                        showCancelButton: true, confirmButtonText: 'Rekap', cancelButtonText: 'Batal', confirmButtonColor: '#4f46e5',
                    }).then(r => {
                        if (!r.isConfirmed) return;
                        const $b = $('#recapBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Memproses');
                        $.post('{{ route('fingerprints.recap') }}', { start_date: s, end_date: e, store_name: store })
                            .done(res => Swal.fire({ icon: 'success', title: 'Rekap selesai', text: res.message }).then(() => { table.ajax.reload(); tableManual?.ajax.reload(); }))
                            .fail(x => Swal.fire({ icon: 'error', title: 'Rekap gagal', text: x.responseJSON?.message ?? 'Terjadi kesalahan saat memproses rekap.' }))
                            .always(() => $b.prop('disabled', false).html('<i class="fas fa-rotate-right" aria-hidden="true"></i> Rekap absensi'));
                    });
                });

                $(document).on('click', '.btn-show-evidence', function() {
                    const row = tableManual.rows().data().toArray().find(x => (x.pin + '_' + x.scan_date) === String(this.dataset.idx));
                    const evidences = row?.evidences || [];
                    $('#evidenceList').empty();
                    $('#evidenceReason').text(evidences[0]?.reason && evidences[0].reason !== '-' ? 'Alasan: ' + evidences[0].reason : 'Tanpa alasan tertulis.');
                    evidences.forEach(ev => {
                        const $fig = $('<figure class="fp-thumb"><figcaption></figcaption></figure>');
                        $fig.find('figcaption').text(ev.file_name);
                        $('#evidenceList').append($fig);
                        $.get('{{ route('manual-recap.signedUrl') }}', { path: ev.file_path }).done(res => {
                            const isImg = (ev.mime_type || '').startsWith('image/');
                            const $a = $('<a target="_blank" rel="noopener" class="fp-open"></a>').attr('href', res.url);
                            if (isImg) $a.append($('<img alt="">').attr('src', res.url).attr('alt', ev.file_name));
                            else $a.addClass('pr-btn pr-btn-sm').css('margin', '8px').text('Buka file');
                            $fig.prepend($a);
                        }).fail(() => $fig.find('figcaption').append(' (gagal dimuat)'));
                    });
                    $('#evidenceModal').modal('show');
                });
                $('#evidenceDropzone').on('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); $('#evidenceFiles').trigger('click'); } });

                /* ── Add recap manual (logika tidak diubah) ── */
                let evidenceFiles = [];

            // Load semua karyawan
            function loadEmployeeList() {
                fetch('{{ route('fingerprints.employee-list') }}')
                    .then(r => r.json())
                    .then(data => {
                        const select = $('#manualEmpIds');
                        select.empty();
                        (data.data || []).forEach(emp => {
                            const text = `${emp.name} — ${emp.store} - ${emp.status} - ${emp.pin}`;
                            select.append(new Option(text, emp.id));
                        });
                    })
                    .catch(err => console.error('Failed load employees:', err));
            }

            // Load shift list
            function loadShiftList() {
                fetch('{{ route('manual-recap.shift-list') }}')
                    .then(r => r.json())
                    .then(data => {
                        const select = $('#manualShiftId');
                        select.empty();
                        select.append(new Option('Use shifts from employee roster', ''));
                        (data.data || []).forEach(shift => {
                            const text = `${shift.store_name} - ${shift.name} - (${shift.time})`;
                            select.append(new Option(text, shift.id));
                        });
                    })
                    .catch(err => console.error('failed load shifts:', err));
            }

            // Init dropzone (dengan guard agar listener tidak menumpuk)
            let evidenceDropzoneInitialized = false;

            function initEvidenceDropzone() {
                if (evidenceDropzoneInitialized) return;

                const dropzone = document.getElementById('evidenceDropzone');
                const fileInput = document.getElementById('evidenceFiles');

                dropzone.addEventListener('click', (e) => {
                    // Cegah trigger ganda kalau klik tepat di input file
                    if (e.target === fileInput) return;
                    fileInput.click();
                });
                fileInput.addEventListener('change', e => handleFiles(e.target.files));
                // Foto dikompres dulu di handleFiles (HRXImage), termasuk yang di-drop

                ['dragenter', 'dragover'].forEach(ev =>
                    dropzone.addEventListener(ev, e => {
                        e.preventDefault();
                        dropzone.classList.add('dragover');
                    })
                );
                ['dragleave', 'drop'].forEach(ev =>
                    dropzone.addEventListener(ev, e => {
                        e.preventDefault();
                        dropzone.classList.remove('dragover');
                    })
                );
                dropzone.addEventListener('drop', e => handleFiles(e.dataTransfer.files));

                evidenceDropzoneInitialized = true;
            }

            async function handleFiles(fileList) {
                // Foto HP dikompres & dibersihkan dulu (public/js/image-upload.js); batas server 512 KB per file
                fileList = window.HRXImage ? await window.HRXImage.compressFiles(fileList, { maxKb: 512 }) : Array.from(fileList);

                const allowedTypes = [
                    'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ];
                const maxSize = 512 * 1024; // sama dengan ManualRecapController (evidence_files.* max:512)

                Array.from(fileList).forEach(file => {
                    if (!allowedTypes.includes(file.type)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'File type not allowed',
                            html: `<strong>${FP.esc(file.name)}</strong><br><small>Only JPG, PNG, GIF, WEBP, PDF, DOC, DOCX, XLS, XLSX.</small>`,
                            confirmButtonColor: '#dc2626'
                        });
                        return;
                    }
                    if (file.size > maxSize) {
                        Swal.fire({
                            icon: 'error',
                            title: 'File is too large',
                            html: `<strong>${FP.esc(file.name)}</strong><br><small>Maximum file size 512 KB.</small>`,
                            confirmButtonColor: '#dc2626'
                        });
                        return;
                    }
                    if (evidenceFiles.some(f => f.name === file.name && f.size === file.size)) return;
                    evidenceFiles.push(file);
                });

                renderFileList();
                document.getElementById('evidenceFiles').value = '';
            }

            function renderFileList() {
                const list = document.getElementById('evidenceFileList');
                list.innerHTML = '';

                evidenceFiles.forEach((file, idx) => {
                    const iconClass = getFileIconClass(file.type);
                    const icon = getFileIcon(file.type);
                    const sizeStr = formatFileSize(file.size);

                    const item = document.createElement('div');
                    item.className = 'evidence-file-item';
                    item.innerHTML = `
                    <div class="evidence-file-icon ${iconClass}"><i class="${icon}"></i></div>
                    <div class="evidence-file-info">
                        <div class="evidence-file-name">${FP.esc(file.name)}</div>
                        <div class="evidence-file-size">${sizeStr}</div>
                    </div>
                    <button type="button" class="evidence-file-remove" data-idx="${idx}" title="Hapus">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                    list.appendChild(item);
                });
            }

            $(document).on('click', '.evidence-file-remove', function() {
                const idx = parseInt($(this).data('idx'));
                evidenceFiles.splice(idx, 1);
                renderFileList();
            });

            function getFileIconClass(mime) {
                if (mime.startsWith('image/')) return 'img';
                if (mime === 'application/pdf') return 'pdf';
                if (mime.includes('word')) return 'doc';
                if (mime.includes('sheet') || mime.includes('excel')) return 'xls';
                return 'other';
            }

            function getFileIcon(mime) {
                if (mime.startsWith('image/')) return 'fas fa-image';
                if (mime === 'application/pdf') return 'fas fa-file-pdf';
                if (mime.includes('word')) return 'fas fa-file-word';
                if (mime.includes('sheet') || mime.includes('excel')) return 'fas fa-file-excel';
                return 'fas fa-file';
            }

            function formatFileSize(bytes) {
                const units = ['B', 'KB', 'MB', 'GB'];
                let i = 0;
                while (bytes >= 1024 && i < units.length - 1) {
                    bytes /= 1024;
                    i++;
                }
                return bytes.toFixed(2) + ' ' + units[i];
            }

            // Init Select2 di modal
            let selectManualInitialized = false;

            function initManualSelect2() {
                if (selectManualInitialized) return;
                $('#manualEmpIds').select2({
                    dropdownParent: $('#addRecapFormModal'),
                    placeholder: 'Choose Employees...',
                    allowClear: true
                });
                $('#manualShiftId').select2({
                    dropdownParent: $('#addRecapFormModal'),
                    placeholder: 'Use shift from roster...',
                    allowClear: true
                });
                selectManualInitialized = true;
            }

            // Step 1: Klik "+Add Recap"
            $('#addRecapBtn').on('click', function() {
                Swal.fire({
                    icon: 'warning',
                    iconColor: '#f59e0b',
                    title: 'Warning',
                    html: `
                    <div style="text-align:left;padding:10px 0">
                        <p style="color:#1f2937;font-size:15px;line-height:1.6;margin-bottom:10px">
                            <strong>Data ini akan dikirimkan ke email dan grup WhatsApp HR dan IT sebagai pertanggungjawaban.</strong>
                        </p>
                        <p style="color:#6b7280;font-size:13px;margin:0">
                            Sebelum masukkan data, pastikan employee yang akan dimasukkan absensi sudah mempunyai roster, Pastikan data & bukti yang Anda masukan sudah benar.
                        </p>
                    </div>
                `,
                    showCancelButton: true,
                    confirmButtonColor: '#f59e0b',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '<i class="fas fa-check"></i> Okay, Understood',
                    cancelButtonText: 'Cancel',
                    focusCancel: true
                }).then(result => {
                    if (result.isConfirmed) openAddRecapForm();
                });
            });

            // Step 2: Buka form
            function openAddRecapForm() {
                loadEmployeeList();
                loadShiftList();
                initManualSelect2();
                initEvidenceDropzone();

                // Reset form
                $('#manualEmpIds').val(null).trigger('change');
                $('#manualShiftId').val('').trigger('change');
                document.getElementById('manualScanDate').value = '';
                document.getElementById('manualEndDate').value = '';
                document.getElementById('manualReason').value = '';
                evidenceFiles = [];
                renderFileList();

                $('#addRecapFormModal').modal('show');
            }

            // Step 3: Submit
            window.submitManualRecap = function() {
                const empIds = $('#manualEmpIds').val() || [];
                const scanDate = document.getElementById('manualScanDate').value;
                const endDate = document.getElementById('manualEndDate').value;
                const shiftId = $('#manualShiftId').val();
                const reason = document.getElementById('manualReason').value.trim();

                // Validasi
                if (empIds.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'Select at least 1 employee.'
                    });
                    return;
                }
                if (!scanDate) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'Scan Date required.'
                    });
                    return;
                }
                if (!endDate) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'End Date required.'
                    });
                    return;
                }
                if (endDate < scanDate) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'End Date cannot be before Scan Date.'
                    });
                    return;
                }
                if (evidenceFiles.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'Upload at least 1 proof file.'
                    });
                    return;
                }
                // if (reason.length === 0) {
                //     Swal.fire({
                //         icon: 'warning',
                //         title: 'Warning',
                //         text: 'Reason required.'
                //     });
                //     return;
                // }
                // if (reason.length < 10) {
                //     Swal.fire({
                //         icon: 'warning',
                //         title: 'Warning',
                //         text: 'Reason minimum 10 characters.'
                //     });
                //     return;
                // }
                if (reason.length > 1000) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'Maximum reason 1000 characters.'
                    });
                    return;
                }

                const diffDays = Math.round((new Date(endDate) - new Date(scanDate)) / (1000 * 60 * 60 * 24)) +
                    1;

                Swal.fire({
                    title: 'Confirmation',
                    html: `Will add manual recap for <strong>${empIds.length} Employess</strong><br>
                       Period: <strong>${scanDate}</strong> to <strong>${endDate}</strong> (<strong>${diffDays} day</strong>)<br>
                       with <strong>${evidenceFiles.length} file </strong>.<br>
                       <small style="color:#64748b">Continue?</small>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#1d4ed8',
                    confirmButtonText: 'Yes, submit',
                    cancelButtonText: 'Cancel'
                }).then(result => {
                    if (!result.isConfirmed) return;

                    const formData = new FormData();
                    empIds.forEach(id => formData.append('employee_ids[]', id));
                    formData.append('scan_date', scanDate);
                    formData.append('end_date', endDate);
                    if (shiftId) formData.append('shift_id', shiftId);
                    formData.append('reason', reason);
                    evidenceFiles.forEach(file => formData.append('evidence_files[]', file));

                    const btn = document.getElementById('submitManualBtn');
                    btn.disabled = true;


                    // Pesan loading dinamis berdasarkan estimasi batch //
                    const totalEntries = empIds.length * diffDays;
                    let loadingMsg = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
                    if (totalEntries > 1000) {
                        loadingMsg = '<i class="fas fa-spinner fa-spin"></i> Memproses ' + totalEntries
                            .toLocaleString('id-ID') + ' entri, mohon tunggu...';
                    }
                    btn.innerHTML = loadingMsg;

                    // AbortController untuk timeout 4 menit // 
                    const controller = new AbortController();
                    const timeoutId = setTimeout(() => controller.abort(), 240000); // 4 menit

                    fetch('{{ route('manual-recap.store') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: formData,
                            signal: controller.signal,
                        })
                        .then(async (response) => {
                            const data = await response.json().catch(() => ({}));
                            if (!response.ok) {
                                const error = new Error(data.message || 'Failed');
                                error.status = response.status;
                                error.errors = data.errors || null;
                                error.data = data;
                                throw error;
                            }
                            return data;
                        })
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: data.message,
                                    confirmButtonColor: '#1d4ed8'
                                }).then(() => {
                                    $('#addRecapFormModal').modal('hide');
                                    evidenceFiles = [];
                                    renderFileList();
                                    // Reload KEDUA tabel (data baru muncul di Manual Added)
                                    table.ajax.reload();
                                    tableManual.ajax.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Failed',
                                    text: data.message || 'Failed to add recap.'
                                });
                            }
                        })
                        .catch(err => {
                            // Error 422: Validation //
                            if (err.status === 422 && err.errors) {
                                const allMessages = Object.values(err.errors)
                                    .flat()
                                    .map(msg => `• ${msg}`)
                                    .join('<br>');
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Validation failed',
                                    html: `<div style="text-align:left;font-size:14px;line-height:1.6">${allMessages}</div>`,
                                    confirmButtonColor: '#f59e0b'
                                });
                            }
                            // Error 422: Batch terlalu besar (Custom dari Backend) // 
                            else if (err.status === 422 && err.message) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Batch Terlalu Besar',
                                    text: err.message,
                                    confirmButtonColor: '#f59e0b'
                                });
                            }
                            // Error 429: Idempotency lock //
                            else if (err.status === 429) {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Submit Berjalan',
                                    text: err.message ||
                                        'Submit sebelumnya masih diproses. Silakan tunggu sebentar dan refresh halaman jika perlu.',
                                    confirmButtonColor: '#1d4ed8'
                                });
                            }
                            // Timeout (AbortController) // 
                            else if (err.name === 'AbortError') {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Proses Lama',
                                    html: 'Proses memakan waktu lebih dari 4 menit. <br>Silakan <strong>refresh halaman</strong> dan cek apakah data sudah masuk. <br>Jika belum, kurangi jumlah karyawan/tanggal dan coba lagi.',
                                    confirmButtonColor: '#f59e0b'
                                });
                            }
                            // Generic Error // 
                            else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: err.message || 'Failed, Try again later.',
                                    confirmButtonColor: '#dc2626'
                                });
                            }

                        })
                        .finally(() => {
                            clearTimeout(timeoutId);
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-paper-plane" aria-hidden="true"></i> Simpan dan rekap';
                        });
                });
            };
            @endcan

            @if (session('success'))
                Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('success')), timer: 3000, timerProgressBar: true });
            @endif
            @if (session('error'))
                Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')) });
            @endif
        });
    </script>
@endpush
