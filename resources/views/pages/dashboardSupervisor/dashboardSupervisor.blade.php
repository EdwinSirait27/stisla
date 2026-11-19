@extends('layouts.app')
@section('title', 'Dashboard Supervisor')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand · redesign: full
         * paper: oklch(98.4% 0.004 265) · accent: oklch(61% 0.17 276) · display: Nunito · body: Nunito
         * nav: kept (Stisla shell) · signature: the team's leave requests waiting on me, then who in my team has and has not clocked in today · states: no team, nothing pending, full
         * axes: light/rounded-sans/chromatic-indigo · posture: app · contrast: pass · v2.0.0 */
        /* Hallmark · pre-emit critique: P4 H4 E4 S4 R4 V4 · weakest: E, the old page invented attendance and a December 2024 calendar, answered with the team's real roster and scans */

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
        }

        .pr-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: var(--pr-space-lg);
            align-items: start;
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

        /* ── Dashboard karyawan ── */
        .pr-big strong.is-muted {
            color: var(--pr-ink-2);
            font-size: var(--pr-text-xl);
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

        .pr-note strong {
            font-weight: 700;
        }

        /* Submissions */
        .pr-sub {
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-bottom: 1px solid var(--pr-rule);
        }

        .pr-sub:last-child {
            border-bottom: 0;
        }

        .pr-sub-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--pr-space-sm);
        }

        .pr-sub-name {
            font-size: var(--pr-text-sm);
            font-weight: 700;
        }

        .pr-sub-meta {
            margin: 2px 0 0;
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
        }

        .pr-sub-note {
            margin: var(--pr-space-xs) 0 0;
            padding: var(--pr-space-xs) var(--pr-space-sm);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-paper);
            font-size: var(--pr-text-xs);
            line-height: 1.5;
            color: var(--pr-ink);
        }

        .pr-sub-note span {
            font-weight: 700;
            color: var(--pr-ink-2);
        }

        .pr-sub-note.is-rejected {
            background: var(--pr-error-soft);
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

        /* Announcements */
        .pr-ann {
            display: block;
            width: 100%;
            text-align: left;
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border: 0;
            border-bottom: 1px solid var(--pr-rule);
            background: transparent;
            font-family: var(--pr-font);
            cursor: pointer;
            transition: background-color var(--pr-dur-micro) var(--pr-ease);
        }

        .pr-ann:hover {
            background: var(--pr-paper);
        }

        .pr-ann:last-child {
            border-bottom: 0;
        }

        .pr-ann-title {
            display: flex;
            align-items: center;
            gap: var(--pr-space-xs);
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-ink);
        }

        .pr-ann-excerpt {
            display: block;
            margin-top: 2px;
            font-size: var(--pr-text-xs);
            line-height: 1.5;
            color: var(--pr-ink-2);
        }

        .pr-ann-date {
            display: block;
            margin-top: 2px;
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
        }

        .pr-ann-byline {
            margin: 0 0 var(--pr-space-sm);
            font-size: var(--pr-text-sm);
            color: var(--pr-ink-2);
        }

        /* Calendar */
        .pk-cal-wrap {
            padding: var(--pr-space-md) var(--pr-space-lg) var(--pr-space-lg);
            transition: opacity var(--pr-dur-short) var(--pr-ease);
        }

        .pk-cal-head {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--pr-space-md);
            margin-bottom: var(--pr-space-md);
        }

        .pk-cal-title {
            min-width: 160px;
            text-align: center;
            font-size: var(--pr-text-lg);
            font-weight: 700;
        }

        .pk-cal {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: var(--pr-space-2xs);
        }

        .pk-cal-dow {
            padding: var(--pr-space-2xs) 0;
            text-align: center;
            font-size: var(--pr-text-xs);
            font-weight: 700;
            color: var(--pr-ink-2);
        }

        .pk-cal-day {
            min-height: 84px;
            padding: var(--pr-space-xs);
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-panel);
            min-width: 0;
        }

        .pk-cal-day.is-empty {
            border-color: transparent;
            background: transparent;
        }

        .pk-cal-day.is-work {
            background: var(--pr-success-soft);
        }

        .pk-cal-day.is-off {
            background: var(--pr-paper-2);
        }

        .pk-cal-day.is-holiday {
            background: var(--pr-warn-soft);
        }

        .pk-cal-day.is-leave {
            background: var(--pr-accent-soft);
        }

        .pk-cal-day.is-sick {
            background: var(--pr-error-soft);
        }

        .pk-cal-day.is-missing {
            box-shadow: inset 0 0 0 2px var(--pr-warn);
        }

        .pk-cal-day.is-today {
            box-shadow: inset 0 0 0 2px var(--pr-accent-strong);
        }

        .pk-cal-num {
            display: block;
            font-size: var(--pr-text-sm);
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        .pk-cal-label,
        .pk-cal-shift,
        .pk-cal-scan {
            display: block;
            font-size: 11px;
            line-height: 1.35;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .pk-cal-label {
            font-weight: 600;
            color: var(--pr-ink);
        }

        .pk-cal-shift {
            color: var(--pr-ink-2);
        }

        .pk-cal-scan {
            margin-top: 2px;
            font-weight: 700;
            color: var(--pr-success);
        }

        .pk-cal-scan.is-missing {
            color: var(--pr-warn);
        }

        .pk-cal-legend {
            display: flex;
            flex-wrap: wrap;
            gap: var(--pr-space-xs) var(--pr-space-md);
            margin: var(--pr-space-md) 0 0;
            padding: 0;
            list-style: none;
            font-size: var(--pr-text-xs);
            color: var(--pr-ink-2);
        }

        .pk-cal-legend li {
            display: inline-flex;
            align-items: center;
            gap: var(--pr-space-xs);
        }

        .pk-swatch {
            width: 14px;
            height: 14px;
            border-radius: 3px;
            border: 1px solid var(--pr-rule);
        }

        .pk-swatch.is-work { background: var(--pr-success-soft); }
        .pk-swatch.is-off { background: var(--pr-paper-2); }
        .pk-swatch.is-holiday { background: var(--pr-warn-soft); }
        .pk-swatch.is-leave { background: var(--pr-accent-soft); }
        .pk-swatch.is-sick { background: var(--pr-error-soft); }
        .pk-swatch.is-missing { background: var(--pr-panel); box-shadow: inset 0 0 0 2px var(--pr-warn); }

        @media (max-width: 40rem) {
            .pk-cal-wrap,
            .pr-sub,
            .pr-ann,
            .pr-period>div {
                padding-left: var(--pr-space-md);
                padding-right: var(--pr-space-md);
            }

            .pk-cal-day {
                min-height: 56px;
                padding: 4px;
            }

            .pk-cal-label,
            .pk-cal-shift {
                display: none;
            }

            .pk-cal-scan {
                font-size: 0;
            }

            .pk-cal-scan i {
                font-size: 10px;
            }

            .pk-cal-scan.is-missing {
                font-size: 0;
                width: 6px;
                height: 6px;
                border-radius: 50%;
                background: var(--pr-warn);
            }

            .pr-period>div {
                border-right: 0;
                border-bottom: 1px solid var(--pr-rule);
            }
        }

        /* ── Dashboard supervisor ── */
        .pr-section-title {
            font-size: var(--pr-text-md);
            font-weight: 700;
            color: var(--pr-ink);
            margin: 0 0 var(--pr-space-sm);
        }

        .pr-appr {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: var(--pr-space-md);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-bottom: 1px solid var(--pr-rule);
            border-left: 3px solid var(--pr-warn);
        }

        .pr-appr:last-child {
            border-bottom: 0;
        }

        .pr-appr-main {
            flex: 1;
            min-width: 0;
        }

        .pr-appr-name {
            display: block;
            font-size: var(--pr-text-md);
            font-weight: 700;
        }

        .pr-appr-meta {
            display: block;
            font-size: var(--pr-text-sm);
            color: var(--pr-ink-2);
        }

        .pr-appr .pr-sub-note {
            display: block;
        }

        .pr-appr-actions {
            display: flex;
            gap: var(--pr-space-xs);
            flex-shrink: 0;
        }

        .pr-btn-danger {
            color: var(--pr-error);
        }

        .pr-btn-danger:hover {
            background: var(--pr-error-soft);
            border-color: var(--pr-error);
            color: var(--pr-error);
        }

        .is-warn-text {
            color: var(--pr-warn) !important;
        }

        .pr-team {
            margin: 0;
            padding: 0;
            list-style: none;
            border-top: 1px solid var(--pr-rule);
        }

        .pr-team li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--pr-space-md);
            padding: var(--pr-space-xs) var(--pr-space-lg);
            border-bottom: 1px solid var(--pr-rule);
        }

        .pr-team li:last-child {
            border-bottom: 0;
        }

        .pr-team-main {
            min-width: 0;
        }

        .pr-team-name {
            display: block;
            font-size: var(--pr-text-sm);
            font-weight: 700;
        }

        .pr-team-sub {
            display: block;
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
        }

        .pr-team-state {
            text-align: right;
            flex-shrink: 0;
        }

        .status-badge.is-draft {
            background: var(--pr-paper-2);
            color: var(--pr-ink-2);
        }

        @media (max-width: 40rem) {
            .pr-appr {
                flex-direction: column;
                padding-left: var(--pr-space-md);
                padding-right: var(--pr-space-md);
            }

            .pr-team li {
                padding-left: var(--pr-space-md);
                padding-right: var(--pr-space-md);
            }
        }
    </style>
@endpush

@section('main')
    @php
        $emp = Auth::user()->employee;
        $hour = now()->hour;
        $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
        $d = fn($v) => $v ? \Carbon\Carbon::parse($v)->setTimezone(config('app.timezone'))->locale('id')->translatedFormat('d M Y') : '-';
        $canApprove = auth()->user()->canAny(['dashboardManager', 'dashboardSupervisor']);
        $stateLabel = [
            'present' => ['is-approved', 'Sudah absen'],
            'not_in' => ['is-pending', 'Belum absen'],
            'not_working' => ['is-draft', null],
            'no_roster' => ['is-rejected', 'Belum ada roster'],
        ];
    @endphp
    <div class="main-content pr">
        <section class="section">

            {{-- Head --}}
            <header class="pr-head">
                <div class="pr-hello">
                    <img src="{{ $emp?->photos ? route('useremployee.photo', basename($emp->photos)) : asset('img/avatar/avatar-1.png') }}"
                        alt="" class="pr-avatar no-drag" width="56" height="56">
                    <div>
                        <p class="pr-hello-date">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</p>
                        <h1 class="pr-title">{{ $greeting }}, {{ $emp->employee_name ?? Auth::user()->username }}</h1>
                        <p class="pr-lede">
                            {{ collect([$emp?->position->first()?->name, $emp?->department->first()?->department_name, 'Supervisor ' . $teamStats['total'] . ' orang'])->filter()->implode(', ') }}
                        </p>
                    </div>
                </div>
                <div class="pr-actions">
                    @can('ManageRosterSPVManager')
                        <a href="{{ route('roster.index') }}" class="pr-btn"><i class="fas fa-calendar-alt" aria-hidden="true"></i> Roster tim</a>
                    @endcan
                    @if ($hasPending)
                        <button type="button" class="pr-btn" disabled title="Masih ada pengajuan yang menunggu persetujuan">
                            <i class="fas fa-clock" aria-hidden="true"></i> Cuti saya menunggu approval
                        </button>
                    @else
                        <button type="button" class="pr-btn pr-btn-primary" id="newSubmissionBtn" data-toggle="modal" data-target="#requestLeaveModal">
                            <i class="fas fa-plus" aria-hidden="true"></i> Ajukan cuti
                        </button>
                    @endif
                </div>
            </header>

            @include('pages.dashboardSupervisor._approvals')

            @include('pages.dashboardSupervisor._team')

            <h2 class="pr-section-title">Data saya</h2>
            @include('pages.dashboardHuman._personal')
        </section>
    </div>

    @include('pages.dashboardHuman._modals')
@endsection

@push('scripts')
    @include('pages.dashboardHuman._scripts')
    @include('pages.dashboardSupervisor._approval_script')

@endpush
