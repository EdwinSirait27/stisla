@extends('layouts.app')
@section('title', 'Payroll')
@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand · redesign: full
             * paper: oklch(98.4% 0.004 265) · accent: oklch(61% 0.17 276) · display: Nunito · body: Nunito
             * nav: kept (Stisla shell) · signature: the period ledger, status flow bar beside the money totals · states: loading, empty, full, flagged
             * axes: light/rounded-sans/chromatic-indigo · posture: app · contrast: pass · v2.0.0 */
        /* Hallmark · pre-emit critique: P4 H4 E4 S4 R3 V4 · weakest: R, the 33-column table, answered with column views (Ringkas, Pendapatan, Potongan, Semua) */

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
            --pr-shadow-pressed: oklch(27% 0.03 265 / .12);
            --pr-focus: oklch(61% 0.17 276);
            --pr-success: oklch(50% 0.12 150);
            --pr-success-soft: oklch(96% 0.035 150);
            --pr-warn: oklch(52% 0.12 70);
            --pr-warn-soft: oklch(96.5% 0.045 85);
            --pr-error: oklch(52% 0.19 27);
            --pr-error-soft: oklch(96.5% 0.025 27);
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

        .pr-crumbs a {
            color: var(--pr-ink-2);
            text-decoration: none;
        }

        .pr-crumbs a:hover {
            color: var(--pr-accent-strong);
            text-decoration: underline;
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

        .pr-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: var(--pr-space-sm);
            margin-top: var(--pr-space-xs);
            font-size: var(--pr-text-sm);
            color: var(--pr-ink-2);
        }

        .pr-meta i {
            color: var(--pr-muted);
            margin-right: var(--pr-space-2xs);
        }

        .pr-actions {
            display: flex;
            flex-wrap: wrap;
            gap: var(--pr-space-xs);
        }

        .pr-menu {
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            padding: var(--pr-space-2xs);
            font-family: var(--pr-font);
        }

        .pr-menu .dropdown-item {
            display: flex;
            align-items: center;
            gap: var(--pr-space-xs);
            min-height: 36px;
            padding: 0 var(--pr-space-sm);
            border-radius: 4px;
            font-size: var(--pr-text-sm);
            font-weight: 600;
            color: var(--pr-ink);
            cursor: pointer;
        }

        .pr-menu .dropdown-item:hover,
        .pr-menu .dropdown-item:focus-visible {
            background: var(--pr-paper-2);
            color: var(--pr-ink);
        }

        .pr-menu .dropdown-item i {
            width: 14px;
            color: var(--pr-muted);
        }

        /* ── Buttons (page-local, not Stisla .btn) ── */
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

        .pr-btn:disabled,
        .pr-btn[aria-disabled="true"] {
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

        .pr-btn-primary:hover {
            background: var(--pr-accent-deep);
            border-color: var(--pr-accent-deep);
            color: var(--pr-accent-ink);
        }

        .pr-btn-primary:active {
            background: var(--pr-accent-deep);
        }

        .pr-btn-danger {
            color: var(--pr-error);
        }

        .pr-btn-danger:hover {
            background: var(--pr-error-soft);
            border-color: var(--pr-error);
            color: var(--pr-error);
        }

        .pr-btn-ghost {
            border-color: transparent;
            background: transparent;
            color: var(--pr-ink-2);
        }

        .pr-btn-sm {
            min-height: 32px;
            padding: 0 var(--pr-space-sm);
            font-size: var(--pr-text-xs);
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

        .pr-modal-title {
            font-size: var(--pr-text-lg);
            font-weight: 700;
            color: var(--pr-ink);
            margin: 0;
        }

        .pr-panel-title {
            font-size: var(--pr-text-md);
            font-weight: 700;
            color: var(--pr-ink);
            margin: 0;
        }

        .pr-panel-sub {
            font-size: var(--pr-text-sm);
            color: var(--pr-muted);
        }

        /* ── Ledger (signature) ── */
        .pr-ledger {
            display: grid;
            grid-template-columns: minmax(0, 5fr) minmax(0, 7fr);
        }

        .pr-flow {
            padding: var(--pr-space-lg);
            border-right: 1px solid var(--pr-rule);
        }

        .pr-flow-total {
            display: flex;
            align-items: baseline;
            gap: var(--pr-space-xs);
            margin-bottom: var(--pr-space-sm);
        }

        .pr-flow-total strong {
            font-size: var(--pr-text-2xl);
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            line-height: 1;
        }

        .pr-flow-total span {
            font-size: var(--pr-text-sm);
            color: var(--pr-ink-2);
        }

        .pr-bar {
            display: flex;
            height: 10px;
            border-radius: var(--pr-pill);
            overflow: hidden;
            background: var(--pr-paper-2);
            margin-bottom: var(--pr-space-md);
        }

        .pr-bar span {
            display: block;
            height: 100%;
        }

        .pr-bar .is-draft,
        .pr-legend .is-draft .pr-swatch {
            background: var(--pr-muted);
        }

        .pr-bar .is-approved,
        .pr-legend .is-approved .pr-swatch {
            background: var(--pr-success);
        }

        .pr-bar .is-paid,
        .pr-legend .is-paid .pr-swatch {
            background: var(--pr-accent);
        }

        .pr-legend {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: var(--pr-space-xs);
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .pr-legend button {
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

        .pr-legend button:hover {
            background: var(--pr-paper-2);
        }

        .pr-legend button[aria-pressed="true"] {
            border-color: var(--pr-accent);
            background: var(--pr-accent-soft);
        }

        .pr-legend-label {
            display: flex;
            align-items: center;
            gap: var(--pr-space-xs);
            font-size: var(--pr-text-xs);
            color: var(--pr-ink-2);
        }

        .pr-swatch {
            width: 8px;
            height: 8px;
            border-radius: 2px;
            flex-shrink: 0;
        }

        .pr-legend-value {
            display: block;
            font-size: var(--pr-text-lg);
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            margin-top: 2px;
        }

        .pr-money {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin: 0;
        }

        .pr-money>div {
            padding: var(--pr-space-md) var(--pr-space-lg);
            border-bottom: 1px solid var(--pr-rule);
        }

        .pr-money>div:nth-child(odd) {
            border-right: 1px solid var(--pr-rule);
        }

        .pr-money>div:nth-last-child(-n+2) {
            border-bottom: 0;
        }

        .pr-money dt {
            font-size: var(--pr-text-sm);
            font-weight: 600;
            color: var(--pr-ink-2);
            margin-bottom: var(--pr-space-2xs);
        }

        .pr-money dd {
            margin: 0;
            font-size: var(--pr-text-xl);
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
            color: var(--pr-ink);
        }

        .pr-money dd small {
            font-size: var(--pr-text-sm);
            font-weight: 600;
            color: var(--pr-muted);
            margin-right: 2px;
        }

        .pr-money .is-net dd {
            color: var(--pr-accent-strong);
        }

        .pr-money .is-deduction dd {
            color: var(--pr-error);
        }

        .pr-money-note {
            display: block;
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
            margin-top: 2px;
        }

        /* ── Checks ── */
        .pr-checks {
            border-top: 1px solid var(--pr-rule);
            background: var(--pr-warn-soft);
            border-radius: 0 0 var(--pr-radius) var(--pr-radius);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: var(--pr-space-sm) var(--pr-space-lg);
        }

        .pr-checks-title {
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-warn);
        }

        .pr-check {
            display: flex;
            align-items: center;
            gap: var(--pr-space-xs);
            font-size: var(--pr-text-sm);
            color: var(--pr-ink);
        }

        .pr-check strong {
            font-variant-numeric: tabular-nums;
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

        .pr-closed {
            border-top: 1px solid var(--pr-rule);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            font-size: var(--pr-text-sm);
            color: var(--pr-ink-2);
            background: var(--pr-paper-2);
            border-radius: 0 0 var(--pr-radius) var(--pr-radius);
        }

        /* ── Import result ── */
        .pr-import-group {
            padding: var(--pr-space-sm) var(--pr-space-lg);
            font-size: var(--pr-text-sm);
            color: var(--pr-ink);
        }

        .pr-import-group+.pr-import-group {
            border-top: 1px solid var(--pr-rule);
        }

        .pr-import-group.is-skipped strong {
            color: var(--pr-error);
        }

        .pr-import-group.is-note strong {
            color: var(--pr-warn);
        }

        .pr-import-group ul {
            margin: var(--pr-space-2xs) 0 0;
            padding-left: var(--pr-space-lg);
        }

        /* ── Empty ── */
        .pr-empty {
            padding: var(--pr-space-lg);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: var(--pr-space-md);
        }

        .pr-empty h2 {
            font-size: var(--pr-text-lg);
            font-weight: 700;
            margin: 0 0 var(--pr-space-2xs);
            color: var(--pr-ink);
            text-wrap: balance;
        }

        .pr-empty p {
            margin: 0;
            font-size: var(--pr-text-sm);
            color: var(--pr-ink-2);
            max-width: 60ch;
        }

        /* ── Toolbar ── */
        .pr-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: var(--pr-space-sm);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-bottom: 1px solid var(--pr-rule);
        }

        .pr-views {
            display: inline-flex;
            padding: var(--pr-space-2xs);
            gap: var(--pr-space-2xs);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-paper-2);
        }

        .pr-views button {
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

        .pr-views button:hover {
            color: var(--pr-ink);
        }

        .pr-views button[aria-pressed="true"] {
            background: var(--pr-panel);
            color: var(--pr-ink);
            box-shadow: 0 1px 2px var(--pr-shadow-pressed);
        }

        .pr-toolbar-right {
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

        .pr-chip {
            display: inline-flex;
            align-items: center;
            gap: var(--pr-space-xs);
            min-height: 28px;
            padding: 0 4px 0 var(--pr-space-sm);
            border-radius: var(--pr-pill);
            background: var(--pr-warn-soft);
            color: var(--pr-ink);
            font-size: var(--pr-text-xs);
            font-weight: 600;
        }

        .pr-chip button {
            width: 22px;
            height: 22px;
            border: 0;
            border-radius: 50%;
            background: transparent;
            color: var(--pr-ink-2);
            cursor: pointer;
        }

        .pr-chip button:hover {
            background: var(--pr-panel);
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
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: var(--pr-space-sm) var(--pr-space-md);
        }

        .pr-field label {
            display: block;
            font-size: var(--pr-text-xs);
            font-weight: 600;
            color: var(--pr-ink-2);
            margin-bottom: var(--pr-space-2xs);
        }

        .pr-field select {
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

        .pr-field select:focus-visible {
            outline-offset: -1px;
        }

        .pr-filter-actions {
            display: flex;
            justify-content: flex-end;
            gap: var(--pr-space-xs);
            margin-top: var(--pr-space-md);
        }

        /* ── Bulk bar ── */
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

        .bulk-bar.show {
            display: flex;
        }

        .bulk-bar-count {
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-ink);
            margin-right: var(--pr-space-xs);
        }

        .bulk-bar-hint {
            flex-basis: 100%;
            font-size: var(--pr-text-xs);
            color: var(--pr-ink-2);
        }

        /* ── Table ── */
        .pr-table-wrap {
            padding: var(--pr-space-xs) var(--pr-space-lg) var(--pr-space-md);
        }

        #payroll-table {
            width: 100% !important;
            font-size: var(--pr-text-sm);
            border-collapse: collapse;
        }

        #payroll-table thead th {
            background: var(--pr-panel);
            color: var(--pr-ink-2);
            font-size: var(--pr-text-xs);
            font-weight: 700;
            padding: var(--pr-space-sm) var(--pr-space-xs);
            border: 0;
            border-bottom: 1px solid var(--pr-rule);
            white-space: nowrap;
        }

        #payroll-table thead th.sorting,
        #payroll-table thead th.sorting_asc,
        #payroll-table thead th.sorting_desc {
            padding-right: var(--pr-space-lg);
        }

        #payroll-table tbody td {
            padding: var(--pr-space-sm) var(--pr-space-xs);
            vertical-align: middle;
            border: 0;
            border-bottom: 1px solid var(--pr-rule);
            color: var(--pr-ink);
            white-space: nowrap;
        }

        #payroll-table tbody tr:hover td {
            background: var(--pr-paper);
        }

        #payroll-table tbody tr.is-flagged td:first-child {
            box-shadow: inset 3px 0 0 var(--pr-error);
        }

        #payroll-table input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--pr-accent);
            cursor: pointer;
        }

        .pr-emp-name {
            display: block;
            font-weight: 700;
            color: var(--pr-ink);
        }

        .pr-emp-sub {
            display: flex;
            gap: var(--pr-space-sm);
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
        }

        .pr-att {
            font-variant-numeric: tabular-nums;
        }

        .pr-att.is-zero {
            color: var(--pr-error);
            font-weight: 700;
        }

        .pr-net {
            font-weight: 700;
        }

        .pr-net.is-negative {
            color: var(--pr-error);
        }

        .pr-income {
            color: var(--pr-success);
        }

        .pr-deduction {
            color: var(--pr-error);
        }

        .pr-muted {
            color: var(--pr-muted);
        }

        /* Badges (HTML from PayrollController::getPayroll) */
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

        .status-badge.is-approved {
            background: var(--pr-success-soft);
            color: var(--pr-success);
        }

        .status-badge.is-paid {
            background: var(--pr-accent-soft);
            color: var(--pr-accent-strong);
        }

        .status-badge.is-prorate {
            background: var(--pr-warn-soft);
            color: var(--pr-warn);
        }

        .status-badge.is-type {
            background: transparent;
            border: 1px solid var(--pr-rule);
            color: var(--pr-ink-2);
        }

        /* Row actions (HTML from PayrollController::getPayroll) */
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
            cursor: pointer;
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

        .act-btn:focus-visible {
            outline: 2px solid var(--pr-focus);
            outline-offset: 2px;
        }

        .act-btn.act-success:hover {
            background: var(--pr-success-soft);
            border-color: var(--pr-success);
            color: var(--pr-success);
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

        .act-btn.act-info:hover,
        .act-btn.act-primary:hover {
            background: var(--pr-accent-soft);
            border-color: var(--pr-accent);
            color: var(--pr-accent-strong);
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

        .pr-dt-bottom>div {
            display: flex;
            align-items: center;
            gap: var(--pr-space-md);
        }

        .pr .dataTables_wrapper .dataTables_length,
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

        .pr .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--pr-paper-2) !important;
            color: var(--pr-ink) !important;
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

        .pr .dataTables_wrapper .dataTables_length select {
            min-height: 34px;
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            padding: 0 var(--pr-space-xs);
            font-family: var(--pr-font);
        }

        .pr .dataTables_wrapper .dataTables_processing {
            background: var(--pr-panel);
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            color: var(--pr-ink-2);
            font-size: var(--pr-text-sm);
        }

        .pr .dataTables_scrollBody,
        .pr .table-responsive {
            overflow-x: auto;
        }

        @media (max-width: 75rem) {
            .pr-ledger {
                grid-template-columns: minmax(0, 1fr);
            }

            .pr-flow {
                border-right: 0;
                border-bottom: 1px solid var(--pr-rule);
            }

            .pr-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 40rem) {
            .pr-money {
                grid-template-columns: minmax(0, 1fr);
            }

            .pr-money>div:nth-child(odd) {
                border-right: 0;
            }

            .pr-money>div:nth-last-child(2) {
                border-bottom: 1px solid var(--pr-rule);
            }

            .pr-filter-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .pr-field select,
            .pr-btn,
            .pr-search input {
                min-height: 44px;
                font-size: 16px;
            }

            .pr-actions,
            .pr-toolbar-right,
            .pr-search,
            .pr-search input {
                width: 100%;
            }

            .pr-actions>* {
                flex: 1 1 auto;
            }

            .pr-actions .dropdown>.pr-btn {
                width: 100%;
            }

            .pr-views {
                width: 100%;
                overflow-x: auto;
            }

            .pr-views button {
                flex: 1 0 auto;
            }

            .pr-head {
                padding: var(--pr-space-md);
            }

            .pr-panel-head,
            .pr-toolbar,
            .pr-filter-body,
            .pr-table-wrap,
            .bulk-bar,
            .pr-checks,
            .pr-flow,
            .pr-money>div {
                padding-left: var(--pr-space-md);
                padding-right: var(--pr-space-md);
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
        $isOpen = $period->isOpen();
        $pct = fn($n) => $stats['total'] > 0 ? round(($n / $stats['total']) * 100, 2) : 0;
        $rp = fn($n) => number_format($n, 0, ',', '.');
        $statusLabel =
            ['open' => 'Open', 'closed' => 'Closed', 'locked' => 'Locked'][$period->status] ?? ucfirst($period->status);
        $periodBadge =
            ['open' => 'is-approved', 'closed' => 'is-prorate', 'locked' => 'is-draft'][$period->status] ?? 'is-draft';
    @endphp
    <div class="main-content pr">
        <section class="section">

            {{-- Head --}}
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs" aria-label="Breadcrumb">
                        <li>Dashboard</li>
                        <li><a href="{{ route('payrollperiod.index') }}">Payroll Periods</a></li>
                        <li aria-current="page">{{ $period->period_label }}</li>
                    </ol>
                    <h1 class="pr-title">Payroll {{ $period->period_label }}</h1>
                    <div class="pr-meta">
                        <span><i class="fas fa-calendar-alt"
                                aria-hidden="true"></i>{{ $period->period_start->format('d M Y') }} sampai
                            {{ $period->period_end->format('d M Y') }}</span>
                        <span class="status-badge {{ $periodBadge }}">Periode {{ $statusLabel }}</span>
                        @if ($period->note)
                            <span>{{ $period->note }}</span>
                        @endif
                    </div>
                </div>

                <div class="pr-actions">
                    @can('payroll.export')
                        <button type="button" class="pr-btn" id="btn-export">
                            <i class="fas fa-file-excel" aria-hidden="true"></i> Export
                        </button>
                    @endcan
                    @if (
                        $isOpen &&
                            auth()->user()->canAny(['payroll.download.template', 'payroll.import']))
                        <div class="dropdown">
                            <button type="button" class="pr-btn" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                <i class="fas fa-file-import" aria-hidden="true"></i> Data eksternal
                                <i class="fas fa-chevron-down" aria-hidden="true"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right pr-menu">
                                @can('payroll.download.template')
                                    <a href="{{ route('payroll.attendanceTemplate', $period->id) }}" class="dropdown-item">
                                        <i class="fas fa-download" aria-hidden="true"></i> Download template
                                    </a>
                                @endcan
                                @can('payroll.import')
                                    <button type="button" class="dropdown-item" data-toggle="modal"
                                        data-target="#importAttendanceModal">
                                        <i class="fas fa-upload" aria-hidden="true"></i> Import file Excel
                                    </button>
                                @endcan
                            </div>
                        </div>
                    @endif
                    @if ($isOpen && auth()->user()->can('payroll.generate'))
                        <a href="{{ route('payroll.generate', $period->id) }}" class="pr-btn pr-btn-primary"
                            onclick="return confirm('Generate payroll untuk semua employee?')">
                            <i class="fas fa-cog" aria-hidden="true"></i> Generate payroll
                        </a>
                    @endif
                </div>
            </header>

            {{-- Hasil import data eksternal --}}
            @if (session('skipped') || session('import_notes'))
                <section class="pr-panel pr-import-result" aria-labelledby="import-result-title">
                    <div class="pr-panel-head">
                        <h2 class="pr-panel-title" id="import-result-title">Hasil import data eksternal</h2>
                    </div>
                    @if (session('skipped'))
                        <div class="pr-import-group is-skipped">
                            <strong>{{ count(session('skipped')) }} baris dilewati, tidak ada yang diubah:</strong>
                            <ul>
                                @foreach (session('skipped') as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (session('import_notes'))
                        <div class="pr-import-group is-note">
                            <strong>{{ count(session('import_notes')) }} baris diimpor dengan catatan:</strong>
                            <ul>
                                @foreach (session('import_notes') as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </section>
            @endif

            {{-- Ledger --}}
            <section class="pr-panel" aria-labelledby="ledger-title">
                <h2 class="visually-hidden sr-only" id="ledger-title">Ringkasan periode</h2>
                @if ($stats['total'] === 0)
                    <div class="pr-empty">
                        <div>
                            <h2>Belum ada payroll di periode ini</h2>
                            <p>
                                Generate payroll untuk menghitung gaji semua karyawan aktif dari data gaji, attendance, dan
                                lembur
                                periode {{ $period->period_start->format('d M') }} sampai
                                {{ $period->period_end->format('d M Y') }}.
                            </p>
                        </div>
                        @if ($isOpen && auth()->user()->can('payroll.generate'))
                            <a href="{{ route('payroll.generate', $period->id) }}" class="pr-btn pr-btn-primary"
                                onclick="return confirm('Generate payroll untuk semua employee?')">
                                <i class="fas fa-cog" aria-hidden="true"></i> Generate payroll
                            </a>
                        @endif
                    </div>
                @else
                    <div class="pr-ledger">
                        <div class="pr-flow">
                            <div class="pr-flow-total">
                                <strong>{{ $stats['total'] }}</strong>
                                <span>payroll di periode ini</span>
                            </div>
                            <div class="pr-bar" role="img"
                                aria-label="{{ $stats['draft'] }} draft, {{ $stats['approved'] }} approved, {{ $stats['paid'] }} paid">
                                <span class="is-paid" style="width: {{ $pct($stats['paid']) }}%"></span>
                                <span class="is-approved" style="width: {{ $pct($stats['approved']) }}%"></span>
                                <span class="is-draft" style="width: {{ $pct($stats['draft']) }}%"></span>
                            </div>
                            <ul class="pr-legend" aria-label="Filter berdasarkan status payroll">
                                <li class="is-draft">
                                    <button type="button" class="js-status-filter" data-status="draft"
                                        aria-pressed="false">
                                        <span class="pr-legend-label"><span class="pr-swatch"></span>Draft</span>
                                        <span class="pr-legend-value">{{ $stats['draft'] }}</span>
                                    </button>
                                </li>
                                <li class="is-approved">
                                    <button type="button" class="js-status-filter" data-status="approved"
                                        aria-pressed="false">
                                        <span class="pr-legend-label"><span class="pr-swatch"></span>Approved</span>
                                        <span class="pr-legend-value">{{ $stats['approved'] }}</span>
                                    </button>
                                </li>
                                <li class="is-paid">
                                    <button type="button" class="js-status-filter" data-status="paid"
                                        aria-pressed="false">
                                        <span class="pr-legend-label"><span class="pr-swatch"></span>Paid</span>
                                        <span class="pr-legend-value">{{ $stats['paid'] }}</span>
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <dl class="pr-money">
                            <div>
                                <dt>Total gross</dt>
                                <dd><small>Rp</small>{{ $rp($stats['total_gross']) }}</dd>
                            </div>
                            <div>
                                <dt>Total lembur</dt>
                                <dd><small>Rp</small>{{ $rp($stats['total_overtime']) }}</dd>
                            </div>
                            <div class="is-deduction">
                                <dt>Total potongan</dt>
                                <dd><small>Rp</small>{{ $rp($stats['total_deduction']) }}</dd>
                            </div>
                            <div class="is-net">
                                <dt>Total net salary</dt>
                                <dd><small>Rp</small>{{ $rp($stats['total_net']) }}</dd>
                                <span class="pr-money-note">Semua status</span>
                            </div>
                        </dl>
                    </div>

                    @if ($stats['net_negative'] > 0 || $stats['no_attendance'] > 0)
                        <div class="pr-checks" role="status">
                            <span class="pr-checks-title">Perlu dicek sebelum approve</span>
                            @if ($stats['net_negative'] > 0)
                                <span class="pr-check">
                                    <span><strong>{{ $stats['net_negative'] }}</strong> draft net salary minus, tidak bisa
                                        di-approve</span>
                                    <button type="button" class="pr-link js-flag" data-flag="net_negative"
                                        data-label="Net salary minus">Tampilkan</button>
                                </span>
                            @endif
                            @if ($stats['no_attendance'] > 0)
                                <span class="pr-check">
                                    <span><strong>{{ $stats['no_attendance'] }}</strong> draft tanpa attendance (0
                                        hari)</span>
                                    <button type="button" class="pr-link js-flag" data-flag="no_attendance"
                                        data-label="Attendance 0 hari">Tampilkan</button>
                                </span>
                            @endif
                        </div>
                    @elseif (!$isOpen)
                        <div class="pr-closed">Periode {{ strtolower($statusLabel) }}: payroll tidak bisa di-generate,
                            diubah, atau di-approve lagi.</div>
                    @endif
                @endif
            </section>

            {{-- Table --}}
            <section class="pr-panel" aria-labelledby="table-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="table-title">Daftar payroll</h2>
                        <span class="pr-panel-sub" id="record-count">Memuat data</span>
                    </div>
                </div>

                <div class="pr-toolbar">
                    <div class="pr-views" role="group" aria-label="Tampilan kolom">
                        <button type="button" data-view="ringkas" aria-pressed="true">Ringkas</button>
                        <button type="button" data-view="pendapatan" aria-pressed="false">Pendapatan</button>
                        <button type="button" data-view="potongan" aria-pressed="false">Potongan</button>
                        <button type="button" data-view="semua" aria-pressed="false">Semua kolom</button>
                    </div>
                    <div class="pr-toolbar-right">
                        <label class="visually-hidden sr-only" for="pr-search">Cari payroll</label>
                        <span class="pr-search">
                            <i class="fas fa-search" aria-hidden="true"></i>
                            <input type="search" id="pr-search" placeholder="Cari nama, NIP, location"
                                autocomplete="off">
                        </span>
                        <span class="pr-chip" id="flag-chip" hidden>
                            <span id="flag-chip-label"></span>
                            <button type="button" id="flag-clear" aria-label="Hapus filter penanda">
                                <i class="fas fa-times" aria-hidden="true"></i>
                            </button>
                        </span>
                        <button type="button" class="pr-btn pr-btn-sm" id="btn-toggle-filter" aria-expanded="false"
                            aria-controls="filter-panel">
                            <i class="fas fa-filter" aria-hidden="true"></i> Filter
                            <span class="pr-filter-count" id="filter-count" hidden>0</span>
                        </button>
                    </div>
                </div>

                <div class="pr-filter-body" id="filter-panel" hidden>
                    <div class="pr-filter-grid">
                        <div class="pr-field">
                            <label for="filter-status">Status payroll</label>
                            <select id="filter-status">
                                <option value="">Semua status</option>
                                <option value="draft">Draft</option>
                                <option value="approved">Approved</option>
                                <option value="paid">Paid</option>
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
                                    <option value="{{ $department->department_name }}">{{ $department->department_name }}
                                    </option>
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
                        <div class="pr-field">
                            <label for="filter-status-employee">Tipe karyawan</label>
                            <select id="filter-status-employee">
                                <option value="">Semua tipe</option>
                                <option value="PKWT">PKWT</option>
                                <option value="On Job Training">On Job Training</option>
                                <option value="DW">Daily Worker</option>
                            </select>
                        </div>
                        <div class="pr-field">
                            <label for="filter-untuk-active-inactive">Status karyawan</label>
                            <select id="filter-untuk-active-inactive">
                                <option value="">Semua status karyawan</option>
                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="pr-filter-actions">
                        <button type="button" class="pr-btn pr-btn-ghost" id="btn-reset">
                            <i class="fas fa-redo" aria-hidden="true"></i> Reset
                        </button>
                        <button type="button" class="pr-btn" id="btn-search">
                            <i class="fas fa-search" aria-hidden="true"></i> Terapkan filter
                        </button>
                    </div>
                </div>

                <div class="bulk-bar" id="bulk-bar" role="region" aria-label="Aksi untuk payroll terpilih">
                    <span class="bulk-bar-count"><span id="selected-count">0</span> dipilih</span>
                    @can('payroll.approve.bulk')
                        <button type="button" class="pr-btn pr-btn-sm" id="btn-do-bulk-approve">
                            <i class="fas fa-check-double" aria-hidden="true"></i> Approve
                        </button>
                    @endcan
                    @can('payroll.send.slip.bulk')
                        <button type="button" class="pr-btn pr-btn-sm" id="btn-do-bulk-send-slip">
                            <i class="fas fa-paper-plane" aria-hidden="true"></i> Kirim slip email
                        </button>
                    @endcan
                    @can('payroll.slip.bulk')
                        <a href="#" id="btn-do-bulk-download-slip" class="pr-btn pr-btn-sm" target="_blank">
                            <i class="fas fa-file-pdf" aria-hidden="true"></i> Download slip
                        </a>
                    @endcan
                    @can('payroll.destroy.bulk')
                        <button type="button" class="pr-btn pr-btn-sm pr-btn-danger" id="btn-do-bulk-delete">
                            <i class="fas fa-trash" aria-hidden="true"></i> Hapus
                        </button>
                    @endcan
                    <button type="button" class="pr-btn pr-btn-sm pr-btn-ghost" id="btn-clear-select">
                        Batal pilih
                    </button>
                    <span class="bulk-bar-hint">Approve dan hapus hanya berlaku untuk draft. Slip hanya untuk payroll
                        approved atau paid.</span>
                </div>

                <div class="table-responsive pr-table-wrap">
                    <table id="payroll-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="check-all" aria-label="Pilih semua di halaman ini"></th>
                                <th>#</th>
                                <th>NIP</th>
                                <th>Employee</th>
                                <th>Company</th>
                                <th>Department</th>
                                <th>Grading</th>
                                <th>Location</th>
                                <th>Position</th>
                                <th>Type</th>
                                <th>Emp. status</th>
                                <th>Work days</th>
                                <th>Attendance</th>
                                <th class="num">Basic salary</th>
                                <th class="num">Pos. allowance</th>
                                <th class="num">Daily rate</th>
                                <th class="num">Gross</th>
                                <th class="num">Meal</th>
                                <th class="num">Transport</th>
                                <th class="num">House</th>
                                <th class="num">Overtime</th>
                                <th class="num">Reimburse</th>
                                <th class="num">Income</th>
                                <th class="num">BPJS Kesehatan</th>
                                <th class="num">BPJS Ketenagakerjaan</th>
                                <th class="num">Punish</th>
                                <th class="num">SO punish</th>
                                <th class="num">Debt</th>
                                <th class="num">Tax</th>
                                <th class="num">Deduction</th>
                                <th class="num">Net salary</th>
                                <th>Status</th>
                                <th style="text-align:center">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </section>

        </section>
    </div>


    @can('payroll.import')
        {{-- Modal --}}
        <div class="modal fade" id="importAttendanceModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title pr-modal-title">Import data eksternal</h2>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <form action="{{ route('payroll.importAttendance', $period->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-info" style="font-size:.8rem">
                                <i class="fas fa-info-circle"></i>
                                Download template dulu, isi kolom <code>reamburse_amount, punishment, punishment_so, debt,
                                    tax</code>,
                                lalu upload di sini.
                            </div>
                            <div class="form-group">
                                <label>File Excel</label>
                                <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
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
    @include('partials.datatables-error', ['permission' => 'payroll.view'])
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(function() {

            // ── Column views ──
            // Kolom tetap lengkap di DOM (export & sorting tidak berubah); tampilan hanya menyembunyikan.
            const VIEWS = {
                ringkas: ['check', 'employee', 'store', 'attendance', 'gross', 'income', 'deduction', 'net',
                    'status', 'action'
                ],
                pendapatan: ['check', 'employee', 'attendance', 'basic', 'pos_allowance', 'daily_rate', 'gross',
                    'meal', 'transport', 'house', 'overtime', 'reimburse', 'income', 'net', 'status',
                    'action'
                ],
                potongan: ['check', 'employee', 'income', 'bpjs_kes', 'bpjs_tk', 'punish', 'punish_so', 'debt',
                    'tax', 'deduction', 'net', 'status', 'action'
                ],
                // NIP sudah tampil di bawah nama; kolom NIP tetap ada untuk sorting default
                semua: ['check', 'no', 'employee', 'company', 'department', 'grading', 'store', 'position',
                    'type',
                    'emp_status', 'work_days', 'attendance', 'basic', 'pos_allowance', 'daily_rate',
                    'gross', 'meal',
                    'transport', 'house', 'overtime', 'reimburse', 'income', 'bpjs_kes', 'bpjs_tk',
                    'punish',
                    'punish_so', 'debt', 'tax', 'deduction', 'net', 'status', 'action'
                ],
            };
            // Kolom yang bukan kolom tabel payrolls (dihitung dari detail) tidak bisa diurutkan di server
            const computed = ['meal_allowance', 'transport_allowance', 'house_allowance', 'bpjs_kesehatan',
                'bpjs_ketenagakerjaan'
            ];
            const money = (field, extraClass = '') => ({
                data: field + '_fmt',
                name: field,
                className: 'num',
                searchable: false,
                orderable: !computed.includes(field),
                render: (d, t) => t === 'display' && extraClass ?
                    `<span class="${extraClass}">${d}</span>` : d,
            });
            const esc = v => $('<div>').text(v ?? '-').html();

            const params = new URLSearchParams(window.location.search);
            let currentView = VIEWS.hasOwnProperty(params.get('view')) ? params.get('view') : 'ringkas';
            let currentFlag = ['net_negative', 'no_attendance'].includes(params.get('flag')) ? params.get('flag') :
                '';
            if (['draft', 'approved', 'paid'].includes(params.get('status'))) {
                $('#filter-status').val(params.get('status'));
            }

            // ── DataTable ──
            const table = $('#payroll-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                dom: 'rt<"pr-dt-bottom"<"pr-dt-left"li>p>',

                ajax: {
                    url: '{{ route('payroll.data', $period->id) }}',
                    data: function(d) {
                        d.status = $('#filter-status').val();
                        d.status_type = $('#filter-status-employee').val();
                        d.status_employee = $('#filter-untuk-active-inactive').val();
                        d.store_name = $('#filter-store').val();
                        d.company_name = $('#filter-company').val();
                        d.department_name = $('#filter-department').val();
                        d.grading_name = $('#filter-grading').val();
                        d.flag = currentFlag;
                    }
                },
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ],
                columns: [{
                        data: null,
                        name: 'check',
                        orderable: false,
                        searchable: false,
                        // checkbox muncul untuk semua status; backend (approveBulk/destroyBulk)
                        // sudah filter where('status','draft') dan melaporkan yang dilewati
                        render: (data, type, row) =>
                            `<input type="checkbox" class="row-check" data-id="${row.id}" aria-label="Pilih ${esc(row.employee_name)}">`,
                    },
                    {
                        data: null,
                        name: 'no',
                        render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1,
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'employee_pengenal',
                        name: 'employee_pengenal',
                        defaultContent: '-'
                    },
                    {
                        data: 'employee_name',
                        name: 'employee_name',
                        defaultContent: '-',
                        render: (d, t, row) => t !== 'display' ? d :
                            `<span class="pr-emp-name">${esc(d)}</span>` +
                            `<span class="pr-emp-sub"><span>${esc(row.employee_pengenal)}</span><span>${esc(row.position_name)}</span></span>`,
                    },
                    {
                        data: 'company_name',
                        name: 'company_name',
                        defaultContent: '-'
                    },
                    {
                        data: 'department_name',
                        name: 'department_name',
                        defaultContent: '-'
                    },
                    {
                        data: 'grading_name',
                        name: 'grading_name',
                        defaultContent: '-'
                    },
                    {
                        data: 'store_name',
                        name: 'store_name',
                        defaultContent: '-'
                    },
                    {
                        data: 'position_name',
                        name: 'position_name',
                        defaultContent: '-'
                    },
                    {
                        data: 'status_type_badge',
                        name: 'status_type_badge',
                    },
                    {
                        data: 'status_employee',
                        name: 'status_employee',
                    },
                    {
                        data: null,
                        name: 'work_days',
                        render: (d, t, row) => `${row.working_days} hari`,
                        orderable: false,
                    },
                    {
                        data: null,
                        name: 'attendance',
                        render: (d, t, row) => {
                            const zero = Number(row.attendance_days) === 0;
                            return `<span class="pr-att${zero ? ' is-zero' : ''}">${row.attendance_days}</span>` +
                                `<span class="pr-muted"> / ${row.working_days} hari</span>`;
                        },
                        orderable: false,
                    },
                    money('basic_salary'),
                    money('position_allowance'),
                    money('daily_rate'),
                    money('gross_salary'),
                    money('meal_allowance'),
                    money('transport_allowance'),
                    money('house_allowance'),
                    money('overtime_amount'),
                    money('reimburse_amount'),
                    money('total_income', 'pr-income'),
                    money('bpjs_kesehatan'),
                    money('bpjs_ketenagakerjaan'),
                    money('punishment'),
                    money('punishment_so'),
                    money('debt'),
                    money('tax'),
                    money('total_deduction', 'pr-deduction'),
                    {
                        data: 'net_salary_fmt',
                        name: 'net_salary_fmt',
                        className: 'num',
                        render: (d, t, row) => t !== 'display' ? d :
                            `<span class="pr-net${Number(row.net_salary) < 0 ? ' is-negative' : ''}">${d}</span>`
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                    },
                ],
                createdRow: function(tr, row) {
                    if (row.status === 'draft' && Number(row.net_salary) < 0) {
                        $(tr).addClass('is-flagged');
                    }
                },
                drawCallback: function() {
                    const info = this.api().page.info();
                    $('#record-count').text(
                        info.recordsDisplay === info.recordsTotal ?
                        `${info.recordsTotal} karyawan` :
                        `${info.recordsDisplay} dari ${info.recordsTotal} karyawan sesuai filter`
                    );
                    $('#check-all').prop('checked', false);
                    updateBulkBar();
                },
                language: {
                    processing: 'Memuat data...',
                    emptyTable: 'Belum ada data payroll. Klik Generate payroll.',
                    zeroRecords: 'Tidak ada payroll yang cocok dengan filter.',
                    lengthMenu: 'Tampilkan _MENU_',
                    info: '_START_ sampai _END_ dari _TOTAL_',
                    infoEmpty: '0 data',
                    infoFiltered: '',
                    paginate: {
                        previous: 'Sebelumnya',
                        next: 'Berikutnya'
                    },
                },
                order: [
                    [2, 'asc']
                ],
                pageLength: 25,
            });

            // Map nama kolom -> index (columns dengan money() memakai nama field)
            const colAlias = {
                basic: 'basic_salary',
                pos_allowance: 'position_allowance',
                daily_rate: 'daily_rate',
                gross: 'gross_salary',
                meal: 'meal_allowance',
                transport: 'transport_allowance',
                house: 'house_allowance',
                overtime: 'overtime_amount',
                reimburse: 'reimburse_amount',
                income: 'total_income',
                bpjs_kes: 'bpjs_kesehatan',
                bpjs_tk: 'bpjs_ketenagakerjaan',
                punish: 'punishment',
                punish_so: 'punishment_so',
                debt: 'debt',
                tax: 'tax',
                deduction: 'total_deduction',
                // nama kolom DataTables = nama kolom di controller (dipakai untuk sorting server)
                employee: 'employee_name',
                company: 'company_name',
                department: 'department_name',
                grading: 'grading_name',
                store: 'store_name',
                position: 'position_name',
                type: 'status_type_badge',
                emp_status: 'status_employee',
                net: 'net_salary_fmt',
            };

            function syncUrl() {
                const p = new URLSearchParams(window.location.search);
                currentView === 'ringkas' ? p.delete('view') : p.set('view', currentView);
                currentFlag ? p.set('flag', currentFlag) : p.delete('flag');
                const st = $('#filter-status').val();
                st ? p.set('status', st) : p.delete('status');
                const qs = p.toString();
                history.replaceState(null, '', window.location.pathname + (qs ? '?' + qs : ''));
            }

            function applyView(view) {
                currentView = view;
                const wanted = VIEWS[view];
                const visible = wanted ? new Set(wanted.map(n => colAlias[n] ?? n)) : null;
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

            $('.pr-views').on('click', 'button', function() {
                applyView($(this).data('view'));
            });

            // ── Filter ──
            const filterSelects =
                '#filter-status, #filter-status-employee, #filter-store, #filter-company, #filter-department, #filter-grading, #filter-untuk-active-inactive';

            function refreshFilterUi() {
                const active = $(filterSelects).filter((i, el) => $(el).val()).length;
                $('#filter-count').text(active).prop('hidden', active === 0);
                const st = $('#filter-status').val();
                $('.js-status-filter').each(function() {
                    $(this).attr('aria-pressed', $(this).data('status') === st ? 'true' : 'false');
                });
                const flagBtn = $(`.js-flag[data-flag="${currentFlag}"]`);
                $('#flag-chip-label').text(currentFlag ? (flagBtn.data('label') ?? currentFlag) : '');
                $('#flag-chip').prop('hidden', !currentFlag);
                syncUrl();
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
                $(filterSelects).val('');
                currentFlag = '';
                refreshFilterUi();
                table.ajax.reload();
            });

            $('.js-status-filter').on('click', function() {
                const st = $(this).data('status');
                $('#filter-status').val($('#filter-status').val() === st ? '' : st);
                refreshFilterUi();
                table.ajax.reload();
            });

            $('.js-flag').on('click', function() {
                currentFlag = $(this).data('flag');
                refreshFilterUi();
                table.ajax.reload();
                document.getElementById('table-title').scrollIntoView({
                    block: 'start'
                });
            });

            $('#flag-clear').on('click', () => {
                currentFlag = '';
                refreshFilterUi();
                table.ajax.reload();
            });

            // Search di toolbar (pengganti kotak search bawaan DataTables)
            let searchTimer = null;
            $('#pr-search').on('input', function() {
                clearTimeout(searchTimer);
                const q = this.value;
                searchTimer = setTimeout(() => table.search(q).draw(), 350);
            });

            applyView(currentView);
            refreshFilterUi();

            // ── Checkbox select all ──
            $(document).on('change', '#check-all', function() {
                $('.row-check').prop('checked', this.checked);
                updateBulkBar();
            });

            $(document).on('change', '.row-check', function() {
                updateBulkBar();
            });

            function updateBulkBar() {
                const count = $('.row-check:checked').length;
                $('#selected-count').text(count);
                if (count > 0) {
                    $('#bulk-bar').addClass('show');
                } else {
                    $('#bulk-bar').removeClass('show');
                }

                // ← update href download slip setiap kali jumlah checked berubah
                const ids = $('.row-check:checked').map((i, el) => $(el).data('id')).get();
                const url = '{{ route('payroll.slipBulk', $period->id) }}' +
                    (ids.length ? '?' + ids.map(id => 'ids[]=' + id).join('&') : '');
                $('#btn-do-bulk-download-slip').attr('href', url);
            }

            $('#btn-clear-select').on('click', () => {
                $('.row-check, #check-all').prop('checked', false);
                updateBulkBar();
            });

            // ── Approve satu ──
            $(document).on('click', '.btn-approve', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Approve payroll ini?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#16a34a',
                    cancelButtonText: 'Batal',
                    confirmButtonText: 'Ya, Approve',
                }).then(result => {
                    if (!result.isConfirmed) return;

                    $.post('{{ url('payroll') }}/' + id + '/approve', {
                            _token: '{{ csrf_token() }}'
                        })
                        .done(res => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Approved!',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            table.ajax.reload();
                        })
                        .fail(err => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: err.responseJSON?.error ?? 'Terjadi kesalahan.'
                            });
                        });
                });
            });

            // ── Bulk Approve ──
            $('#btn-do-bulk-approve').on('click', function() {
                const ids = $('.row-check:checked').map((i, el) => $(el).data('id')).get();

                if (ids.length === 0) return;

                Swal.fire({
                    title: `Approve ${ids.length} payroll?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#16a34a',
                    cancelButtonText: 'Batal',
                    confirmButtonText: 'Ya, Approve Semua',
                }).then(result => {
                    if (!result.isConfirmed) return;

                    $.post('{{ route('payroll.approveBulk') }}', {
                            _token: '{{ csrf_token() }}',
                            ids: ids,
                        })
                        .done(res => {
                            const skipped = res.skipped ?? [];
                            if (skipped.length === 0) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Bulk Approved!',
                                    text: res.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            } else {
                                const list = $('<ul>').css({
                                    textAlign: 'left',
                                    fontSize: '.85rem',
                                    maxHeight: '220px',
                                    overflowY: 'auto'
                                });
                                skipped.forEach(item => list.append($('<li>').text(item)));
                                Swal.fire({
                                    icon: res.approved > 0 ? 'warning' : 'error',
                                    title: res.approved > 0 ? 'Sebagian di-approve' :
                                        'Tidak ada yang di-approve',
                                    html: $('<div>').append($('<p>').text(res.message),
                                        list).html(),
                                });
                            }
                            $('.row-check, #check-all').prop('checked', false);
                            updateBulkBar();
                            table.ajax.reload();
                        })
                        .fail(err => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: err.responseJSON?.error ?? 'Terjadi kesalahan.'
                            });
                        });
                });
            });

            $(document).on('click', '.btn-delete-single', function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Hapus Payroll?',
                    text: 'Data payroll dan detail akan dihapus permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: '{{ url('payroll') }}/' + id,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE',
                        },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Dihapus!',
                                    text: res.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                table.ajax.reload(null, false);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: res.message
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: xhr.responseJSON?.message ??
                                    'Terjadi kesalahan.'
                            });
                        }
                    });
                });
            });

            // ── Bulk delete ──
            $('#btn-do-bulk-delete').on('click', function() {
                const ids = $('.row-check:checked').map((i, el) => $(el).data('id')).get();

                if (ids.length === 0) return;

                Swal.fire({
                    title: `Hapus ${ids.length} Payroll?`,
                    text: 'Semua data payroll dan detail yang dipilih akan dihapus permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus Semua!',
                    cancelButtonText: 'Batal',
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $.post('{{ route('payroll.destroyBulk') }}', {
                            _token: '{{ csrf_token() }}',
                            ids: ids,
                        })
                        .done(res => {
                            if (res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Dihapus!',
                                    text: res.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                $('.row-check, #check-all').prop('checked', false);
                                updateBulkBar();
                                table.ajax.reload(null, false);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: res.message
                                });
                            }
                        })
                        .fail(err => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: err.responseJSON?.message ?? 'Terjadi kesalahan.'
                            });
                        });
                });
            });

            // ── Export ──
            $('#btn-export').on('click', () => {
                const params = new URLSearchParams({
                    status: $('#filter-status').val(),
                    status_employee: $('#filter-status-employee').val(),
                    store_name: $('#filter-store').val(),
                    company_name: $('#filter-company').val(),
                    department_name: $('#filter-department').val(),
                    grading_name: $('#filter-grading').val(),
                });
                window.location.href = '{{ route('payroll.export', $period->id) }}?' + params.toString();
            });

            // ── Kirim slip per-baris ──
            $(document).on('click', '.btn-send-slip', function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Kirim slip gaji?',
                    text: 'Email akan diproses di background.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#1d4ed8',
                    confirmButtonText: 'Ya, Kirim',
                    cancelButtonText: 'Batal',
                }).then(result => {
                    if (!result.isConfirmed) return;

                    $.post('{{ url('payroll') }}/' + id + '/slip/send', {
                            _token: '{{ csrf_token() }}'
                        })
                        .done(res => Swal.fire({
                            icon: 'success',
                            title: 'Terkirim!',
                            text: res.message ?? res,
                            timer: 2500,
                            showConfirmButton: false
                        }))
                        .fail(err => Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: err.responseJSON?.message ?? 'Terjadi kesalahan.'
                        }));
                });
            });

            // ── Kirim slip bulk ──
            $('#btn-do-bulk-send-slip').on('click', function() {
                const ids = $('.row-check:checked').map((i, el) => $(el).data('id')).get();

                if (ids.length === 0) return;

                Swal.fire({
                    title: `Kirim slip ke ${ids.length} karyawan?`,
                    text: 'Email akan diproses di background, mungkin butuh beberapa menit.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#1d4ed8',
                    confirmButtonText: 'Ya, Kirim',
                    cancelButtonText: 'Batal',
                }).then(result => {
                    if (!result.isConfirmed) return;

                    $.post('{{ route('payroll.slipSendBulk', $period->id) }}', {
                            _token: '{{ csrf_token() }}',
                            ids: ids,
                        })
                        .done(res => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Masuk Antrian!',
                                text: res.message
                            });
                            $('.row-check, #check-all').prop('checked', false);
                            updateBulkBar();
                        })
                        .fail(err => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: err.responseJSON?.message ?? 'Terjadi kesalahan.'
                            });
                        });
                });
            });

            // ── Flash messages ──
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#1d4ed8',
                    timer: 4000,
                    timerProgressBar: true,
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: '{{ session('error') }}',
                    confirmButtonColor: '#dc2626',
                });
            @endif
        });
    </script>
@endpush
