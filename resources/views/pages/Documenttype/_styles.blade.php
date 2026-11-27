<style>
    /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand · redesign: full
     * paper: oklch(98.4% 0.004 265) · accent: oklch(61% 0.17 276) · display: Nunito · body: Nunito
     * nav: kept (Stisla shell) · signature: berita acara whose approval chain reads as an ordered timeline with each approver's captured signature, signed on the spot with a pad when the profile has none; a company by document-type grid showing where each letter can be issued, its bank variant and how many it has issued, with ambiguous double settings flagged; each document type beside its template file (flagged when the file is missing), the companies it is switched on for and how many documents it has issued; on the form, the document number it will produce redraws as the code is typed · states: loading, empty, error, no-permission, inactive, missing template, code locked by issued documents, special-cased code, validation errors
     * axes: light/rounded-sans/chromatic-indigo · posture: app · contrast: pass · v2.0.0 */
    /* Hallmark · pre-emit critique: P4 H4 E4 S4 R4 V4 · weakest: E, a master table edited only in the database; answered by usage, template checks and the live number preview */
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
    #doctype-table { width: 100% !important; border-collapse: collapse; font-size: var(--pr-text-sm); }
    #doctype-table thead th { padding: var(--pr-space-sm) var(--pr-space-xs); border: 0; border-bottom: 1px solid var(--pr-rule); background: var(--pr-panel); font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-ink-2); white-space: nowrap; }
    #doctype-table thead th.sorting, #doctype-table thead th.sorting_asc, #doctype-table thead th.sorting_desc { padding-right: var(--pr-space-lg); }
    #doctype-table tbody td { padding: var(--pr-space-sm) var(--pr-space-xs); border: 0; border-bottom: 1px solid var(--pr-rule); vertical-align: middle; color: var(--pr-ink); }
    #doctype-table tbody tr:hover td { background: var(--pr-paper); }
    .pr-cell-name { display: block; font-weight: 700; overflow-wrap: anywhere; }
    .pr-cell-sub { display: block; font-size: var(--pr-text-xs); color: var(--pr-muted); }
    .dt-count { font-weight: 700; font-variant-numeric: tabular-nums; }
    .dt-zero { color: var(--pr-muted); font-weight: 600; }

    /* Porsi karyawan aktif per bank */
    .dt-share { display: grid; grid-template-columns: minmax(0, 1fr) 4.5ch; align-items: center; gap: var(--pr-space-xs); min-width: 160px; }
    .dt-bar { height: 8px; border-radius: var(--pr-pill); background: var(--pr-paper-2); overflow: hidden; }
    .dt-bar > i { display: block; height: 100%; border-radius: inherit; background: var(--pr-accent); transform-origin: left center; }
    .dt-share span { font-size: var(--pr-text-xs); font-weight: 700; font-variant-numeric: tabular-nums; text-align: right; color: var(--pr-ink-2); }

    /* DataTables */
    .pr-dt-bottom { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: var(--pr-space-sm); padding-top: var(--pr-space-md); }
    .pr .dataTables_wrapper .dataTables_info, .pr .dataTables_wrapper .dataTables_paginate, .pr .dataTables_wrapper .dataTables_length { float: none; padding: 0; font-size: var(--pr-text-sm); color: var(--pr-ink-2); }
    .pr .dataTables_wrapper .dataTables_paginate .paginate_button { display: inline-flex; align-items: center; justify-content: center; min-width: 32px; min-height: 32px; margin: 0 0 0 var(--pr-space-2xs) !important; padding: 0 var(--pr-space-sm) !important; border: 1px solid var(--pr-rule) !important; border-radius: var(--pr-radius-sm); background: var(--pr-panel) !important; color: var(--pr-ink) !important; font-weight: 600; }
    .pr .dataTables_wrapper .dataTables_paginate .paginate_button.current, .pr .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover { background: var(--pr-accent-soft) !important; border-color: var(--pr-accent) !important; color: var(--pr-accent-strong) !important; }
    .pr .dataTables_wrapper .dataTables_paginate .paginate_button.disabled, .pr .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover { opacity: .55; cursor: not-allowed; }
    .pr .dataTables_wrapper .dataTables_processing { background: var(--pr-panel); border: 1px solid var(--pr-rule); border-radius: var(--pr-radius-sm); color: var(--pr-ink-2); font-size: var(--pr-text-sm); }
    .pr .dataTables_wrapper .dataTables_length select { min-height: 32px; border: 1px solid var(--pr-field-edge); border-radius: var(--pr-radius-sm); font-family: var(--pr-font); }

    /* ── Form ── */
    .dt-layout { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 320px); gap: var(--pr-space-lg); align-items: start; }
    .dt-layout > .pr-panel { margin-bottom: 0; }
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

    .dt-similar { max-width: 480px; margin: var(--pr-space-xs) 0 0; padding: var(--pr-space-xs) var(--pr-space-sm); border-radius: var(--pr-radius-sm); background: var(--pr-warn-soft); font-size: var(--pr-text-sm); line-height: 1.45; color: var(--pr-ink); }
    .dt-similar strong { color: var(--pr-warn); }

    .dt-rules { margin: 0; padding: var(--pr-space-sm) var(--pr-space-lg) var(--pr-space-sm) calc(var(--pr-space-lg) + 1.1em); border-top: 1px solid var(--pr-rule); background: var(--pr-paper); font-size: var(--pr-text-sm); line-height: 1.55; color: var(--pr-ink-2); }

    .dt-savebar { display: flex; align-items: center; justify-content: flex-end; flex-wrap: wrap; gap: var(--pr-space-xs); padding: var(--pr-space-sm) var(--pr-space-lg); border-top: 1px solid var(--pr-rule); }

    /* Panel samping: bank terdaftar & pemakaian */
    .dt-usage { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); margin: 0; border-bottom: 1px solid var(--pr-rule); }
    .dt-usage > div { padding: var(--pr-space-sm) var(--pr-space-lg); }
    .dt-usage > div + div { border-left: 1px solid var(--pr-rule); }
    .dt-usage dt { font-size: var(--pr-text-xs); font-weight: 600; color: var(--pr-ink-2); }
    .dt-usage dd { margin: 2px 0 0; font-size: var(--pr-text-xl); font-weight: 800; font-variant-numeric: tabular-nums; }
    .dt-usage-note { margin: 0; padding: var(--pr-space-sm) var(--pr-space-lg); border-bottom: 1px solid var(--pr-rule); font-size: var(--pr-text-xs); line-height: 1.5; color: var(--pr-ink-2); }
    .dt-list { max-height: 360px; margin: 0; padding: var(--pr-space-xs) 0; overflow-y: auto; list-style: none; }
    .dt-list li { padding: var(--pr-space-2xs) var(--pr-space-lg); font-size: var(--pr-text-sm); color: var(--pr-ink); }
    .dt-list li.is-match { background: var(--pr-warn-soft); font-weight: 700; }
    .dt-list-empty { margin: 0; padding: var(--pr-space-md) var(--pr-space-lg); font-size: var(--pr-text-sm); color: var(--pr-muted); }

    /* ── Jenis dokumen ── */
    .dt-code { display: inline-block; margin-left: var(--pr-space-xs); padding: 0 var(--pr-space-xs); border: 1px solid var(--pr-rule); border-radius: var(--pr-radius-sm); background: var(--pr-paper); font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-ink-2); vertical-align: 1px; }
    .dt-code.is-missing { border-color: var(--pr-warn); background: var(--pr-warn-soft); color: var(--pr-warn); }
    .dt-warn { display: inline-block; padding: 2px var(--pr-space-xs); border-radius: var(--pr-radius-sm); background: var(--pr-warn-soft); font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-warn); }
    .dt-names { display: block; max-width: 36ch; overflow-wrap: anywhere; }
    #doctype-table td { vertical-align: top; }
    .dt-flags-wrap { padding: var(--pr-space-sm) var(--pr-space-lg); border-bottom: 1px solid var(--pr-rule); }
    .dt-flags { display: flex; flex-wrap: wrap; gap: var(--pr-space-xs); }
    .dt-flags button { min-height: 32px; padding: 0 var(--pr-space-sm); border: 1px solid var(--pr-rule); border-radius: var(--pr-pill); background: var(--pr-panel); font-family: var(--pr-font); font-size: var(--pr-text-sm); font-weight: 600; color: var(--pr-ink-2); cursor: pointer; transition: background-color var(--pr-dur-micro) var(--pr-ease), color var(--pr-dur-micro) var(--pr-ease), border-color var(--pr-dur-micro) var(--pr-ease); }
    .dt-flags button:hover { color: var(--pr-ink); background: var(--pr-paper-2); }
    .dt-flags button:active { background: var(--pr-rule); }
    .dt-flags button[aria-pressed="true"] { border-color: var(--pr-accent); background: var(--pr-accent-soft); color: var(--pr-accent-deep); }
    .dt-fields-2 { grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); }
    .dt-fields-2 .emp-input { max-width: none; }
    .dt-code-input { font-weight: 700; }
    .dt-code-input::placeholder { font-weight: 400; }
    .dt-list li { display: flex; justify-content: space-between; gap: var(--pr-space-sm); }
    .dt-list li span { overflow-wrap: anywhere; min-width: 0; }
    .dt-list li b { flex: none; font-weight: 700; color: var(--pr-ink-2); }
    .dt-breakdown { margin: 0; padding: var(--pr-space-xs) 0 var(--pr-space-sm); list-style: none; border-bottom: 1px solid var(--pr-rule); }
    .dt-breakdown li { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: var(--pr-space-sm); padding: 2px var(--pr-space-lg); font-size: var(--pr-text-sm); color: var(--pr-ink); }
    .dt-breakdown li span { overflow-wrap: anywhere; }
    .dt-breakdown li b { font-variant-numeric: tabular-nums; color: var(--pr-ink-2); }
    .dt-breakdown-title { margin: 0; padding: var(--pr-space-sm) var(--pr-space-lg) 0; font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-ink-2); }
    .dt-more { padding: 2px var(--pr-space-lg) 0; font-size: var(--pr-text-xs); color: var(--pr-muted); }
    @media (max-width: 64rem) { .dt-fields-2 { grid-template-columns: minmax(0, 1fr); } }

    .dt-status { display: inline-block; padding: 2px var(--pr-space-xs); border-radius: var(--pr-radius-sm); font-size: var(--pr-text-xs); font-weight: 700; }
    .dt-status.is-on { background: var(--pr-accent-soft); color: var(--pr-accent-deep); }
    .dt-status.is-off { background: var(--pr-paper-2); color: var(--pr-ink-2); }
    .dt-file { font-size: var(--pr-text-sm); color: var(--pr-ink); overflow-wrap: anywhere; }
    .dt-select { box-sizing: border-box; width: 100%; height: 40px; padding: 0 12px; border: 1px solid var(--pr-field-edge); border-radius: var(--pr-radius-sm); background: var(--pr-panel); font-family: var(--pr-font); font-size: var(--pr-text-md); color: var(--pr-ink); }
    .dt-select:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 0; }
    .dt-select.is-invalid { border-color: var(--pr-error); }
    .dt-wide { grid-column: 1 / -1; }
    .dt-check { display: flex; gap: var(--pr-space-sm); align-items: flex-start; padding: var(--pr-space-sm) var(--pr-space-md); border: 1px solid var(--pr-rule); border-radius: var(--pr-radius-sm); cursor: pointer; }
    .emp-field > label.dt-check { display: flex; margin-bottom: 0; font-weight: 400; }
    .emp-input[readonly] { background: var(--pr-paper-2); color: var(--pr-ink-2); cursor: not-allowed; }
    .dt-check:has(input:checked) { border-color: var(--pr-accent); background: var(--pr-accent-soft); }
    .dt-check input { flex: none; width: 18px; height: 18px; margin-top: 2px; accent-color: var(--pr-accent-strong); }
    .dt-check input:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 2px; }
    .dt-check strong { display: block; font-size: var(--pr-text-sm); color: var(--pr-ink); }
    .dt-check small { display: block; margin-top: 2px; font-size: var(--pr-text-xs); line-height: 1.45; color: var(--pr-ink-2); }
    .dt-number { margin: 0; padding: var(--pr-space-md) var(--pr-space-lg); border-bottom: 1px solid var(--pr-rule); }
    .dt-number dt { font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-ink-2); }
    .dt-number dd { margin: 4px 0 0; font-size: var(--pr-text-lg); font-weight: 800; color: var(--pr-ink); font-variant-numeric: tabular-nums; overflow-wrap: anywhere; }
    .dt-number dd small { display: block; margin-top: 2px; font-size: var(--pr-text-xs); font-weight: 400; color: var(--pr-muted); }
    .dt-usage dd small { font-size: var(--pr-text-sm); font-weight: 600; color: var(--pr-muted); }

    /* ── Pengaturan per perusahaan: matriks ── */
    .dc-wrap { overflow-x: auto; padding: 0 var(--pr-space-lg) var(--pr-space-md); }
    .dc-grid { width: 100%; border-collapse: collapse; font-size: var(--pr-text-sm); }
    .dc-grid th { padding: var(--pr-space-sm) var(--pr-space-xs); border-bottom: 1px solid var(--pr-rule); text-align: left; font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-ink-2); white-space: nowrap; vertical-align: bottom; }
    .dc-grid th small { display: block; font-weight: 400; color: var(--pr-muted); max-width: 18ch; white-space: normal; }
    .dc-grid td { padding: var(--pr-space-xs); border-bottom: 1px solid var(--pr-rule); vertical-align: top; }
    .dc-grid tbody th { padding-right: var(--pr-space-md); font-size: var(--pr-text-sm); color: var(--pr-ink); white-space: normal; min-width: 220px; vertical-align: top; }
    .dc-cell { display: grid; gap: 4px; min-width: 140px; }
    .dc-item { display: block; padding: var(--pr-space-xs) var(--pr-space-sm); border: 1px solid var(--pr-rule); border-radius: var(--pr-radius-sm); background: var(--pr-panel); color: var(--pr-ink); text-decoration: none; line-height: 1.35; transition: border-color var(--pr-dur-micro) var(--pr-ease), background-color var(--pr-dur-micro) var(--pr-ease); }
    .dc-item:hover { border-color: var(--pr-accent); background: var(--pr-accent-soft); color: var(--pr-ink); text-decoration: none; }
    .dc-item:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 1px; }
    .dc-item.is-on { border-color: var(--pr-accent); }
    .dc-item.is-off { background: var(--pr-paper-2); color: var(--pr-ink-2); }
    .dc-item.is-dup { border-color: var(--pr-warn); background: var(--pr-warn-soft); }
    .dc-item strong { display: block; font-size: var(--pr-text-xs); font-weight: 700; }
    .dc-item span { display: block; font-size: var(--pr-text-xs); color: var(--pr-ink-2); overflow-wrap: anywhere; }
    .dc-add { display: inline-flex; align-items: center; gap: 4px; min-height: 32px; padding: 0 var(--pr-space-sm); border: 1px dashed var(--pr-field-edge); border-radius: var(--pr-radius-sm); color: var(--pr-ink-2); font-size: var(--pr-text-xs); font-weight: 600; text-decoration: none; }
    .dc-add:hover { border-color: var(--pr-accent); color: var(--pr-accent-deep); text-decoration: none; }
    .dc-add:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 1px; }
    .dc-legend { display: flex; flex-wrap: wrap; gap: var(--pr-space-md); margin: 0; padding: var(--pr-space-sm) var(--pr-space-lg); list-style: none; font-size: var(--pr-text-xs); color: var(--pr-ink-2); border-bottom: 1px solid var(--pr-rule); }
    .dc-legend li { display: flex; align-items: center; gap: var(--pr-space-xs); }
    .dc-swatch { width: 14px; height: 14px; border-radius: 4px; border: 1px solid var(--pr-rule); background: var(--pr-panel); }
    .dc-swatch.is-on { border-color: var(--pr-accent); }
    .dc-swatch.is-off { background: var(--pr-paper-2); }
    .dc-swatch.is-dup { border-color: var(--pr-warn); background: var(--pr-warn-soft); }
    .dc-extra[hidden] { display: none; }
    .dc-extra-note { margin: 0; padding: 0 var(--pr-space-lg) var(--pr-space-md); font-size: var(--pr-text-sm); color: var(--pr-ink-2); }

    /* ── Berita acara ── */
    .ba-tabs { display: flex; flex-wrap: wrap; gap: var(--pr-space-xs); padding: var(--pr-space-sm) var(--pr-space-lg); border-bottom: 1px solid var(--pr-rule); }
    .ba-tab { display: inline-flex; align-items: center; gap: var(--pr-space-xs); min-height: 36px; padding: 0 var(--pr-space-md); border: 1px solid var(--pr-rule); border-radius: var(--pr-pill); background: var(--pr-panel); color: var(--pr-ink-2); font-size: var(--pr-text-sm); font-weight: 600; text-decoration: none; transition: background-color var(--pr-dur-micro) var(--pr-ease), border-color var(--pr-dur-micro) var(--pr-ease); }
    .ba-tab:hover { background: var(--pr-paper-2); color: var(--pr-ink); text-decoration: none; }
    .ba-tab:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 2px; }
    .ba-tab[aria-current="page"] { border-color: var(--pr-accent); background: var(--pr-accent-soft); color: var(--pr-accent-deep); }
    .ba-count { min-width: 20px; padding: 0 var(--pr-space-2xs); border-radius: var(--pr-pill); background: var(--pr-paper-2); color: var(--pr-ink); font-size: var(--pr-text-xs); font-weight: 700; text-align: center; font-variant-numeric: tabular-nums; }
    .ba-count.is-hot { background: var(--pr-warn-soft); color: var(--pr-warn); }
    .ba-filter { display: flex; flex-wrap: wrap; gap: var(--pr-space-sm); align-items: center; }
    .ba-filter .dt-select { width: auto; min-width: 200px; height: 36px; font-size: var(--pr-text-sm); }
    .ba-list { margin: 0; padding: 0; list-style: none; }
    .ba-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: var(--pr-space-md); align-items: center; padding: var(--pr-space-md) var(--pr-space-lg); border-bottom: 1px solid var(--pr-rule); color: var(--pr-ink); text-decoration: none; transition: background-color var(--pr-dur-micro) var(--pr-ease); }
    .ba-row:hover { background: var(--pr-paper); color: var(--pr-ink); text-decoration: none; }
    .ba-row:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: -2px; }
    .ba-row-title { display: block; font-weight: 700; overflow-wrap: anywhere; }
    .ba-row-meta { display: block; margin-top: 2px; font-size: var(--pr-text-xs); color: var(--pr-ink-2); }
    .ba-row-side { display: grid; gap: 4px; justify-items: end; text-align: right; font-size: var(--pr-text-xs); color: var(--pr-ink-2); }
    .ba-new { padding: 0 var(--pr-space-2xs); border-radius: var(--pr-radius-sm); background: var(--pr-accent-soft); color: var(--pr-accent-deep); font-size: var(--pr-text-xs); font-weight: 700; }
    .ba-st { display: inline-block; padding: 2px var(--pr-space-xs); border-radius: var(--pr-radius-sm); font-size: var(--pr-text-xs); font-weight: 700; white-space: nowrap; }
    .ba-st.is-pending, .ba-st.is-in_review { background: var(--pr-accent-soft); color: var(--pr-accent-deep); }
    .ba-log { margin: 0; padding: 0 var(--pr-space-lg) var(--pr-space-md); list-style: none; display: grid; gap: var(--pr-space-sm); }
    .ba-log li { display: grid; gap: 2px; padding-left: var(--pr-space-sm); border-left: 2px solid var(--pr-rule); }
    .ba-log li.is-step_approved, .ba-log li.is-approved { border-left-color: var(--pr-success); }
    .ba-log li.is-returned { border-left-color: var(--pr-warn); }
    .ba-log-text { font-size: var(--pr-text-sm); color: var(--pr-ink); line-height: 1.45; overflow-wrap: anywhere; }
    .ba-log time { font-size: var(--pr-text-xs); color: var(--pr-ink-2); }
    .ba-st.is-revision, .ba-st.is-returned { background: var(--pr-warn-soft); color: var(--pr-warn); }
    .ba-st.is-approved { background: var(--pr-success-soft); color: var(--pr-success); }
    .ba-st.is-cancelled, .ba-st.is-voided, .ba-st.is-waiting { background: var(--pr-paper-2); color: var(--pr-ink-2); }
    .ba-empty { padding: var(--pr-space-lg); color: var(--pr-ink-2); }
    .ba-pager { padding: var(--pr-space-md) var(--pr-space-lg); }

    /* Form: pemilih approver berurutan & tembusan */
    .ba-picker { position: relative; }
    .ba-results { position: absolute; z-index: 20; left: 0; right: 0; top: calc(100% + 4px); max-height: 280px; margin: 0; padding: var(--pr-space-2xs) 0; overflow-y: auto; list-style: none; background: var(--pr-panel); border: 1px solid var(--pr-field-edge); border-radius: var(--pr-radius-sm); }
    .ba-results button { display: block; width: 100%; padding: var(--pr-space-xs) var(--pr-space-md); border: 0; background: none; text-align: left; font-family: var(--pr-font); color: var(--pr-ink); cursor: pointer; }
    .ba-results button:hover, .ba-results button.is-active { background: var(--pr-accent-soft); }
    .ba-results button:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: -2px; }
    .ba-results strong { display: block; font-size: var(--pr-text-sm); }
    .ba-results small { display: block; font-size: var(--pr-text-xs); color: var(--pr-ink-2); }
    .ba-results .ba-none { padding: var(--pr-space-xs) var(--pr-space-md); font-size: var(--pr-text-sm); color: var(--pr-ink-2); }
    .ba-order { margin: var(--pr-space-sm) 0 0; padding: 0; list-style: none; display: grid; gap: var(--pr-space-xs); }
    .ba-order li { display: grid; grid-template-columns: 28px minmax(0, 1fr) auto; gap: var(--pr-space-sm); align-items: center; padding: var(--pr-space-xs) var(--pr-space-sm); border: 1px solid var(--pr-rule); border-radius: var(--pr-radius-sm); background: var(--pr-panel); }
    .ba-num { display: grid; place-items: center; width: 28px; height: 28px; border-radius: var(--pr-pill); background: var(--pr-accent-soft); color: var(--pr-accent-deep); font-size: var(--pr-text-xs); font-weight: 800; }
    .ba-who strong { display: block; font-size: var(--pr-text-sm); overflow-wrap: anywhere; }
    .ba-who small { display: block; font-size: var(--pr-text-xs); color: var(--pr-ink-2); }
    .ba-who em { display: block; font-style: normal; font-size: var(--pr-text-xs); color: var(--pr-warn); }
    .ba-acts { display: flex; gap: 2px; }
    .ba-icon { display: grid; place-items: center; width: 32px; height: 32px; padding: 0; border: 1px solid transparent; border-radius: var(--pr-radius-sm); background: none; color: var(--pr-ink-2); cursor: pointer; }
    .ba-icon:hover { border-color: var(--pr-rule); background: var(--pr-paper-2); color: var(--pr-ink); }
    .ba-icon:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 1px; }
    .ba-icon:disabled { opacity: .35; cursor: not-allowed; }
    .ba-chips { display: flex; flex-wrap: wrap; gap: var(--pr-space-xs); margin-top: var(--pr-space-sm); }
    .ba-chip { display: inline-flex; align-items: center; gap: 4px; padding: 2px 4px 2px var(--pr-space-sm); border: 1px solid var(--pr-rule); border-radius: var(--pr-pill); background: var(--pr-paper); font-size: var(--pr-text-sm); }
    .ba-chip button { display: grid; place-items: center; width: 24px; height: 24px; border: 0; border-radius: var(--pr-pill); background: none; color: var(--pr-ink-2); cursor: pointer; }
    .ba-chip button:hover { background: var(--pr-rule); color: var(--pr-ink); }
    .ba-chip button:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 1px; }
    .ba-textarea { box-sizing: border-box; width: 100%; min-height: 180px; padding: var(--pr-space-sm) 12px; border: 1px solid var(--pr-field-edge); border-radius: var(--pr-radius-sm); background: var(--pr-panel); font-family: var(--pr-font); font-size: var(--pr-text-md); line-height: 1.55; color: var(--pr-ink); resize: vertical; }
    .ba-textarea:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 0; }
    .ba-textarea.is-invalid { border-color: var(--pr-error); }
    .ba-files { margin: var(--pr-space-xs) 0 0; padding: 0; list-style: none; display: grid; gap: 4px; font-size: var(--pr-text-sm); }
    .ba-files label { display: flex; gap: var(--pr-space-xs); align-items: center; }

    /* Detail */
    .ba-content { padding: var(--pr-space-md) var(--pr-space-lg) var(--pr-space-lg); }
    .ba-facts { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 180px), 1fr)); gap: var(--pr-space-sm) var(--pr-space-lg); margin: 0 0 var(--pr-space-md); }
    .ba-facts dt { font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-ink-2); }
    .ba-facts dd { margin: 2px 0 0; font-size: var(--pr-text-sm); color: var(--pr-ink); overflow-wrap: anywhere; }
    .ba-body { margin: 0; font-size: var(--pr-text-md); line-height: 1.65; color: var(--pr-ink); white-space: pre-wrap; overflow-wrap: anywhere; max-width: 75ch; }
    .ba-steps { margin: 0; padding: var(--pr-space-sm) var(--pr-space-lg) var(--pr-space-md); list-style: none; display: grid; gap: 0; }
    .ba-step { display: grid; grid-template-columns: 28px minmax(0, 1fr); gap: var(--pr-space-sm); position: relative; padding-bottom: var(--pr-space-md); }
    .ba-step:not(:last-child)::before { content: ""; position: absolute; left: 13px; top: 30px; bottom: 0; width: 2px; background: var(--pr-rule); }
    .ba-step.is-approved .ba-num { background: var(--pr-success-soft); color: var(--pr-success); }
    .ba-step.is-returned .ba-num { background: var(--pr-warn-soft); color: var(--pr-warn); }
    .ba-step.is-waiting .ba-num, .ba-step.is-voided .ba-num { background: var(--pr-paper-2); color: var(--pr-ink-2); }
    .ba-step-head { display: flex; flex-wrap: wrap; gap: var(--pr-space-xs); align-items: center; }
    .ba-step-name { font-size: var(--pr-text-sm); font-weight: 700; color: var(--pr-ink); }
    .ba-step-sub { display: block; font-size: var(--pr-text-xs); color: var(--pr-ink-2); }
    .ba-step-note { margin: var(--pr-space-xs) 0 0; padding: var(--pr-space-xs) var(--pr-space-sm); border-radius: var(--pr-radius-sm); background: var(--pr-paper); font-size: var(--pr-text-sm); color: var(--pr-ink); white-space: pre-wrap; }
    .ba-sign { display: block; height: 56px; width: auto; max-width: 180px; margin-top: var(--pr-space-xs); }
    .ba-pad { display: block; width: 100%; height: 180px; border: 1px dashed var(--pr-field-edge); border-radius: var(--pr-radius-sm); background: var(--pr-panel); touch-action: none; cursor: crosshair; }
    .ba-pad:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 2px; }
    .ba-pad-bar { display: flex; justify-content: space-between; align-items: center; gap: var(--pr-space-sm); margin-top: var(--pr-space-xs); font-size: var(--pr-text-xs); color: var(--pr-ink-2); }
    .ba-action { padding: var(--pr-space-md) var(--pr-space-lg); border-top: 1px solid var(--pr-rule); display: grid; gap: var(--pr-space-sm); }
    .ba-action h3 { margin: 0; font-size: var(--pr-text-sm); font-weight: 700; color: var(--pr-ink); }
    .ba-action .emp-input { max-width: none; }
    .ba-danger { border-color: var(--pr-warn); color: var(--pr-warn); }
    .ba-danger:hover { background: var(--pr-warn-soft); color: var(--pr-warn); }
    .ba-people { margin: 0; padding: var(--pr-space-xs) 0; list-style: none; }
    .ba-people li { display: flex; justify-content: space-between; gap: var(--pr-space-sm); padding: var(--pr-space-2xs) var(--pr-space-lg); font-size: var(--pr-text-sm); }
    .ba-people li small { color: var(--pr-ink-2); white-space: nowrap; }
    @media (max-width: 40rem) { .ba-row { grid-template-columns: minmax(0, 1fr); } .ba-row-side { justify-items: start; text-align: left; } }

    .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }

    @media (max-width: 64rem) {
        .dt-layout { grid-template-columns: minmax(0, 1fr); }
        .tl-stats > div { border-right: 0; border-bottom: 1px solid var(--pr-rule); }
        .tl-stats > div:last-child { border-bottom: 0; }
        .pr-search, .pr-search input { width: 100%; }
        .emp-input { font-size: 16px; }
    }

    /* Di HP: nama, jumlah aktif, dan tombol Ubah tetap terlihat; porsi dan total disembunyikan */
    @media (max-width: 40rem) {
        #doctype-table .dt-col-opt { display: none; }
    }

    @media (prefers-reduced-motion: reduce) {
        .pr *, .pr *::before, .pr *::after { transition-duration: 0ms !important; animation-duration: 0ms !important; }
    }
</style>
