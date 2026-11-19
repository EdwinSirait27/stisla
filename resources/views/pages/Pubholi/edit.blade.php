@extends('layouts.app')
@section('title', 'Ubah public holiday')
@push('styles')
    <style>
        /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand · redesign: full
         * paper: oklch(98.4% 0.004 265) · accent: oklch(61% 0.17 276) · display: Nunito · body: Nunito
         * nav: kept (Stisla shell) · signature: what the date will do to the roster, said before saving · states: no PH this year, past, today, locked period, Sunday PH, with/without roster
         * axes: light/rounded-sans/chromatic-indigo · posture: app · contrast: pass · v2.0.0 */
        /* Hallmark · pre-emit critique: P4 H4 E4 S4 R4 V4 · weakest: S, a list not a month grid; chosen because 30 dates a year read faster as a list */
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

        .status-badge.is-draft {
            background: var(--pr-paper-2);
            color: var(--pr-ink-2);
        }

        /* ── Public holiday ── */
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

        .status-badge.is-type {
            background: var(--pr-accent-soft);
            color: var(--pr-accent-deep);
        }

        .ph-next {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            align-items: center;
            gap: var(--pr-space-lg);
            padding: var(--pr-space-lg);
        }

        .ph-next-date {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 96px;
            height: 96px;
            border-radius: var(--pr-radius);
            background: var(--pr-accent-strong);
            color: var(--pr-accent-ink);
        }

        .ph-next-day {
            font-size: 2.4rem;
            font-weight: 800;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }

        .ph-next-mon {
            margin-top: 4px;
            font-size: var(--pr-text-xs);
            font-weight: 700;
        }

        .ph-next-kicker {
            margin: 0;
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-accent-strong);
        }

        .ph-next-name {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: var(--pr-space-xs);
            margin: 2px 0 0;
            font-size: var(--pr-text-xl);
            font-weight: 800;
            line-height: 1.25;
            color: var(--pr-ink);
            overflow-wrap: break-word;
            min-width: 0;
        }

        .ph-next-sub {
            margin: var(--pr-space-2xs) 0 0;
            font-size: var(--pr-text-sm);
            color: var(--pr-ink-2);
        }

        .ph-year-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, auto));
            gap: var(--pr-space-xs) var(--pr-space-lg);
            margin: 0;
            padding-left: var(--pr-space-lg);
            border-left: 1px solid var(--pr-rule);
        }

        .ph-year-stats dt {
            font-size: var(--pr-text-xs);
            font-weight: 600;
            color: var(--pr-ink-2);
        }

        .ph-year-stats dd {
            margin: 0;
            font-size: var(--pr-text-lg);
            font-weight: 800;
            font-variant-numeric: tabular-nums;
            color: var(--pr-ink);
        }

        .ph-year-stats dd small {
            font-size: var(--pr-text-xs);
            font-weight: 600;
            color: var(--pr-muted);
        }

        .ph-select {
            min-height: 36px;
            padding: 0 var(--pr-space-sm);
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-panel);
            font-family: var(--pr-font);
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-ink);
        }

        .ph-select:focus-visible {
            outline: 2px solid var(--pr-focus);
            outline-offset: 1px;
        }

        .ph-tab {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: var(--pr-radius-sm);
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-ink-2);
            text-decoration: none;
            transition: background-color var(--pr-dur-micro) var(--pr-ease);
        }

        .ph-tab:hover {
            background: var(--pr-paper-2);
            color: var(--pr-ink);
            text-decoration: none;
        }

        .ph-tab[aria-current="true"] {
            background: var(--pr-panel);
            color: var(--pr-ink);
            box-shadow: 0 1px 2px var(--pr-shadow-pressed);
        }

        .ph-tab:focus-visible {
            outline: 2px solid var(--pr-focus);
            outline-offset: 1px;
        }

        .ph-month {
            display: flex;
            align-items: baseline;
            gap: var(--pr-space-xs);
            margin: 0;
            padding: var(--pr-space-sm) var(--pr-space-lg) var(--pr-space-2xs);
            font-size: var(--pr-text-md);
            font-weight: 800;
            color: var(--pr-ink);
        }

        .ph-month span {
            font-size: var(--pr-text-xs);
            font-weight: 600;
            color: var(--pr-muted);
        }

        .ph-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .ph-row {
            display: grid;
            grid-template-columns: 56px minmax(0, 1fr) auto auto;
            align-items: center;
            gap: var(--pr-space-md);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-bottom: 1px solid var(--pr-rule);
        }

        .ph-row.is-past .ph-name,
        .ph-row.is-past .ph-date-num {
            color: var(--pr-ink-2);
        }

        .ph-row.is-today {
            background: var(--pr-accent-soft);
        }

        .ph-date {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 4px 0;
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-panel);
        }

        .ph-date-num {
            font-size: var(--pr-text-lg);
            font-weight: 800;
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
            color: var(--pr-ink);
        }

        .ph-date-dow {
            font-size: var(--pr-text-xs);
            font-weight: 600;
            color: var(--pr-muted);
        }

        .ph-main {
            min-width: 0;
        }

        .ph-name {
            display: block;
            font-size: var(--pr-text-md);
            font-weight: 700;
            color: var(--pr-ink);
            overflow-wrap: break-word;
        }

        .ph-sub {
            display: block;
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
        }

        .ph-lock {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: var(--pr-text-xs);
            font-weight: 600;
            color: var(--pr-muted);
            white-space: nowrap;
        }

        /* Form */
        .ph-form-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 360px);
            gap: var(--pr-space-lg);
            align-items: start;
        }

        .ph-form-grid > .pr-panel {
            margin-bottom: 0;
        }

        .ph-fields {
            display: flex;
            flex-direction: column;
            gap: var(--pr-space-lg);
            padding: var(--pr-space-lg);
        }

        .ph-field {
            margin: 0;
            padding: 0;
            border: 0;
            min-width: 0;
        }

        .ph-field label,
        .ph-field legend {
            display: block;
            margin-bottom: 6px;
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-ink);
        }

        .ph-input {
            width: 100%;
            max-width: 420px;
            min-height: 42px;
            padding: 8px 12px;
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-panel);
            font-family: var(--pr-font);
            font-size: var(--pr-text-md);
            color: var(--pr-ink);
            transition: border-color var(--pr-dur-micro) var(--pr-ease);
        }

        .ph-input:hover {
            border-color: var(--pr-muted);
        }

        .ph-input:focus-visible {
            outline: 2px solid var(--pr-focus);
            outline-offset: 0;
            border-color: var(--pr-accent);
        }

        .ph-input.is-invalid {
            border-color: var(--pr-error);
        }

        .ph-help {
            display: block;
            margin-top: 4px;
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
        }

        .ph-help.is-day {
            font-weight: 700;
            color: var(--pr-accent-deep);
        }

        .ph-help.is-day:empty {
            display: none;
        }

        .ph-error {
            display: block;
            margin-top: 4px;
            font-size: var(--pr-text-xs);
            font-weight: 700;
            color: var(--pr-error);
        }

        .ph-errors {
            margin: var(--pr-space-lg) var(--pr-space-lg) 0;
            padding: var(--pr-space-sm) var(--pr-space-md);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-error-soft);
            font-size: var(--pr-text-sm);
            color: var(--pr-ink);
        }

        .ph-errors ul {
            margin: 4px 0 0;
            padding-left: 1.2em;
        }

        .ph-types {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: var(--pr-space-xs);
        }

        .ph-type {
            display: flex;
            align-items: flex-start;
            gap: var(--pr-space-xs);
            margin: 0 !important;
            padding: var(--pr-space-sm);
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            cursor: pointer;
            font-weight: 400 !important;
            transition: border-color var(--pr-dur-micro) var(--pr-ease), background-color var(--pr-dur-micro) var(--pr-ease);
        }

        .ph-type:hover {
            border-color: var(--pr-muted);
        }

        .ph-type:has(input:checked) {
            border-color: var(--pr-accent);
            background: var(--pr-accent-soft);
        }

        .ph-type:has(input:focus-visible) {
            outline: 2px solid var(--pr-focus);
            outline-offset: 2px;
        }

        .ph-type input {
            margin-top: 3px;
            accent-color: var(--pr-accent-strong);
        }

        .ph-type-name {
            display: block;
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-ink);
        }

        .ph-type-sub {
            display: block;
            font-size: var(--pr-text-xs);
            line-height: 1.45;
            color: var(--pr-ink-2);
        }

        .ph-foot {
            display: flex;
            justify-content: flex-end;
            gap: var(--pr-space-xs);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-top: 1px solid var(--pr-rule);
            background: var(--pr-paper);
            border-radius: 0 0 var(--pr-radius) var(--pr-radius);
        }

        .ph-effects {
            margin: 0;
            padding: var(--pr-space-md) var(--pr-space-lg) var(--pr-space-md) calc(var(--pr-space-lg) + 1.1em);
            font-size: var(--pr-text-sm);
            line-height: 1.55;
            color: var(--pr-ink-2);
        }

        .ph-effects li,
        .pr-empty-note {
            line-height: 1.55;
        }

        .ph-effects li + li {
            margin-top: var(--pr-space-xs);
        }

        .ph-effects strong {
            color: var(--pr-ink);
        }

        @media (max-width: 64rem) {
            .ph-next {
                grid-template-columns: auto minmax(0, 1fr);
            }

            .ph-year-stats {
                grid-column: 1 / -1;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                padding: var(--pr-space-md) 0 0;
                border-left: 0;
                border-top: 1px solid var(--pr-rule);
            }

            .ph-form-grid {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        @media (max-width: 40rem) {
            .ph-next {
                padding: var(--pr-space-md);
                gap: var(--pr-space-md);
            }

            .ph-next-date {
                width: 72px;
                height: 72px;
            }

            .ph-next-day {
                font-size: 1.8rem;
            }

            .ph-year-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .ph-row {
                grid-template-columns: 48px minmax(0, 1fr) auto;
                padding-left: var(--pr-space-md);
                padding-right: var(--pr-space-md);
            }

            .ph-row .status-badge {
                grid-column: 2;
                justify-self: start;
            }

            .ph-row .action-wrap {
                grid-column: 3;
                grid-row: 1;
            }

            .ph-month,
            .ph-fields,
            .ph-foot {
                padding-left: var(--pr-space-md);
                padding-right: var(--pr-space-md);
            }

            .ph-types {
                grid-template-columns: minmax(0, 1fr);
            }

            .ph-input {
                font-size: 16px;
            }
        }
    </style>
@endpush

@section('main')
    @include('pages.Pubholi._form')
@endsection

@push('scripts')
    <script>
        // Tampilkan nama hari dari tanggal yang dipilih, plus peringatan hari Minggu
        (function() {
            const input = document.getElementById('date');
            const out = document.getElementById('date-day');
            if (!input || !out) return;
            const show = () => {
                if (!input.value) { out.textContent = ''; return; }
                const dt = new Date(input.value + 'T00:00:00');
                const label = dt.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                out.textContent = label + (dt.getDay() === 0 ? '. Hari Minggu: tidak berlaku di 3 store statis.' : '');
            };
            input.addEventListener('input', show);
            show();
        })();
    </script>
@endpush
