@extends('layouts.app')
@section('title', 'Karyawan')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand · redesign: full
         * paper: oklch(98.4% 0.004 265) · accent: oklch(61% 0.17 276) · display: Nunito · body: Nunito
         * nav: kept (Stisla shell) · signature: who is on the payroll right now, filterable, with the org chart beneath · states: manage, SPV, view-only, empty filter, no chart yet
         * axes: light/rounded-sans/chromatic-indigo · posture: app · contrast: pass · v2.0.0 */
        /* Hallmark · pre-emit critique: P4 H4 E4 S4 R4 V4 · weakest: H, a wide table; answered by folding NIP and position under the name */
        /* Hallmark · waive 48: org chart SVG keeps its per-grading palette (data colours set in JS) */
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
    </style>
@endpush

@section('main')
    @php
        $canManage = auth()->user()->can('ManageEmployee');
        $canSpv = auth()->user()->can('ManageEmployeeSPVManager');
    @endphp
    <div class="main-content pr">
        <section class="section">

            <header class="pr-head">
                <div>
                    <h1 class="pr-title">Karyawan</h1>
                    <p class="pr-lede">
                        {{ $canManage ? 'Semua karyawan di semua perusahaan.' : ($canSpv ? 'Karyawan di lokasi dan departemen Anda, ditambah bawahan langsung.' : 'Rekan kerja di lokasi dan departemen utama Anda.') }}
                    </p>
                </div>
                @if ($canManage)
                    <div class="pr-actions">
                        <a href="{{ route('pages.Import') }}" class="pr-btn"><i class="fas fa-file-import" aria-hidden="true"></i> Import</a>
                        <a href="{{ route('pages.Employeeall') }}" class="pr-btn"><i class="fas fa-table" aria-hidden="true"></i> Data lengkap</a>
                        <a href="{{ route('Employee.create') }}" class="pr-btn pr-btn-primary"><i class="fas fa-plus" aria-hidden="true"></i> Tambah karyawan</a>
                    </div>
                @endif
            </header>

            @if ($canManage || $canSpv)
                <section class="pr-panel" aria-label="Ringkasan karyawan">
                    <dl class="emp-stats">
                        <div>
                            <dt>Ditampilkan</dt>
                            <dd id="stat-total">–</dd>
                        </div>
                        @if ($canManage)
                            <div>
                                <dt>Aktif</dt>
                                <dd>{{ number_format($countactives ?? 0, 0, ',', '.') }}</dd>
                                <button type="button" class="js-quick-status" data-status="Active">Tampilkan</button>
                            </div>
                        @endif
                        <div>
                            <dt>Pending</dt>
                            <dd>{{ number_format($countpendings ?? 0, 0, ',', '.') }}</dd>
                            @if ($canManage)<button type="button" class="js-quick-status" data-status="Pending">Tampilkan</button>@endif
                        </div>
                        @if ($canManage)
                            <div>
                                <dt>Resign</dt>
                                <dd>{{ number_format($countresigns ?? 0, 0, ',', '.') }}</dd>
                                <button type="button" class="js-quick-status" data-status="Resign">Tampilkan</button>
                            </div>
                        @endif
                    </dl>
                </section>
            @endif

            {{-- Daftar karyawan --}}
            <section class="pr-panel" aria-labelledby="emp-list-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="emp-list-title">Daftar karyawan</h2>
                        <span class="pr-panel-sub" id="emp-count">Memuat</span>
                    </div>
                    <div class="pr-search">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <label for="emp-search" class="sr-only">Cari nama, NIP, atau jabatan</label>
                        <input type="search" id="emp-search" placeholder="Cari nama, NIP, atau jabatan">
                    </div>
                </div>

                @if ($canManage)
                    <div class="emp-filters">
                        <div class="pr-field">
                            <label for="filter-status">Status</label>
                            <select id="filter-status" class="emp-select">
                                <option value="">Semua status</option>
                                @foreach ($statuses as $s)
                                    <option value="{{ $s }}">{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pr-field">
                            <label for="filter-company">Perusahaan</label>
                            <select id="filter-company" class="emp-select js-s2">
                                <option value="">Semua perusahaan</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company }}">{{ $company }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pr-field">
                            <label for="filter-department">Departemen</label>
                            <select id="filter-department" class="emp-select js-s2">
                                <option value="">Semua departemen</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->department_name }}">{{ $dept->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pr-field">
                            <label for="filter-store">Lokasi</label>
                            <select id="filter-store" class="emp-select js-s2">
                                <option value="">Semua lokasi</option>
                                @foreach ($stores as $store)
                                    <option value="{{ $store->name }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pr-field">
                            <label for="filter-emp-status">Tipe</label>
                            <select id="filter-emp-status" class="emp-select">
                                <option value="">Semua tipe</option>
                                @foreach ($employeestatuses as $es)
                                    <option value="{{ $es }}">{{ $es }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pr-field">
                            <label for="filter-grading">Grading</label>
                            <select id="filter-grading" class="emp-select js-s2">
                                <option value="">Semua grading</option>
                                @foreach ($gradings as $grading)
                                    <option value="{{ $grading }}">{{ $grading }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pr-field">
                            <label for="filter-group">Group</label>
                            <select id="filter-group" class="emp-select js-s2">
                                <option value="">Semua group</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group }}">{{ $group }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pr-field">
                            <label for="filter-los">Masa kerja</label>
                            <select id="filter-los" class="emp-select">
                                <option value="">Semua</option>
                                <option value="under3months">Kurang dari 3 bulan</option>
                                <option value="1year">1 tahun atau lebih</option>
                                <option value="3years">3 tahun atau lebih</option>
                                <option value="5years">5 tahun atau lebih</option>
                            </select>
                        </div>
                        <div class="emp-filter-actions">
                            <button type="button" id="btn-reset-filter" class="pr-btn pr-btn-sm" hidden>Hapus filter</button>
                            <a id="btn-export-excel" href="{{ route('Employee.export') }}" class="pr-btn pr-btn-sm"><i class="fas fa-file-excel" aria-hidden="true"></i> Excel</a>
                            <a id="btn-export-csv" href="{{ route('Employee.export') }}?type=csv" class="pr-btn pr-btn-sm"><i class="fas fa-file-csv" aria-hidden="true"></i> CSV</a>
                        </div>
                    </div>
                @elseif ($canSpv)
                    <div class="emp-filters">
                        <div class="pr-field">
                            <label for="filter-store">Lokasi</label>
                            <select id="filter-store" class="emp-select">
                                <option value="">Semua lokasi saya</option>
                                @foreach ($stores as $store)
                                    <option value="{{ $store->name }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="emp-filter-actions">
                            <button type="button" id="btn-reset-filter" class="pr-btn pr-btn-sm" hidden>Hapus filter</button>
                        </div>
                    </div>
                @endif

                <div class="pr-table-wrap">
                    <table id="employees-table" class="table">
                        <thead>
                            <tr>
                                <th>Karyawan</th>
                                <th>NIP</th>
                                <th>Jabatan</th>
                                <th>Perusahaan</th>
                                <th>Departemen</th>
                                <th>Lokasi</th>
                                @if ($canManage)
                                    <th>Grading</th>
                                    <th>Group</th>
                                    <th>Tipe</th>
                                    <th>Masa kerja</th>
                                @endif
                                <th>Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </section>

            @if ($canManage)
                {{-- Bagan organisasi --}}
                <section class="pr-panel" aria-labelledby="emp-chart-title">
                    <div class="pr-panel-head">
                        <div>
                            <h2 class="pr-panel-title" id="emp-chart-title">Bagan organisasi</h2>
                            <span class="pr-panel-sub">Struktur atasan dan bawahan per lokasi dan departemen. Klik kotak untuk detail.</span>
                        </div>
                    </div>
                    <div class="emp-chart-tools">
                        <div class="pr-field">
                            <label for="sel-store">Lokasi</label>
                            <select id="sel-store" class="emp-select js-s2">
                                <option value="all">Semua lokasi</option>
                                @foreach ($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pr-field">
                            <label for="sel-dept">Departemen</label>
                            <select id="sel-dept" class="emp-select js-s2">
                                <option value="all">Semua departemen</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" id="btn-load" class="pr-btn pr-btn-primary"><i class="fas fa-sitemap" aria-hidden="true"></i> Tampilkan bagan</button>
                        <button type="button" id="btn-toggle-lines" class="pr-btn">Sembunyikan garis</button>
                        <button type="button" id="btn-bagan-reset" class="pr-btn">Reset</button>
                        <div class="emp-zoom" role="group" aria-label="Zoom bagan">
                            <button type="button" id="btn-zoom-out" class="act-btn" title="Perkecil"><i class="fas fa-minus" aria-hidden="true"></i><span class="sr-only">Perkecil</span></button>
                            <span id="zoom-label">100%</span>
                            <button type="button" id="btn-zoom-in" class="act-btn" title="Perbesar"><i class="fas fa-plus" aria-hidden="true"></i><span class="sr-only">Perbesar</span></button>
                            <button type="button" id="btn-fit" class="pr-btn pr-btn-sm">Muat layar</button>
                            <button type="button" id="btn-reset-zoom" class="pr-btn pr-btn-sm">100%</button>
                        </div>
                    </div>
                    <div id="chart-area">
                        <div id="chart-placeholder">Pilih lokasi dan departemen, lalu klik Tampilkan bagan.</div>
                        <svg id="svg-chart" style="display:none"></svg>
                    </div>
                </section>

                {{-- Riwayat perubahan --}}
                <section class="pr-panel" aria-labelledby="emp-log-title">
                    <div class="pr-panel-head">
                        <div>
                            <h2 class="pr-panel-title" id="emp-log-title">Riwayat perubahan</h2>
                            <span class="pr-panel-sub">Siapa mengubah data karyawan apa, terbaru di atas</span>
                        </div>
                    </div>
                    <div class="pr-table-wrap">
                        <table id="activityTable" class="table">
                            <thead>
                                <tr>
                                    <th style="width:48px">#</th>
                                    <th>Keterangan</th>
                                    <th>Oleh</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </section>
            @endif
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @include('partials.datatables-error', ['permission' => 'ManageEmployee, ManageEmployeeSPVManager, atau ViewEmployee'])
    <script>
        $(function() {
            const canManage = @json(auth()->user()->can('ManageEmployee'));
            const esc = v => $('<div>').text(v ?? '').html();
            const dash = v => (v === null || v === undefined || v === '' || v === 'Empty') ? '<span class="pr-muted">-</span>' : esc(v);
            const empInitials = n => String(n || '?').split(' ').filter(Boolean).slice(0, 2).map(w => w[0]).join('').toUpperCase();
            const STATUS = { 'Active': 'is-approved', 'Pending': 'is-pending', 'On Leave': 'is-type', 'Mutation': 'is-type', 'Resign': 'is-rejected' };
            const TYPE = { 'PKWT': 'PKWT', 'PKWTT': 'PKWTT', 'On Job Training': 'OJT', 'DW': 'Daily worker' };
            const LOS = v => {
                if (!v || v === 'Empty') return '<span class="pr-muted">-</span>';
                const m = String(v).match(/(\d+) year (\d+) month/);
                if (!m) return esc(v);
                const y = +m[1], mo = +m[2];
                return `<span class="emp-los">${y ? y + ' th ' : ''}${mo} bln</span>`;
            };

            $('.js-s2').each(function() { $(this).select2({ width: '100%' }); });

            const filters = ['#filter-company', '#filter-department', '#filter-emp-status', '#filter-status', '#filter-store', '#filter-los', '#filter-group', '#filter-grading'];
            const val = id => $(id).length ? ($(id).val() || '') : '';

            const columns = [
                {
                    data: 'employee_name', name: 'employee_name',
                    render: (d, t, row) => t !== 'display' ? d : `<div class="emp-cell"><span class="emp-avatar" aria-hidden="true">${esc(empInitials(d))}</span>
                        <span><span class="pr-cell-name">${dash(d)}</span><span class="pr-cell-sub">${esc([row.employee_pengenal, row.position_name].filter(Boolean).join(', ') || '-')}</span></span></div>`
                },
                // Disembunyikan, tetap dikirim supaya pencarian mencakup NIP dan jabatan
                { data: 'employee_pengenal', name: 'employee_pengenal', visible: false },
                { data: 'position_name', name: 'position_name', visible: false },
                { data: 'name_company', name: 'name_company', render: d => dash(d) },
                { data: 'department_name', name: 'department_name', render: d => dash(d) },
                { data: 'store_name', name: 'store_name', render: d => dash(d) },
                ...(canManage ? [
                    { data: 'grading_name', name: 'grading_name', render: d => dash(d) },
                    { data: 'remark', name: 'remark', render: d => dash(d) },
                    { data: 'status_employee', name: 'status_employee', render: d => d ? `<span class="status-badge is-draft">${esc(TYPE[d] ?? d)}</span>` : dash(d) },
                    { data: 'length_of_service', name: 'length_of_service', searchable: false, render: d => LOS(d) },
                ] : []),
                { data: 'status', name: 'status', render: d => d ? `<span class="status-badge ${STATUS[d] ?? 'is-draft'}">${esc(d)}</span>` : dash(d) },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right', render: d => `<div class="action-wrap">${d}</div>` },
            ];

            const empTable = $('#employees-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                dom: 'rt<"pr-dt-bottom"<"pr-dt-left"li>p>',
                ajax: {
                    url: '{{ route('employees.employees') }}',
                    data: function(d) {
                        d.filter_company = val('#filter-company');
                        d.filter_department = val('#filter-department');
                        d.filter_emp_status = val('#filter-emp-status');
                        d.filter_status = val('#filter-status');
                        d.filter_store = val('#filter-store');
                        d.filter_los = val('#filter-los');
                        d.filter_group = val('#filter-group');
                        d.filter_grading = val('#filter-grading');
                    }
                },
                order: [[0, 'asc']],
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                pageLength: 25,
                language: {
                    processing: 'Memuat',
                    lengthMenu: 'Tampilkan _MENU_',
                    info: '_START_ sampai _END_ dari _TOTAL_',
                    infoEmpty: 'Tidak ada data',
                    infoFiltered: '',
                    zeroRecords: 'Tidak ada karyawan yang cocok dengan filter ini.',
                    emptyTable: 'Belum ada karyawan.',
                    paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                },
                columns,
            });

            empTable.on('xhr', function() {
                const json = empTable.ajax.json();
                if (!json) return;
                const n = Number(json.recordsFiltered ?? 0).toLocaleString('id-ID');
                $('#emp-count').text(`${n} karyawan${json.recordsFiltered !== json.recordsTotal ? ' cocok dari ' + Number(json.recordsTotal).toLocaleString('id-ID') : ''}`);
                $('#stat-total').text(n);
            });

            // ── Pencarian & filter ──
            let timer = null;
            $('#emp-search').on('input', function() {
                clearTimeout(timer);
                const v = this.value;
                timer = setTimeout(() => { empTable.search(v).draw(); syncReset(); }, 350);
            });

            function syncReset() {
                const active = filters.some(f => val(f)) || $('#emp-search').val();
                $('#btn-reset-filter').prop('hidden', !active);
            }

            function updateExportLinks() {
                if (!$('#btn-export-excel').length) return;
                const params = new URLSearchParams({
                    filter_company: val('#filter-company'), filter_department: val('#filter-department'),
                    filter_group: val('#filter-group'), filter_grading: val('#filter-grading'),
                    filter_store: val('#filter-store'), filter_emp_status: val('#filter-emp-status'),
                    filter_status: val('#filter-status'), filter_los: val('#filter-los'),
                });
                const base = "{{ route('Employee.export') }}";
                $('#btn-export-excel').attr('href', `${base}?${params}`);
                $('#btn-export-csv').attr('href', `${base}?${params}&type=csv`);
            }

            $(filters.join(',')).on('change', function() { empTable.ajax.reload(); updateExportLinks(); syncReset(); });
            $('#btn-reset-filter').on('click', function() {
                filters.forEach(f => $(f).val('').trigger('change.select2'));
                $('#emp-search').val('');
                empTable.search('').ajax.reload();
                updateExportLinks();
                syncReset();
            });
            $('.js-quick-status').on('click', function() {
                $('#filter-status').val(this.dataset.status).trigger('change');
                document.getElementById('emp-list-title').scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
            updateExportLinks();

            // ── Riwayat perubahan ──
            if ($('#activityTable').length) {
                $('#activityTable').DataTable({
                    processing: true,
                    serverSide: true,
                    autoWidth: false,
                    dom: 'rt<"pr-dt-bottom"<"pr-dt-left"li>p>',
                    ajax: "{{ route('data.data') }}",
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'description', name: 'description', render: d => dash(d) },
                        { data: 'causer', name: 'causer', orderable: false, searchable: false, render: d => dash(d) },
                        { data: 'created_at', name: 'created_at', render: d => d ? `<span class="pr-cell-sub">${esc(d)}</span>` : dash(d) },
                    ],
                    order: [[3, 'desc']],
                    lengthMenu: [[10, 25, 50], [10, 25, 50]],
                    language: {
                        processing: 'Memuat',
                        lengthMenu: 'Tampilkan _MENU_',
                        info: '_START_ sampai _END_ dari _TOTAL_',
                        infoEmpty: 'Tidak ada data',
                        emptyTable: 'Belum ada perubahan.',
                        paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                    },
                });
            }

            @if (session('success'))
                Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('success')), confirmButtonColor: '#4f46e5' });
            @endif
            @if (session('error'))
                Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')), confirmButtonColor: '#dc2626' });
            @endif
        });
    </script>
    @if (auth()->user()->can('ManageEmployee'))
        {{-- Bagan organisasi --}}
    <script>

        const BAGAN_URL = '{{ route('employee.bagan') }}';

        let scale = 1,
            panX = 0,
            panY = 0;
        let isPanning = false,
            startX = 0,
            startY = 0,
            startPanX = 0,
            startPanY = 0;
        let totalW = 0,
            totalH = 0;
        let linesVisible = true; // ← state garis

        function applyTransform() {
            const svg = document.getElementById('svg-chart');
            svg.style.transform = `translate(${panX}px, ${panY}px) scale(${scale})`;
            document.getElementById('zoom-label').textContent = Math.round(scale * 100) + '%';
        }

        function fitToScreen() {
            const area = document.getElementById('chart-area');
            const areaW = area.clientWidth - 32;
            const areaH = area.clientHeight - 32;
            if (!totalW || !totalH) return;
            scale = Math.min(areaW / totalW, areaH / totalH, 1);
            panX = (areaW - totalW * scale) / 2 + 16;
            panY = 16;
            applyTransform();
        }

        document.getElementById('btn-zoom-in').addEventListener('click', () => {
            scale = Math.min(scale + 0.1, 3);
            applyTransform();
        });
        document.getElementById('btn-zoom-out').addEventListener('click', () => {
            scale = Math.max(scale - 0.1, 0.1);
            applyTransform();
        });
        document.getElementById('btn-fit').addEventListener('click', fitToScreen);
        document.getElementById('btn-reset-zoom').addEventListener('click', () => {
            scale = 1;
            panX = 0;
            panY = 0;
            applyTransform();
        });

        // ← Toggle garis
        document.getElementById('btn-toggle-lines').addEventListener('click', function() {
            linesVisible = !linesVisible;
            document.querySelectorAll('.connector-line').forEach(path => {
                path.style.display = linesVisible ? '' : 'none';
            });
            this.textContent = linesVisible ? 'Sembunyikan garis' : 'Tampilkan garis';
        });

        document.getElementById('chart-area').addEventListener('wheel', function(e) {
            e.preventDefault();
            const delta = e.deltaY > 0 ? -0.08 : 0.08;
            const newScale = Math.min(Math.max(scale + delta, 0.1), 3);
            const rect = this.getBoundingClientRect();
            const mouseX = e.clientX - rect.left;
            const mouseY = e.clientY - rect.top;
            panX = mouseX - (mouseX - panX) * (newScale / scale);
            panY = mouseY - (mouseY - panY) * (newScale / scale);
            scale = newScale;
            applyTransform();
        }, {
            passive: false
        });

        const chartArea = document.getElementById('chart-area');
        chartArea.addEventListener('mousedown', function(e) {
            if (e.target.closest('svg')) {
                isPanning = true;
                startX = e.clientX;
                startY = e.clientY;
                startPanX = panX;
                startPanY = panY;
                this.style.cursor = 'grabbing';
            }
        });
        document.addEventListener('mousemove', function(e) {
            if (!isPanning) return;
            panX = startPanX + (e.clientX - startX);
            panY = startPanY + (e.clientY - startY);
            applyTransform();
        });
        document.addEventListener('mouseup', function() {
            isPanning = false;
            chartArea.style.cursor = 'grab';
        });
        chartArea.addEventListener('touchstart', function(e) {
            if (e.touches.length === 1) {
                isPanning = true;
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
                startPanX = panX;
                startPanY = panY;
            }
        }, {
            passive: true
        });
        chartArea.addEventListener('touchmove', function(e) {
            if (!isPanning || e.touches.length !== 1) return;
            panX = startPanX + (e.touches[0].clientX - startX);
            panY = startPanY + (e.touches[0].clientY - startY);
            applyTransform();
        }, {
            passive: true
        });
        chartArea.addEventListener('touchend', () => {
            isPanning = false;
        });

        function gradingColor(level) {
            if (level <= 1) return {
                fill: '#EEEDFE',
                stroke: '#534AB7',
                text: '#3C3489'
            };
            if (level <= 2) return {
                fill: '#E1F5EE',
                stroke: '#1D9E75',
                text: '#085041'
            };
            if (level <= 3) return {
                fill: '#FAECE7',
                stroke: '#D85A30',
                text: '#712B13'
            };
            if (level <= 4) return {
                fill: '#E0F7FA',
                stroke: '#00ACC1',
                text: '#006064'
            };
            if (level <= 5) return {
                fill: '#FFF3E0',
                stroke: '#FB8C00',
                text: '#E65100'
            };
            if (level <= 6) return {
                fill: '#FFF8E1',
                stroke: '#FBC02D',
                text: '#F57F17'
            };
            if (level <= 7) return {
                fill: '#FFFDE7',
                stroke: '#C0CA33',
                text: '#827717'
            };
            if (level <= 8) return {
                fill: '#F1F8E9',
                stroke: '#7CB342',
                text: '#33691E'
            };
            if (level <= 9) return {
                fill: '#EDE7F6',
                stroke: '#673AB7',
                text: '#311B92'
            };
            if (level <= 10) return {
                fill: '#F3E5F5',
                stroke: '#8E24AA',
                text: '#4A148C'
            };
            if (level <= 11) return {
                fill: '#FCE4EC',
                stroke: '#D81B60',
                text: '#880E4F'
            };
            return {
                fill: '#F1EFE8',
                stroke: '#888780',
                text: '#444441'
            };
        }

        function initials(name) {
            return String(name || '?').split(' ').filter(Boolean).slice(0, 2).map(w => w[0]).join('').toUpperCase();
        }

        function truncate(str, max) {
            return str.length > max ? str.substring(0, max - 1) + '…' : str;
        }

        function showDetail(node) {
            const esc = v => String(v ?? '-').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            Swal.fire({
                title: esc(node.name),
                html: `<dl style="text-align:left;margin:0;font-size:.875rem;line-height:1.7">
                    <dt>Perusahaan</dt><dd>${esc(node.company_name)}</dd>
                    <dt>Grading</dt><dd>${esc(node.grading)}</dd>
                    <dt>Jabatan</dt><dd>${esc(node.all_positions)}</dd>
                    <dt>Lokasi</dt><dd>${esc(node.all_stores)}</dd>
                    <dt>Departemen</dt><dd>${esc(node.all_departments)}</dd>
                </dl>`,
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#4f46e5',
            });
        }

        function drawEmpty(message) {
            const svg = document.getElementById('svg-chart');
            const placeholder = document.getElementById('chart-placeholder');
            svg.style.display = 'none';
            placeholder.classList.remove('d-none');
            placeholder.classList.add('d-flex');
            placeholder.textContent = message;
        }

        function drawChart(nodes) {
            const placeholder = document.getElementById('chart-placeholder');
            const svg = document.getElementById('svg-chart');
            placeholder.classList.remove('d-flex');
            placeholder.classList.add('d-none');
            svg.style.display = 'block';
            svg.innerHTML = '';

            const nodeW = 180,
                nodeH = 76,
                hGap = 28,
                vGap = 64;

            const byLevel = {};
            nodes.forEach(n => {
                if (!byLevel[n.grading_level]) byLevel[n.grading_level] = [];
                byLevel[n.grading_level].push(n);
            });
            const levels = Object.keys(byLevel).map(Number).sort((a, b) => a - b);
            const maxInRow = Math.max(...levels.map(l => byLevel[l].length));
            totalW = Math.max(700, maxInRow * (nodeW + hGap) + hGap * 2);
            totalH = levels.length * (nodeH + vGap) + vGap + 20;

            svg.setAttribute('width', totalW);
            svg.setAttribute('height', totalH);

            const posMap = {};
            levels.forEach((level, li) => {
                const row = byLevel[level];
                const rowW = row.length * nodeW + (row.length - 1) * hGap;
                const startX = (totalW - rowW) / 2;
                const y = vGap + li * (nodeH + vGap);
                row.forEach((node, ni) => {
                    const x = startX + ni * (nodeW + hGap);
                    posMap[node.id] = {
                        x,
                        y,
                        cx: x + nodeW / 2,
                        cy: y + nodeH / 2
                    };
                });
            });

            const ns = 'http://www.w3.org/2000/svg';
            const defs = document.createElementNS(ns, 'defs');
            const marker = document.createElementNS(ns, 'marker');
            marker.setAttribute('id', 'arrow');
            marker.setAttribute('markerWidth', '6');
            marker.setAttribute('markerHeight', '6');
            marker.setAttribute('refX', '5');
            marker.setAttribute('refY', '3');
            marker.setAttribute('orient', 'auto');
            const mpath = document.createElementNS(ns, 'path');
            mpath.setAttribute('d', 'M0,0 L0,6 L6,3 z');
            mpath.setAttribute('fill', '#B4B2A9');
            marker.appendChild(mpath);
            defs.appendChild(marker);
            svg.appendChild(defs);

            // ← Gambar garis dengan class connector-line
            nodes.forEach(node => {
                if (!node.atasan_id || !posMap[node.atasan_id]) return;
                const from = posMap[node.atasan_id];
                const to = posMap[node.id];
                const midY = from.y + nodeH + (to.y - from.y - nodeH) / 2;
                const path = document.createElementNS(ns, 'path');
                path.setAttribute('d',
                    `M${from.cx},${from.y + nodeH} L${from.cx},${midY} L${to.cx},${midY} L${to.cx},${to.y}`);
                path.setAttribute('stroke', '#B4B2A9');
                path.setAttribute('stroke-width', '1.5');
                path.setAttribute('fill', 'none');
                path.setAttribute('marker-end', 'url(#arrow)');
                path.setAttribute('class', 'connector-line'); // ← tambah class
                if (!linesVisible) path.style.display = 'none'; // ← pertahankan state
                svg.appendChild(path);
            });

            // ← Gambar node dengan satu click handler di g
            nodes.forEach(node => {
                const pos = posMap[node.id];
                const c = gradingColor(node.grading_level);
                const g = document.createElementNS(ns, 'g');
                g.style.cursor = 'pointer';
                g.addEventListener('click', () => showDetail(node)); // ← satu click handler saja

                const rect = document.createElementNS(ns, 'rect');
                rect.setAttribute('x', pos.x);
                rect.setAttribute('y', pos.y);
                rect.setAttribute('width', nodeW);
                rect.setAttribute('height', nodeH);
                rect.setAttribute('rx', 8);
                rect.setAttribute('fill', c.fill);
                rect.setAttribute('stroke', c.stroke);
                rect.setAttribute('stroke-width', '1');
                g.appendChild(rect);

                const avatarCx = pos.x + 28;
                const avatarCy = pos.y + nodeH / 2;
                const circle = document.createElementNS(ns, 'circle');
                circle.setAttribute('cx', avatarCx);
                circle.setAttribute('cy', avatarCy);
                circle.setAttribute('r', 20);
                circle.setAttribute('fill', c.stroke);
                g.appendChild(circle);

                if (node.photo) {
                    const clipId = 'clip-' + node.id;
                    const clipPath = document.createElementNS(ns, 'clipPath');
                    clipPath.setAttribute('id', clipId);
                    const clipCircle = document.createElementNS(ns, 'circle');
                    clipCircle.setAttribute('cx', avatarCx);
                    clipCircle.setAttribute('cy', avatarCy);
                    clipCircle.setAttribute('r', 20);
                    clipPath.appendChild(clipCircle);
                    defs.appendChild(clipPath);
                    const img = document.createElementNS(ns, 'image');
                    img.setAttribute('href', node.photo);
                    img.setAttribute('x', avatarCx - 20);
                    img.setAttribute('y', avatarCy - 20);
                    img.setAttribute('width', 40);
                    img.setAttribute('height', 40);
                    img.setAttribute('clip-path', `url(#${clipId})`);
                    g.appendChild(img);
                } else {
                    const avatarText = document.createElementNS(ns, 'text');
                    avatarText.setAttribute('x', avatarCx);
                    avatarText.setAttribute('y', avatarCy + 5);
                    avatarText.setAttribute('text-anchor', 'middle');
                    avatarText.setAttribute('font-size', '12');
                    avatarText.setAttribute('font-weight', '500');
                    avatarText.setAttribute('fill', '#fff');
                    avatarText.textContent = initials(node.name);
                    g.appendChild(avatarText);
                }

                const nameText = document.createElementNS(ns, 'text');
                nameText.setAttribute('x', pos.x + 56);
                nameText.setAttribute('y', pos.y + 24);
                nameText.setAttribute('font-size', '12');
                nameText.setAttribute('font-weight', '500');
                nameText.setAttribute('fill', c.text);
                nameText.textContent = truncate(node.name, 20);
                g.appendChild(nameText);

                const posText = document.createElementNS(ns, 'text');
                posText.setAttribute('x', pos.x + 56);
                posText.setAttribute('y', pos.y + 42);
                posText.setAttribute('font-size', '11');
                posText.setAttribute('fill', c.stroke);
                posText.textContent = truncate(node.position, 22);
                g.appendChild(posText);

                const gradText = document.createElementNS(ns, 'text');
                gradText.setAttribute('x', pos.x + 56);
                gradText.setAttribute('y', pos.y + 58);
                gradText.setAttribute('font-size', '10');
                gradText.setAttribute('fill', c.text);
                gradText.setAttribute('opacity', '0.7');
                gradText.textContent = node.grading;
                g.appendChild(gradText);

                svg.appendChild(g);
            });

            setTimeout(fitToScreen, 50);
        }

        document.getElementById('btn-load').addEventListener('click', function() {
            const storeId = $('#sel-store').val();
            const deptId = $('#sel-dept').val();
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Memuat';

            fetch(`${BAGAN_URL}?store_id=${storeId}&department_id=${deptId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        drawEmpty('Error: ' + data.error);
                        return;
                    }
                    if (!data.nodes || !data.nodes.length) {
                        drawEmpty('Tidak ada karyawan di lokasi dan departemen ini.');
                    } else {
                        drawChart(data.nodes);
                    }
                })
                .catch(err => {
                    drawEmpty('Gagal memuat data: ' + err.message);
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-sitemap" aria-hidden="true"></i> Tampilkan bagan';
                });
        });

        document.getElementById('btn-bagan-reset').addEventListener('click', function() {
            $('#sel-store').val('all').trigger('change');
            $('#sel-dept').val('all').trigger('change');
            const svg = document.getElementById('svg-chart');
            svg.style.display = 'none';
            const placeholder = document.getElementById('chart-placeholder');
            placeholder.classList.remove('d-none');
            placeholder.classList.add('d-flex');
            placeholder.textContent = 'Pilih lokasi dan departemen, lalu klik Tampilkan bagan.';
            scale = 1;
            panX = 0;
            panY = 0;
            applyTransform();
        });
    </script>

    @endif
@endpush
