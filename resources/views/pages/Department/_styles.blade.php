<style>
    /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand · redesign: full
     * paper: oklch(98.4% 0.004 265) · accent: oklch(61% 0.17 276) · display: Nunito · body: Nunito
     * nav: kept (Stisla shell) · signature: each department beside who is in it (active headcount, its managers, the positions and locations it spans), with departments that have people but no manager surfaced as a filter; on the form, look-alike names and taken codes flagged as you type, and on edit the department's people shown beside the fields · states: loading, empty, error, no-permission, unused, no manager, no code, validation errors
     * axes: light/rounded-sans/chromatic-indigo · posture: app · contrast: pass · v2.0.0 */
    /* Hallmark · pre-emit critique: P4 H4 E4 S4 R4 V4 · weakest: E, the list showed only a name and a code; answered by headcount, managers, positions and locations per department plus quick filters */
    :root {
        --pr-paper: oklch(98.4% 0.004 265);
        --pr-panel: oklch(100% 0 0);
        --pr-paper-2: oklch(96.6% 0.006 265);
        --pr-ink: oklch(27% 0.03 265);
        --pr-ink-2: oklch(42% 0.03 265);
        --pr-muted: oklch(55% 0.025 265);
        --pr-rule: oklch(91.5% 0.008 265);
        --pr-field-edge: oklch(62% 0.02 265); /* tepi input, minimal 3:1 terhadap panel */
        --pr-accent: oklch(61% 0.17 276);
        --pr-accent-strong: oklch(52% 0.18 276);
        --pr-accent-deep: oklch(45% 0.17 276);
        --pr-accent-soft: oklch(95% 0.03 276);
        --pr-accent-ink: oklch(100% 0 0);
        --pr-focus: oklch(61% 0.17 276);
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

    html, body { overflow-x: clip; }

    .pr { font-family: var(--pr-font); color: var(--pr-ink); }
    .pr [hidden] { display: none !important; }
    .pr :focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 2px; }
    .pr .num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }

    /* ── Kepala halaman ── */
    .pr-head {
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;
        gap: var(--pr-space-md); margin-bottom: var(--pr-space-lg); padding: var(--pr-space-md) var(--pr-space-lg);
        background: var(--pr-panel); border: 1px solid var(--pr-rule); border-radius: var(--pr-radius);
    }
    .pr-head > div { min-width: 0; }
    .pr-crumbs { display: flex; flex-wrap: wrap; gap: var(--pr-space-xs); margin: 0 0 var(--pr-space-2xs); padding: 0; list-style: none; font-size: var(--pr-text-sm); color: var(--pr-muted); }
    .pr-crumbs li + li::before { content: "/"; margin-right: var(--pr-space-xs); color: var(--pr-rule); }
    .pr-crumbs a { color: var(--pr-ink-2); text-decoration: none; }
    .pr-crumbs a:hover { color: var(--pr-accent-strong); text-decoration: underline; }
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
    .pr-btn:disabled { opacity: .55; cursor: not-allowed; }
    .pr-btn i { font-size: .85em; }
    .pr-btn-primary { background: var(--pr-accent-strong); border-color: var(--pr-accent-strong); color: var(--pr-accent-ink); }
    .pr-btn-primary:hover, .pr-btn-primary:active { background: var(--pr-accent-deep); border-color: var(--pr-accent-deep); color: var(--pr-accent-ink); }
    .pr-btn-primary:disabled:hover { background: var(--pr-accent-strong); }
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
    .tl-stats > div.is-warn { background: var(--pr-warn-soft); }
    .tl-stats dt { font-size: var(--pr-text-sm); font-weight: 600; color: var(--pr-ink-2); }
    .tl-stats dd { margin: 2px 0 0; font-size: var(--pr-text-xl); font-weight: 800; font-variant-numeric: tabular-nums; color: var(--pr-ink); }
    .tl-stats > div > span { display: block; font-size: var(--pr-text-xs); line-height: 1.45; color: var(--pr-ink-2); }

    /* ── Pencarian ── */
    .pr-search { position: relative; display: inline-flex; align-items: center; }
    .pr-search i { position: absolute; left: var(--pr-space-sm); font-size: var(--pr-text-xs); color: var(--pr-muted); pointer-events: none; }
    .pr-search input { box-sizing: border-box; width: 240px; max-width: 100%; height: 36px; padding: 0 var(--pr-space-sm) 0 32px; border: 1px solid var(--pr-field-edge); border-radius: var(--pr-radius-sm); background: var(--pr-panel); font-family: var(--pr-font); font-size: var(--pr-text-sm); color: var(--pr-ink); }
    .pr-search input:focus-visible { outline-offset: -1px; }

    /* ── Tabel ── */
    .pr-table-wrap { padding: var(--pr-space-xs) var(--pr-space-lg) var(--pr-space-md); overflow-x: auto; }
    #department-table { width: 100% !important; border-collapse: collapse; font-size: var(--pr-text-sm); }
    #department-table thead th { padding: var(--pr-space-sm) var(--pr-space-xs); border: 0; border-bottom: 1px solid var(--pr-rule); background: var(--pr-panel); font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-ink-2); white-space: nowrap; }
    #department-table thead th.sorting, #department-table thead th.sorting_asc, #department-table thead th.sorting_desc { padding-right: var(--pr-space-lg); }
    #department-table tbody td { padding: var(--pr-space-sm) var(--pr-space-xs); border: 0; border-bottom: 1px solid var(--pr-rule); vertical-align: middle; color: var(--pr-ink); }
    #department-table tbody tr:hover td { background: var(--pr-paper); }
    .pr-cell-name { display: block; font-weight: 700; overflow-wrap: anywhere; }
    .pr-cell-sub { display: block; font-size: var(--pr-text-xs); color: var(--pr-muted); }
    .dp-count { font-weight: 700; font-variant-numeric: tabular-nums; }
    .dp-zero { color: var(--pr-muted); font-weight: 600; }

    /* Porsi karyawan aktif per bank */
    .dp-share { display: grid; grid-template-columns: minmax(0, 1fr) 4.5ch; align-items: center; gap: var(--pr-space-xs); min-width: 160px; }
    .dp-bar { height: 8px; border-radius: var(--pr-pill); background: var(--pr-paper-2); overflow: hidden; }
    .dp-bar > i { display: block; height: 100%; border-radius: inherit; background: var(--pr-accent); transform-origin: left center; }
    .dp-share span { font-size: var(--pr-text-xs); font-weight: 700; font-variant-numeric: tabular-nums; text-align: right; color: var(--pr-ink-2); }

    /* DataTables */
    .pr-dt-bottom { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: var(--pr-space-sm); padding-top: var(--pr-space-md); }
    .pr .dataTables_wrapper .dataTables_info, .pr .dataTables_wrapper .dataTables_paginate, .pr .dataTables_wrapper .dataTables_length { float: none; padding: 0; font-size: var(--pr-text-sm); color: var(--pr-ink-2); }
    .pr .dataTables_wrapper .dataTables_paginate .paginate_button { display: inline-flex; align-items: center; justify-content: center; min-width: 32px; min-height: 32px; margin: 0 0 0 var(--pr-space-2xs) !important; padding: 0 var(--pr-space-sm) !important; border: 1px solid var(--pr-rule) !important; border-radius: var(--pr-radius-sm); background: var(--pr-panel) !important; color: var(--pr-ink) !important; font-weight: 600; }
    .pr .dataTables_wrapper .dataTables_paginate .paginate_button.current, .pr .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover { background: var(--pr-accent-soft) !important; border-color: var(--pr-accent) !important; color: var(--pr-accent-strong) !important; }
    .pr .dataTables_wrapper .dataTables_paginate .paginate_button.disabled, .pr .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover { opacity: .55; cursor: not-allowed; }
    .pr .dataTables_wrapper .dataTables_processing { background: var(--pr-panel); border: 1px solid var(--pr-rule); border-radius: var(--pr-radius-sm); color: var(--pr-ink-2); font-size: var(--pr-text-sm); }
    .pr .dataTables_wrapper .dataTables_length select { min-height: 32px; border: 1px solid var(--pr-field-edge); border-radius: var(--pr-radius-sm); font-family: var(--pr-font); }

    /* ── Form ── */
    .dp-layout { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 320px); gap: var(--pr-space-lg); align-items: start; }
    .dp-layout > .pr-panel { margin-bottom: 0; }
    .emp-fields { display: grid; gap: var(--pr-space-md); padding: var(--pr-space-md) var(--pr-space-lg) var(--pr-space-lg); }
    .emp-field { min-width: 0; }
    .emp-field > label { display: block; margin-bottom: 4px; font-size: var(--pr-text-sm); font-weight: 700; color: var(--pr-ink); }
    .emp-req { color: var(--pr-error); }
    .emp-input { box-sizing: border-box; width: 100%; max-width: 480px; height: 40px; padding: 0 12px; border: 1px solid var(--pr-field-edge); border-radius: var(--pr-radius-sm); background: var(--pr-panel); font-family: var(--pr-font); font-size: var(--pr-text-md); color: var(--pr-ink); }
    .emp-input:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 0; }
    .emp-input.is-invalid { border-color: var(--pr-error); }
    .emp-help { display: block; margin-top: 4px; font-size: var(--pr-text-xs); line-height: 1.45; color: var(--pr-muted); }
    .emp-error { display: block; margin-top: 4px; font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-error); }
    .emp-errors { margin: 0 0 var(--pr-space-lg); padding: var(--pr-space-sm) var(--pr-space-md); border-radius: var(--pr-radius); background: var(--pr-error-soft); font-size: var(--pr-text-sm); color: var(--pr-ink); }
    .emp-errors ul { margin: 4px 0 0; padding-left: 1.2em; }

    .dp-similar { max-width: 480px; margin: var(--pr-space-xs) 0 0; padding: var(--pr-space-xs) var(--pr-space-sm); border-radius: var(--pr-radius-sm); background: var(--pr-warn-soft); font-size: var(--pr-text-sm); line-height: 1.45; color: var(--pr-ink); }
    .dp-similar strong { color: var(--pr-warn); }

    .dp-rules { margin: 0; padding: var(--pr-space-sm) var(--pr-space-lg) var(--pr-space-sm) calc(var(--pr-space-lg) + 1.1em); border-top: 1px solid var(--pr-rule); background: var(--pr-paper); font-size: var(--pr-text-sm); line-height: 1.55; color: var(--pr-ink-2); }

    .dp-savebar { display: flex; align-items: center; justify-content: flex-end; flex-wrap: wrap; gap: var(--pr-space-xs); padding: var(--pr-space-sm) var(--pr-space-lg); border-top: 1px solid var(--pr-rule); }

    /* Panel samping: bank terdaftar & pemakaian */
    .dp-usage { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); margin: 0; border-bottom: 1px solid var(--pr-rule); }
    .dp-usage > div { padding: var(--pr-space-sm) var(--pr-space-lg); }
    .dp-usage > div + div { border-left: 1px solid var(--pr-rule); }
    .dp-usage dt { font-size: var(--pr-text-xs); font-weight: 600; color: var(--pr-ink-2); }
    .dp-usage dd { margin: 2px 0 0; font-size: var(--pr-text-xl); font-weight: 800; font-variant-numeric: tabular-nums; }
    .dp-usage-note { margin: 0; padding: var(--pr-space-sm) var(--pr-space-lg); border-bottom: 1px solid var(--pr-rule); font-size: var(--pr-text-xs); line-height: 1.5; color: var(--pr-ink-2); }
    .dp-list { max-height: 360px; margin: 0; padding: var(--pr-space-xs) 0; overflow-y: auto; list-style: none; }
    .dp-list li { padding: var(--pr-space-2xs) var(--pr-space-lg); font-size: var(--pr-text-sm); color: var(--pr-ink); }
    .dp-list li.is-match { background: var(--pr-warn-soft); font-weight: 700; }
    .dp-list-empty { margin: 0; padding: var(--pr-space-md) var(--pr-space-lg); font-size: var(--pr-text-sm); color: var(--pr-muted); }

    /* ── Departemen ── */
    .dp-code { display: inline-block; margin-left: var(--pr-space-xs); padding: 0 var(--pr-space-xs); border: 1px solid var(--pr-rule); border-radius: var(--pr-radius-sm); background: var(--pr-paper); font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-ink-2); vertical-align: 1px; }
    .dp-code.is-missing { border-color: var(--pr-warn); background: var(--pr-warn-soft); color: var(--pr-warn); }
    .dp-warn { display: inline-block; padding: 2px var(--pr-space-xs); border-radius: var(--pr-radius-sm); background: var(--pr-warn-soft); font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-warn); }
    .dp-names { display: block; max-width: 36ch; overflow-wrap: anywhere; }
    #department-table td { vertical-align: top; }
    .dp-flags-wrap { padding: var(--pr-space-sm) var(--pr-space-lg); border-bottom: 1px solid var(--pr-rule); }
    .dp-flags { display: flex; flex-wrap: wrap; gap: var(--pr-space-xs); }
    .dp-flags button { min-height: 32px; padding: 0 var(--pr-space-sm); border: 1px solid var(--pr-rule); border-radius: var(--pr-pill); background: var(--pr-panel); font-family: var(--pr-font); font-size: var(--pr-text-sm); font-weight: 600; color: var(--pr-ink-2); cursor: pointer; transition: background-color var(--pr-dur-micro) var(--pr-ease), color var(--pr-dur-micro) var(--pr-ease), border-color var(--pr-dur-micro) var(--pr-ease); }
    .dp-flags button:hover { color: var(--pr-ink); background: var(--pr-paper-2); }
    .dp-flags button:active { background: var(--pr-rule); }
    .dp-flags button[aria-pressed="true"] { border-color: var(--pr-accent); background: var(--pr-accent-soft); color: var(--pr-accent-deep); }
    .dp-fields-2 { grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); }
    .dp-fields-2 .emp-input { max-width: none; }
    .dp-code-input { font-weight: 700; }
    .dp-code-input::placeholder { font-weight: 400; }
    .dp-list li { display: flex; justify-content: space-between; gap: var(--pr-space-sm); }
    .dp-list li span { overflow-wrap: anywhere; min-width: 0; }
    .dp-list li b { flex: none; font-weight: 700; color: var(--pr-ink-2); }
    .dp-breakdown { margin: 0; padding: var(--pr-space-xs) 0 var(--pr-space-sm); list-style: none; border-bottom: 1px solid var(--pr-rule); }
    .dp-breakdown li { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: var(--pr-space-sm); padding: 2px var(--pr-space-lg); font-size: var(--pr-text-sm); color: var(--pr-ink); }
    .dp-breakdown li span { overflow-wrap: anywhere; }
    .dp-breakdown li b { font-variant-numeric: tabular-nums; color: var(--pr-ink-2); }
    .dp-breakdown-title { margin: 0; padding: var(--pr-space-sm) var(--pr-space-lg) 0; font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-ink-2); }
    .dp-more { padding: 2px var(--pr-space-lg) 0; font-size: var(--pr-text-xs); color: var(--pr-muted); }
    @media (max-width: 64rem) { .dp-fields-2 { grid-template-columns: minmax(0, 1fr); } }

    .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }

    @media (max-width: 64rem) {
        .dp-layout { grid-template-columns: minmax(0, 1fr); }
        .tl-stats > div { border-right: 0; border-bottom: 1px solid var(--pr-rule); }
        .tl-stats > div:last-child { border-bottom: 0; }
        .pr-search, .pr-search input { width: 100%; }
        .emp-input { font-size: 16px; }
    }

    /* Di HP: nama, jumlah aktif, dan tombol Ubah tetap terlihat; porsi dan total disembunyikan */
    @media (max-width: 40rem) {
        #department-table .dp-col-opt { display: none; }
    }

    @media (prefers-reduced-motion: reduce) {
        .pr *, .pr *::before, .pr *::after { transition-duration: 0ms !important; animation-duration: 0ms !important; }
    }
</style>
