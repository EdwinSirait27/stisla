@extends('layouts.app')
@section('title', 'Absensi mobile')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand · redesign: full
         * paper: oklch(98.4% 0.004 265) · accent: oklch(61% 0.17 276) · display: Nunito · body: Nunito
         * nav: kept (Stisla shell) · signature: pending manual entries counted for the viewer with one click to list them; face, GPS and geofence folded into one verification cell · states: HR, SPV, view-only, empty, manual entry
         * axes: light/rounded-sans/chromatic-indigo · posture: app · contrast: pass · v2.0.0 */
        /* Hallmark · pre-emit critique: P4 H4 E4 S4 R4 V4 · weakest: E, three verification flags per row; answered by one chip group */
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

        /* ── Absensi mobile ── */
        .am-checks { display: flex; flex-wrap: wrap; gap: 4px; max-width: 22ch; }
        .am-chip { display: inline-flex; align-items: center; gap: 4px; padding: 0 7px; border-radius: var(--pr-pill); background: var(--pr-success-soft); font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-success); white-space: nowrap; }
        .am-chip.is-bad { background: var(--pr-error-soft); color: var(--pr-error); }
        .am-chip.is-muted { background: var(--pr-paper-2); color: var(--pr-ink-2); }
        .am-type { display: inline-block; padding: 1px 8px; border-radius: var(--pr-pill); font-size: var(--pr-text-xs); font-weight: 700; background: var(--pr-accent-soft); color: var(--pr-accent-deep); }
        .am-type.is-out { background: var(--pr-warn-soft); color: var(--pr-warn); }
        .am-time { display: block; margin-top: 2px; font-size: var(--pr-text-lg); font-weight: 800; font-variant-numeric: tabular-nums; color: var(--pr-ink); }
        #attendance-table td { vertical-align: top; }

        /* ── Persetujuan bertingkat ── */
        .am-flow { display: flex; flex-wrap: wrap; align-items: center; gap: var(--pr-space-xs); margin: 0; padding: var(--pr-space-sm) var(--pr-space-lg); list-style: none; border-top: 1px solid var(--pr-rule); font-size: var(--pr-text-xs); color: var(--pr-muted); }
        .am-flow li { display: flex; align-items: center; gap: var(--pr-space-xs); }
        .am-flow i { font-size: 10px; color: var(--pr-muted); }
        .am-bulk { display: flex; flex-wrap: wrap; align-items: center; gap: var(--pr-space-sm); margin: 0 var(--pr-space-lg) var(--pr-space-sm); padding: var(--pr-space-sm) var(--pr-space-md); border-radius: var(--pr-radius-sm); background: var(--pr-accent-soft); color: var(--pr-accent-deep); font-size: var(--pr-text-sm); font-weight: 600; }
        .am-bulk[hidden] { display: none; }
        .am-bulk span { margin-right: auto; }
        .am-select { width: 18px; height: 18px; accent-color: var(--pr-accent); cursor: pointer; }
        .am-select:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 2px; }
        #attendance-table th.am-col-select, #attendance-table td.am-col-select { width: 36px; }
        .am-col-select .pr-muted { font-size: var(--pr-text-xs); }
        #activity-table td { vertical-align: top; }
        .am-activity[hidden] { display: none; }

        .am-grid { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: var(--pr-space-lg); align-items: start; }
        .am-photo { display: block; width: 100%; padding: 0; border: 0; background: var(--pr-paper); cursor: zoom-in; }
        .am-photo img { display: block; width: 100%; max-height: 420px; object-fit: contain; }
        .am-photo:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: -2px; }
        .am-map { display: block; width: 100%; height: 260px; border: 0; border-top: 1px solid var(--pr-rule); }
        .am-verdict { display: grid; gap: 0; margin: 0; padding: 0; list-style: none; }
        .am-verdict li { display: flex; gap: var(--pr-space-sm); align-items: flex-start; padding: var(--pr-space-sm) var(--pr-space-lg); border-bottom: 1px solid var(--pr-rule); font-size: var(--pr-text-sm); color: var(--pr-ink); }
        .am-verdict li:last-child { border-bottom: 0; }
        .am-verdict i { width: 1.1em; margin-top: 3px; color: var(--pr-success); }
        .am-verdict li.is-bad i { color: var(--pr-error); }
        .am-verdict li.is-muted i { color: var(--pr-muted); }
        .am-verdict small { display: block; color: var(--pr-ink-2); }

        @media (max-width: 64rem) { .am-grid { grid-template-columns: minmax(0, 1fr); } }
    </style>
@endpush


@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <h1 class="pr-title">Absensi mobile</h1>
                    <p class="pr-lede">Check-in dan check-out dari aplikasi, diverifikasi dengan selfie, GPS, dan radius lokasi. Entri manual dari atasan atau HR juga tercatat di sini.</p>
                </div>
                <div class="pr-actions">
                    @if ($canManage)
                        <button type="button" class="pr-btn" id="btnActivity" aria-controls="am-activity"><i class="fas fa-clock-rotate-left" aria-hidden="true"></i> Riwayat aktivitas</button>
                    @endif
                    @if ($canCreate)
                        <button type="button" class="pr-btn pr-btn-primary" data-toggle="modal" data-target="#modalCreateAttendance"><i class="fas fa-plus" aria-hidden="true"></i> Ajukan entri manual</button>
                    @endif
                </div>
            </header>

            <section class="pr-panel" aria-label="Ringkasan">
                <dl class="tl-stats">
                    <div id="pending-box"><dt>Menunggu checker</dt><dd id="st-pending">–</dd>
                        <button type="button" class="lv-link" data-status="pending" hidden>Tampilkan</button>
                        <span>{{ $canSpvManager ? 'entri manual yang perlu Anda cek' : 'entri manual, dicek SPV atau manager' }}</span></div>
                    <div id="approved-box"><dt>Menunggu HR</dt><dd id="st-approved">–</dd>
                        <button type="button" class="lv-link" data-status="approved" hidden>Tampilkan</button>
                        <span>{{ $canManage ? 'lolos checker atau verifikasi aplikasi, perlu Anda setujui' : 'lolos checker atau verifikasi aplikasi' }}</span></div>
                    <div><dt>Disetujui HR</dt><dd id="st-approved-hr">–</dd><span>final</span></div>
                    <div id="flag-box"><dt>Ditandai</dt><dd id="st-flagged">–</dd><span>perlu ditinjau</span></div>
                    <div><dt>Absen</dt><dd id="st-in">–</dd><span id="st-emp">dari – karyawan</span></div>
                    <div><dt>Entri manual</dt><dd id="st-manual">–</dd><span>tanpa GPS dan selfie</span></div>
                </dl>
                <ol class="am-flow" aria-label="Alur persetujuan">
                    <li>Entri manual diajukan</li><li><i class="fas fa-chevron-right" aria-hidden="true"></i>Menunggu checker</li>
                    <li><i class="fas fa-chevron-right" aria-hidden="true"></i>SPV atau manager mengecek</li><li><i class="fas fa-chevron-right" aria-hidden="true"></i>Menunggu HR</li>
                    <li><i class="fas fa-chevron-right" aria-hidden="true"></i>HR menyetujui</li><li><i class="fas fa-chevron-right" aria-hidden="true"></i>Disetujui HR</li>
                    <li>Absen dari aplikasi yang lolos verifikasi wajah dan GPS langsung menunggu HR.</li>
                </ol>
            </section>

            <section class="pr-panel" aria-labelledby="am-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="am-title">Log absensi</h2>
                        <span class="pr-panel-sub" id="am-count">Memuat</span>
                    </div>
                    <div class="pr-search">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <label for="am-search" class="sr-only">Cari nama atau NIP</label>
                        <input type="search" id="am-search" placeholder="Cari nama atau NIP">
                    </div>
                </div>
                <div class="emp-filters">
                    <div class="pr-field"><label for="filterDateFrom">Tanggal kerja dari</label><input type="date" id="filterDateFrom" class="emp-input" value="{{ $defaultFrom }}"></div>
                    <div class="pr-field"><label for="filterDateTo">Sampai</label><input type="date" id="filterDateTo" class="emp-input" value="{{ $defaultTo }}"></div>
                    <div class="pr-field"><label for="filterType">Tipe</label>
                        <select id="filterType" class="emp-select"><option value="">Semua</option><option value="checkin">Check-in</option><option value="checkout">Check-out</option></select></div>
                    <div class="pr-field"><label for="filterStatus">Status</label>
                        <select id="filterStatus" class="emp-select"><option value="">Semua</option><option value="pending">Menunggu checker</option><option value="approved">Menunggu HR</option><option value="approved_hr">Disetujui HR</option><option value="flagged">Ditandai</option></select></div>
                    <div class="pr-field"><label for="filterSource">Sumber</label>
                        <select id="filterSource" class="emp-select"><option value="">Semua</option><option value="app">Aplikasi</option><option value="manual">Entri manual</option><option value="offsite">Di luar lokasi</option></select></div>
                    @if ($canManage)
                        <div class="pr-field"><label for="filterCompany">Perusahaan</label>
                            <select id="filterCompany" class="emp-select js-s2"><option value="">Semua perusahaan</option>
                                @foreach ($companies as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                            </select></div>
                        <div class="pr-field"><label for="filterDepartment">Departemen</label>
                            <select id="filterDepartment" class="emp-select js-s2"><option value="">Semua departemen</option>
                                @foreach ($departments as $d)<option value="{{ $d->id }}">{{ $d->department_name }}</option>@endforeach
                            </select></div>
                    @endif
                    @if ($canManage || $canSpvManager || $canView)
                        <div class="pr-field"><label for="filterStore">Lokasi</label>
                            <select id="filterStore" class="emp-select js-s2"><option value="">Semua lokasi</option>
                                @foreach ($stores as $st)<option value="{{ $st->id }}">{{ $st->name }}</option>@endforeach
                            </select></div>
                        <div class="pr-field"><label for="filterEmployee">Karyawan</label>
                            <select id="filterEmployee" class="emp-select js-s2"><option value="">Semua karyawan</option>
                                @foreach ($employees as $e)<option value="{{ $e->id }}">{{ $e->employee_name }}</option>@endforeach
                            </select></div>
                    @endif
                    <div class="emp-filter-actions">
                        <button type="button" class="pr-btn pr-btn-sm" id="btnReset">Reset</button>
                        <button type="button" class="pr-btn pr-btn-sm pr-btn-primary" id="btnFilter">Terapkan</button>
                    </div>
                </div>
                @if ($canManage || $canSpvManager)
                    <div class="am-bulk" id="am-bulk" role="region" aria-live="polite" aria-label="Persetujuan massal" hidden>
                        <span id="am-bulk-count">0 dipilih</span>
                        @if ($canSpvManager)
                            <button type="button" class="pr-btn pr-btn-sm pr-btn-primary" id="btnBulkChecker" hidden>Setujui sebagai checker</button>
                        @endif
                        @if ($canManage)
                            <button type="button" class="pr-btn pr-btn-sm pr-btn-primary" id="btnBulkHr" hidden>Setujui HR</button>
                        @endif
                        <button type="button" class="pr-btn pr-btn-sm" id="btnBulkClear">Batal pilih</button>
                    </div>
                @endif
                <div class="pr-table-wrap">
                    <table class="table" id="attendance-table">
                        <thead>
                            <tr>
                                <th class="am-col-select">@if ($canManage || $canSpvManager)<input type="checkbox" class="am-select" id="am-select-all" aria-label="Pilih semua yang bisa disetujui di halaman ini">@else<span class="sr-only">Pilih</span>@endif</th>
                                <th>Karyawan</th>
                                <th>Lokasi</th>
                                <th>Tanggal kerja</th>
                                <th>Absen</th>
                                <th>Verifikasi</th>
                                <th>Status</th>
                                <th class="text-right"><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </section>

            @if ($canManage)
                <section class="pr-panel am-activity" id="am-activity" aria-labelledby="act-title">
                    <div class="pr-panel-head">
                        <div>
                            <h2 class="pr-panel-title" id="act-title">Riwayat aktivitas</h2>
                            <span class="pr-panel-sub">Siapa mengajukan, mengecek, menyetujui, atau menandai absensi mobile.</span>
                        </div>
                    </div>
                    <div class="pr-table-wrap">
                        <table class="table" id="activity-table">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Oleh</th>
                                    <th>Aktivitas</th>
                                    <th>Absensi</th>
                                    <th class="text-right"><span class="sr-only">Aksi</span></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </section>
            @endif
        </section>
    </div>

    @if ($canCreate)
        <div class="modal fade" id="modalCreateAttendance" tabindex="-1" role="dialog" aria-labelledby="mc-title">
            <div class="modal-dialog modal-lg" role="document">
                <form class="modal-content" id="formCreateAttendance" novalidate>
                    @csrf
                    <div class="modal-header">
                        <h2 class="modal-title" id="mc-title">Ajukan entri absensi manual</h2>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p class="emp-help" style="margin:0 0 var(--pr-space-sm)">Untuk karyawan yang lupa absen di aplikasi. {{ $canManage ? 'Sebagai HR, Anda bisa langsung memfinalkannya.' : 'Entri masuk sebagai menunggu checker: dicek SPV atau manager, lalu disetujui HR.' }}</p>
                        <div class="emp-fields" style="padding:0">
                            <div class="emp-field is-wide">
                                <label for="create_employee_id">Karyawan <span class="emp-req" aria-hidden="true">*</span></label>
                                <select class="emp-select" id="create_employee_id" name="employee_id" required style="width:100%">
                                    <option value="">Pilih karyawan</option>
                                    @foreach ($employees as $e)<option value="{{ $e->id }}" data-flex="{{ $e->attendance_type === 'location_flexible' ? 1 : 0 }}">{{ $e->employee_name }}{{ $e->employee_pengenal ? ' (' . $e->employee_pengenal . ')' : '' }}</option>@endforeach
                                </select>
                                @if (!$canManage && !$canSpvManager)<span class="emp-help">Anda hanya bisa mengajukan entri untuk diri sendiri.</span>@endif
                            </div>
                            <div class="emp-field">
                                <span class="emp-label">Tipe <span class="emp-req" aria-hidden="true">*</span></span>
                                <div class="tl-choice" role="radiogroup">
                                    <label class="emp-check"><input type="radio" name="type" value="checkin" checked> <strong>Check-in</strong></label>
                                    <label class="emp-check"><input type="radio" name="type" value="checkout"> <strong>Check-out</strong></label>
                                </div>
                            </div>
                            <div class="emp-field">
                                <span class="emp-label">Blok</span>
                                <div class="tl-choice" role="radiogroup">
                                    <label class="emp-check"><input type="radio" name="session" value="1" checked> <strong>Blok 1</strong></label>
                                    <label class="emp-check"><input type="radio" name="session" value="2"> <strong>Blok 2</strong></label>
                                </div>
                                <span class="emp-help">Blok 2 hanya untuk karyawan yang jadwalnya split shift di tanggal itu. Shift biasa selalu blok 1.</span>
                            </div>
                            <div class="emp-field">
                                <label for="create_store_id">Lokasi <span class="emp-req" aria-hidden="true">*</span></label>
                                <select class="emp-select" id="create_store_id" name="store_id" style="width:100%" aria-describedby="create_store_help"></select>
                                <span class="emp-help" id="create_store_help">Pilih karyawan dulu. Hanya lokasi kerja karyawan itu yang bisa dipilih.</span>
                            </div>
                            <div class="emp-field">
                                <label for="create_work_date">Tanggal kerja <span class="emp-req" aria-hidden="true">*</span></label>
                                <input type="date" class="emp-input" id="create_work_date" name="work_date" @if ($entryMin) min="{{ $entryMin }}" @endif max="{{ $entryMax }}" required>
                                @if ($entryMin)<span class="emp-help">Periode berjalan: {{ $periodLabel }}. Periode sebelumnya sudah cut off, hanya HR yang bisa mengisi. Pengecualian: check-out shift malam tanggal 25 masih bisa diajukan pada tanggal 26.</span>@endif
                            </div>
                            <div class="emp-field">
                                <label for="create_logged_at">Jam absen <span class="emp-req" aria-hidden="true">*</span></label>
                                <input type="datetime-local" class="emp-input" id="create_logged_at" name="logged_at" @if ($entryMin) min="{{ $entryMin }}T00:00" max="{{ $entryMax }}T23:59" @endif required>
                                <span class="emp-help">Di tanggal kerja, atau dini hari besoknya untuk shift malam.</span>
                            </div>
                            @if ($canManage)
                                <div class="emp-field">
                                    <label for="create_status">Status <span class="emp-req" aria-hidden="true">*</span></label>
                                    <select class="emp-select" id="create_status" name="status" required><option value="approved_hr">Langsung disetujui HR (final)</option><option value="pending">Menunggu checker</option></select>
                                </div>
                            @endif
                            <div class="emp-field is-wide">
                                <label for="create_flag_reason">Alasan <span class="emp-req" aria-hidden="true">*</span></label>
                                <textarea class="emp-input" id="create_flag_reason" name="flag_reason" rows="2" maxlength="255" required placeholder="Mis. HP mati saat jam masuk, sudah dikonfirmasi SPV"></textarea>
                            </div>
                            <div class="emp-field is-wide">
                                <label for="create_photo">Foto atau surat pendukung</label>
                                <input type="file" class="emp-input" id="create_photo" name="photo_path" accept=".jpg,.jpeg,.png,.webp,.pdf" aria-describedby="create_photo_help">
                                <span class="emp-help" id="create_photo_help">Opsional, tapi membantu checker dan HR menyetujui lebih cepat. Foto (jpg, png, webp, maksimal 10 MB) otomatis dikecilkan sebelum disimpan. PDF maksimal 5 MB.</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="pr-btn" data-dismiss="modal">Batal</button>
                        <button type="submit" id="btnSaveAttendance" class="pr-btn pr-btn-primary">Simpan</button>
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
    @include('partials.datatables-error', ['permission' => 'ManageAttendanceMobile, ManageAttendanceMobileSPVManager atau ViewAttendanceMobile'])
    <script>
        $(function() {
            const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const fmtDate = s => s ? new Date(s + 'T00:00:00').toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' }) : '-';
            const DEF = { from: @json($defaultFrom), to: @json($defaultTo) };
            const STATUS = { pending: ['Menunggu checker', 'is-pending'], approved: ['Menunggu HR', 'is-open'], approved_hr: ['Disetujui HR', 'is-approved'], flagged: ['Ditandai', 'is-rejected'] };
            const CAN_BULK = @json($canManage || $canSpvManager);
            const selected = new Map(); // hash -> level ('checker' | 'hr')
            $('.js-s2').each(function() { $(this).select2({ width: '100%' }); });

            const table = $('#attendance-table').DataTable({
                processing: true, serverSide: true, autoWidth: false,
                dom: 'rt<"pr-dt-bottom"<"pr-dt-left"li>p>',
                pageLength: 25, lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [[4, 'desc']],
                language: {
                    processing: 'Memuat', lengthMenu: 'Tampilkan _MENU_', info: '_START_ sampai _END_ dari _TOTAL_', infoEmpty: 'Tidak ada data',
                    infoFiltered: '', zeroRecords: 'Tidak ada absensi yang cocok dengan filter ini.', emptyTable: 'Belum ada absensi di periode ini.',
                    paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                },
                ajax: {
                    url: '{{ route('attendancemobiles.attendancemobiles') }}',
                    data: d => {
                        d.date_from = $('#filterDateFrom').val(); d.date_to = $('#filterDateTo').val(); d.type = $('#filterType').val(); d.status = $('#filterStatus').val();
                        d.source = $('#filterSource').val(); d.store_id = $('#filterStore').val(); d.company_id = $('#filterCompany').val();
                        d.department_id = $('#filterDepartment').val(); d.employee_id = $('#filterEmployee').val();
                    },
                },
                columns: [
                    { data: 'approve_level', orderable: false, searchable: false, className: 'am-col-select', render: (d, t, r) => {
                        if (!CAN_BULK) return '';
                        if (d) return `<input type="checkbox" class="am-select am-row" data-hash="${esc(r.hash)}" data-level="${esc(d)}" ${selected.has(r.hash) ? 'checked' : ''} aria-label="Pilih absensi ${esc(r.employee_name)}">`;
                        return r.status === 'pending' && r.is_mine ? '<span class="pr-muted" title="Ajuan Anda sendiri dicek orang lain">ajuan Anda</span>' : '';
                    } },
                    { data: 'employee_name', name: 'employee_name', render: (d, t, r) => `<span class="pr-cell-name">${esc(d)}</span><span class="pr-cell-sub">${esc([r.employee_pengenal !== '-' ? r.employee_pengenal : null, r.department_name !== '-' ? r.department_name : null].filter(Boolean).join(', '))}</span>` },
                    { data: 'store_name', name: 'store_name', searchable: false, render: (d, t, r) => `<span class="pr-cell-name">${esc(d)}</span>${r.distance_from_store !== null ? `<span class="pr-cell-sub">${Math.round(r.distance_from_store).toLocaleString('id-ID')} m${r.geofence_radius ? ' dari ' + r.geofence_radius + ' m' : ''}</span>` : ''}` },
                    { data: 'work_date', name: 'attendance_logs.work_date', searchable: false, render: d => `<span class="pr-cell-name">${esc(fmtDate(d))}</span>` },
                    { data: 'logged_at', name: 'attendance_logs.logged_at', searchable: false, render: (d, t, r) => `<span class="am-type ${r.type === 'checkout' ? 'is-out' : ''}">${r.type === 'checkin' ? 'Check-in' : 'Check-out'}${r.is_split_session ? ' · Blok ' + esc(r.session) : ''}</span><span class="am-time">${esc(r.logged_time)}</span>${r.logged_date && r.work_date && r.logged_date !== r.work_date ? '<span class="pr-cell-sub">' + esc(r.logged_full) + '</span>' : ''}` },
                    { data: 'is_within_geofence', orderable: false, searchable: false, render: (d, t, r) => {
                        if (r.is_manual) return '<div class="am-checks"><span class="am-chip is-muted">Entri manual</span></div>';
                        return '<div class="am-checks">'
                            + (r.liveness_passed ? '<span class="am-chip">Wajah cocok</span>' : '<span class="am-chip is-bad">Wajah gagal</span>')
                            + (r.is_mock_location ? '<span class="am-chip is-bad">GPS palsu</span>' : '<span class="am-chip">GPS asli</span>')
                            + (r.is_offsite ? '<span class="am-chip is-bad">Di luar lokasi</span>' : (r.store_name === 'Work From Anywhere' ? '<span class="am-chip is-muted">Bebas lokasi</span>' : (r.is_within_geofence ? '<span class="am-chip">Dalam radius</span>' : '<span class="am-chip is-bad">Luar radius</span>')))
                            + (r.attendance_mode_label ? `<span class="am-chip is-muted">${esc(r.attendance_mode_label)}</span>` : '')
                            + '</div>';
                    } },
                    { data: 'status', name: 'attendance_logs.status', searchable: false, render: (d, t, r) => { const [l, c] = STATUS[d] ?? [d, 'is-draft']; return `<span class="status-badge ${c}">${esc(l)}</span>${r.submitted_by ? `<span class="pr-cell-sub">Diajukan ${esc(r.submitted_by)}</span>` : ''}${r.flag_reason ? `<span class="pr-cell-sub" title="${esc(r.flag_reason)}">${esc(String(r.flag_reason).slice(0, 48))}${String(r.flag_reason).length > 48 ? '…' : ''}</span>` : ''}`; } },
                    { data: 'show_url', orderable: false, searchable: false, className: 'text-right', render: (d, t, r) => `<a href="${esc(d)}" class="pr-btn pr-btn-sm" aria-label="Detail absensi ${esc(r.employee_name)}">Detail</a>` },
                ],
                drawCallback: function(settings) {
                    const j = settings.json; if (!j) return;
                    const n = v => Number(v || 0).toLocaleString('id-ID'), s = j.stats || {};
                    $('#am-count').text(`${n(j.recordsFiltered)} log`);
                    $('#st-pending').text(n(s.pending)); $('#st-approved').text(n(s.approved)); $('#st-approved-hr').text(n(s.approved_hr)); $('#st-flagged').text(n(s.flagged));
                    $('#st-in').text(`${n(s.checkin)} / ${n(s.checkout)}`); $('#st-emp').text(`check-in / check-out dari ${n(s.employees)} karyawan`);
                    $('#st-manual').text(n(s.manual_entries));
                    const mePending = @json($canSpvManager) && s.pending_checkable > 0, meApproved = @json($canManage) && s.approved > 0;
                    $('#pending-box').toggleClass('is-warn', mePending).find('[data-status]').prop('hidden', !(s.pending > 0));
                    $('#approved-box').toggleClass('is-warn', meApproved).find('[data-status]').prop('hidden', !(s.approved > 0));
                    $('#flag-box').toggleClass('is-warn', s.flagged > 0);
                    syncSelectAll();
                },
            });

            let ts; $('#am-search').on('input', function() { clearTimeout(ts); const v = this.value; ts = setTimeout(() => table.search(v).draw(), 400); });
            $('#filterType, #filterStatus, #filterSource, #filterStore, #filterCompany, #filterDepartment, #filterEmployee').on('change', () => table.ajax.reload());
            $('#btnFilter').on('click', () => table.ajax.reload());
            $('.tl-stats [data-status]').on('click', function() { $('#filterStatus').val(this.dataset.status); table.ajax.reload(); document.getElementById('am-title').scrollIntoView({ behavior: 'smooth' }); });

            // ── Persetujuan massal ──
            function renderBulk() {
                const levels = [...selected.values()];
                const checker = levels.filter(l => l === 'checker').length, hr = levels.filter(l => l === 'hr').length;
                $('#am-bulk').prop('hidden', selected.size === 0);
                $('#am-bulk-count').text(`${selected.size} dipilih`);
                $('#btnBulkChecker').prop('hidden', !checker).text(`Setujui ${checker} sebagai checker`);
                $('#btnBulkHr').prop('hidden', !hr).text(`Setujui HR ${hr}`);
            }
            function syncSelectAll() {
                const boxes = $('#attendance-table .am-row');
                $('#am-select-all').prop('checked', boxes.length > 0 && boxes.filter(':checked').length === boxes.length).prop('disabled', boxes.length === 0);
            }
            $('#attendance-table').on('change', '.am-row', function() {
                this.checked ? selected.set(this.dataset.hash, this.dataset.level) : selected.delete(this.dataset.hash);
                renderBulk(); syncSelectAll();
            });
            $('#am-select-all').on('change', function() {
                const on = this.checked;
                $('#attendance-table .am-row').each(function() { this.checked = on; on ? selected.set(this.dataset.hash, this.dataset.level) : selected.delete(this.dataset.hash); });
                renderBulk();
            });
            $('#btnBulkClear').on('click', () => { selected.clear(); $('#attendance-table .am-row, #am-select-all').prop('checked', false); renderBulk(); });
            // Filter berubah: pilihan lama dibuang supaya tidak menyetujui baris yang sudah tidak terlihat
            table.on('preXhr.dt', (e, settings, data) => { if (data.start === 0 && settings.iDraw > 0 && settings._bulkKeep !== true) { selected.clear(); renderBulk(); } });

            function bulkApprove(level) {
                const hashes = [...selected].filter(([, l]) => l === level).map(([h]) => h);
                if (!hashes.length) return;
                const isHr = level === 'hr';
                Swal.fire({
                    icon: 'question',
                    title: isHr ? `Setujui HR ${hashes.length} absensi?` : `Setujui ${hashes.length} absensi sebagai checker?`,
                    text: isHr ? 'Status menjadi Disetujui HR (final).' : 'Status menjadi Menunggu HR dan diteruskan ke HR.',
                    showCancelButton: true, confirmButtonText: 'Setujui', cancelButtonText: 'Batal', confirmButtonColor: '#4f46e5',
                    showLoaderOnConfirm: true,
                    preConfirm: () => $.ajax({ url: '{{ route('attendancemobile.bulk-approve') }}', method: 'POST', data: { level, hashes, _token: '{{ csrf_token() }}' } })
                        .catch(x => Swal.showValidationMessage(x.responseJSON?.errors ? Object.values(x.responseJSON.errors).flat().join(' ') : (x.responseJSON?.message ?? 'Terjadi kesalahan.'))),
                    allowOutsideClick: () => !Swal.isLoading(),
                }).then(r => {
                    if (!r.isConfirmed || !r.value) return;
                    hashes.forEach(h => selected.delete(h)); renderBulk();
                    Swal.fire({ icon: r.value.approved ? 'success' : 'info', title: r.value.approved ? 'Disetujui' : 'Tidak ada yang disetujui', text: r.value.message });
                    table.ajax.reload(null, false);
                    if (activityTable) activityTable.ajax.reload(null, false);
                });
            }
            $('#btnBulkChecker').on('click', () => bulkApprove('checker'));
            $('#btnBulkHr').on('click', () => bulkApprove('hr'));

            // ── Riwayat aktivitas (HR) ──
            let activityTable = null;
            // Tampil langsung di bawah tabel absensi (hanya HR); tombol di header cukup menggulir ke panel
            if (document.getElementById('activity-table')) {
                activityTable = $('#activity-table').DataTable({
                    processing: true, serverSide: true, autoWidth: false, searching: true,
                    dom: '<"pr-dt-top"f>rt<"pr-dt-bottom"<"pr-dt-left"li>p>',
                    pageLength: 25, order: [[0, 'desc']],
                    language: { processing: 'Memuat', search: 'Cari', lengthMenu: 'Tampilkan _MENU_', info: '_START_ sampai _END_ dari _TOTAL_', infoEmpty: 'Tidak ada data', infoFiltered: '', zeroRecords: 'Tidak ada aktivitas yang cocok.', emptyTable: 'Belum ada aktivitas.', paginate: { previous: 'Sebelumnya', next: 'Berikutnya' } },
                    ajax: { url: '{{ route('attendancemobile.activities') }}' },
                    columns: [
                        { data: 'time', name: 'time', searchable: false, render: d => `<span class="pr-cell-name">${esc(d)}</span>` },
                        { data: 'causer_name', orderable: false, searchable: false, render: d => esc(d) },
                        { data: 'description', orderable: false, searchable: false, render: (d, t, r) => `<span class="pr-cell-name">${esc(d)}</span>${r.status_from || r.status_to ? `<span class="pr-cell-sub">${esc(r.status_from ?? 'baru')} ke ${esc(r.status_to ?? '-')}</span>` : ''}${r.note ? `<span class="pr-cell-sub">${esc(r.note)}</span>` : ''}` },
                        { data: 'employee_name', orderable: false, searchable: false, render: (d, t, r) => `<span class="pr-cell-name">${esc(d)}</span><span class="pr-cell-sub">${esc([r.type === 'checkin' ? 'Check-in' : r.type === 'checkout' ? 'Check-out' : null, r.work_date].filter(Boolean).join(', '))}</span>` },
                        { data: 'show_url', orderable: false, searchable: false, className: 'text-right', render: d => d ? `<a href="${esc(d)}" class="pr-btn pr-btn-sm">Detail</a>` : '' },
                    ],
                });
            }
            $('#btnActivity').on('click', () => document.getElementById('am-activity')?.scrollIntoView({ behavior: 'smooth' }));
            $('#btnReset').on('click', () => {
                selected.clear(); renderBulk();
                $('#filterDateFrom').val(DEF.from); $('#filterDateTo').val(DEF.to); $('#filterType, #filterStatus, #filterSource').val('');
                $('#filterStore, #filterCompany, #filterDepartment, #filterEmployee').val('').trigger('change.select2'); $('#am-search').val(''); table.search('').ajax.reload();
            });

            const $m = $('#modalCreateAttendance');
            // Lokasi kerja tiap karyawan; pilihan lokasi di form mengikuti karyawan yang dipilih
            const EMP_STORES = @json($employeeStores ?? []);
            const STORE_NAMES = @json(($entryStores ?? collect())->pluck('name', 'id'));
            function fillStores() {
                const $emp = $('#create_employee_id'), $st = $('#create_store_id'), id = $emp.val();
                const flex = $emp.find('option:selected').data('flex') == 1;
                const ids = (EMP_STORES[id] || []).filter(s => STORE_NAMES[s]);
                $st.empty();
                if (!id) {
                    $st.append(new Option('Pilih karyawan dulu', '', true, true)).prop('disabled', true);
                } else {
                    $st.prop('disabled', false);
                    if (flex) $st.append(new Option('Work From Anywhere (karyawan bebas lokasi)', '', false, false));
                    else if (ids.length !== 1) $st.append(new Option('Pilih lokasi', '', true, true));
                    ids.forEach(s => $st.append(new Option(STORE_NAMES[s], s, ids.length === 1, ids.length === 1)));
                    if (!flex && !ids.length) $st.empty().append(new Option('Karyawan ini belum punya lokasi kerja', '', true, true)).prop('disabled', true);
                }
                $('#create_store_help').text(!id ? 'Pilih karyawan dulu. Hanya lokasi kerja karyawan itu yang bisa dipilih.'
                    : flex ? 'Karyawan bebas lokasi boleh tanpa lokasi (Work From Anywhere).'
                    : ids.length ? 'Hanya lokasi kerja karyawan ini yang bisa dipilih.' : 'Atur lokasi kerja karyawan ini dulu di data karyawan.');
                $st.trigger('change.select2');
            }
            $('#create_employee_id').on('change', fillStores);
            $m.on('shown.bs.modal', function() {
                $('#create_employee_id, #create_store_id').each(function() {
                    if ($(this).hasClass('select2-hidden-accessible')) $(this).select2('destroy');
                    $(this).select2({ width: '100%', dropdownParent: $m });
                });
            });
            $m.on('show.bs.modal', () => {
                $('#formCreateAttendance')[0].reset();
                // Pengaju (View) hanya punya dirinya sendiri di daftar: langsung terpilih
                const only = $('#create_employee_id option[value!=""]');
                $('#create_employee_id').val(only.length === 1 ? only.val() : null).trigger('change');
            });
            $('#create_work_date').on('change', function() { if (this.value && !$('#create_logged_at').val()) $('#create_logged_at').val(this.value + 'T' + ($('input[name=type]:checked').val() === 'checkout' ? '17:00' : '08:00')); });

            $('#formCreateAttendance').on('submit', function(e) {
                e.preventDefault();
                const f = this, miss = [];
                if (!$('#create_employee_id').val()) miss.push('karyawan');
                if (!$('#create_work_date').val()) miss.push('tanggal kerja');
                if (!$('#create_logged_at').val()) miss.push('jam absen');
                // Cut off tanggal 25 untuk selain HR (dicek ulang di server)
                const PMIN = @json($entryMin), PMAX = @json($entryMax);
                if (PMIN && !miss.length) {
                    const wd = $('#create_work_date').val(), la = $('#create_logged_at').val().slice(0, 10);
                    // Check-out shift malam tanggal 25 diputuskan server (cek roster shift malam)
                    const nightCheckout = $('input[name=type]:checked').val() === 'checkout' && wd === PMIN && la > wd;
                    if (!nightCheckout && (wd < PMIN || wd > PMAX || la < PMIN || la > PMAX)) {
                        return Swal.fire({ icon: 'error', title: 'Di luar periode', text: @json('Entri manual hanya bisa untuk periode berjalan, ' . ($periodLabel ?? '') . '. Periode sebelumnya sudah cut off, hubungi HR.') });
                    }
                }
                const flex = $('#create_employee_id option:selected').data('flex') == 1;
                if ($('#create_employee_id').val() && !flex && !$('#create_store_id').val()) miss.push('lokasi');
                if (($('#create_flag_reason').val() || '').trim().length < 5) miss.push('alasan (minimal 5 karakter)');
                if (miss.length) return Swal.fire({ icon: 'error', title: 'Belum lengkap', text: 'Isi dulu: ' + miss.join(', ') + '.' });
                const file = $('#create_photo')[0].files[0];
                if (file) {
                    const isPdf = /\.pdf$/i.test(file.name);
                    if (file.size > (isPdf ? 5 : 10) * 1024 * 1024) return Swal.fire({ icon: 'error', title: 'File terlalu besar', text: isPdf ? 'PDF maksimal 5 MB.' : 'Foto maksimal 10 MB.' });
                }
                const name = $('#create_employee_id option:selected').text(), type = $('input[name=type]:checked').val() === 'checkin' ? 'check-in' : 'check-out';
                Swal.fire({ icon: 'question', title: 'Simpan entri manual?', text: `${name}, ${type} ${$('#create_logged_at').val().replace('T', ' ')}.`, showCancelButton: true, confirmButtonText: 'Simpan', cancelButtonText: 'Batal', confirmButtonColor: '#4f46e5' })
                    .then(r => {
                        if (!r.isConfirmed) return;
                        const $b = $('#btnSaveAttendance').prop('disabled', true).text('Menyimpan');
                        $.ajax({ url: '{{ route('attendancemobile.store') }}', method: 'POST', data: new FormData(f), processData: false, contentType: false })
                            .done(res => { $m.modal('hide'); Swal.fire({ icon: 'success', title: 'Tersimpan', text: res.message }); table.ajax.reload(null, false); })
                            .fail(x => Swal.fire({ icon: 'error', title: 'Tidak bisa disimpan', text: x.responseJSON?.errors ? Object.values(x.responseJSON.errors).flat().join('\n') : (x.responseJSON?.message ?? 'Terjadi kesalahan.') }))
                            .always(() => $b.prop('disabled', false).text('Simpan'));
                    });
            });
        });
    </script>
@endpush
