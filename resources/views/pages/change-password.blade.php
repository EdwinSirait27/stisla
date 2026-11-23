@extends('layouts.app')
@section('title', 'Ganti password')
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Hallmark · shape: app screen · genre: modern-minimal · theme: brand · redesign: full
         * paper: oklch(98.4% 0.004 265) · accent: oklch(61% 0.17 276) · display: Nunito · body: Nunito
         * nav: kept (Stisla shell) · signature: the server's password rules as a checklist that ticks while you type, so a rejection never comes as a surprise · states: forced default password, validation errors, throttled, success elsewhere
         * axes: light/rounded-sans/chromatic-indigo · posture: app · contrast: pass · v2.0.0 */
        /* Hallmark · pre-emit critique: P4 H4 E4 S4 R4 V4 · weakest: E, a one-line hint hid six rules; answered by the live checklist */
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
            --pr-success: oklch(50% 0.12 150);
            --pr-success-soft: oklch(96% 0.035 150);
            --pr-warn: oklch(52% 0.12 70);
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
            --pr-dur-micro: 120ms;
            --pr-ease: cubic-bezier(0.16, 1, 0.3, 1);
        }

        html, body { overflow-x: clip; }

        .pr { font-family: var(--pr-font); color: var(--pr-ink); }
        .pr [hidden] { display: none !important; }
        .pr :focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 2px; }

        .pr-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: var(--pr-space-md); margin-bottom: var(--pr-space-lg); padding: var(--pr-space-md) var(--pr-space-lg); background: var(--pr-panel); border: 1px solid var(--pr-rule); border-radius: var(--pr-radius); }
        .pr-head > div { min-width: 0; }
        .pr-crumbs { display: flex; flex-wrap: wrap; gap: var(--pr-space-xs); margin: 0 0 var(--pr-space-2xs); padding: 0; list-style: none; font-size: var(--pr-text-sm); color: var(--pr-muted); }
        .pr-crumbs li + li::before { content: "/"; margin-right: var(--pr-space-xs); color: var(--pr-rule); }
        .pr-crumbs a { color: var(--pr-ink-2); text-decoration: none; }
        .pr-crumbs a:hover { color: var(--pr-accent-strong); text-decoration: underline; }
        .pr-title { margin: 0; font-size: var(--pr-text-2xl); font-weight: 700; line-height: 1.15; color: var(--pr-ink); overflow-wrap: break-word; min-width: 0; }
        .pr-lede { margin: var(--pr-space-xs) 0 0; max-width: 72ch; font-size: var(--pr-text-sm); line-height: 1.5; color: var(--pr-ink-2); }

        .pr-btn { display: inline-flex; align-items: center; justify-content: center; gap: var(--pr-space-xs); min-height: 40px; padding: 0 var(--pr-space-md); border: 1px solid var(--pr-rule); border-radius: var(--pr-radius-sm); background: var(--pr-panel); color: var(--pr-ink); font-family: var(--pr-font); font-size: var(--pr-text-sm); font-weight: 600; line-height: 1; text-decoration: none; white-space: nowrap; cursor: pointer; transition: background-color var(--pr-dur-micro) var(--pr-ease), border-color var(--pr-dur-micro) var(--pr-ease), color var(--pr-dur-micro) var(--pr-ease); }
        .pr-btn:hover { background: var(--pr-paper-2); color: var(--pr-ink); text-decoration: none; }
        .pr-btn:active { background: var(--pr-rule); }
        .pr-btn:disabled { opacity: .55; cursor: not-allowed; }
        .pr-btn-primary { background: var(--pr-accent-strong); border-color: var(--pr-accent-strong); color: var(--pr-accent-ink); }
        .pr-btn-primary:hover, .pr-btn-primary:active { background: var(--pr-accent-deep); border-color: var(--pr-accent-deep); color: var(--pr-accent-ink); }

        .pr-panel { margin-bottom: var(--pr-space-lg); background: var(--pr-panel); border: 1px solid var(--pr-rule); border-radius: var(--pr-radius); }
        .pr-panel-head { padding: var(--pr-space-md) var(--pr-space-lg); border-bottom: 1px solid var(--pr-rule); }
        .pr-panel-title { margin: 0; font-size: var(--pr-text-md); font-weight: 700; color: var(--pr-ink); }
        .pr-panel-sub { display: block; font-size: var(--pr-text-sm); color: var(--pr-muted); }

        .pw-layout { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 300px); gap: var(--pr-space-lg); align-items: start; max-width: 1040px; }
        .pw-layout > .pr-panel { margin-bottom: 0; }

        .pw-banner { display: flex; gap: var(--pr-space-sm); max-width: 1040px; margin: 0 0 var(--pr-space-lg); padding: var(--pr-space-sm) var(--pr-space-md); border-radius: var(--pr-radius); background: var(--pr-warn-soft); font-size: var(--pr-text-sm); line-height: 1.5; color: var(--pr-ink); }
        .pw-banner i { margin-top: var(--pr-space-2xs); color: var(--pr-warn); }
        .pw-banner strong { display: block; }
        .pw-errors { max-width: 1040px; margin: 0 0 var(--pr-space-lg); padding: var(--pr-space-sm) var(--pr-space-md); border-radius: var(--pr-radius); background: var(--pr-error-soft); font-size: var(--pr-text-sm); color: var(--pr-ink); }
        .pw-errors ul { margin: 4px 0 0; padding-left: 1.2em; }

        .pw-fields { display: grid; gap: var(--pr-space-lg); padding: var(--pr-space-lg); }
        .pw-field { min-width: 0; max-width: 440px; }
        .pw-field > label { display: block; margin-bottom: 4px; font-size: var(--pr-text-sm); font-weight: 700; color: var(--pr-ink); }
        .pw-control { display: flex; }
        .pw-input { box-sizing: border-box; flex: 1; min-width: 0; height: 40px; padding: 0 12px; border: 1px solid var(--pr-field-edge); border-right: 0; border-radius: var(--pr-radius-sm) 0 0 var(--pr-radius-sm); background: var(--pr-panel); font-family: var(--pr-font); font-size: var(--pr-text-md); color: var(--pr-ink); }
        .pw-input:focus-visible { outline: 2px solid var(--pr-focus); outline-offset: 0; position: relative; z-index: 1; }
        .pw-input.is-invalid, .pw-input.is-invalid + .pw-eye { border-color: var(--pr-error); }
        .pw-eye { box-sizing: border-box; display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 40px; border: 1px solid var(--pr-field-edge); border-radius: 0 var(--pr-radius-sm) var(--pr-radius-sm) 0; background: var(--pr-paper-2); color: var(--pr-ink-2); cursor: pointer; transition: background-color var(--pr-dur-micro) var(--pr-ease); }
        .pw-eye:hover { background: var(--pr-rule); }
        .pw-eye:focus-visible { outline-offset: 0; }
        .pw-help { display: block; margin-top: 4px; font-size: var(--pr-text-xs); line-height: 1.45; color: var(--pr-muted); }
        .pw-error { display: block; margin-top: 4px; font-size: var(--pr-text-xs); font-weight: 700; color: var(--pr-error); }
        .pw-match { display: block; margin-top: 4px; font-size: var(--pr-text-xs); font-weight: 700; }
        .pw-match.is-ok { color: var(--pr-success); }
        .pw-match.is-bad { color: var(--pr-error); }

        /* Aturan password, dicentang saat mengetik */
        .pw-rules { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: var(--pr-space-2xs) var(--pr-space-md); max-width: 440px; margin: var(--pr-space-xs) 0 0; padding: var(--pr-space-sm); list-style: none; border-radius: var(--pr-radius-sm); background: var(--pr-paper); }
        .pw-rules li { display: flex; gap: var(--pr-space-xs); align-items: baseline; font-size: var(--pr-text-xs); line-height: 1.45; color: var(--pr-ink-2); }
        .pw-rules li i { width: 1em; color: var(--pr-muted); }
        .pw-rules li.is-ok { color: var(--pr-ink); }
        .pw-rules li.is-ok i { color: var(--pr-success); }
        .pw-rules li.is-bad i { color: var(--pr-error); }

        .pw-savebar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: var(--pr-space-xs); padding: var(--pr-space-sm) var(--pr-space-lg); border-top: 1px solid var(--pr-rule); }
        .pw-savebar-note { font-size: var(--pr-text-xs); color: var(--pr-ink-2); }
        .pw-savebar-actions { display: flex; flex-wrap: wrap; gap: var(--pr-space-xs); margin-left: auto; }

        .pw-after { margin: 0; padding: var(--pr-space-sm) var(--pr-space-lg) var(--pr-space-md) calc(var(--pr-space-lg) + 1.1em); font-size: var(--pr-text-sm); line-height: 1.55; color: var(--pr-ink-2); }
        .pw-after li + li { margin-top: var(--pr-space-xs); }
        .pw-account { display: grid; gap: 2px; margin: 0; padding: var(--pr-space-sm) var(--pr-space-lg); border-top: 1px solid var(--pr-rule); font-size: var(--pr-text-sm); }
        .pw-account dt { font-size: var(--pr-text-xs); font-weight: 600; color: var(--pr-ink-2); }
        .pw-account dd { margin: 0 0 var(--pr-space-xs); font-weight: 700; overflow-wrap: anywhere; }

        .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }

        @media (max-width: 64rem) {
            .pw-layout { grid-template-columns: minmax(0, 1fr); }
            .pw-input { font-size: 16px; }
        }
        @media (max-width: 30rem) {
            .pw-rules { grid-template-columns: minmax(0, 1fr); }
            .pw-fields { padding: var(--pr-space-md); }
        }
        @media (prefers-reduced-motion: reduce) {
            .pr *, .pr *::before, .pr *::after { transition-duration: 0ms !important; }
        }
    </style>
@endpush

@section('main')
    @php
        $nip = $user->employee?->employee_pengenal;
    @endphp
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs">
                        @unless ($isDefault)<li><a href="{{ route('pages.feature-profile') }}">Profil</a></li>@endunless
                        <li aria-current="page">Ganti password</li>
                    </ol>
                    <h1 class="pr-title">Ganti password</h1>
                    <p class="pr-lede">Password ini dipakai untuk login di web dan aplikasi mobile.</p>
                </div>
            </header>

            @if ($isDefault)
                <div class="pw-banner" role="alert">
                    <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
                    <div>
                        <strong>Password Anda masih password default.</strong>
                        Password default sama dengan username atau NIP, jadi mudah ditebak. Ganti dulu sebelum memakai aplikasi; menu lain terbuka setelah password baru tersimpan.
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="pw-errors" role="alert">
                    <strong>Password belum diganti.</strong>
                    <ul>
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="pw-layout">
                <form id="pw-form" action="{{ route('change-password.update') }}" method="POST" class="pr-panel" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="pw-fields">
                        <div class="pw-field">
                            <label for="current_password">Password saat ini</label>
                            <div class="pw-control">
                                <input type="password" class="pw-input @error('current_password') is-invalid @enderror"
                                    id="current_password" name="current_password" autocomplete="current-password" required
                                    @error('current_password') aria-invalid="true" aria-describedby="current_password-error" @enderror>
                                <button type="button" class="pw-eye" data-toggle-password="current_password" aria-label="Tampilkan password saat ini" aria-pressed="false">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            @error('current_password')<span class="pw-error" id="current_password-error">{{ $message }}</span>@enderror
                            @if ($isDefault)
                                <span class="pw-help">Password default Anda adalah username atau NIP.</span>
                            @endif
                        </div>

                        <div class="pw-field">
                            <label for="password">Password baru</label>
                            <div class="pw-control">
                                <input type="password" class="pw-input @error('password') is-invalid @enderror"
                                    id="password" name="password" autocomplete="new-password" required minlength="8" maxlength="20"
                                    aria-describedby="pw-rules @error('password') password-error @enderror"
                                    @error('password') aria-invalid="true" @enderror>
                                <button type="button" class="pw-eye" data-toggle-password="password" aria-label="Tampilkan password baru" aria-pressed="false">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            @error('password')<span class="pw-error" id="password-error">{{ $message }}</span>@enderror
                            <ul class="pw-rules" id="pw-rules" aria-label="Syarat password baru">
                                <li data-rule="length"><i class="fas fa-circle-minus" aria-hidden="true"></i> 8 sampai 20 karakter</li>
                                <li data-rule="upper"><i class="fas fa-circle-minus" aria-hidden="true"></i> Huruf besar (A-Z)</li>
                                <li data-rule="lower"><i class="fas fa-circle-minus" aria-hidden="true"></i> Huruf kecil (a-z)</li>
                                <li data-rule="number"><i class="fas fa-circle-minus" aria-hidden="true"></i> Angka (0-9)</li>
                                <li data-rule="symbol"><i class="fas fa-circle-minus" aria-hidden="true"></i> Simbol, mis. @ # ! ?</li>
                                <li data-rule="nospace"><i class="fas fa-circle-minus" aria-hidden="true"></i> Tanpa spasi</li>
                                <li data-rule="identity"><i class="fas fa-circle-minus" aria-hidden="true"></i> Bukan username{{ $nip ? ' atau NIP' : '' }}</li>
                                <li data-rule="different"><i class="fas fa-circle-minus" aria-hidden="true"></i> Beda dari password saat ini</li>
                            </ul>
                        </div>

                        <div class="pw-field">
                            <label for="password_confirmation">Ulangi password baru</label>
                            <div class="pw-control">
                                <input type="password" class="pw-input" id="password_confirmation" name="password_confirmation"
                                    autocomplete="new-password" required maxlength="20" aria-describedby="pw-match">
                                <button type="button" class="pw-eye" data-toggle-password="password_confirmation" aria-label="Tampilkan ulangan password baru" aria-pressed="false">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            <span class="pw-match" id="pw-match" aria-live="polite"></span>
                        </div>
                    </div>

                    <div class="pw-savebar">
                        <span class="pw-savebar-note" id="pw-status" aria-live="polite"></span>
                        <div class="pw-savebar-actions">
                            @if ($isDefault)
                                <button type="submit" form="logout-form" class="pr-btn">Keluar</button>
                            @else
                                <a href="{{ route('pages.feature-profile') }}" class="pr-btn">Batal</a>
                            @endif
                            <button type="submit" id="pw-submit" class="pr-btn pr-btn-primary"><i class="fas fa-check" aria-hidden="true"></i> Simpan password</button>
                        </div>
                    </div>
                </form>

                <aside class="pr-panel" aria-labelledby="pw-after-title">
                    <div class="pr-panel-head">
                        <h2 class="pr-panel-title" id="pw-after-title">Setelah disimpan</h2>
                    </div>
                    <ul class="pw-after">
                        <li>Anda tetap login di perangkat ini.</li>
                        <li>Perangkat lain yang login dengan akun ini, termasuk aplikasi mobile, dikeluarkan dan perlu login ulang.</li>
                        <li>Gunakan password baru untuk login berikutnya, di web maupun aplikasi.</li>
                    </ul>
                    <dl class="pw-account">
                        <dt>Username</dt><dd>{{ $user->username }}</dd>
                        @if ($nip)<dt>NIP</dt><dd>{{ $nip }}</dd>@endif
                    </dl>
                </aside>
            </div>

            @if ($isDefault)
                <form id="logout-form" action="{{ route('logout') }}" method="POST" hidden>@csrf</form>
            @endif
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function() {
            const form = document.getElementById('pw-form');
            const current = document.getElementById('current_password');
            const next = document.getElementById('password');
            const confirm = document.getElementById('password_confirmation');
            const match = document.getElementById('pw-match');
            const status = document.getElementById('pw-status');
            const identities = @json(array_values(array_filter([strtolower((string) $user->username), strtolower((string) ($nip ?? ''))])));

            // Sama dengan validasi di UserprofileController::updatePassword
            const rules = {
                length: v => v.length >= 8 && v.length <= 20,
                upper: v => /[A-Z]/.test(v),
                lower: v => /[a-z]/.test(v),
                number: v => /\d/.test(v),
                symbol: v => /[^A-Za-z0-9\s]/.test(v),
                nospace: v => v.length > 0 && !/\s/.test(v),
                identity: v => v.length > 0 && !identities.includes(v.toLowerCase()),
                different: v => v.length > 0 && v !== current.value,
            };
            const items = [...document.querySelectorAll('#pw-rules [data-rule]')];

            function check() {
                const v = next.value;
                let passed = 0;
                items.forEach(li => {
                    const ok = rules[li.dataset.rule](v);
                    if (ok) passed++;
                    li.classList.toggle('is-ok', ok);
                    li.classList.toggle('is-bad', !ok && v.length > 0);
                    li.querySelector('i').className = 'fas ' + (ok ? 'fa-circle-check' : (v.length ? 'fa-circle-xmark' : 'fa-circle-minus'));
                });
                const same = confirm.value.length > 0 && confirm.value === v;
                match.textContent = confirm.value.length ? (same ? 'Password sama.' : 'Belum sama dengan password baru.') : '';
                match.className = 'pw-match' + (confirm.value.length ? (same ? ' is-ok' : ' is-bad') : '');
                status.textContent = v.length ? `${passed} dari ${items.length} syarat terpenuhi` : '';
                return passed === items.length && same;
            }
            [current, next, confirm].forEach(el => el.addEventListener('input', check));
            check();

            document.querySelectorAll('[data-toggle-password]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const input = document.getElementById(btn.dataset.togglePassword);
                    const show = input.type === 'password';
                    input.type = show ? 'text' : 'password';
                    btn.setAttribute('aria-pressed', String(show));
                    btn.setAttribute('aria-label', btn.getAttribute('aria-label').replace(show ? 'Tampilkan' : 'Sembunyikan', show ? 'Sembunyikan' : 'Tampilkan'));
                    btn.querySelector('i').className = 'fa ' + (show ? 'fa-eye-slash' : 'fa-eye');
                });
            });

            form.addEventListener('submit', e => {
                if (!current.value) { e.preventDefault(); current.focus(); status.textContent = 'Isi password saat ini dulu.'; return; }
                if (!check()) {
                    e.preventDefault();
                    (confirm.value && confirm.value !== next.value ? confirm : next).focus();
                    status.textContent = 'Lengkapi syarat password baru dan pastikan ulangannya sama.';
                    return;
                }
                document.getElementById('pw-submit').disabled = true;
            });

            @if (session('success'))
                Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('success')) });
            @endif
            @if (session('error'))
                Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')) });
            @endif
        })();
    </script>
@endpush
