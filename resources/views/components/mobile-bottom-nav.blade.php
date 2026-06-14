<style>
   
    /* .mobile-drawer-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0);
        z-index: 1040;
        transition: background 0.25s ease;
    }

    .mobile-drawer-overlay.open {
        display: block;
        background: rgba(0, 0, 0, 0.45);
    }

    .mobile-bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: 64px;
        background: #ffffff;
        border-top: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-around;
        z-index: 1030;
        box-shadow: 0 -2px 12px rgba(0, 0, 0, 0.07);
        padding-bottom: env(safe-area-inset-bottom);
    }

    .mobile-bottom-nav .nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 3px;
        padding: 8px 16px;
        min-width: 56px;
        min-height: 44px;
        border: none;
        background: none;
        color: #6b7280;
        font-size: 10px;
        font-weight: 500;
        border-radius: 10px;
        cursor: pointer;
        transition: color 0.15s ease, background 0.15s ease;
        -webkit-tap-highlight-color: transparent;
    }

    .mobile-bottom-nav .nav-item i {
        font-size: 20px;
        line-height: 1;
    }

    .mobile-bottom-nav .nav-item:hover,
    .mobile-bottom-nav .nav-item.active {
        color: #4f46e5;
        background: #ede9fe;
    }

    .mobile-more-drawer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #ffffff;
        border-radius: 20px 20px 0 0;
        z-index: 1050;
        max-height: 75vh;
        overflow-y: auto;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
        padding-bottom: calc(16px + env(safe-area-inset-bottom));

        transform: translateY(100%);
        transition: transform 0.3s cubic-bezier(0.32, 0.72, 0, 1);
    }

    .mobile-more-drawer.open {
        transform: translateY(0);
    }

    .drawer-handle {
        width: 40px;
        height: 4px;
        background: #d1d5db;
        border-radius: 2px;
        margin: 12px auto 6px;
        flex-shrink: 0;
    }

    .drawer-section-title {
        font-size: 11px;
        font-weight: 600;
        color: #9ca3af;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        padding: 12px 20px 4px;
    }

    .drawer-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 13px 20px;
        font-size: 15px;
        color: #111827;
        text-decoration: none;
        transition: background 0.12s ease;
        -webkit-tap-highlight-color: transparent;
    }

    .drawer-item:hover,
    .drawer-item:active {
        background: #f3f4f6;
        color: #111827;
        text-decoration: none;
    }

    .drawer-item i {
        font-size: 18px;
        color: #6b7280;
        width: 22px;
        text-align: center;
        flex-shrink: 0;
    } */
      .mobile-drawer-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0);
        z-index: 1040;
        transition: background 0.25s ease;
    }

    .mobile-drawer-overlay.open {
        display: block;
        background: rgba(0, 0, 0, 0.45);
    }

 .mobile-bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 64px;
    background: #ffffff;
    border-top: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: flex-start; /* ← bukan space-around */
    z-index: 1030;
    box-shadow: 0 -2px 12px rgba(0, 0, 0, 0.07);
    padding-bottom: env(safe-area-inset-bottom);

    overflow-x: auto;
    overflow-y: hidden;
    scroll-snap-type: x mandatory;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}

.mobile-bottom-nav::-webkit-scrollbar {
    display: none;
}


   .mobile-bottom-nav .nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 3px;
    padding: 8px 0;
    width: 25vw;      /* ← tepat 1/4 layar, jadi 4 item = penuh */
    min-width: 25vw;  /* ← jangan sampai menyusut */
    min-height: 44px;
    border: none;
    background: none;
    color: #6b7280;
    font-size: 10px;
    font-weight: 500;
    border-radius: 10px;
    cursor: pointer;
    transition: color 0.15s ease, background 0.15s ease;
    -webkit-tap-highlight-color: transparent;
    flex-shrink: 0;
    scroll-snap-align: start;
}

    .mobile-bottom-nav .nav-item i {
        font-size: 20px;
        line-height: 1;
    }

    .mobile-bottom-nav .nav-item:hover,
    .mobile-bottom-nav .nav-item.active {
        color: #4f46e5;
        background: #ede9fe;
    }

    .mobile-more-drawer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #ffffff;
        border-radius: 20px 20px 0 0;
        z-index: 1050;
        max-height: 75vh;
        overflow-y: auto;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
        padding-bottom: calc(16px + env(safe-area-inset-bottom));

        transform: translateY(100%);
        transition: transform 0.3s cubic-bezier(0.32, 0.72, 0, 1);
    }

    .mobile-more-drawer.open {
        transform: translateY(0);
    }

    .drawer-handle {
        width: 40px;
        height: 4px;
        background: #d1d5db;
        border-radius: 2px;
        margin: 12px auto 6px;
        flex-shrink: 0;
    }

    .drawer-section-title {
        font-size: 11px;
        font-weight: 600;
        color: #9ca3af;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        padding: 12px 20px 4px;
    }

    .drawer-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 13px 20px;
        font-size: 15px;
        color: #111827;
        text-decoration: none;
        transition: background 0.12s ease;
        -webkit-tap-highlight-color: transparent;
    }

    .drawer-item:hover,
    .drawer-item:active {
        background: #f3f4f6;
        color: #111827;
        text-decoration: none;
    }

    .drawer-item i {
        font-size: 18px;
        color: #6b7280;
        width: 22px;
        text-align: center;
        flex-shrink: 0;
    }
</style>
<div class="mobile-drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>

<nav class="mobile-bottom-nav" aria-label="Mobile navigation">
    <button class="nav-item" onclick="openDrawerProfile()" type="button" aria-label="Profile Menu">
        <i class="fas fa-user"></i>
        <span>Profile</span>
    </button>
    @can('dashboardAdmin')
        <button class="nav-item" onclick="openDrawerUsers()" type="button" aria-label="Users">
            <i class="fas fa-lock"></i>
            <span>Users</span>
        </button>
        <a href="{{ route('pages.dashboardAdmin') }}" class="nav-item" aria-label="Home">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
      
    @endcan
    @can('DashboardSupervisor')
        <a href="{{ route('pages.dashboardSupervisor') }}" class="nav-item" aria-label="Home">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
    @endcan
    @can('dashboardSupervisor')
        <a href="{{ route('pages.dashboardSupervisor') }}" class="nav-item" aria-label="Home">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
    @endcan
    @can('ManageEmployee')
     <button class="nav-item" onclick="openDrawerEmployee()" type="button" aria-label="Users">
            <i class="fas fa-user-tie"></i>
            <span>Employee</span>
        </button>
    @endcan
    @role('HeadHR|HR')
     <button class="nav-item" onclick="openDrawerMasterData()" type="button" aria-label="Master Data">
            <i class="fas fa-database"></i>
            <span>Master Data</span>
        </button>
    @endrole
    @can('ManageContracts')
      <button class="nav-item" onclick="openDrawerContract()" type="button" aria-label="Contract">
            <i class="fas fa-building"></i>
            <span>Contract</span>
        </button>
    @endcan
    @role('HeadHR')
     <button class="nav-item" onclick="openDrawerSK()" type="button" aria-label="SK">
            <i class="fas fa-file-signature"></i>
            <span>SK</span>
        </button>
    @endrole
    @role('HeadHR')
     <button class="nav-item" onclick="openDrawerST()" type="button" aria-label="ST">
            <i class="fas fa-file-signature"></i>
            <span>ST</span>
        </button>
    @endrole
    @role('HeadHR')
     <button class="nav-item" onclick="openDrawerPayroll()" type="button" aria-label="Payroll">
            <i class="fas fa-file-signature"></i>
            <span>Payroll</span>
        </button>
    @endrole
      @can('ManageFingerspot')
      <button class="nav-item" onclick="openDrawerAttendance()" type="button" aria-label="Attendance">
            <i class="fas fa-calendar-check"></i>
            <span>Attendance</span>
        </button>
    @endcan
    @role('HR')
        <a href="{{ route('pages.dashboardHR') }}" class="nav-item" aria-label="Home">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        
    @endrole
    @role('HeadHR')
      <button class="nav-item" onclick="openDrawerDashboardManagerHR()" type="button" aria-label="Dashboard Manager HR">
            <i class="fas fa-lock"></i>
            <span>Home</span>
        </button>
        @endrole
    @can('dashboardHuman')
        <a href="{{ route('pages.dashboardHuman') }}" class="nav-item" aria-label="Home">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
    @endcan
    @can('dashboardManager')
       <button class="nav-item" onclick="openDrawerDashboardManager()" type="button" aria-label="Dashboard Manager">
            <i class="fas fa-lock"></i>
            <span>Home</span>
        </button>
    @endcan
    
    @can('dashboardDirector')
       <a href="{{ route('pages.dashboardDirector') }}" class="nav-item" aria-label="Home">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
    @endcan
</nav>

<div class="mobile-more-drawer" id="moreDrawerProfile" role="dialog" aria-modal="true" aria-label="Profile Menu">
    <div class="drawer-handle"></div>
    <div class="drawer-section-title">Profile Menu</div>
    <a href="{{ route('pages.feature-profile') }}" class="drawer-item">
        <i class="fas fa-user"></i> Profile
    </a>
    <a href="{{ route('pages.rnr') }}" class="drawer-item">
        <i class="fas fa-award"></i> Roles &amp; Responsibilities
    </a>
    <a href="{{ route('pages.change-password') }}" class="drawer-item">
        <i class="fas fa-key"></i> Change Password
    </a>
    <div class="drawer-section-title">Account</div>
</div>

{{-- Drawer Users: ada tombolnya, harus ada divnya --}}
    {{-- @can('dashboardAdmin') --}}

<div class="mobile-more-drawer" id="moreDrawerUsers" role="dialog" aria-modal="true" aria-label="Users">
    <div class="drawer-handle"></div>
    <div class="drawer-section-title">Users</div>
    @can('dashboardAdmin')

        <a href="{{ route('pages.dashboardAdmin') }}" class="drawer-item">
            <i class="fas fa-tachometer-alt"></i> Users
        </a>
        <a href="{{ route('roles.index') }}" class="drawer-item">
            <i class="fas fa-shield-alt"></i> Roles
        </a>
        <a href="{{ route('permissions.index') }}" class="drawer-item">
            <i class="fas fa-key"></i> Permissions
        </a>
        @endcan
    </div>
<div class="mobile-more-drawer" id="moreDrawerEmployee" role="dialog" aria-modal="true" aria-label="Employee">
    <div class="drawer-handle"></div>
    <div class="drawer-section-title">Employee</div>
    @can('ManageEmployee')
        <a href="{{ route('pages.Employee') }}" class="drawer-item">
            <i class="fas fa-tachometer-alt"></i> Employee
        </a>
        @endcan
    @can('RequestPosition')
        <a href="{{ route('pages.Positionrequest') }}" class="drawer-item">
            <i class="fas fa-tachometer-alt"></i> Position Request
        </a>
        @endcan
    @can('RequestPositionList')
        <a href="{{ route('pages.Positionreqlist') }}" class="drawer-item">
            <i class="fas fa-tachometer-alt"></i> Position Request List
        </a>
        @endcan
    
    @can('ManageSummaries')
        <a href="{{ route('pages.Summaries') }}" class="drawer-item">
            <i class="fas fa-tachometer-alt"></i> Leave List
        </a>
        @endcan
    </div>
<div class="mobile-more-drawer" id="moreDrawerMasterData" role="dialog" aria-modal="true" aria-label="Master Data">
    <div class="drawer-handle"></div>
    <div class="drawer-section-title">Master Data</div>
    @can('ManageBanks')
        <a href="{{ route('pages.Banks') }}" class="drawer-item">
            <i class="fas fa-tachometer-alt"></i> Banks
        </a>
        @endcan
    @can('ManageCompanies')
        <a href="{{ route('pages.Company') }}" class="drawer-item">
            <i class="fas fa-tachometer-alt"></i> Companies
        </a>
        @endcan
    @can('ManagePositions')
        <a href="{{ route('pages.Position') }}" class="drawer-item">
            <i class="fas fa-tachometer-alt"></i> Positions
        </a>
        @endcan
    @can('ManageDepartments')
        <a href="{{ route('pages.Department') }}" class="drawer-item">
            <i class="fas fa-tachometer-alt"></i> Departments
        </a>
        @endcan
    @can('ManageStores')
        <a href="{{ route('pages.Store') }}" class="drawer-item">
            <i class="fas fa-tachometer-alt"></i> Locations
        </a>
        @endcan
    @can('ManageGrading')
        <a href="{{ route('pages.Grading') }}" class="drawer-item">
            <i class="fas fa-tachometer-alt"></i> Gradings
        </a>
        @endcan
    @can('ManagePH')
        <a href="{{ route('pages.Pubholi') }}" class="drawer-item">
            <i class="fas fa-tachometer-alt"></i> PH
        </a>
        @endcan
    @can('ManageShifts')
        <a href="{{ route('pages.Shifts') }}" class="drawer-item">
            <i class="fas fa-tachometer-alt"></i> Shifts
        </a>
        @endcan
    </div>
<div class="mobile-more-drawer" id="moreDrawerContract" role="dialog" aria-modal="true" aria-label="Contract">
    <div class="drawer-handle"></div>
    <div class="drawer-section-title">Contract</div>
    @can('ManageContracts')
        <a href="{{ route('contract') }}" class="drawer-item">
            <i class="fas fa-tachometer-alt"></i> Contract List
        </a>
        @endcan
    </div>
<div class="mobile-more-drawer" id="moreDrawerSK" role="dialog" aria-modal="true" aria-label="SK">
    <div class="drawer-handle"></div>
    <div class="drawer-section-title">SK</div>
    @can('ManageSktypes')
        <a href="{{ route('pages.Sktype') }}" class="drawer-item">
            <i class="fas fa-tachometer-alt"></i> SK Types
        </a>
        @endcan
    @can('ManageSkLetters')
        <a href="{{ route('SkLetters') }}" class="drawer-item">
            <i class="fas fa-tachometer-alt"></i> SK Letter
        </a>
        @endcan
    </div>
<div class="mobile-more-drawer" id="moreDrawerST" role="dialog" aria-modal="true" aria-label="ST">
    <div class="drawer-handle"></div>
    <div class="drawer-section-title">ST</div>
    
    @can('ManageStLetters')
        <a href="{{ route('SkLetters') }}" class="drawer-item">
            <i class="fas fa-tachometer-alt"></i> ST Letter
        </a>
        @endcan
    </div>
<div class="mobile-more-drawer" id="moreDrawerDashboardManager" role="dialog" aria-modal="true" aria-label="Dashboard Manager">
    <div class="drawer-handle"></div>
    <div class="drawer-section-title">Home</div>
    @can('dashboardManager')
        <a href="{{ route('pages.dashboardTeam') }}" class="drawer-item">
            <i class="fas fa-tachometer-alt"></i> Dashboard Team
        </a>
        <a href="{{ route('pages.dashboardManager') }}" class="drawer-item">
            <i class="fas fa-shield-alt"></i> Dashboard Manager
        </a>
    @endcan
    </div>
<div class="mobile-more-drawer" id="moreDrawerDashboardManagerHR" role="dialog" aria-modal="true" aria-label="Dashboard Manager HR">
    <div class="drawer-handle"></div>
    <div class="drawer-section-title">Home</div>
    @role('HeadHR')
        <a href="{{ route('pages.dashboardTeam') }}" class="drawer-item">
            <i class="fas fa-tachometer-alt"></i> Dashboard Team
        </a>
        <a href="{{ route('pages.dashboardManager') }}" class="drawer-item">
            <i class="fas fa-shield-alt"></i> Dashboard Manager
        </a>
        <a href="{{ route('pages.dashboardHR') }}" class="drawer-item">
            <i class="fas fa-shield-alt"></i> Dashboard HR
        </a>
    @endrole
    </div>
<script>
    // Daftarkan SEMUA id drawer yang ada di HTML
    const ALL_DRAWERS = ['moreDrawerProfile', 'moreDrawerUsers','moreDrawerEmployee','moreDrawerMasterData','moreDrawerContract','moreDrawerSK','moreDrawerST','moreDrawerDashboardManager','moreDrawerDashboardManagerHR'];

    function closeAllDrawers() {
        ALL_DRAWERS.forEach(id => document.getElementById(id).classList.remove('open'));
        document.getElementById('drawerOverlay').classList.remove('open');
        document.body.style.overflow = '';
    }

    function openDrawerById(id) {
        closeAllDrawers();
        document.getElementById(id).classList.add('open');
        document.getElementById('drawerOverlay').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function openDrawerProfile() { openDrawerById('moreDrawerProfile'); }
    function openDrawerUsers()   { openDrawerById('moreDrawerUsers'); }
    function openDrawerEmployee()   { openDrawerById('moreDrawerEmployee'); }
    function openDrawerMasterData()   { openDrawerById('moreDrawerMasterData'); }
    function openDrawerContract()   { openDrawerById('moreDrawerContract'); }
    function openDrawerSK()   { openDrawerById('moreDrawerSK'); }
    function openDrawerST()   { openDrawerById('moreDrawerST'); }
    function openDrawerDashboardManager() { openDrawerById('moreDrawerDashboardManager'); }
    function openDrawerDashboardManagerHR() { openDrawerById('moreDrawerDashboardManagerHR'); }
    function closeDrawer()       { closeAllDrawers(); }

    // Swipe down
    document.querySelectorAll('.mobile-more-drawer').forEach(drawer => {
        let startY = 0;
        drawer.addEventListener('touchstart', e => { startY = e.touches[0].clientY; }, { passive: true });
        drawer.addEventListener('touchend', e => {
            if (e.changedTouches[0].clientY - startY > 80) closeAllDrawers();
        }, { passive: true });
    });
    // Kalau item <= 4, pakai space-around. Kalau lebih, pakai scroll
const nav = document.querySelector('.mobile-bottom-nav');
const items = nav.querySelectorAll('.nav-item');
if (items.length <= 4) {
    nav.style.justifyContent = 'space-around';
    items.forEach(i => { i.style.width = 'auto'; i.style.minWidth = '56px'; });
}
</script>