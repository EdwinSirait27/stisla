{{--
    Sidebar (layar lebar). Isinya ditentukan PERMISSION (Spatie) lewat $user->can(), sama seperti @can sebelumnya.
    - Grup yang tidak punya satu pun menu yang boleh diakses tidak ditampilkan.
    - Menu dengan tujuan sama (mis. Employee List dari 3 permission) hanya muncul sekali.
    - Menu aktif dihitung dari path URL, dan grupnya otomatis terbuka (Stisla membuka li.active).
    - Route bernama yang belum terdaftar dilewati, supaya satu route hilang tidak membuat semua halaman error.
    Tampilan: public/css/shell.css bagian "Sidebar".
--}}
@php
    /** @var \App\Models\User|null $sbUser */
    $sbUser = auth()->user();

    // Tujuan: string = url(path), ['route', 'nama.route'] = route(nama)
    // [label, tujuan, permission (salah satu cukup; [] = semua user login)]
    $sbSections = [
        null => [
            ['Profile', 'fa-user', [
                ['Profile', 'feature-profile', []],
                ['Roles & Responsibilities', 'rnr', []],
            ]],
            ['Dashboards', 'fa-house', [
                ['Dashboard', 'dashboardHuman', ['dashboardHuman']],
                ['Dashboard Manager', 'dashboardManager', ['dashboardManager']],
                ['Dashboard HR', 'dashboardHR', ['dashboardHR']],
                ['Dashboard', 'dashboardSupervisor', ['dashboardSupervisor']],
                ['Dashboard', 'dashboardDirector', ['dashboardDirector']],
                ['Dashboard', 'dashboardAdmin', ['dashboardAdmin']],
            ]],
        ],
        'People' => [
            ['Employee', 'fa-users', [
                ['Team List', 'Team', ['ManageTeam']],
                ['Overtime Rate', 'overtime-rate', ['ManageOvertimeRate']],
                ['Employee List', 'Employee', ['ManageEmployee', 'ManageEmployeeSPVManager', 'ViewEmployee']],
                ['Leaves List', ['route', 'leave.list.index'], ['leave.list.view', 'leave.list.view.manager', 'leave.list.view.human']],
            ]],
            ['Training', 'fa-graduation-cap', [
                ['Employee', 'employee-training', ['ManageEmployeeTraining']],
            ]],
            ['Contracts', 'fa-file-contract', [
                ['Employees Contracts', 'contract', ['ManageContracts']],
            ]],
            ['Documents', 'fa-folder-open', [
                ['Berita Acara', ['route', 'beritaacara.index'], ['CreateBeritaAcara']],
                ['Documents', 'document', ['ManageDocument']],
                ['Document Types', ['route', 'documenttype.index'], ['ManageDocumentType']],
                ['Document Settings', ['route', 'documentconfig.index'], ['ManageDocumentType']],
            ]],
            ['SK', 'fa-file-signature', [
                ['SK Type', 'Sktype', ['ManageSktypes']],
                ['SK Letters', 'SkLetters', ['ManageSktypes', 'ManageSkLetters']],
            ]],
        ],
        'Time & attendance' => [
            ['Attendance', 'fa-fingerprint', [
                ['Fingerprints Data', 'Fingerprints', ['ManageFingerspot', 'ManageFingerspotSPVManager', 'ViewFingerspot']],
                ['Fingerprint Recap', ['route', 'fingerprint-recap.index'], ['ManageFingerspot']],
                
            ]],
            ['Attendance Mobile', 'fa-mobile-screen', [
                ['Attendance Data', 'AttendanceMobile', ['ManageAttendanceMobile', 'ViewAttendanceMobile', 'ManageAttendanceMobileSPVManager']],
                ['Overtime Attendance', ['route', 'pages.OvertimeAttendanceMobile'], ['ManageOvertimeAttendanceMobile', 'ViewOvertimeAttendanceMobile', 'ManageOvertimeAttendanceMobileSPVManager']],
            ]],
            ['TOIL', 'fa-business-time', [
                ['Balance', ['route', 'toil.balance'], ['toil']],
                ['History', ['route', 'toil.history'], ['toil']],
                ['All Balances', ['route', 'toil.all-balances'], ['allbalances']],
                ['Overtime Assignment', ['route', 'toil.assignment.index'], ['assignment']],
                ['Approval', ['route', 'toil.approval.index'], ['assignment']],
                ['TOIL Setting', ['route', 'toilsetting.index'], ['ManageToilSetting']],
            ]],
        ],
        'Schedule' => [
            ['Roster', 'fa-database', [
               ['Roster & Schedule', ['route', 'roster.index'], ['ManageRoster', 'ManageRosterSPVManager', 'ViewRoster']],
                ['Roster Setting', ['route', 'rostersetting.index'], ['ManageRosterSetting']],
            ]],
            
        ],
        'Payroll' => [
            ['Payroll Fingerprint', 'fa-wallet', [
                ['Payroll Components', 'payrollcomponents', ['payroll.component.view']],
                ['Employee Salary', 'employee-salary', ['employee.salary.view']],
                ['Payroll Period', 'payroll-period', ['payroll.period.view']],
            ]],
            ['Payroll Absensi Mobile', 'fa-mobile-screen', [
                ['Payroll Period', ['route', 'payrollmobileperiod.index'], ['payroll.mobile.period.view']],
            ]],
        ],
        'Administration' => [
            ['Master Data', 'fa-database', [
                ['Banks', ['route', 'pages.Bank'], ['bank.view']],
                ['Companies', 'Company', ['ManageCompanies']],
                ['Positions', 'Position', ['ManagePositions']],
                ['Departments', 'Department', ['ManageDepartments']],
                ['Locations', 'Store', ['ManageStores']],
                ['Gradings', 'Grading', ['ManageGrading']],
                ['Public Holidays', 'Pubholi', ['ManagePH', 'ManageShiftSPVManager', 'ViewShifts', 'ManagePHSPVManager', 'ViewPH']],
                ['Shifts', 'Shifts', ['ManageShifts', 'ManageShiftSPVManager', 'ViewShifts', 'ManagePHSPVManager', 'ViewPH']],
                ['Leaves', 'Leavestype', ['ManageLeavestype']],
            ]],
            ['Users', 'fa-user-shield', [
                ['List Users', 'dashboardAdmin', ['dashboardAdmin']],
                ['Activity Logs', 'Activity', ['activity.view']],
                ['Roles', 'roles', ['dashboardAdmin']],
                ['Permission', 'permissions', ['dashboardAdmin']],
                ['Registered Device', ['route', 'pages.RegisteredDevice'], ['ManageRegisteredDevice', 'ViewRegisteredDevice', 'ManageRegisteredDeviceSPVManager']],
            ]],
        ],
    ];

    $sbAllowed = function (array $perms) use ($sbUser) {
        if (!$sbUser) return false;
        if (!$perms) return true;
        foreach ($perms as $p) {
            if ($sbUser->can($p)) return true;
        }
        return false;
    };

    // Angka penanda menu Berita Acara: menunggu persetujuan saya + ajuan saya yang perlu revisi
    $sbBadges = [];
    if ($sbUser?->employee_id) {
        try {
            $sbBadges['Berita Acara'] = \App\Models\DocumentApproval::where('employee_id', $sbUser->employee_id)->where('status', 'pending')->count()
                + \App\Models\Documents::where('employee_id', $sbUser->employee_id)->where('status', 'revision')->count();
        } catch (\Throwable $e) {
            // tabel belum dimigrasi
        }
    }

    $sbPath = '/' . ltrim(request()->path(), '/');
    $sbMenu = [];
    $sbOpened = false; // hanya satu grup yang terbuka, walau satu URL ada di dua grup
    foreach ($sbSections as $title => $groups) {
        foreach ($groups as [$groupLabel, $icon, $items]) {
            $links = [];
            $seen = [];
            $groupActive = false;
            foreach ($items as [$label, $target, $perms]) {
                if (!$sbAllowed($perms)) continue;
                if (is_array($target)) {
                    if (!\Illuminate\Support\Facades\Route::has($target[1])) continue;
                    $url = route($target[1]);
                } else {
                    $url = url($target);
                }
                if (isset($seen[$url])) continue;
                $seen[$url] = true;
                $path = '/' . ltrim((string) parse_url($url, PHP_URL_PATH), '/');
                $active = $path !== '/' && ($sbPath === $path || str_starts_with($sbPath, rtrim($path, '/') . '/'));
                $groupActive = $groupActive || $active;
                $badge = $sbBadges[$label] ?? 0;
                $links[] = compact('label', 'url', 'active', 'badge');
            }
            if (!$links) continue;
            $open = $groupActive && !$sbOpened;
            $sbOpened = $sbOpened || $open;
            $sbMenu[$title ?? ''][] = ['label' => $groupLabel, 'icon' => $icon, 'links' => $links, 'open' => $open];
        }
    }

    $sbHomeRoute = $sbUser ? \App\Services\DashboardRedirectService::routeNameFor($sbUser) : null;
    $sbHome = $sbHomeRoute && \Illuminate\Support\Facades\Route::has($sbHomeRoute) ? route($sbHomeRoute) : url('feature-profile');
@endphp

<div class="main-sidebar sidebar-style-2 sb">
    <aside id="sidebar-wrapper" aria-label="Menu utama">
        <div class="sidebar-brand sb-brand">
            <a href="{{ $sbHome }}" aria-label="Asian Bay Development, ke dashboard">
                <img src="{{ asset('img/AsianBay.png') }}" alt="Asian Bay Development" width="70">
            </a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm sb-brand">
            <a href="{{ $sbHome }}" aria-label="Asian Bay Development, ke dashboard">ABD</a>
        </div>

        <ul class="sidebar-menu">
            @foreach ($sbMenu as $title => $groups)
                @if ($title !== '')
                    <li class="menu-header">{{ $title }}</li>
                @endif
                @foreach ($groups as $group)
                    <li class="nav-item dropdown {{ $group['open'] ? 'active' : '' }}">
                        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown" aria-expanded="{{ $group['open'] ? 'true' : 'false' }}">
                            <i class="fas {{ $group['icon'] }}" aria-hidden="true"></i>
                            <span>{{ $group['label'] }}</span>
                        </a>
                        <ul class="dropdown-menu">
                            @foreach ($group['links'] as $link)
                                <li class="{{ $link['active'] ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ $link['url'] }}" @if ($link['active']) aria-current="page" @endif>{{ $link['label'] }}@if ($link['badge'])<span class="sb-badge" aria-label="{{ $link['badge'] }} perlu tindakan">{{ $link['badge'] }}</span>@endif</a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            @endforeach
        </ul>
    </aside>
</div>
