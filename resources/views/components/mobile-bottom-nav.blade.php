{{--
    Navigasi bawah untuk layar HP (di layar lebar sidebar yang dipakai).
    Semua menu ditentukan PERMISSION (Spatie), bukan nama role, dan isinya mengikuti components/sidebar.
    Menu dengan nama route yang tidak terdaftar dilewati, supaya satu route yang hilang tidak membuat semua halaman error.
--}}
@php
    /** @var \App\Models\User|null $navUser */
    $navUser = auth()->user();

    // [label, ikon, route name, permission (salah satu cukup; [] = semua user login)]
    $navSections = [
        'Dashboard' => [
            ['Dashboard HR', 'fa-chart-line', 'pages.dashboardHR', ['dashboardHR']],
            ['Dashboard manager', 'fa-chart-pie', 'pages.dashboardManager', ['dashboardManager']],
            ['Dashboard supervisor', 'fa-chart-column', 'pages.dashboardSupervisor', ['dashboardSupervisor']],
            ['Dashboard direktur', 'fa-chart-area', 'pages.dashboardDirector', ['dashboardDirector']],
            ['Dashboard saya', 'fa-house', 'pages.dashboardHuman', ['dashboardHuman']],
        ],
        'Karyawan' => [
            ['Daftar karyawan', 'fa-user-tie', 'pages.Employee', ['ManageEmployee', 'ManageEmployeeSPVManager', 'ViewEmployee']],
            ['Tim saya', 'fa-people-group', 'pages.Team', ['ManageTeam']],
            ['Daftar cuti', 'fa-plane-departure', 'leave.list.index', ['leave.list.view', 'leave.list.view.manager', 'leave.list.view.human']],
            ['Tarif lembur', 'fa-clock', 'overtime.rate', ['ManageOvertimeRate']],
            ['Training', 'fa-graduation-cap', 'pages.employee-training', ['ManageEmployeeTraining']],
        ],
        'Absensi' => [
            ['Fingerprint', 'fa-fingerprint', 'pages.Fingerprints', ['ManageFingerspot', 'ManageFingerspotSPVManager', 'ViewFingerspot']],
            ['Rekap fingerprint', 'fa-table-list', 'fingerprint-recap.index', ['ManageFingerspot']],
            ['Roster', 'fa-calendar-days', 'roster.index', ['ManageRoster', 'ManageRosterSPVManager', 'ViewRoster']],
            ['Pengaturan roster', 'fa-calendar-check', 'rostersetting.index', ['ManageRosterSetting']],
            ['Absensi mobile', 'fa-mobile-screen', 'pages.AttendanceMobile', ['ManageAttendanceMobile', 'ManageAttendanceMobileSPVManager', 'ViewAttendanceMobile']],
            ['Absensi lembur', 'fa-user-clock', 'pages.OvertimeAttendanceMobile', ['ManageOvertimeAttendanceMobile', 'ManageOvertimeAttendanceMobileSPVManager', 'ViewOvertimeAttendanceMobile']],
            ['Device terdaftar', 'fa-mobile-retro', 'pages.RegisteredDevice', ['ManageRegisteredDevice', 'ManageRegisteredDeviceSPVManager', 'ViewRegisteredDevice']],
        ],
        'TOIL' => [
            ['Saldo TOIL saya', 'fa-hourglass-half', 'toil.balance', ['toil']],
            ['Riwayat TOIL', 'fa-clock-rotate-left', 'toil.history', ['toil']],
            ['Semua saldo TOIL', 'fa-scale-balanced', 'toil.all-balances', ['allbalances']],
            ['Penugasan lembur', 'fa-list-check', 'toil.assignment.index', ['assignment']],
            ['Persetujuan TOIL', 'fa-circle-check', 'toil.approval.index', ['assignment']],
            ['Pengaturan TOIL', 'fa-sliders', 'toilsetting.index', ['ManageToilSetting']],
        ],
        'Dokumen' => [
            ['Kontrak', 'fa-file-contract', 'contract', ['ManageContracts']],
            ['Dokumen', 'fa-folder-open', 'document.index', ['ManageDocument']],
            ['Berita Acara', 'fa-folder-open', 'beritaacara.index', ['CreateBeritaAcara']],
            ['Jenis SK', 'fa-tags', 'pages.Sktype', ['ManageSktypes']],
            ['Surat SK', 'fa-file-signature', 'SkLetters', ['ManageSkLetters']],
        ],
        'Payroll' => [
            ['Periode payroll', 'fa-calendar-check', 'payrollperiod.index', ['payroll.period.view']],
            ['Gaji karyawan', 'fa-money-bill-wave', 'employeesalary.index', ['employee.salary.view']],
            ['Komponen payroll', 'fa-puzzle-piece', 'payrollcomponents', ['payroll.component.view']],
        ],
        'Master data' => [
            ['Perusahaan', 'fa-building', 'pages.Company', ['ManageCompanies']],
            ['Lokasi', 'fa-store', 'pages.Store', ['ManageStores']],
            ['Departemen', 'fa-sitemap', 'pages.Department', ['ManageDepartments']],
            ['Jabatan', 'fa-briefcase', 'pages.Position', ['ManagePositions']],
            ['Grading', 'fa-layer-group', 'pages.Grading', ['ManageGrading']],
            ['Shift', 'fa-business-time', 'pages.Shifts', ['ManageShifts', 'ManageShiftsSPVManager', 'ViewShifts']],
            ['Hari libur', 'fa-umbrella-beach', 'pages.Pubholi', ['ManagePH', 'ManagePHSPVManager', 'ViewPH']],
            ['Jenis cuti', 'fa-calendar-minus', 'pages.Leavestype', ['ManageLeavestype']],
            ['Bank', 'fa-building-columns', 'pages.Bank', ['bank.view']],
        ],
        'Pengguna' => [
            ['Akun pengguna', 'fa-users-gear', 'pages.dashboardAdmin', ['dashboardAdmin']],
            ['Role', 'fa-user-shield', 'roles.index', ['dashboardAdmin']],
            ['Permission', 'fa-key', 'permissions.index', ['dashboardAdmin']],
            ['Log aktivitas', 'fa-list-ul', 'pages.Activity', ['activity.view']],
        ],
        'Akun' => [
            ['Profil', 'fa-user', 'pages.feature-profile', []],
            ['Roles & responsibilities', 'fa-award', 'pages.rnr', []],
            ['Ganti password', 'fa-lock', 'pages.change-password', []],
        ],
    ];

    $navAllowed = function (array $perms) use ($navUser) {
        if (!$navUser) return false;
        if (!$perms) return true;
        foreach ($perms as $p) {
            if ($navUser->checkPermissionTo($p)) return true; // false (bukan exception) kalau permission belum terdaftar
        }
        return false;
    };

    $navPath = '/' . ltrim(request()->path(), '/');
    $navMenu = [];
    $navActiveLabel = null;
    foreach ($navSections as $title => $items) {
        $seen = [];
        foreach ($items as [$label, $icon, $route, $perms]) {
            if (!\Illuminate\Support\Facades\Route::has($route) || !$navAllowed($perms)) continue;
            $url = route($route);
            if (isset($seen[$url])) continue;
            $seen[$url] = true;
            $path = '/' . ltrim((string) parse_url($url, PHP_URL_PATH), '/');
            $active = $path !== '/' && ($navPath === $path || str_starts_with($navPath . '/', rtrim($path, '/') . '/'));
            if ($active && !$navActiveLabel) $navActiveLabel = $label;
            $navMenu[$title][] = compact('label', 'icon', 'url', 'active', 'route');
        }
    }

    // Beranda = dashboard yang sama dengan tujuan setelah login
    $navHomeRoute = $navUser ? \App\Services\DashboardRedirectService::routeNameFor($navUser) : null;
    $navHome = $navHomeRoute && \Illuminate\Support\Facades\Route::has($navHomeRoute) ? route($navHomeRoute) : route('pages.feature-profile');
    $navHomePath = '/' . ltrim((string) parse_url($navHome, PHP_URL_PATH), '/');

    // Tiga pintasan: menu pertama yang boleh diakses dari urutan prioritas ini
    $navShortLabels = [
        'pages.Employee' => 'Karyawan', 'pages.Fingerprints' => 'Fingerprint', 'roster.index' => 'Roster',
        'leave.list.index' => 'Cuti', 'pages.AttendanceMobile' => 'Absensi', 'pages.OvertimeAttendanceMobile' => 'Lembur', 'pages.RegisteredDevice' => 'Device', 'toil.balance' => 'TOIL',
        'payrollperiod.index' => 'Payroll', 'contract' => 'Kontrak', 'pages.Team' => 'Tim',
    ];
    $navShortcuts = [];
    foreach (array_keys($navShortLabels) as $r) {
        foreach ($navMenu as $items) {
            foreach ($items as $it) {
                if ($it['route'] === $r && count($navShortcuts) < 3 && $it['url'] !== $navHome) {
                    $navShortcuts[] = $it + ['short' => $navShortLabels[$r]];
                    continue 3;
                }
            }
        }
    }
    $navMenuActive = $navActiveLabel && !collect($navShortcuts)->contains('active', true) && $navPath !== $navHomePath;
    $navCount = collect($navMenu)->except('Akun')->flatten(1)->count();
@endphp

@if ($navUser)
<style>
    /* Navigasi bawah HP · permission-based · tokens below; colours only through them */
    :root {
        --mbn-bg: oklch(100% 0.003 265);
        --mbn-ink: oklch(27% 0.03 265);
        --mbn-ink-2: oklch(45% 0.03 265);
        --mbn-muted: oklch(58% 0.025 265);
        --mbn-rule: oklch(91.5% 0.008 265);
        --mbn-soft: oklch(96.6% 0.006 265);
        --mbn-accent: oklch(52% 0.18 276);
        --mbn-accent-soft: oklch(95% 0.03 276);
        --mbn-danger: oklch(52% 0.18 25);
        --mbn-danger-soft: oklch(96% 0.03 25);
        --mbn-shade: oklch(20% 0.02 265 / 0.45);
        --mbn-shadow: oklch(20% 0.02 265 / 0.08);
        --mbn-focus: oklch(61% 0.17 276);
    }
    .mbn-bar { display: none; }
    @media (max-width: 768px) {
        .mbn-bar {
            position: fixed; left: 0; right: 0; bottom: 0; z-index: 1030;
            display: grid; grid-auto-flow: column; grid-auto-columns: minmax(0, 1fr);
            padding: 4px 6px calc(4px + env(safe-area-inset-bottom));
            background: var(--mbn-bg); border-top: 1px solid var(--mbn-rule); box-shadow: 0 -2px 12px var(--mbn-shadow);
        }
    }
    .mbn-item {
        display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px;
        min-height: 52px; padding: 6px 2px; border: 0; border-radius: 12px; background: none;
        font: inherit; font-size: 11px; font-weight: 600; line-height: 1.1; color: var(--mbn-muted);
        text-decoration: none; cursor: pointer; -webkit-tap-highlight-color: transparent;
        transition: color 0.15s ease, background-color 0.15s ease;
    }
    .mbn-item i { font-size: 19px; line-height: 1; }
    .mbn-item span { max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .mbn-item:hover { color: var(--mbn-ink); text-decoration: none; }
    .mbn-item.is-active, .mbn-item[aria-expanded="true"] { color: var(--mbn-accent); background: var(--mbn-accent-soft); }
    .mbn-item:focus-visible { outline: 2px solid var(--mbn-focus); outline-offset: -2px; }

    .mbn-shade { position: fixed; inset: 0; z-index: 1040; background: var(--mbn-shade); opacity: 0; pointer-events: none; transition: opacity 0.2s ease; }
    .mbn-shade.is-open { opacity: 1; pointer-events: auto; }
    .mbn-sheet {
        position: fixed; left: 0; right: 0; bottom: 0; z-index: 1050;
        display: flex; flex-direction: column; max-height: 82vh;
        background: var(--mbn-bg); border-radius: 18px 18px 0 0; box-shadow: 0 -6px 24px var(--mbn-shadow);
        transform: translateY(105%); visibility: hidden;
        transition: transform 0.28s cubic-bezier(0.32, 0.72, 0, 1), visibility 0s linear 0.28s;
    }
    .mbn-sheet.is-open { transform: translateY(0); visibility: visible; transition: transform 0.28s cubic-bezier(0.32, 0.72, 0, 1); }
    .mbn-grip { width: 40px; height: 4px; margin: 10px auto 4px; border-radius: 2px; background: var(--mbn-rule); flex-shrink: 0; }
    .mbn-head { display: flex; align-items: center; gap: 12px; padding: 6px 16px 10px; border-bottom: 1px solid var(--mbn-rule); }
    .mbn-head-text { flex: 1; min-width: 0; }
    .mbn-head strong { display: block; font-size: 15px; color: var(--mbn-ink); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .mbn-head span { font-size: 12px; color: var(--mbn-ink-2); }
    .mbn-close { display: grid; place-items: center; width: 40px; height: 40px; border: 0; border-radius: 50%; background: var(--mbn-soft); color: var(--mbn-ink-2); cursor: pointer; }
    .mbn-close:focus-visible, .mbn-link:focus-visible, .mbn-search input:focus-visible, .mbn-logout:focus-visible { outline: 2px solid var(--mbn-focus); outline-offset: 2px; }
    .mbn-search { padding: 10px 16px 4px; }
    .mbn-search input { width: 100%; min-height: 40px; padding: 8px 12px; border: 1px solid var(--mbn-rule); border-radius: 10px; background: var(--mbn-soft); font: inherit; font-size: 15px; color: var(--mbn-ink); }
    .mbn-body { overflow-y: auto; overscroll-behavior: contain; -webkit-overflow-scrolling: touch; padding: 4px 12px calc(16px + env(safe-area-inset-bottom)); }
    .mbn-group { margin: 0; padding: 10px 4px 0; }
    .mbn-group h3 { margin: 0 4px 6px; font-size: 13px; font-weight: 700; color: var(--mbn-ink-2); }
    .mbn-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px; margin: 0; padding: 0; list-style: none; }
    .mbn-link {
        display: flex; align-items: center; gap: 10px; min-height: 48px; padding: 8px 10px;
        border: 1px solid var(--mbn-rule); border-radius: 12px; background: var(--mbn-bg);
        font-size: 14px; line-height: 1.25; color: var(--mbn-ink); text-decoration: none;
        transition: background-color 0.12s ease, border-color 0.12s ease;
    }
    .mbn-link i { width: 20px; text-align: center; font-size: 16px; color: var(--mbn-accent); flex-shrink: 0; }
    .mbn-link:hover, .mbn-link:active { background: var(--mbn-soft); color: var(--mbn-ink); text-decoration: none; }
    .mbn-link.is-active { border-color: var(--mbn-accent); background: var(--mbn-accent-soft); font-weight: 700; }
    .mbn-empty { margin: 16px 4px; font-size: 14px; color: var(--mbn-muted); }
    .mbn-logout {
        display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; min-height: 48px; margin-top: 16px;
        border: 1px solid var(--mbn-rule); border-radius: 12px; background: var(--mbn-danger-soft);
        font: inherit; font-size: 15px; font-weight: 700; color: var(--mbn-danger); cursor: pointer;
    }
    @media (min-width: 769px) { .mbn-bar, .mbn-sheet, .mbn-shade { display: none !important; } }
    @media (prefers-reduced-motion: reduce) { .mbn-sheet, .mbn-shade, .mbn-item, .mbn-link { transition: none; } }
</style>

<nav class="mbn-bar" aria-label="Navigasi utama">
    <a href="{{ $navHome }}" class="mbn-item {{ $navPath === $navHomePath ? 'is-active' : '' }}" @if ($navPath === $navHomePath) aria-current="page" @endif>
        <i class="fas fa-house" aria-hidden="true"></i><span>Beranda</span>
    </a>
    @foreach ($navShortcuts as $s)
        <a href="{{ $s['url'] }}" class="mbn-item {{ $s['active'] ? 'is-active' : '' }}" @if ($s['active']) aria-current="page" @endif>
            <i class="fas {{ $s['icon'] }}" aria-hidden="true"></i><span>{{ $s['short'] }}</span>
        </a>
    @endforeach
    <button type="button" class="mbn-item {{ $navMenuActive ? 'is-active' : '' }}" id="mbnMenuBtn" aria-haspopup="dialog" aria-controls="mbnSheet" aria-expanded="false">
        <i class="fas fa-grip" aria-hidden="true"></i><span>Menu</span>
    </button>
</nav>

<div class="mbn-shade" id="mbnShade" aria-hidden="true"></div>
<div class="mbn-sheet" id="mbnSheet" role="dialog" aria-modal="true" aria-labelledby="mbnTitle">
    <div class="mbn-grip" aria-hidden="true"></div>
    <div class="mbn-head">
        <div class="mbn-head-text">
            <strong id="mbnTitle">{{ $navUser->employee->employee_name ?? $navUser->username }}</strong>
            <span>{{ $navCount }} menu sesuai akses Anda{{ $navActiveLabel ? ', sedang di ' . $navActiveLabel : '' }}</span>
        </div>
        <button type="button" class="mbn-close" id="mbnClose" aria-label="Tutup menu"><i class="fas fa-xmark" aria-hidden="true"></i></button>
    </div>
    @if ($navCount > 8)
        <div class="mbn-search">
            <label for="mbnFind" class="sr-only">Cari menu</label>
            <input type="search" id="mbnFind" placeholder="Cari menu" autocomplete="off">
        </div>
    @endif
    <div class="mbn-body">
        @foreach ($navMenu as $title => $items)
            <section class="mbn-group" aria-label="{{ $title }}">
                <h3>{{ $title }}</h3>
                <ul class="mbn-grid">
                    @foreach ($items as $it)
                        <li><a href="{{ $it['url'] }}" class="mbn-link {{ $it['active'] ? 'is-active' : '' }}" data-find="{{ strtolower($title . ' ' . $it['label']) }}" @if ($it['active']) aria-current="page" @endif>
                            <i class="fas {{ $it['icon'] }}" aria-hidden="true"></i><span>{{ $it['label'] }}</span></a></li>
                    @endforeach
                </ul>
            </section>
        @endforeach
        <p class="mbn-empty" id="mbnNone" hidden>Tidak ada menu yang cocok.</p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="mbn-logout"><i class="fas fa-right-from-bracket" aria-hidden="true"></i> Keluar</button>
        </form>
    </div>
</div>

<script>
    (function() {
        const btn = document.getElementById('mbnMenuBtn'), sheet = document.getElementById('mbnSheet'), shade = document.getElementById('mbnShade');
        const find = document.getElementById('mbnFind');
        let lastFocus = null;

        function open() {
            lastFocus = document.activeElement;
            sheet.classList.add('is-open'); shade.classList.add('is-open');
            btn.setAttribute('aria-expanded', 'true'); document.body.style.overflow = 'hidden';
            setTimeout(() => (sheet.querySelector('.mbn-link.is-active') || document.getElementById('mbnClose')).focus({ preventScroll: false }), 50);
        }
        function close() {
            if (!sheet.classList.contains('is-open')) return;
            sheet.classList.remove('is-open'); shade.classList.remove('is-open');
            btn.setAttribute('aria-expanded', 'false'); document.body.style.overflow = '';
            (lastFocus || btn).focus();
        }
        btn.addEventListener('click', () => sheet.classList.contains('is-open') ? close() : open());
        shade.addEventListener('click', close);
        document.getElementById('mbnClose').addEventListener('click', close);
        document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });

        // Geser ke bawah dari pegangan/kepala untuk menutup
        let startY = null;
        sheet.addEventListener('touchstart', e => { startY = e.target.closest('.mbn-body') ? null : e.touches[0].clientY; }, { passive: true });
        sheet.addEventListener('touchend', e => { if (startY !== null && e.changedTouches[0].clientY - startY > 70) close(); startY = null; }, { passive: true });

        if (find) find.addEventListener('input', () => {
            const q = find.value.trim().toLowerCase();
            let any = false;
            sheet.querySelectorAll('.mbn-group').forEach(g => {
                let shown = 0;
                g.querySelectorAll('.mbn-link').forEach(a => { const ok = !q || a.dataset.find.includes(q); a.parentElement.hidden = !ok; if (ok) shown++; });
                g.hidden = shown === 0; if (shown) any = true;
            });
            document.getElementById('mbnNone').hidden = any;
        });
    })();
</script>
@endif
