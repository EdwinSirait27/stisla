@extends('layouts.app')
@section('title', 'Detail karyawan')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand · redesign: full
         * paper: oklch(98.4% 0.004 265) · accent: oklch(61% 0.17 276) · display: Nunito · body: Nunito
         * nav: kept (Stisla shell) · signature: where this person sits today: placement, who approves them, contract clock, pay in force · states: manage vs SPV/view (sensitive hidden), no atasan, no salary, scheduled salary, resign
         * axes: light/rounded-sans/chromatic-indigo · posture: app · contrast: pass · v2.0.0 */
        /* Hallmark · pre-emit critique: P4 H4 E4 S4 R4 V4 · weakest: R, two columns of panels; the facts strip carries the one-glance answer */
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
        $emp = $employee->Employee;
        $canManage = auth()->user()->can('ManageEmployee');
        $fmt = fn($v, $f = 'd F Y') => $v ? \Carbon\Carbon::parse($v)->locale('id')->translatedFormat($f) : null;
        $primary = fn($rel) => $rel?->firstWhere('pivot.is_primary', true) ?? $rel?->first();
        $position = $primary($emp->position);
        $department = $primary($emp->department);
        $store = $primary($emp->store);
        $joinRaw = $emp->getRawOriginal('join_date');
        $tenure = null;
        if ($joinRaw) {
            $j = \Carbon\Carbon::parse($joinRaw);
            $y = (int) $j->diffInYears(now());
            $m = (int) $j->copy()->addYears($y)->diffInMonths(now());
            $tenure = trim(($y ? $y . ' tahun ' : '') . $m . ' bulan');
        }
        $endDate = $emp->end_date ? \Carbon\Carbon::parse($emp->getRawOriginal('end_date') ?? $emp->end_date) : null;
        $daysToEnd = $endDate ? (int) today()->diffInDays($endDate, false) : null;
        $statusClass = ['Active' => 'is-approved', 'Pending' => 'is-pending', 'Resign' => 'is-rejected'][$emp->status] ?? 'is-type';
        $acct = $emp->bank_account_number;
        $row = function ($label, $value) {
            $empty = $value === null || trim((string) $value) === '' || in_array(trim((string) $value), ['-', '0'], true);
            return '<div><dt>' . e($label) . '</dt><dd class="' . ($empty ? 'is-empty' : '') . '">' . e($empty ? 'Belum diisi' : $value) . '</dd></div>';
        };
        $rp = fn($n) => 'Rp' . number_format((float) $n, 0, ',', '.');
        $isDw = strtoupper($emp->status_employee ?? '') === 'DW';
    @endphp
    <div class="main-content pr">
        <section class="section">

            <header class="pr-head">
                <div class="pr-hello">
                    @if (!empty($emp->photos))
                        <img src="{{ route('useremployee.photo', basename($emp->photos)) }}" alt="" class="emp-avatar-lg no-drag" width="84" height="84">
                    @else
                        <span class="emp-avatar-lg emp-initials-lg" aria-hidden="true">{{ collect(explode(' ', $emp->employee_name ?? 'U'))->filter()->take(2)->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') }}</span>
                    @endif
                    <div>
                        <nav aria-label="Breadcrumb">
                            <ol class="pr-crumbs">
                                <li><a href="{{ route('pages.Employee') }}">Karyawan</a></li>
                                <li aria-current="page">Detail</li>
                            </ol>
                        </nav>
                        <h1 class="pr-title">{{ $emp->employee_name }}</h1>
                        <p class="pr-lede">{{ collect([$position?->name, $department?->department_name, $store?->name, $emp->company?->name])->filter()->implode(', ') ?: 'Penempatan belum diatur' }}</p>
                        <div class="emp-chips">
                            <span class="status-badge {{ $statusClass }}">{{ $emp->status ?? '-' }}</span>
                            @if ($emp->employee_pengenal)<span class="emp-chip">NIP {{ $emp->employee_pengenal }}</span>@endif
                            @if ($emp->status_employee)<span class="emp-chip">{{ $emp->status_employee }}</span>@endif
                            @if ($emp->grading?->grading_name)<span class="emp-chip">Grading {{ $emp->grading->grading_name }}</span>@endif
                            @if ($emp->can_approve)<span class="emp-chip">Bisa menyetujui</span>@endif
                        </div>
                    </div>
                </div>
                @if ($canManage)
                    <div class="pr-actions">
                        <a href="{{ route('Employee.edit', $hashedId) }}" class="pr-btn pr-btn-primary"><i class="fas fa-pen" aria-hidden="true"></i> Ubah data</a>
                    </div>
                @endif
            </header>

            {{-- Ringkas (signature: posisi karyawan ini hari ini) --}}
            <section class="pr-panel" aria-label="Ringkasan">
                <dl class="emp-facts">
                    <div><dt>Masa kerja</dt><dd>{{ $tenure ?? '-' }}<small>sejak {{ $fmt($joinRaw) ?? '-' }}</small></dd></div>
                    <div>
                        <dt>{{ $emp->status === 'Resign' ? 'Tanggal keluar' : 'Akhir kontrak' }}</dt>
                        <dd class="{{ $daysToEnd !== null && $daysToEnd >= 0 && $daysToEnd <= 30 ? 'is-warn' : '' }}">
                            {{ $endDate ? $fmt($endDate) : 'Tidak ada' }}
                            @if ($daysToEnd !== null && $emp->status !== 'Resign')
                                <small>{{ $daysToEnd >= 0 ? $daysToEnd . ' hari lagi' : 'sudah lewat ' . abs($daysToEnd) . ' hari' }}</small>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt>Atasan langsung</dt>
                        <dd>{{ $emp->atasanList->pluck('employee_name')->implode(', ') ?: 'Belum diatur' }}<small>{{ $emp->atasanList->isEmpty() ? 'cuti tidak punya penyetuju' : 'menyetujui cuti dan lembur' }}</small></dd>
                    </div>
                    <div><dt>Bawahan langsung</dt><dd>{{ $emp->bawahanList->count() }} orang</dd></div>
                    @if ($canManage)
                        <div><dt>PIN fingerprint</dt><dd>{{ $emp->pin ?: 'Belum diisi' }}<small>{{ $emp->attendance_type ?: 'tipe absen belum diatur' }}</small></dd></div>
                    @endif
                </dl>
            </section>

            <div class="emp-grid">
                <div class="emp-col">
                    {{-- Penempatan --}}
                    <section class="pr-panel" aria-labelledby="sec-place">
                        <div class="pr-panel-head"><h2 class="pr-panel-title" id="sec-place">Penempatan</h2></div>
                        @foreach ([['Jabatan', $emp->position, 'name'], ['Departemen', $emp->department, 'department_name'], ['Lokasi', $emp->store, 'name']] as [$label, $items, $col])
                            <div class="emp-placement">
                                <h3>{{ $label }}</h3>
                                <div class="emp-pills">
                                    @forelse ($items->sortByDesc(fn($x) => (int) $x->pivot->is_primary) as $it)
                                        <span class="emp-pill {{ $it->pivot->is_primary ? 'is-primary' : '' }}">{{ $it->{$col} }}@if ($it->pivot->is_primary) <small>utama</small>@endif</span>
                                    @empty
                                        <span class="pr-muted">Belum diatur</span>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </section>

                    {{-- Data pribadi --}}
                    <section class="pr-panel" aria-labelledby="sec-personal">
                        <div class="pr-panel-head"><h2 class="pr-panel-title" id="sec-personal">Data pribadi</h2></div>
                        <dl class="emp-dl">
                            {!! $row('Jenis kelamin', $emp->gender) !!}
                            {!! $row('Agama', $emp->religion) !!}
                            @if ($canManage)
                                {!! $row('Tempat, tanggal lahir', collect([$emp->place_of_birth, $fmt($emp->date_of_birth)])->filter()->implode(', ')) !!}
                                {!! $row('Nama ibu kandung', $emp->biological_mother_name) !!}
                            @endif
                            {!! $row('Status menikah', $emp->marriage === 'Yes' ? 'Menikah' : ($emp->marriage === 'No' ? 'Belum menikah' : $emp->marriage)) !!}
                            {!! $row('Jumlah anak', (string) $emp->child) !!}
                            {!! $row('Golongan darah', $emp->blood_type) !!}
                            {!! $row('Pendidikan terakhir', collect([$emp->last_education, $emp->institution])->filter()->implode(', ')) !!}
                        </dl>
                    </section>

                    @if ($canManage)
                        <section class="pr-panel" aria-labelledby="sec-contact">
                            <div class="pr-panel-head"><h2 class="pr-panel-title" id="sec-contact">Kontak dan alamat</h2></div>
                            <dl class="emp-dl">
                                {!! $row('Telepon', $emp->telp_number) !!}
                                @if ($emp->pending_telp_number){!! $row('Telepon baru (menunggu HR)', $emp->pending_telp_number) !!}@endif
                                {!! $row('Email pribadi', $emp->email) !!}
                                @if ($emp->pending_email){!! $row('Email baru (menunggu HR)', $emp->pending_email) !!}@endif
                                {!! $row('Email kantor', $emp->company_email) !!}
                                {!! $row('Kontak darurat', $emp->emergency_contact_name) !!}
                                {!! $row('Alamat domisili', $emp->current_address) !!}
                                {!! $row('Alamat KTP', $emp->id_card_address) !!}
                            </dl>
                        </section>
                    @else
                        <section class="pr-panel" aria-labelledby="sec-contact">
                            <div class="pr-panel-head"><h2 class="pr-panel-title" id="sec-contact">Kontak kantor</h2></div>
                            <dl class="emp-dl">{!! $row('Email kantor', $emp->company_email) !!}</dl>
                        </section>
                    @endif
                </div>

                <div class="emp-col">
                    {{-- Atasan & bawahan --}}
                    <section class="pr-panel" aria-labelledby="sec-team">
                        <div class="pr-panel-head">
                            <div>
                                <h2 class="pr-panel-title" id="sec-team">Atasan dan bawahan</h2>
                                <span class="pr-panel-sub">Dari data atasan karyawan (dipakai untuk approval)</span>
                            </div>
                        </div>
                        <ul class="emp-list">
                            @forelse ($emp->atasanList as $a)
                                <li>
                                    <span><span class="emp-list-name">{{ $a->employee_name }}</span><span class="emp-list-sub">{{ $a->position->first()?->name ?? '-' }}</span></span>
                                    <span class="status-badge is-open">Atasan{{ $a->pivot->is_primary ? ' utama' : '' }}</span>
                                </li>
                            @empty
                                <li><span class="emp-list-sub">Belum ada atasan. Pengajuan cuti karyawan ini tidak punya penyetuju.</span></li>
                            @endforelse
                            @foreach ($emp->bawahanList as $b)
                                <li>
                                    <span><span class="emp-list-name">{{ $b->employee_name }}</span><span class="emp-list-sub">{{ $b->position->first()?->name ?? '-' }}</span></span>
                                    <span class="status-badge is-draft">Bawahan</span>
                                </li>
                            @endforeach
                        </ul>
                    </section>

                    @if ($canManage)
                        <section class="pr-panel" aria-labelledby="sec-id">
                            <div class="pr-panel-head"><h2 class="pr-panel-title" id="sec-id">Identitas, BPJS, dan bank</h2></div>
                            <dl class="emp-dl">
                                {!! $row('NIK', $emp->nik) !!}
                                {!! $row('Nomor KK', $emp->kk_number) !!}
                                {!! $row('NPWP', $emp->npwp) !!}
                                {!! $row('BPJS Kesehatan', $emp->bpjs_kes) !!}
                                {!! $row('BPJS Ketenagakerjaan', $emp->bpjs_ket) !!}
                                {!! $row('Bank', $emp->bank?->name) !!}
                                {!! $row('Nomor rekening', $acct) !!}
                                {!! $row('Group', $emp->group ? trim($emp->group->group_name . ' ' . $emp->group->remark) : null) !!}
                            </dl>
                        </section>
                    @endif

                    @if ($salary || $scheduledSalary)
                        <section class="pr-panel" aria-labelledby="sec-salary">
                            <div class="pr-panel-head">
                                <div>
                                    <h2 class="pr-panel-title" id="sec-salary">Gaji yang berlaku</h2>
                                    <span class="pr-panel-sub">{{ $salary ? 'Sejak ' . $fmt($salary->effective_date) : 'Belum ada gaji yang berlaku' }}</span>
                                </div>
                                @can('employee.salary.view')
                                    <a href="{{ route('employeesalary.index') }}" class="pr-btn pr-btn-sm">Data gaji</a>
                                @endcan
                            </div>
                            @if ($salary)
                                <dl class="emp-dl emp-money">
                                    @if ($isDw)
                                        {!! $row('Tarif harian', $rp($salary->daily_rate)) !!}
                                    @else
                                        {!! $row('Gaji pokok', $rp($salary->basic_salary)) !!}
                                        {!! $row('Tunjangan jabatan', $rp($salary->position_allowance)) !!}
                                    @endif
                                    {!! $row('Tunjangan (makan, rumah, transport)', $rp($salary->meal_allowance + $salary->house_allowance + $salary->transport_allowance)) !!}
                                    {!! $row('Potongan BPJS', $rp($salary->bpjs_kesehatan + $salary->bpjs_ketenagakerjaan)) !!}
                                </dl>
                            @endif
                            @if ($scheduledSalary)
                                <p class="pr-note">Perubahan terjadwal mulai {{ $fmt($scheduledSalary->effective_date) }}.</p>
                            @endif
                        </section>
                    @elseif (auth()->user()->can('employee.salary.view'))
                        <section class="pr-panel" aria-labelledby="sec-salary">
                            <div class="pr-panel-head"><h2 class="pr-panel-title" id="sec-salary">Gaji yang berlaku</h2></div>
                            <p class="pr-empty-note">
                                Belum ada data gaji, jadi payroll karyawan ini tidak bisa di-generate.
                                @can('employee.salary.create')<a href="{{ route('employeesalary.create', ['employee_id' => $emp->id]) }}">Isi gaji sekarang</a>.@endcan
                            </p>
                        </section>
                    @endif

                    @if ($canManage)
                        <section class="pr-panel" aria-labelledby="sec-photos">
                            <div class="pr-panel-head"><h2 class="pr-panel-title" id="sec-photos">Foto dan tanda tangan</h2></div>
                            <div class="emp-photos">
                                @foreach ([['Foto profil', $emp->photos, 'useremployee.photo'], ['KTP', $emp->ktp_photos, 'useremployeektp.photo'], ['KK', $emp->kk_photos, 'useremployeekk.photo'], ['Tanda tangan', $emp->signature, 'useremployeesignature.photo']] as [$label, $path, $route])
                                    <div class="emp-photo">
                                        <span>{{ $label }}</span>
                                        <div class="emp-photo-box">
                                            @if ($path)
                                                <a href="{{ route($route, basename($path)) }}" target="_blank" rel="noopener"><img src="{{ route($route, basename($path)) }}" alt="{{ $label }} {{ $emp->employee_name }}" loading="lazy"></a>
                                            @else
                                                Belum ada
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>

                        <section class="pr-panel" aria-labelledby="sec-docs">
                            <div class="pr-panel-head">
                                <div>
                                    <h2 class="pr-panel-title" id="sec-docs">Dokumen</h2>
                                    <span class="pr-panel-sub">{{ $documents->count() }} dokumen</span>
                                </div>
                            </div>
                            @if ($documents->isEmpty())
                                <p class="pr-empty-note">Belum ada dokumen yang diterbitkan untuk karyawan ini.</p>
                            @else
                                <ul class="emp-list">
                                    @foreach ($documents as $doc)
                                        <li>
                                            <span><span class="emp-list-name">{{ $doc->companydocumentconfigs?->documenttypes?->document_name ?? 'Dokumen' }}</span><span class="emp-list-sub">{{ $doc->document_number }}</span></span>
                                            <a href="{{ route('Employee.document.download', ['hashedId' => $hashedId, 'documentId' => $doc->id]) }}" class="pr-btn pr-btn-sm"><i class="fas fa-download" aria-hidden="true"></i> Unduh</a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </section>

                        <section class="pr-panel" aria-labelledby="sec-log">
                            <div class="pr-panel-head"><h2 class="pr-panel-title" id="sec-log">Perubahan terakhir</h2></div>
                            @if ($activities->isEmpty())
                                <p class="pr-empty-note">Belum ada catatan perubahan.</p>
                            @else
                                <ul class="emp-list">
                                    @foreach ($activities as $act)
                                        <li>
                                            <span><span class="emp-list-name">{{ \Illuminate\Support\Str::limit($act->description, 140) }}</span>
                                            <span class="emp-list-sub">{{ $act->causer?->employee?->employee_name ?? 'Sistem' }}, {{ $act->created_at->locale('id')->translatedFormat('d M Y H:i') }}</span></span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </section>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection
