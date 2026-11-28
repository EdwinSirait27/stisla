<style>
    /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand · redesign: full
     * paper: oklch(98.4% 0.004 265) · accent: oklch(61% 0.17 276) · display: Nunito · body: Nunito
     * nav: kept (Stisla shell) · signature: every berita acara beside how far its approval has come (a bar of approvers signed) and who it is waiting on and for how long, with anything stuck past three days surfaced and, for ManageBeritaAcara, a reminder that can go to the approver on turn, the submitter's superior, the next approver, or any address · states: empty per tab, filtered empty, my turn, stuck, revision, unread, reminder sent, reminder target unavailable
     * axes: light/rounded-sans/chromatic-indigo · posture: app · contrast: pass · v2.0.0 */
    /* Hallmark · dropped onchange="this.form.submit()" · the filter selects submit from one listener in the page script; an Apply button keeps the form usable without JavaScript */
    /* Hallmark · pre-emit critique: P4 H4 E4 S4 R4 V4 · weakest: E, rows said who it waits on but not how far along or how long; answered by progress bar, waiting time and a stuck filter */
    .bl-notice { margin: 0 0 var(--pr-space-lg); padding: var(--pr-space-sm) var(--pr-space-md); border-radius: var(--pr-radius); font-size: var(--pr-text-sm); color: var(--pr-ink); }
    .bl-notice.is-ok { background: var(--pr-accent-soft); }
    .bl-notice.is-warn { background: var(--pr-warn-soft); }

    /* Ringkasan: tiap angka adalah tautan ke daftar yang sesuai */
    .bl-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 180px), 1fr)); margin: 0; }
    .bl-stats > a { display: block; padding: var(--pr-space-md) var(--pr-space-lg); border-right: 1px solid var(--pr-rule); color: var(--pr-ink); text-decoration: none; transition: background-color var(--pr-dur-micro) var(--pr-ease); }
    .bl-stats > a:last-child { border-right: 0; }
    .bl-stats > a:hover { background: var(--pr-paper); color: var(--pr-ink); text-decoration: none; }
    .bl-stats > a:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: -2px; }
    .bl-stats > a.is-warn { background: var(--pr-warn-soft); }
    .bl-stats dt { font-size: var(--pr-text-sm); font-weight: 600; color: var(--pr-ink-2); }
    .bl-stats dd { margin: 2px 0 0; font-size: var(--pr-text-xl); font-weight: 800; font-variant-numeric: tabular-nums; }
    .bl-stats span { display: block; font-size: var(--pr-text-xs); line-height: 1.45; color: var(--pr-ink-2); }

    /* Filter */
    .bl-filters { display: flex; flex-wrap: wrap; gap: var(--pr-space-sm); align-items: flex-end; padding: var(--pr-space-sm) var(--pr-space-lg) var(--pr-space-md); border-bottom: 1px solid var(--pr-rule); }
    .bl-field { display: grid; gap: var(--pr-space-2xs); min-width: 160px; }
    .bl-field.bl-grow { flex: 1 1 240px; }
    .bl-field label { font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-ink-2); }
    .bl-field input, .bl-field select { box-sizing: border-box; width: 100%; height: 36px; padding: 0 var(--pr-space-sm); border: 1px solid var(--pr-field-edge); border-radius: var(--pr-radius-sm); background: var(--pr-panel); font-family: var(--pr-font); font-size: var(--pr-text-sm); color: var(--pr-ink); }
    .bl-field input:focus-visible, .bl-field select:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 0; }
    .bl-actions { display: flex; gap: var(--pr-space-xs); }

    /* Daftar */
    .bl-list { margin: 0; padding: 0; list-style: none; }
    .bl-list li.is-turn .bl-row { box-shadow: inset 3px 0 0 var(--pr-accent); }
    .bl-list li.is-stuck .bl-row { box-shadow: inset 3px 0 0 var(--pr-warn); }
    .bl-row { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 280px); gap: var(--pr-space-md); padding: var(--pr-space-md) var(--pr-space-lg); border-bottom: 1px solid var(--pr-rule); color: var(--pr-ink); text-decoration: none; transition: background-color var(--pr-dur-micro) var(--pr-ease); }
    .bl-row:hover { background: var(--pr-paper); color: var(--pr-ink); text-decoration: none; }
    .bl-row:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: -2px; }
    .bl-main { min-width: 0; display: grid; gap: var(--pr-space-2xs); }
    .bl-title { font-size: var(--pr-text-md); font-weight: 700; overflow-wrap: anywhere; }
    .bl-meta { font-size: var(--pr-text-sm); line-height: 1.45; color: var(--pr-ink-2); }
    .bl-extra { display: flex; flex-wrap: wrap; gap: var(--pr-space-xs) var(--pr-space-md); font-size: var(--pr-text-xs); color: var(--pr-muted); }
    .bl-extra i { margin-right: 2px; }
    .bl-side { display: grid; gap: var(--pr-space-xs); align-content: start; justify-items: start; }
    .bl-progress { display: grid; gap: var(--pr-space-2xs); width: 100%; font-size: var(--pr-text-xs); font-weight: 600; color: var(--pr-ink-2); font-variant-numeric: tabular-nums; }
    .bl-bar { display: block; height: 6px; border-radius: var(--pr-pill); background: var(--pr-paper-2); overflow: hidden; }
    .bl-bar > i { display: block; height: 100%; border-radius: inherit; background: var(--pr-accent); transform-origin: left center; }
    .bl-wait { font-size: var(--pr-text-xs); line-height: 1.45; color: var(--pr-ink-2); }
    .bl-wait.is-stuck { color: var(--pr-warn); font-weight: 700; }
    .bl-empty { display: grid; gap: var(--pr-space-sm); justify-items: start; padding: var(--pr-space-lg); }
    .bl-empty p { margin: 0; font-size: var(--pr-text-sm); color: var(--pr-ink-2); }

    /* Kirim ulang email (detail) */
    .rm-form { display: grid; gap: var(--pr-space-sm); padding: var(--pr-space-md) var(--pr-space-lg) var(--pr-space-lg); }
    .rm-option { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: var(--pr-space-xs); align-items: start; padding: var(--pr-space-xs) var(--pr-space-sm); border: 1px solid var(--pr-rule); border-radius: var(--pr-radius-sm); cursor: pointer; transition: border-color var(--pr-dur-micro) var(--pr-ease), background-color var(--pr-dur-micro) var(--pr-ease); }
    .rm-option:hover { background: var(--pr-paper); }
    .rm-option:has(input:checked) { border-color: var(--pr-accent); background: var(--pr-accent-soft); }
    .rm-option:has(input:disabled) { opacity: .6; cursor: not-allowed; }
    .rm-option:has(input:focus-visible) { outline: 2px solid var(--pr-focus); outline-offset: 2px; }
    .rm-option input { margin-top: var(--pr-space-2xs); accent-color: var(--pr-accent-strong); }
    .rm-option strong { display: block; font-size: var(--pr-text-sm); }
    .rm-option small { display: block; font-size: var(--pr-text-xs); line-height: 1.45; color: var(--pr-ink-2); }
    .rm-form input[type=email], .rm-form textarea { box-sizing: border-box; width: 100%; padding: var(--pr-space-xs) var(--pr-space-sm); border: 1px solid var(--pr-field-edge); border-radius: var(--pr-radius-sm); font-family: var(--pr-font); font-size: var(--pr-text-sm); color: var(--pr-ink); }
    .rm-form input[type=email]:focus-visible, .rm-form textarea:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 0; }
    .rm-form label.rm-label { font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-ink-2); }

    @media (max-width: 64rem) {
        .bl-row { grid-template-columns: minmax(0, 1fr); }
        .bl-stats > a { border-right: 0; border-bottom: 1px solid var(--pr-rule); }
        .bl-stats > a:last-child { border-bottom: 0; }
        .bl-field { flex: 1 1 100%; }
        .bl-field input, .bl-field select { font-size: 16px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .bl-row, .bl-stats > a, .rm-option { transition: none; }
    }
</style>
