<style>
    /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand · redesign: full
     * paper: oklch(98.4% 0.004 265) · accent: oklch(61% 0.17 276) · display: Nunito · body: Nunito
     * nav: kept (Stisla shell) · signature: the employee directory the training team works from: each person beside how to reach them (tap-to-call number) and where they are placed (position over department and location), with employment type as one-tap filters so the On Job Training group is a click away · states: loading, empty (with reset), error (HTTP reason), no placement, no phone
     * axes: light/rounded-sans/chromatic-indigo · posture: app · contrast: pass · v2.0.0
 */
    /* Hallmark · dropped https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js, https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js, https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js, https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js, jqvmap.min.css · no button, chart or map on the page used them; export is server-side */
    /* Hallmark · pre-emit critique: P4 H4 E4 S4 R4 V4 · weakest: E, eight centred columns with placement mostly empty; answered by placement read from the employee when the pivot is missing, three grouped columns, type chips and a status default of Active */
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

    /* ── Jenis karyawan (chip) & filter ── */
    .et-flags-wrap { padding: var(--pr-space-sm) var(--pr-space-lg) 0; }
    .et-flags { display: flex; flex-wrap: wrap; gap: var(--pr-space-xs); }
    .et-flags button { min-height: 32px; padding: 0 var(--pr-space-sm); border: 1px solid var(--pr-rule); border-radius: var(--pr-pill); background: var(--pr-panel); font-family: var(--pr-font); font-size: var(--pr-text-sm); font-weight: 600; color: var(--pr-ink-2); cursor: pointer; transition: background-color var(--pr-dur-micro) var(--pr-ease), color var(--pr-dur-micro) var(--pr-ease), border-color var(--pr-dur-micro) var(--pr-ease); }
    .et-flags button:hover { color: var(--pr-ink); background: var(--pr-paper-2); }
    .et-flags button:active { background: var(--pr-rule); }
    .et-flags button[aria-pressed="true"] { border-color: var(--pr-accent); background: var(--pr-accent-soft); color: var(--pr-accent-deep); }

    .et-filters { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)) auto; gap: var(--pr-space-sm); align-items: end; padding: var(--pr-space-sm) var(--pr-space-lg) var(--pr-space-md); border-bottom: 1px solid var(--pr-rule); }
    .et-filter { min-width: 0; }
    .et-filter label { display: block; margin-bottom: var(--pr-space-2xs); font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-ink-2); }
    .et-filter select { box-sizing: border-box; width: 100%; height: 36px; padding: 0 var(--pr-space-sm); border: 1px solid var(--pr-field-edge); border-radius: var(--pr-radius-sm); background: var(--pr-panel); font-family: var(--pr-font); font-size: var(--pr-text-sm); color: var(--pr-ink); }
    .et-filters .select2-container { width: 100% !important; }
    .et-filters .select2-container .select2-selection--single { display: flex; align-items: center; height: 36px; border: 1px solid var(--pr-field-edge); border-radius: var(--pr-radius-sm); font-family: var(--pr-font); font-size: var(--pr-text-sm); }
    .et-filters .select2-container .select2-selection__rendered { color: var(--pr-ink); }
    .et-filters .select2-container .select2-selection__arrow { height: 34px; }
    .et-filters .select2-container--focus .select2-selection { outline: 2px solid var(--pr-focus); outline-offset: 0; }
    .et-filter.is-set label { color: var(--pr-accent-deep); }

    /* ── Tabel ── */
    .pr-table-wrap { padding: var(--pr-space-xs) var(--pr-space-lg) var(--pr-space-md); overflow-x: auto; }
    #employees-table { width: 100% !important; border-collapse: collapse; font-size: var(--pr-text-sm); }
    #employees-table thead th { padding: var(--pr-space-sm) var(--pr-space-xs); border: 0; border-bottom: 1px solid var(--pr-rule); background: var(--pr-panel); font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-ink-2); text-align: left; white-space: nowrap; }
    #employees-table thead th.sorting, #employees-table thead th.sorting_asc, #employees-table thead th.sorting_desc { padding-right: var(--pr-space-lg); }
    #employees-table tbody td { padding: var(--pr-space-sm) var(--pr-space-xs); border: 0; border-bottom: 1px solid var(--pr-rule); vertical-align: top; color: var(--pr-ink); }
    #employees-table tbody tr:hover td { background: var(--pr-paper); }
    .pr-cell-name { display: block; font-weight: 700; overflow-wrap: anywhere; }
    .pr-cell-sub { display: block; font-size: var(--pr-text-xs); color: var(--pr-muted); overflow-wrap: anywhere; }
    .et-none { color: var(--pr-muted); font-weight: 600; }
    .et-phone { font-weight: 700; color: var(--pr-accent-strong); text-decoration: none; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .et-phone:hover { color: var(--pr-accent-deep); text-decoration: underline; }

    .et-badge { display: inline-block; padding: 2px var(--pr-space-xs); border-radius: var(--pr-radius-sm); font-size: var(--pr-text-xs); font-weight: 700; white-space: nowrap; background: var(--pr-paper-2); color: var(--pr-ink-2); }
    .et-badge.is-active { background: var(--pr-success-soft); color: var(--pr-success); }
    .et-badge.is-pending { background: var(--pr-warn-soft); color: var(--pr-warn); }
    .et-badge.is-moving { background: var(--pr-accent-soft); color: var(--pr-accent-deep); }
    .et-badge.is-ojt { background: var(--pr-accent-soft); color: var(--pr-accent-deep); }

    /* DataTables */
    .pr-dt-bottom { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: var(--pr-space-sm); padding-top: var(--pr-space-md); }
    .pr .dataTables_wrapper .dataTables_info, .pr .dataTables_wrapper .dataTables_paginate, .pr .dataTables_wrapper .dataTables_length { float: none; padding: 0; font-size: var(--pr-text-sm); color: var(--pr-ink-2); }
    .pr .dataTables_wrapper .dataTables_paginate .paginate_button { display: inline-flex; align-items: center; justify-content: center; min-width: 32px; min-height: 32px; margin: 0 0 0 var(--pr-space-2xs) !important; padding: 0 var(--pr-space-sm) !important; border: 1px solid var(--pr-rule) !important; border-radius: var(--pr-radius-sm); background: var(--pr-panel) !important; color: var(--pr-ink) !important; font-weight: 600; }
    .pr .dataTables_wrapper .dataTables_paginate .paginate_button.current, .pr .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover { background: var(--pr-accent-soft) !important; border-color: var(--pr-accent) !important; color: var(--pr-accent-strong) !important; }
    .pr .dataTables_wrapper .dataTables_paginate .paginate_button.disabled, .pr .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover { opacity: .55; cursor: not-allowed; }
    .pr .dataTables_wrapper .dataTables_processing { background: var(--pr-panel); border: 1px solid var(--pr-rule); border-radius: var(--pr-radius-sm); color: var(--pr-ink-2); font-size: var(--pr-text-sm); }
    .pr .dataTables_wrapper .dataTables_length select { min-height: 32px; border: 1px solid var(--pr-field-edge); border-radius: var(--pr-radius-sm); font-family: var(--pr-font); }
    .et-nip { white-space: nowrap; overflow-wrap: normal; font-variant-numeric: tabular-nums; }
    .et-inline { display: none; }
    .pr .dataTables_wrapper .dataTables_paginate { display: flex; flex-wrap: wrap; align-items: center; gap: var(--pr-space-2xs); }
    .pr .dataTables_wrapper .dataTables_paginate > span { display: contents; }
    .pr .dataTables_wrapper .dataTables_paginate .ellipsis { padding: 0 var(--pr-space-2xs); color: var(--pr-muted); }
    .et-empty { padding: var(--pr-space-md) 0; color: var(--pr-ink-2); }
    .et-empty button { margin-left: var(--pr-space-xs); }

    .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }

    @media (max-width: 64rem) {
        .et-filters { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .tl-stats > div { border-right: 0; border-bottom: 1px solid var(--pr-rule); }
        .tl-stats > div:last-child { border-bottom: 0; }
        .pr-search, .pr-search input { width: 100%; }
    }
    /* Di HP: nama, kontak, dan penempatan tetap; perusahaan dan jenis disembunyikan */
    @media (max-width: 40rem) {
        .et-filters { grid-template-columns: minmax(0, 1fr); }
        #employees-table .et-col-opt { display: none; }
        .et-inline { display: flex; flex-wrap: wrap; align-items: center; gap: var(--pr-space-xs); margin-top: var(--pr-space-2xs); }
        .et-filter select, .pr-search input { font-size: 16px; }
    }

    @media (prefers-reduced-motion: reduce) {
        .pr *, .pr *::before, .pr *::after { transition-duration: 0ms !important; animation-duration: 0ms !important; }
    }
</style>
