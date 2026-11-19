@extends('layouts.app')
@section('title', 'Profil saya')
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand · redesign: full
         * paper: oklch(98.4% 0.004 265) · accent: oklch(61% 0.17 276) · display: Nunito · body: Nunito
         * nav: kept (Stisla shell) · signature: what is still missing from my profile, and what is waiting on HR · states: nothing uploaded, pending email/phone, signed, 2FA on/off/required, single/multi role
         * axes: light/rounded-sans/chromatic-indigo · posture: app · contrast: pass · v2.0.0 */
        /* Hallmark · pre-emit critique: P4 H4 E4 S4 R4 V4 · weakest: H, two columns of equal weight; answered by putting the checklist above both */
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


        /* ── Profil ── */
        .pf-avatar {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
            border: 1px solid var(--pr-rule);
            background: var(--pr-accent-soft);
        }

        .pf-initials {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: var(--pr-text-xl);
            font-weight: 800;
            color: var(--pr-accent-deep);
        }

        .pf-chips {
            display: flex;
            flex-wrap: wrap;
            gap: var(--pr-space-2xs);
            margin-top: var(--pr-space-xs);
        }

        .pf-chip {
            display: inline-block;
            padding: 2px 10px;
            border-radius: var(--pr-pill);
            background: var(--pr-paper-2);
            font-size: var(--pr-text-xs);
            font-weight: 600;
            color: var(--pr-ink-2);
        }

        /* Kelengkapan */
        .pf-check {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin: 0;
            padding: 0;
            list-style: none;
        }

        @media (max-width: 64rem) {
            .pf-check {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 40rem) {
            .pf-check {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        .pf-check li {
            display: flex;
            align-items: flex-start;
            gap: var(--pr-space-sm);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-top: 1px solid var(--pr-rule);
        }

        .pf-check-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 24px;
            height: 24px;
            margin-top: 1px;
            border-radius: 50%;
            font-size: 11px;
            background: var(--pr-success-soft);
            color: var(--pr-success);
        }

        .pf-check li.is-missing .pf-check-icon {
            background: var(--pr-warn-soft);
            color: var(--pr-warn);
        }

        .pf-check li.is-pending .pf-check-icon {
            background: var(--pr-accent-soft);
            color: var(--pr-accent-strong);
        }

        .pf-check-name {
            display: block;
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-ink);
        }

        .pf-check-sub {
            display: block;
            font-size: var(--pr-text-xs);
            line-height: 1.45;
            color: var(--pr-ink-2);
            overflow-wrap: anywhere;
        }

        .pf-check-sub a {
            font-weight: 700;
            color: var(--pr-accent-strong);
        }

        .pf-meter {
            display: flex;
            align-items: center;
            gap: var(--pr-space-sm);
            min-width: 180px;
        }

        .pf-meter-track {
            flex: 1;
            height: 8px;
            border-radius: var(--pr-pill);
            background: var(--pr-paper-2);
            overflow: hidden;
        }

        .pf-meter-fill {
            display: block;
            height: 100%;
            border-radius: var(--pr-pill);
            background: var(--pr-accent);
        }

        .pf-meter-num {
            font-size: var(--pr-text-sm);
            font-weight: 800;
            font-variant-numeric: tabular-nums;
            color: var(--pr-ink);
        }

        /* Layout dua kolom */
        .pf-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: var(--pr-space-lg);
            align-items: start;
        }

        .pf-grid > .pf-col {
            display: flex;
            flex-direction: column;
            gap: var(--pr-space-lg);
            min-width: 0;
        }

        .pf-grid .pr-panel {
            margin-bottom: 0;
        }

        /* Data read-only */
        .pf-dl {
            margin: 0;
            padding: var(--pr-space-xs) var(--pr-space-lg) var(--pr-space-md);
        }

        .pf-dl-group + .pf-dl-group {
            margin-top: var(--pr-space-sm);
            padding-top: var(--pr-space-sm);
            border-top: 1px solid var(--pr-rule);
        }

        .pf-dl-title {
            margin: var(--pr-space-xs) 0 var(--pr-space-2xs);
            font-size: var(--pr-text-sm);
            font-weight: 800;
            color: var(--pr-ink);
        }

        .pf-dl-row {
            display: grid;
            grid-template-columns: minmax(0, 42%) minmax(0, 1fr);
            gap: var(--pr-space-sm);
            padding: 5px 0;
        }

        .pf-dl-row dt {
            font-size: var(--pr-text-sm);
            font-weight: 600;
            color: var(--pr-ink-2);
        }

        .pf-dl-row dd {
            margin: 0;
            font-size: var(--pr-text-sm);
            font-weight: 600;
            color: var(--pr-ink);
            overflow-wrap: anywhere;
        }

        .pf-dl-row dd.is-empty {
            font-weight: 500;
            color: var(--pr-muted);
        }

        .pf-dl-row dd.is-num {
            font-variant-numeric: tabular-nums;
        }

        /* Form */
        .pf-form-body {
            display: flex;
            flex-direction: column;
            gap: var(--pr-space-md);
            padding: var(--pr-space-md) var(--pr-space-lg);
        }

        .pf-field label {
            display: block;
            margin-bottom: 4px;
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-ink);
        }

        .pf-input {
            width: 100%;
            min-height: 40px;
            padding: 8px 12px;
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-panel);
            font-family: var(--pr-font);
            font-size: var(--pr-text-md);
            color: var(--pr-ink);
            transition: border-color var(--pr-dur-micro) var(--pr-ease);
        }

        .pf-input:hover {
            border-color: var(--pr-muted);
        }

        .pf-input:focus-visible {
            outline: 2px solid var(--pr-focus);
            outline-offset: 0;
            border-color: var(--pr-accent);
        }

        .pf-input.is-invalid {
            border-color: var(--pr-error);
        }

        .pf-hint {
            display: block;
            margin-top: 4px;
            font-size: var(--pr-text-xs);
            line-height: 1.45;
            color: var(--pr-muted);
        }

        .pf-hint.is-pending {
            color: var(--pr-accent-deep);
            font-weight: 600;
        }

        .pf-error {
            display: block;
            margin-top: 4px;
            font-size: var(--pr-text-xs);
            font-weight: 600;
            color: var(--pr-error);
        }

        /* Upload foto */
        .pf-uploads {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: var(--pr-space-sm);
        }

        .pf-upload {
            display: flex;
            flex-direction: column;
            gap: var(--pr-space-xs);
            min-width: 0;
        }

        .pf-upload-name {
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-ink);
        }

        .pf-thumb {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            aspect-ratio: 4 / 3;
            border: 1px dashed var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-paper);
            overflow: hidden;
            color: var(--pr-muted);
        }

        .pf-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            cursor: zoom-in;
        }

        .pf-thumb.is-portrait img {
            object-fit: cover;
        }

        .pf-thumb-empty {
            padding: var(--pr-space-xs);
            text-align: center;
            font-size: var(--pr-text-xs);
            cursor: pointer;
        }

        .pf-thumb-empty i {
            display: block;
            margin-bottom: 4px;
            font-size: 20px;
        }

        .pf-file-label {
            justify-content: center;
            width: 100%;
            margin: 0;
        }

        .pf-file-state {
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
            overflow-wrap: anywhere;
        }

        .pf-file-state.is-new {
            font-weight: 700;
            color: var(--pr-accent-deep);
        }

        .pf-visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
            overflow: hidden;
        }

        .pf-file-label:has(+ .pf-visually-hidden:focus-visible),
        .pf-upload:focus-within .pf-file-label {
            outline: 2px solid var(--pr-focus);
            outline-offset: 2px;
        }

        .pf-form-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: var(--pr-space-sm);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-top: 1px solid var(--pr-rule);
            background: var(--pr-paper);
            border-radius: 0 0 var(--pr-radius) var(--pr-radius);
        }

        .pf-dirty {
            font-size: var(--pr-text-xs);
            font-weight: 700;
            color: var(--pr-warn);
        }

        /* Keamanan */
        .pf-sec {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .pf-sec li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--pr-space-sm);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-top: 1px solid var(--pr-rule);
        }

        .pf-sec li:first-child {
            border-top: 0;
        }

        .pf-sec-main {
            min-width: 0;
        }

        .pf-role-form {
            display: flex;
            gap: var(--pr-space-xs);
            margin-top: var(--pr-space-xs);
        }

        .pf-role-form .pf-input {
            min-height: 34px;
            padding: 4px 10px;
            font-size: var(--pr-text-sm);
        }

        /* Dokumen */
        .pf-docs {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .pf-docs li {
            border-top: 1px solid var(--pr-rule);
        }

        .pf-docs li:first-child {
            border-top: 0;
        }

        .pf-doc {
            display: flex;
            align-items: center;
            gap: var(--pr-space-sm);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            color: var(--pr-ink);
            text-decoration: none;
            transition: background-color var(--pr-dur-micro) var(--pr-ease);
        }

        .pf-doc:hover {
            background: var(--pr-paper);
            color: var(--pr-ink);
            text-decoration: none;
        }

        .pf-doc:focus-visible {
            outline: 2px solid var(--pr-focus);
            outline-offset: -2px;
        }

        .pf-doc-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 34px;
            height: 34px;
            border-radius: var(--pr-radius-sm);
            background: var(--pr-error-soft);
            color: var(--pr-error);
        }

        .pf-doc-main {
            flex: 1;
            min-width: 0;
        }

        .pf-doc-name {
            display: block;
            font-size: var(--pr-text-sm);
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .pf-doc-sub {
            display: block;
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
        }

        .pf-doc-dl {
            font-size: var(--pr-text-xs);
            font-weight: 700;
            color: var(--pr-accent-strong);
            white-space: nowrap;
        }

        /* Tanda tangan */
        .pf-sign-body {
            padding: var(--pr-space-md) var(--pr-space-lg);
        }

        .pf-sign-saved {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 140px;
            padding: var(--pr-space-md);
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-paper);
        }

        .pf-sign-saved img {
            max-width: 100%;
            max-height: 140px;
            object-fit: contain;
        }

        .pf-pad {
            border: 1px dashed var(--pr-muted);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-panel);
        }

        .pf-pad canvas {
            display: block;
            width: 100%;
            height: 200px;
            cursor: crosshair;
            touch-action: none;
        }

        .pf-sign-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: var(--pr-space-xs);
            margin-top: var(--pr-space-sm);
        }

        .pf-sign-import {
            display: flex;
            align-items: center;
            gap: var(--pr-space-md);
            padding: var(--pr-space-md);
            border: 1px dashed var(--pr-muted);
            border-radius: var(--pr-radius-sm);
        }

        .pf-sign-import img {
            max-width: 200px;
            height: 96px;
            object-fit: contain;
        }

        /* Lightbox */
        .pf-lightbox {
            position: fixed;
            inset: 0;
            z-index: 1060;
            display: none;
            align-items: center;
            justify-content: center;
            padding: var(--pr-space-lg);
            background: oklch(20% 0.02 265 / .82);
            cursor: zoom-out;
        }

        .pf-lightbox.is-open {
            display: flex;
        }

        .pf-lightbox img {
            max-width: 100%;
            max-height: 100%;
            border-radius: var(--pr-radius-sm);
            background: var(--pr-panel);
        }

        .pf-lightbox button {
            position: absolute;
            top: 16px;
            right: 16px;
            width: 40px;
            height: 40px;
            border: 0;
            border-radius: 50%;
            background: var(--pr-panel);
            font-size: 22px;
            line-height: 1;
            color: var(--pr-ink);
            cursor: pointer;
        }

        .pf-lightbox button:focus-visible {
            outline: 2px solid var(--pr-focus);
            outline-offset: 2px;
        }

        @media (max-width: 64rem) {
            .pf-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            /* Satu kolom: yang bisa diubah tampil lebih dulu */
            .pf-grid > .pf-col:last-child {
                order: -1;
            }
        }

        @media (max-width: 40rem) {
            .pf-check li,
            .pf-dl,
            .pf-form-body,
            .pf-form-foot,
            .pf-sec li,
            .pf-doc,
            .pf-sign-body {
                padding-left: var(--pr-space-md);
                padding-right: var(--pr-space-md);
            }

            .pf-uploads {
                gap: var(--pr-space-xs);
            }

            .pf-thumb {
                aspect-ratio: 1;
            }

            .pf-dl-row {
                grid-template-columns: minmax(0, 1fr);
                gap: 0;
            }

            .pf-sec li {
                flex-wrap: wrap;
            }

            .pf-input {
                font-size: 16px;
            }
        }
    </style>
@endpush

@section('main')
    @php
        $emp = $user->employee;
        $fmt = fn($v) => $v ? \Carbon\Carbon::parse($v)->locale('id')->translatedFormat('d F Y') : null;
        $primary = fn($rel) => $rel?->firstWhere('pivot.is_primary', true) ?? $rel?->first();
        // Utama dulu, sisanya menyusul
        $names = fn($rel, $col) => $rel?->sortByDesc(fn($r) => (int) $r->pivot?->is_primary)->pluck($col)->filter()->unique()->implode(', ');
        $position = $primary($emp?->position);
        $department = $primary($emp?->department);
        $store = $primary($emp?->store);
        $joinRaw = $emp?->getRawOriginal('join_date');
        $tenure = null;
        if ($joinRaw) {
            $j = \Carbon\Carbon::parse($joinRaw);
            $y = (int) $j->diffInYears(now());
            $m = (int) $j->copy()->addYears($y)->diffInMonths(now());
            $tenure = trim(($y ? $y . ' tahun ' : '') . $m . ' bulan');
        }
        $acct = $emp?->bank_account_number;
        $acctMasked = $acct ? str_repeat('•', max(0, strlen($acct) - 4)) . substr($acct, -4) : null;

        $checks = [
            ['Foto profil', !empty($emp?->photos), null, 'Dipakai di dashboard dan dokumen.'],
            ['Foto KTP', !empty($emp?->ktp_photos), null, 'Untuk verifikasi data HR.'],
            ['Foto KK', !empty($emp?->kk_photos), null, 'Untuk data tanggungan dan BPJS.'],
            ['Tanda tangan', !empty($emp?->signature), null, 'Dipakai di SK dan surat. Sekali simpan, hanya HR yang bisa mengubah.'],
            ['Email', !empty($emp?->email), $emp?->pending_email, 'Untuk slip gaji dan reset password.'],
            ['Nomor telepon', !empty($emp?->telp_number), $emp?->pending_telp_number, 'Untuk notifikasi WhatsApp.'],
        ];
        $done = collect($checks)->filter(fn($c) => $c[1])->count();
        $pct = (int) round($done / count($checks) * 100);
        $documents = $emp?->documents ?? collect();
        $skLetters = $emp?->skletters?->where('status', 'Draft') ?? collect();
        $roles = Auth::user()->all_roles_hrx ?? [];
        $row = function ($label, $value, $num = false) {
            $empty = $value === null || trim((string) $value) === '' || in_array(trim((string) $value), ['-', '0'], true);
            return '<div class="pf-dl-row"><dt>' . e($label) . '</dt><dd class="' . ($empty ? 'is-empty' : ($num ? 'is-num' : '')) . '">' . e($empty ? 'Belum diisi' : $value) . '</dd></div>';
        };
    @endphp
    <div class="main-content pr">
        <section class="section">

            <header class="pr-head">
                <div class="pr-hello">
                    @if (!empty($emp?->photos))
                        <img src="{{ route('useremployee.photo', basename($emp->photos)) }}" alt="" class="pf-avatar no-drag" width="72" height="72">
                    @else
                        <span class="pf-avatar pf-initials" aria-hidden="true">
                            {{ collect(explode(' ', $emp->employee_name ?? ($user->username ?? 'U')))->filter()->take(2)->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') }}
                        </span>
                    @endif
                    <div>
                        <h1 class="pr-title">{{ $emp->employee_name ?? $user->username }}</h1>
                        <p class="pr-lede">
                            {{ collect([$position?->name, $department?->department_name, $store?->name])->filter()->implode(', ') ?: 'Data jabatan belum diatur HR' }}
                        </p>
                        <div class="pf-chips">
                            @if ($emp?->employee_pengenal)<span class="pf-chip">NIP {{ $emp->employee_pengenal }}</span>@endif
                            @if ($emp?->status_employee)<span class="pf-chip">{{ $emp->status_employee }}</span>@endif
                            @if ($emp?->grading?->grading_name)<span class="pf-chip">Grading {{ $emp->grading->grading_name }}</span>@endif
                            @if ($tenure)<span class="pf-chip">Masa kerja {{ $tenure }}</span>@endif
                        </div>
                    </div>
                </div>
                <div class="pr-actions">
                    <a href="{{ route('pages.change-password') }}" class="pr-btn"><i class="fas fa-key" aria-hidden="true"></i> Ganti password</a>
                </div>
            </header>

            @if ($errors->any())
                <div class="pr-panel" role="alert">
                    <p class="pr-note is-warn" style="border-top:0">
                        <strong>Perubahan belum tersimpan.</strong> {{ $errors->first() }}
                    </p>
                </div>
            @endif

            {{-- Kelengkapan (signature: apa yang masih kurang dari profil saya) --}}
            <section class="pr-panel" aria-labelledby="pf-check-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="pf-check-title">Kelengkapan profil</h2>
                        <span class="pr-panel-sub">
                            {{ $done === count($checks) ? 'Semua data yang Anda kelola sudah lengkap' : (count($checks) - $done) . ' hal masih perlu dilengkapi' }}
                        </span>
                    </div>
                    <div class="pf-meter" aria-label="{{ $pct }} persen lengkap">
                        <span class="pf-meter-track"><span class="pf-meter-fill" style="width: {{ $pct }}%"></span></span>
                        <span class="pf-meter-num">{{ $done }}/{{ count($checks) }}</span>
                    </div>
                </div>
                <ul class="pf-check">
                    @foreach ($checks as [$name, $ok, $pending, $why])
                        @php $state = $pending ? 'is-pending' : ($ok ? '' : 'is-missing'); @endphp
                        <li class="{{ $state }}">
                            <span class="pf-check-icon" aria-hidden="true">
                                <i class="fas {{ $pending ? 'fa-clock' : ($ok ? 'fa-check' : 'fa-exclamation') }}"></i>
                            </span>
                            <span>
                                <span class="pf-check-name">{{ $name }}</span>
                                <span class="pf-check-sub">
                                    @if ($pending)
                                        Perubahan ke {{ $pending }} menunggu persetujuan HR.
                                    @elseif ($ok)
                                        Sudah ada.
                                    @else
                                        Belum ada. {{ $why }}
                                    @endif
                                </span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </section>

            <div class="pf-grid">
                {{-- Kolom kiri: data dari HR --}}
                <div class="pf-col">
                    <section class="pr-panel" aria-labelledby="pf-data-title">
                        <div class="pr-panel-head">
                            <div>
                                <h2 class="pr-panel-title" id="pf-data-title">Data karyawan</h2>
                                <span class="pr-panel-sub">Diatur HR. Kalau ada yang salah, hubungi HR.</span>
                            </div>
                        </div>
                        <dl class="pf-dl">
                            <div class="pf-dl-group">
                                <h3 class="pf-dl-title">Pekerjaan</h3>
                                {!! $row('Perusahaan', $emp?->company?->name) !!}
                                {!! $row('Jabatan', $names($emp?->position, 'name')) !!}
                                {!! $row('Departemen', $names($emp?->department, 'department_name')) !!}
                                {!! $row('Lokasi', $names($emp?->store, 'name')) !!}
                                {!! $row('Atasan langsung', $emp?->atasanList?->pluck('employee_name')->implode(', ')) !!}
                                {!! $row('Status karyawan', $emp?->status_employee) !!}
                                {!! $row('Grading', $emp?->grading?->grading_name) !!}
                                {!! $row('Grouping', $emp?->group?->group_name) !!}
                                {!! $row('Tanggal bergabung', $fmt($joinRaw)) !!}
                                @if ($emp?->end_date)
                                    {!! $row('Akhir kontrak', $fmt($emp->end_date)) !!}
                                @endif
                            </div>
                            <div class="pf-dl-group">
                                <h3 class="pf-dl-title">Akun</h3>
                                {!! $row('Username', $user->username) !!}
                                {!! $row('Email kantor', $emp?->company_email) !!}
                            </div>
                            <div class="pf-dl-group">
                                <h3 class="pf-dl-title">Identitas</h3>
                                {!! $row('NIK', $emp?->nik, true) !!}
                                {!! $row('Nomor KK', $emp?->kk_number, true) !!}
                                {!! $row('NPWP', $emp?->npwp, true) !!}
                                {!! $row('Tempat, tanggal lahir', collect([$emp?->place_of_birth, $fmt($emp?->date_of_birth)])->filter()->implode(', ')) !!}
                                {!! $row('Jenis kelamin', $emp?->gender) !!}
                                {!! $row('Agama', $emp?->religion) !!}
                            </div>
                            <div class="pf-dl-group">
                                <h3 class="pf-dl-title">BPJS dan gaji</h3>
                                {!! $row('BPJS Kesehatan', $emp?->bpjs_kes, true) !!}
                                {!! $row('BPJS Ketenagakerjaan', $emp?->bpjs_ket, true) !!}
                                {!! $row('Rekening gaji', $acctMasked ? collect([$emp?->bank?->name, $acctMasked])->filter()->implode(', ') : null, true) !!}
                            </div>
                        </dl>
                    </section>

                    <section class="pr-panel" aria-labelledby="pf-doc-title">
                        <div class="pr-panel-head">
                            <div>
                                <h2 class="pr-panel-title" id="pf-doc-title">Dokumen dan SK</h2>
                                <span class="pr-panel-sub">{{ $documents->count() + $skLetters->count() }} file bisa diunduh</span>
                            </div>
                        </div>
                        @if ($documents->isEmpty() && $skLetters->isEmpty())
                            <p class="pr-empty-note">Belum ada dokumen atau SK untuk Anda. Dokumen yang diterbitkan HR akan muncul di sini.</p>
                        @else
                            <ul class="pf-docs">
                                @foreach ($documents as $doc)
                                    <li>
                                        <a href="{{ route('profile.documents.download', $doc->id) }}" class="pf-doc">
                                            <span class="pf-doc-icon" aria-hidden="true"><i class="fas fa-file-pdf"></i></span>
                                            <span class="pf-doc-main">
                                                <span class="pf-doc-name">{{ $doc->companydocumentconfigs?->documenttypes?->document_name ?? 'Dokumen' }}</span>
                                                <span class="pf-doc-sub">{{ $doc->document_number }}</span>
                                            </span>
                                            <span class="pf-doc-dl">Unduh</span>
                                        </a>
                                    </li>
                                @endforeach
                                @foreach ($skLetters as $sk)
                                    <li>
                                        <a href="{{ route('my-sk-letter.download', $sk->id) }}" class="pf-doc">
                                            <span class="pf-doc-icon" aria-hidden="true"><i class="fas fa-file-signature"></i></span>
                                            <span class="pf-doc-main">
                                                <span class="pf-doc-name">Surat keputusan</span>
                                                <span class="pf-doc-sub">{{ $sk->sk_number }}</span>
                                            </span>
                                            <span class="pf-doc-dl">Unduh</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </section>
                </div>

                {{-- Kolom kanan: yang bisa saya ubah --}}
                <div class="pf-col">
                    <form action="{{ route('feature-profile.update') }}" method="POST" enctype="multipart/form-data" class="pr-panel" id="pf-form" aria-labelledby="pf-edit-title">
                        @csrf
                        @method('PUT')
                        <div class="pr-panel-head">
                            <div>
                                <h2 class="pr-panel-title" id="pf-edit-title">Kontak dan foto</h2>
                                <span class="pr-panel-sub">Foto langsung tersimpan. Email dan telepon baru berlaku setelah disetujui HR.</span>
                            </div>
                        </div>
                        <div class="pf-form-body">
                            <div class="pf-field">
                                <label for="email">Email pribadi</label>
                                <input type="email" id="email" name="email" maxlength="100" autocomplete="email"
                                    class="pf-input @error('email') is-invalid @enderror"
                                    value="{{ old('email', $emp->email ?? '') }}" placeholder="nama@contoh.com">
                                @error('email')<span class="pf-error">{{ $message }}</span>@enderror
                                @if ($emp?->pending_email)
                                    <span class="pf-hint is-pending">Menunggu persetujuan HR: {{ $emp->pending_email }}</span>
                                @endif
                            </div>
                            <div class="pf-field">
                                <label for="telp_number">Nomor telepon</label>
                                <input type="tel" id="telp_number" name="telp_number" maxlength="20" autocomplete="tel" inputmode="tel"
                                    class="pf-input @error('telp_number') is-invalid @enderror"
                                    value="{{ old('telp_number', $emp->telp_number ?? '') }}" placeholder="08xxxxxxxxxx">
                                @error('telp_number')<span class="pf-error">{{ $message }}</span>@enderror
                                @if ($emp?->pending_telp_number)
                                    <span class="pf-hint is-pending">Menunggu persetujuan HR: {{ $emp->pending_telp_number }}</span>
                                @endif
                            </div>

                            <div class="pf-uploads">
                                @foreach ([
                                    ['photos', 'Foto profil', 'preview-image', 'photo-placeholder', $emp?->photos ? route('useremployee.photo', basename($emp->photos)) : null, 2048],
                                    ['ktp_photos', 'Foto KTP', 'preview-image-ktp', 'photo-placeholder-ktp', $emp?->ktp_photos ? route('useremployeektp.photo', basename($emp->ktp_photos)) : null, 512],
                                    ['kk_photos', 'Foto KK', 'preview-image-kk', 'photo-placeholder-kk', $emp?->kk_photos ? route('useremployeekk.photo', basename($emp->kk_photos)) : null, 512],
                                ] as [$field, $label, $imgId, $phId, $src, $maxKb])
                                    <div class="pf-upload">
                                        <span class="pf-upload-name">{{ $label }}</span>
                                        <div class="pf-thumb">
                                            <img id="{{ $imgId }}" src="{{ $src ?? '' }}" alt="{{ $label }}" onclick="openImageModal(this.src)" @if (!$src) hidden @endif>
                                            <span class="pf-thumb-empty" id="{{ $phId }}" onclick="document.getElementById('{{ $field }}').click()" @if ($src) hidden @endif>
                                                <i class="fas fa-image" aria-hidden="true"></i> Belum ada
                                            </span>
                                        </div>
                                        <label for="{{ $field }}" class="pr-btn pr-btn-sm pf-file-label">
                                            <i class="fas fa-upload" aria-hidden="true"></i> {{ $src ? 'Ganti' : 'Unggah' }}
                                        </label>
                                        <input type="file" name="{{ $field }}" id="{{ $field }}" class="pf-visually-hidden js-photo-input"
                                            accept=".jpg,.jpeg,.png,.webp" data-max-kb="{{ $maxKb }}" data-img="{{ $imgId }}" data-ph="{{ $phId }}">
                                        <span class="pf-file-state" id="{{ $field }}-state">JPG, PNG, WEBP, maks {{ $maxKb >= 1024 ? ($maxKb / 1024) . ' MB' : $maxKb . ' KB' }}</span>
                                        @error($field)<span class="pf-error">{{ $message }}</span>@enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="pf-form-foot">
                            <span class="pf-dirty" id="pf-dirty" hidden>Ada perubahan yang belum disimpan</span>
                            <button type="submit" class="pr-btn pr-btn-primary" style="margin-left:auto"><i class="fas fa-floppy-disk" aria-hidden="true"></i> Simpan perubahan</button>
                        </div>
                    </form>

                    <section class="pr-panel" aria-labelledby="pf-sec-title">
                        <div class="pr-panel-head">
                            <div>
                                <h2 class="pr-panel-title" id="pf-sec-title">Keamanan akun</h2>
                            </div>
                        </div>
                        <ul class="pf-sec">
                            <li>
                                <span class="pf-sec-main">
                                    <span class="pf-check-name">Password</span>
                                    <span class="pf-check-sub">Ganti secara berkala, jangan sama dengan NIP.</span>
                                </span>
                                <a href="{{ route('pages.change-password') }}" class="pr-btn pr-btn-sm">Ganti</a>
                            </li>
                            <li>
                                <span class="pf-sec-main">
                                    <span class="pf-check-name">Verifikasi dua langkah (2FA)</span>
                                    <span class="pf-check-sub">
                                        @if ($user->hasTwoFactorEnabled())
                                            Aktif. Login butuh kode dari aplikasi Authenticator.
                                        @elseif ($user->requiresTwoFactor())
                                            Diwajibkan untuk akun Anda tapi belum diaktifkan.
                                        @else
                                            Belum aktif. Melindungi akun walau password bocor.
                                        @endif
                                    </span>
                                </span>
                                @if ($user->hasTwoFactorEnabled())
                                    <span class="status-badge is-approved">Aktif</span>
                                @else
                                    <a href="{{ route('2fa.setup') }}" class="pr-btn pr-btn-sm {{ $user->requiresTwoFactor() ? 'pr-btn-primary' : '' }}">Aktifkan</a>
                                @endif
                            </li>
                            @if (count($roles) > 1)
                                <li>
                                    <span class="pf-sec-main" style="flex:1">
                                        <span class="pf-check-name">Role aktif</span>
                                        <span class="pf-check-sub">Menu dan dashboard mengikuti role yang aktif.</span>
                                        <form action="{{ route('profile.switchRole') }}" method="POST" class="pf-role-form">
                                            @csrf
                                            @method('PUT')
                                            <label for="active_role_hrx" class="pf-visually-hidden">Pilih role</label>
                                            <select name="active_role_hrx" id="active_role_hrx" class="pf-input">
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role }}" @selected(Auth::user()->active_role_hrx === $role)>{{ $role }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="pr-btn pr-btn-sm">Ganti role</button>
                                        </form>
                                        @error('active_role_hrx')<span class="pf-error">{{ $message }}</span>@enderror
                                    </span>
                                </li>
                            @endif
                        </ul>
                    </section>

                    <section class="pr-panel" aria-labelledby="pf-sign-title">
                        <div class="pr-panel-head">
                            <div>
                                <h2 class="pr-panel-title" id="pf-sign-title">Tanda tangan</h2>
                                <span class="pr-panel-sub">
                                    {{ !empty($emp?->signature) ? 'Tersimpan. Untuk mengubah, hubungi HR.' : 'Dipakai di SK dan surat resmi. Hanya bisa disimpan sekali.' }}
                                </span>
                            </div>
                            <span class="status-badge {{ !empty($emp?->signature) ? 'is-approved' : 'is-pending' }}">
                                {{ !empty($emp?->signature) ? 'Tersimpan' : 'Belum ada' }}
                            </span>
                        </div>
                        <div class="pf-sign-body">
                            @if (!empty($emp?->signature))
                                <div class="pf-sign-saved">
                                    <img src="{{ route('useremployeesignature.photo', basename($emp->signature)) }}" alt="Tanda tangan Anda">
                                </div>
                            @else
                                <form method="POST" action="{{ route('save.signature') }}" id="form-signature" enctype="multipart/form-data">
                                    @csrf
                                    <div class="pr-tabs" role="group" aria-label="Cara membuat tanda tangan" style="margin-bottom: var(--pr-space-sm)">
                                        <button type="button" id="tab-draw" aria-pressed="true" onclick="switchTab('draw')">Gambar</button>
                                        <button type="button" id="tab-import" aria-pressed="false" onclick="switchTab('import')">Unggah file</button>
                                    </div>

                                    <div id="section-draw">
                                        <div class="pf-pad">
                                            <canvas id="signature-pad" aria-label="Area tanda tangan"></canvas>
                                        </div>
                                        <span class="pf-hint">Tanda tangani di kotak dengan mouse atau jari.</span>
                                        <input type="hidden" name="signature" id="signature-input">
                                        @error('signature')<span class="pf-error">{{ $message }}</span>@enderror
                                    </div>

                                    <div id="section-import" hidden>
                                        <div class="pf-sign-import">
                                            <img id="preview-signature-import" src="" alt="" hidden>
                                            <i class="fas fa-file-image" id="signature-import-placeholder" aria-hidden="true" style="font-size:2rem;color:var(--pr-muted)"></i>
                                            <div>
                                                <label for="signature_file" class="pr-btn pr-btn-sm pf-file-label">Pilih file</label>
                                                <input type="file" name="signature_file" id="signature_file" class="pf-visually-hidden"
                                                    accept=".jpg,.jpeg,.png,.webp" onchange="previewSignatureImport(event)">
                                                <span class="pf-hint" id="signature-file-name">JPG, PNG, WEBP, maks 512 KB. Latar putih atau transparan.</span>
                                            </div>
                                        </div>
                                        @error('signature_file')<span class="pf-error">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="pf-sign-actions">
                                        <button type="button" class="pr-btn pr-btn-sm" id="clear-signature">Hapus coretan</button>
                                        <button type="submit" class="pr-btn pr-btn-primary">Simpan tanda tangan</button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </section>
                </div>
            </div>

            <div class="pf-lightbox" id="imagePreviewModal" onclick="closeImageModal()" role="dialog" aria-modal="true" aria-label="Pratinjau gambar">
                <button type="button" onclick="closeImageModal()" aria-label="Tutup">×</button>
                <img id="modalPreviewImage" alt="">
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

    <script>
        // ─── Lightbox gambar ───────────────────────────────────────────────
        function openImageModal(src) {
            if (!src) return;
            document.getElementById('modalPreviewImage').src = src;
            document.getElementById('imagePreviewModal').classList.add('is-open');
        }

        function closeImageModal() {
            document.getElementById('imagePreviewModal').classList.remove('is-open');
        }
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeImageModal(); });

        // ─── Pratinjau foto + cek ukuran sebelum upload ───────────────────
        const dirty = document.getElementById('pf-dirty');
        const markDirty = () => { if (dirty) dirty.hidden = false; };
        document.querySelectorAll('#pf-form input[type="email"], #pf-form input[type="tel"]').forEach(i => i.addEventListener('input', markDirty));

        document.querySelectorAll('.js-photo-input').forEach(input => input.addEventListener('change', function() {
            const file = this.files[0];
            const state = document.getElementById(this.id + '-state');
            if (!file) return;
            const maxKb = Number(this.dataset.maxKb);
            if (!/\.(jpe?g|png|webp)$/i.test(file.name) || file.size > maxKb * 1024) {
                this.value = '';
                Swal.fire({
                    icon: 'warning',
                    title: 'File tidak bisa dipakai',
                    text: `Gunakan JPG, PNG, atau WEBP dengan ukuran maksimal ${maxKb >= 1024 ? maxKb / 1024 + ' MB' : maxKb + ' KB'}. File Anda ${Math.ceil(file.size / 1024)} KB.`,
                    confirmButtonColor: '#4f46e5'
                });
                return;
            }
            const img = document.getElementById(this.dataset.img);
            img.src = URL.createObjectURL(file);
            img.hidden = false;
            document.getElementById(this.dataset.ph).hidden = true;
            state.textContent = `Baru: ${file.name}, belum disimpan`;
            state.classList.add('is-new');
            markDirty();
        }));

        // ─── Notifikasi flash ──────────────────────────────────────────────
        @if (session('success') || session('status'))
            Swal.fire({
                icon: 'success',
                title: 'Tersimpan',
                text: @json(session('success') ?? session('status')),
                confirmButtonColor: '#4f46e5',
                timer: 3000,
                timerProgressBar: true
            });
        @endif
        @if (session('error'))
            Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')), confirmButtonColor: '#dc2626' });
        @endif

        // ─── Signature pad ─────────────────────────────────────────────────
        const canvas = document.getElementById('signature-pad');
        let signaturePad = null;

        if (canvas) {
            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const data = signaturePad ? signaturePad.toData() : null;
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                const ctx = canvas.getContext('2d');
                ctx.scale(ratio, ratio);
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                if (signaturePad && data) signaturePad.fromData(data);
            }

            resizeCanvas();
            window.addEventListener('resize', resizeCanvas);

            signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgba(0,0,0,0)',
                penColor: 'black',
                minWidth: 4,
                maxWidth: 7,
                velocityFilterWeight: 0.7
            });

            document.getElementById('clear-signature').addEventListener('click', () => signaturePad.clear());
        }

        // ─── Export tanda tangan (crop + skala ke 800x300) ─────────────────
        function exportSignatureAsPNG() {
            const imgData = canvas.getContext('2d').getImageData(0, 0, canvas.width, canvas.height);
            const { data, width, height } = imgData;
            let minX = width, minY = height, maxX = 0, maxY = 0;

            for (let y = 0; y < height; y++) {
                for (let x = 0; x < width; x++) {
                    if (data[(y * width + x) * 4 + 3] > 10) {
                        if (x < minX) minX = x;
                        if (x > maxX) maxX = x;
                        if (y < minY) minY = y;
                        if (y > maxY) maxY = y;
                    }
                }
            }

            const padding = 20;
            minX = Math.max(0, minX - padding);
            minY = Math.max(0, minY - padding);
            maxX = Math.min(width, maxX + padding);
            maxY = Math.min(height, maxY + padding);

            const cropW = maxX - minX;
            const cropH = maxY - minY;
            const targetW = 800;
            const targetH = 300;
            const scale = Math.min(targetW / cropW, targetH / cropH, 2);
            const drawW = cropW * scale;
            const drawH = cropH * scale;

            const exportCanvas = document.createElement('canvas');
            exportCanvas.width = targetW;
            exportCanvas.height = targetH;
            exportCanvas.getContext('2d').drawImage(canvas, minX, minY, cropW, cropH, (targetW - drawW) / 2, (targetH - drawH) / 2, drawW, drawH);

            return exportCanvas.toDataURL('image/png');
        }

        // ─── Tab gambar / unggah ───────────────────────────────────────────
        function switchTab(tab) {
            const isDraw = tab === 'draw';
            document.getElementById('section-draw').hidden = !isDraw;
            document.getElementById('section-import').hidden = isDraw;
            document.getElementById('tab-draw').setAttribute('aria-pressed', isDraw);
            document.getElementById('tab-import').setAttribute('aria-pressed', !isDraw);
            document.getElementById('clear-signature').hidden = !isDraw;

            if (isDraw) {
                document.getElementById('signature_file').value = '';
                document.getElementById('preview-signature-import').hidden = true;
                document.getElementById('signature-import-placeholder').hidden = false;
                document.getElementById('signature-file-name').textContent = 'JPG, PNG, WEBP, maks 512 KB. Latar putih atau transparan.';
                resizeCanvas();
            } else {
                document.getElementById('signature-input').value = '';
                if (signaturePad) signaturePad.clear();
            }
        }

        function previewSignatureImport(event) {
            const file = event.target.files[0];
            if (!file) return;
            if (file.size > 512 * 1024) {
                event.target.value = '';
                Swal.fire({ icon: 'warning', title: 'File terlalu besar', text: `Maksimal 512 KB, file Anda ${Math.ceil(file.size / 1024)} KB.`, confirmButtonColor: '#4f46e5' });
                return;
            }
            const img = document.getElementById('preview-signature-import');
            img.src = URL.createObjectURL(file);
            img.hidden = false;
            document.getElementById('signature-import-placeholder').hidden = true;
            document.getElementById('signature-file-name').textContent = file.name;
        }

        // ─── Submit tanda tangan ───────────────────────────────────────────
        const formSignature = document.getElementById('form-signature');
        if (formSignature) {
            formSignature.addEventListener('submit', function(e) {
                const isDraw = !document.getElementById('section-draw').hidden;
                e.preventDefault();

                if (isDraw && (!signaturePad || signaturePad.isEmpty())) {
                    Swal.fire({ icon: 'warning', title: 'Tanda tangan kosong', text: 'Silakan buat tanda tangan terlebih dahulu.', confirmButtonColor: '#4f46e5' });
                    return;
                }
                if (!isDraw && !document.getElementById('signature_file').files.length) {
                    Swal.fire({ icon: 'warning', title: 'File kosong', text: 'Silakan pilih file tanda tangan terlebih dahulu.', confirmButtonColor: '#4f46e5' });
                    return;
                }

                Swal.fire({
                    icon: 'question',
                    title: 'Simpan tanda tangan?',
                    text: 'Tanda tangan hanya bisa disimpan sekali. Setelah itu, perubahan harus lewat HR.',
                    showCancelButton: true,
                    confirmButtonText: 'Simpan',
                    cancelButtonText: 'Periksa lagi',
                    confirmButtonColor: '#4f46e5'
                }).then(r => {
                    if (!r.isConfirmed) return;
                    if (isDraw) {
                        document.getElementById('signature-input').value = exportSignatureAsPNG();
                        document.getElementById('signature_file').value = '';
                    } else {
                        document.getElementById('signature-input').value = '';
                    }
                    formSignature.submit();
                });
            });
        }
    </script>
@endpush
