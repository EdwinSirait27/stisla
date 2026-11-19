@extends('layouts.app')
@section('title', 'Tugas dan tanggung jawab')
@push('styles')
    <style>
        /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand · redesign: full
         * paper: oklch(98.4% 0.004 265) · accent: oklch(61% 0.17 276) · display: Nunito · body: Nunito
         * nav: kept (Stisla shell) · signature: what my job expects of me, and who I answer to · states: no position, position without description, multiple positions, no atasan, with team
         * axes: light/rounded-sans/chromatic-indigo · posture: app · contrast: pass · v2.0.0 */
        /* Hallmark · pre-emit critique: P4 H4 E4 S4 R4 V4 · weakest: E, 79 of 83 positions have no description yet; the empty state says who fills it */
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

        .pr-empty-note {
            margin: 0;
            padding: var(--pr-space-md) var(--pr-space-lg);
            font-size: var(--pr-text-sm);
            color: var(--pr-muted);
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

        /* ── Uraian jabatan ── */
        .pf-avatar {
            width: 64px;
            height: 64px;
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
            font-size: var(--pr-text-lg);
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

        .rnr-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 320px);
            gap: var(--pr-space-lg);
            align-items: start;
        }

        .rnr-main,
        .rnr-side {
            display: flex;
            flex-direction: column;
            gap: var(--pr-space-lg);
            min-width: 0;
        }

        .rnr-main .pr-panel,
        .rnr-side .pr-panel {
            margin-bottom: 0;
        }

        .rnr-h {
            margin: 0 0 var(--pr-space-xs);
            font-size: var(--pr-text-sm);
            font-weight: 800;
            color: var(--pr-ink);
        }

        .rnr-summary {
            padding: var(--pr-space-md) var(--pr-space-lg);
            border-bottom: 1px solid var(--pr-rule);
        }

        .rnr-summary p {
            max-width: 72ch;
            margin: 0;
            font-size: var(--pr-text-md);
            line-height: 1.65;
            color: var(--pr-ink);
            white-space: pre-line;
        }

        .rnr-cols {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .rnr-col {
            padding: var(--pr-space-md) var(--pr-space-lg);
            min-width: 0;
        }

        .rnr-col + .rnr-col {
            border-left: 1px solid var(--pr-rule);
        }

        .rnr-list {
            margin: 0;
            padding-left: 1.4em;
            font-size: var(--pr-text-sm);
            line-height: 1.55;
            color: var(--pr-ink);
        }

        .rnr-list li {
            padding: 4px 0;
            overflow-wrap: break-word;
        }

        .rnr-list li::marker {
            font-weight: 700;
            color: var(--pr-accent-strong);
        }

        .rnr-list.is-check {
            list-style: none;
            padding-left: 0;
        }

        .rnr-list.is-check li {
            position: relative;
            padding-left: 1.5em;
        }

        .rnr-list.is-check li::before {
            content: "\2713";
            position: absolute;
            left: 0;
            font-weight: 800;
            color: var(--pr-success);
        }

        .rnr-none {
            margin: 0;
            font-size: var(--pr-text-sm);
            color: var(--pr-muted);
        }

        .rnr-person {
            padding: var(--pr-space-sm) var(--pr-space-lg);
            border-top: 1px solid var(--pr-rule);
        }

        .rnr-person:first-of-type {
            border-top: 0;
        }

        .rnr-team {
            max-height: 360px;
            overflow-y: auto;
        }

        .rnr-person-name {
            display: block;
            font-size: var(--pr-text-sm);
            font-weight: 700;
            color: var(--pr-ink);
        }

        .rnr-person-sub {
            display: block;
            font-size: var(--pr-text-xs);
            color: var(--pr-muted);
        }

        .rnr-main .pr-empty-note,
        .rnr-side .pr-empty-note {
            line-height: 1.55;
        }

        .rnr-foot {
            margin: 0;
            font-size: var(--pr-text-xs);
            line-height: 1.5;
            color: var(--pr-ink-2);
        }

        .empty-state {
            padding: var(--pr-space-lg);
        }

        .empty-state p {
            max-width: 56ch;
            margin: var(--pr-space-xs) 0 0;
            font-size: var(--pr-text-sm);
            color: var(--pr-ink-2);
        }

        @media (max-width: 64rem) {
            .rnr-grid {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        @media (max-width: 40rem) {
            .rnr-cols {
                grid-template-columns: minmax(0, 1fr);
            }

            .rnr-col + .rnr-col {
                border-left: 0;
                border-top: 1px solid var(--pr-rule);
            }

            .rnr-summary,
            .rnr-col,
            .rnr-person {
                padding-left: var(--pr-space-md);
                padding-right: var(--pr-space-md);
            }
        }
    </style>
@endpush

@section('main')
    @php
        $primary = fn($rel) => $rel?->firstWhere('pivot.is_primary', true) ?? $rel?->first();
        $mainPos = $positions->first();
        $dept = $primary($employee?->department);
        $store = $primary($employee?->store);
        $filled = $positions->filter(fn($p) => $p->role_summary || $p->responsibilities->isNotEmpty() || $p->qualifications->isNotEmpty())->count();
        $atasan = $employee?->atasanList ?? collect();
        $bawahan = $employee?->bawahanList ?? collect();
    @endphp
    <div class="main-content pr">
        <section class="section">

            <header class="pr-head">
                <div class="pr-hello">
                    @if (!empty($employee?->photos))
                        <img src="{{ route('useremployee.photo', basename($employee->photos)) }}" alt="" class="pf-avatar no-drag" width="64" height="64">
                    @else
                        <span class="pf-avatar pf-initials" aria-hidden="true">
                            {{ collect(explode(' ', $employee->employee_name ?? ($user->username ?? 'U')))->filter()->take(2)->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') }}
                        </span>
                    @endif
                    <div>
                        <p class="pr-hello-date">Tugas dan tanggung jawab</p>
                        <h1 class="pr-title">{{ $mainPos?->name ?? 'Jabatan belum diatur' }}</h1>
                        <p class="pr-lede">
                            {{ collect([$employee?->employee_name, $dept?->department_name, $store?->name, $employee?->company?->name])->filter()->implode(', ') }}
                        </p>
                        <div class="pf-chips">
                            @if ($employee?->employee_pengenal)<span class="pf-chip">NIP {{ $employee->employee_pengenal }}</span>@endif
                            @if ($employee?->status_employee)<span class="pf-chip">{{ $employee->status_employee }}</span>@endif
                            @if ($employee?->grading?->grading_name)<span class="pf-chip">Grading {{ $employee->grading->grading_name }}</span>@endif
                            @if ($positions->count() > 1)<span class="pf-chip">{{ $positions->count() }} jabatan</span>@endif
                        </div>
                    </div>
                </div>
            </header>

            <div class="rnr-grid">
                {{-- Uraian jabatan (signature: apa yang diharapkan dari saya) --}}
                <div class="rnr-main">
                    @forelse ($positions as $position)
                        @php
                            $resp = $position->responsibilities;
                            $qual = $position->qualifications;
                            $empty = !$position->role_summary && $resp->isEmpty() && $qual->isEmpty();
                        @endphp
                        <section class="pr-panel" aria-labelledby="pos-{{ $position->id }}">
                            <div class="pr-panel-head">
                                <div>
                                    <h2 class="pr-panel-title" id="pos-{{ $position->id }}">{{ $position->name }}</h2>
                                    <span class="pr-panel-sub">
                                        {{ $empty ? 'Uraian jabatan belum diisi HR' : $resp->count() . ' tanggung jawab utama, ' . $qual->count() . ' kualifikasi' }}
                                    </span>
                                </div>
                                @if ($position->pivot->is_primary)
                                    <span class="status-badge is-approved">Jabatan utama</span>
                                @else
                                    <span class="status-badge is-draft">Jabatan tambahan</span>
                                @endif
                            </div>

                            @if ($empty)
                                <p class="pr-empty-note">
                                    HR belum menuliskan ringkasan peran, tanggung jawab, dan kualifikasi untuk jabatan ini.
                                    Tanyakan ke atasan langsung atau HR kalau ada yang ingin Anda pastikan tentang tugas Anda.
                                </p>
                            @else
                                @if ($position->role_summary)
                                    <div class="rnr-summary">
                                        <h3 class="rnr-h">Ringkasan peran</h3>
                                        <p>{{ $position->role_summary }}</p>
                                    </div>
                                @endif
                                <div class="rnr-cols">
                                    <div class="rnr-col">
                                        <h3 class="rnr-h">Tanggung jawab utama</h3>
                                        @if ($resp->isEmpty())
                                            <p class="rnr-none">Belum diisi.</p>
                                        @else
                                            <ol class="rnr-list">
                                                @foreach ($resp as $item)
                                                    <li>{{ $item->description }}</li>
                                                @endforeach
                                            </ol>
                                        @endif
                                    </div>
                                    <div class="rnr-col">
                                        <h3 class="rnr-h">Kualifikasi</h3>
                                        @if ($qual->isEmpty())
                                            <p class="rnr-none">Belum diisi.</p>
                                        @else
                                            <ul class="rnr-list is-check">
                                                @foreach ($qual as $item)
                                                    <li>{{ $item->description }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </section>
                    @empty
                        <section class="pr-panel">
                            <div class="empty-state">
                                <h2 class="pr-panel-title">Belum ada jabatan</h2>
                                <p>Akun Anda belum punya jabatan di data karyawan, jadi uraian tugas belum bisa ditampilkan. Hubungi HR untuk mengaturnya.</p>
                            </div>
                        </section>
                    @endforelse
                </div>

                {{-- Posisi di struktur --}}
                <aside class="rnr-side">
                    <section class="pr-panel" aria-labelledby="rnr-atasan">
                        <div class="pr-panel-head">
                            <div>
                                <h2 class="pr-panel-title" id="rnr-atasan">Atasan langsung</h2>
                                <span class="pr-panel-sub">Yang menyetujui cuti dan lembur Anda</span>
                            </div>
                        </div>
                        @forelse ($atasan as $a)
                            <div class="rnr-person">
                                <span class="rnr-person-name">{{ $a->employee_name }}</span>
                                <span class="rnr-person-sub">{{ $a->position->first()?->name ?? '-' }}</span>
                            </div>
                        @empty
                            <p class="pr-empty-note">Belum diatur. Tanpa atasan, pengajuan cuti Anda tidak punya penyetuju. Hubungi HR.</p>
                        @endforelse
                    </section>

                    <section class="pr-panel" aria-labelledby="rnr-tim">
                        <div class="pr-panel-head">
                            <div>
                                <h2 class="pr-panel-title" id="rnr-tim">Bawahan langsung</h2>
                                <span class="pr-panel-sub">{{ $bawahan->count() ? $bawahan->count() . ' orang' : 'Tidak ada' }}</span>
                            </div>
                        </div>
                        @if ($bawahan->isNotEmpty())
                            <div class="rnr-team">
                                @foreach ($bawahan as $b)
                                    <div class="rnr-person">
                                        <span class="rnr-person-name">{{ $b->employee_name }}</span>
                                        <span class="rnr-person-sub">{{ $b->position->first()?->name ?? '-' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="pr-empty-note">Tidak ada karyawan yang tercatat melapor langsung ke Anda.</p>
                        @endif
                    </section>

                    @if ($filled < $positions->count())
                        <p class="rnr-foot">
                            Uraian jabatan diisi HR lewat menu Position. Kalau isinya sudah tidak sesuai dengan pekerjaan Anda sekarang, sampaikan ke HR.
                        </p>
                    @endif
                </aside>
            </div>
        </section>
    </div>
@endsection
