@extends('layouts.app')
@section('title', 'HR Dashboard')
@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand · redesign: full
         * paper: oklch(98.4% 0.004 265) · accent: oklch(61% 0.17 276) · display: Nunito · body: Nunito
         * nav: kept (Stisla shell) · signature: the action queue, what HR must do today, above live counts with their data freshness · states: loading, empty, stale data, full
         * axes: light/rounded-sans/chromatic-indigo · posture: app · contrast: pass · v2.0.0 */
        /* Hallmark · pre-emit critique: P4 H4 E4 S4 R4 V4 · weakest: E, the old cards showed zeros nobody computed and invented events, answered with real counts or an honest empty state */

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


        /* ── Dashboard HR ── */
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

        .pr-lede {
            line-height: 1.5;
        }

        .pr-btn-sm {
            min-height: 32px;
            padding: 0 var(--pr-space-sm);
            font-size: var(--pr-text-xs);
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

        .pr-card-link {
            display: inline-block;
            margin: 0 var(--pr-space-lg) var(--pr-space-md);
        }

        .pr-empty-note {
            margin: 0;
            padding: var(--pr-space-md) var(--pr-space-lg);
            font-size: var(--pr-text-sm);
            color: var(--pr-muted);
        }

        /* Action queue */
        .pr-todo {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .pr-todo li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--pr-space-md);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-bottom: 1px solid var(--pr-rule);
            border-left: 3px solid var(--pr-warn);
        }

        .pr-todo li.is-ready {
            border-left-color: var(--pr-success);
        }

        .pr-todo li:last-child {
            border-bottom: 0;
        }

        .pr-todo-text {
            font-size: var(--pr-text-md);
            color: var(--pr-ink);
        }

        .pr-todo-text strong {
            font-size: var(--pr-text-lg);
            font-variant-numeric: tabular-nums;
            margin-right: 2px;
        }

        /* Today */
        .pr-today {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 320px), 1fr));
            gap: var(--pr-space-lg);
        }

        .pr-today>.pr-panel {
            display: flex;
            flex-direction: column;
        }

        .pr-big {
            padding: var(--pr-space-md) var(--pr-space-lg) var(--pr-space-xs);
        }

        .pr-big strong {
            display: block;
            font-size: var(--pr-text-2xl);
            font-weight: 700;
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
            color: var(--pr-ink);
        }

        .pr-big strong small {
            font-size: var(--pr-text-md);
            color: var(--pr-muted);
            font-weight: 600;
            margin-right: 2px;
        }

        .pr-big span {
            display: block;
            margin-top: 2px;
            font-size: var(--pr-text-sm);
            color: var(--pr-ink-2);
        }

        .pr-bar {
            display: flex;
            height: 8px;
            margin: var(--pr-space-xs) var(--pr-space-lg);
            border-radius: var(--pr-pill);
            overflow: hidden;
            background: var(--pr-paper-2);
        }

        .pr-bar span {
            display: block;
            height: 100%;
            background: var(--pr-success);
        }

        .pr-bar.is-stack .is-paid {
            background: var(--pr-accent);
        }

        .pr-bar.is-stack .is-approved {
            background: var(--pr-success);
        }

        .pr-bar.is-stack .is-draft {
            background: var(--pr-muted);
        }

        .pr-mini-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
            gap: var(--pr-space-xs);
            margin: var(--pr-space-xs) 0 0;
            padding: var(--pr-space-xs) var(--pr-space-lg) var(--pr-space-md);
        }

        .pr-mini-stats dt {
            font-size: var(--pr-text-xs);
            font-weight: 600;
            color: var(--pr-ink-2);
        }

        .pr-mini-stats dd {
            margin: 2px 0 0;
            font-size: var(--pr-text-lg);
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            color: var(--pr-ink);
        }

        .pr-mini-stats dd.is-warn {
            color: var(--pr-warn);
        }

        .pr-note {
            margin: auto 0 0;
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-top: 1px solid var(--pr-rule);
            font-size: var(--pr-text-xs);
            line-height: 1.5;
            color: var(--pr-ink-2);
        }

        .pr-note+.pr-note {
            margin-top: 0;
        }

        .pr-note.is-warn {
            background: var(--pr-warn-soft);
            color: var(--pr-ink);
        }

        /* Charts */
        .pr-range {
            display: flex;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: var(--pr-space-xs);
        }

        .pr-field label {
            display: block;
            font-size: var(--pr-text-xs);
            font-weight: 600;
            color: var(--pr-ink-2);
            margin-bottom: var(--pr-space-2xs);
        }

        .pr-field input {
            min-height: 36px;
            padding: 0 var(--pr-space-sm);
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-panel);
            font-family: var(--pr-font);
            font-size: var(--pr-text-sm);
            color: var(--pr-ink);
        }

        .pr-field input:focus-visible {
            outline-offset: -1px;
        }

        .pr-chart-body {
            padding: var(--pr-space-md) var(--pr-space-lg);
        }

        .pr-chart {
            position: relative;
        }

        .pr-chart[hidden] {
            display: none !important;
        }

        .pr-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: var(--pr-space-lg);
            align-items: start;
        }

        .pr-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: var(--pr-space-lg);
        }

        /* Lists */
        .pr-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .pr-list li {
            display: flex;
            align-items: center;
            gap: var(--pr-space-md);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-bottom: 1px solid var(--pr-rule);
        }

        .pr-list li:last-child {
            border-bottom: 0;
        }

        .pr-list-main {
            flex: 1;
            min-width: 0;
        }

        .pr-list-title {
            display: block;
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-ink);
        }

        .pr-list-sub {
            display: block;
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
        }

        .pr-days {
            width: 48px;
            flex-shrink: 0;
            text-align: center;
            padding: var(--pr-space-2xs) 0;
            border-radius: var(--pr-radius-sm);
            background: var(--pr-warn-soft);
            font-size: var(--pr-text-lg);
            font-weight: 700;
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
            color: var(--pr-warn);
        }

        .pr-days small {
            display: block;
            font-size: 10px;
            font-weight: 600;
        }

        .pr-days.is-urgent {
            background: var(--pr-error-soft);
            color: var(--pr-error);
        }

        /* Announcements */
        #users-table {
            width: 100% !important;
            font-size: var(--pr-text-sm);
            border-collapse: collapse;
        }

        #users-table thead th {
            background: var(--pr-panel);
            color: var(--pr-ink-2);
            font-size: var(--pr-text-xs);
            font-weight: 700;
            padding: var(--pr-space-sm) var(--pr-space-xs);
            border: 0;
            border-bottom: 1px solid var(--pr-rule);
            white-space: nowrap;
        }

        #users-table tbody td {
            padding: var(--pr-space-sm) var(--pr-space-xs);
            vertical-align: middle;
            border: 0;
            border-bottom: 1px solid var(--pr-rule);
            color: var(--pr-ink);
        }

        .pr-modal-title {
            font-size: var(--pr-text-lg);
            font-weight: 700;
            color: var(--pr-ink);
            margin: 0;
        }

        .pr-preview-title {
            font-size: var(--pr-text-xl);
            font-weight: 700;
            margin: 0 0 var(--pr-space-sm);
            color: var(--pr-ink);
        }

        .pr-facts {
            margin: 0 0 var(--pr-space-md);
        }

        .pr-facts>div {
            display: flex;
            gap: var(--pr-space-md);
            padding: var(--pr-space-2xs) 0;
            font-size: var(--pr-text-sm);
        }

        .pr-facts dt {
            width: 110px;
            font-weight: 400;
            color: var(--pr-ink-2);
        }

        .pr-facts dd {
            margin: 0;
            font-weight: 700;
        }

        .pr-preview-content {
            max-height: 420px;
            overflow-y: auto;
            padding-top: var(--pr-space-md);
            border-top: 1px solid var(--pr-rule);
            font-size: var(--pr-text-md);
            line-height: 1.7;
        }

        .pr-today,
        .pr-grid-3,
        .pr-grid-2 {
            margin-bottom: var(--pr-space-lg);
        }

        .pr-today .pr-panel,
        .pr-grid-3 .pr-panel,
        .pr-grid-2 .pr-panel {
            margin-bottom: 0;
        }

        @media (max-width: 75rem) {
            .pr-grid-3 {
                grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            }

            .pr-grid-3>.pr-panel:last-child {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 60rem) {

            .pr-grid-3,
            .pr-grid-2 {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        @media (max-width: 40rem) {

            .pr-head,
            .pr-panel-head,
            .pr-big,
            .pr-mini-stats,
            .pr-note,
            .pr-todo li,
            .pr-list li,
            .pr-chart-body,
            .pr-table-wrap,
            .pr-empty-note {
                padding-left: var(--pr-space-md);
                padding-right: var(--pr-space-md);
            }

            .pr-bar {
                margin-left: var(--pr-space-md);
                margin-right: var(--pr-space-md);
            }

            .pr-todo li {
                flex-wrap: wrap;
            }

            .pr-actions .pr-btn,
            .pr-field input {
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
        $emp = Auth::user()->employee;
        $hour = now()->hour;
        $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
        $fmtDate = fn($d) => $d ? \Carbon\Carbon::parse($d)->locale('id')->translatedFormat('d M Y') : '-';
        $fmtDateTime = fn($d) => $d ? \Carbon\Carbon::parse($d)->locale('id')->translatedFormat('d M Y, H:i') : '-';
        $a = $attendance;
        $scanStale = $a['last_scan'] && \Carbon\Carbon::parse($a['last_scan'])->lt(today());
        $openActions = collect([
            ['key' => 'leave', 'count' => $actions['leave'], 'label' => 'cuti menunggu approval', 'href' => route('pages.Leaverequest'), 'cta' => 'Review cuti'],
            ['key' => 'position', 'count' => $actions['position'], 'label' => 'pengajuan posisi menunggu approval', 'href' => route('pages.Positionreqlist'), 'cta' => 'Review pengajuan'],
            ['key' => 'contract', 'count' => $actions['contract'], 'label' => 'kontrak habis dalam 30 hari', 'href' => route('contract'), 'cta' => 'Lihat kontrak'],
        ]);
        foreach ($payrollPeriods as $p) {
            $step = $p->nextStep();
            if ($step['tone'] !== 'done') {
                $openActions->push(['key' => 'payroll', 'count' => null, 'label' => 'Payroll ' . $p->period_label . ': ' . $step['text'], 'href' => route('payroll.index', $p->id), 'cta' => 'Buka payroll', 'ready' => $step['tone'] === 'ready']);
            }
        }
        $pending = $openActions->filter(fn($x) => $x['count'] === null || $x['count'] > 0);
    @endphp
    <div class="main-content pr">
        <section class="section">

            {{-- Head --}}
            <header class="pr-head">
                <div class="pr-hello">
                    <img src="{{ $emp?->photos ? route('useremployee.photo', basename($emp->photos)) : asset('img/avatar/avatar-1.png') }}"
                        alt="" class="pr-avatar" width="56" height="56">
                    <div>
                        <p class="pr-hello-date">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</p>
                        <h1 class="pr-title">{{ $greeting }}, {{ $emp->employee_name ?? Auth::user()->username }}</h1>
                        <p class="pr-lede">
                            {{ collect([$emp?->position->first()?->name, $emp?->department->first()?->department_name])->filter()->implode(', ') ?: 'HR' }}
                        </p>
                    </div>
                </div>
                <div class="pr-actions">
                    <a href="{{ route('Employee.create') }}" class="pr-btn"><i class="fas fa-user-plus" aria-hidden="true"></i> Tambah karyawan</a>
                    <a href="{{ route('pages.Fingerprints') }}" class="pr-btn"><i class="fas fa-fingerprint" aria-hidden="true"></i> Attendance log</a>
                    @can('payroll.period.view')
                        <a href="{{ route('payrollperiod.index') }}" class="pr-btn"><i class="fas fa-wallet" aria-hidden="true"></i> Payroll</a>
                    @endcan
                    <a href="{{ route('contract') }}" class="pr-btn"><i class="fas fa-file-contract" aria-hidden="true"></i> Kontrak</a>
                    <button type="button" class="pr-btn pr-btn-primary" id="btn-announcement-quick">
                        <i class="fas fa-bullhorn" aria-hidden="true"></i> Buat pengumuman
                    </button>
                </div>
            </header>

            {{-- Action queue (signature) --}}
            <section class="pr-panel" aria-labelledby="todo-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="todo-title">Perlu tindakan</h2>
                        <span class="pr-panel-sub">{{ $pending->count() ? $pending->count() . ' hal menunggu' : 'Tidak ada yang menunggu' }}</span>
                    </div>
                </div>
                @if ($pending->isEmpty())
                    <p class="pr-empty-note">Semua approval, kontrak, dan payroll aman untuk saat ini.</p>
                @else
                    <ul class="pr-todo">
                        @foreach ($pending as $item)
                            <li class="{{ ($item['ready'] ?? false) ? 'is-ready' : '' }}">
                                <span class="pr-todo-text">
                                    @if ($item['count'] !== null)
                                        <strong>{{ $item['count'] }}</strong>
                                    @endif
                                    {{ $item['label'] }}
                                </span>
                                <a href="{{ $item['href'] }}" class="pr-btn pr-btn-sm">{{ $item['cta'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            {{-- Today --}}
            <div class="pr-today">
                <section class="pr-panel" aria-labelledby="hc-title">
                    <div class="pr-panel-head">
                        <h2 class="pr-panel-title" id="hc-title">Karyawan</h2>
                        <a href="{{ route('pages.Employee') }}" class="pr-link">Lihat semua</a>
                    </div>
                    <div class="pr-big">
                        <strong>{{ $headcount['active'] }}</strong>
                        <span>karyawan aktif</span>
                    </div>
                    <dl class="pr-mini-stats">
                        <div><dt>Pending</dt><dd>{{ $headcount['pending'] }}</dd></div>
                        <div><dt>Masuk bulan ini</dt><dd>{{ $headcount['joined'] }}</dd></div>
                        <div><dt>Keluar bulan ini</dt><dd>{{ $headcount['resigned'] }}</dd></div>
                    </dl>
                </section>

                <section class="pr-panel" aria-labelledby="att-title">
                    <div class="pr-panel-head">
                        <h2 class="pr-panel-title" id="att-title">Kehadiran hari ini</h2>
                        <a href="{{ route('pages.Fingerprints') }}" class="pr-link">Attendance log</a>
                    </div>
                    @if ($a['has_roster'])
                        <div class="pr-big">
                            <strong>{{ $a['present_scheduled'] }}<small> / {{ $a['scheduled'] }}</small></strong>
                            <span>yang dijadwalkan kerja sudah absen masuk{{ $a['rate'] !== null ? ' (' . $a['rate'] . '%)' : '' }}</span>
                        </div>
                        <div class="pr-bar" role="img" aria-label="{{ $a['rate'] ?? 0 }} persen hadir">
                            <span style="width: {{ $a['rate'] ?? 0 }}%"></span>
                        </div>
                        <dl class="pr-mini-stats">
                            <div><dt>Belum absen</dt><dd class="{{ $a['not_in_yet'] ? 'is-warn' : '' }}">{{ $a['not_in_yet'] }}</dd></div>
                            <div><dt>Cuti</dt><dd>{{ $a['leave'] }}</dd></div>
                            <div><dt>Sakit</dt><dd>{{ $a['sick'] }}</dd></div>
                            <div><dt>Off / libur</dt><dd>{{ $a['off'] + $a['holiday'] }}</dd></div>
                        </dl>
                    @else
                        <div class="pr-big">
                            <strong>{{ $a['present'] }}</strong>
                            <span>karyawan sudah absen hari ini</span>
                        </div>
                        <p class="pr-note">Belum ada roster untuk hari ini, jadi kehadiran belum bisa dibandingkan dengan jadwal. Roster terakhir: {{ $fmtDate($a['last_roster']) }}.</p>
                    @endif
                    <p class="pr-note {{ $scanStale ? 'is-warn' : '' }}">
                        Data fingerprint terakhir {{ $fmtDateTime($a['last_scan']) }}.
                        @if ($scanStale)
                            Data hari ini belum masuk, sync fingerprint dulu.
                        @endif
                        Kemarin: {{ $a['yesterday'] }} absen.
                    </p>
                </section>

                {{-- Periode yang ditampilkan: yang terbaru dan sudah punya payroll; kalau belum ada, yang terbaru --}}
                @foreach (collect([$payrollPeriods->firstWhere('payrolls_count', '>', 0) ?? $payrollPeriods->first()])->filter() as $p)
                    @php
                        $total = (int) $p->payrolls_count;
                        $pct = fn($n) => $total > 0 ? round($n / $total * 100, 2) : 0;
                    @endphp
                    <section class="pr-panel" aria-labelledby="pay-title">
                        <div class="pr-panel-head">
                            <h2 class="pr-panel-title" id="pay-title">Payroll {{ $p->period_label }}</h2>
                            <span class="status-badge is-{{ $p->status }}">{{ ucfirst($p->status) }}</span>
                        </div>
                        <div class="pr-big">
                            <strong><small>Rp</small>{{ number_format((float) $p->net_total, 0, ',', '.') }}</strong>
                            <span>total net dari {{ $total }} payroll</span>
                        </div>
                        <div class="pr-bar is-stack" role="img" aria-label="{{ $p->draft_count }} draft, {{ $p->approved_count }} approved, {{ $p->paid_count }} paid">
                            <span class="is-paid" style="width: {{ $pct($p->paid_count) }}%"></span>
                            <span class="is-approved" style="width: {{ $pct($p->approved_count) }}%"></span>
                            <span class="is-draft" style="width: {{ $pct($p->draft_count) }}%"></span>
                        </div>
                        <dl class="pr-mini-stats">
                            <div><dt>Draft</dt><dd>{{ $p->draft_count }}</dd></div>
                            <div><dt>Approved</dt><dd>{{ $p->approved_count }}</dd></div>
                            <div><dt>Paid</dt><dd>{{ $p->paid_count }}</dd></div>
                        </dl>
                        @can('payroll.view')
                            <a href="{{ route('payroll.index', $p->id) }}" class="pr-link pr-card-link">Buka payroll {{ $p->period_label }}</a>
                        @endcan
                    </section>
                @endforeach
            </div>

            {{-- Attendance trend --}}
            <section class="pr-panel" aria-labelledby="trend-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="trend-title">Tingkat kehadiran harian</h2>
                        <span class="pr-panel-sub" id="trend-sub">Persentase karyawan aktif yang absen per hari</span>
                    </div>
                    <div class="pr-range">
                        <div class="pr-field">
                            <label for="startDate">Dari</label>
                            <input type="date" id="startDate" value="{{ now()->startOfMonth()->toDateString() }}">
                        </div>
                        <div class="pr-field">
                            <label for="endDate">Sampai</label>
                            <input type="date" id="endDate" value="{{ now()->endOfMonth()->toDateString() }}">
                        </div>
                        <button type="button" id="filterButton" class="pr-btn">Terapkan</button>
                    </div>
                </div>
                <div class="pr-chart-body">
                    <div class="pr-chart" style="height: 280px;">
                        <canvas id="attendanceChart" aria-label="Grafik tingkat kehadiran harian" role="img"></canvas>
                    </div>
                    <p class="pr-empty-note" id="trend-empty" hidden></p>
                </div>
            </section>

            {{-- Composition --}}
            <div class="pr-grid-3">
                <section class="pr-panel" aria-labelledby="dept-title">
                    <div class="pr-panel-head">
                        <div>
                            <h2 class="pr-panel-title" id="dept-title">Per department</h2>
                            <span class="pr-panel-sub" id="dept-sub">Karyawan aktif</span>
                        </div>
                    </div>
                    <div class="pr-chart-body">
                        <div class="pr-chart js-bar-wrap" id="deptWrap"><canvas id="deptChart" role="img" aria-label="Jumlah karyawan per department"></canvas></div>
                    </div>
                </section>
                <section class="pr-panel" aria-labelledby="company-title">
                    <div class="pr-panel-head">
                        <div>
                            <h2 class="pr-panel-title" id="company-title">Per company</h2>
                            <span class="pr-panel-sub" id="company-sub">Karyawan aktif</span>
                        </div>
                    </div>
                    <div class="pr-chart-body">
                        <div class="pr-chart js-bar-wrap" id="companyWrap"><canvas id="companyChart" role="img" aria-label="Jumlah karyawan per company"></canvas></div>
                    </div>
                </section>
                <section class="pr-panel" aria-labelledby="los-title">
                    <div class="pr-panel-head">
                        <div>
                            <h2 class="pr-panel-title" id="los-title">Masa kerja</h2>
                            <span class="pr-panel-sub" id="los-sub">Karyawan aktif</span>
                        </div>
                    </div>
                    <div class="pr-chart-body">
                        <div class="pr-chart" style="height: 260px;"><canvas id="losChart" role="img" aria-label="Jumlah karyawan per masa kerja"></canvas></div>
                    </div>
                </section>
            </div>

            {{-- Lists --}}
            <div class="pr-grid-2">
                <section class="pr-panel" aria-labelledby="contract-title">
                    <div class="pr-panel-head">
                        <div>
                            <h2 class="pr-panel-title" id="contract-title">Kontrak habis dalam 30 hari</h2>
                            <span class="pr-panel-sub">{{ $contractsExpiringCount }} kontrak</span>
                        </div>
                        <a href="{{ route('contract') }}" class="pr-link">Semua kontrak</a>
                    </div>
                    @if ($expiringContracts->isEmpty())
                        <p class="pr-empty-note">Tidak ada kontrak yang habis dalam 30 hari ke depan.</p>
                    @else
                        <ul class="pr-list">
                            @foreach ($expiringContracts as $c)
                                <li>
                                    <span class="pr-days {{ $c->days_left <= 7 ? 'is-urgent' : '' }}">{{ $c->days_left }}<small>hari</small></span>
                                    <span class="pr-list-main">
                                        <span class="pr-list-title">{{ $c->employee->employee_name ?? '-' }}</span>
                                        <span class="pr-list-sub">Berakhir {{ $fmtDate($c->end_date) }}, {{ $c->contract_type ?? 'PKWT' }}</span>
                                    </span>
                                    <a href="{{ route('editcontract', $c->id) }}" class="pr-btn pr-btn-sm">Perpanjang</a>
                                </li>
                            @endforeach
                        </ul>
                        @if ($contractsExpiringCount > $expiringContracts->count())
                            <p class="pr-note" style="padding: 0 24px 16px;">dan {{ $contractsExpiringCount - $expiringContracts->count() }} kontrak lainnya.</p>
                        @endif
                    @endif
                </section>

                <section class="pr-panel" aria-labelledby="pos-title">
                    <div class="pr-panel-head">
                        <div>
                            <h2 class="pr-panel-title" id="pos-title">Pengajuan posisi menunggu</h2>
                            <span class="pr-panel-sub">{{ $actions['position'] }} pengajuan</span>
                        </div>
                        <a href="{{ route('pages.Positionreqlist') }}" class="pr-link">Review semua</a>
                    </div>
                    @if ($positionRequests->isEmpty())
                        <p class="pr-empty-note">Tidak ada pengajuan posisi yang menunggu approval.</p>
                    @else
                        <ul class="pr-list">
                            @foreach ($positionRequests as $req)
                                <li>
                                    <span class="pr-list-main">
                                        <span class="pr-list-title">{{ $req->title ?? 'Pengajuan posisi' }}</span>
                                        <span class="pr-list-sub">{{ $req->submitter->employee_name ?? '-' }}, diajukan {{ $fmtDate($req->created_at) }}</span>
                                    </span>
                                    <span class="status-badge is-closed">Perlu approval</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </div>

            {{-- Announcements --}}
            <section class="pr-panel" aria-labelledby="ann-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="ann-title">Pengumuman</h2>
                        <span class="pr-panel-sub">{{ $activeAnnouncements }} sedang tayang</span>
                    </div>
                    <button type="button" id="btn-announcement" class="pr-btn">
                        <i class="fas fa-plus" aria-hidden="true"></i> Pengumuman baru
                    </button>
                </div>
                <div class="pr-table-wrap">
                    <table id="users-table">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Status</th>
                                <th>Tayang</th>
                                <th>Berakhir</th>
                                <th style="text-align:center">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </section>
        </section>
    </div>

    {{-- Preview Modal --}}
    <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title pr-modal-title" id="previewModalLabel">Pratinjau pengumuman</h2>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h3 class="pr-preview-title" id="previewTitle"></h3>
                    <dl class="pr-facts">
                        <div><dt>Tayang</dt><dd id="previewDate"></dd></div>
                        <div><dt>Berakhir</dt><dd id="previewEndDate"></dd></div>
                        <div><dt>Dibuat oleh</dt><dd id="previewEmployee"></dd></div>
                    </dl>
                    <div id="previewContent" class="pr-preview-content"></div>
                </div>
                <div class="modal-footer">
                    <small class="text-muted text-center w-100">
                        Pengumuman resmi dari Departemen HR.
                        <a href="https://wa.me/6281138310552" target="_blank" rel="noopener">Hubungi HR</a>
                    </small>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
    <script>
        (function() {
            // Satu seri per chart → satu warna aksen (lolos validasi kontras), teks memakai warna ink
            const css = getComputedStyle(document.documentElement);
            const C = {
                mark: '#5157d4',
                ink2: '#4a5163',
                muted: '#6b7183',
                grid: '#eceef4',
                font: "'Nunito', 'Segoe UI', system-ui, sans-serif",
            };
            Chart.defaults.font.family = C.font;
            Chart.defaults.font.size = 12;
            Chart.defaults.color = C.muted;
            const esc = v => $('<div>').text(v ?? '').html();
            const nf = new Intl.NumberFormat('id-ID');
            const tooltip = {
                backgroundColor: '#1f2433',
                padding: 10,
                cornerRadius: 6,
                displayColors: false,
                titleFont: { weight: '700' },
            };

            // ── Tren kehadiran (line) ──
            const attendanceChart = new Chart(document.getElementById('attendanceChart'), {
                type: 'line',
                data: { labels: [], datasets: [{ data: [], borderColor: C.mark, backgroundColor: C.mark, borderWidth: 2, pointRadius: 3, pointHoverRadius: 5, tension: 0.25, fill: false }] },
                options: {
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: { ...tooltip, callbacks: { label: c => `${c.parsed.y}% karyawan aktif absen` } },
                    },
                    scales: {
                        y: { min: 0, max: 100, ticks: { callback: v => v + '%', stepSize: 25 }, grid: { color: C.grid }, border: { display: false } },
                        x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkipPadding: 12 } },
                    },
                },
            });

            function loadAttendance() {
                const start = document.getElementById('startDate').value;
                const end = document.getElementById('endDate').value;
                fetch(`{{ route('dashboardHR.data') }}?start_date=${start}&end_date=${end}`)
                    .then(r => r.json())
                    .then(res => {
                        const rows = res.data || [];
                        attendanceChart.data.labels = rows.map(r => new Date(r.date + 'T00:00:00').toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }));
                        attendanceChart.data.datasets[0].data = rows.map(r => r.percentage);
                        attendanceChart.update();
                        const empty = document.getElementById('trend-empty');
                        empty.hidden = rows.length > 0;
                        empty.textContent = 'Belum ada data fingerprint di rentang tanggal ini. Data terakhir {{ $fmtDate($attendance['last_scan']) }}.';
                        document.querySelector('#attendanceChart').parentElement.hidden = rows.length === 0;
                        const avg = rows.length ? Math.round(rows.reduce((s, r) => s + r.percentage, 0) / rows.length) : null;
                        document.getElementById('trend-sub').textContent = avg !== null
                            ? `Rata-rata ${avg}% dari ${nf.format(res.totalEmployees)} karyawan aktif, ${rows.length} hari ada data`
                            : 'Persentase karyawan aktif yang absen per hari';
                    })
                    .catch(() => {
                        const empty = document.getElementById('trend-empty');
                        empty.hidden = false;
                        empty.textContent = 'Gagal memuat data kehadiran. Muat ulang halaman.';
                    });
            }
            document.getElementById('filterButton').addEventListener('click', loadAttendance);
            loadAttendance();

            // ── Bar horizontal terurut (department, company) ──
            function horizontalBar(canvasId, wrapId, subId, rows, labelKey) {
                rows = rows.filter(r => r.total > 0).sort((a, b) => b.total - a.total);
                const wrap = document.getElementById(wrapId);
                if (!rows.length) {
                    wrap.innerHTML = '<p class="pr-empty-note">Belum ada data.</p>';
                    return;
                }
                wrap.style.height = Math.max(160, rows.length * 30 + 30) + 'px';
                const total = rows.reduce((s, r) => s + r.total, 0);
                document.getElementById(subId).textContent = `${nf.format(total)} karyawan aktif di ${rows.length} ${labelKey === 'department' ? 'department' : 'company'}`;
                new Chart(document.getElementById(canvasId), {
                    type: 'bar',
                    data: { labels: rows.map(r => r[labelKey]), datasets: [{ data: rows.map(r => r.total), backgroundColor: C.mark, borderRadius: 4, borderSkipped: 'start', barThickness: 14 }] },
                    options: {
                        indexAxis: 'y',
                        maintainAspectRatio: false,
                        layout: { padding: { left: 8 } },
                        plugins: {
                            legend: { display: false },
                            tooltip: { ...tooltip, callbacks: { label: c => {
                                const r = rows[c.dataIndex];
                                return [`${nf.format(r.total)} karyawan`, `Active ${r.active ?? 0}, Pending ${r.pending ?? 0}, Mutation ${r.mutation ?? 0}, On leave ${r.on_leave ?? 0}`];
                            } } },
                        },
                        scales: {
                            x: { beginAtZero: true, grid: { color: C.grid }, border: { display: false }, ticks: { precision: 0 } },
                            y: { grid: { display: false }, ticks: { color: C.ink2, autoSkip: false, callback: function(v) { const l = this.getLabelForValue(v); return l.length > 18 ? l.slice(0, 17) + '…' : l; } } },
                        },
                    },
                });
            }

            fetch('/dashboard/employee-by-department').then(r => r.json())
                .then(d => horizontalBar('deptChart', 'deptWrap', 'dept-sub', d, 'department'))
                .catch(() => document.getElementById('deptWrap').innerHTML = '<p class="pr-empty-note">Gagal memuat data.</p>');
            fetch('/dashboard/employee-by-company').then(r => r.json())
                .then(d => horizontalBar('companyChart', 'companyWrap', 'company-sub', d, 'company'))
                .catch(() => document.getElementById('companyWrap').innerHTML = '<p class="pr-empty-note">Gagal memuat data.</p>');

            // ── Masa kerja (bar vertikal, urutan rentang tetap) ──
            const LOS = { '< 1 Year': '< 1 tahun', '1-3 Years': '1-3 tahun', '3-5 Years': '3-5 tahun', '5-10 Years': '5-10 tahun', '> 10 Years': '> 10 tahun' };
            fetch('/dashboard/employee-by-los').then(r => r.json()).then(d => {
                const byKey = Object.fromEntries(d.map(r => [r.range_label, Number(r.total)]));
                const keys = Object.keys(LOS);
                const values = keys.map(k => byKey[k] || 0);
                const total = values.reduce((s, v) => s + v, 0);
                document.getElementById('los-sub').textContent = `${nf.format(total)} karyawan dengan tanggal masuk`;
                new Chart(document.getElementById('losChart'), {
                    type: 'bar',
                    data: { labels: keys.map(k => LOS[k]), datasets: [{ data: values, backgroundColor: C.mark, borderRadius: 4, borderSkipped: 'start', maxBarThickness: 36 }] },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { ...tooltip, callbacks: { label: c => `${nf.format(c.parsed.y)} karyawan (${total ? Math.round(c.parsed.y / total * 100) : 0}%)` } },
                        },
                        scales: {
                            y: { beginAtZero: true, grid: { color: C.grid }, border: { display: false }, ticks: { precision: 0 } },
                            x: { grid: { display: false }, ticks: { color: C.ink2 } },
                        },
                    },
                });
            }).catch(() => {});

            // ── Tabel pengumuman ──
            const STATE = { active: ['is-open', 'Tayang'], scheduled: ['is-paid', 'Terjadwal'], ended: ['is-locked', 'Berakhir'] };
            $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                dom: 'rt<"pr-dt-bottom"ip>',
                ajax: { url: '{{ route('announcements.announcements') }}' },
                columns: [
                    { data: 'title', render: (d, t) => t !== 'display' ? d : `<span class="pr-cell-name">${esc(d)}</span>` },
                    { data: 'state', orderable: false, searchable: false, render: (d, t) => { const s = STATE[d] || ['', d]; return `<span class="status-badge ${s[0]}">${s[1]}</span>`; } },
                    { data: 'publish_fmt', orderable: false, searchable: false },
                    { data: 'end_fmt', orderable: false, searchable: false },
                    { data: 'action', orderable: false, searchable: false, className: 'text-center' },
                ],
                order: [],
                pageLength: 5,
                language: {
                    processing: 'Memuat data...',
                    emptyTable: 'Belum ada pengumuman. Klik Pengumuman baru untuk membuat.',
                    info: '_START_ sampai _END_ dari _TOTAL_',
                    infoEmpty: '0 pengumuman',
                    infoFiltered: '',
                    paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                },
            });

            $(document).on('click', '.preview-btn', function() {
                $('#previewTitle').text($(this).data('title'));
                $('#previewDate').text($(this).data('date'));
                $('#previewEndDate').text($(this).data('enddate'));
                $('#previewEmployee').text($(this).data('employee'));
                $('#previewContent').html($(this).data('content'));
                $('#previewModal').modal('show');
            });

            // ── Buat pengumuman ──
            function openAnnouncementModal() {
                Swal.fire({
                    title: 'Buat pengumuman',
                    html: `
                    <form id="announcementForm" action="{{ route('dashboardHR.store') }}" method="POST" style="text-align:left">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="form-label" for="ann-title-input">Judul</label>
                            <input type="text" name="title" id="ann-title-input" class="form-control" maxlength="255" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label" for="editor">Isi pengumuman</label>
                            <textarea id="editor" name="content" class="form-control"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label class="form-label" for="ann-publish">Tanggal tayang</label>
                                <input type="date" name="publish_date" id="ann-publish" class="form-control" value="{{ now()->toDateString() }}" required>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label class="form-label" for="ann-end">Berakhir <span class="text-muted">(opsional)</span></label>
                                <input type="date" name="end_date" id="ann-end" class="form-control">
                            </div>
                        </div>
                        <p class="text-muted" style="font-size:.8rem;margin:0">Email pengumuman dikirim ke semua karyawan aktif yang punya email.</p>
                    </form>`,
                    showCancelButton: true,
                    confirmButtonText: 'Tayangkan & kirim email',
                    cancelButtonText: 'Batal',
                    width: '680px',
                    focusConfirm: false,
                    didOpen: () => {
                        if (tinymce.get('editor')) tinymce.get('editor').remove();
                        tinymce.init({ selector: '#editor', plugins: 'lists link image table code', toolbar: 'undo redo | styles | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code', menubar: false, height: 260, license_key: 'gpl' });
                    },
                    willClose: () => { if (tinymce.get('editor')) tinymce.get('editor').remove(); },
                    preConfirm: () => {
                        tinymce.triggerSave();
                        const title = document.querySelector('input[name="title"]').value.trim();
                        const content = document.querySelector('textarea[name="content"]').value.trim();
                        const publish = document.querySelector('input[name="publish_date"]').value;
                        const end = document.querySelector('input[name="end_date"]').value;
                        if (!title) { Swal.showValidationMessage('Judul wajib diisi'); return false; }
                        if (!content) { Swal.showValidationMessage('Isi pengumuman wajib diisi'); return false; }
                        if (!publish) { Swal.showValidationMessage('Tanggal tayang wajib diisi'); return false; }
                        if (end && end < publish) { Swal.showValidationMessage('Tanggal berakhir tidak boleh sebelum tanggal tayang'); return false; }
                        document.getElementById('announcementForm').submit();
                    }
                });
            }
            document.getElementById('btn-announcement').addEventListener('click', openAnnouncementModal);
            document.getElementById('btn-announcement-quick').addEventListener('click', openAnnouncementModal);

            @if (session('success'))
                Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('success')), timer: 3000, showConfirmButton: false });
            @endif
            @if (session('error'))
                Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')) });
            @endif

            $(document).on('hidden.bs.modal', function() {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');
            });
        })();
    </script>
@endpush
