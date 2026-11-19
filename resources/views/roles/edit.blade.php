@extends('layouts.app')
@section('title', 'Edit Role')
@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand · redesign: full
         * paper: oklch(98.4% 0.004 265) · accent: oklch(61% 0.17 276) · display: Nunito · body: Nunito
         * nav: kept (Stisla shell) · signature: grouped permission picker with an added/removed summary beside the users and code it affects · states: loading, empty, full, flagged
         * axes: light/rounded-sans/chromatic-indigo · posture: app · contrast: pass · v2.0.0 */
        /* Hallmark · pre-emit critique: P4 H4 E4 S4 R4 V4 · weakest: E, 100 permissions in one flat list, answered with groups, search and a change summary */

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

        /* ── Roles pages ── */
        .pk-changes {
            display: flex;
            gap: var(--pr-space-xs);
        }

        .pk-chip {
            display: inline-flex;
            padding: 2px 8px;
            border-radius: var(--pr-pill);
            font-size: var(--pr-text-xs);
            font-weight: 700;
        }

        .pk-chip.is-add {
            background: var(--pr-success-soft);
            color: var(--pr-success);
        }

        .pk-chip.is-remove {
            background: var(--pr-error-soft);
            color: var(--pr-error);
        }

        .pk-toggle {
            display: inline-flex;
            align-items: center;
            gap: var(--pr-space-xs);
            margin: 0;
            min-height: 36px;
            font-size: var(--pr-text-sm);
            font-weight: 600;
            color: var(--pr-ink-2);
            cursor: pointer;
        }

        .pk-toggle input,
        .pk-item input {
            width: 16px;
            height: 16px;
            accent-color: var(--pr-accent);
            cursor: pointer;
            flex-shrink: 0;
        }

        .pk-groups {
            padding: var(--pr-space-xs) var(--pr-space-lg) var(--pr-space-lg);
            max-height: 620px;
            overflow-y: auto;
        }

        .pk-group {
            margin: 0;
            padding: var(--pr-space-md) 0 var(--pr-space-xs);
            border: 0;
            border-bottom: 1px solid var(--pr-rule);
            min-width: 0;
        }

        .pk-group:last-of-type {
            border-bottom: 0;
        }

        .pk-group-head {
            display: flex;
            align-items: baseline;
            gap: var(--pr-space-sm);
            width: 100%;
            margin-bottom: var(--pr-space-xs);
            font-size: var(--pr-text-md);
        }

        .pk-group-name {
            font-weight: 700;
            color: var(--pr-ink);
        }

        .pk-group-count {
            font-size: var(--pr-text-sm);
            color: var(--pr-muted);
            font-variant-numeric: tabular-nums;
        }

        .pk-group-head .pr-link {
            margin-left: auto;
            font-size: var(--pr-text-xs);
        }

        .pr-link {
            border: 0;
            background: none;
            padding: 0;
            font-family: var(--pr-font);
            font-weight: 700;
            color: var(--pr-accent-strong);
            text-decoration: underline;
            text-underline-offset: .16em;
            cursor: pointer;
        }

        .pr-link:hover {
            color: var(--pr-ink);
        }

        .pk-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(min(100%, 240px), 1fr));
            gap: var(--pr-space-2xs) var(--pr-space-md);
        }

        .pk-item {
            display: flex;
            align-items: center;
            gap: var(--pr-space-xs);
            margin: 0;
            padding: 6px var(--pr-space-xs);
            border-radius: var(--pr-radius-sm);
            font-size: var(--pr-text-sm);
            font-weight: 400;
            color: var(--pr-ink);
            cursor: pointer;
            word-break: break-word;
            transition: background-color var(--pr-dur-micro) var(--pr-ease);
        }

        .pk-item:hover {
            background: var(--pr-paper-2);
        }

        .pk-item:has(input:checked) {
            background: var(--pr-accent-soft);
            font-weight: 600;
        }

        .pr-meter {
            display: block;
            width: 120px;
            height: 6px;
            margin-top: var(--pr-space-2xs);
            border-radius: var(--pr-pill);
            background: var(--pr-paper-2);
            overflow: hidden;
        }

        .pr-meter span {
            display: block;
            height: 100%;
            background: var(--pr-accent);
        }

        .pr-users {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .pr-users li {
            display: flex;
            justify-content: space-between;
            gap: var(--pr-space-sm);
            padding: var(--pr-space-xs) 0;
            border-bottom: 1px solid var(--pr-rule);
            font-size: var(--pr-text-sm);
        }

        .pr-users li:last-child {
            border-bottom: 0;
        }

        .pr-users .muted {
            color: var(--pr-muted);
        }

        .pr-side-stack>.pr-panel+.pr-panel {
            margin-top: 0;
        }

        #roles-table {
            width: 100% !important;
            font-size: var(--pr-text-sm);
            border-collapse: collapse;
        }

        #roles-table thead th {
            background: var(--pr-panel);
            color: var(--pr-ink-2);
            font-size: var(--pr-text-xs);
            font-weight: 700;
            padding: var(--pr-space-sm) var(--pr-space-xs);
            border: 0;
            border-bottom: 1px solid var(--pr-rule);
            white-space: nowrap;
        }

        #roles-table thead th.sorting,
        #roles-table thead th.sorting_asc,
        #roles-table thead th.sorting_desc {
            padding-right: var(--pr-space-lg);
        }

        #roles-table tbody td {
            padding: var(--pr-space-sm) var(--pr-space-xs);
            vertical-align: middle;
            border: 0;
            border-bottom: 1px solid var(--pr-rule);
            color: var(--pr-ink);
        }

        #roles-table tbody tr:hover td {
            background: var(--pr-paper);
        }
    </style>
@endpush

@section('main')
    @php $byFile = collect($locations)->groupBy('file'); @endphp
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs" aria-label="Breadcrumb">
                        <li>Dashboard</li>
                        <li><a href="{{ route('roles.index') }}">Roles</a></li>
                        <li aria-current="page">Edit</li>
                    </ol>
                    <h1 class="pr-title">{{ $role->name }}</h1>
                    <p class="pr-lede">
                        Dipegang {{ $usersCount }} user, punya {{ count($rolePermissions) }} permission.
                        @if ($codeFiles)
                            Nama role ini dipakai langsung di {{ $codeFiles }} file kode.
                        @endif
                    </p>
                </div>
            </header>

            @if (session('error'))
                <div class="pr-alert is-error" role="alert">{{ session('error') }}</div>
            @endif

            <div class="pr-form-layout">
                <form id="roles-edit" action="{{ route('roles.update', $hashedId) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')

                    <section class="pr-panel" aria-labelledby="name-title">
                        <div class="pr-panel-head">
                            <h2 class="pr-panel-title" id="name-title">Nama role</h2>
                        </div>
                        <div class="pr-form">
                            @if ($codeFiles)
                                <div class="pr-alert is-warn" role="note">
                                    <strong>Nama ini dipakai di {{ $codeFiles }} file kode</strong>, misalnya untuk menentukan dashboard.
                                    Kalau diganti, user dengan role ini bisa kehilangan dashboard atau menu sampai kodenya ikut diubah.
                                </div>
                            @endif
                            <div class="pr-field" style="margin-bottom:0;">
                                <label for="name">Nama role</label>
                                <input id="name" type="text" name="name" value="{{ old('name', $role->name) }}" required maxlength="125"
                                    autocomplete="off" class="{{ $errors->has('name') ? 'is-invalid' : '' }}">
                                @error('name')
                                    <p class="pr-error-text">{{ $message }}</p>
                                @enderror
                                <p class="pr-live" id="name-live" aria-live="polite"></p>
                            </div>
                        </div>
                    </section>

                    @include('roles._permission-picker')

                    <div class="pr-panel pr-form-actions" style="padding:16px 24px;border-top:1px solid var(--pr-rule);">
                        <a href="{{ route('roles.index') }}" class="pr-btn pr-btn-ghost">Batal</a>
                        <button type="submit" id="edit-btn" class="pr-btn pr-btn-primary">
                            <i class="fas fa-save" aria-hidden="true"></i> Simpan perubahan
                        </button>
                    </div>
                </form>

                <aside class="pr-side-stack">
                    <section class="pr-panel" aria-labelledby="users-title">
                        <div class="pr-panel-head">
                            <div>
                                <h2 class="pr-panel-title" id="users-title">User dengan role ini</h2>
                                <span class="pr-panel-sub">{{ $usersCount }} user terdampak oleh perubahan</span>
                            </div>
                        </div>
                        <div class="pr-side-body">
                            @if ($users->isEmpty())
                                <p>Belum ada user. Berikan role ini lewat halaman Users.</p>
                            @else
                                <ul class="pr-users">
                                    @foreach ($users as $u)
                                        <li>
                                            <span>{{ $u->Employee->employee_name ?? $u->username }}</span>
                                            <span class="muted">{{ $u->username }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                                @if ($usersCount > $users->count())
                                    <p style="margin-top:8px;">dan {{ $usersCount - $users->count() }} user lainnya.</p>
                                @endif
                            @endif
                        </div>
                    </section>

                    <section class="pr-panel" aria-labelledby="usage-title">
                        <div class="pr-panel-head">
                            <div>
                                <h2 class="pr-panel-title" id="usage-title">Nama dipakai di kode</h2>
                                <span class="pr-panel-sub">{{ count($locations) }} lokasi</span>
                            </div>
                        </div>
                        @if ($byFile->isEmpty())
                            <p class="pr-empty-note">Nama role ini tidak ditemukan di kode. Aman diganti dari sisi kode.</p>
                        @else
                            <ul class="pr-locations">
                                @foreach ($byFile as $file => $items)
                                    @foreach ($items as $loc)
                                        <li>
                                            <span class="pr-loc-file">{{ $file }}:{{ $loc['line'] }}</span>
                                            <span class="pr-loc-text">{{ $loc['text'] }}</span>
                                        </li>
                                    @endforeach
                                @endforeach
                            </ul>
                        @endif
                    </section>
                </aside>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function() {
            const existing = new Set(@json($existingNames->map(fn($n) => mb_strtolower($n))->values()));
            const oldName = @json($role->name);
            const codeFiles = {{ $codeFiles }};
            const usersCount = {{ $usersCount }};
            const input = document.getElementById('name');
            const live = document.getElementById('name-live');
            const esc = v => $('<div>').text(v).html();

            function check() {
                const v = input.value.trim();
                live.className = 'pr-live';
                if (!v) { live.textContent = 'Nama role wajib diisi.'; live.classList.add('is-bad'); return false; }
                if (existing.has(v.toLowerCase())) { live.textContent = 'Nama ini sudah dipakai role lain.'; live.classList.add('is-bad'); return false; }
                live.textContent = v === oldName ? '' : 'Nama tersedia.';
                if (v !== oldName) live.classList.add('is-ok');
                return true;
            }
            input.addEventListener('input', check);

            document.getElementById('edit-btn').addEventListener('click', function(e) {
                e.preventDefault();
                if (!check()) { input.focus(); return; }
                const s = window.pkSummary || { total: 0, added: 0, removed: 0 };
                if (!s.total) {
                    Swal.fire({ icon: 'warning', title: 'Belum ada permission', text: 'Role harus punya minimal satu permission.' });
                    return;
                }
                const newName = input.value.trim();
                const lines = [];
                if (newName !== oldName) {
                    lines.push(`Nama: <code>${esc(oldName)}</code> menjadi <code>${esc(newName)}</code>`);
                    if (codeFiles) lines.push(`<b>Nama lama dipakai di ${codeFiles} file kode.</b>`);
                }
                if (s.added) lines.push(`${s.added} permission ditambah`);
                if (s.removed) lines.push(`${s.removed} permission dihapus`);
                if (!lines.length) lines.push('Tidak ada perubahan.');
                lines.push(`Berlaku untuk ${usersCount} user dengan role ini.`);
                Swal.fire({
                    title: 'Simpan perubahan role?',
                    html: lines.join('<br>'),
                    icon: (newName !== oldName && codeFiles) || s.removed ? 'warning' : 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, simpan',
                    cancelButtonText: 'Batal'
                }).then(r => { if (r.isConfirmed) document.getElementById('roles-edit').submit(); });
            });
        })();
    </script>
@endpush
