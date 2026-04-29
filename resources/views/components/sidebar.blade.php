<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand bg-dark">
            <img src="{{ asset('img/AsianBay.png') }}" alt="logo" width="70" class="light mb-5 mt-10">
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="">ABD</a>
        </div>
        <ul class="sidebar-menu">
             <li class="nav-item dropdown ">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-user"></i>
                        <span>Profile</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ Request::is('feature-profile') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('feature-profile') }}">Profile</a>
                        </li>
                       
                    </ul>
                </li>
            @role('Human')
                <li class="{{ Request::is('Dashboard') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('Dashboard') }}"><i class="fas fa-house"></i>
                        <span>Dashboard</span></a>
                </li>
            
            @endrole
            @role('Manager')
                <li class="nav-item dropdown ">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-house"></i>
                        <span>Dashboards</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ Request::is('dashboardTeam') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('dashboardTeam') }}">Dashboard Team</a>
                        </li>
                        <li class="{{ Request::is('dashboardManager') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('dashboardManager') }}">Dashboard Manager</a>
                        </li>
                    </ul>
                </li>
               
                <li class="nav-item dropdown ">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-lock"></i>
                        <span>Employee</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ Request::is('Team') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Team') }}">Team List</a>
                        </li>
                        <li class="{{ Request::is('Positionrequest') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Positionrequest') }}">Position Request</a>
                        </li>
                        <li class="{{ Request::is('Teamfingerprint') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Teamfingerprint') }}">Team Fingerprints</a>
                        </li>
                    </ul>
                </li>
            @endrole
            @role('Director')
                <li class="{{ Request::is('dashboardDirector') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('dashboardDirector') }}"><i class="fas fa-house"></i>
                        <span>Dashboard</span></a>
                </li>
                
                <li class="nav-item dropdown ">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-lock"></i>
                        <span>Employee</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ Request::is('PositionApproval') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('PositionApproval') }}">Position Request List</a>
                        </li>
                    </ul>
                </li>
            @endrole
            @role('Admin')
              
                <li class="nav-item dropdown ">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-lock"></i>
                        <span>Users</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ Request::is('dashboardAdmin') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('dashboardAdmin') }}">List Users</a>
                        </li>
                        <li class="{{ Request::is('Activity') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Activity') }}">Activity Logs</a>
                        </li>
                        <li class="{{ Request::is('roles') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('roles') }}">Roles</a>
                        </li>
                        <li class="{{ Request::is('permission') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('permissions') }}">Permission</a>
                        </li>
                    </ul>
                </li>
            @endrole
            @role('HeadHR|HR')
                <li class="{{ Request::is('dashboardHR') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('dashboardHR') }}"><i class="fas fa-house"></i>
                        <span>Dashboard</span></a>
                </li>
               
               
                <li class="nav-item dropdown ">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-user-tie"></i>
                        <span>Employee</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ Request::is('Employee') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Employee') }}">Employee List</a>
                        </li>
                        @role('HeadHR')
                        <li class="{{ Request::is('Positionrequest') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Positionrequest') }}">Position Request</a>
                        </li>
                        <li class="{{ Request::is('Positionreqlist') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Positionreqlist') }}">Position Req List</a>
                        </li>
                        @endrole
                        <li class="{{ Request::is('Structuresnew') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Structuresnew') }}">Structure List</a>
                        </li>

                    </ul>
                </li>
                <li class="nav-item dropdown ">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-database"></i>
                        <span>Master Data</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ Request::is('{Banks}') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Banks') }}">Banks</a>
                        </li>
                        <li class="{{ Request::is('{Company}') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Company') }}">Companies</a>
                        </li>
                        <li class="{{ Request::is('{Position}') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Position') }}">Positions</a>
                        </li>
                        <li class="{{ Request::is('Department') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Department') }}">Departments</a>
                        </li>
                        <li class="{{ Request::is('Store') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Store') }}">Locations</a>
                        </li>
                        <li class="{{ Request::is('Grading') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Grading') }}">Gradings</a>
                        </li>
                        <li class="{{ Request::is('Pubholi') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Pubholi') }}">Public Holidays</a>
                        </li>
                        <li class="{{ Request::is('Shifts') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Shifts') }}">Shifts</a>
                        </li>
                    </ul>
                </li>
                @role('HeadHR')
                <li class="nav-item dropdown ">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-building"></i>
                        <span>Contracts</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ Request::is('{contract}') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('contract') }}">Employees Contracts</a>
                        </li>
                        
                    </ul>
                </li>
                <li class="nav-item dropdown ">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-file-signature"></i>
                        <span>SK</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ Request::is('{Sktype}') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Sktype') }}">SK Type</a>
                        </li>
                        <li class="{{ Request::is('{Sktemplate}') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Sktemplate') }}">SK Template</a>
                        </li>
                        <li class="{{ Request::is('{Skestablishment}') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Skestablishment') }}">SK Establishment</a>
                        </li>
                    </ul>
                </li>
                @endrole

                {{-- ── Attendance (HeadHR) ── --}}
                <li
                    class="nav-item dropdown {{ Request::is('Fingerprints', 'Editedfinger', 'roster*', 'schedule*', 'fingerprint-recap*') ? 'active' : '' }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-calendar-check"></i>
                        <span>Attendance</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ Request::is('{Fingerprints}') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Fingerprints') }}">Fingerprints Data</a>
                        </li>
                        {{-- ── TAMBAHAN ── --}}
                        <li class="{{ Request::is('roster*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('roster.index') }}">Roster & Schedule</a>
                        </li>
                        <li class="{{ Request::is('fingerprint-recap*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('fingerprint-recap.index') }}">Fingerprint Recap</a>
                        </li>
                        <li class="{{ Request::is('Summaries') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Summaries') }}">Leaves List</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown ">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-paper-plane""></i>
                        <span>Submissions</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ Request::is('{Submussions}') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Submissions') }}">All Submissions</a>
                        </li>
                    </ul>
                </li>
            @endrole
            @role('HeadHR')
             <li class="nav-item dropdown ">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-wallet"></i>
                        <span>Payrolls</span></a>
                    <ul class="dropdown-menu">
                       
                        <li class="{{ Request::is('payrollcomponents') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('payrollcomponents') }}">Payroll Components</a>
                        </li>
                        <li class="{{ Request::is('Payrolls') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Payrolls') }}">Payrolls</a>
                        </li>
                    </ul>
                </li>

            @endrole
            
            {{-- @role('HR')
                <li class="{{ Request::is('dashboardHR') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('dashboardHR') }}"><i class="fas fa-house"></i>
                        <span>Dashboard</span></a>
                </li>
                
                <li class="nav-item dropdown ">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-lock"></i>
                        <span>Employee</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ Request::is('Employee') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Employee') }}">List Employees</a>
                        </li>
                        <li class="{{ Request::is('Structuresnew') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Structuresnew') }}">Structure List</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown ">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-lock"></i>
                        <span>Master Data</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ Request::is('{Banks}') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Banks') }}">Banks</a>
                        </li>
                        <li class="{{ Request::is('{Company}') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Company') }}">Companies</a>
                        </li>
                        <li class="{{ Request::is('{Position}') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Position') }}">Position</a>
                        </li>
                        <li class="{{ Request::is('Department') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Department') }}">Departments</a>
                        </li>
                        <li class="{{ Request::is('Store') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Store') }}">Locations</a>
                        </li>
                        <li class="{{ Request::is('Grading') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Grading') }}">Gradings</a>
                        </li>
                        <li class="{{ Request::is('Pubholi') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Pubholi') }}">Public Holidays</a>
                        </li>
                        <li class="{{ Request::is('Shifts') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Shifts') }}">Shifts</a>
                        </li>
                    </ul>
                </li>
                <li
                    class="nav-item dropdown {{ Request::is('Fingerprints', 'Editedfinger', 'roster*', 'schedule*', 'fingerprint-recap*') ? 'active' : '' }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-lock"></i>
                        <span>Attendance</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ Request::is('{Fingerprints}') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Fingerprints') }}">Fingerprints Data</a>
                        </li>
                        <li class="{{ Request::is('roster*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('roster.index') }}">Roster & Schedule</a>
                        </li>
                        <li class="{{ Request::is('fingerprint-recap*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('fingerprint-recap.index') }}">Fingerprint Recap</a>
                        </li>
                    </ul>
                </li>
            @endrole --}}
        </ul>
    </aside>
</div>
