{{-- Overlay --}}
<div class="mobile-drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>

{{-- Bottom Navigation Bar --}}
<nav class="mobile-bottom-nav" aria-label="Mobile navigation">

    {{-- Dashboard --}}
    @role('Human')
    <a href="{{ url('Dashboard') }}" class="nav-item {{ Request::is('Dashboard') ? 'active' : '' }}">
        <i class="fas fa-house"></i>
        <span>Home</span>
    </a>
    @endrole
    @role('Manager')
    <a href="{{ url('dashboardTeam') }}" class="nav-item {{ Request::is('dashboardTeam','dashboardManager') ? 'active' : '' }}">
        <i class="fas fa-house"></i>
        <span>Home</span>
    </a>
    @endrole
    @role('Director')
    <a href="{{ url('dashboardDirector') }}" class="nav-item {{ Request::is('dashboardDirector') ? 'active' : '' }}">
        <i class="fas fa-house"></i>
        <span>Home</span>
    </a>
    @endrole
    @role('HeadHR|HR')
    <a href="{{ url('dashboardHR') }}" class="nav-item {{ Request::is('dashboardHR') ? 'active' : '' }}">
        <i class="fas fa-house"></i>
        <span>Home</span>
    </a>
    @endrole

    {{-- Profile --}}
    <a href="{{ url('feature-profile') }}" class="nav-item {{ Request::is('feature-profile') ? 'active' : '' }}">
        <i class="fas fa-user"></i>
        <span>Profile</span>
    </a>

    {{-- Attendance --}}
    @role('HeadHR|HR')
    <a href="{{ url('Fingerprints') }}" class="nav-item {{ Request::is('Fingerprints','roster*','fingerprint-recap*') ? 'active' : '' }}">
        <i class="fas fa-calendar-check"></i>
        <span>Attendance</span>
    </a>
    @endrole

    {{-- TOIL --}}
    <a href="{{ route('toil.balance') }}" class="nav-item {{ Request::is('toil*') ? 'active' : '' }}">
        <i class="fas fa-business-time"></i>
        <span>TOIL</span>
    </a>

    {{-- More --}}
    <button class="nav-item" onclick="openDrawer()" type="button" aria-label="More menu">
        <i class="fas fa-bars"></i>
        <span>More</span>
    </button>
</nav>

{{-- "More" Slide-up Drawer --}}
<div class="mobile-more-drawer" id="moreDrawer" role="dialog" aria-label="More options">
    <div class="drawer-handle"></div>

    <div class="drawer-section-title">Menu</div>

    <a href="{{ url('documents') }}" class="drawer-item">
        <i class="fas fa-file"></i> Document
    </a>
    <a href="{{ url('rnr') }}" class="drawer-item">
        <i class="fas fa-award"></i> R&R
    </a>

    @role('Manager')
    <a href="{{ url('Team') }}" class="drawer-item">
        <i class="fas fa-users"></i> Team List
    </a>
    <a href="{{ url('Teamfingerprint') }}" class="drawer-item">
        <i class="fas fa-fingerprint"></i> Team Fingerprints
    </a>
    @endrole

    @role('HeadHR|HR')
    <a href="{{ url('Employee') }}" class="drawer-item">
        <i class="fas fa-user-tie"></i> Employee List
    </a>
    <a href="{{ url('Submissions') }}" class="drawer-item">
        <i class="fas fa-paper-plane"></i> Submissions
    </a>
    @endrole

    @role('HeadHR')
    <a href="{{ url('Payrolls') }}" class="drawer-item">
        <i class="fas fa-wallet"></i> Payrolls
    </a>
    <a href="{{ url('contract') }}" class="drawer-item">
        <i class="fas fa-building"></i> Contracts
    </a>
    @endrole

    @role('Admin')
    <a href="{{ url('dashboardAdmin') }}" class="drawer-item">
        <i class="fas fa-users-cog"></i> Users
    </a>
    <a href="{{ url('Activity') }}" class="drawer-item">
        <i class="fas fa-history"></i> Activity Logs
    </a>
    @endrole

    <div class="drawer-section-title">Account</div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="drawer-item" style="width:100%; border:none; background:none; text-align:left; cursor:pointer; color:#e74c3c;">
            <i class="fas fa-sign-out-alt" style="color:#e74c3c;"></i> Logout
        </button>
    </form>
</div>

<script>
function openDrawer() {
    document.getElementById('moreDrawer').classList.add('open');
    document.getElementById('drawerOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeDrawer() {
    document.getElementById('moreDrawer').classList.remove('open');
    document.getElementById('drawerOverlay').classList.remove('open');
    document.body.style.overflow = '';
}
</script>