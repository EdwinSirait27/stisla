@extends('layouts.app')
@section('title', 'Employee Salary')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand · redesign: full
         * paper: oklch(98.4% 0.004 265) · accent: oklch(61% 0.17 276) · display: Nunito · body: Nunito
         * nav: kept (Stisla shell) · signature: salary coverage beside the gaps that block payroll, rows marked current, scheduled or history · states: loading, empty, full, missing salaries, scheduled
         * axes: light/rounded-sans/chromatic-indigo · posture: app · contrast: pass · v2.0.0 */
        /* Hallmark · pre-emit critique: P4 H4 E4 S4 R4 V4 · weakest: E, every history row looked current, answered with a scope switch and a state badge per row */

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

        .pr-head-actions {
            display: flex;
            flex-wrap: wrap;
            gap: var(--pr-space-xs);
        }

        .status-badge.is-current {
            background: var(--pr-success-soft);
            color: var(--pr-success);
        }

        .status-badge.is-scheduled {
            background: var(--pr-accent-soft);
            color: var(--pr-accent-strong);
        }

        .status-badge.is-history {
            background: var(--pr-paper-2);
            color: var(--pr-ink-2);
        }

        .status-badge.is-created {
            background: var(--pr-success-soft);
            color: var(--pr-success);
        }

        .status-badge.is-updated {
            background: var(--pr-warn-soft);
            color: var(--pr-warn);
        }

        .status-badge.is-deleted {
            background: var(--pr-error-soft);
            color: var(--pr-error);
        }

        .status-badge.is-type {
            background: transparent;
            border: 1px solid var(--pr-rule);
            color: var(--pr-ink-2);
        }

        /* ── Fields ── */
        .pr-field label {
            display: block;
            font-size: var(--pr-text-xs);
            font-weight: 600;
            color: var(--pr-ink-2);
            margin-bottom: var(--pr-space-2xs);
        }

        .pr-field select,
        .pr-field input {
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

        .pr-field select:focus-visible,
        .pr-field input:focus-visible {
            outline-offset: -1px;
        }

        /* ── Overview (signature) ── */
        .pr-overview {
            display: grid;
            grid-template-columns: minmax(0, 5fr) minmax(0, 7fr);
        }

        .pr-coverage {
            padding: var(--pr-space-lg);
            border-right: 1px solid var(--pr-rule);
        }

        .pr-coverage-total {
            display: flex;
            align-items: baseline;
            gap: var(--pr-space-xs);
            margin-bottom: var(--pr-space-md);
        }

        .pr-coverage-total strong {
            font-size: var(--pr-text-2xl);
            font-weight: 700;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }

        .pr-coverage-total span {
            font-size: var(--pr-text-sm);
            color: var(--pr-ink-2);
        }

        .pr-types {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: var(--pr-space-xs);
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .pr-types button {
            width: 100%;
            text-align: left;
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-panel);
            padding: var(--pr-space-xs) var(--pr-space-sm);
            font-family: var(--pr-font);
            color: var(--pr-ink);
            cursor: pointer;
            transition: background-color var(--pr-dur-micro) var(--pr-ease), border-color var(--pr-dur-micro) var(--pr-ease);
        }

        .pr-types button:hover {
            background: var(--pr-paper-2);
        }

        .pr-types button[aria-pressed="true"] {
            border-color: var(--pr-accent);
            background: var(--pr-accent-soft);
        }

        .pr-types-label {
            display: block;
            font-size: var(--pr-text-xs);
            color: var(--pr-ink-2);
        }

        .pr-types-value {
            display: block;
            margin-top: 2px;
            font-size: var(--pr-text-lg);
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        .pr-types-rule {
            display: block;
            margin-top: 2px;
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
        }

        .pr-gaps {
            padding: var(--pr-space-lg);
        }

        .pr-gap {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: var(--pr-space-sm);
        }

        .pr-gap+.pr-gap {
            margin-top: var(--pr-space-md);
            padding-top: var(--pr-space-md);
            border-top: 1px solid var(--pr-rule);
        }

        .pr-gap-title {
            font-size: var(--pr-text-md);
            font-weight: 700;
            color: var(--pr-ink);
            margin: 0;
        }

        .pr-gap-title strong {
            font-variant-numeric: tabular-nums;
        }

        .pr-gap.is-warn .pr-gap-title strong {
            color: var(--pr-error);
        }

        .pr-gap p {
            margin: 2px 0 0;
            font-size: var(--pr-text-sm);
            line-height: 1.5;
            color: var(--pr-ink-2);
            max-width: 60ch;
        }

        .pr-names {
            display: flex;
            flex-wrap: wrap;
            gap: var(--pr-space-2xs);
            margin: var(--pr-space-xs) 0 0;
            padding: 0;
            list-style: none;
        }

        .pr-names li {
            padding: 2px var(--pr-space-xs);
            border-radius: var(--pr-pill);
            background: var(--pr-paper-2);
            font-size: var(--pr-text-xs);
            color: var(--pr-ink);
        }

        .pr-names li.is-more {
            background: transparent;
            color: var(--pr-muted);
        }

        .pr-gap-actions {
            display: flex;
            flex-wrap: wrap;
            gap: var(--pr-space-xs);
        }

        .pr-link {
            border: 0;
            background: none;
            padding: 0;
            font-family: var(--pr-font);
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-accent-strong);
            text-decoration: underline;
            text-underline-offset: .16em;
            cursor: pointer;
        }

        .pr-link:hover {
            color: var(--pr-ink);
        }

        .pr-alert {
            padding: var(--pr-space-sm) var(--pr-space-lg);
            margin-bottom: var(--pr-space-lg);
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius);
            background: var(--pr-warn-soft);
            font-size: var(--pr-text-sm);
        }

        .pr-alert strong {
            color: var(--pr-warn);
        }

        .pr-alert ul {
            margin: var(--pr-space-2xs) 0 0;
            padding-left: var(--pr-space-lg);
        }

        /* ── Table toolbar ── */
        .pr-tablebar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: var(--pr-space-sm);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-bottom: 1px solid var(--pr-rule);
        }

        .pr-tablebar-group {
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
            width: 220px;
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

        .pr-btn-sm {
            min-height: 32px;
            padding: 0 var(--pr-space-sm);
            font-size: var(--pr-text-xs);
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
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: var(--pr-space-sm) var(--pr-space-md);
        }

        .pr-filter-actions {
            display: flex;
            justify-content: flex-end;
            gap: var(--pr-space-xs);
            margin-top: var(--pr-space-md);
        }

        .pr-scope-note {
            padding: var(--pr-space-xs) var(--pr-space-lg);
            border-bottom: 1px solid var(--pr-rule);
            font-size: var(--pr-text-xs);
            color: var(--pr-ink-2);
            background: var(--pr-paper);
        }

        /* ── Salary table ── */
        #salary-table,
        #activity-table {
            width: 100% !important;
            font-size: var(--pr-text-sm);
            border-collapse: collapse;
        }

        #salary-table thead th,
        #activity-table thead th {
            background: var(--pr-panel);
            color: var(--pr-ink-2);
            font-size: var(--pr-text-xs);
            font-weight: 700;
            padding: var(--pr-space-sm) var(--pr-space-xs);
            border: 0;
            border-bottom: 1px solid var(--pr-rule);
            white-space: nowrap;
        }

        /* Kepala kolom nominal boleh dua baris supaya mode Rincian muat */
        #salary-table thead th.num {
            white-space: normal;
            vertical-align: bottom;
        }

        #salary-table thead th.sorting,
        #salary-table thead th.sorting_asc,
        #salary-table thead th.sorting_desc {
            padding-right: var(--pr-space-lg);
        }

        #salary-table tbody td,
        #activity-table tbody td {
            padding: var(--pr-space-sm) var(--pr-space-xs);
            vertical-align: middle;
            border: 0;
            border-bottom: 1px solid var(--pr-rule);
            color: var(--pr-ink);
        }

        #activity-table tbody td {
            vertical-align: top;
        }

        #salary-table tbody tr:hover td,
        #activity-table tbody tr:hover td {
            background: var(--pr-paper);
        }

        #salary-table tbody tr.is-history td {
            color: var(--pr-muted);
        }

        .pr-amount {
            display: block;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        .pr-amount-sub {
            display: block;
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
            white-space: nowrap;
        }

        .pr-dash {
            color: var(--pr-muted);
        }

        /* Activity changes */
        .pr-changes {
            display: grid;
            grid-template-columns: max-content max-content;
            gap: 2px var(--pr-space-md);
            margin: 0;
            font-size: var(--pr-text-xs);
        }

        .pr-changes dt {
            color: var(--pr-ink-2);
            font-weight: 400;
        }

        .pr-changes dd {
            margin: 0;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        .pr-changes del {
            color: var(--pr-muted);
            margin-right: var(--pr-space-xs);
        }

        .pr-changes ins {
            text-decoration: none;
            font-weight: 700;
            color: var(--pr-ink);
        }

        /* Import modal */
        .pr-modal-title {
            font-size: var(--pr-text-lg);
            font-weight: 700;
            color: var(--pr-ink);
            margin: 0;
        }

        .pr-columns {
            display: flex;
            flex-wrap: wrap;
            gap: var(--pr-space-2xs);
            margin: var(--pr-space-xs) 0 0;
            padding: 0;
            list-style: none;
        }

        .pr-columns code {
            display: inline-block;
            padding: 2px var(--pr-space-xs);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-paper-2);
            color: var(--pr-ink);
            font-size: var(--pr-text-xs);
        }

        @media (max-width: 75rem) {
            .pr-overview {
                grid-template-columns: minmax(0, 1fr);
            }

            .pr-coverage {
                border-right: 0;
                border-bottom: 1px solid var(--pr-rule);
            }

            .pr-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 40rem) {

            .pr-head,
            .pr-panel-head,
            .pr-coverage,
            .pr-gaps,
            .pr-tablebar,
            .pr-filter-body,
            .pr-scope-note,
            .pr-table-wrap,
            .pr-alert {
                padding-left: var(--pr-space-md);
                padding-right: var(--pr-space-md);
            }

            .pr-filter-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .pr-head-actions,
            .pr-tablebar-group,
            .pr-search,
            .pr-search input,
            .pr-tabs {
                width: 100%;
            }

            .pr-head-actions .pr-btn,
            .pr-tabs button {
                flex: 1 1 auto;
            }

            .pr-field select,
            .pr-field input,
            .pr-search input,
            .pr-btn {
                min-height: 44px;
                font-size: 16px;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .pr *,
            .pr *::before,
            .pr *::after {
                transition-duration: 0.01ms !important;
            }
        }
    </style>
@endpush

@section('main')
    @php
        $types = [
            ['value' => 'PKWT', 'label' => 'PKWT', 'count' => $stats['pkwt'], 'rule' => 'Gaji bulanan'],
            ['value' => 'On Job Training', 'label' => 'OJT', 'count' => $stats['ojt'], 'rule' => 'Gaji bulanan'],
            ['value' => 'DW', 'label' => 'Daily worker', 'count' => $stats['dw'], 'rule' => 'Per hari hadir'],
        ];
    @endphp
    <div class="main-content pr">
        <section class="section">

            {{-- Head --}}
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs" aria-label="Breadcrumb">
                        <li>Dashboard</li>
                        <li>Payroll</li>
                        <li aria-current="page">Employee Salary</li>
                    </ol>
                    <h1 class="pr-title">Employee salary</h1>
                    <p class="pr-lede">
                        Payroll memakai gaji dengan effective date terakhir yang tidak lewat dari akhir periode.
                        Gaji baru tidak mengubah payroll draft yang sudah di-generate.
                    </p>
                </div>
                <div class="pr-head-actions">
                    @can('employee.salary.import')
                        <button type="button" class="pr-btn" onclick="downloadTemplate()">
                            <i class="fas fa-download" aria-hidden="true"></i> Template
                        </button>
                        <button type="button" class="pr-btn" data-toggle="modal" data-target="#importModal">
                            <i class="fas fa-file-import" aria-hidden="true"></i> Import Excel
                        </button>
                    @endcan
                    @can('employee.salary.create')
                        <a href="{{ route('employeesalary.create') }}" class="pr-btn pr-btn-primary">
                            <i class="fas fa-plus" aria-hidden="true"></i> Tambah gaji
                        </a>
                    @endcan
                </div>
            </header>

            @if (session('skipped'))
                <div class="pr-alert" role="status">
                    <strong>Beberapa data diskip saat import:</strong>
                    <ul>
                        @foreach (session('skipped') as $skip)
                            <li>{{ $skip }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Overview --}}
            <section class="pr-panel" aria-labelledby="overview-title">
                <h2 class="visually-hidden sr-only" id="overview-title">Ringkasan data gaji</h2>
                <div class="pr-overview">
                    <div class="pr-coverage">
                        <div class="pr-coverage-total">
                            <strong>{{ $stats['total'] }}</strong>
                            <span>karyawan punya data gaji</span>
                        </div>
                        <ul class="pr-types" aria-label="Filter berdasarkan tipe karyawan">
                            @foreach ($types as $t)
                                <li>
                                    <button type="button" class="js-type-filter" data-type="{{ $t['value'] }}" aria-pressed="false">
                                        <span class="pr-types-label">{{ $t['label'] }}</span>
                                        <span class="pr-types-value">{{ $t['count'] }}</span>
                                        <span class="pr-types-rule">{{ $t['rule'] }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="pr-gaps">
                        <div class="pr-gap {{ $stats['missing'] > 0 ? 'is-warn' : '' }}">
                            <div>
                                @if ($stats['missing'] > 0)
                                    <h3 class="pr-gap-title"><strong>{{ $stats['missing'] }}</strong> karyawan aktif belum punya data gaji</h3>
                                    <p>Payroll mereka tidak bisa di-generate sampai gajinya diisi.</p>
                                    <ul class="pr-names">
                                        @foreach ($missingEmployees as $m)
                                            <li>{{ $m->employee_name }}</li>
                                        @endforeach
                                        @if ($stats['missing'] > $missingEmployees->count())
                                            <li class="is-more">dan {{ $stats['missing'] - $missingEmployees->count() }} lainnya</li>
                                        @endif
                                    </ul>
                                @else
                                    <h3 class="pr-gap-title">Semua karyawan aktif sudah punya data gaji</h3>
                                @endif
                            </div>
                            @if ($stats['missing'] > 0)
                                <div class="pr-gap-actions">
                                    @can('employee.salary.import')
                                        <button type="button" class="pr-btn pr-btn-sm" data-toggle="modal" data-target="#importModal">Import Excel</button>
                                    @endcan
                                    @can('employee.salary.create')
                                        <a href="{{ route('employeesalary.create') }}" class="pr-btn pr-btn-sm">Tambah satu</a>
                                    @endcan
                                </div>
                            @endif
                        </div>
                        <div class="pr-gap">
                            <div>
                                <h3 class="pr-gap-title"><strong>{{ $stats['scheduled'] }}</strong> perubahan gaji terjadwal</h3>
                                <p>Effective date setelah hari ini. Belum dipakai payroll sampai tanggalnya masuk periode.</p>
                            </div>
                            @if ($stats['scheduled'] > 0)
                                <button type="button" class="pr-link js-scope-jump" data-scope="scheduled">Tampilkan</button>
                            @endif
                        </div>
                    </div>
                </div>
            </section>

            {{-- Salary table --}}
            <section class="pr-panel" aria-labelledby="table-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="table-title">Data gaji</h2>
                        <span class="pr-panel-sub" id="record-count">Memuat data</span>
                    </div>
                    <div class="pr-tabs" role="group" aria-label="Lingkup data gaji">
                        <button type="button" data-scope="current" aria-pressed="true">Berlaku sekarang</button>
                        <button type="button" data-scope="scheduled" aria-pressed="false">Terjadwal</button>
                        <button type="button" data-scope="all" aria-pressed="false">Semua riwayat</button>
                    </div>
                </div>

                <div class="pr-tablebar">
                    <div class="pr-tabs pr-views" role="group" aria-label="Tampilan kolom">
                        <button type="button" data-view="ringkas" aria-pressed="true">Ringkas</button>
                        <button type="button" data-view="rincian" aria-pressed="false">Rincian komponen</button>
                        <button type="button" data-view="semua" aria-pressed="false">Semua kolom</button>
                    </div>
                    <div class="pr-tablebar-group">
                        <label class="visually-hidden sr-only" for="salary-search">Cari karyawan</label>
                        <span class="pr-search">
                            <i class="fas fa-search" aria-hidden="true"></i>
                            <input type="search" id="salary-search" placeholder="Cari nama atau NIP" autocomplete="off">
                        </span>
                        <button type="button" class="pr-btn pr-btn-sm" id="btn-toggle-filter" aria-expanded="false" aria-controls="filter-panel">
                            <i class="fas fa-filter" aria-hidden="true"></i> Filter
                            <span class="pr-filter-count" id="filter-count" hidden>0</span>
                        </button>
                        @can('employee.salary.export')
                            <button type="button" class="pr-btn pr-btn-sm" id="btn-export">
                                <i class="fas fa-file-excel" aria-hidden="true"></i> Export
                            </button>
                        @endcan
                    </div>
                </div>

                <div class="pr-filter-body" id="filter-panel" hidden>
                    <div class="pr-filter-grid">
                        <div class="pr-field">
                            <label for="filter-effective-date">Effective date</label>
                            <input type="date" id="filter-effective-date">
                        </div>
                        <div class="pr-field">
                            <label for="filter-status">Tipe karyawan</label>
                            <select id="filter-status">
                                <option value="">Semua tipe</option>
                                <option value="PKWT">PKWT</option>
                                <option value="On Job Training">On Job Training</option>
                                <option value="DW">Daily Worker</option>
                            </select>
                        </div>
                        <div class="pr-field">
                            <label for="filter-company">Company</label>
                            <select id="filter-company">
                                <option value="">Semua company</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->name }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pr-field">
                            <label for="filter-department">Department</label>
                            <select id="filter-department">
                                <option value="">Semua department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->department_name }}">{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pr-field">
                            <label for="filter-store">Location</label>
                            <select id="filter-store">
                                <option value="">Semua location</option>
                                @foreach ($stores as $store)
                                    <option value="{{ $store->name }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pr-field">
                            <label for="filter-grading">Grading</label>
                            <select id="filter-grading">
                                <option value="">Semua grading</option>
                                @foreach ($gradings as $grading)
                                    <option value="{{ $grading->grading_name }}">{{ $grading->grading_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="pr-filter-actions">
                        <button type="button" class="pr-btn pr-btn-ghost" id="btn-reset"><i class="fas fa-redo" aria-hidden="true"></i> Reset</button>
                        <button type="button" class="pr-btn" id="btn-search"><i class="fas fa-search" aria-hidden="true"></i> Terapkan filter</button>
                    </div>
                </div>

                <div class="pr-scope-note" id="scope-note">Satu baris per karyawan: gaji yang dipakai payroll per hari ini.</div>

                <div class="pr-table-wrap">
                    <table id="salary-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Company</th>
                                <th>Department</th>
                                <th>Location</th>
                                <th>Grading</th>
                                <th>Tipe</th>
                                <th>Status</th>
                                <th class="num">Basic salary</th>
                                <th class="num">Pos. allowance</th>
                                <th class="num">Daily rate</th>
                                <th class="num">Meal</th>
                                <th class="num">House</th>
                                <th class="num">Transport</th>
                                <th class="num">BPJS Ketenagakerjaan</th>
                                <th class="num">BPJS Kesehatan</th>
                                <th class="num">Gaji pokok</th>
                                <th class="num">Tunjangan</th>
                                <th class="num">BPJS</th>
                                <th>Effective date</th>
                                <th style="text-align:center">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </section>

            {{-- Activity --}}
            <section class="pr-panel" aria-labelledby="activity-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="activity-title">Riwayat perubahan</h2>
                        <span class="pr-panel-sub">Siapa mengubah gaji siapa, dari berapa ke berapa</span>
                    </div>
                </div>
                <div class="pr-table-wrap">
                    <table id="activity-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Event</th>
                                <th>Employee</th>
                                <th>Perubahan</th>
                                <th>Oleh</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </section>
        </section>
    </div>

    @can('employee.salary.import')
    {{-- Modal Import --}}
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title pr-modal-title">Import employee salary</h2>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('employeesalary.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="import-effective-date">Effective date <span class="text-danger">*</span></label>
                            <input type="date" name="effective_date" id="import-effective-date" class="form-control" required>
                            <small class="text-muted">Tanggal berlaku gaji ini untuk semua baris di file.</small>
                        </div>
                        <div class="form-group">
                            <label for="import-file">File Excel <span class="text-danger">*</span></label>
                            <input type="file" name="file" id="import-file" class="form-control" accept=".xlsx,.xls" required>
                            <small class="text-muted">Format .xlsx atau .xls</small>
                        </div>
                        <div class="alert alert-info py-2" style="font-size:.8rem">
                            Isi angka tanpa titik, koma, spasi, atau simbol. Kolom yang dibaca:
                            <ul class="pr-columns">
                                <li><code>employee_pengenal</code></li>
                                <li><code>basic_salary</code></li>
                                <li><code>position_allowance</code></li>
                                <li><code>daily_rate</code></li>
                                <li><code>meal_allowance</code></li>
                                <li><code>house_allowance</code></li>
                                <li><code>transport_allowance</code></li>
                                <li><code>bpjs_ketenagakerjaan</code></li>
                                <li><code>bpjs_kesehatan</code></li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            const esc = v => $('<div>').text(v ?? '').html();
            const idr = v => Math.round(Number(v) || 0).toLocaleString('id-ID');
            const isDw = row => row.status_employee === 'DW';
            const dash = '<span class="pr-dash">-</span>';
            const money = (field, dwOnly = null) => ({
                data: field,
                name: field,
                className: 'num',
                render: (d, t, row) => {
                    if (t !== 'display') return d;
                    if (dwOnly === true && !isDw(row)) return dash;
                    if (dwOnly === false && isDw(row)) return dash;
                    return idr(d);
                },
            });

            const SCOPE_NOTE = {
                current: 'Satu baris per karyawan: gaji yang dipakai payroll per hari ini.',
                scheduled: 'Gaji dengan effective date setelah hari ini. Belum dipakai payroll.',
                all: 'Semua baris gaji, termasuk riwayat lama dan yang terjadwal.',
            };
            const VIEWS = {
                ringkas: ['employee', 'store', 'type', 'fixed', 'allowances', 'bpjs', 'effective_date', 'action'],
                rincian: ['employee', 'basic_salary', 'position_allowance', 'daily_rate', 'meal_allowance',
                    'house_allowance', 'transport_allowance', 'bpjs_ketenagakerjaan', 'bpjs_kesehatan',
                    'effective_date', 'action'
                ],
                semua: null,
            };

            const params = new URLSearchParams(window.location.search);
            let currentScope = SCOPE_NOTE.hasOwnProperty(params.get('scope')) ? params.get('scope') : 'current';
            let currentView = VIEWS.hasOwnProperty(params.get('view')) ? params.get('view') : 'ringkas';

            // ── Salary table ──
            const table = $('#salary-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                dom: 'rt<"pr-dt-bottom"ip>',
                ajax: {
                    url: '{{ route('employeesalary.data') }}',
                    data: function(d) {
                        d.scope = currentScope;
                        d.effective_date = $('#filter-effective-date').val();
                        d.status_employee = $('#filter-status').val();
                        d.company_name = $('#filter-company').val();
                        d.department_name = $('#filter-department').val();
                        d.store_name = $('#filter-store').val();
                        d.grading_name = $('#filter-grading').val();
                    }
                },
                columns: [{
                        data: 'employee_name',
                        name: 'employee',
                        defaultContent: '-',
                        render: (d, t, row) => t !== 'display' ? d :
                            `<span class="pr-cell-name">${esc(d)}</span>` +
                            `<span class="pr-cell-sub">${esc(row.employee_pengenal)} ${row.position_name && row.position_name !== '-' ? '<span class="pr-dash">/</span> ' + esc(row.position_name) : ''}</span>`,
                    },
                    { data: 'company_name', name: 'company', defaultContent: '-' },
                    { data: 'department_name', name: 'department', defaultContent: '-' },
                    { data: 'store_name', name: 'store', defaultContent: '-' },
                    { data: 'grading_name', name: 'grading', defaultContent: '-' },
                    {
                        data: 'status_employee',
                        name: 'type',
                        render: (d, t) => {
                            if (t !== 'display') return d;
                            const label = { 'On Job Training': 'OJT', 'DW': 'Daily worker' }[d] ?? d;
                            return `<span class="status-badge is-type">${esc(label)}</span>`;
                        }
                    },
                    { data: 'status', name: 'emp_status', defaultContent: '-' },
                    money('basic_salary', false),
                    money('position_allowance', false),
                    money('daily_rate', true),
                    money('meal_allowance'),
                    money('house_allowance'),
                    money('transport_allowance'),
                    money('bpjs_ketenagakerjaan'),
                    money('bpjs_kesehatan'),
                    {
                        data: null,
                        name: 'fixed',
                        className: 'num',
                        orderable: false,
                        searchable: false,
                        render: (d, t, row) => isDw(row) ?
                            `<span class="pr-amount">${idr(row.daily_rate)}</span><span class="pr-amount-sub">per hari</span>` :
                            `<span class="pr-amount">${idr(Number(row.basic_salary) + Number(row.position_allowance))}</span>` +
                            `<span class="pr-amount-sub">${idr(row.basic_salary)} + ${idr(row.position_allowance)}</span>`,
                    },
                    {
                        data: null,
                        name: 'allowances',
                        className: 'num',
                        orderable: false,
                        searchable: false,
                        render: (d, t, row) =>
                            `<span class="pr-amount">${idr(Number(row.meal_allowance) + Number(row.house_allowance) + Number(row.transport_allowance))}</span>` +
                            `<span class="pr-amount-sub">meal, house, transport</span>`,
                    },
                    {
                        data: null,
                        name: 'bpjs',
                        className: 'num',
                        orderable: false,
                        searchable: false,
                        render: (d, t, row) =>
                            `<span class="pr-amount">${idr(Number(row.bpjs_ketenagakerjaan) + Number(row.bpjs_kesehatan))}</span>` +
                            `<span class="pr-amount-sub">TK ${idr(row.bpjs_ketenagakerjaan)}, Kes ${idr(row.bpjs_kesehatan)}</span>`,
                    },
                    {
                        data: 'effective_date',
                        name: 'effective_date',
                        render: (d, t, row) => {
                            if (t !== 'display') return d;
                            const state = {
                                current: ['is-current', 'Berlaku'],
                                scheduled: ['is-scheduled', 'Terjadwal'],
                                history: ['is-history', 'Riwayat'],
                            }[row.salary_state] ?? ['is-history', row.salary_state];
                            return `<span class="pr-cell-name">${esc(row.effective_date_fmt)}</span>` +
                                `<span class="status-badge ${state[0]}">${state[1]}</span>`;
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                ],
                createdRow: (tr, row) => {
                    if (row.salary_state === 'history') $(tr).addClass('is-history');
                },
                drawCallback: function() {
                    const info = this.api().page.info();
                    $('#record-count').text(`${info.recordsDisplay} baris gaji`);
                },
                language: {
                    processing: 'Memuat data...',
                    emptyTable: 'Tidak ada data gaji di lingkup ini.',
                    zeroRecords: 'Tidak ada data gaji yang cocok dengan filter.',
                    info: '_START_ sampai _END_ dari _TOTAL_',
                    infoEmpty: '0 data',
                    infoFiltered: '',
                    paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                },
                pageLength: 25,
                order: [[18, 'desc']],
            });

            function syncUrl() {
                const p = new URLSearchParams(window.location.search);
                currentScope === 'current' ? p.delete('scope') : p.set('scope', currentScope);
                currentView === 'ringkas' ? p.delete('view') : p.set('view', currentView);
                const qs = p.toString();
                history.replaceState(null, '', window.location.pathname + (qs ? '?' + qs : ''));
            }

            function applyScope(scope) {
                currentScope = scope;
                $('.pr-panel-head .pr-tabs button').each(function() {
                    $(this).attr('aria-pressed', $(this).data('scope') === scope ? 'true' : 'false');
                });
                $('#scope-note').text(SCOPE_NOTE[scope]);
                syncUrl();
            }

            function applyView(view) {
                currentView = view;
                const wanted = VIEWS[view];
                const visible = wanted ? new Set(wanted) : null;
                table.columns().every(function() {
                    const name = this.settings()[0].aoColumns[this.index()].sName;
                    this.visible(!visible || visible.has(name), false);
                });
                table.columns.adjust().draw(false);
                $('.pr-views button').each(function() {
                    $(this).attr('aria-pressed', $(this).data('view') === view ? 'true' : 'false');
                });
                syncUrl();
            }

            $('.pr-panel-head .pr-tabs').on('click', 'button', function() {
                applyScope($(this).data('scope'));
                table.ajax.reload();
            });
            $('.js-scope-jump').on('click', function() {
                applyScope($(this).data('scope'));
                table.ajax.reload();
                document.getElementById('table-title').scrollIntoView({ block: 'start' });
            });
            $('.pr-views').on('click', 'button', function() {
                applyView($(this).data('view'));
            });

            // ── Filter ──
            const filterInputs = '#filter-effective-date, #filter-status, #filter-store, #filter-company, #filter-department, #filter-grading';

            function refreshFilterUi() {
                const active = $(filterInputs).filter((i, el) => $(el).val()).length;
                $('#filter-count').text(active).prop('hidden', active === 0);
                const type = $('#filter-status').val();
                $('.js-type-filter').each(function() {
                    $(this).attr('aria-pressed', $(this).data('type') === type ? 'true' : 'false');
                });
            }

            $('#btn-toggle-filter').on('click', function() {
                const open = $(this).attr('aria-expanded') === 'true';
                $(this).attr('aria-expanded', open ? 'false' : 'true');
                $('#filter-panel').prop('hidden', open);
            });
            $('#btn-search').on('click', () => {
                refreshFilterUi();
                table.ajax.reload();
            });
            $('#btn-reset').on('click', () => {
                $(filterInputs).val('');
                refreshFilterUi();
                table.ajax.reload();
            });
            $('.js-type-filter').on('click', function() {
                const type = $(this).data('type');
                $('#filter-status').val($('#filter-status').val() === type ? '' : type);
                refreshFilterUi();
                table.ajax.reload();
            });

            let searchTimer = null;
            $('#salary-search').on('input', function() {
                clearTimeout(searchTimer);
                const q = this.value;
                searchTimer = setTimeout(() => table.search(q).draw(), 350);
            });

            applyScope(currentScope);
            applyView(currentView);
            refreshFilterUi();

            // ── Activity table ──
            const FIELDS = [
                ['basic_salary', 'Basic salary'],
                ['position_allowance', 'Position allowance'],
                ['daily_rate', 'Daily rate'],
                ['meal_allowance', 'Meal'],
                ['house_allowance', 'House'],
                ['transport_allowance', 'Transport'],
                ['bpjs_ketenagakerjaan', 'BPJS TK'],
                ['bpjs_kesehatan', 'BPJS Kes'],
            ];

            $('#activity-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                dom: 'rt<"pr-dt-bottom"ip>',
                ajax: {
                    url: '{{ route('employeesalary.activity') }}',
                },
                columns: [{
                        data: 'changed_at',
                        orderable: false,
                        render: (d, t) => t !== 'display' ? d : `<span class="pr-cell-sub">${esc(d)}</span>`,
                    },
                    {
                        data: 'event_badge',
                        orderable: false
                    },
                    {
                        data: 'employee_name',
                        orderable: false,
                        defaultContent: '-',
                        render: (d, t, row) => t !== 'display' ? d :
                            `<span class="pr-cell-name">${esc(d)}</span>` +
                            (row.effective_date && row.effective_date !== '-' ? `<span class="pr-cell-sub">Effective ${esc(row.effective_date)}</span>` : ''),
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: (d, t, row) => {
                            const items = FIELDS.map(([key, label]) => {
                                const oldV = row[`${key}_old`];
                                const newV = row[`${key}_new`];
                                if (oldV !== '-' && newV !== '-' && oldV !== newV) {
                                    return `<dt>${label}</dt><dd><del>${esc(oldV)}</del><ins>${esc(newV)}</ins></dd>`;
                                }
                                if (oldV === '-' && newV !== '-' && newV !== '0') {
                                    return `<dt>${label}</dt><dd><ins>${esc(newV)}</ins></dd>`;
                                }
                                return '';
                            }).join('');
                            return items ? `<dl class="pr-changes">${items}</dl>` : '<span class="pr-dash">Tidak ada perubahan nominal</span>';
                        },
                    },
                    {
                        data: 'causer_name',
                        orderable: false,
                        defaultContent: '-'
                    },
                ],
                language: {
                    processing: 'Memuat data...',
                    emptyTable: 'Belum ada perubahan gaji yang tercatat.',
                    info: '_START_ sampai _END_ dari _TOTAL_',
                    infoEmpty: '0 data',
                    infoFiltered: '',
                    paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                },
                order: [],
                pageLength: 10,
            });

            // ── Export ──
            $('#btn-export').on('click', () => {
                const params = new URLSearchParams({
                    effective_date: $('#filter-effective-date').val(),
                    status: $('#filter-status').val(),
                    store_name: $('#filter-store').val(),
                    company_name: $('#filter-company').val(),
                    department_name: $('#filter-department').val(),
                    grading_name: $('#filter-grading').val(),
                });
                window.location.href = '{{ route('employeesalary.export') }}?' + params.toString();
            });

            // ── Download template ──
            window.downloadTemplate = () => {
                window.location.href = '{{ route('employeesalary.template') }}';
            };

            // ── Flash messages ──
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: @json(session('success')),
                    confirmButtonColor: '#1d4ed8',
                    timer: 3000,
                    timerProgressBar: true
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: @json(session('error')),
                    confirmButtonColor: '#dc2626'
                });
            @endif
        });
    </script>
@endpush
