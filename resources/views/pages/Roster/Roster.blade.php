@extends('layouts.app')
@section('title', 'Roster & Schedule')
@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand · redesign: full
         * paper: oklch(98.4% 0.004 265) · accent: oklch(61% 0.17 276) · display: Nunito · body: Nunito
         * nav: kept (Stisla shell) · signature: how many workdays in the period still have no schedule, per store and per person · states: no store, no employees, view-only, window closed, full
         * axes: light/rounded-sans/chromatic-indigo · posture: app · contrast: pass · v2.0.0 */
        /* Hallmark · pre-emit critique: P4 H4 E4 S4 R3 V4 · weakest: R, modal bodies keep their inline styles because 1,600 lines of JS read them; restyled via shared classes only */
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

        .pr-empty-note {
            margin: 0;
            padding: var(--pr-space-md) var(--pr-space-lg);
            font-size: var(--pr-text-sm);
            color: var(--pr-muted);
        }


        /* ── Roster: tokens khusus tipe hari ── */
        :root {
            --rs-work: oklch(95.5% 0.03 250);
            --rs-work-ink: oklch(42% 0.13 255);
            --rs-off: var(--pr-paper-2);
            --rs-off-ink: var(--pr-ink-2);
            --rs-holiday: var(--pr-warn-soft);
            --rs-holiday-ink: var(--pr-warn);
            --rs-leave: oklch(95.5% 0.035 305);
            --rs-leave-ink: oklch(45% 0.14 305);
            --rs-sick: var(--pr-error-soft);
            --rs-sick-ink: var(--pr-error);
            --rs-toil: oklch(95.5% 0.035 180);
            --rs-toil-ink: oklch(42% 0.08 180);
            --rs-weekend-col: oklch(97.6% 0.006 265);
            --rs-overlay: oklch(27% 0.03 265 / .45);
            --rs-shadow: oklch(27% 0.03 265 / .18);
        }

        /* Head: periode & jendela */
        .rs-period {
            margin: 2px 0 0;
            font-size: var(--pr-text-sm);
            color: var(--pr-ink-2);
        }

        .rs-window {
            display: inline-flex;
            align-items: center;
            gap: var(--pr-space-2xs);
            margin-left: var(--pr-space-xs);
        }

        /* Filter */
        .rs-filter {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: var(--pr-space-sm) var(--pr-space-md);
            padding: var(--pr-space-md) var(--pr-space-lg);
        }

        .rs-field {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .rs-field.is-store {
            flex: 1 1 240px;
            max-width: 340px;
        }

        .rs-field.is-date {
            flex: 0 1 170px;
        }

        .rs-filter-actions {
            display: flex;
            gap: var(--pr-space-xs);
        }

        .rs-field-hint {
            margin-top: 2px;
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
        }

        /* Ringkasan periode */
        .rs-summary .pr-period dd.is-warn-text {
            color: var(--pr-warn);
        }

        /* Legend / filter tipe */
        .legend {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: var(--pr-space-xs);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-bottom: 1px solid var(--pr-rule);
            margin: 0;
        }

        .legend-lead {
            margin-right: var(--pr-space-2xs);
            font-size: var(--pr-text-xs);
            font-weight: 600;
            color: var(--pr-ink-2);
        }

        .legend-item {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin: 0;
            padding: 4px 10px;
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-pill);
            background: var(--pr-panel);
            font-size: var(--pr-text-xs);
            font-weight: 600;
            color: var(--pr-ink-2);
            cursor: pointer;
            user-select: none;
            transition: background-color var(--pr-dur-micro) var(--pr-ease), border-color var(--pr-dur-micro) var(--pr-ease);
        }

        .legend-item:hover {
            background: var(--pr-paper);
        }

        .legend-item input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            width: 1px;
            height: 1px;
        }

        .legend-item:has(input:checked) {
            border-color: var(--pr-accent);
            background: var(--pr-accent-soft);
            color: var(--pr-accent-deep);
        }

        .legend-item:has(input:focus-visible) {
            outline: 2px solid var(--pr-focus);
            outline-offset: 2px;
        }

        .legend-count {
            font-variant-numeric: tabular-nums;
            color: var(--pr-muted);
        }

        .rs-sw {
            width: 12px;
            height: 12px;
            border-radius: 3px;
            border: 1px solid var(--pr-rule);
        }

        .rs-sw.is-work { background: var(--rs-work); }
        .rs-sw.is-off { background: var(--rs-off); }
        .rs-sw.is-holiday { background: var(--rs-holiday); }
        .rs-sw.is-leave { background: var(--rs-leave); }
        .rs-sw.is-sick { background: var(--rs-sick); }
        .rs-sw.is-toil { background: var(--rs-toil); }
        .rs-sw.is-today { background: var(--pr-panel); box-shadow: inset 0 0 0 2px var(--pr-accent-strong); }

        /* Grid roster */
        .roster-scroll {
            overflow: auto;
            max-height: 72vh;
        }

        .roster-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            font-family: var(--pr-font);
            font-size: var(--pr-text-sm);
        }

        .roster-table thead th {
            position: sticky;
            top: 0;
            z-index: 3;
            padding: 8px 4px;
            background: var(--pr-paper);
            border-bottom: 1px solid var(--pr-rule);
            border-right: 1px solid var(--pr-rule);
            text-align: center;
            white-space: nowrap;
            font-size: var(--pr-text-xs);
            font-weight: 700;
            color: var(--pr-ink-2);
        }

        .roster-table thead th .rs-dow {
            display: block;
            font-weight: 600;
            color: var(--pr-muted);
        }

        .roster-table thead th .rs-date {
            display: block;
            font-size: var(--pr-text-sm);
            font-variant-numeric: tabular-nums;
            color: var(--pr-ink);
        }

        .roster-table thead th.weekend {
            background: var(--pr-paper-2);
        }

        .roster-table thead th.today {
            background: var(--pr-accent-soft);
            box-shadow: inset 0 -2px 0 var(--pr-accent-strong);
        }

        .roster-table thead th.today .rs-date {
            color: var(--pr-accent-deep);
        }

        .roster-table thead th.col-emp {
            left: 0;
            z-index: 5;
            min-width: 220px;
            padding-left: var(--pr-space-md);
            text-align: left;
            font-size: var(--pr-text-sm);
        }

        .roster-table tbody td {
            padding: 4px;
            border-bottom: 1px solid var(--pr-rule);
            border-right: 1px solid var(--pr-rule);
            vertical-align: middle;
            text-align: center;
            background: var(--pr-panel);
        }

        .roster-table tbody .col-emp {
            position: sticky;
            left: 0;
            z-index: 2;
            min-width: 220px;
            max-width: 260px;
            padding: var(--pr-space-xs) var(--pr-space-md);
            text-align: left;
            background: var(--pr-panel);
            box-shadow: inset -1px 0 0 var(--pr-rule);
        }

        .roster-table tbody tr:hover td {
            background: var(--pr-paper);
        }

        .emp-name {
            display: block;
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-ink);
            overflow-wrap: break-word;
        }

        .emp-meta {
            display: block;
            margin-top: 1px;
            font-size: 11px;
            line-height: 1.4;
            color: var(--pr-muted);
            overflow-wrap: break-word;
        }

        .emp-status {
            display: inline-block;
            margin-top: 4px;
            padding: 1px 8px;
            border-radius: var(--pr-pill);
            font-size: 11px;
            font-weight: 700;
        }

        .status-pkwt { background: var(--rs-work); color: var(--rs-work-ink); }
        .status-ojt { background: var(--pr-warn-soft); color: var(--pr-warn); }
        .status-dw { background: var(--rs-leave); color: var(--rs-leave-ink); }

        .day-cell {
            min-width: 86px;
            cursor: pointer;
        }

        .roster-table tbody td.day-cell.weekend {
            background: var(--rs-weekend-col);
        }

        .roster-table tbody td.day-cell.today {
            box-shadow: inset 2px 0 0 var(--pr-accent-strong), inset -2px 0 0 var(--pr-accent-strong);
        }

        .roster-table tbody td.day-cell:hover {
            background: var(--pr-accent-soft);
        }

        .r-badge {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 74px;
            padding: 3px 6px;
            border-radius: var(--pr-radius-sm);
            line-height: 1.3;
        }

        .r-badge .r-name {
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 11px;
            font-weight: 700;
        }

        .r-badge .r-time {
            font-size: 11px;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        .r-badge .r-notes {
            max-width: 80px;
            margin-top: 1px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 10px;
            opacity: .9;
        }

        .r-work { background: var(--rs-work); color: var(--rs-work-ink); }
        .r-off { background: var(--rs-off); color: var(--rs-off-ink); }
        .r-holiday { background: var(--rs-holiday); color: var(--rs-holiday-ink); }
        .r-leave { background: var(--rs-leave); color: var(--rs-leave-ink); }
        .r-sick { background: var(--rs-sick); color: var(--rs-sick-ink); }
        .r-toiloff { background: var(--rs-toil); color: var(--rs-toil-ink); }

        .day-cell.weekend:not([data-has-roster="1"]) .r-off {
            background: transparent;
            color: var(--pr-muted);
            font-weight: 600;
        }

        .r-empty {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border: 1px dashed var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            font-size: var(--pr-text-md);
            line-height: 1;
            color: var(--pr-muted);
        }

        .day-cell:hover .r-empty {
            border-color: var(--pr-accent);
            color: var(--pr-accent-strong);
        }

        /* Form controls */
        .f-label {
            display: block;
            margin-bottom: 4px;
            font-size: var(--pr-text-xs);
            font-weight: 600;
            color: var(--pr-ink-2);
        }

        .f-label .rs-req {
            color: var(--pr-error);
        }

        .f-control {
            width: 100%;
            min-height: 38px;
            padding: 7px 12px;
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-panel);
            font-family: var(--pr-font);
            font-size: var(--pr-text-sm);
            color: var(--pr-ink);
            transition: border-color var(--pr-dur-micro) var(--pr-ease);
        }

        .f-control:hover {
            border-color: var(--pr-muted);
        }

        .f-control:focus,
        .f-control:focus-visible {
            outline: 2px solid var(--pr-focus);
            outline-offset: 0;
            border-color: var(--pr-accent);
        }

        .f-control:disabled {
            background: var(--pr-paper-2);
            color: var(--pr-ink-2);
            cursor: not-allowed;
        }

        /* Tombol lama (dipakai modal & JS): disamakan dengan pr-btn */
        .btn-primary-r,
        .btn-secondary-r,
        .btn-danger-r,
        .btn-success-r {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 36px;
            padding: 0 14px;
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-panel);
            font-family: var(--pr-font);
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-ink);
            text-decoration: none;
            white-space: nowrap;
            cursor: pointer;
            transition: background-color var(--pr-dur-micro) var(--pr-ease), border-color var(--pr-dur-micro) var(--pr-ease), transform var(--pr-dur-micro) var(--pr-ease);
        }

        .btn-secondary-r:hover {
            background: var(--pr-paper);
            border-color: var(--pr-muted);
            color: var(--pr-ink);
            text-decoration: none;
        }

        .btn-primary-r,
        .btn-success-r {
            border-color: var(--pr-accent-strong);
            background: var(--pr-accent-strong);
            color: var(--pr-accent-ink);
        }

        .btn-primary-r:hover,
        .btn-success-r:hover {
            background: var(--pr-accent-deep);
            border-color: var(--pr-accent-deep);
            color: var(--pr-accent-ink);
        }

        .btn-danger-r {
            border-color: var(--pr-error-soft);
            background: var(--pr-error-soft);
            color: var(--pr-error);
        }

        .btn-danger-r:hover {
            border-color: var(--pr-error);
        }

        .btn-primary-r:active,
        .btn-secondary-r:active,
        .btn-danger-r:active,
        .btn-success-r:active {
            transform: translateY(1px);
        }

        .btn-primary-r:focus-visible,
        .btn-secondary-r:focus-visible,
        .btn-danger-r:focus-visible,
        .btn-success-r:focus-visible {
            outline: 2px solid var(--pr-focus);
            outline-offset: 2px;
        }

        .btn-primary-r:disabled,
        .btn-secondary-r:disabled,
        .btn-danger-r:disabled,
        .btn-success-r:disabled {
            opacity: .6;
            cursor: not-allowed;
            transform: none;
        }

        /* Modal */
        .m-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1050;
            align-items: center;
            justify-content: center;
            padding: var(--pr-space-md);
            background: var(--rs-overlay);
            font-family: var(--pr-font);
            color: var(--pr-ink);
        }

        .m-overlay.open {
            display: flex;
        }

        .m-box {
            width: 380px;
            max-width: 100%;
            max-height: calc(100vh - 32px);
            overflow-y: auto;
            border-radius: var(--pr-radius);
            background: var(--pr-panel);
            box-shadow: 0 20px 50px var(--rs-shadow);
        }

        .m-head {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--pr-space-sm);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-bottom: 1px solid var(--pr-rule);
            background: var(--pr-panel);
            font-size: var(--pr-text-md);
            font-weight: 700;
            color: var(--pr-ink);
        }

        .m-head button {
            width: 32px;
            height: 32px;
            border: 0;
            border-radius: var(--pr-radius-sm);
            background: transparent;
            font-size: 20px;
            line-height: 1;
            color: var(--pr-muted);
            cursor: pointer;
        }

        .m-head button:hover {
            background: var(--pr-paper-2);
            color: var(--pr-ink);
        }

        .m-head button:focus-visible {
            outline: 2px solid var(--pr-focus);
        }

        .m-body {
            padding: var(--pr-space-md) var(--pr-space-lg);
        }

        .m-foot {
            display: flex;
            justify-content: flex-end;
            gap: var(--pr-space-xs);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-top: 1px solid var(--pr-rule);
        }

        #rosterToast {
            position: fixed;
            right: 24px;
            bottom: 24px;
            z-index: 1080;
            display: none;
            max-width: 340px;
            padding: 12px 18px;
            border-radius: var(--pr-radius);
            background: var(--pr-ink);
            font-family: var(--pr-font);
            font-size: var(--pr-text-sm);
            font-weight: 600;
            color: var(--pr-panel);
        }

        .empty-state {
            padding: 56px 20px;
            text-align: center;
        }

        .empty-state h3 {
            margin: 0 0 6px;
            font-size: var(--pr-text-lg);
            font-weight: 700;
            color: var(--pr-ink);
        }

        .empty-state p {
            max-width: 46ch;
            margin: 0 auto;
            font-size: var(--pr-text-sm);
            color: var(--pr-ink-2);
        }

        /* Histori */
        .rs-history-filter {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: var(--pr-space-sm);
            padding: var(--pr-space-md) var(--pr-space-lg);
            border-bottom: 1px solid var(--pr-rule);
        }

        .rs-history-filter > div {
            min-width: 0;
        }

        #historyResult {
            max-height: 520px;
            overflow: auto;
        }

        #historyResult .pr-empty-note {
            margin: 0;
        }

        .rs-type {
            display: inline-block;
            padding: 1px 8px;
            border-radius: var(--pr-pill);
            font-size: 11px;
            font-weight: 700;
        }

        .rs-type.is-work { background: var(--rs-work); color: var(--rs-work-ink); }
        .rs-type.is-off { background: var(--rs-off); color: var(--rs-off-ink); }
        .rs-type.is-holiday { background: var(--rs-holiday); color: var(--rs-holiday-ink); }
        .rs-type.is-leave { background: var(--rs-leave); color: var(--rs-leave-ink); }
        .rs-type.is-sick { background: var(--rs-sick); color: var(--rs-sick-ink); }
        .rs-type.is-toil { background: var(--rs-toil); color: var(--rs-toil-ink); }

        /* Activity log (DataTables) */
        #activityTable {
            width: 100% !important;
            font-size: var(--pr-text-sm);
        }

        .rs-dt {
            padding: var(--pr-space-md) var(--pr-space-lg);
        }

        @media (max-width: 40rem) {
            .rs-filter,
            .legend,
            .rs-history-filter,
            .rs-dt,
            .m-body,
            .m-head,
            .m-foot {
                padding-left: var(--pr-space-md);
                padding-right: var(--pr-space-md);
            }

            .rs-field.is-store,
            .rs-field.is-date {
                flex: 1 1 100%;
                max-width: none;
            }

            .roster-table thead th.col-emp,
            .roster-table tbody .col-emp {
                min-width: 150px;
                max-width: 160px;
            }

            .roster-scroll {
                max-height: 64vh;
            }
        }

        .emp-meta.is-warn {
            font-weight: 700;
            color: var(--pr-warn);
        }

        /* ── Auto generate & import (gaya lama, warna dipetakan ke token) ── */

        /* ── Auto Roster Static: Periode card ── */
        .ag-period-card {
            background: var(--pr-paper);
            border: 1px solid var(--pr-rule);
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 14px;
        }

        .ag-period-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--pr-ink-2);
            margin-bottom: 8px;
        }

        .ag-period-title small {
            font-size: 11px;
            font-weight: 400;
            color: var(--pr-muted);
        }

        .btn-update-preview {
            background: none;
            border: none;
            color: var(--pr-accent-strong);
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            padding: 4px 0;
            margin-top: 4px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-update-preview:hover {
            color: var(--pr-accent-deep);
            text-decoration: underline;
        }

        .btn-update-preview:disabled {
            color: var(--pr-muted);
            cursor: not-allowed;
        }

        .auto-roster-preview {
            background: var(--pr-accent-soft);
            border: 1px solid var(--pr-accent-soft);
            border-radius: 6px;
            padding: 12px;
            font-size: 12px;
            color: var(--pr-accent-deep);
            margin-bottom: 14px;
        }

        .auto-roster-preview .preview-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }

        .auto-roster-preview .preview-row strong {
            color: var(--pr-accent-deep);
        }

        .auto-roster-preview .preview-row.created strong {
            color: var(--pr-success);
            font-weight: 700;
        }

        .auto-roster-preview .preview-row.skipped strong {
            color: var(--pr-warn);
            font-weight: 700;
        }

        /* ══════════════════════════════════════════════════════
                                           AUTO ROSTER OTHER STORE — Step Indicator
                                           ══════════════════════════════════════════════════════ */
        .aro-step-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .aro-step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .aro-step-dot {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--pr-rule);
            color: var(--pr-muted);
            font-weight: 700;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color .25s ease;
        }

        .aro-step-item.active .aro-step-dot {
            background: var(--pr-accent-strong);
            color: var(--pr-panel);
        }

        .aro-step-item.done .aro-step-dot {
            background: var(--pr-success);
            color: var(--pr-panel);
        }

        .aro-step-label {
            font-size: 10px;
            margin-top: 4px;
            color: var(--pr-muted);
            white-space: nowrap;
        }

        .aro-step-item.active .aro-step-label {
            color: var(--pr-accent-strong);
            font-weight: 600;
        }

        .aro-step-item.done .aro-step-label {
            color: var(--pr-success);
        }

        .aro-step-line {
            width: 60px;
            height: 2px;
            background: var(--pr-rule);
            margin-bottom: 18px;
            transition: background-color .25s ease;
        }

        .aro-step-line.done {
            background: var(--pr-success);
        }

        /* ARO modal body scroll */
        .aro-modal-body {
            padding: 18px;
            max-height: 70vh;
            overflow-y: auto;
        }

        /* ARO day pattern table */
        .aro-day-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .aro-day-table th {
            background: var(--pr-ink);
            color: var(--pr-panel);
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
            
        }

        .aro-day-table td {
            border-bottom: 1px solid var(--pr-paper-2);
            padding: 6px 8px;
            vertical-align: middle;
        }

        .aro-day-table tr:last-child td {
            border-bottom: none;
        }

        .aro-day-table tr.weekend-row td {
            background: var(--pr-error-soft);
        }

        /* ARO info box */
        .aro-info-box {
            background: var(--pr-warn-soft);
            border: 1px solid var(--pr-warn-soft);
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 11px;
            color: var(--pr-warn);
            margin-bottom: 14px;
            line-height: 1.6;
        }

        .aro-preview-box {
            background: var(--pr-success-soft);
            border: 1px solid var(--pr-success-soft);
            border-radius: 6px;
            padding: 12px;
            font-size: 12px;
            margin-bottom: 14px;
        }

        .aro-preview-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
            color: var(--pr-success);
        }

        .aro-preview-row strong {
            color: var(--pr-success);
        }

        /* ARO result card */
        .aro-result-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 14px;
        }

        .aro-result-card {
            border-radius: 8px;
            padding: 14px;
            text-align: center;
        }

        .aro-result-card .val {
            font-size: 26px;
            font-weight: 700;
        }

        .aro-result-card .lbl {
            font-size: 11px;
            margin-top: 2px;
        }

        .aro-card-created {
            background: var(--pr-success-soft);
        }

        .aro-card-created .val {
            color: var(--pr-success);
        }

        .aro-card-created .lbl {
            color: var(--pr-success);
        }

        .aro-card-updated {
            background: var(--pr-accent-soft);
        }

        .aro-card-updated .val {
            color: var(--pr-accent-strong);
        }

        .aro-card-updated .lbl {
            color: var(--pr-accent);
        }

        .aro-card-skipped {
            background: var(--pr-paper);
        }

        .aro-card-skipped .val {
            color: var(--pr-muted);
        }

        .aro-card-skipped .lbl {
            color: var(--pr-muted);
        }

        .aro-card-ph {
            background: var(--pr-warn-soft);
        }

        .aro-card-ph .val {
            color: var(--pr-warn);
        }

        .aro-card-ph .lbl {
            color: var(--pr-warn);
        }

        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            min-height: 34px;
            margin: 0 6px;
            padding: 4px 10px;
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-panel);
            font-family: var(--pr-font);
            font-size: var(--pr-text-sm);
            color: var(--pr-ink);
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info {
            font-size: var(--pr-text-sm);
            color: var(--pr-ink-2);
        }
    </style>
@endpush

@section('main')
    @php
        $fmtD = fn($v) => \Carbon\Carbon::parse($v)->locale('id')->translatedFormat('d M Y');
        $autoGenerateStores = ['Head Office', 'Holding', 'Distribution Center'];
        $showAutoGenerate = $storeId && in_array($currentStoreName, $autoGenerateStores);
        $canEditActions = $storeId && (auth()->user()->can('ManageRoster') || (auth()->user()->can('ManageRosterSPVManager') && $isSupervisorOrManager && $isRosterOpen));
        $showGrid = $storeId || ($canManageSPV && !$canManageAll);
        $locationLabel = $storeId ? $currentStoreName : ($showGrid ? 'Semua lokasi saya' : null);

        // Ringkasan periode dari sel grid (tanpa query tambahan)
        $allCells = $employees->flatMap(fn($e) => array_values($e->cells));
        $typeCount = $allCells->countBy('cell_type');
        $emptyByEmp = $employees->mapWithKeys(fn($e) => [$e->id => collect($e->cells)->where('cell_type', 'empty')->count()]);
        $todayStr = now()->toDateString();
        $todayCells = $employees->map(fn($e) => $e->cells[$todayStr] ?? null)->filter();
        $legendTypes = [
            'work' => ['Kerja', 'is-work'],
            'off' => ['Off', 'is-off'],
            'weekend' => ['Akhir pekan', 'is-off'],
            'holiday' => ['Public holiday', 'is-holiday'],
            'leave' => ['Cuti', 'is-leave'],
            'melahirkan' => ['Cuti melahirkan', 'is-leave'],
            'sick' => ['Sakit', 'is-sick'],
            'toiloff' => ['TOIL off', 'is-toil'],
        ];
    @endphp
    <div class="main-content pr">
        <section class="section">

            <header class="pr-head">
                <div>
                    <h1 class="pr-title">Roster & jadwal</h1>
                    <p class="pr-lede">
                        {{ $locationLabel ?? 'Pilih lokasi untuk melihat roster' }}{{ $showGrid ? ', ' . $fmtD($startDate) . ' sampai ' . $fmtD($endDate) : '' }}
                        @if ($isSupervisorOrManager)
                            <span class="rs-window">
                                <span class="status-badge {{ $isRosterOpen ? 'is-approved' : 'is-rejected' }}">
                                    {{ $isRosterOpen ? 'Pengisian roster dibuka' : 'Pengisian roster ditutup' }}
                                </span>
                            </span>
                        @endif
                    </p>
                </div>
                @if ($canEditActions)
                    <div class="pr-actions">
                        @can('ManageRoster')
                            @if ($showAutoGenerate)
                                <button type="button" class="pr-btn" onclick="confirmAutoGenerate()" title="Auto generate roster untuk periode yang dipilih">
                                    <i class="fas fa-magic" aria-hidden="true"></i> Auto generate
                                </button>
                            @endif
                        @endcan
                        <button type="button" class="pr-btn" onclick="downloadTemplate()">
                            <i class="fas fa-download" aria-hidden="true"></i> Template
                        </button>
                        <button type="button" class="pr-btn" onclick="openModal('modalBulkDelete')">
                            <i class="fas fa-trash" aria-hidden="true"></i> Hapus massal
                        </button>
                        <button type="button" class="pr-btn pr-btn-primary" onclick="openImportModal()">
                            <i class="fas fa-file-import" aria-hidden="true"></i> Import Excel
                        </button>
                    </div>
                @endif
            </header>

            {{-- Filter --}}
            <section class="pr-panel" aria-label="Filter roster">
                <form method="GET" action="{{ route('roster.index') }}" class="rs-filter">
                    <div class="rs-field is-store">
                        <label class="f-label" for="rsStore">Lokasi
                            @can('ManageRoster')<span class="rs-req" aria-hidden="true">*</span>@endcan
                        </label>
                        @can('ManageRoster')
                            <select name="store_id" id="rsStore" class="f-control select2" required>
                                <option value="">Pilih lokasi</option>
                                @foreach ($stores as $store)
                                    <option value="{{ $store->id }}" {{ $storeId == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                                @endforeach
                            </select>
                        @elsecan('ManageRosterSPVManager')
                            @if ($myStores->count() >= 1)
                                <select name="store_id" id="rsStore" class="f-control select2">
                                    <option value="">Semua lokasi saya</option>
                                    @foreach ($myStores as $store)
                                        <option value="{{ $store->id }}" {{ $storeId == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="hidden" name="store_id" value="{{ $myStoreId }}">
                                <input type="text" id="rsStore" class="f-control" value="{{ $myStoreName }}" disabled>
                            @endif
                        @else
                            <input type="hidden" name="store_id" value="{{ $myStoreId }}">
                            <input type="text" id="rsStore" class="f-control" value="{{ $myStoreName }}" disabled>
                        @endcan
                    </div>

                    @can('ManageRoster')
                        <div class="rs-field is-date">
                            <label class="f-label" for="rsStart">Dari</label>
                            <input type="date" name="start_date" id="rsStart" class="f-control" value="{{ $startDate }}">
                        </div>
                        <div class="rs-field is-date">
                            <label class="f-label" for="rsEnd">Sampai</label>
                            <input type="date" name="end_date" id="rsEnd" class="f-control" value="{{ $endDate }}">
                        </div>
                    @else
                        @can('ManageRosterSPVManager')
                            <div class="rs-field is-date">
                                <label class="f-label" for="rsStart">Dari</label>
                                <input type="date" name="start_date" id="rsStart" class="f-control" value="{{ $startDate }}"
                                    min="{{ $startDate }}" max="{{ \Carbon\Carbon::parse($startDate)->addMonth()->toDateString() }}">
                            </div>
                            <div class="rs-field is-date">
                                <label class="f-label" for="rsEnd">Sampai</label>
                                <input type="date" name="end_date" id="rsEnd" class="f-control" value="{{ $endDate }}"
                                    min="{{ $endDate }}" max="{{ \Carbon\Carbon::parse($endDate)->addMonth()->toDateString() }}">
                            </div>
                        @endcan
                    @endcan

                    <div class="rs-filter-actions">
                        <button type="submit" class="pr-btn pr-btn-primary"><i class="fas fa-search" aria-hidden="true"></i> Tampilkan</button>
                        <a href="{{ route('roster.index') }}" class="pr-btn">Reset</a>
                    </div>
                </form>
            </section>

            @if (!$showGrid)
                <section class="pr-panel">
                    <div class="empty-state">
                        <h3>Pilih lokasi dulu</h3>
                        <p>Pilih lokasi di filter atas, lalu klik Tampilkan untuk melihat dan mengisi roster periode ini.</p>
                    </div>
                </section>
            @else
                {{-- Ringkasan periode (signature: berapa jadwal yang masih bolong) --}}
                <section class="pr-panel rs-summary" aria-labelledby="rs-sum-title">
                    <div class="pr-panel-head">
                        <div>
                            <h2 class="pr-panel-title" id="rs-sum-title">Kelengkapan roster</h2>
                            <span class="pr-panel-sub">{{ $employees->count() }} karyawan, {{ count($dates) }} hari</span>
                        </div>
                    </div>
                    <dl class="pr-period">
                        <div>
                            <dt>Hari kerja belum diisi</dt>
                            <dd class="{{ ($typeCount['empty'] ?? 0) ? 'is-warn-text' : '' }}">{{ $typeCount['empty'] ?? 0 }} <small>sel</small></dd>
                            <span class="pr-period-note">{{ $emptyByEmp->filter()->count() }} karyawan masih ada yang kosong</span>
                        </div>
                        <div>
                            <dt>Jadwal kerja</dt>
                            <dd>{{ $typeCount['work'] ?? 0 }} <small>sel</small></dd>
                        </div>
                        <div>
                            <dt>Off dan TOIL off</dt>
                            <dd>{{ ($typeCount['off'] ?? 0) + ($typeCount['toiloff'] ?? 0) }} <small>sel</small></dd>
                            <span class="pr-period-note">ditambah {{ $typeCount['weekend'] ?? 0 }} akhir pekan tanpa roster</span>
                        </div>
                        <div>
                            <dt>Cuti, sakit, libur</dt>
                            <dd>{{ ($typeCount['leave'] ?? 0) + ($typeCount['melahirkan'] ?? 0) + ($typeCount['sick'] ?? 0) + ($typeCount['holiday'] ?? 0) }} <small>sel</small></dd>
                        </div>
                        @if ($todayCells->isNotEmpty())
                            <div>
                                <dt>Hari ini</dt>
                                <dd>{{ $todayCells->where('cell_type', 'work')->count() }} <small>kerja</small></dd>
                                <span class="pr-period-note">{{ $todayCells->where('cell_type', 'empty')->count() }} belum dijadwalkan</span>
                            </div>
                        @endif
                    </dl>
                </section>

                {{-- Grid roster --}}
                <section class="pr-panel" aria-labelledby="rs-grid-title">
                    <div class="pr-panel-head">
                        <div>
                            <h2 class="pr-panel-title" id="rs-grid-title">Jadwal per karyawan</h2>
                            <span class="pr-panel-sub">
                                {{ $canViewOnly ? 'Hanya lihat' : 'Klik sel untuk mengisi atau mengubah jadwal' }}
                            </span>
                        </div>
                    </div>
                    <div class="legend" role="group" aria-label="Sorot satu tipe hari">
                        <span class="legend-lead">Sorot:</span>
                        @foreach ($legendTypes as $type => [$label, $sw])
                            <label class="legend-item">
                                <input type="checkbox" class="legend-filter" data-type="{{ $type }}">
                                <span class="rs-sw {{ $sw }}" aria-hidden="true"></span>
                                {{ $label }} <span class="legend-count">{{ $typeCount[$type] ?? 0 }}</span>
                            </label>
                        @endforeach
                        @if ($todayCells->isNotEmpty())
                            <label class="legend-item">
                                <input type="checkbox" class="legend-filter" data-type="today">
                                <span class="rs-sw is-today" aria-hidden="true"></span> Hari ini
                            </label>
                        @endif
                    </div>
                    <div class="roster-scroll">
                        <table class="roster-table">
                            <thead>
                                <tr>
                                    <th class="col-emp" scope="col">Karyawan</th>
                                    @foreach ($dates as $carbon)
                                        <th scope="col" class="{{ $carbon->isWeekend() ? 'weekend' : '' }} {{ $carbon->isToday() ? 'today' : '' }}">
                                            <span class="rs-dow">{{ $carbon->locale('id')->translatedFormat('D') }}</span>
                                            <span class="rs-date">{{ $carbon->format('d/m') }}</span>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $employee)
                                    @php $empty = $emptyByEmp[$employee->id] ?? 0; @endphp
                                    <tr>
                                        <th scope="row" class="col-emp">
                                            <span class="emp-name">{{ $employee->employee_name }}</span>
                                            <span class="emp-meta">
                                                {{ collect([$employee->position->first()?->name, $employee->department->first()?->department_name, !$storeId ? $employee->store->first()?->name : null])->filter()->implode(', ') ?: '-' }}
                                            </span>
                                            @if ($employee->status_employee)
                                                <span class="emp-status {{ $employee->status_class }}">{{ $employee->status_employee }}</span>
                                            @endif
                                            @if ($employee->status && $employee->status !== 'Active')
                                                <span class="emp-status status-ojt">{{ $employee->status }}</span>
                                            @endif
                                            @if ($empty)
                                                <span class="emp-meta is-warn">{{ $empty }} hari kerja belum diisi</span>
                                            @endif
                                        </th>
                                        @foreach ($employee->cells as $cell)
                                            <td class="day-cell {{ $cell['is_weekend'] ? 'weekend' : '' }} {{ $cell['is_today'] ? 'today' : '' }}"
                                                data-emp-id="{{ $employee->id }}"
                                                data-emp-name="{{ $employee->employee_name }}"
                                                data-emp-status="{{ $employee->status_employee ?? '' }}"
                                                data-date="{{ $cell['date_str'] }}"
                                                data-shift-id="{{ $cell['shift_id'] }}"
                                                data-day-type="{{ $cell['day_type'] }}"
                                                data-has-roster="{{ $cell['has_roster'] ? '1' : '0' }}"
                                                data-sick-attachment="{{ $cell['sick_attachment'] }}"
                                                data-notes="{{ $cell['notes'] }}"
                                                data-cell-type="{{ $cell['cell_type'] }}"
                                                data-is-today="{{ $cell['is_today'] ? '1' : '0' }}"
                                                onclick="openCellModal(this)"
                                                title="{{ $employee->employee_name }}, {{ $fmtD($cell['date_str']) }}{{ $cell['notes'] ? ', ' . $cell['notes'] : '' }}">
                                                @if ($cell['badge_class'] === '')
                                                    <span class="r-empty" aria-label="Belum diisi">+</span>
                                                @else
                                                    <span class="{{ $cell['badge_class'] }}">
                                                        <span class="r-name">{{ $cell['badge_name'] }}</span>
                                                        @if ($cell['badge_time'])
                                                            <span class="r-time">{{ $cell['badge_time'] }}</span>
                                                        @endif
                                                        @if ($cell['notes'])
                                                            <span class="r-notes">{{ $cell['notes'] }}</span>
                                                        @endif
                                                    </span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($dates) + 1 }}">
                                            <p class="pr-empty-note">Tidak ada karyawan aktif di lokasi ini.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- Histori roster --}}
                <section class="pr-panel" aria-labelledby="rs-hist-title">
                    <div class="pr-panel-head">
                        <div>
                            <h2 class="pr-panel-title" id="rs-hist-title">Histori roster</h2>
                            <span class="pr-panel-sub">Cari jadwal per tanggal dan nama, lalu export ke Excel</span>
                        </div>
                    </div>
                    <div class="rs-history-filter">
                        @can('ManageRoster')
                            <div class="rs-field is-store">
                                <label class="f-label" for="historyStore">Lokasi</label>
                                <select id="historyStore" class="f-control select2">
                                    <option value="">Semua lokasi</option>
                                    @foreach ($stores as $store)
                                        <option value="{{ $store->id }}" {{ $storeId == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            @if ($myStores->count() > 1)
                                <div class="rs-field is-store">
                                    <label class="f-label" for="historyStore">Lokasi</label>
                                    <select id="historyStore" class="f-control select2">
                                        <option value="">Semua lokasi saya</option>
                                        @foreach ($myStores as $store)
                                            <option value="{{ $store->id }}" {{ $storeId == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <input type="hidden" id="historyStore" value="{{ $myStoreId }}">
                            @endif
                        @endcan
                        <div class="rs-field is-date">
                            <label class="f-label" for="historyStart">Dari</label>
                            <input type="date" id="historyStart" value="{{ $startDate }}" class="f-control">
                        </div>
                        <div class="rs-field is-date">
                            <label class="f-label" for="historyEnd">Sampai</label>
                            <input type="date" id="historyEnd" value="{{ $endDate }}" class="f-control">
                        </div>
                        <div class="rs-field is-store">
                            <label class="f-label" for="historySearch">Nama karyawan</label>
                            <input type="search" id="historySearch" class="f-control" placeholder="Ketik nama">
                        </div>
                        <div class="rs-filter-actions">
                            <button type="button" class="pr-btn pr-btn-primary" onclick="loadHistory()"><i class="fas fa-search" aria-hidden="true"></i> Cari</button>
                            <button type="button" class="pr-btn" onclick="resetHistory()">Reset</button>
                            <button type="button" class="pr-btn" onclick="exportHistory()"><i class="fas fa-file-excel" aria-hidden="true"></i> Export</button>
                        </div>
                    </div>
                    <div id="historyResult">
                        <p class="pr-empty-note">Isi rentang tanggal lalu klik Cari.</p>
                    </div>
                </section>
            @endif

            @canany(['ManageRoster', 'ManageRosterSPVManager'])
                <section class="pr-panel" aria-labelledby="rs-log-title">
                    <div class="pr-panel-head">
                        <div>
                            <h2 class="pr-panel-title" id="rs-log-title">Log aktivitas roster</h2>
                            <span class="pr-panel-sub">Siapa mengubah jadwal apa, terbaru di atas</span>
                        </div>
                    </div>
                    <div class="rs-dt pr-table-wrap">
                        <table class="table" id="activityTable">
                            <thead>
                                <tr>
                                    <th width="40">#</th>
                                    <th width="100">Aksi</th>
                                    <th>Keterangan</th>
                                    <th width="150">Oleh</th>
                                    <th>Perubahan</th>
                                    <th width="150">Waktu</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </section>
            @endcanany

        </section>
    </div>

    <div class="m-overlay" id="modalCell">
        <div class="m-box">
            <div class="m-head">
                <span>Set Schedule</span>
                <button onclick="closeModal('modalCell')">×</button>
            </div>

            @if ($canViewOnly)
                {{-- ViewRoster: read only --}}
                <div class="m-body">
                    <div style="font-weight:700;color:#0f172a;margin-bottom:2px" id="mEmpName"></div>
                    <div style="font-size:12px;color:#64748b;margin-bottom:14px" id="mDate"></div>
                    <div
                        style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:20px;text-align:center;">
                        <i class="fas fa-lock" style="color:#ef4444;font-size:24px;margin-bottom:10px;display:block;"></i>
                        <div style="font-weight:600;color:#991b1b;margin-bottom:4px;">Akses Terbatas</div>
                        <div style="font-size:12px;color:#b91c1c;">Anda tidak memiliki izin untuk mengubah jadwal.</div>
                    </div>
                </div>
                <div class="m-foot">
                    <button class="btn-secondary-r" onclick="closeModal('modalCell')">Tutup</button>
                </div>
            @else
                {{-- ManageRoster / ManageRosterSPVManager: form normal --}}
                <div class="m-body">
                    <div style="font-weight:700;color:#0f172a;margin-bottom:2px" id="mEmpName"></div>
                    <div style="font-size:12px;color:#64748b;margin-bottom:14px" id="mDate"></div>
                    <input type="hidden" id="mEmpId">

                    <label class="f-label">Day Type</label>
                    <select id="mDayType" class="f-control mb-3" onchange="toggleShift()">
                        <option value="Work">Work (Kerja)</option>
                        <option value="Off">Off (Libur)</option>
                        <option value="Public Holiday" class="opt-ph">Public Holiday</option>
                        <option value="Leave" class="opt-cuti">Leave (Cuti)</option>
                        <option value="Cuti Melahirkan" class="opt-cuti">Cuti Melahirkan</option>
                        <option value="Sick" class="opt-sick">Sick</option>
                    </select>
                    <small id="mDayTypeNote"
                        style="display:none;color:#92400e;font-size:11px;margin-top:-8px;margin-bottom:10px;display:block"></small>

                    <div id="shiftWrap">
                        <label class="f-label">Shift</label>
                        <select id="mShiftId" class="f-control mb-3">
                            <option value="">-- Choose Shift --</option>
                            @foreach ($shifts as $shift)
                                <option value="{{ $shift->id }}">
                                    {{ $shift->shift_name }}
                                    ({{ $shift->time_label }})
                                </option>
                            @endforeach
                        </select>
                    </div>


                    <div id="sickWrap" style="display:none">
                        <label class="f-label">Bukti Sakit <span style="color:#ef4444">*</span>
                            <small style="color:#94a3b8;font-weight:400">(JPG/PNG/PDF, maks 5MB)</small>
                        </label>
                        <input type="file" id="mSickFile" class="f-control mb-3" accept=".jpg,.jpeg,.png,.pdf">
                    </div>

                    <div id="mSickExisting"
                        style="font-size:11px;color:#16a34a;margin-top:-8px;margin-bottom:10px;display:none">
                        <i class="fas fa-check-circle"></i> Sudah ada bukti tersimpan.
                        <button type="button" id="mSickLihatBtn"
                            style="margin-left:6px;background:none;border:none;color:#0369a1;font-weight:600;cursor:pointer;font-size:11px;padding:0">
                            <i class="fas fa-eye"></i> Lihat
                        </button>
                    </div>
                    {{-- <div id="mSickExisting"
        style="font-size:11px;color:#16a34a;margin-top:-8px;margin-bottom:10px;display:none">
        <i class="fas fa-check-circle"></i> Sudah ada bukti tersimpan.
        <a id="mSickPreviewLink" href="#" target="_blank"
            style="margin-left:6px;color:#0369a1;font-weight:600">
            <i class="fas fa-eye"></i> Lihat
        </a>
    </div> --}}

                    <div id="phCarryoverWrap" style="display:none">
                        <label class="f-label">PH Tukar (Simpanan)
                            <small style="color:#94a3b8;font-weight:400">(opsional — pilih kalau pakai PH simpanan)</small>
                        </label>
                        <select id="mPhCarryover" class="f-control mb-3">
                            <option value="">-- Tidak pakai PH simpanan --</option>
                        </select>
                        <small id="mPhCarryoverNote"
                            style="display:none;color:#0f766e;font-size:11px;margin-top:-8px;margin-bottom:10px;"></small>
                    </div>

                    <label class="f-label">Notes</label>
                    <input type="text" id="mNotes" class="f-control" placeholder="optional note...">
                </div>

                <div class="m-foot">
                    <button class="btn-danger-r" id="mDeleteBtn" style="display:none" onclick="deleteRoster()">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                    <button class="btn-secondary-r" onclick="closeModal('modalCell')">Cancel</button>
                    <button class="btn-primary-r" id="mSaveBtn" onclick="saveRoster()">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            @endif

        </div>
    </div>



    {{-- ════════════════════════════════════════════════════
     MODAL: Bulk Assign
     ════════════════════════════════════════════════════ --}}
    <div class="m-overlay" id="modalBulk">
        <div class="m-box" style="width:400px">
            <div class="m-head">
                <span>Bulk Assign Shift</span>
                <button onclick="closeModal('modalBulk')">×</button>
            </div>
            <div class="m-body">
                <label class="f-label">Choose Employee</label>
                <select id="bulkEmps" class="f-control mb-1" multiple style="height:100px">
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}" data-status="{{ $emp->status_employee ?? '' }}">
                            {{ $emp->employee_name }} – {{ $emp->store->first()?->name ?? '' }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted d-block mb-3">Hold the button <kbd>Ctrl</kbd> to choose more than 1
                    employee</small>

                <div class="d-flex gap-2 mb-3">
                    <div style="flex:1"><label class="f-label">Start Date</label><input type="date" id="bulkStart"
                            class="f-control" value="{{ $startDate }}"></div>
                    <div style="flex:1"><label class="f-label">End Date</label><input type="date" id="bulkEnd"
                            class="f-control" value="{{ $endDate }}"></div>
                </div>

                <div class="m-body">
                    <label class="f-label">Choose Employee</label>
                    <select id="bulkEmps" class="f-control mb-1" multiple style="height:100px">
                        {{-- @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}" data-status="{{ $emp->status_employee ?? '' }}">
                            {{ $emp->employee_name }} – {{ $emp->store->first()?->name ?? '' }}
                        </option>
                    @endforeach --}}
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}" data-status="{{ $emp->status_employee ?? '' }}">
                                {{ $emp->employee_name }} – {{ $emp->store->first()?->name ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex align-items-center gap-2 mb-2">
                    <input type="checkbox" id="bulkSkipWeekend" checked>
                    <label for="bulkSkipWeekend" class="f-label mb-0">Skip Sunday</label>
                </div>

                <div class="d-flex align-items-center gap-2 mb-2" style="margin-top:8px">
                    <input type="checkbox" id="bulkSaturdayShift" onchange="toggleSaturdayShift()">
                    <label for="bulkSaturdayShift" class="f-label mb-0">Add Saturday Shift</label>
                </div>

                <div id="saturdayShiftWrap" style="display:none;margin-top:8px">
                    <label class="f-label">Pilih Shift Sabtu</label>
                    <select id="bulkSaturdayShiftId" class="f-control mb-3">
                        <option value="">-- Pilih Shift Sabtu --</option>
                        @foreach ($shifts as $shift)
                            <option value="{{ $shift->id }}">
                                {{ $shift->shift_name }}
                                ({{ $shift->time_label }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="m-foot">
                <button class="btn-secondary-r" onclick="closeModal('modalBulk')">Cancel</button>
                <button class="btn-primary-r" onclick="saveBulk()"><i class="fas fa-calendar-check"></i> Assign</button>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
     MODAL: Copy Roster
     ════════════════════════════════════════════════════ --}}
    <div class="m-overlay" id="modalCopy">
        <div class="m-box">
            <div class="m-head">
                <span>Copy Roster</span>
                <button onclick="closeModal('modalCopy')">×</button>
            </div>
            <div class="m-body">
                <p style="font-size:12px;color:#64748b;margin-bottom:14px">Copy jadwal dari periode sumber ke periode
                    target.</p>
                <div
                    style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;padding:10px;font-size:12px;color:#1e40af;margin-bottom:14px">
                    <i class="fas fa-info-circle"></i>
                    <strong>Sumber</strong> = periode yang ingin dicopy.
                    <strong>Target</strong> = periode tujuan yang akan diisi jadwal baru.
                </div>
                <label class="f-label">Sumber: Start Date <span style="color:#ef4444">*</span></label>
                <input type="date" id="copySourceStart" class="f-control mb-2" value="{{ $startDate }}">
                <label class="f-label">Sumber: End Date <span style="color:#ef4444">*</span></label>
                <input type="date" id="copySourceEnd" class="f-control mb-3" value="{{ $endDate }}">
                <div style="border-top:1px dashed #e2e8f0;margin:14px 0"></div>
                <label class="f-label">Target: Start Date <span style="color:#ef4444">*</span></label>
                <input type="date" id="copyTargetStart" class="f-control mb-2">
                <label class="f-label">Target: End Date <span style="color:#ef4444">*</span></label>
                <input type="date" id="copyTargetEnd" class="f-control mb-2">
            </div>
            <div class="m-foot">
                <button class="btn-secondary-r" onclick="closeModal('modalCopy')">Cancel</button>
                <button class="btn-primary-r" onclick="saveCopy()"><i class="fas fa-copy"></i> Copy</button>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
     MODAL: Bulk Delete
     ════════════════════════════════════════════════════ --}}
    <div class="m-overlay" id="modalBulkDelete">
        <div class="m-box" style="width:400px">
            <div class="m-head">
                <span>Bulk Delete Roster</span>
                <button onclick="closeModal('modalBulkDelete')">×</button>
            </div>

            <div class="m-body">
                <label class="f-label">Pilih Karyawan</label>
                <select id="deleteEmps" class="f-control mb-1" multiple style="height:100px">
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->employee_name }} –
                            {{ $emp->store->first()?->name ?? '' }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted d-block mb-3">Tahan <kbd>Ctrl</kbd> untuk pilih lebih dari satu</small>
                <div class="d-flex gap-2 mb-3">
                    <div style="flex:1">
                        <label class="f-label">Start Date</label>
                        <input type="date" id="deleteStart" class="f-control" value="{{ $startDate }}">
                    </div>
                    <div style="flex:1">
                        <label class="f-label">End Date</label>
                        <input type="date" id="deleteEnd" class="f-control" value="{{ $endDate }}">
                    </div>
                </div>
                <div
                    style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:10px;font-size:12px;color:#991b1b;">
                    Semua jadwal karyawan yang dipilih dalam rentang tanggal ini akan dihapus permanen!
                </div>
            </div>
            <div class="m-foot">
                <button class="btn-secondary-r" onclick="closeModal('modalBulkDelete')">Batal</button>
                <button class="btn-danger-r" onclick="saveBulkDelete()"><i class="fas fa-trash"></i> Hapus</button>
            </div>

        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
     MODAL: Auto Generate Roster — 3 Store Static
     ════════════════════════════════════════════════════ --}}
    <div class="m-overlay" id="modalAutoGenerate">
        <div class="m-box" style="width:440px; max-height:90vh; display:flex; flex-direction:column;">
            <div class="m-head">
                <span>Auto Generate Roster</span>
                <button onclick="closeModal('modalAutoGenerate')">×</button>
            </div>
            <div class="m-body" style="overflow-y:auto; flex:1;">
                <p style="font-size:12px;color:#64748b;margin-bottom:14px;line-height:1.6">
                    Sistem akan generate roster otomatis untuk karyawan di:
                    <strong>Head Office, Holding, Distribution Center</strong>.
                </p>

                {{-- Pilih Shift --}}
                <div class="ag-period-card">
                    <div class="ag-period-title">
                        Pilih Shift <small>(wajib dipilih)</small>
                    </div>
                    <div class="d-flex gap-2">
                        <div style="flex:1">
                            <label class="f-label">
                                Shift Senin - Jumat <span style="color:#ef4444">*</span>
                            </label>
                            <select id="ag-shift-weekday" class="f-control ag-shift-select2">
                                <option value="">-- Pilih Shift --</option>
                                @foreach ($shifts as $shift)
                                    <option value="{{ $shift->id }}">
                                        {{ $shift->shift_name }}
                                        ({{ $shift->time_label }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div style="flex:1">
                            <label class="f-label">
                                Shift Sabtu <span style="color:#ef4444">*</span>
                            </label>
                            <select id="ag-shift-saturday" class="f-control ag-shift-select2">
                                <option value="">-- Pilih Shift --</option>
                                @foreach ($shifts as $shift)
                                    <option value="{{ $shift->id }}">
                                        {{ $shift->shift_name }}
                                        ({{ $shift->time_label }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>


                <div class="m-body">
                    <div class="d-flex gap-2 mb-3">
                        <div style="flex:1">
                            <label class="f-label">Start Date</label>
                            <input type="date" id="ag-start-date" class="f-control"
                                onchange="loadAutoGeneratePreview()">
                        </div>
                        <div style="flex:1">
                            <label class="f-label">End Date</label>
                            <input type="date" id="ag-end-date" class="f-control"
                                onchange="loadAutoGeneratePreview()">
                        </div>
                    </div>
                    <button type="button" class="btn-update-preview" onclick="loadAutoGeneratePreview()">
                        <i class="fas fa-sync-alt"></i> Update Preview
                    </button>
                </div>

                <div class="auto-roster-preview" id="autoRosterPreview">
                    <div style="font-weight:600;margin-bottom:8px;color:#1e3a8a">Detail Periode</div>
                    <div class="preview-row"><span>Periode:</span><strong id="ag-period">-</strong></div>
                    <div class="preview-row"><span>Total karyawan:</span><strong id="ag-employees">-</strong></div>
                    <div class="preview-row"><span>&nbsp;&nbsp;• Hindu:</span><strong id="ag-hindu">-</strong></div>
                    <div class="preview-row"><span>&nbsp;&nbsp;• Non Hindu:</span><strong id="ag-non-hindu">-</strong>
                    </div>
                    <div class="preview-row"><span>Public Holiday dalam periode:</span><strong id="ag-ph">-</strong>
                    </div>
                    <div style="border-top:1px dashed #bfdbfe;margin:6px 0"></div>
                    <div class="preview-row created"><span>Estimasi akan dibuat:</span><strong
                            id="ag-estimated-created">-</strong></div>
                    <div class="preview-row skipped"><span>Estimasi akan dilewati (sudah ada):</span><strong
                            id="ag-estimated-skipped">-</strong></div>
                </div>

                <div
                    style="background:#fef9c3;border:1px solid #fde047;border-radius:6px;padding:10px;font-size:12px;color:#854d0e;">
                    <i class="fas fa-info-circle"></i>
                    Pola jadwal: <strong>Senin-Jumat</strong> (sesuai shift dipilih), <strong>Sabtu</strong> (sesuai shift
                    dipilih), <strong>Minggu</strong> (Off).<br><br>
                    Public Holiday di-filter sesuai agama karyawan.<br><br>
                    Roster yang sudah ada <strong>tidak akan ditimpa</strong>.
                </div>
            </div>
            <div class="m-foot">
                <button class="btn-secondary-r" onclick="closeModal('modalAutoGenerate')">Cancel</button>
                <button class="btn-success-r" onclick="executeAutoGenerate()" id="btnExecuteAutoGenerate">
                    <i class="fas fa-magic"></i> Generate
                </button>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════
     MODAL: Auto Generate Roster — Store Lain (Per Minggu)
     ════════════════════════════════════════════════════ --}}
    {{-- <div class="m-overlay" id="modalAroOther">
        <div class="m-box" style="width:560px; max-width:97vw;">
            <div class="m-head" style="background:linear-gradient(135deg,#0f172a,#1e3a8a);">
                <span>Auto Generate Roster — Store Lain</span>
                <button onclick="closeModal('modalAroOther')">×</button>
            </div>

            <div style="background:#f8fafc;padding:14px 18px 0;border-bottom:1px solid #e2e8f0;">
                <div class="aro-step-wrap">
                    <div class="aro-step-item active" id="aroStepDot1">
                        <div class="aro-step-dot">1</div>
                        <div class="aro-step-label">Pilih Minggu</div>
                    </div>

                    <div class="aro-step-line" id="aroStepLine1"></div>
                    <div class="aro-step-item" id="aroStepDot2">
                        <div class="aro-step-dot">2</div>
                        <div class="aro-step-label">Atur Pola Shift</div>
                    </div>
                    <div class="aro-step-line" id="aroStepLine2"></div>
                    <div class="aro-step-item" id="aroStepDot3">
                        <div class="aro-step-dot">3</div>
                        <div class="aro-step-label">Preview & Generate</div>
                    </div>
                </div>
            </div>

            <div id="aroPanel1" class="aro-modal-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                    <div>
                        <label class="f-label">Pilih Minggu (Senin) <span style="color:#ef4444">*</span></label>
                        <input type="date" id="aroWeekStart" class="f-control" onchange="aroOnWeekChange()">
                        <div id="aroWeekRangeInfo" style="font-size:11px;color:#64748b;margin-top:4px;"></div>
                        <div id="aroWeekDayWarn" style="font-size:11px;color:#dc2626;margin-top:3px;display:none;">
                            <i class="fas fa-exclamation-triangle"></i> Bukan hari Senin, otomatis disesuaikan.
                        </div>
                    </div>
                    <div>
                        <label class="f-label">Store Aktif</label>
                        <div style="background:#f1f5f9;border-radius:6px;padding:10px 12px;font-size:12px;color:#334155;">
                            <strong id="aroStoreName">{{ $currentStoreName ?? '-' }}</strong>
                            <div id="aroStoreEmpCount" style="color:#64748b;margin-top:2px;">— karyawan</div>
                        </div>
                    </div>
                </div>

                <label class="f-label">Terapkan ke Karyawan</label>
                <div style="display:flex;gap:20px;margin-bottom:10px;">
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
                        <input type="radio" name="aroApplyTo" value="all" checked onchange="aroToggleEmpSelect()">
                        <span><strong>Semua Karyawan</strong></span>
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
                        <input type="radio" name="aroApplyTo" value="selected" onchange="aroToggleEmpSelect()">
                        <span><strong>Karyawan Tertentu</strong></span>
                    </label>
                </div>
                <div id="aroEmpSelectWrap" style="display:none;margin-bottom:12px;">
                    <select id="aroEmpMultiSelect" class="f-control" multiple style="height:90px;">
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->employee_name }}</option>
                        @endforeach
                    </select>
                    <small style="color:#64748b;font-size:11px;">Tahan Ctrl/Cmd untuk memilih lebih dari satu.</small>
                </div>

                <div
                    style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:10px 12px;margin-bottom:4px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;">
                        <input type="checkbox" id="aroOverride" style="width:15px;height:15px;">
                        <span>
                            <strong>Timpa roster yang sudah ada</strong>
                            <span style="display:block;font-size:11px;color:#64748b;font-weight:400;">
                                Jika aktif, roster di minggu ini yang sudah ada akan diperbarui.
                            </span>
                        </span>
                    </label>
                </div>

                <div class="m-foot" style="padding:12px 0 0;border-top:none;justify-content:flex-end;">
                    <button class="btn-primary-r" id="aroStep1Next" onclick="aroGoStep2()" disabled>
                        Atur Pola Shift <i class="fas fa-arrow-right" style="margin-left:6px;"></i>
                    </button>
                </div>
            </div>

            <div id="aroPanel2" class="aro-modal-body" style="display:none;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                    <div style="font-size:13px;font-weight:600;color:#0f172a;">
                        <i class="fas fa-clock" style="color:#1d4ed8;margin-right:6px;"></i>
                        Pola Shift Senin – Minggu
                    </div>
                    <div style="display:flex;gap:6px;">
                        <button class="btn-secondary-r" style="padding:5px 10px;font-size:11px;"
                            onclick="aroQuickPattern('default')">
                            <i class="fas fa-magic"></i> Default
                        </button>
                        <button class="btn-secondary-r" style="padding:5px 10px;font-size:11px;"
                            onclick="aroQuickPattern('allOff')">
                            <i class="fas fa-moon"></i> Semua Off
                        </button>
                    </div>
                </div>

                <div style="overflow-x:auto;">
                    <table class="aro-day-table">
                        <thead>
                            <tr>
                                <th style="width:90px;">Hari</th>
                                <th style="width:150px;">Tipe Hari</th>
                                <th>Shift</th>
                                <th style="width:130px;">Jam</th>
                            </tr>
                        </thead>
                        <tbody id="aroDayPatternBody"></tbody>
                    </table>
                </div>

                <div class="aro-info-box" style="margin-top:12px;">
                    <i class="fas fa-info-circle"></i>
                    Jika hari yang di-set <strong>Work</strong> ternyata merupakan hari libur nasional yang relevan
                    untuk agama karyawan, sistem otomatis mengubahnya menjadi <strong>Public Holiday</strong>.
                </div>

                <div class="m-foot" style="padding:12px 0 0;border-top:none;justify-content:space-between;">
                    <button class="btn-secondary-r" onclick="aroGoStep1()">
                        <i class="fas fa-arrow-left" style="margin-right:6px;"></i> Kembali
                    </button>
                    <button class="btn-primary-r" onclick="aroGoStep3()">
                        Preview & Konfirmasi <i class="fas fa-arrow-right" style="margin-left:6px;"></i>
                    </button>
                </div>
            </div>

            <div id="aroPanel3" class="aro-modal-body" style="display:none;">
                <div id="aroPreviewLoading" style="text-align:center;padding:30px 0;">
                    <i class="fas fa-spinner fa-spin fa-2x" style="color:#1d4ed8;"></i>
                    <div style="margin-top:10px;font-size:13px;color:#64748b;">Memuat preview...</div>
                </div>
                <div id="aroPreviewContent" style="display:none;">
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:8px;margin-bottom:12px;"
                        id="aroPreviewCards"></div>
                    <div id="aroPhSection" style="display:none;margin-bottom:12px;">
                        <div
                            style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:10px 12px;font-size:12px;color:#991b1b;">
                            <strong><i class="fas fa-calendar-times"></i> Hari Libur Nasional di Minggu Ini:</strong>
                            <ul id="aroPhList" style="margin:6px 0 0 16px;padding:0;"></ul>
                        </div>
                    </div>
                    <div style="font-size:12px;font-weight:600;color:#334155;margin-bottom:6px;">Ringkasan Pola Shift:
                    </div>
                    <div style="overflow-x:auto;margin-bottom:12px;">
                        <table class="aro-day-table">
                            <thead>
                                <tr>
                                    <th>Hari</th>
                                    <th>Tipe</th>
                                    <th>Shift</th>
                                </tr>
                            </thead>
                            <tbody id="aroPreviewPatternBody"></tbody>
                        </table>
                    </div>
                    <div id="aroOverrideWarn" style="display:none;margin-bottom:12px;">
                        <div
                            style="background:#fffbeb;border:1px solid #fde68a;border-radius:6px;padding:10px 12px;font-size:12px;color:#92400e;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Mode Timpa aktif.</strong>
                            <span id="aroOverrideWarnCount"></span> roster yang sudah ada akan <strong>diperbarui</strong>.
                        </div>
                    </div>
                </div>
                <div class="m-foot" style="padding:12px 0 0;border-top:none;justify-content:space-between;">
                    <button class="btn-secondary-r" onclick="aroGoStep2()">
                        <i class="fas fa-arrow-left" style="margin-right:6px;"></i> Kembali
                    </button>
                    <button class="btn-success-r" id="aroGenerateBtn" onclick="aroDoGenerate()" disabled>
                        <i class="fas fa-lightning-bolt"></i> Generate Sekarang
                    </button>
                </div>
            </div>

            <div id="aroPanel4" class="aro-modal-body" style="display:none;">
                <div id="aroResultContent"></div>
                <div class="m-foot" style="padding:16px 0 0;border-top:none;justify-content:center;gap:10px;">
                    <button class="btn-secondary-r" onclick="aroReset()">
                        <i class="fas fa-redo" style="margin-right:6px;"></i> Generate Lagi
                    </button>
                    <button class="btn-primary-r" onclick="closeModal('modalAroOther');location.reload();">
                        <i class="fas fa-check" style="margin-right:6px;"></i> Selesai & Refresh
                    </button>
                </div>
            </div>

        </div>
    </div> --}}

    {{-- ════════════════════════════════════════════════════
     MODAL: Import Excel Roster
     ════════════════════════════════════════════════════ --}}
    <div class="m-overlay" id="modalImport">
        <div class="m-box" style="width:460px">
            <div class="m-head">
                <span>Import Roster (Excel)</span>
                <button onclick="closeModal('modalImport')">×</button>
            </div>
            <div class="m-body">
                <div
                    style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;padding:10px 12px;font-size:12px;color:#1e40af;margin-bottom:14px;line-height:1.6">
                    <i class="fas fa-info-circle"></i>
                    Format: kolom A = <strong>Employee Pengenal</strong>, B = Nama, C dst = shift per tanggal.
                    Kolom shift pertama = <strong>tanggal mulai</strong> yang dipilih di bawah.
                </div>

                <label class="f-label">Tanggal Mulai <span style="color:#ef4444">*</span>
                    <small style="color:#94a3b8;font-weight:400">(kolom shift pertama di Excel)</small>
                </label>
                @can('ManageRoster')
                    <input type="date" id="importStartDate" class="f-control mb-3" value="{{ $startDate }}">
                @endcan
                @can('ManageRosterSPVManager')
                    <input type="date" id="importStartDate" class="f-control mb-3" value="{{ $startDate }}"
                        min="{{ $startDate }}" max="{{ \Carbon\Carbon::parse($startDate)->addMonth()->toDateString() }}">
                @endcan
                <label class="f-label">File Excel <span style="color:#ef4444">*</span>
                    <small style="color:#94a3b8;font-weight:400">(.xlsx/.xls/.csv, maks 5MB)</small>
                </label>
                <input type="file" id="importFile" class="f-control mb-3" accept=".xlsx,.xls,.csv">

                {{-- Hasil import --}}
                <div id="importResult" style="display:none;font-size:12px;margin-top:8px;"></div>
            </div>
            <div class="m-foot">
                <button class="btn-secondary-r" onclick="closeModal('modalImport')">Cancel</button>
                <button class="btn-primary-r" id="importBtn" onclick="doImport()">
                    <i class="fas fa-file-import"></i> Import
                </button>
            </div>
        </div>
    </div>

    <div id="rosterToast"></div>

@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#start_date", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d F Y",
            locale: "id",
            defaultDate: (function() {
                let now = new Date();
                return new Date(now.getFullYear(), now.getMonth(), 26);
            })()
        });
    </script>
    <script>
        // ════════════════════════════════════════════════════════
        //  GLOBALS
        // ════════════════════════════════════════════════════════
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const CURRENT_STORE_ID = '{{ $storeId ?? '' }}';
        const CURRENT_STORE_NAME = '{{ $currentStoreName ?? '' }}';

        // ── Toast ──
        function toast(msg, ok = true) {
            const el = document.getElementById('rosterToast');
            el.textContent = msg;
            el.style.background = ok ? '#0f172a' : '#991b1b';
            el.style.display = 'block';
            setTimeout(() => el.style.display = 'none', 3500);
        }

        // ── Modal open/close ──
        function openModal(id) {
            document.getElementById(id).classList.add('open');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('open');
        }

        document.querySelectorAll('.m-overlay').forEach(el => {
            el.addEventListener('click', e => {
                if (e.target === el) el.classList.remove('open');
            });
        });

        // ── Toggles ──
        // function toggleShift() {
        // const dt = document.getElementById('mDayType').value;
        // document.getElementById('shiftWrap').style.display = dt === 'Work' ? 'block' : 'none';
        // document.getElementById('sickWrap').style.display = dt === 'Sick' ? 'block' : 'none';
        // document.getElementById('phCarryoverWrap').style.display = dt === 'Public Holiday' ? 'block' : 'none';
        // }
        function toggleShift() {
            const dt = document.getElementById('mDayType').value;
            document.getElementById('shiftWrap').style.display = dt === 'Work' ? 'block' : 'none';
            document.getElementById('sickWrap').style.display = dt === 'Sick' ? 'block' : 'none';
            document.getElementById('phCarryoverWrap').style.display = dt === 'Public Holiday' ? 'block' : 'none';
        }


        function toggleBulkShift() {
            document.getElementById('bulkShiftWrap').style.display =
                document.getElementById('bulkDayType').value === 'Work' ? 'block' : 'none';
        }

        function toggleSaturdayShift() {
            document.getElementById('saturdayShiftWrap').style.display =
                document.getElementById('bulkSaturdayShift').checked ? 'block' : 'none';
        }


        // ── Format tanggal DD/MM/YYYY ──
        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            return String(d.getDate()).padStart(2, '0') + '/' +
                String(d.getMonth() + 1).padStart(2, '0') + '/' +
                d.getFullYear();
        }

        // ── Legend filter ──
        document.querySelectorAll('.legend-filter').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    document.querySelectorAll('.legend-filter').forEach(function(cb) {
                        if (cb !== this) cb.checked = false;
                    }.bind(this));
                }
                applyLegendFilter();
            });
        });

        function applyLegendFilter() {
            const checkedTypes = [...document.querySelectorAll('.legend-filter:checked')].map(cb => cb.dataset.type);
            const showAll = checkedTypes.length === 0;

            document.querySelectorAll('.day-cell').forEach(function(cell) {
                const type = cell.dataset.cellType;
                const isToday = cell.dataset.isToday === '1';
                let show = false;

                if (showAll || type === 'empty') {
                    show = true;
                } else if (checkedTypes.includes('work') && type === 'work') {
                    show = true;
                } else if (checkedTypes.includes('off') && type === 'off') {
                    show = true;
                } else if (checkedTypes.includes('holiday') && type === 'holiday') {
                    show = true;
                } else if (checkedTypes.includes('leave') && type === 'leave') {
                    show = true;
                } else if (checkedTypes.includes('melahirkan') && type === 'melahirkan') {
                    show = true;
                } else if (checkedTypes.includes('weekend') && type === 'weekend') {
                    show = true;
                } else if (checkedTypes.includes('today') && isToday) {
                    show = true;
                } else if (checkedTypes.includes('sick') && type === 'sick') {
                    show = true;
                } else if (checkedTypes.includes('toiloff') && type === 'toiloff') {
                    show = true;
                }

                cell.style.visibility = show ? 'visible' : 'hidden';
                cell.style.pointerEvents = show ? 'auto' : 'none';
            });
        }

        // ════════════════════════════════════════════════════════
        //  HELPER: Filter opsi day_type berdasarkan status_employee
        //  PKWT            → semua opsi
        //  On Job Training → Work, Off, Public Holiday (sembunyikan Cuti)
        //  DW              → Work, Off saja (sembunyikan PH + Cuti)
        // ════════════════════════════════════════════════════════
        function applyDayTypeFilter(status, selectEl, noteEl) {
            const s = (status || '').toUpperCase().trim();
            // ✅ FIX 2: gunakan includes('JOB TRAINING') agar cocok dengan "On Job Training" dari DB
            const isDW = s === 'DW';
            const isOJT = s.includes('JOB TRAINING');

            selectEl.querySelectorAll('option').forEach(opt => {
                let hidden = false;
                if (opt.classList.contains('opt-ph') && isDW) hidden = true;
                if (opt.classList.contains('bulk-opt-ph') && isDW) hidden = true;
                if (opt.classList.contains('opt-cuti') && (isDW || isOJT)) hidden = true;
                if (opt.classList.contains('bulk-opt-cuti') && (isDW || isOJT)) hidden = true;
                if (opt.classList.contains('opt-sick') && isDW) hidden = true;
                opt.style.display = hidden ? 'none' : '';
            });

            // Reset ke Work jika nilai aktif tersembunyi
            const currentOpt = selectEl.options[selectEl.selectedIndex];
            if (currentOpt && currentOpt.style.display === 'none') {
                selectEl.value = 'Work';
            }

            // Tampilkan note
            if (noteEl) {
                if (isDW) {
                    noteEl.textContent = 'Karyawan DW — opsi Public Holiday dan Cuti tidak tersedia.';
                    noteEl.style.display = 'block';
                } else if (isOJT) {
                    noteEl.textContent = 'Karyawan On Job Training — opsi Cuti tidak tersedia.';
                    noteEl.style.display = 'block';
                } else {
                    noteEl.style.display = 'none';
                }
            }
        }

        // ════════════════════════════════════════════════════════
        //  CELL MODAL
        // ════════════════════════════════════════════════════════

        let _currentSickUrl = null;
        let _currentSickPath = null;

        function openCellModal(cell) {
            document.getElementById('mEmpId').value = cell.dataset.empId;
            document.getElementById('mEmpName').textContent = cell.dataset.empName;
            document.getElementById('mDate').textContent = cell.dataset.date;
            document.getElementById('mNotes').value = cell.dataset.notes || '';
            document.getElementById('mDeleteBtn').style.display = cell.dataset.hasRoster === '1' ? 'block' : 'none';

            const select = document.getElementById('mDayType');
            const noteEl = document.getElementById('mDayTypeNote');
            const status = cell.dataset.empStatus || '';

            applyDayTypeFilter(status, select, noteEl);

            const currentDayType = cell.dataset.dayType || 'Work';
            const targetOpt = Array.from(select.options).find(o => o.value === currentDayType && o.style.display !==
                'none');
            select.value = targetOpt ? currentDayType : 'Work';

            document.getElementById('mShiftId').value = cell.dataset.shiftId || '';

            // ── Sick attachment ──
            document.getElementById('mSickFile').value = '';
            _currentSickUrl = null;
            _currentSickPath = null;

            const sickPath = cell.dataset.sickAttachment || '';
            const sickExisting = document.getElementById('mSickExisting');
            const lihatBtn = document.getElementById('mSickLihatBtn');

            console.log('[SICK] dayType:', cell.dataset.dayType, '| hasRoster:', cell.dataset.hasRoster, '| sickPath:',
                sickPath);

            if (cell.dataset.dayType === 'Sick' && cell.dataset.hasRoster === '1' && sickPath) {
                _currentSickPath = sickPath;
                sickExisting.style.display = 'block';

                // Set onclick via JS langsung agar tidak tertimpa event lain
                lihatBtn.onclick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    previewSickAttachment();
                };
            } else {
                sickExisting.style.display = 'none';
                lihatBtn.onclick = null;
            }

            const saveBtn = document.getElementById('mSaveBtn');
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Save';

            const delBtn = document.getElementById('mDeleteBtn');
            delBtn.disabled = false;
            delBtn.innerHTML = '<i class="fas fa-trash"></i> Delete';

            loadPhCarryovers(cell.dataset.empId, cell.dataset.date);
            toggleShift();
            openModal('modalCell');
        }

        function previewSickAttachment() {
            if (!_currentSickPath) {
                toast('Tidak ada bukti sakit tersimpan.', false);
                return;
            }

            // Simpan isi body asli
            const mBody = document.querySelector('#modalCell .m-body');
            const mFoot = document.querySelector('#modalCell .m-foot');
            _savedModalBody = mBody.innerHTML;
            _savedModalFoot = mFoot.innerHTML;

            // Tampilkan loading
            mBody.innerHTML = `
        <div style="text-align:center;padding:10px">
            <i class="fas fa-spinner fa-spin" style="font-size:24px;color:#64748b"></i>
            <p style="color:#64748b;margin-top:8px">Memuat bukti sakit...</p>
        </div>`;

            mFoot.innerHTML = `
        <button class="btn-secondary-r" onclick="closeSickPreview()">
            <i class="fas fa-arrow-left"></i> Kembali
        </button>
        <a id="sickPreviewDownload" href="#" target="_blank" class="btn-primary-r">
            <i class="fas fa-download"></i> Download
        </a>`;

            fetch(`{{ route('roster.sick-attachment-url') }}?path=${encodeURIComponent(_currentSickPath)}`, {
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (!data.url) {
                        mBody.innerHTML = '<p style="color:#ef4444;text-align:center">Gagal memuat bukti sakit.</p>';
                        return;
                    }
                    document.getElementById('sickPreviewDownload').href = data.url;
                    const isPdf = _currentSickPath.toLowerCase().endsWith('.pdf');
                    if (isPdf) {
                        mBody.innerHTML =
                            `<iframe src="${data.url}" style="width:100%;height:450px;border:none;border-radius:6px"></iframe>`;
                    } else {
                        mBody.innerHTML =
                            `<img src="${data.url}" style="max-width:100%;max-height:450px;border-radius:6px;object-fit:contain">`;
                    }
                })
                .catch(() => {
                    mBody.innerHTML = '<p style="color:#ef4444;text-align:center">Terjadi kesalahan.</p>';
                });
        }

        let _savedModalBody = null;
        let _savedModalFoot = null;

        function closeSickPreview() {
            if (_savedModalBody) {
                document.querySelector('#modalCell .m-body').innerHTML = _savedModalBody;
                document.querySelector('#modalCell .m-foot').innerHTML = _savedModalFoot;
                _savedModalBody = null;
                _savedModalFoot = null;

                // Re-attach event listeners yang hilang setelah innerHTML di-replace
                document.getElementById('mSickLihatBtn').onclick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    previewSickAttachment();
                };
            }
        }

        function saveRoster() {
            const btn = document.getElementById('mSaveBtn');
            const dayType = document.getElementById('mDayType').value;
            const empId = document.getElementById('mEmpId').value;
            const date = document.getElementById('mDate').textContent;
            const phCarryId = document.getElementById('mPhCarryover').value;

            // Jika Public Holiday + ada carryover dipilih → validasi ulang ketersediaan
            if (dayType === 'Public Holiday' && phCarryId) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memvalidasi...';

                // Re-fetch carryover untuk pastikan masih available
                fetch(`{{ route('roster.phCarryovers') }}?employee_id=${empId}&date=${date}`, {
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        const stillAvailable = data.success && data.data.some(it => it.id === phCarryId);
                        if (!stillAvailable) {
                            toast('Saldo PH tukar sudah tidak tersedia. Silakan pilih ulang.', false);
                            // Refresh dropdown
                            loadPhCarryovers(empId, date);
                            document.getElementById('mPhCarryover').value = '';
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-save"></i> Save';
                            return;
                        }
                        // Lanjut submit
                        submitRoster(btn, dayType, phCarryId);
                    })
                    .catch(() => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-save"></i> Save';
                        toast('Gagal validasi saldo PH.', false);
                    });
                return;
            }

            submitRoster(btn, dayType, phCarryId);
        }

        function submitRoster(btn, dayType, phCarryId) {
            const sickFile = document.getElementById('mSickFile').files[0];
            const hasExisting = document.getElementById('mSickExisting').style.display === 'block';
            const shiftId = document.getElementById('mShiftId').value;

            // ── Validasi shift wajib saat Work ──
            if (dayType === 'Work' && !shiftId) {
                toast('Shift wajib dipilih untuk tipe Work.', false);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Save';
                return;
            }

            // ── Validasi sick attachment ──
            if (dayType === 'Sick' && !sickFile && !hasExisting) {
                toast('Bukti sakit wajib di-upload.', false);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Save';
                return;
            }

            // ── Validasi PH tanpa carryover ──
            const phCarrySelect = document.getElementById('mPhCarryover');
            const hasCarryOptions = phCarrySelect && phCarrySelect.options.length > 1;

            if (dayType === 'Public Holiday' && !phCarryId) {
                if (!hasCarryOptions) {
                    toast('Tanggal ini bukan Public Holiday dan tidak ada saldo PH Tukar tersedia.', false);
                } else {
                    toast('Tanggal ini bukan Public Holiday. Pilih saldo PH Tukar (Simpanan) terlebih dahulu.', false);
                }
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Save';
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

            const fd = new FormData();
            fd.append('employee_id', document.getElementById('mEmpId').value);
            fd.append('shift_id', shiftId || '');
            fd.append('date', document.getElementById('mDate').textContent);
            fd.append('day_type', dayType);
            fd.append('notes', document.getElementById('mNotes').value);
            if (sickFile) fd.append('sick_attachment', sickFile);

            // PH Tukar: kirim id saldo kalau dipilih
            if (dayType === 'Public Holiday' && phCarryId) {
                fd.append('ph_carryover_id', phCarryId);
            }

            fetch('{{ route('roster.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: fd,
                })
                .then(r => r.json().then(data => ({
                    ok: r.ok,
                    data
                })))
                .then(({
                    ok,
                    data
                }) => {
                    if (ok && data.success) {
                        btn.innerHTML = '<i class="fas fa-check"></i> Berhasil!';
                        toast('Schedule saved!');
                        closeModal('modalCell');
                        setTimeout(() => location.reload(), 700);
                    } else {
                        toast((data.message || 'Failed to save data.'), false);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-save"></i> Save';
                    }
                })
                .catch(() => {
                    toast('Terjadi kesalahan.', false);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save"></i> Save';
                });
        }



        function deleteRoster() {
            if (!confirm('Delete this schedule?')) return;
            const btn = document.getElementById('mDeleteBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

            fetch('{{ route('roster.destroy') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({
                        employee_id: document.getElementById('mEmpId').value,
                        date: document.getElementById('mDate').textContent,
                    }),
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        btn.innerHTML = '<i class="fas fa-check"></i> Berhasil!';
                        toast('Schedule deleted!');
                        closeModal('modalCell');
                        setTimeout(() => location.reload(), 700);
                    } else {
                        toast('Gagal menghapus.', false);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-trash"></i> Delete';
                    }
                })
                .catch(() => {
                    toast('Terjadi kesalahan.', false);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-trash"></i> Delete';
                });
        }

        // ════════════════════════════════════════════════════════
        //  BULK ASSIGN — filter day_type berdasarkan status karyawan
        // ════════════════════════════════════════════════════════
        function filterBulkDayType() {
            const selected = [...document.getElementById('bulkEmps').selectedOptions];
            const statuses = selected.map(o => (o.dataset.status || '').toUpperCase().trim());

            const hasDW = statuses.includes('DW');
            // ✅ FIX 3: gunakan .some() + includes('JOB TRAINING') agar cocok dengan "On Job Training" dari DB
            const hasOJT = statuses.some(s => s.includes('JOB TRAINING'));

            const select = document.getElementById('bulkDayType');
            const noteEl = document.getElementById('bulkDayTypeNote');

            select.querySelectorAll('option').forEach(opt => {
                let hidden = false;
                if (opt.classList.contains('bulk-opt-ph') && hasDW) hidden = true;
                if (opt.classList.contains('bulk-opt-cuti') && (hasDW || hasOJT)) hidden = true;
                opt.style.display = hidden ? 'none' : '';
            });

            // Reset ke Work jika nilai aktif tersembunyi
            const currentOpt = select.options[select.selectedIndex];
            if (currentOpt && currentOpt.style.display === 'none') {
                select.value = 'Work';
                toggleBulkShift();
            }

            // Tampilkan note
            if (hasDW) {
                noteEl.textContent = 'Terdapat karyawan DW — opsi Public Holiday dan Cuti tidak tersedia.';
                noteEl.style.display = 'block';
            } else if (hasOJT) {
                noteEl.textContent = 'Terdapat karyawan On Job Training — opsi Cuti tidak tersedia.';
                noteEl.style.display = 'block';
            } else {
                noteEl.style.display = 'none';
            }
        }

        document.getElementById('bulkEmps').addEventListener('change', filterBulkDayType);

        function saveBulk() {
            const selected = [...document.getElementById('bulkEmps').selectedOptions].map(o => o.value);
            if (!selected.length) {
                toast('Choose at least 1 employee.', false);
                return;
            }
            const shiftId = document.getElementById('bulkShift').value;
            if (!shiftId) {
                toast('Pilih shift terlebih dahulu.', false);
                return;
            }


            const saturdayShiftChecked = document.getElementById('bulkSaturdayShift').checked;
            const saturdayShiftId = document.getElementById('bulkSaturdayShiftId').value;
            if (saturdayShiftChecked && !saturdayShiftId) {
                toast('Pilih shift untuk hari Sabtu.', false);
                return;
            }

            const btn = document.querySelector('#modalBulk .btn-primary-r');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

            fetch('{{ route('roster.bulkAssign') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({
                        employee_ids: selected,
                        shift_id: document.getElementById('bulkShift').value || null,
                        start_date: document.getElementById('bulkStart').value,
                        end_date: document.getElementById('bulkEnd').value,
                        day_type: document.getElementById('bulkDayType').value,
                        skip_weekend: document.getElementById('bulkSkipWeekend').checked,
                        saturday_shift: saturdayShiftChecked,
                        saturday_shift_id: saturdayShiftId || null,
                    }),
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        btn.innerHTML = '<i class="fas fa-check"></i> Berhasil!';
                        toast('success' + data.message);
                        closeModal('modalBulk');
                        setTimeout(() => location.reload(), 700);
                    } else {
                        toast('failed' + (data.message || 'Gagal memproses.'), false);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-calendar-check"></i> Assign';
                    }
                })
                .catch(() => {
                    toast('Terjadi kesalahan.', false);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-calendar-check"></i> Assign';
                });
        }

        function openImportModal() {
            document.getElementById('importFile').value = '';
            document.getElementById('importResult').style.display = 'none';
            document.getElementById('importResult').innerHTML = '';
            const btn = document.getElementById('importBtn');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-file-import"></i> Import';
            openModal('modalImport');
        }

        function downloadTemplate() {
            if (!CURRENT_STORE_ID) {
                toast('Pilih store di filter atas dulu.', false);
                return;
            }
            const start = document.querySelector('input[name="start_date"]')?.value || '{{ $startDate }}';
            const end = document.querySelector('input[name="end_date"]')?.value || '{{ $endDate }}';

            const url = `{{ route('roster.template') }}?store_id=${CURRENT_STORE_ID}&start_date=${start}&end_date=${end}`;
            window.location.href = url;
        }

        function doImport() {
            const startDate = document.getElementById('importStartDate').value;
            const file = document.getElementById('importFile').files[0];

            if (!startDate) {
                toast('Tanggal mulai wajib diisi.', false);
                return;
            }
            if (!file) {
                toast('Pilih file Excel dulu.', false);
                return;
            }
            if (!CURRENT_STORE_ID) {
                toast('Pilih store di filter atas dulu.', false);
                return;
            }

            const btn = document.getElementById('importBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

            const fd = new FormData();
            fd.append('store_id', CURRENT_STORE_ID);
            fd.append('start_date', startDate);
            fd.append('file', file);

            fetch('{{ route('roster.import') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: fd,
                })
                .then(r => r.json())
                .then(data => {
                    const resultEl = document.getElementById('importResult');
                    resultEl.style.display = 'block';

                    if (data.success) {
                        let html = `<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;padding:10px;color:#166534;">
                    <strong><i class="fas fa-check-circle"></i> ${data.message}</strong>`;
                        if (data.errors && data.errors.length) {
                            html +=
                                `<div style="margin-top:8px;color:#92400e;"><strong>Baris dilewati:</strong><ul style="margin:4px 0 0 16px;padding:0;">`;
                            data.errors.forEach(e => html += `<li>${e}</li>`);
                            html += `</ul></div>`;
                        }
                        html += `</div>`;
                        resultEl.innerHTML = html;

                        toast('Import selesai!');
                        btn.innerHTML = '<i class="fas fa-check"></i> Selesai';
                        setTimeout(() => location.reload(), 7000);
                    } else {
                        resultEl.innerHTML = `<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:10px;color:#991b1b;">
                    <i class="fas fa-exclamation-circle"></i> ${data.message || 'Gagal import.'}
                </div>`;
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-file-import"></i> Import';
                    }
                })
                .catch(() => {
                    toast('Terjadi kesalahan.', false);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-file-import"></i> Import';
                });
        }


        // ════════════════════════════════════════════════════════
        //  COPY ROSTER
        // ════════════════════════════════════════════════════════
        function confirmCopyRoster() {
            Swal.fire({
                icon: 'warning',
                iconColor: '#f59e0b',
                title: 'Perhatian!',
                html: `<div style="text-align:left;padding:8px 0;line-height:1.7;font-size:14px;color:#374151">
                Mohon Dicek Kembali Setelah Mengcopy Roster Karena Mengikuti Roster Pada Bulan Ini, Agar Karyawan Yang Mengambil
                <strong>Cuti</strong>, <strong>Libur</strong>, Dan lain-lain Tidak Terinput Kembali Di Roster Yang Akan Di Gunakan Pada Bulan Berikutnya.
               </div>`,
                showCancelButton: true,
                confirmButtonColor: '#1d4ed8',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fas fa-copy"></i> Mengerti, Lanjutkan',
                cancelButtonText: 'Batal',
                focusCancel: true
            }).then(result => {
                if (result.isConfirmed) openModal('modalCopy');
            });
        }

        function saveCopy() {
            const sourceStart = document.getElementById('copySourceStart').value;
            const sourceEnd = document.getElementById('copySourceEnd').value;
            const targetStart = document.getElementById('copyTargetStart').value;
            const targetEnd = document.getElementById('copyTargetEnd').value;

            if (!sourceStart || !sourceEnd || !targetStart || !targetEnd) {
                toast('Semua tanggal wajib diisi.', false);
                return;
            }
            if (sourceEnd < sourceStart) {
                toast('Sumber End Date tidak boleh sebelum Sumber Start Date.', false);
                return;
            }
            if (targetEnd < targetStart) {
                toast('Target End Date tidak boleh sebelum Target Start Date.', false);
                return;
            }

            const btn = document.querySelector('#modalCopy .btn-primary-r');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

            fetch('{{ route('roster.copyRoster') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({
                        source_start: sourceStart,
                        source_end: sourceEnd,
                        target_start: targetStart,
                        target_end: targetEnd,
                        store_id: CURRENT_STORE_ID
                    }),
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        btn.innerHTML = '<i class="fas fa-check"></i> Berhasil!';
                        toast('success' + data.message);
                        closeModal('modalCopy');
                        setTimeout(() => location.reload(), 700);
                    } else {
                        toast('Gagal memproses.', false);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-copy"></i> Copy';
                    }
                })
                .catch(() => {
                    toast('Terjadi kesalahan.', false);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-copy"></i> Copy';
                });
        }


        // ════════════════════════════════════════════════════════
        //  BULK DELETE
        // ════════════════════════════════════════════════════════
        function saveBulkDelete() {
            const selected = [...document.getElementById('deleteEmps').selectedOptions].map(o => o.value);
            if (!selected.length) {
                toast('Pilih minimal 1 karyawan.', false);
                return;
            }
            if (!confirm('Yakin ingin menghapus semua jadwal yang dipilih?')) return;

            const btn = document.querySelector('#modalBulkDelete .btn-danger-r');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

            fetch('{{ route('roster.bulkDelete') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({
                        employee_ids: selected,
                        start_date: document.getElementById('deleteStart').value,
                        end_date: document.getElementById('deleteEnd').value,
                    }),
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        btn.innerHTML = '<i class="fas fa-check"></i> Berhasil!';
                        toast('delete' + data.message);
                        closeModal('modalBulkDelete');
                        setTimeout(() => location.reload(), 700);
                    } else {
                        toast('Gagal menghapus.', false);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-trash"></i> Hapus';
                    }
                })
                .catch(() => {
                    toast('Terjadi kesalahan.', false);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-trash"></i> Hapus';
                });
        }

        // ════════════════════════════════════════════════════════
        //  AUTO GENERATE ROSTER — 3 STORE STATIC
        // ════════════════════════════════════════════════════════
        function confirmAutoGenerate() {
            Swal.fire({
                icon: 'info',
                iconColor: '#16a34a',
                title: 'Auto Generate Roster',
                html: `<div style="text-align:left;padding:8px 0;line-height:1.7;font-size:14px;color:#374151">
                Sistem akan men-generate roster otomatis untuk karyawan di
                <strong>Head Office, Holding, dan Distribution Center</strong> dengan pola jadwal:
                <ul style="margin:10px 0 10px 18px;padding:0">
                    <li>Senin - Jumat: <strong>sesuai shift yang dipilih</strong></li>
                    <li>Sabtu: <strong>sesuai shift yang dipilih</strong></li>
                    <li>Minggu: <strong>Off</strong></li>
                    <li>Public Holiday: sesuai agama karyawan (PKWT & On Job Training)</li>
                    <li>Karyawan DW: tidak mendapat Public Holiday</li>
                </ul>
                Roster yang sudah ada <strong>tidak akan ditimpa</strong>.
               </div>`,

                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fas fa-eye"></i> Lihat Preview',
                cancelButtonText: 'Batal',
                focusCancel: true
            }).then(result => {
                if (result.isConfirmed) {
                    document.getElementById('ag-start-date').value = '';
                    document.getElementById('ag-end-date').value = '';
                    $('#ag-shift-weekday').val('').trigger('change');
                    $('#ag-shift-saturday').val('').trigger('change');
                    ['ag-period', 'ag-employees', 'ag-hindu', 'ag-non-hindu', 'ag-ph', 'ag-estimated-created',
                        'ag-estimated-skipped'
                    ]
                    .forEach(id => document.getElementById(id).textContent = '-');
                    const genBtn = document.getElementById('btnExecuteAutoGenerate');
                    genBtn.disabled = false;
                    genBtn.innerHTML = '<i class="fas fa-magic"></i> Generate';
                    openModal('modalAutoGenerate');
                    $('.ag-shift-select2').select2({
                        placeholder: '-- Pilih Shift --',
                        allowClear: false,
                        width: '100%',
                        dropdownParent: $('#modalAutoGenerate .m-body')
                    });

                    $('#modalAutoGenerate .m-body').on('scroll', function() {
                        $('.ag-shift-select2').select2('close');
                    });

                    loadAutoGeneratePreview();
                }
            });
        }

        function loadAutoGeneratePreview() {
            const startDate = document.getElementById('ag-start-date').value;
            const endDate = document.getElementById('ag-end-date').value;
            let url = '{{ route('roster.auto-generate.preview') }}';

            if (startDate && endDate) {
                url += `?start_date=${startDate}&end_date=${endDate}`;
            } else if (startDate || endDate) {
                toast('Isi Start Date dan End Date keduanya, atau kosongkan keduanya.', false);
                return;
            }

            ['ag-period', 'ag-employees', 'ag-hindu', 'ag-non-hindu', 'ag-ph', 'ag-estimated-created',
                'ag-estimated-skipped'
            ]
            .forEach(id => document.getElementById(id).textContent = '...');

            fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const p = data.preview;
                        document.getElementById('ag-period').textContent = formatDate(p.start_date) + ' s/d ' +
                            formatDate(p.end_date);
                        document.getElementById('ag-employees').textContent = p.total_employees + ' karyawan';
                        document.getElementById('ag-hindu').textContent = (p.employees_by_religion?.Hindu ?? 0) +
                            ' orang';
                        document.getElementById('ag-non-hindu').textContent = (p.employees_by_religion?.['Non Hindu'] ??
                            0) + ' orang';
                        const phByType = p.public_holidays_by_type || {};
                        const phTotal = Object.values(phByType).reduce((s, n) => s + n, 0);
                        const phDetail = [];
                        if (phByType.Hindu) phDetail.push('Hindu: ' + phByType.Hindu);
                        if (phByType['Non Hindu']) phDetail.push('Non Hindu: ' + phByType['Non Hindu']);
                        if (phByType.All) phDetail.push('All: ' + phByType.All);
                        document.getElementById('ag-ph').textContent = phTotal + (phDetail.length ? ' (' + phDetail
                            .join(', ') + ')' : '');
                        document.getElementById('ag-estimated-created').textContent = (p.estimated_created ?? p
                            .estimated_rows ?? 0) + ' roster';
                        document.getElementById('ag-estimated-skipped').textContent = (p.estimated_skipped ?? 0) +
                            ' roster';
                        const genBtn = document.getElementById('btnExecuteAutoGenerate');
                        genBtn.disabled = false;
                        genBtn.innerHTML = '<i class="fas fa-magic"></i> Generate';
                    } else {
                        toast('error' + (data.message || 'Gagal load preview'), false);
                        ['ag-period', 'ag-employees', 'ag-hindu', 'ag-non-hindu', 'ag-ph', 'ag-estimated-created',
                            'ag-estimated-skipped'
                        ]
                        .forEach(id => document.getElementById(id).textContent = '-');
                    }
                })
                .catch(err => {
                    toast('Terjadi kesalahan saat load preview.', false);
                    console.error(err);
                });
        }


        function executeAutoGenerate() {
            const startDate = document.getElementById('ag-start-date').value;
            const endDate = document.getElementById('ag-end-date').value;
            const shiftWeekday = document.getElementById('ag-shift-weekday').value;
            const shiftSaturday = document.getElementById('ag-shift-saturday').value;

            // Validasi DULU sebelum disable button
            if (!shiftWeekday) {
                toast('Pilih shift untuk Senin - Jumat terlebih dahulu.', false);
                return;
            }
            if (!shiftSaturday) {
                toast('Pilih shift untuk Sabtu terlebih dahulu.', false);
                return;
            }

            const btn = document.getElementById('btnExecuteAutoGenerate');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

            const payload = {};
            if (startDate && endDate) {
                payload.start_date = startDate;
                payload.end_date = endDate;
            }

            // Tambahkan shift ke payload
            payload.shift_weekday_id = shiftWeekday;
            payload.shift_saturday_id = shiftSaturday;

            fetch('{{ route('roster.auto-generate') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload),
                })
                .then(r => r.json())
                .then(data => {
                    closeModal('modalAutoGenerate');
                    if (data.success) {
                        const s = data.summary;
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            html: `<div style="text-align:left;padding:8px 0;font-size:13px;color:#374151;line-height:1.8">
                        <div style="margin-bottom:10px">
                            <strong style="color:#16a34a">${s.created}</strong> roster baru di-generate
                            ${s.skipped>0?', <strong style="color:#f59e0b">'+s.skipped+'</strong> dilewati (sudah ada)':''}
                        </div>
                        <div style="background:#f0fdf4;border-radius:6px;padding:10px;font-size:12px">
                            <div style="font-weight:600;margin-bottom:6px">Breakdown:</div>
                            <div>• Work: ${s.breakdown_by_type.Work}</div>
                            <div>• Off: ${s.breakdown_by_type.Off}</div>
                            <div>• Public Holiday: ${s.breakdown_by_type['Public Holiday']}</div>
                        </div>
                        <div style="margin-top:10px;font-size:12px;color:#64748b">
                            Periode: ${formatDate(s.period.start)} s/d ${formatDate(s.period.end)}
                        </div>
                      </div>`,
                            confirmButtonColor: '#16a34a',
                            confirmButtonText: 'OK'
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message || 'Terjadi kesalahan',
                            confirmButtonColor: '#dc2626'
                        });
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-magic"></i> Generate';
                    }
                })
                .catch(err => {
                    closeModal('modalAutoGenerate');
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: 'Tidak dapat menghubungi server',
                        confirmButtonColor: '#dc2626'
                    });
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-magic"></i> Generate';
                    console.error(err);
                });
        }

        // ════════════════════════════════════════════════════════
        //  AUTO GENERATE ROSTER — STORE LAIN (PER MINGGU)
        // ════════════════════════════════════════════════════════
        const ARO_DAYS = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        const ARO_SHIFTS_DATA = @json($shifts ?? []);

        let aroState = {
            weekStart: null,
            dayPattern: ARO_DAYS.map(() => ({
                day_type: 'Off',
                shift_id: null
            })),
            previewData: null,
            availableShifts: ARO_SHIFTS_DATA,
        };


        function openAroModal() {
            aroReset();
            document.getElementById('aroStoreName').textContent = CURRENT_STORE_NAME || '-';
            document.getElementById('aroStoreEmpCount').textContent = document.querySelectorAll('.day-cell').length > 0 ?
                '{{ $employees->count() ?? 0 }} karyawan aktif' :
                '—';
            openModal('modalAroOther');
        }

        function aroSetStep(n) {
            [1, 2, 3, 4].forEach(i => {
                const panel = document.getElementById('aroPanel' + i);
                if (panel) panel.style.display = (i === n) ? 'block' : 'none';
            });
            [1, 2, 3].forEach(i => {
                const dot = document.getElementById('aroStepDot' + i);
                const line = document.getElementById('aroStepLine' + i);
                if (dot) {
                    dot.classList.toggle('active', i === n);
                    dot.classList.toggle('done', i < n);
                }
                if (line) {
                    line.classList.toggle('done', i < n);
                }
            });
        }

        function aroOnWeekChange() {
            const val = document.getElementById('aroWeekStart').value;
            const warn = document.getElementById('aroWeekDayWarn');
            if (!val) {
                aroState.weekStart = null;
                aroCheckStep1();
                return;
            }

            const date = new Date(val + 'T00:00:00');
            const dow = date.getDay();

            if (dow !== 1) {
                warn.style.display = 'block';
                const diff = (dow === 0) ? -6 : -(dow - 1);
                date.setDate(date.getDate() + diff);
                document.getElementById('aroWeekStart').value = date.toISOString().split('T')[0];
            } else {
                warn.style.display = 'none';
            }

            aroState.weekStart = date.toISOString().split('T')[0];

            const end = new Date(date);
            end.setDate(date.getDate() + 6);
            const fmt = d => d.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
            document.getElementById('aroWeekRangeInfo').textContent = fmt(date) + ' – ' + fmt(end);

            aroCheckStep1();
        }

        function aroCheckStep1() {
            document.getElementById('aroStep1Next').disabled = !aroState.weekStart;
        }

        function aroToggleEmpSelect() {
            const val = document.querySelector('input[name="aroApplyTo"]:checked').value;
            document.getElementById('aroEmpSelectWrap').style.display = val === 'selected' ? 'block' : 'none';
        }

        function aroGoStep1() {
            aroSetStep(1);
        }

        function aroGoStep2() {
            if (!aroState.weekStart) return;
            aroRenderDayPattern();
            aroSetStep(2);
        }

        function aroGoStep3() {
            aroLoadPreview();
            aroSetStep(3);
        }

        function aroRenderDayPattern() {
            const tbody = document.getElementById('aroDayPatternBody');
            tbody.innerHTML = '';

            const shiftOptions = aroState.availableShifts.map(s =>
                `<option value="${s.id}">${s.shift_name} (${s.time_label || (s.start_time.substring(0,5) + '–' + s.end_time.substring(0,5))})</option>`
            ).join('');

            ARO_DAYS.forEach((day, i) => {
                const isWeekend = i >= 5;
                const pat = aroState.dayPattern[i];

                tbody.innerHTML += `
        <tr class="${isWeekend ? 'weekend-row' : ''}">
            <td><span style="font-weight:600;color:${isWeekend?'#b91c1c':'#0f172a'};">${day}</span></td>
            <td>
                <select class="f-control" style="padding:5px 8px;font-size:12px;" id="aroDt${i}" onchange="aroOnDtChange(${i})">
                    <option value="Work"           ${pat.day_type==='Work'?'selected':''}>Work</option>
                    <option value="Off"            ${pat.day_type==='Off'?'selected':''}>Off</option>
                    <option value="Public Holiday" ${pat.day_type==='Public Holiday'?'selected':''}>Public Holiday</option>
                </select>
            </td>
            <td>
                <select class="f-control" style="padding:5px 8px;font-size:12px;" id="aroSh${i}" onchange="aroOnShChange(${i})" ${pat.day_type!=='Work'?'disabled':''}>
                    <option value="">— Pilih Shift —</option>
                    ${shiftOptions}
                </select>
            </td>
            <td style="font-size:11px;color:#64748b;" id="aroShInfo${i}">—</td>
        </tr>`;
            });

            aroState.dayPattern.forEach((pat, i) => {
                if (pat.shift_id) {
                    const sel = document.getElementById(`aroSh${i}`);
                    if (sel) {
                        sel.value = pat.shift_id;
                        aroUpdateShiftInfo(i);
                    }
                }
            });
        }

        function aroOnDtChange(i) {
            const dt = document.getElementById(`aroDt${i}`).value;
            const sel = document.getElementById(`aroSh${i}`);
            aroState.dayPattern[i].day_type = dt;
            if (dt !== 'Work') {
                sel.disabled = true;
                sel.value = '';
                aroState.dayPattern[i].shift_id = null;
                document.getElementById(`aroShInfo${i}`).textContent = '—';
            } else {
                sel.disabled = false;
            }
        }

        function aroOnShChange(i) {
            const val = document.getElementById(`aroSh${i}`).value;
            aroState.dayPattern[i].shift_id = val ? parseInt(val) : null;
            aroUpdateShiftInfo(i);
        }

        function aroUpdateShiftInfo(i) {
            const shiftId = aroState.dayPattern[i].shift_id;
            const shift = aroState.availableShifts.find(s => s.id == shiftId);
            const el = document.getElementById(`aroShInfo${i}`);
            if (el) el.textContent = shift ? (shift.time_label || shift.start_time.substring(0, 5) + ' – ' + shift.end_time.substring(0, 5)) :
                '—';
        }

        function aroQuickPattern(type) {
            const firstShift = aroState.availableShifts[0];
            if (type === 'default') {
                aroState.dayPattern = ARO_DAYS.map((_, i) => ({
                    day_type: (i < 6) ? 'Work' : 'Off',
                    shift_id: (i < 6) ? (firstShift?.id || null) : null,
                }));
            } else {
                aroState.dayPattern = ARO_DAYS.map(() => ({
                    day_type: 'Off',
                    shift_id: null
                }));
            }
            aroRenderDayPattern();
        }

        async function aroLoadPreview() {
            document.getElementById('aroPreviewLoading').style.display = 'block';
            document.getElementById('aroPreviewContent').style.display = 'none';
            document.getElementById('aroGenerateBtn').disabled = true;

            try {
                const url =
                    `/roster/auto-generate/other/preview?store_id=${CURRENT_STORE_ID}&week_start=${aroState.weekStart}`;
                const resp = await fetch(url);
                const data = await resp.json();

                if (!data.success) {
                    document.getElementById('aroPreviewLoading').innerHTML =
                        `<div style="color:#dc2626;font-size:13px;"><i class="fas fa-exclamation-circle"></i> ${data.message}</div>`;
                    return;
                }

                aroState.previewData = data.preview;
                aroRenderPreview(data.preview);
            } catch (err) {
                document.getElementById('aroPreviewLoading').innerHTML =
                    `<div style="color:#dc2626;font-size:13px;"><i class="fas fa-exclamation-circle"></i> Gagal memuat preview: ${err.message}</div>`;
            }
        }

        function aroRenderPreview(p) {
            const override = document.getElementById('aroOverride').checked;

            document.getElementById('aroPreviewCards').innerHTML = `
        <div class="aro-result-card aro-card-created">
            <div class="val">${p.will_be_created}</div>
            <div class="lbl">Akan Dibuat</div>
        </div>
        <div class="aro-result-card aro-card-updated" style="background:${override?'#eff6ff':'#f8fafc'}">
            <div class="val" style="color:${override?'#1d4ed8':'#94a3b8'}">${p.existing_rosters}</div>
            <div class="lbl" style="color:${override?'#60a5fa':'#cbd5e1'}">${override?'Akan Diperbarui':'Dilewati'}</div>
        </div>
        <div class="aro-result-card aro-card-ph">
            <div class="val">${p.public_holidays ? p.public_holidays.length : 0}</div>
            <div class="lbl">Hari Libur</div>
        </div>
        <div class="aro-result-card" style="background:#f0f9ff;">
            <div class="val" style="color:#0369a1;">${p.total_employees}</div>
            <div class="lbl" style="color:#7dd3fc;">Karyawan</div>
        </div>`;

            const phSection = document.getElementById('aroPhSection');
            const phList = document.getElementById('aroPhList');
            if (p.public_holidays && p.public_holidays.length > 0) {
                phList.innerHTML = p.public_holidays.map(ph =>
                    `<li>${ph.date}: ${ph.remark} <span style="background:#fde047;color:#854d0e;border-radius:3px;padding:1px 5px;font-size:10px;">${ph.type}</span></li>`
                ).join('');
                phSection.style.display = 'block';
            } else {
                phSection.style.display = 'none';
            }

            const tbody = document.getElementById('aroPreviewPatternBody');
            tbody.innerHTML = '';
            aroState.dayPattern.forEach((pat, i) => {
                const shift = aroState.availableShifts.find(s => s.id == pat.shift_id);
                const color = pat.day_type === 'Work' ? '#1d4ed8' : pat.day_type === 'Off' ? '#64748b' : '#854d0e';
                tbody.innerHTML += `
            <tr>
                <td style="font-weight:600;">${ARO_DAYS[i]}</td>
                <td><span style="color:${color};font-weight:600;">${pat.day_type}</span></td>
                <td style="color:#64748b;font-size:11px;">${shift ? shift.shift_name+' ('+(shift.time_label || shift.start_time.substring(0,5)+'–'+shift.end_time.substring(0,5))+')' : '—'}</td>
            </tr>`;
            });

            const overrideWarn = document.getElementById('aroOverrideWarn');
            if (override && p.existing_rosters > 0) {
                document.getElementById('aroOverrideWarnCount').textContent = p.existing_rosters;
                overrideWarn.style.display = 'block';
            } else {
                overrideWarn.style.display = 'none';
            }

            document.getElementById('aroPreviewLoading').style.display = 'none';
            document.getElementById('aroPreviewContent').style.display = 'block';
            document.getElementById('aroGenerateBtn').disabled = false;
        }

        async function aroDoGenerate() {
            const btn = document.getElementById('aroGenerateBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';

            const applyTo = document.querySelector('input[name="aroApplyTo"]:checked').value;
            const empIds = applyTo === 'selected' ?
                Array.from(document.getElementById('aroEmpMultiSelect').selectedOptions).map(o => parseInt(o.value)) :
                [];

            const payload = {
                store_id: CURRENT_STORE_ID,
                week_start: aroState.weekStart,
                apply_to: applyTo,
                employee_ids: empIds,
                override_existing: document.getElementById('aroOverride').checked,
                day_pattern: aroState.dayPattern,
            };

            try {
                const resp = await fetch('/roster/auto-generate/other', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify(payload),
                });
                const data = await resp.json();
                aroShowResult(data);
                aroSetStep(4);
            } catch (err) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-lightning-bolt"></i> Generate Sekarang';
                toast('Terjadi kesalahan: ' + err.message, false);
            }
        }

        function aroShowResult(data) {
            const el = document.getElementById('aroResultContent');
            if (data.success) {
                const s = data.summary;
                el.innerHTML = `
            <div style="text-align:center;margin-bottom:16px;">
                <div style="font-size:48px;">Success</div>
                <h5 style="font-weight:700;margin-top:8px;color:#16a34a;">Generate Berhasil!</h5>
                <p style="color:#64748b;font-size:13px;">${data.message}</p>
            </div>
            <div class="aro-result-grid">
                <div class="aro-result-card aro-card-created"><div class="val">${s.created}</div><div class="lbl">Dibuat</div></div>
                <div class="aro-result-card aro-card-updated"><div class="val">${s.updated}</div><div class="lbl">Diperbarui</div></div>
                <div class="aro-result-card aro-card-skipped"><div class="val">${s.skipped}</div><div class="lbl">Dilewati</div></div>
                <div class="aro-result-card aro-card-ph"><div class="val">${s.public_holidays}</div><div class="lbl">Hari Libur</div></div>
            </div>
            <div style="background:#f8fafc;border-radius:6px;padding:10px 12px;font-size:12px;color:#475569;margin-top:12px;">
                <strong>Breakdown:</strong>
                Work: ${s.breakdown_by_type.Work} &nbsp;|&nbsp;
                Off: ${s.breakdown_by_type.Off} &nbsp;|&nbsp;
                Public Holiday: ${s.breakdown_by_type['Public Holiday']}
            </div>`;
            } else {
                el.innerHTML = `
            <div style="text-align:center;">
                <div style="font-size:48px;">Failed</div>
                <h5 style="font-weight:700;margin-top:8px;color:#dc2626;">Generate Gagal</h5>
                <p style="color:#64748b;font-size:13px;">${data.message}</p>
            </div>`;
            }
        }

        function aroReset() {
            aroState.weekStart = null;
            aroState.dayPattern = ARO_DAYS.map(() => ({
                day_type: 'Off',
                shift_id: null
            }));
            aroState.previewData = null;

            document.getElementById('aroWeekStart').value = '';
            document.getElementById('aroWeekRangeInfo').textContent = '';
            document.getElementById('aroWeekDayWarn').style.display = 'none';
            document.getElementById('aroOverride').checked = false;
            document.getElementById('aroStep1Next').disabled = true;
            document.getElementById('aroEmpSelectWrap').style.display = 'none';
            document.querySelector('input[name="aroApplyTo"][value="all"]').checked = true;

            document.getElementById('aroPreviewLoading').style.display = 'block';
            document.getElementById('aroPreviewLoading').innerHTML = `
        <i class="fas fa-spinner fa-spin fa-2x" style="color:#1d4ed8;"></i>
        <div style="margin-top:10px;font-size:13px;color:#64748b;">Memuat preview...</div>`;
            document.getElementById('aroPreviewContent').style.display = 'none';

            aroSetStep(1);
        }

        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
    {{-- untuk tracking history --}}

    <script>
        function loadHistory() {
            const start = document.getElementById('historyStart').value;
            const end = document.getElementById('historyEnd').value;
            const search = document.getElementById('historySearch').value;
            const storeId = document.getElementById('historyStore').value; // ← ambil dari select/hidden

            const resultEl = document.getElementById('historyResult');

            if (!start || !end) {
                toast('Start Date dan End Date wajib diisi.', false);
                return;
            }

            resultEl.innerHTML = '<p class="pr-empty-note"><i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Memuat histori</p>';

            let url = `{{ route('roster.history') }}?start_date=${start}&end_date=${end}&search=${encodeURIComponent(search)}`;
            // Kirim store_id hanya kalau ada (Admin bisa kosong = semua store)
            if (storeId) url += `&store_id=${storeId}`;
            fetch(url)
                .then(r => r.json())
                .then(data => {
                    if (!data.success || !data.data.length) {
                        resultEl.innerHTML = '<p class="pr-empty-note">Tidak ada roster di rentang dan nama itu.</p>';
                        return;
                    }

                    const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
                    const typeClass = {
                        'Work': 'is-work', 'Off': 'is-off', 'TOIL Off': 'is-toil', 'Public Holiday': 'is-holiday',
                        'Leave': 'is-leave', 'Cuti Melahirkan': 'is-leave', 'Sick': 'is-sick',
                    };
                    let html = `<table class="roster-table">
                    <thead>
                        <tr>
                            <th class="col-emp">Karyawan</th>
                            <th>Tanggal</th>
                            <th>Tipe hari</th>
                            <th>Shift</th>
                            <th>Jam</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>`;

                    data.data.forEach(emp => {
                        emp.rosters.forEach((r, i) => {
                            html += `<tr>`;
                            if (i === 0) {
                                html += `<th scope="row" class="col-emp" rowspan="${emp.rosters.length}" style="vertical-align:top;">
                            <span class="emp-name">${esc(emp.employee_name)}</span>
                            <span class="emp-meta">${esc([emp.position, emp.department].filter(v => v && v !== '-').join(', ') || '-')}</span>
                            ${emp.status_employee ? `<span class="emp-status status-pkwt">${esc(emp.status_employee)}</span>` : ''}
                        </th>`;
                            }
                            html += `
                        <td>${esc(r.date)}</td>
                        <td><span class="rs-type ${typeClass[r.day_type] || 'is-off'}">${esc(r.day_type)}</span></td>
                        <td>${esc(r.shift_name || '-')}</td>
                        <td>${r.time_label ? esc(r.time_label) : (r.start_time && r.end_time ? esc(r.start_time + '–' + r.end_time) : '-')}</td>
                        <td>${esc(r.notes || '-')}</td>
                    </tr>`;
                        });
                    });
                    html += `</tbody></table>`;
                    resultEl.innerHTML = html;
                })
                .catch(() => {
                    resultEl.innerHTML = '<p class="pr-empty-note">Gagal memuat histori. Coba lagi.</p>';
                });
        }

        function resetHistory() {
            document.getElementById('historyStart').value = '';
            document.getElementById('historyEnd').value = '';
            document.getElementById('historySearch').value = '';
            document.getElementById('historyResult').innerHTML =
                '<p class="pr-empty-note">Isi rentang tanggal lalu klik Cari.</p>';
        }

        function exportHistory() {
            const start = document.getElementById('historyStart').value;
            const end = document.getElementById('historyEnd').value;
            const search = document.getElementById('historySearch').value;
            const storeId = document.getElementById('historyStore').value;

            if (!start || !end) {
                toast('Start Date dan End Date wajib diisi.', false);
                return;
            }

            let url = `{{ route('roster.history.export') }}?start_date=${start}&end_date=${end}&search=${search}`;
            if (storeId) url += `&store_id=${storeId}`;

            // Langsung download via window.location
            window.location.href = url;
        }

        // Ambil daftar PH simpanan untuk karyawan + tanggal aktif
        function loadPhCarryovers(empId, date) {
            const sel = document.getElementById('mPhCarryover');
            const note = document.getElementById('mPhCarryoverNote');
            sel.innerHTML = '<option value="">-- Tidak pakai PH simpanan --</option>';
            note.style.display = 'none';

            const url = `{{ route('roster.phCarryovers') }}?employee_id=${empId}&date=${date}`;
            fetch(url, {
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.data.length) {
                        data.data.forEach(it => {
                            const opt = document.createElement('option');
                            opt.value = it.id;
                            opt.textContent = `${it.ph_name} (asal ${it.ph_date}, exp ${it.expired_at})`;
                            sel.appendChild(opt);
                        });
                    } else {
                        note.textContent = 'Tidak ada PH simpanan tersedia untuk karyawan ini.';
                        note.style.display = 'block';
                    }
                })
                .catch(() => {
                    note.textContent = 'Gagal memuat PH simpanan.';
                    note.style.display = 'block';
                });
        }
    </script>

    <script>
        $(document).ready(function() {
            $('#activityTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('roster.activities') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '50px'
                    },
                    {
                        data: 'event_badge',
                        name: 'event',
                        width: '100px'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'causer_name',
                        name: 'causer_name',
                        width: '150px'
                    },
                    {
                        data: 'properties',
                        name: 'properties',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'created_at_formatted',
                        name: 'created_at',
                        width: '160px'
                    },
                ],
                order: [
                    [5, 'desc']
                ],
                pageLength: 10,
                language: {
                    processing: '<i class="fas fa-spinner fa-spin"></i> Loading...',
                    emptyTable: 'No activity log found.',
                    zeroRecords: 'No matching records found.',
                }
            });
        });
    </script>
    @if ($isSupervisorOrManager && !$isRosterOpen)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Roster Ditutup',
                    text: 'Periode pengisian roster sedang ditutup.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6',
                });
            });

        </script>
    @endif
@endpush
