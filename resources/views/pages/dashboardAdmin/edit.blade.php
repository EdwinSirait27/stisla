@extends('layouts.app')
@section('title', 'Edit User')
@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand · redesign: full
         * paper: oklch(98.4% 0.004 265) · accent: oklch(61% 0.17 276) · display: Nunito · body: Nunito
         * nav: kept (Stisla shell) · signature: role cards with their reach, beside the account facts and recovery actions · states: loading, empty, full, blocked
         * axes: light/rounded-sans/chromatic-indigo · posture: app · contrast: pass · v2.0.0 */
        /* Hallmark · pre-emit critique: P4 H4 E4 S4 R4 V4 · weakest: E, an account row did not say whether it can log in at all, answered with a login column and filters */

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


        /* ── Permissions pages ── */
        .pr-actions {
            display: flex;
            flex-wrap: wrap;
            gap: var(--pr-space-xs);
        }

        .pr-lede {
            line-height: 1.5;
        }

        .pr code,
        .pr-code {
            font-family: SFMono-Regular, Consolas, "Liberation Mono", Menlo, monospace;
            font-size: .85em;
            padding: 1px 6px;
            border-radius: 4px;
            background: var(--pr-paper-2);
            color: var(--pr-ink);
        }

        .pr-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            margin-bottom: var(--pr-space-lg);
        }

        .pr-stat {
            display: block;
            width: 100%;
            text-align: left;
            padding: var(--pr-space-md) var(--pr-space-lg);
            border: 0;
            border-right: 1px solid var(--pr-rule);
            background: transparent;
            font-family: var(--pr-font);
            color: var(--pr-ink);
            text-decoration: none;
        }

        .pr-stats>*:last-child .pr-stat,
        .pr-stats>.pr-stat:last-child {
            border-right: 0;
        }

        button.pr-stat {
            cursor: pointer;
            transition: background-color var(--pr-dur-micro) var(--pr-ease);
        }

        button.pr-stat:hover {
            background: var(--pr-paper);
        }

        button.pr-stat[aria-pressed="true"] {
            background: var(--pr-accent-soft);
        }

        a.pr-stat:hover {
            background: var(--pr-paper);
            color: var(--pr-ink);
            text-decoration: none;
        }

        .pr-stat-label {
            display: block;
            font-size: var(--pr-text-sm);
            font-weight: 600;
            color: var(--pr-ink-2);
        }

        .pr-stat-value {
            display: block;
            margin-top: var(--pr-space-2xs);
            font-size: var(--pr-text-xl);
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        .pr-stat-note {
            display: block;
            margin-top: 2px;
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
        }

        .pr-stat.is-warn .pr-stat-value {
            color: var(--pr-warn);
        }

        .pr-tablebar {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: var(--pr-space-sm);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-bottom: 1px solid var(--pr-rule);
        }

        .pr-tablebar-group {
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

        .pr-field input.is-invalid {
            border-color: var(--pr-error);
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
            min-height: 36px;
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

        #permissions-table {
            width: 100% !important;
            font-size: var(--pr-text-sm);
            border-collapse: collapse;
        }

        #permissions-table thead th {
            background: var(--pr-panel);
            color: var(--pr-ink-2);
            font-size: var(--pr-text-xs);
            font-weight: 700;
            padding: var(--pr-space-sm) var(--pr-space-xs);
            border: 0;
            border-bottom: 1px solid var(--pr-rule);
            white-space: nowrap;
        }

        #permissions-table thead th.sorting,
        #permissions-table thead th.sorting_asc,
        #permissions-table thead th.sorting_desc {
            padding-right: var(--pr-space-lg);
        }

        #permissions-table tbody td {
            padding: var(--pr-space-sm) var(--pr-space-xs);
            vertical-align: middle;
            border: 0;
            border-bottom: 1px solid var(--pr-rule);
            color: var(--pr-ink);
        }

        #permissions-table tbody tr:hover td {
            background: var(--pr-paper);
        }

        .pr-perm-name {
            display: block;
            font-weight: 700;
            word-break: break-word;
        }

        .pr-perm-group {
            display: block;
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
        }

        .pr-roles {
            display: flex;
            flex-wrap: wrap;
            gap: var(--pr-space-2xs);
        }

        .pr-role {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: var(--pr-pill);
            border: 1px solid var(--pr-rule);
            font-size: var(--pr-text-xs);
            font-weight: 600;
            color: var(--pr-ink-2);
            white-space: nowrap;
        }

        .pr-flag {
            display: inline-flex;
            align-items: center;
            gap: var(--pr-space-2xs);
            font-size: var(--pr-text-xs);
            font-weight: 700;
            color: var(--pr-warn);
        }

        .pr-usage {
            font-variant-numeric: tabular-nums;
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
            transition: background-color var(--pr-dur-micro) var(--pr-ease), color var(--pr-dur-micro) var(--pr-ease);
        }

        .act-btn:hover {
            background: var(--pr-paper-2);
            color: var(--pr-ink);
            text-decoration: none;
        }

        .pr-alert {
            padding: var(--pr-space-sm) var(--pr-space-lg);
            margin-bottom: var(--pr-space-lg);
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius);
            font-size: var(--pr-text-sm);
        }

        .pr-alert.is-success {
            background: var(--pr-success-soft);
            color: var(--pr-success);
        }

        .pr-alert.is-error {
            background: var(--pr-error-soft);
            color: var(--pr-error);
        }

        .pr-alert.is-warn {
            background: var(--pr-warn-soft);
            color: var(--pr-ink);
        }

        .pr-alert.is-warn strong {
            color: var(--pr-warn);
        }

        /* Form pages */
        .pr-form-layout {
            display: grid;
            grid-template-columns: minmax(0, 7fr) minmax(0, 5fr);
            gap: var(--pr-space-lg);
            align-items: start;
        }

        .pr-form {
            padding: var(--pr-space-lg);
        }

        .pr-form .pr-field {
            margin-bottom: var(--pr-space-md);
        }

        .pr-form .pr-field label {
            font-size: var(--pr-text-sm);
        }

        .pr-form .pr-field input {
            min-height: 40px;
            font-size: var(--pr-text-md);
        }

        .pr-hint {
            margin: var(--pr-space-2xs) 0 0;
            font-size: var(--pr-text-xs);
            line-height: 1.5;
            color: var(--pr-ink-2);
        }

        .pr-error-text {
            margin: var(--pr-space-2xs) 0 0;
            font-size: var(--pr-text-xs);
            font-weight: 600;
            color: var(--pr-error);
        }

        .pr-live {
            margin: var(--pr-space-xs) 0 0;
            font-size: var(--pr-text-xs);
            font-weight: 600;
            min-height: 1.4em;
        }

        .pr-live.is-ok {
            color: var(--pr-success);
        }

        .pr-live.is-bad {
            color: var(--pr-error);
        }

        .pr-form-actions {
            display: flex;
            justify-content: flex-end;
            gap: var(--pr-space-xs);
            padding-top: var(--pr-space-md);
            border-top: 1px solid var(--pr-rule);
        }

        .pr-side-body {
            padding: var(--pr-space-md) var(--pr-space-lg);
            font-size: var(--pr-text-sm);
            line-height: 1.55;
            color: var(--pr-ink-2);
        }

        .pr-side-body p {
            margin: 0 0 var(--pr-space-sm);
        }

        .pr-examples {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .pr-examples li {
            display: flex;
            justify-content: space-between;
            gap: var(--pr-space-sm);
            padding: var(--pr-space-xs) 0;
            border-bottom: 1px solid var(--pr-rule);
        }

        .pr-examples li:last-child {
            border-bottom: 0;
        }

        .pr-chips {
            display: flex;
            flex-wrap: wrap;
            gap: var(--pr-space-2xs);
        }

        .pr-chips button {
            padding: 2px 8px;
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-pill);
            background: var(--pr-panel);
            font-family: var(--pr-font);
            font-size: var(--pr-text-xs);
            font-weight: 600;
            color: var(--pr-ink-2);
            cursor: pointer;
        }

        .pr-chips button:hover {
            background: var(--pr-accent-soft);
            border-color: var(--pr-accent);
            color: var(--pr-accent-strong);
        }

        .pr-locations {
            margin: 0;
            padding: 0;
            list-style: none;
            max-height: 360px;
            overflow-y: auto;
        }

        .pr-locations li {
            padding: var(--pr-space-xs) var(--pr-space-lg);
            border-bottom: 1px solid var(--pr-rule);
        }

        .pr-locations li:last-child {
            border-bottom: 0;
        }

        .pr-loc-file {
            display: block;
            font-size: var(--pr-text-xs);
            font-weight: 700;
            color: var(--pr-ink);
            word-break: break-all;
        }

        .pr-loc-text {
            display: block;
            margin-top: 2px;
            font-family: SFMono-Regular, Consolas, "Liberation Mono", Menlo, monospace;
            font-size: 11px;
            color: var(--pr-ink-2);
            word-break: break-all;
        }

        .pr-empty-note {
            padding: var(--pr-space-md) var(--pr-space-lg);
            font-size: var(--pr-text-sm);
            color: var(--pr-muted);
        }

        @media (max-width: 75rem) {
            .pr-form-layout {
                grid-template-columns: minmax(0, 1fr);
            }

            .pr-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .pr-stats>*:nth-child(2) .pr-stat,
            .pr-stats>.pr-stat:nth-child(2) {
                border-right: 0;
            }
        }

        @media (max-width: 40rem) {

            .pr-head,
            .pr-panel-head,
            .pr-tablebar,
            .pr-table-wrap,
            .pr-form,
            .pr-side-body,
            .pr-stat,
            .pr-alert {
                padding-left: var(--pr-space-md);
                padding-right: var(--pr-space-md);
            }

            .pr-tablebar-group,
            .pr-search,
            .pr-search input,
            .pr-tabs,
            .pr-field {
                width: 100%;
            }

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

        /* ── Users pages ── */
        .pr-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: var(--pr-space-xs) var(--pr-space-sm);
            margin-top: var(--pr-space-xs);
            font-size: var(--pr-text-sm);
            color: var(--pr-ink-2);
        }

        .pr-cell-name {
            display: block;
            font-weight: 700;
        }

        .pr-cell-sub {
            display: block;
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
        }

        .pr-mono {
            font-family: SFMono-Regular, Consolas, "Liberation Mono", Menlo, monospace;
            font-size: var(--pr-text-xs);
        }

        .pr-twofa-actions {
            display: flex;
            gap: var(--pr-space-sm);
            margin-top: var(--pr-space-2xs);
        }

        .pr-twofa-actions form {
            margin: 0;
        }

        .pr-link {
            border: 0;
            background: none;
            padding: 0;
            font-family: var(--pr-font);
            font-size: var(--pr-text-xs);
            font-weight: 700;
            color: var(--pr-accent-strong);
            text-decoration: underline;
            text-underline-offset: .16em;
            cursor: pointer;
        }

        .pr-link.is-danger,
        .pr-danger-text {
            color: var(--pr-error);
        }

        .bulk-bar {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: var(--pr-space-xs);
            padding: var(--pr-space-sm) var(--pr-space-lg);
            background: var(--pr-accent-soft);
            border-bottom: 1px solid var(--pr-rule);
            position: sticky;
            top: 70px;
            z-index: 20;
        }

        .bulk-bar-count {
            font-size: var(--pr-text-sm);
            font-weight: 700;
            margin-right: var(--pr-space-xs);
        }

        .bulk-bar-hint {
            flex-basis: 100%;
            font-size: var(--pr-text-xs);
            color: var(--pr-ink-2);
        }

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

        #users-table thead th.sorting,
        #users-table thead th.sorting_asc,
        #users-table thead th.sorting_desc {
            padding-right: var(--pr-space-lg);
        }

        #users-table tbody td {
            padding: var(--pr-space-sm) var(--pr-space-xs);
            vertical-align: middle;
            border: 0;
            border-bottom: 1px solid var(--pr-rule);
            color: var(--pr-ink);
        }

        #users-table tbody tr:hover td {
            background: var(--pr-paper);
        }

        #users-table input[type="checkbox"],
        #check-all {
            width: 16px;
            height: 16px;
            accent-color: var(--pr-accent);
            cursor: pointer;
        }

        .pr .dataTables_wrapper .dataTables_length select {
            min-height: 32px;
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            padding: 0 var(--pr-space-xs);
            font-family: var(--pr-font);
        }

        .pr-dt-bottom>.dataTables_length,
        .pr-dt-bottom>.dataTables_info {
            float: none !important;
        }

        /* Edit */
        .pr-form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 240px), 1fr));
            gap: 0 var(--pr-space-lg);
        }

        .pr-optional {
            font-weight: 400;
            color: var(--pr-muted);
        }

        .pr-input-icon {
            position: relative;
        }

        .pr-input-icon input {
            padding-right: 44px !important;
        }

        .pr-eye {
            position: absolute;
            right: 4px;
            top: 50%;
            transform: translateY(-50%);
            width: 36px;
            height: 36px;
            border: 0;
            background: transparent;
            color: var(--pr-muted);
            cursor: pointer;
        }

        .pr-role-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(min(100%, 220px), 1fr));
            gap: var(--pr-space-xs);
            padding: var(--pr-space-md) var(--pr-space-lg) var(--pr-space-lg);
        }

        .pr-role-option {
            display: flex;
            align-items: flex-start;
            gap: var(--pr-space-sm);
            margin: 0;
            padding: var(--pr-space-sm);
            border: 1px solid var(--pr-rule);
            border-radius: var(--pr-radius-sm);
            cursor: pointer;
            transition: background-color var(--pr-dur-micro) var(--pr-ease), border-color var(--pr-dur-micro) var(--pr-ease);
        }

        .pr-role-option:hover {
            background: var(--pr-paper);
        }

        .pr-role-option:has(input:checked) {
            border-color: var(--pr-accent);
            background: var(--pr-accent-soft);
        }

        .pr-role-option input {
            width: 16px;
            height: 16px;
            margin-top: 2px;
            accent-color: var(--pr-accent);
            flex-shrink: 0;
        }

        .pr-role-name {
            display: block;
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-ink);
        }

        .pr-role-meta {
            display: block;
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
        }

        .pr-facts {
            margin: 0;
        }

        .pr-facts>div {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(0, 3fr);
            gap: var(--pr-space-sm);
            padding: var(--pr-space-xs) 0;
            border-bottom: 1px solid var(--pr-rule);
            font-size: var(--pr-text-sm);
        }

        .pr-facts>div:last-child {
            border-bottom: 0;
        }

        .pr-facts dt {
            font-weight: 400;
            color: var(--pr-ink-2);
        }

        .pr-facts dd {
            margin: 0;
            font-weight: 700;
            color: var(--pr-ink);
            overflow-wrap: anywhere;
        }

        .pr-note {
            margin: 0;
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-top: 1px solid var(--pr-rule);
            font-size: var(--pr-text-xs);
            line-height: 1.5;
        }

        .pr-note.is-warn {
            background: var(--pr-warn-soft);
            color: var(--pr-ink);
        }

        .pr-side-actions {
            display: flex;
            flex-wrap: wrap;
            gap: var(--pr-space-xs);
            padding: var(--pr-space-sm) var(--pr-space-lg) var(--pr-space-md);
            border-top: 1px solid var(--pr-rule);
        }

        .pr-side-actions form {
            margin: 0;
        }

        .pr-side-stack .pr-panel {
            margin-bottom: var(--pr-space-lg);
        }

        #reset-link-btn:disabled {
            opacity: .55;
            cursor: not-allowed;
        }
    </style>
@endpush

@section('main')
    @php
        $emp = $user->Employee;
        $fmt = fn($d) => $d ? \Carbon\Carbon::parse($d)->setTimezone(config('app.timezone'))->locale('id')->translatedFormat('d M Y, H:i') : '-';
        $twofaLabel = ['active' => ['is-open', 'Aktif'], 'required' => ['is-closed', 'Wajib, belum setup'], 'off' => ['is-locked', 'Tidak aktif']][$twofaState];
        $chosenRoles = old('roles', $selectedRoles ?? []);
        // Ringkas user-agent: "Chrome di macOS"
        $device = null;
        if ($session?->device_type) {
            $ua = $session->device_type;
            $browser = collect(['Edg' => 'Edge', 'OPR' => 'Opera', 'Chrome' => 'Chrome', 'Firefox' => 'Firefox', 'Safari' => 'Safari'])
                ->first(fn($label, $needle) => str_contains($ua, $needle)) ?? 'Browser';
            $os = collect(['Android' => 'Android', 'iPhone' => 'iOS', 'iPad' => 'iPadOS', 'Windows' => 'Windows', 'Mac OS X' => 'macOS', 'Linux' => 'Linux'])
                ->first(fn($label, $needle) => str_contains($ua, $needle)) ?? '';
            $device = trim($browser . ($os ? ' di ' . $os : ''));
            if (strlen($ua) < 25) { $device = $ua; }
        }
    @endphp
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs" aria-label="Breadcrumb">
                        <li>Dashboard</li>
                        <li><a href="{{ route('pages.dashboardAdmin') }}">Users</a></li>
                        <li aria-current="page">Edit</li>
                    </ol>
                    <h1 class="pr-title">{{ $emp->employee_name ?? $user->username }}</h1>
                    <div class="pr-meta">
                        <span>Username {{ $user->username }}</span>
                        @if ($emp?->employee_pengenal && $emp->employee_pengenal !== $user->username)
                            <span>NIP {{ $emp->employee_pengenal }}</span>
                        @endif
                        @if ($canLogin)
                            <span class="status-badge is-open">Bisa login</span>
                        @else
                            <span class="status-badge is-locked">Diblokir, karyawan {{ $emp->status ?? 'tidak terhubung' }}</span>
                        @endif
                    </div>
                </div>
            </header>

            @if ($errors->any())
                <div class="pr-alert is-error" role="alert">
                    <strong>Periksa kembali isian berikut:</strong>
                    <ul style="margin:4px 0 0;padding-left:20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if (session('error'))
                <div class="pr-alert is-error" role="alert">{{ session('error') }}</div>
            @endif
            @if (session('success'))
                <div class="pr-alert is-success" role="status">{{ session('success') }}</div>
            @endif

            <div class="pr-form-layout">
                <form id="user-edit" action="{{ route('dashboardAdmin.update', $hashedId) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')

                    <section class="pr-panel" aria-labelledby="login-title">
                        <div class="pr-panel-head">
                            <h2 class="pr-panel-title" id="login-title">Login</h2>
                        </div>
                        <div class="pr-form pr-form-grid">
                            <div class="pr-field">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" required
                                    minlength="8" maxlength="20" autocomplete="off" class="{{ $errors->has('username') ? 'is-invalid' : '' }}"
                                    oninput="this.value = this.value.replace(/[^a-zA-Z0-9_-]/g, '')">
                                @error('username')
                                    <p class="pr-error-text">{{ $message }}</p>
                                @enderror
                                <p class="pr-hint">8 sampai 20 karakter: huruf, angka, underscore, atau strip.</p>
                            </div>
                            <div class="pr-field">
                                <label for="password">Password baru <span class="pr-optional">(opsional)</span></label>
                                <div class="pr-input-icon">
                                    <input type="password" id="password" name="password" placeholder="Kosongkan kalau tidak diganti"
                                        minlength="8" maxlength="20" autocomplete="new-password" class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                                        oninput="this.value = this.value.replace(/\s/g, '');">
                                    <button type="button" class="pr-eye" onclick="togglePassword()" aria-label="Tampilkan password">
                                        <i id="eyeIcon" class="fa fa-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="pr-error-text">{{ $message }}</p>
                                @enderror
                                <p class="pr-hint">8 sampai 20 karakter, wajib huruf besar, huruf kecil, angka, dan simbol. Lebih aman pakai link reset di samping.</p>
                            </div>
                        </div>
                    </section>

                    <section class="pr-panel" aria-labelledby="att-title">
                        <div class="pr-panel-head">
                            <h2 class="pr-panel-title" id="att-title">Absensi dan perangkat</h2>
                        </div>
                        <div class="pr-form pr-form-grid">
                            <div class="pr-field">
                                <label for="pin">PIN fingerprint</label>
                                <input type="number" id="pin" name="pin" value="{{ old('pin', $emp->pin ?? '') }}" required
                                    maxlength="4" inputmode="numeric" placeholder="mis. 1024" class="{{ $errors->has('pin') ? 'is-invalid' : '' }}">
                                @error('pin')
                                    <p class="pr-error-text">{{ $message }}</p>
                                @enderror
                                <p class="pr-hint">Harus sama dengan PIN di mesin fingerprint, maksimal 4 digit.</p>
                            </div>
                            <div class="pr-field">
                                <label for="device_wifi_mac">MAC Wifi <span class="pr-optional">(opsional)</span></label>
                                <input type="text" id="device_wifi_mac" name="device_wifi_mac" value="{{ old('device_wifi_mac', $user->Terms->device_wifi_mac ?? '') }}"
                                    maxlength="255" placeholder="xx-xx-xx-xx-xx-xx" class="pr-mono {{ $errors->has('device_wifi_mac') ? 'is-invalid' : '' }}">
                                @error('device_wifi_mac')
                                    <p class="pr-error-text">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="pr-field">
                                <label for="device_lan_mac">MAC LAN <span class="pr-optional">(opsional)</span></label>
                                <input type="text" id="device_lan_mac" name="device_lan_mac" value="{{ old('device_lan_mac', $user->Terms->device_lan_mac ?? '') }}"
                                    maxlength="255" placeholder="xx-xx-xx-xx-xx-xx" class="pr-mono {{ $errors->has('device_lan_mac') ? 'is-invalid' : '' }}">
                                @error('device_lan_mac')
                                    <p class="pr-error-text">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    <section class="pr-panel" aria-labelledby="roles-title">
                        <div class="pr-panel-head">
                            <div>
                                <h2 class="pr-panel-title" id="roles-title">Role</h2>
                                <span class="pr-panel-sub">Role pertama yang dicentang menjadi role aktif saat login.</span>
                            </div>
                            <span class="pr-panel-sub" id="roles-count"></span>
                        </div>
                        @error('roles')
                            <p class="pr-error-text" style="padding: 8px 24px 0;">{{ $message }}</p>
                        @enderror
                        <div class="pr-role-grid">
                            @foreach ($roleOptions as $role)
                                <label class="pr-role-option">
                                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" @checked(in_array($role->name, $chosenRoles, true))>
                                    <span>
                                        <span class="pr-role-name">{{ $role->name }}</span>
                                        <span class="pr-role-meta">{{ $role->permissions_count }} permission, {{ $role->users_count }} user</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </section>

                    <div class="pr-panel pr-form-actions" style="padding:16px 24px;">
                        <a href="{{ route('pages.dashboardAdmin') }}" class="pr-btn pr-btn-ghost">Batal</a>
                        <button type="submit" id="edit-btn" class="pr-btn pr-btn-primary">
                            <i class="fas fa-save" aria-hidden="true"></i> Simpan perubahan
                        </button>
                    </div>
                </form>

                <aside class="pr-side-stack">
                    <section class="pr-panel" aria-labelledby="acc-title">
                        <div class="pr-panel-head">
                            <h2 class="pr-panel-title" id="acc-title">Akun</h2>
                        </div>
                        <dl class="pr-facts pr-side-body">
                            <div><dt>Karyawan</dt><dd>{{ $emp->employee_name ?? 'Belum terhubung' }}</dd></div>
                            <div><dt>Status karyawan</dt><dd>{{ $emp->status ?? '-' }}</dd></div>
                            <div><dt>Akun dibuat</dt><dd>{{ $fmt($user->created_at) }}</dd></div>
                            <div><dt>Sesi terakhir</dt><dd>{{ $session ? $fmt($session->last_activity) . ($device ? ', ' . $device : '') : 'Tidak ada sesi aktif' }}</dd></div>
                            <div>
                                <dt>2FA</dt>
                                <dd><span class="status-badge {{ $twofaLabel[0] }}">{{ $twofaLabel[1] }}</span></dd>
                            </div>
                        </dl>
                        @if (!$canLogin)
                            <p class="pr-note is-warn">Status karyawan {{ $emp->status ?? 'tidak terhubung' }}, jadi akun ini tidak bisa login. Menyimpan form ini tidak mengubah status karyawan.</p>
                        @endif
                        <div class="pr-side-actions">
                            <form method="POST" action="{{ route('admin.2fa.toggle-required', $user->id) }}">
                                @csrf
                                <button type="submit" class="pr-btn pr-btn-sm">{{ $twofaState === 'off' ? 'Wajibkan 2FA' : 'Batalkan wajib 2FA' }}</button>
                            </form>
                            @if ($twofaState === 'active')
                                <form method="POST" action="{{ route('admin.2fa.disable', $user->id) }}" id="twofa-reset-form">
                                    @csrf
                                    <button type="submit" class="pr-btn pr-btn-sm pr-btn-danger">Reset 2FA</button>
                                </form>
                            @endif
                        </div>
                    </section>

                    {{-- Reset password via email --}}
                    <section class="pr-panel" id="reset-password" aria-labelledby="reset-title">
                        <div class="pr-panel-head">
                            <h2 class="pr-panel-title" id="reset-title">Reset password via email</h2>
                        </div>
                        @if (session('reset_success'))
                            <div class="pr-alert is-success" role="status" style="margin:12px 24px 0;">{{ session('reset_success') }}</div>
                        @endif
                        @if (session('reset_error'))
                            <div class="pr-alert is-error" role="alert" style="margin:12px 24px 0;">{{ session('reset_error') }}</div>
                        @endif
                        <div class="pr-side-body">
                            <p>Karyawan membuat password baru sendiri lewat link sekali pakai, berlaku {{ \App\Services\PasswordResetLinkService::EXPIRE_MINUTES }} menit. Semua perangkat yang sedang login ikut keluar.</p>
                            <dl class="pr-facts">
                                <div><dt>Dikirim ke</dt><dd>{!! $resetEmail ? e($resetEmail) : '<span class="pr-danger-text">Belum ada email valid</span>' !!}</dd></div>
                                <div>
                                    <dt>Status link</dt>
                                    <dd>
                                        @if ($activeResetLink)
                                            <span class="status-badge is-closed">Aktif</span> sampai {{ $fmt($activeResetLink->expires_at) }}
                                        @elseif ($lastResetLink && $lastResetLink->used_at)
                                            <span class="status-badge is-open">Terpakai</span> {{ $fmt($lastResetLink->used_at) }}
                                        @elseif ($lastResetLink)
                                            <span class="status-badge is-locked">Kedaluwarsa</span>
                                        @else
                                            Belum pernah dikirim
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                            <form id="reset-link-form" action="{{ route('dashboardAdmin.resetLink', $hashedId) }}" method="POST">
                                @csrf
                                <button type="submit" id="reset-link-btn" class="pr-btn pr-btn-sm" @disabled(!$resetEmail)>
                                    <i class="fas fa-paper-plane" aria-hidden="true"></i>
                                    {{ $activeResetLink ? 'Kirim ulang link' : 'Kirim link reset password' }}
                                </button>
                            </form>
                            @if ($activeResetLink)
                                <p class="pr-hint">Mengirim ulang membuat link sebelumnya tidak berlaku.</p>
                            @endif
                        </div>
                    </section>
                </aside>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.classList.toggle('fa-eye', !show);
            icon.classList.toggle('fa-eye-slash', show);
        }

        (function() {
            const roleBoxes = Array.from(document.querySelectorAll('input[name="roles[]"]'));
            const originalRoles = @json($selectedRoles ?? []);
            const count = () => {
                const n = roleBoxes.filter(b => b.checked).length;
                document.getElementById('roles-count').textContent = n ? `${n} role dipilih` : 'Belum ada role';
            };
            roleBoxes.forEach(b => b.addEventListener('change', count));
            count();

            const esc = v => $('<div>').text(v).html();

            document.getElementById('edit-btn').addEventListener('click', function(e) {
                e.preventDefault();
                const chosen = roleBoxes.filter(b => b.checked).map(b => b.value);
                if (!chosen.length) {
                    Swal.fire({ icon: 'warning', title: 'Belum ada role', text: 'Pilih minimal satu role untuk akun ini.' });
                    return;
                }
                const added = chosen.filter(r => !originalRoles.includes(r));
                const removed = originalRoles.filter(r => !chosen.includes(r));
                const lines = [];
                if (document.getElementById('password').value) lines.push('Password diganti.');
                if (added.length) lines.push('Role ditambah: <b>' + added.map(esc).join(', ') + '</b>');
                if (removed.length) lines.push('Role dihapus: <b>' + removed.map(esc).join(', ') + '</b>');
                if (!lines.length) lines.push('Simpan perubahan username, PIN, atau perangkat.');
                Swal.fire({
                    title: 'Simpan perubahan akun?',
                    html: lines.join('<br>'),
                    icon: removed.length ? 'warning' : 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, simpan',
                    cancelButtonText: 'Batal'
                }).then(r => { if (r.isConfirmed) document.getElementById('user-edit').submit(); });
            });

            document.getElementById('reset-link-form')?.addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this;
                Swal.fire({
                    title: 'Kirim link reset password?',
                    html: 'Link sekali pakai akan dikirim ke <b>{{ e($resetEmail ?? '-') }}</b>.<br>Password lama tetap berlaku sampai karyawan membuat password baru.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, kirim',
                    cancelButtonText: 'Batal'
                }).then(r => {
                    if (r.isConfirmed) {
                        document.getElementById('reset-link-btn').disabled = true;
                        form.submit();
                    }
                });
            });

            document.getElementById('twofa-reset-form')?.addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this;
                Swal.fire({ title: 'Reset 2FA akun ini?', text: 'User harus setup 2FA ulang saat login berikutnya.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d', confirmButtonText: 'Ya, reset', cancelButtonText: 'Batal' })
                    .then(r => { if (r.isConfirmed) form.submit(); });
            });
        })();
    </script>
@endpush
