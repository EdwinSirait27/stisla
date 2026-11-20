@extends('layouts.app')
@section('title', 'Detail klarifikasi')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand · redesign: full
         * paper: oklch(98.4% 0.004 265) · accent: oklch(61% 0.17 276) · display: Nunito · body: Nunito
         * nav: kept (Stisla shell) · signature: machine scan vs clarification side by side with the approve/reject decision on top · states: draft for SPV, approved_spv for HR, rejected, no photo
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
    $statusMap = [
        'draft' => ['Draft, menunggu SPV', 'is-pending'], 'approved_spv' => ['Disetujui SPV, menunggu HR', 'is-open'],
        'rejected_spv' => ['Ditolak SPV', 'is-rejected'], 'approved_hr' => ['Disetujui HR', 'is-approved'], 'rejected_hr' => ['Ditolak HR', 'is-rejected'],
    ];
    $attUrl = function ($path) {
        try { return \Illuminate\Support\Facades\Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(30)); } catch (\Throwable $e) { return null; }
    };
    $dateLabel = \Carbon\Carbon::parse($scanDate)->locale('id')->translatedFormat('l, d F Y');
    $roster = $raw['roster'] ?? null;
    $rosterLabel = $roster ? ($roster['day_type'] === 'Work' ? trim(($roster['shift'] ?? 'Kerja') . ' ' . ($roster['time'] ?? '')) : $roster['day_type']) : 'Belum ada roster';
@endphp
    @php
        $status = $data?->status;
        $fields = [1 => 'Jam masuk', 2 => 'Jam pulang'];
    @endphp
    <div class="main-content pr">
        <section class="section">
            <nav aria-label="Breadcrumb"><ol class="pr-crumbs"><li><a href="{{ route('pages.Fingerprints') }}">Absensi fingerprint</a></li><li aria-current="page">Detail klarifikasi</li></ol></nav>
            <header class="pr-head">
                <div>
                    <h1 class="pr-title">{{ $info['employee_name'] }}</h1>
                    <p class="pr-lede">{{ $dateLabel }}. {{ $isEdited ? 'Klarifikasi jam absensi dibandingkan dengan scan mesin.' : 'Belum ada klarifikasi untuk tanggal ini, hanya scan mesin.' }}</p>
                </div>
                <div class="pr-actions">
                    @if ($status && isset($statusMap[$status]))<span class="status-badge {{ $statusMap[$status][1] }}">{{ $statusMap[$status][0] }}</span>@endif
                    @if ($editUrl)<a href="{{ $editUrl }}" class="pr-btn"><i class="fas fa-pen" aria-hidden="true"></i> Ubah</a>@endif
                </div>
            </header>

            @if (in_array($status, ['rejected_spv', 'rejected_hr'], true) && $data->rejection_note)
                <p class="fp-note" role="note"><strong>Alasan ditolak:</strong> {{ $data->rejection_note }}</p>
            @endif

            @if ($canSpvAct || $canHrAct)
                <section class="pr-panel" aria-labelledby="act-title">
                    <div class="pr-panel-head">
                        <div>
                            <h2 class="pr-panel-title" id="act-title">{{ $canHrAct ? 'Keputusan HR' : 'Keputusan Anda' }}</h2>
                            <span class="pr-panel-sub">{{ $canHrAct ? 'Setelah disetujui, jam klarifikasi dipakai saat rekap absensi.' : 'Setelah disetujui, klarifikasi diteruskan ke HR.' }}</span>
                        </div>
                        <div class="pr-actions">
                            <form method="POST" action="{{ route('fingerprints.updateStatus', $data->id) }}" class="js-status-form" data-reject="1">
                                @csrf
                                <input type="hidden" name="status" value="{{ $canHrAct ? 'rejected_hr' : 'rejected_spv' }}">
                                <input type="hidden" name="rejection_note" value="">
                                <button type="submit" class="pr-btn pr-btn-danger">Tolak</button>
                            </form>
                            <form method="POST" action="{{ route('fingerprints.updateStatus', $data->id) }}" class="js-status-form">
                                @csrf
                                <input type="hidden" name="status" value="{{ $canHrAct ? 'approved_hr' : 'approved_spv' }}">
                                <button type="submit" class="pr-btn pr-btn-primary">Setujui</button>
                            </form>
                        </div>
                    </div>
                </section>
            @endif

            <section class="pr-panel" aria-label="Data karyawan dan jadwal">
                <dl class="fp-meta">
                    <div><dt>PIN, NIP</dt><dd>{{ $pin }}, {{ $info['employee_pengenal'] }}</dd></div>
                    <div><dt>Lokasi</dt><dd>{{ $data?->store_name ?: $info['store_name'] }}</dd></div>
                    <div><dt>Jabatan</dt><dd>{{ $data?->position_name ?: $info['position_name'] }}</dd></div>
                    <div><dt>Status karyawan</dt><dd>{{ $info['status_employee'] }}</dd></div>
                    <div><dt>Jadwal</dt><dd>{{ $rosterLabel }}</dd></div>
                </dl>
                <div class="fp-compare">
                    <div>
                        <h2>Scan mesin</h2>
                        <dl class="fp-kv">
                            @foreach ($fields as $i => $label)
                                <dt>{{ $label }}</dt>
                                <dd>{{ $raw["in_$i"] ?? 'tidak ada scan' }}@if ($raw["device_$i"])<small>{{ $raw["device_$i"] }}</small>@endif</dd>
                            @endforeach
                        </dl>
                        @if (count($raw['all']) > 2)
                            <p class="emp-help" style="margin:var(--pr-space-sm) 0 4px">Semua scan hari itu:</p>
                            <ul class="fp-scans">@foreach ($raw['all'] as $sc)<li><b>{{ $sc['time'] }}</b><span>{{ $sc['mode'] }}, {{ $sc['device'] }}</span></li>@endforeach</ul>
                        @endif
                    </div>
                    <div class="is-new">
                        <h2>Klarifikasi</h2>
                        @if ($isEdited)
                            <dl class="fp-kv">
                                @foreach ($fields as $i => $label)
                                    @php $changed = $data->{"in_$i"} && substr((string) $data->{"in_$i"}, 0, 8) !== (string) ($raw["in_$i"] ?? ''); @endphp
                                    <dt>{{ $label }}</dt>
                                    <dd class="{{ $changed ? 'is-change' : '' }}">{{ $data->{"in_$i"} ?? '-' }}{{ $changed ? ' (diubah)' : '' }}@if ($data->{"device_$i"})<small>{{ $data->{"device_$i"} }}</small>@endif</dd>
                                @endforeach
                                <dt>Durasi</dt><dd>{{ $data->duration ? preg_replace(['/(\d+) hours?/', '/(\d+) minutes?/'], ['$1 jam', '$1 menit'], $data->duration) : '-' }}</dd>
                                <dt>Alasan</dt><dd style="font-weight:500">{{ $data->notes ?: '-' }}</dd>
                                @if ($approver)<dt>{{ str_starts_with((string) $status, 'rejected') ? 'Ditolak oleh' : 'Disetujui oleh' }}</dt><dd>{{ $approver }}<small>{{ $data->approved_at ? \Carbon\Carbon::parse($data->approved_at)->timezone('Asia/Makassar')->format('d M Y H:i') : '' }}</small></dd>@endif
                                <dt>Dikirim</dt><dd>{{ $data->created_at?->timezone('Asia/Makassar')->format('d M Y H:i') }}@if ($data->updated_at && $data->updated_at != $data->created_at)<small>diubah {{ $data->updated_at->timezone('Asia/Makassar')->format('d M Y H:i') }}</small>@endif</dd>
                            </dl>
                        @else
                            <p class="emp-help">Belum ada klarifikasi.</p>
                        @endif
                    </div>
                </div>
            </section>

            @if ($isEdited)
                <section class="pr-panel" aria-labelledby="att-title">
                    <div class="pr-panel-head"><div><h2 class="pr-panel-title" id="att-title">Foto bukti</h2><span class="pr-panel-sub">{{ $data->attachments->count() }} foto</span></div></div>
                    @if ($data->attachments->count())
                        <div class="fp-gallery">
                            @foreach ($data->attachments as $att)
                                @php $u = $att->attachment ? $attUrl($att->attachment) : null; @endphp
                                <figure class="fp-thumb">
                                    @if ($u)<button type="button" class="fp-open js-preview" data-url="{{ $u }}" data-title="Bukti {{ $loop->iteration }}" aria-label="Perbesar bukti {{ $loop->iteration }}"><img src="{{ $u }}" alt="Bukti {{ $loop->iteration }}" loading="lazy"></button>@endif
                                    <figcaption>Bukti {{ $loop->iteration }}@unless ($u) (gagal dimuat)@endunless</figcaption>
                                </figure>
                            @endforeach
                        </div>
                    @else
                        <p class="pr-empty-note" style="padding:var(--pr-space-md) var(--pr-space-lg)">Tidak ada foto bukti. {{ ($canSpvAct || $canHrAct) ? 'Pertimbangkan sebelum menyetujui.' : '' }}</p>
                    @endif
                </section>

                @if ($history->count())
                    <section class="pr-panel" aria-labelledby="hist-title">
                        <div class="pr-panel-head"><div><h2 class="pr-panel-title" id="hist-title">Riwayat</h2></div></div>
                        <ol class="fp-timeline">
                            @foreach ($history as $h)
                                <li>{{ $h->description }}<small>{{ $h->causer?->employee?->employee_name ?? $h->causer?->username ?? 'Sistem' }}, {{ $h->created_at->timezone('Asia/Makassar')->format('d M Y H:i') }}</small></li>
                            @endforeach
                        </ol>
                    </section>
                @endif
            @endif
        </section>
    </div>

    <div class="modal fade" id="attachmentModal" tabindex="-1" role="dialog" aria-labelledby="attachmentModalTitle">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="attachmentModalTitle">Bukti</h2>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body text-center"><img id="attachmentModalImg" src="" class="img-fluid" alt=""></div>
                <div class="modal-footer">
                    <a id="attachmentModalDownload" href="#" target="_blank" rel="noopener" class="pr-btn">Buka di tab baru</a>
                    <button type="button" class="pr-btn" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            $(document).on('click', '.js-preview', function() {
                $('#attachmentModalTitle').text(this.dataset.title); $('#attachmentModalImg').attr({ src: this.dataset.url, alt: this.dataset.title });
                $('#attachmentModalDownload').attr('href', this.dataset.url); $('#attachmentModal').modal('show');
            });
            $('.js-status-form').on('submit', function(e) {
                if (this.dataset.ok) return;
                e.preventDefault();
                const form = this, reject = !!form.dataset.reject;
                Swal.fire({
                    icon: reject ? 'warning' : 'question',
                    title: reject ? 'Tolak klarifikasi ini?' : 'Setujui klarifikasi ini?',
                    text: reject ? 'Karyawan bisa memperbaiki dan mengirim ulang.' : undefined,
                    input: reject ? 'textarea' : undefined, inputPlaceholder: 'Alasan penolakan, wajib diisi',
                    inputValidator: reject ? v => (!v || v.trim().length < 5) ? 'Tulis alasan minimal 5 karakter.' : undefined : undefined,
                    showCancelButton: true, confirmButtonText: reject ? 'Tolak' : 'Setujui', cancelButtonText: 'Batal',
                    confirmButtonColor: reject ? '#dc2626' : '#4f46e5',
                }).then(r => {
                    if (!r.isConfirmed) return;
                    if (reject) form.querySelector('[name=rejection_note]').value = r.value;
                    form.dataset.ok = '1'; form.querySelector('button').disabled = true; form.submit();
                });
            });
            @if (session('success'))
                Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('success')), timer: 2500 });
            @endif
            @if (session('error') || $errors->any())
                Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error') ?? $errors->first()) });
            @endif
        });
    </script>
@endpush
