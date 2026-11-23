@extends('layouts.app')
@section('title', 'Master shift')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand · redesign: full
         * paper: oklch(98.4% 0.004 265) · accent: oklch(61% 0.17 276) · display: Nunito · body: Nunito
         * nav: kept (Stisla shell) · signature: each shift drawn on a 24-hour bar, with roster usage beside it so a delete that would wipe history is visibly blocked · states: HR manage, SPV/view-only, empty, overnight shift, location without shift
         * axes: light/rounded-sans/chromatic-indigo · posture: app · contrast: pass · v2.0.0 */
        /* Hallmark · pre-emit critique: P4 H4 E4 S4 R4 V4 · weakest: E, time ranges hard to compare as text; answered by the 24-hour bar */
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

        /* ── Ringkasan ── */
        .tl-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 170px), 1fr)); margin: 0; }
        .tl-stats > div { padding: var(--pr-space-md) var(--pr-space-lg); border-right: 1px solid var(--pr-rule); border-bottom: 1px solid var(--pr-rule); }
        .tl-stats > div.is-warn { background: var(--pr-warn-soft); }
        .tl-stats dt { font-size: var(--pr-text-sm); font-weight: 600; color: var(--pr-ink-2); }
        .tl-stats dd { margin: 2px 0 0; font-size: var(--pr-text-xl); font-weight: 800; font-variant-numeric: tabular-nums; color: var(--pr-ink); }
        .tl-stats dd small { font-size: var(--pr-text-sm); font-weight: 600; color: var(--pr-muted); }
        .tl-stats > div > span { display: block; font-size: var(--pr-text-xs); line-height: 1.45; color: var(--pr-ink-2); }
        .sh-link { padding: 0; border: 0; background: none; font: inherit; font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-accent-strong); text-decoration: underline; text-underline-offset: 2px; cursor: pointer; }
        .sh-link:hover { color: var(--pr-accent-deep); }
        .sh-link:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 2px; }
        .sh-missing { margin: 0; padding: var(--pr-space-sm) var(--pr-space-lg); border-top: 1px solid var(--pr-rule); background: var(--pr-warn-soft); font-size: var(--pr-text-sm); line-height: 1.6; color: var(--pr-ink); }

        /* ── Shift: nama, kode, rentang jam di skala 24 jam ── */
        .sh-code { display: inline-grid; place-items: center; min-width: 2.4em; margin-right: var(--pr-space-xs, 6px); padding: 1px 6px; border-radius: 6px; background: var(--pr-accent-soft); font-size: var(--pr-text-xs); font-weight: 800; color: var(--pr-accent-deep); font-variant-numeric: tabular-nums; vertical-align: 1px; }
        .sh-time { font-size: var(--pr-text-lg); font-weight: 800; font-variant-numeric: tabular-nums; color: var(--pr-ink); white-space: nowrap; }
        .sh-day { position: relative; width: min(100%, 240px); height: 10px; margin-top: 6px; border-radius: 999px; background: var(--pr-paper-2); overflow: hidden; }
        .sh-day > i { position: absolute; z-index: 1; top: 0; bottom: 0; border-radius: 999px; background: var(--pr-accent); }
        .sh-day::after { content: ''; position: absolute; top: 0; bottom: 0; left: 50%; width: 1px; background: var(--pr-rule); }
        .sh-scale { display: flex; justify-content: space-between; width: min(100%, 240px); font-size: 10px; color: var(--pr-muted); font-variant-numeric: tabular-nums; }
        .sh-night { display: inline-block; margin-left: 6px; padding: 0 7px; border-radius: var(--pr-pill); background: var(--pr-warn-soft); font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-warn); vertical-align: 2px; }
        .sh-use { font-weight: 700; font-variant-numeric: tabular-nums; color: var(--pr-ink); }
        .sh-use.is-zero { font-weight: 600; color: var(--pr-muted); }
        .sh-actions { display: inline-flex; gap: 6px; justify-content: flex-end; }
        #shifts-table td { vertical-align: middle; }

        /* ── Modal ── */
        .sh-preview { display: grid; gap: 6px; margin-top: var(--pr-space-sm); padding: var(--pr-space-sm) var(--pr-space-md); border: 1px solid var(--pr-rule); border-radius: 10px; background: var(--pr-paper); font-size: var(--pr-text-sm); color: var(--pr-ink-2); }
        .sh-preview strong { color: var(--pr-ink); font-variant-numeric: tabular-nums; }
        .sh-preview .sh-day { width: 100%; }
        .sh-preview .sh-scale { width: 100%; }
        .sh-warn { margin: var(--pr-space-sm) 0 0; padding: var(--pr-space-sm) var(--pr-space-md); border-radius: 10px; background: var(--pr-warn-soft); font-size: var(--pr-text-sm); line-height: 1.5; color: var(--pr-ink); }
        .sh-warn[hidden] { display: none; }
        @media (prefers-reduced-motion: reduce) { .sh-link { transition: none; } }
    </style>
@endpush

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <h1 class="pr-title">Master shift</h1>
                    <p class="pr-lede">Jam kerja tiap lokasi. Roster memakai shift ini, dan dua huruf pertama namanya menjadi kode di template roster.</p>
                </div>
                @if ($canManage)
                    <div class="pr-actions">
                        <button type="button" class="pr-btn pr-btn-primary" id="btnAdd"><i class="fas fa-plus" aria-hidden="true"></i> Tambah shift</button>
                    </div>
                @endif
            </header>

            <section class="pr-panel" aria-label="Ringkasan">
                <dl class="tl-stats">
                    <div><dt>Shift</dt><dd id="st-total">–</dd><span id="st-total-sub">di semua lokasi</span></div>
                    <div id="cover-box"><dt>Lokasi punya shift</dt><dd id="st-cover">–</dd>
                        <span id="st-cover-sub">dari – lokasi</span>
                        <button type="button" class="sh-link" id="btnMissing" hidden aria-expanded="false" aria-controls="sh-missing">Lihat yang belum</button></div>
                    <div><dt>Dipakai di roster</dt><dd id="st-used">–</dd><span>shift yang sudah dijadwalkan</span></div>
                    <div><dt>Lintas hari</dt><dd id="st-night">–</dd><span>jam pulang di hari berikutnya</span></div>
                    <div><dt>Split shift</dt><dd id="st-split">–</dd><span>dua blok kerja sehari</span></div>
                </dl>
                <p class="sh-missing" id="sh-missing" hidden></p>
            </section>

            <section class="pr-panel" aria-labelledby="sh-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="sh-title">Daftar shift</h2>
                        <span class="pr-panel-sub" id="sh-count">Memuat</span>
                    </div>
                    <div class="pr-search">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <label for="sh-search" class="sr-only">Cari lokasi atau nama shift</label>
                        <input type="search" id="sh-search" placeholder="Cari lokasi atau shift">
                    </div>
                </div>
                <div class="emp-filters">
                    <div class="pr-field"><label for="filterStore">Lokasi</label>
                        <select id="filterStore" class="emp-select js-s2"><option value="">Semua lokasi</option>
                            @foreach ($stores as $store)<option value="{{ $store->id }}">{{ $store->name }}</option>@endforeach
                        </select></div>
                    <div class="emp-filter-actions">
                        <button type="button" class="pr-btn pr-btn-sm" id="btnReset">Reset</button>
                    </div>
                </div>
                <div class="pr-table-wrap">
                    <table class="table" id="shifts-table">
                        <thead>
                            <tr>
                                <th>Lokasi</th>
                                <th>Shift</th>
                                <th>Jam kerja</th>
                                <th>Dipakai di roster</th>
                                <th class="text-right"><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </section>
        </section>
    </div>

    @if ($canManage)
        <div class="modal fade" id="modalShift" tabindex="-1" role="dialog" aria-labelledby="modalTitle">
            <div class="modal-dialog" role="document">
                <form class="modal-content" id="formShift" novalidate>
                    <div class="modal-header">
                        <h2 class="modal-title" id="modalTitle">Tambah shift</h2>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="shiftId">
                        <div class="emp-fields" style="padding:0">
                            <div class="emp-field is-wide">
                                <label for="shiftStore">Lokasi <span class="emp-req" aria-hidden="true">*</span></label>
                                <select id="shiftStore" class="emp-select" required style="width:100%">
                                    <option value="">Pilih lokasi</option>
                                    @foreach ($stores as $store)<option value="{{ $store->id }}">{{ $store->name }}</option>@endforeach
                                </select>
                            </div>
                            <div class="emp-field is-wide">
                                <label for="shiftName">Nama shift <span class="emp-req" aria-hidden="true">*</span></label>
                                <input type="text" id="shiftName" class="emp-input" maxlength="50" required placeholder="Mis. Pagi, Siang, Malam" autocomplete="off">
                                <span class="emp-help" id="codeHelp">Kode di template roster: dua huruf pertama nama.</span>
                            </div>
                            <div class="emp-field is-wide">
                                <label class="emp-check" for="shiftSplit">
                                    <input type="checkbox" id="shiftSplit">
                                    <span><strong>Split shift</strong><small>Dua blok kerja di hari yang sama dengan jeda di tengah, mis. 07:00-11:00 lalu 17:00-21:00. Karyawan absen masuk dan pulang di tiap blok.</small></span>
                                </label>
                            </div>
                            <div class="emp-field">
                                <label for="shiftStart"><span class="js-split-label" data-normal="Jam masuk" data-split="Masuk blok 1">Jam masuk</span> <span class="emp-req" aria-hidden="true">*</span></label>
                                <input type="time" id="shiftStart" class="emp-input" required>
                            </div>
                            <div class="emp-field js-split-field" hidden>
                                <label for="shiftBreakStart">Pulang blok 1 <span class="emp-req" aria-hidden="true">*</span></label>
                                <input type="time" id="shiftBreakStart" class="emp-input">
                            </div>
                            <div class="emp-field js-split-field" hidden>
                                <label for="shiftBreakEnd">Masuk blok 2 <span class="emp-req" aria-hidden="true">*</span></label>
                                <input type="time" id="shiftBreakEnd" class="emp-input">
                            </div>
                            <div class="emp-field">
                                <label for="shiftEnd"><span class="js-split-label" data-normal="Jam pulang" data-split="Pulang blok 2">Jam pulang</span> <span class="emp-req" aria-hidden="true">*</span></label>
                                <input type="time" id="shiftEnd" class="emp-input" required>
                            </div>
                        </div>
                        <div class="sh-preview" id="preview" aria-live="polite">
                            <div id="previewText">Isi jam masuk dan pulang.</div>
                            <div class="sh-day" aria-hidden="true"><i id="previewBar1"></i><i id="previewBar2"></i></div>
                            <div class="sh-scale" aria-hidden="true"><span>00</span><span>06</span><span>12</span><span>18</span><span>24</span></div>
                        </div>
                        <p class="sh-warn" id="usageWarn" hidden></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="pr-btn" data-dismiss="modal">Batal</button>
                        <button type="submit" class="pr-btn pr-btn-primary" id="btnSave">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('partials.datatables-error', ['permission' => 'ManageShifts, ManageShiftsSPVManager atau ViewShifts'])
    <script>
        $(function() {
            const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const n = v => Number(v || 0).toLocaleString('id-ID');
            const mins = t => { const m = /^(\d{2}):(\d{2})/.exec(t || ''); return m ? (+m[1]) * 60 + (+m[2]) : null; };
            const fmtDate = s => s ? new Date(s + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '';

            /** Durasi dan potongan bar di skala 24 jam; shift lintas hari dipecah dua. */
            function span(start, end) {
                const s = mins(start), e = mins(end);
                if (s === null || e === null || s === e) return null;
                const night = e < s, dur = night ? e + 1440 - s : e - s;
                const pct = v => (v / 1440 * 100).toFixed(2) + '%';
                const bars = night ? [[pct(s), pct(1440 - s)], ['0%', pct(e)]] : [[pct(s), pct(dur)]];
                const h = Math.floor(dur / 60), m = dur % 60;
                return { night, bars, label: `${h} jam${m ? ' ' + m + ' menit' : ''}` };
            }
            /** Split shift: dua blok di hari yang sama, durasi tanpa jeda. */
            function splitSpan(start, bStart, bEnd, end) {
                const a = mins(start), b = mins(bStart), c = mins(bEnd), d = mins(end);
                if ([a, b, c, d].includes(null) || !(a < b && b < c && c < d)) return null;
                const pct = v => (v / 1440 * 100).toFixed(2) + '%';
                const dur = (b - a) + (d - c), gap = c - b;
                const fmt = v => `${Math.floor(v / 60)} jam${v % 60 ? ' ' + (v % 60) + ' menit' : ''}`;
                return { night: false, split: true, bars: [[pct(a), pct(b - a)], [pct(c), pct(d - c)]], label: `${fmt(dur)} kerja, jeda ${fmt(gap)}` };
            }
            const barHtml = sp => `<div class="sh-day" aria-hidden="true">${sp.bars.map(([l, w]) => `<i style="left:${l};width:${w}"></i>`).join('')}</div>`;

            $('.js-s2').each(function() { $(this).select2({ width: '100%' }); });
            let rows = {};

            const table = $('#shifts-table').DataTable({
                processing: true, serverSide: true, autoWidth: false,
                dom: 'rt<"pr-dt-bottom"<"pr-dt-left"li>p>',
                pageLength: 25, lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [[0, 'asc']],
                language: {
                    processing: 'Memuat', lengthMenu: 'Tampilkan _MENU_', info: '_START_ sampai _END_ dari _TOTAL_', infoEmpty: 'Tidak ada data',
                    infoFiltered: '', zeroRecords: 'Tidak ada shift yang cocok.', emptyTable: 'Belum ada shift.',
                    paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                },
                ajax: {
                    url: '{{ route('shifts.data') }}', type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: d => { d.store_id = $('#filterStore').val(); },
                },
                columns: [
                    { data: 'store_name', name: 'store_name', render: d => `<span class="pr-cell-name">${esc(d)}</span>` },
                    { data: 'shift_name', name: 'shifts_tables.shift_name', render: (d, t, r) => `<span class="sh-code" title="Kode di template roster">${esc(r.code)}</span><span class="pr-cell-name" style="display:inline">${esc(d)}</span>` },
                    { data: 'start_time', name: 'shifts_tables.start_time', searchable: false, render: (d, t, r) => {
                        if (r.is_split) {
                            const ss = splitSpan(r.start_time, r.break_start, r.break_end, r.end_time);
                            const txt = `${esc(r.start_time)}–${esc(r.break_start)} / ${esc(r.break_end)}–${esc(r.end_time)}`;
                            return `<span class="sh-time">${txt}</span><span class="sh-night">split</span>${ss ? `<span class="pr-cell-sub">${ss.label}</span>${barHtml(ss)}` : ''}`;
                        }
                        const sp = span(r.start_time, r.end_time);
                        if (!sp) return `<span class="sh-time">${esc(r.start_time)}–${esc(r.end_time)}</span>`;
                        return `<span class="sh-time">${esc(r.start_time)}–${esc(r.end_time)}</span>${sp.night ? '<span class="sh-night">lintas hari</span>' : ''}<span class="pr-cell-sub">${sp.label}</span>${barHtml(sp)}`;
                    } },
                    { data: 'roster_count', name: 'roster_count', searchable: false, render: (d, t, r) => d > 0
                        ? `<span class="sh-use">${n(d)} jadwal</span><span class="pr-cell-sub">terakhir ${esc(fmtDate(r.last_used))}</span>`
                        : '<span class="sh-use is-zero">Belum dipakai</span>' },
                    { data: 'id', orderable: false, searchable: false, className: 'text-right', render: (d, t, r) => {
                        if (!r.can_manage) return '';
                        return `<span class="sh-actions">
                            <button type="button" class="pr-btn pr-btn-sm btn-edit" data-id="${esc(d)}" aria-label="Ubah shift ${esc(r.shift_name)} di ${esc(r.store_name)}">Ubah</button>
                            <button type="button" class="pr-btn pr-btn-sm pr-btn-danger btn-delete" data-id="${esc(d)}" ${r.roster_count > 0 ? 'disabled title="Sudah dipakai di roster, tidak bisa dihapus"' : ''} aria-label="Hapus shift ${esc(r.shift_name)} di ${esc(r.store_name)}">Hapus</button>
                        </span>`;
                    } },
                ],
                drawCallback: function(settings) {
                    const j = settings.json; if (!j) return;
                    const s = j.stats || {};
                    $('#sh-count').text(`${n(j.recordsFiltered)} shift`);
                    $('#st-total').text(n(s.total)); $('#st-total-sub').text(`di ${n(s.stores_covered)} lokasi`);
                    $('#st-cover').html(`${n(s.stores_covered)} <small>/ ${n(s.stores)}</small>`);
                    const miss = s.stores_without || [];
                    $('#st-cover-sub').text(miss.length ? `${n(miss.length)} lokasi belum punya shift, roster-nya tidak bisa dibuat` : 'semua lokasi sudah punya shift');
                    $('#cover-box').toggleClass('is-warn', miss.length > 0);
                    $('#btnMissing').prop('hidden', !miss.length);
                    $('#sh-missing').text(miss.length ? 'Belum punya shift: ' + miss.join(', ') + '.' : '');
                    if (!miss.length) $('#sh-missing').prop('hidden', true);
                    $('#st-used').html(`${n(s.used)} <small>/ ${n(s.total)}</small>`);
                    $('#st-night').text(n(s.overnight));
                    $('#st-split').text(n(s.split));
                    rows = {}; (j.data || []).forEach(r => rows[r.id] = r);
                },
            });
            $('#btnMissing').on('click', function() {
                const open = $('#sh-missing').prop('hidden');
                $('#sh-missing').prop('hidden', !open); $(this).attr('aria-expanded', open).text(open ? 'Sembunyikan' : 'Lihat yang belum');
            });
            let ts; $('#sh-search').on('input', function() { clearTimeout(ts); const v = this.value; ts = setTimeout(() => table.search(v).draw(), 400); });
            $('#filterStore').on('change', () => table.ajax.reload());
            $('#btnReset').on('click', () => { $('#filterStore').val('').trigger('change.select2'); $('#sh-search').val(''); table.search('').ajax.reload(); });

            @if ($canManage)
                const $m = $('#modalShift');
                $('#shiftStore').select2({ width: '100%', dropdownParent: $m });

                function preview() {
                    const name = ($('#shiftName').val() || '').trim().toUpperCase();
                    $('#codeHelp').text(name.length >= 2 ? `Kode di template roster: ${name.slice(0, 2)}` : 'Kode di template roster: dua huruf pertama nama.');
                    const split = $('#shiftSplit').is(':checked');
                    $('.js-split-field').prop('hidden', !split);
                    $('.js-split-label').each(function() { $(this).text($(this).data(split ? 'split' : 'normal')); });
                    const sp = split
                        ? splitSpan($('#shiftStart').val(), $('#shiftBreakStart').val(), $('#shiftBreakEnd').val(), $('#shiftEnd').val())
                        : span($('#shiftStart').val(), $('#shiftEnd').val());
                    const bars = sp ? sp.bars : [];
                    ['#previewBar1', '#previewBar2'].forEach((id, i) => $(id).css(bars[i] ? { left: bars[i][0], width: bars[i][1], display: 'block' } : { display: 'none' }));
                    if (split) {
                        $('#previewText').html(sp
                            ? `Durasi <strong>${sp.label}</strong>.`
                            : 'Isi keempat jam berurutan di hari yang sama: masuk blok 1, pulang blok 1, masuk blok 2, pulang blok 2.');
                        return;
                    }
                    $('#previewText').html(sp
                        ? `Durasi <strong>${sp.label}</strong>${sp.night ? ', pulang <strong>keesokan harinya</strong>' : ''}.`
                        : ($('#shiftStart').val() && $('#shiftStart').val() === $('#shiftEnd').val() ? 'Jam pulang tidak boleh sama dengan jam masuk.' : 'Isi jam masuk dan pulang.'));
                }
                $('#shiftName, #shiftStart, #shiftEnd, #shiftBreakStart, #shiftBreakEnd, #shiftSplit').on('input change', preview);

                function openModal(r) {
                    $('#modalTitle').text(r ? `Ubah shift ${r.shift_name}` : 'Tambah shift');
                    $('#shiftId').val(r?.id ?? '');
                    $('#shiftStore').val(r?.store_id ?? ($('#filterStore').val() || '')).trigger('change');
                    $('#shiftName').val(r?.shift_name ?? ''); $('#shiftStart').val(r?.start_time ?? ''); $('#shiftEnd').val(r?.end_time ?? '');
                    $('#shiftSplit').prop('checked', !!r?.is_split);
                    $('#shiftBreakStart').val(r?.break_start ?? ''); $('#shiftBreakEnd').val(r?.break_end ?? '');
                    const used = r?.roster_count || 0;
                    $('#usageWarn').prop('hidden', !used).text(used ? `Dipakai di ${n(used)} jadwal roster. Lokasinya tidak bisa dipindah, dan jam baru berlaku untuk semua jadwal tersebut.` : '');
                    preview(); $m.modal('show');
                }
                $('#btnAdd').on('click', () => openModal(null));
                $(document).on('click', '.btn-edit', function() { const r = rows[$(this).data('id')]; if (r) openModal(r); });
                $m.on('shown.bs.modal', () => $('#shiftName').trigger('focus'));

                $('#formShift').on('submit', function(e) {
                    e.preventDefault();
                    const id = $('#shiftId').val(), miss = [];
                    if (!$('#shiftStore').val()) miss.push('lokasi');
                    if (($('#shiftName').val() || '').trim().length < 2) miss.push('nama shift (minimal 2 karakter)');
                    if (!$('#shiftStart').val()) miss.push('jam masuk');
                    if (!$('#shiftEnd').val()) miss.push('jam pulang');
                    const split = $('#shiftSplit').is(':checked');
                    if (split && !$('#shiftBreakStart').val()) miss.push('pulang blok 1');
                    if (split && !$('#shiftBreakEnd').val()) miss.push('masuk blok 2');
                    if (miss.length) return Swal.fire({ icon: 'error', title: 'Belum lengkap', text: 'Isi dulu: ' + miss.join(', ') + '.' });
                    const $b = $('#btnSave').prop('disabled', true).text('Menyimpan');
                    $.ajax({
                        url: id ? '{{ url('/shifts') }}/' + encodeURIComponent(id) : '{{ route('shifts.store') }}',
                        type: id ? 'PUT' : 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', Accept: 'application/json' },
                        data: { store_id: $('#shiftStore').val(), shift_name: $('#shiftName').val(), start_time: $('#shiftStart').val(), end_time: $('#shiftEnd').val(),
                            is_split: split ? 1 : 0, break_start: split ? $('#shiftBreakStart').val() : '', break_end: split ? $('#shiftBreakEnd').val() : '' },
                    })
                        .done(res => { $m.modal('hide'); table.ajax.reload(null, false); Swal.fire({ icon: 'success', title: 'Tersimpan', text: res.message, timer: 2200 }); })
                        .fail(x => Swal.fire({ icon: 'error', title: 'Tidak bisa disimpan', text: x.responseJSON?.errors ? Object.values(x.responseJSON.errors).flat().join('\n') : (x.responseJSON?.message ?? 'Terjadi kesalahan.') }))
                        .always(() => $b.prop('disabled', false).text('Simpan'));
                });

                $(document).on('click', '.btn-delete', function() {
                    const r = rows[$(this).data('id')]; if (!r) return;
                    Swal.fire({
                        icon: 'warning', title: `Hapus shift ${r.shift_name}?`, text: `${r.store_name}, ${r.is_split ? `${r.start_time}–${r.break_start} / ${r.break_end}–${r.end_time}` : `${r.start_time}–${r.end_time}`}. Shift ini belum dipakai di roster.`,
                        showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc2626', focusCancel: true,
                    }).then(res => {
                        if (!res.isConfirmed) return;
                        $.ajax({ url: '{{ url('/shifts') }}/' + encodeURIComponent(r.id), type: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', Accept: 'application/json' } })
                            .done(x => { table.ajax.reload(null, false); Swal.fire({ icon: 'success', title: 'Dihapus', text: x.message, timer: 1800 }); })
                            .fail(x => Swal.fire({ icon: 'error', title: 'Tidak bisa dihapus', text: x.responseJSON?.message ?? 'Terjadi kesalahan.' }));
                    });
                });
            @endif
        });
    </script>
@endpush
