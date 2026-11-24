@extends('layouts.app')
@section('title', 'Pengaturan TOIL')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand (shared pr-* tokens) · scope: app
         * signature: a worked example beside the form (one overtime today: when its TOIL and Cash balance lapse, how many leave days a balance buys, when mobile check-in opens) that redraws as each number changes · states: defaults (never saved), saved, validation errors, no change, empty history
         * posture: app · contrast: shared tokens · v2.0.0 */
        /* Hallmark · pre-emit critique: P4 H4 E4 S4 R4 V4 · weakest: E, eight bare number inputs; answered by the live worked example and the change history */
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
        /* ── Jenis cuti: form ── */
        .lt-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 320px); gap: var(--pr-space-lg); align-items: start; }
        .lt-aside { position: sticky; top: 90px; margin-bottom: 0; }
        .lt-callout { margin: 0; padding: var(--pr-space-sm) var(--pr-space-md); border-radius: var(--pr-radius); background: var(--pr-accent-soft); font-size: var(--pr-text-sm); line-height: 1.55; color: var(--pr-ink); }
        .lt-callout code, .emp-help code, .emp-savebar code { font-size: 0.92em; color: var(--pr-accent-deep); background: none; padding: 0; }
        .lt-suffix { display: flex; align-items: center; gap: var(--pr-space-xs); }
        .lt-suffix .emp-input { max-width: 9rem; }
        .lt-suffix span { font-size: var(--pr-text-sm); color: var(--pr-ink-2); white-space: nowrap; }
        .lt-seg { display: inline-flex; flex-wrap: wrap; gap: 0; border: 1px solid var(--pr-rule); border-radius: var(--pr-radius); overflow: hidden; }
        .lt-seg label { margin: 0; cursor: pointer; }
        .lt-seg label + label { border-left: 1px solid var(--pr-rule); }
        .lt-seg input { position: absolute; opacity: 0; pointer-events: none; }
        .lt-seg span { display: block; padding: 8px 14px; font-size: var(--pr-text-sm); font-weight: 600; color: var(--pr-ink-2); background: var(--pr-panel); transition: background-color 0.15s, color 0.15s; }
        .lt-seg input:checked + span { background: var(--pr-accent-soft); color: var(--pr-accent-deep); }
        .lt-seg label:hover span { color: var(--pr-ink); }
        .lt-seg input:focus-visible + span { outline: 2px solid var(--pr-focus); outline-offset: -2px; }
        .lt-dur { margin-top: var(--pr-space-sm); }
        .lt-dur[hidden], #special-box[hidden] { display: none; }
        .lt-chips { display: flex; flex-wrap: wrap; gap: var(--pr-space-xs); }
        .lt-chip { padding: 6px 12px; }
        .lt-eligible { padding: var(--pr-space-md) var(--pr-space-lg) var(--pr-space-sm); }
        .lt-eligible strong { display: block; font-size: 2.25rem; line-height: 1.1; font-weight: 800; font-variant-numeric: tabular-nums; color: var(--pr-ink); }
        .lt-eligible span { font-size: var(--pr-text-sm); color: var(--pr-ink-2); }
        .lt-meter { height: 8px; margin-top: var(--pr-space-sm); border-radius: var(--pr-pill); background: var(--pr-paper-2); overflow: hidden; }
        .lt-meter i { display: block; height: 100%; border-radius: var(--pr-pill); background: var(--pr-accent); transform-origin: left; transition: transform 0.25s ease; }
        .lt-rules { margin: 0; padding: var(--pr-space-sm) var(--pr-space-lg) var(--pr-space-md) calc(var(--pr-space-lg) + 1em); border-top: 1px solid var(--pr-rule); font-size: var(--pr-text-sm); line-height: 1.55; color: var(--pr-ink); }
        .lt-rules li + li { margin-top: 4px; }
        .lt-usage { display: grid; grid-template-columns: 1fr 1fr; margin: 0; border-top: 1px solid var(--pr-rule); }
        .lt-usage > div { padding: var(--pr-space-sm) var(--pr-space-lg); }
        .lt-usage > div + div { border-left: 1px solid var(--pr-rule); }
        .lt-usage dt { font-size: var(--pr-text-xs); font-weight: 600; color: var(--pr-ink-2); }
        .lt-usage dd { margin: 0; font-weight: 800; font-variant-numeric: tabular-nums; color: var(--pr-ink); }
        .emp-field > label.emp-check { display: flex; margin-bottom: 0; font-weight: 400; }
        .lt-rule-list li, .lt-rules li { line-height: 1.5; }
        @media (max-width: 40rem) { .emp-savebar > span { display: none; } .emp-savebar { justify-content: flex-end; } }
        @media (max-width: 64rem) { .lt-grid { grid-template-columns: minmax(0, 1fr); } .lt-aside { position: static; order: -1; } }
        @media (prefers-reduced-motion: reduce) { .lt-meter i, .lt-seg span { transition: none; } }

        .po-names { margin: 0; padding: var(--pr-space-sm) var(--pr-space-lg) var(--pr-space-md); border-top: 1px solid var(--pr-rule); font-size: var(--pr-text-xs); line-height: 1.55; color: var(--pr-ink-2); }
        .tl-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 170px), 1fr)); margin: 0; }
        .tl-stats > div { padding: var(--pr-space-md) var(--pr-space-lg); border-right: 1px solid var(--pr-rule); border-bottom: 1px solid var(--pr-rule); }
        .tl-stats > div.is-warn { background: var(--pr-warn-soft); }
        .tl-stats dt { font-size: var(--pr-text-sm); font-weight: 600; color: var(--pr-ink-2); }
        .tl-stats dd { margin: 2px 0 0; font-size: var(--pr-text-xl); font-weight: 800; font-variant-numeric: tabular-nums; color: var(--pr-ink); }
        .tl-stats dd small { font-size: var(--pr-text-sm); font-weight: 600; color: var(--pr-muted); }
        .tl-stats > div > span { display: block; font-size: var(--pr-text-xs); line-height: 1.45; color: var(--pr-ink-2); }

        .po-names { margin: 0; padding: var(--pr-space-sm) var(--pr-space-lg) var(--pr-space-md); border-top: 1px solid var(--pr-rule); font-size: var(--pr-text-xs); line-height: 1.55; color: var(--pr-ink-2); }
        .po-flags { display: flex; flex-wrap: wrap; gap: 6px; }
        .po-flags button { padding: 4px 12px; border: 1px solid var(--pr-rule); border-radius: var(--pr-pill); background: var(--pr-panel); font: inherit; font-size: var(--pr-text-sm); font-weight: 600; color: var(--pr-ink-2); cursor: pointer; transition: background-color 0.15s, color 0.15s, border-color 0.15s; }
        .po-flags button:hover { color: var(--pr-ink); }
        .po-flags button[aria-pressed="true"] { border-color: var(--pr-accent); background: var(--pr-accent-soft); color: var(--pr-accent-deep); }
        .po-flags button:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 2px; }
        .po-flags-wrap { padding: var(--pr-space-sm) var(--pr-space-lg); border-bottom: 1px solid var(--pr-rule); }
        @media (prefers-reduced-motion: reduce) { .po-remove, .po-flags button { transition: none; } }
        .po-names { margin: 0; padding: var(--pr-space-sm) var(--pr-space-lg) var(--pr-space-md); border-top: 1px solid var(--pr-rule); font-size: var(--pr-text-xs); line-height: 1.55; color: var(--pr-ink-2); }
        .co-missing { margin: 0; padding: var(--pr-space-sm) var(--pr-space-lg); border-top: 1px solid var(--pr-rule); background: var(--pr-warn-soft); font-size: var(--pr-text-sm); color: var(--pr-ink); }
        .gr-code { display: inline-block; margin-left: 4px; padding: 0 7px; border-radius: 6px; background: var(--pr-paper-2); font-size: var(--pr-text-xs); font-weight: 800; color: var(--pr-ink-2); vertical-align: 1px; }

        /* ── Lokasi ── */
        .st-map-wrap { padding: var(--pr-space-md) var(--pr-space-lg) 0; }
        .st-map { height: 360px; border: 1px solid var(--pr-rule); border-radius: var(--pr-radius); background: var(--pr-paper-2); z-index: 0; }
        .st-map:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 2px; }
        .st-addr { max-width: 46ch; }
        #stores-table .pr-cell-sub { white-space: normal; overflow-wrap: anywhere; }
        .st-maplink { color: var(--pr-accent-strong); text-decoration: underline; text-underline-offset: 2px; font-variant-numeric: tabular-nums; }
        .st-maplink:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 2px; }
        #stores-table td { vertical-align: top; }
        .emp-help code { font-size: 0.92em; color: var(--pr-accent-deep); background: none; padding: 0; }
        @media (max-width: 40rem) { .st-map { height: 280px; } }

        /* ── Pengaturan roster ── */
        .rs-flash { margin: 0 0 var(--pr-space-md); padding: var(--pr-space-sm) var(--pr-space-md); border-radius: var(--pr-radius-sm); background: var(--pr-success-soft); color: var(--pr-success); font-size: var(--pr-text-sm); font-weight: 600; }
        .rs-status { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr); gap: var(--pr-space-lg); padding: var(--pr-space-lg); align-items: start; }
        .rs-window { margin: var(--pr-space-sm) 0 var(--pr-space-2xs); font-size: var(--pr-text-2xl); font-weight: 800; color: var(--pr-ink); line-height: 1.15; overflow-wrap: break-word; min-width: 0; }
        .rs-window-sub { margin: 0; color: var(--pr-ink-2); font-size: var(--pr-text-md); }
        .rs-next { margin: var(--pr-space-sm) 0 0; color: var(--pr-muted); font-size: var(--pr-text-sm); }
        .rs-facts { display: grid; gap: var(--pr-space-sm); margin: 0; }
        .rs-facts div { padding: var(--pr-space-sm) var(--pr-space-md); border: 1px solid var(--pr-rule); border-radius: var(--pr-radius-sm); }
        .rs-facts dt { font-size: var(--pr-text-xs); color: var(--pr-muted); font-weight: 600; }
        .rs-facts dd { margin: 2px 0 0; font-size: var(--pr-text-lg); font-weight: 800; color: var(--pr-ink); font-variant-numeric: tabular-nums; }
        .rs-facts dd small { display: block; font-size: var(--pr-text-xs); font-weight: 400; color: var(--pr-muted); }

        .rs-months { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: var(--pr-space-lg); padding: var(--pr-space-md) var(--pr-space-lg) var(--pr-space-lg); }
        .rs-month h3 { margin: 0 0 var(--pr-space-xs); font-size: var(--pr-text-md); font-weight: 700; color: var(--pr-ink); }
        .rs-month p { margin: 0 0 var(--pr-space-sm); font-size: var(--pr-text-xs); color: var(--pr-muted); }
        .rs-cal { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 4px; margin: 0; padding: 0; list-style: none; }
        .rs-cal .rs-wd { font-size: var(--pr-text-xs); color: var(--pr-muted); text-align: center; padding-bottom: 2px; }
        .rs-day { display: flex; align-items: center; justify-content: center; min-height: 34px; border-radius: var(--pr-radius-sm); font-size: var(--pr-text-sm); font-variant-numeric: tabular-nums; color: var(--pr-muted); background: var(--pr-paper-2); }
        .rs-day.is-open { background: var(--pr-accent-soft); color: var(--pr-accent-deep); font-weight: 700; }
        .rs-day.is-today { box-shadow: inset 0 0 0 2px var(--pr-ink); color: var(--pr-ink); }
        .rs-legend { display: flex; flex-wrap: wrap; gap: var(--pr-space-md); padding: 0 var(--pr-space-lg) var(--pr-space-md); margin: 0; list-style: none; font-size: var(--pr-text-xs); color: var(--pr-muted); }
        .rs-legend li { display: flex; align-items: center; gap: var(--pr-space-xs); }
        .rs-swatch { width: 14px; height: 14px; border-radius: 4px; background: var(--pr-paper-2); }
        .rs-swatch.is-open { background: var(--pr-accent-soft); box-shadow: inset 0 0 0 1px var(--pr-accent); }
        .rs-swatch.is-today { background: transparent; box-shadow: inset 0 0 0 2px var(--pr-ink); }

        .rs-table { width: 100%; border-collapse: collapse; font-size: var(--pr-text-sm); }
        .rs-table th { text-align: left; font-weight: 600; color: var(--pr-muted); padding: var(--pr-space-sm); border-bottom: 1px solid var(--pr-rule); }
        .rs-table td { padding: var(--pr-space-sm); border-bottom: 1px solid var(--pr-rule); vertical-align: middle; color: var(--pr-ink); }
        .rs-table tr:last-child td { border-bottom: 0; }
        .rs-empty { padding: var(--pr-space-lg); }
        .rs-empty p { margin: 0 0 var(--pr-space-md); color: var(--pr-ink-2); max-width: 60ch; }

        .rs-days { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: var(--pr-space-md); }
        .rs-sentence { margin: 0; padding: var(--pr-space-sm) var(--pr-space-md); border-radius: var(--pr-radius-sm); background: var(--pr-accent-soft); color: var(--pr-accent-deep); font-weight: 600; }
        .rs-warn { margin: var(--pr-space-xs) 0 0; color: var(--pr-warn); font-size: var(--pr-text-sm); }
        .rs-warn[hidden] { display: none; }

        @media (max-width: 48rem) {
            .rs-status, .rs-months, .rs-days { grid-template-columns: minmax(0, 1fr); }
        }

        /* ── Pengaturan TOIL ── */
        .ts-example { margin: 0; padding: var(--pr-space-md) var(--pr-space-lg); display: grid; gap: var(--pr-space-sm); }
        .ts-example div { padding-bottom: var(--pr-space-sm); border-bottom: 1px solid var(--pr-rule); }
        .ts-example div:last-child { padding-bottom: 0; border-bottom: 0; }
        .ts-example dt { font-size: var(--pr-text-xs); font-weight: 600; color: var(--pr-muted); }
        .ts-example dd { margin: 2px 0 0; font-size: var(--pr-text-lg); font-weight: 800; color: var(--pr-ink); font-variant-numeric: tabular-nums; overflow-wrap: break-word; min-width: 0; }
        .ts-example dd small { display: block; font-size: var(--pr-text-xs); font-weight: 400; color: var(--pr-muted); }
        .ts-fixed { margin: 0; padding: var(--pr-space-sm) var(--pr-space-lg) var(--pr-space-md) calc(var(--pr-space-lg) + 1em); border-top: 1px solid var(--pr-rule); font-size: var(--pr-text-sm); line-height: 1.55; color: var(--pr-ink-2); }
        .ts-fixed li + li { margin-top: 4px; }
        .ts-fixed-title { margin: 0; padding: var(--pr-space-sm) var(--pr-space-lg) 0; font-size: var(--pr-text-sm); font-weight: 700; color: var(--pr-ink); }
        .ts-when { white-space: nowrap; font-variant-numeric: tabular-nums; color: var(--pr-ink-2); }
        .ts-who { color: var(--pr-muted); }
        .ts-flash-none { background: var(--pr-paper-2); color: var(--pr-ink-2); }
        @media (max-width: 64rem) {
            .lt-grid { grid-template-columns: minmax(0, 1fr); }
            .lt-aside { position: static; }
        }
    </style>
@endpush

@section('main')
    @php
        $req = '<span class="emp-req" aria-hidden="true">*</span>';
        $num = fn($v) => \App\Models\ToilSetting::num($v);
        $val = fn($key) => old($key, $num($setting->{$key}));

        // [field, unit, step, min, max, help]
        $panels = [
            ['ts-h-balance', 'Masa berlaku saldo', 'Dihitung dari tanggal lembur. Berlaku untuk lembur yang dibuat atau diubah tanggalnya mulai sekarang; saldo yang sudah ada tetap dengan tanggal hangusnya.', [
                ['toil_expiry_months', 'bulan', 1, 1, 24, 'Setelah lewat, sisa saldo TOIL hangus dan tidak bisa dipakai libur.'],
                ['cash_expiry_months', 'bulan', 1, 1, 24, 'Saldo Cash tidak hangus: tetap dibayar lewat payroll. Tanggal ini hanya batas tampilan saldo aktif.'],
                ['expiring_soon_days', 'hari', 1, 0, 90, 'Di aplikasi mobile, saldo TOIL yang hangus dalam rentang ini ditandai segera hangus. Isi 0 untuk mematikan tanda.'],
            ]],
            ['ts-h-leave', 'Libur TOIL', 'Dipakai saat atasan menukar saldo TOIL bawahan menjadi hari libur.', [
                ['hours_per_day', 'jam', 0.5, 1, 24, 'Saldo yang dipotong untuk setiap hari libur TOIL. Libur TOIL yang sudah disetujui tidak dihitung ulang.'],
                ['max_leave_range_days', 'hari', 1, 1, 366, 'Rentang tanggal terpanjang dalam satu kali pengajuan libur TOIL.'],
            ]],
            ['ts-h-overtime', 'Penugasan lembur', 'Dipakai saat atasan menugaskan lembur dan saat karyawan check-in lembur di aplikasi mobile.', [
                ['min_overtime_hours', 'jam', 0.5, 0.5, 24, 'Lembur lebih pendek dari ini ditolak.'],
                ['max_overtime_hours', 'jam', 0.5, 0.5, 24, 'Lembur lebih panjang dari ini ditolak.'],
                ['checkin_early_minutes', 'menit', 1, 0, 240, 'Check-in lembur di aplikasi mobile paling cepat sekian menit sebelum jam mulai lembur.'],
            ]],
        ];
        $lastBy = $setting->updatedBy?->name;
    @endphp
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <h1 class="pr-title">Pengaturan TOIL</h1>
                    <p class="pr-lede">
                        Angka yang dipakai modul TOIL dan lembur.
                        @if ($saved && $setting->updated_at)
                            Terakhir diubah {{ $setting->updated_at->locale('id')->translatedFormat('j F Y, H:i') }}@if ($lastBy) oleh {{ $lastBy }}@endif.
                        @else
                            Belum pernah diubah: semua angka masih nilai bawaan.
                        @endif
                        Tanda {!! $req !!} wajib diisi.
                    </p>
                </div>
            </header>

            @if (session('success'))
                <p class="rs-flash {{ session('success') === 'Tidak ada yang berubah.' ? 'ts-flash-none' : '' }}" role="status">{{ session('success') }}</p>
            @endif

            <form action="{{ route('toilsetting.update') }}" method="POST" id="ts-form" novalidate>
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="emp-errors" role="alert">
                        <strong>{{ $errors->count() }} isian perlu diperbaiki.</strong>
                        <ul>@foreach ($errors->keys() as $key)<li>{{ $labels[$key] ?? $key }}: {{ $errors->first($key) }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="lt-grid">
                    <div class="emp-sections">
                        @foreach ($panels as [$hid, $title, $sub, $fields])
                            <section class="pr-panel" aria-labelledby="{{ $hid }}">
                                <div class="pr-panel-head">
                                    <div>
                                        <h2 class="pr-panel-title" id="{{ $hid }}">{{ $title }}</h2>
                                        <span class="pr-panel-sub">{{ $sub }}</span>
                                    </div>
                                </div>
                                <div class="emp-fields">
                                    @foreach ($fields as [$key, $unit, $step, $min, $max, $help])
                                        <div class="emp-field">
                                            <label for="{{ $key }}">{{ preg_replace('/ \((bulan|hari|menit)\)$/', '', $labels[$key]) }} {!! $req !!}</label>
                                            <div class="lt-suffix">
                                                <input type="number" inputmode="decimal" class="emp-input @error($key) is-invalid @enderror" id="{{ $key }}" name="{{ $key }}"
                                                    value="{{ $val($key) }}" step="{{ $step }}" min="{{ $min }}" max="{{ $max }}" required
                                                    aria-describedby="{{ $key }}-help @error($key) {{ $key }}-error @enderror">
                                                <span>{{ $unit }}</span>
                                            </div>
                                            <span class="emp-help" id="{{ $key }}-help">{{ $help }} Bawaan: {{ $num(\App\Models\ToilSetting::DEFAULTS[$key]) }} {{ $unit }}.</span>
                                            @error($key)<span class="emp-error" id="{{ $key }}-error">{{ $message }}</span>@enderror
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach

                        <div class="pr-actions" style="justify-content:flex-end">
                            <a href="{{ route('toilsetting.index') }}" class="pr-btn">Batalkan perubahan</a>
                            <button type="submit" class="pr-btn pr-btn-primary">Simpan pengaturan</button>
                        </div>
                    </div>

                    <aside class="pr-panel lt-aside" aria-labelledby="ts-h-example">
                        <div class="pr-panel-head">
                            <div>
                                <h2 class="pr-panel-title" id="ts-h-example">Contoh dengan angka ini</h2>
                                <span class="pr-panel-sub" id="ts-example-sub">Lembur hari ini, berubah saat angka diubah.</span>
                            </div>
                        </div>
                        <dl class="ts-example" aria-live="polite">
                            <div><dt>Saldo TOIL hangus</dt><dd id="ex-toil">–</dd></div>
                            <div><dt>Saldo Cash aktif sampai</dt><dd id="ex-cash">–</dd></div>
                            <div><dt>Saldo 20 jam bisa ditukar</dt><dd id="ex-days">–</dd></div>
                            <div><dt>Lembur jam 18:00 bisa check-in mulai</dt><dd id="ex-checkin">–</dd></div>
                        </dl>
                        <p class="ts-fixed-title">Tidak diatur di sini</p>
                        <ul class="ts-fixed">
                            <li>Satu karyawan hanya satu lembur per tanggal.</li>
                            <li>Karyawan DW selalu Cash dan tidak bisa libur TOIL.</li>
                            <li>Periode penugasan lembur mengikuti periode payroll (26 sampai 25).</li>
                        </ul>
                    </aside>
                </div>
            </form>

            <section class="pr-panel" aria-labelledby="ts-h-history" style="margin-top:var(--pr-space-lg)">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="ts-h-history">Riwayat perubahan</h2>
                        <span class="pr-panel-sub">10 perubahan terakhir.</span>
                    </div>
                </div>
                @if ($history->isEmpty())
                    <div class="rs-empty"><p>Belum ada perubahan. Setiap kali pengaturan disimpan, angka lama dan barunya dicatat di sini dan di Activity Logs.</p></div>
                @else
                    <div style="overflow-x:auto">
                        <table class="rs-table">
                            <thead><tr><th scope="col">Waktu</th><th scope="col">Oleh</th><th scope="col">Perubahan</th></tr></thead>
                            <tbody>
                                @foreach ($history as $log)
                                    @php($old = $log->properties['old'] ?? [])
                                    @php($new = $log->properties['attributes'] ?? [])
                                    <tr>
                                        <td class="ts-when">{{ $log->created_at->locale('id')->translatedFormat('j M Y, H:i') }}</td>
                                        <td class="ts-who">{{ $log->causer?->name ?? 'Sistem' }}</td>
                                        <td>
                                            @foreach ($new as $key => $to)
                                                <div>{{ $labels[$key] ?? $key }}: {{ $num($old[$key] ?? 0) }} menjadi <strong>{{ $num($to) }}</strong></div>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        // Contoh langsung: logika sama dengan ToilSetting::expiryDate() (Carbon addMonths) dan pemotongan saldo per hari
        (function() {
            const $ = id => document.getElementById(id);
            const fmt = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
            const n = id => parseFloat(String($(id).value).replace(',', '.'));
            const addMonths = (d, m) => { const r = new Date(d); r.setMonth(r.getMonth() + m); return r; };
            const num = v => String(Math.round(v * 100) / 100).replace('.', ',');
            const today = new Date(); today.setHours(0, 0, 0, 0);
            const bad = '<small>Isi angka yang valid</small>';

            function render() {
                const toilM = n('toil_expiry_months'), cashM = n('cash_expiry_months'), soon = n('expiring_soon_days');
                const hpd = n('hours_per_day'), early = n('checkin_early_minutes');

                if (toilM >= 1) {
                    const exp = addMonths(today, toilM), warn = new Date(exp); warn.setDate(warn.getDate() - (soon || 0));
                    $('ex-toil').innerHTML = `${fmt.format(exp)}<small>${soon > 0 ? 'Ditandai segera hangus mulai ' + fmt.format(warn) + '.' : 'Tanpa tanda segera hangus.'}</small>`;
                } else $('ex-toil').innerHTML = bad;

                $('ex-cash').innerHTML = cashM >= 1 ? `${fmt.format(addMonths(today, cashM))}<small>Setelah itu tetap dibayar lewat payroll.</small>` : bad;

                if (hpd >= 1) {
                    const days = Math.floor(20 / hpd), rest = 20 - days * hpd;
                    $('ex-days').innerHTML = `${days} hari libur<small>${rest > 0 ? 'Sisa ' + num(rest) + ' jam tetap di saldo.' : 'Tanpa sisa.'}</small>`;
                } else $('ex-days').innerHTML = bad;

                if (early >= 0) {
                    const t = 18 * 60 - early, h = Math.floor(((t % 1440) + 1440) % 1440 / 60), m = ((t % 60) + 60) % 60;
                    $('ex-checkin').innerHTML = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}<small>${early} menit sebelum lembur mulai.</small>`;
                } else $('ex-checkin').innerHTML = bad;
            }

            document.getElementById('ts-form').addEventListener('input', render);
            render();
        })();
    </script>
@endpush
