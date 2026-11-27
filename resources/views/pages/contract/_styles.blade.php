<style>
    /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand · redesign: full
     * paper: oklch(98.4% 0.004 265) · accent: oklch(61% 0.17 276) · display: Nunito · body: Nunito
     * nav: kept (Stisla shell) · signature: every contract beside how long it has left, with contracts ending within 30 days and active employees without a contract one tap away; the form starts from the employee's current placement and latest pay · states: loading, empty, error, active, ending soon, expired, terminated, no document, password confirm, validation errors
     * axes: light/rounded-sans/chromatic-indigo · posture: app · contrast: pass · v2.0.0 */
    /* Hallmark · pre-emit critique: P4 H4 E4 S4 R4 V4 · weakest: E, the list never loaded and showed structure-derived placement; answered by placement from the contract (fallback employee), days left, and an ending-soon filter */
    /* Hallmark · dropped the activity table and twelve filter selects from the list page · history moved to each contract's detail page; filters cut to type, company, department, location */
    :root {
        --pr-paper: oklch(98.4% 0.004 265);
        --pr-panel: oklch(100% 0 0);
        --pr-paper-2: oklch(96.6% 0.006 265);
        --pr-ink: oklch(27% 0.03 265);
        --pr-ink-2: oklch(42% 0.03 265);
        --pr-muted: oklch(55% 0.025 265);
        --pr-rule: oklch(91.5% 0.008 265);
        --pr-field-edge: oklch(62% 0.02 265);
        --pr-accent: oklch(61% 0.17 276);
        --pr-accent-strong: oklch(52% 0.18 276);
        --pr-accent-deep: oklch(45% 0.17 276);
        --pr-accent-soft: oklch(95% 0.03 276);
        --pr-accent-ink: oklch(100% 0 0);
        --pr-focus: oklch(61% 0.17 276);
        --pr-success: oklch(45% 0.12 150);
        --pr-success-soft: oklch(96% 0.04 150);
        --pr-warn: oklch(50% 0.12 70);
        --pr-warn-soft: oklch(96.5% 0.045 85);
        --pr-error: oklch(52% 0.19 27);
        --pr-error-soft: oklch(96.5% 0.025 27);
        --pr-font: "Nunito", "Segoe UI", system-ui, sans-serif;

        --pr-text-xs: 0.75rem;
        --pr-text-sm: 0.8125rem;
        --pr-text-md: 0.9375rem;
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

    html, body { overflow-x: clip; }

    .pr { font-family: var(--pr-font); color: var(--pr-ink); }
    .pr [hidden] { display: none !important; }
    .pr :focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 2px; }

    /* ── Kepala halaman ── */
    .pr-head {
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;
        gap: var(--pr-space-md); margin-bottom: var(--pr-space-lg); padding: var(--pr-space-md) var(--pr-space-lg);
        background: var(--pr-panel); border: 1px solid var(--pr-rule); border-radius: var(--pr-radius);
    }
    .pr-head > div { min-width: 0; }
    .pr-crumbs { display: flex; flex-wrap: wrap; gap: var(--pr-space-xs); margin: 0 0 var(--pr-space-2xs); padding: 0; list-style: none; font-size: var(--pr-text-sm); color: var(--pr-muted); }
    .pr-crumbs li + li::before { content: "/"; margin-right: var(--pr-space-xs); color: var(--pr-rule); }
    .pr-title { margin: 0; font-size: var(--pr-text-2xl); font-weight: 700; line-height: 1.15; color: var(--pr-ink); overflow-wrap: break-word; min-width: 0; text-wrap: balance; }
    .pr-lede { margin: var(--pr-space-xs) 0 0; max-width: 72ch; font-size: var(--pr-text-sm); line-height: 1.5; color: var(--pr-ink-2); }
    .pr-actions { display: flex; flex-wrap: wrap; gap: var(--pr-space-xs); }

    /* ── Tombol ── */
    .pr-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: var(--pr-space-xs);
        min-height: 36px; padding: 0 var(--pr-space-md); border: 1px solid var(--pr-rule); border-radius: var(--pr-radius-sm);
        background: var(--pr-panel); color: var(--pr-ink); font-family: var(--pr-font); font-size: var(--pr-text-sm); font-weight: 600;
        line-height: 1; text-decoration: none; white-space: nowrap; cursor: pointer;
        transition: background-color var(--pr-dur-micro) var(--pr-ease), border-color var(--pr-dur-micro) var(--pr-ease), color var(--pr-dur-micro) var(--pr-ease);
    }
    .pr-btn:hover { background: var(--pr-paper-2); color: var(--pr-ink); text-decoration: none; }
    .pr-btn:active { background: var(--pr-rule); }
    .pr-btn:disabled, .pr-btn[aria-disabled="true"] { opacity: .55; cursor: not-allowed; }
    .pr-btn i { font-size: .85em; }
    .pr-btn-primary { background: var(--pr-accent-strong); border-color: var(--pr-accent-strong); color: var(--pr-accent-ink); }
    .pr-btn-primary:hover, .pr-btn-primary:active { background: var(--pr-accent-deep); border-color: var(--pr-accent-deep); color: var(--pr-accent-ink); }
    .pr-btn-sm { min-height: 32px; padding: 0 var(--pr-space-sm); font-size: var(--pr-text-xs); }

    /* ── Panel ── */
    .pr-panel { margin-bottom: var(--pr-space-lg); background: var(--pr-panel); border: 1px solid var(--pr-rule); border-radius: var(--pr-radius); }
    .pr-panel-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: var(--pr-space-sm); padding: var(--pr-space-md) var(--pr-space-lg); border-bottom: 1px solid var(--pr-rule); }
    .pr-panel-title { margin: 0; font-size: var(--pr-text-md); font-weight: 700; color: var(--pr-ink); }
    .pr-panel-sub { display: block; font-size: var(--pr-text-sm); color: var(--pr-muted); }

    /* ── Ringkasan ── */
    .tl-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 190px), 1fr)); margin: 0; }
    .tl-stats > div { padding: var(--pr-space-md) var(--pr-space-lg); border-right: 1px solid var(--pr-rule); }
    .tl-stats > div:last-child { border-right: 0; }
    .tl-stats dt { font-size: var(--pr-text-sm); font-weight: 600; color: var(--pr-ink-2); }
    .tl-stats dd { margin: 2px 0 0; font-size: var(--pr-text-xl); font-weight: 800; font-variant-numeric: tabular-nums; color: var(--pr-ink); }
    .tl-stats > div > span { display: block; font-size: var(--pr-text-xs); line-height: 1.45; color: var(--pr-ink-2); }

    /* ── Pencarian ── */
    .pr-search { position: relative; display: inline-flex; align-items: center; }
    .pr-search i { position: absolute; left: var(--pr-space-sm); font-size: var(--pr-text-xs); color: var(--pr-muted); pointer-events: none; }
    .pr-search input { box-sizing: border-box; width: 260px; max-width: 100%; height: 36px; padding: 0 var(--pr-space-sm) 0 32px; border: 1px solid var(--pr-field-edge); border-radius: var(--pr-radius-sm); background: var(--pr-panel); font-family: var(--pr-font); font-size: var(--pr-text-sm); color: var(--pr-ink); }
    .pr-search input:focus-visible { outline-offset: -1px; }


    /* ── Chip & filter ── */
    .sk-flags { display: flex; flex-wrap: wrap; gap: var(--pr-space-xs); padding: var(--pr-space-sm) var(--pr-space-lg) 0; }
    .sk-flags button { min-height: 32px; padding: 0 var(--pr-space-sm); border: 1px solid var(--pr-rule); border-radius: var(--pr-pill); background: var(--pr-panel); font-family: var(--pr-font); font-size: var(--pr-text-sm); font-weight: 600; color: var(--pr-ink-2); cursor: pointer; transition: background-color var(--pr-dur-micro) var(--pr-ease), color var(--pr-dur-micro) var(--pr-ease), border-color var(--pr-dur-micro) var(--pr-ease); }
    .sk-flags button:hover { color: var(--pr-ink); background: var(--pr-paper-2); }
    .sk-flags button[aria-pressed="true"] { border-color: var(--pr-accent); background: var(--pr-accent-soft); color: var(--pr-accent-deep); }
    .sk-flags button b { margin-left: var(--pr-space-2xs); font-variant-numeric: tabular-nums; }
    .sk-filters { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)) auto; gap: var(--pr-space-sm); align-items: end; padding: var(--pr-space-sm) var(--pr-space-lg) var(--pr-space-md); border-bottom: 1px solid var(--pr-rule); }
    .sk-filters label { display: block; margin-bottom: var(--pr-space-2xs); font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-ink-2); }
    .sk-filters select, .sk-filters input { box-sizing: border-box; width: 100%; height: 36px; padding: 0 var(--pr-space-sm); border: 1px solid var(--pr-field-edge); border-radius: var(--pr-radius-sm); background: var(--pr-panel); font-family: var(--pr-font); font-size: var(--pr-text-sm); color: var(--pr-ink); }

    .pr-table-wrap { padding: var(--pr-space-xs) var(--pr-space-lg) var(--pr-space-md); overflow-x: auto; }
    .pr-cell-name { display: block; font-weight: 700; overflow-wrap: anywhere; }
    .pr-cell-sub { display: block; font-size: var(--pr-text-xs); color: var(--pr-muted); overflow-wrap: anywhere; }

    /* DataTables */
    .pr-dt-bottom { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: var(--pr-space-sm); padding-top: var(--pr-space-md); }
    .pr .dataTables_wrapper .dataTables_info, .pr .dataTables_wrapper .dataTables_paginate, .pr .dataTables_wrapper .dataTables_length { float: none; padding: 0; font-size: var(--pr-text-sm); color: var(--pr-ink-2); }
    .pr .dataTables_wrapper .dataTables_paginate .paginate_button { display: inline-flex; align-items: center; justify-content: center; min-width: 32px; min-height: 32px; margin: 0 0 0 var(--pr-space-2xs) !important; padding: 0 var(--pr-space-sm) !important; border: 1px solid var(--pr-rule) !important; border-radius: var(--pr-radius-sm); background: var(--pr-panel) !important; color: var(--pr-ink) !important; font-weight: 600; }
    .pr .dataTables_wrapper .dataTables_paginate .paginate_button.current, .pr .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover { background: var(--pr-accent-soft) !important; border-color: var(--pr-accent) !important; color: var(--pr-accent-strong) !important; }
    .pr .dataTables_wrapper .dataTables_paginate .paginate_button.disabled, .pr .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover { opacity: .55; cursor: not-allowed; }
    .pr .dataTables_wrapper .dataTables_processing { background: var(--pr-panel); border: 1px solid var(--pr-rule); border-radius: var(--pr-radius-sm); color: var(--pr-ink-2); font-size: var(--pr-text-sm); }
    .pr .dataTables_wrapper .dataTables_length select { min-height: 32px; border: 1px solid var(--pr-field-edge); border-radius: var(--pr-radius-sm); font-family: var(--pr-font); }
    .pr .dataTables_wrapper .dataTables_paginate { display: flex; flex-wrap: wrap; align-items: center; gap: var(--pr-space-2xs); }
    .pr .dataTables_wrapper .dataTables_paginate > span { display: contents; }
    .pr .dataTables_wrapper .dataTables_paginate .ellipsis { padding: 0 var(--pr-space-2xs); color: var(--pr-muted); }

    /* ── Tabel daftar ── */
    #contract-table { width: 100% !important; border-collapse: collapse; font-size: var(--pr-text-sm); }
    #contract-table thead th { padding: var(--pr-space-sm) var(--pr-space-xs); border: 0; border-bottom: 1px solid var(--pr-rule); background: var(--pr-panel); font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-ink-2); text-align: left; white-space: nowrap; }
    #contract-table tbody td { padding: var(--pr-space-sm) var(--pr-space-xs); border: 0; border-bottom: 1px solid var(--pr-rule); vertical-align: top; color: var(--pr-ink); }
    #contract-table tbody tr:hover td { background: var(--pr-paper); }
    #contract-table tbody tr.is-turn td:first-child { box-shadow: inset 3px 0 0 var(--pr-warn); }
    .sk-names { display: block; max-width: 42ch; font-size: var(--pr-text-xs); color: var(--pr-ink-2); overflow-wrap: anywhere; }
    .sk-badge { display: inline-block; padding: 2px var(--pr-space-xs); border-radius: var(--pr-radius-sm); font-size: var(--pr-text-xs); font-weight: 700; background: var(--pr-paper-2); color: var(--pr-ink-2); }
    .sk-badge.is-draft { background: var(--pr-paper-2); color: var(--pr-ink-2); }
    .sk-badge.is-returned { background: var(--pr-warn-soft); color: var(--pr-warn); }
    .sk-badge.is-waiting { background: var(--pr-accent-soft); color: var(--pr-accent-deep); }
    .sk-badge.is-approved { background: var(--pr-success-soft); color: var(--pr-success); }
    .sk-badge.is-cancelled { background: var(--pr-error-soft); color: var(--pr-error); }
    .sk-turn { display: block; margin-top: var(--pr-space-2xs); font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-warn); }
    .sk-row-actions { display: flex; gap: var(--pr-space-2xs); justify-content: flex-end; }

    /* ── Form ── */
    .emp-fields { display: grid; gap: var(--pr-space-md); padding: var(--pr-space-md) var(--pr-space-lg) var(--pr-space-lg); }
    .sk-fields-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .sk-span-2 { grid-column: span 2; }
    .sk-span-all { grid-column: 1 / -1; }
    .emp-field { min-width: 0; }
    .emp-field > label { display: block; margin-bottom: var(--pr-space-2xs); font-size: var(--pr-text-sm); font-weight: 700; color: var(--pr-ink); }
    .emp-req { color: var(--pr-error); }
    .emp-input { box-sizing: border-box; width: 100%; height: 40px; padding: 0 var(--pr-space-sm); border: 1px solid var(--pr-field-edge); border-radius: var(--pr-radius-sm); background: var(--pr-panel); font-family: var(--pr-font); font-size: var(--pr-text-md); color: var(--pr-ink); }
    textarea.emp-input { height: auto; min-height: 88px; padding: var(--pr-space-xs) var(--pr-space-sm); line-height: 1.5; resize: vertical; }
    .emp-input:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 0; }
    .emp-input.is-invalid { border-color: var(--pr-error); }
    .emp-help { display: block; margin-top: var(--pr-space-2xs); font-size: var(--pr-text-xs); line-height: 1.45; color: var(--pr-muted); }
    .emp-errors { margin: 0 0 var(--pr-space-lg); padding: var(--pr-space-sm) var(--pr-space-md); border-radius: var(--pr-radius); background: var(--pr-error-soft); font-size: var(--pr-text-sm); color: var(--pr-ink); }
    .emp-errors ul { margin: var(--pr-space-2xs) 0 0; padding-left: 1.2em; }
    .sk-money { font-variant-numeric: tabular-nums; }
    .sk-effects { margin: 0; padding: var(--pr-space-xs) var(--pr-space-sm); border-radius: var(--pr-radius-sm); background: var(--pr-accent-soft); font-size: var(--pr-text-sm); line-height: 1.5; color: var(--pr-ink); }
    .sk-savebar { position: sticky; bottom: 0; z-index: 5; display: flex; align-items: center; justify-content: flex-end; flex-wrap: wrap; gap: var(--pr-space-xs); padding: var(--pr-space-sm) var(--pr-space-lg); border: 1px solid var(--pr-rule); border-radius: var(--pr-radius); background: var(--pr-panel); }
    .sk-savebar .sk-savebar-note { margin-right: auto; font-size: var(--pr-text-sm); color: var(--pr-ink-2); }
    .pr .select2-container { width: 100% !important; }
    .pr .select2-container .select2-selection--single { display: flex; align-items: center; height: 40px; border: 1px solid var(--pr-field-edge); border-radius: var(--pr-radius-sm); font-family: var(--pr-font); font-size: var(--pr-text-md); }
    .pr .select2-container .select2-selection__arrow { height: 38px; }
    .pr .select2-container--focus .select2-selection { outline: 2px solid var(--pr-focus); outline-offset: 0; }

    /* Karyawan dalam SK */
    .sk-people { margin: 0; padding: 0; list-style: none; }
    .sk-person { padding: var(--pr-space-md) var(--pr-space-lg); border-bottom: 1px solid var(--pr-rule); }
    .sk-person:last-child { border-bottom: 0; }
    .sk-person-head { display: flex; align-items: flex-start; justify-content: space-between; gap: var(--pr-space-sm); margin-bottom: var(--pr-space-sm); }
    .sk-person-head strong { display: block; font-size: var(--pr-text-md); }
    .sk-person-now { display: block; font-size: var(--pr-text-xs); color: var(--pr-ink-2); }
    .sk-person .emp-fields { padding: 0; grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .sk-group-title { grid-column: 1 / -1; margin: var(--pr-space-2xs) 0 0; font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-ink-2); }
    .sk-empty { margin: 0; padding: var(--pr-space-md) var(--pr-space-lg); font-size: var(--pr-text-sm); color: var(--pr-muted); }
    .sk-add { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: var(--pr-space-sm); align-items: end; padding: var(--pr-space-md) var(--pr-space-lg); border-bottom: 1px solid var(--pr-rule); }

    /* Pasal: menimbang / mengingat / keputusan */
    .sk-clauses { margin: 0; padding: 0; list-style: none; display: grid; gap: var(--pr-space-xs); }
    .sk-clause { display: grid; grid-template-columns: 2.2em minmax(0, 1fr) auto; gap: var(--pr-space-xs); align-items: start; }
    .sk-clause-num { padding-top: var(--pr-space-xs); font-weight: 700; font-variant-numeric: tabular-nums; color: var(--pr-ink-2); text-align: right; }
    .sk-clause textarea { min-height: 44px; }
    .sk-icon { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border: 1px solid var(--pr-rule); border-radius: var(--pr-radius-sm); background: var(--pr-panel); color: var(--pr-ink-2); cursor: pointer; transition: background-color var(--pr-dur-micro) var(--pr-ease), color var(--pr-dur-micro) var(--pr-ease); }
    .sk-icon:hover { background: var(--pr-error-soft); color: var(--pr-error); }

    /* ── Detail ── */
    .sk-layout { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 340px); gap: var(--pr-space-lg); align-items: start; }
    .sk-layout > div > .pr-panel:last-child { margin-bottom: 0; }
    .sk-meta { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0; margin: 0; }
    .sk-meta > div { padding: var(--pr-space-sm) var(--pr-space-lg); border-bottom: 1px solid var(--pr-rule); }
    .sk-meta dt { font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-ink-2); }
    .sk-meta dd { margin: 2px 0 0; font-size: var(--pr-text-sm); color: var(--pr-ink); overflow-wrap: anywhere; }
    .sk-change { display: grid; grid-template-columns: 8.5em minmax(0, 1fr); gap: var(--pr-space-2xs) var(--pr-space-sm); margin: 0; font-size: var(--pr-text-sm); }
    .sk-change dt { color: var(--pr-ink-2); font-weight: 600; }
    .sk-change dd { margin: 0; overflow-wrap: anywhere; }
    .sk-change .from { color: var(--pr-muted); }
    .sk-applied { display: inline-block; margin-top: var(--pr-space-xs); font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-success); }
    .sk-applied.is-later { color: var(--pr-warn); }
    .sk-text { padding: var(--pr-space-md) var(--pr-space-lg); font-size: var(--pr-text-sm); line-height: 1.6; }
    .sk-text h3 { margin: var(--pr-space-sm) 0 var(--pr-space-2xs); font-size: var(--pr-text-sm); font-weight: 700; color: var(--pr-ink-2); }
    .sk-text h3:first-child { margin-top: 0; }
    .sk-text ol { margin: 0; padding-left: 1.4em; }
    .sk-steps { margin: 0; padding: var(--pr-space-sm) var(--pr-space-lg) var(--pr-space-md); list-style: none; display: grid; gap: var(--pr-space-sm); }
    .sk-step { display: grid; grid-template-columns: 28px minmax(0, 1fr); gap: var(--pr-space-sm); }
    .sk-step-num { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: var(--pr-pill); background: var(--pr-paper-2); font-size: var(--pr-text-xs); font-weight: 800; color: var(--pr-ink-2); }
    .sk-step.is-done .sk-step-num { background: var(--pr-success-soft); color: var(--pr-success); }
    .sk-step.is-now .sk-step-num { background: var(--pr-warn-soft); color: var(--pr-warn); }
    .sk-step strong { display: block; font-size: var(--pr-text-sm); }
    .sk-step span { display: block; font-size: var(--pr-text-xs); line-height: 1.45; color: var(--pr-ink-2); }
    .sk-step strong { line-height: 1.3; }
    .sk-notice { margin: 0 0 var(--pr-space-lg); padding: var(--pr-space-sm) var(--pr-space-md); border-radius: var(--pr-radius); font-size: var(--pr-text-sm); line-height: 1.5; color: var(--pr-ink); }
    .sk-notice.is-warn { background: var(--pr-warn-soft); }
    .sk-notice.is-info { background: var(--pr-accent-soft); }
    .sk-notice.is-ok { background: var(--pr-success-soft); }
    .sk-decide { display: grid; gap: var(--pr-space-sm); padding: var(--pr-space-md) var(--pr-space-lg); }
    .sk-log { margin: 0; padding: var(--pr-space-sm) var(--pr-space-lg) var(--pr-space-md); list-style: none; display: grid; gap: var(--pr-space-sm); font-size: var(--pr-text-xs); line-height: 1.45; color: var(--pr-ink-2); }
    .sk-log time { display: block; color: var(--pr-muted); }
    .pr-btn-danger { color: var(--pr-error); }
    .pr-btn-danger:hover { background: var(--pr-error-soft); color: var(--pr-error); }
    .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }

    @media (max-width: 64rem) {
        .sk-layout, .sk-filters, .sk-fields-3, .sk-person .emp-fields { grid-template-columns: minmax(0, 1fr); }
        .sk-span-2 { grid-column: auto; }
        .tl-stats > div { border-right: 0; border-bottom: 1px solid var(--pr-rule); }
        .tl-stats > div:last-child { border-bottom: 0; }
        .pr-search, .pr-search input { width: 100%; }
        .emp-input, .sk-filters select, .sk-filters input { font-size: 16px; }
    }
    @media (max-width: 40rem) { #contract-table .sk-col-opt { display: none; } .sk-meta { grid-template-columns: minmax(0, 1fr); } }
    @media (prefers-reduced-motion: reduce) {
        .pr *, .pr *::before, .pr *::after { transition-duration: 0ms !important; animation-duration: 0ms !important; }
    }
</style>

<style>
    #contract-table tbody tr.is-turn td:first-child { box-shadow: inset 3px 0 0 var(--pr-warn); }
    .ct-left { font-weight: 700; font-variant-numeric: tabular-nums; }
    .ct-left.is-soon { color: var(--pr-warn); }
    .ct-left.is-late { color: var(--pr-error); }
    .ct-pw { display: grid; grid-template-columns: minmax(0, 320px) auto; gap: var(--pr-space-sm); align-items: end; padding: var(--pr-space-md) var(--pr-space-lg); }
    @media (max-width: 40rem) { .ct-pw { grid-template-columns: minmax(0, 1fr); } }
</style>
