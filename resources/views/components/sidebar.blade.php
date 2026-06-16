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
                    <li class="{{ Request::is('rnr') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('rnr') }}">Roles & Responsibilities</a>
                    </li>
                    {{-- <li class="{{ Request::is('documents') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('documents') }}">Document</a>
                    </li> --}}

                </ul>
            </li>
            {{-- @can('DashboardHuman')
                <li class="{{ Request::is('dashboardHuman') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('dashboardHuman') }}"><i class="fas fa-house"></i>
                        <span>Dashboard</span></a>
                </li> --}}
            {{-- @endrole --}}
            <li class="nav-item dropdown ">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-house"></i>
                    <span>Dashboards</span></a>
                <ul class="dropdown-menu">
                    @can('dashboardHuman')
                        <li class="{{ Request::is('dashboardHuman') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('dashboardHuman') }}">Dashboard</a>
                        </li>
                    @endcan
                    @can('dashboardManager')
                        <li class="{{ Request::is('dashboardTeam') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('dashboardTeam') }}">Dashboard Team</a>
                        </li>
                        <li class="{{ Request::is('dashboardManager') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('dashboardManager') }}">Dashboard Manager</a>
                        </li>
                    @endcan
                    @can('dashboardHR')
                        <li class="{{ Request::is('dashboardHR') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('dashboardHR') }}">Dashboard HR</a>
                        </li>
                    @endcan
                    @can('dashboardSupervisor')
                        <li class="{{ Request::is('dashboardSupervisor') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('dashboardSupervisor') }}">Dashboard</a>
                        </li>
                    @endcan
                    @can('dashboardDirector')
                        <li class="{{ Request::is('dashboardDirector') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('dashboardDirector') }}">Dashboard</a>
                        </li>
                    @endcan
                    @can('dashboardAdmin')
                        <li class="{{ Request::is('dashboardAdmin') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('dashboardAdmin') }}">Dashboard</a>
                        </li>
                    @endcan
                </ul>
            </li>

            <li class="nav-item dropdown ">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-lock"></i>
                    <span>Employee</span></a>
                <ul class="dropdown-menu">
                    @can('ManageTeam')
                        <li class="{{ Request::is('Team') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Team') }}">Team List</a>
                        </li>
                    @endcan
                    {{-- @can('RequestPosition')
                        <li class="{{ Request::is('Positionrequest') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Positionrequest') }}">Position Request</a>
                        </li>
                    @endcan
                    @can('RequestPositionList')
                        <li class="{{ Request::is('Positionreqlist') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Positionreqlist') }}">Position Req List</a>
                        </li>
                    @endcan --}}
                    {{-- @can('ManageTeamfingerprint')
                        <li class="{{ Request::is('Teamfingerprint') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Teamfingerprint') }}">Team Fingerprints</a>
                        </li>
                    @endcan --}}
                    
                   
                    {{-- @can('Positionapprovals')
                        <li class="{{ Request::is('Positionapprovals') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Positionapprovals') }}">Position Approvals</a>
                        </li>
                    @endcan --}}
                    {{-- @can('ManageStructuresnew')
                        <li class="{{ Request::is('Structuresnew') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Structuresnew') }}">Structure List</a>
                        </li>
                    @endcan --}}
                     {{-- @canany(['ManageEmployee','ManageEmployeeSPVManager','ViewEmployee']) --}}
                     @can('ManageEmployee')
                        <li class="{{ Request::is('Employee') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Employee') }}">Employee List</a>
                        </li>
 <li class="{{ Request::is('employees/bulk') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('employees/bulk') }}">Bulk</a>
                        </li>
                    @endcan
                     @can('ManageEmployeeSPVManager')
                        <li class="{{ Request::is('Employee') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Employee') }}">Employee List</a>
                        </li>
                    @endcan
                     @can('ViewEmployee')
                        <li class="{{ Request::is('Employee') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Employee') }}">Employee List</a>
                        </li>
                    @endcan
                    @can('ManageSummaries')
                        <li class="{{ Request::is('Summaries') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Summaries') }}">Leaves List</a>
                        </li>
                    @endcan



                </ul>
            </li>

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
            <li class="nav-item dropdown ">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                        class="fas fa-database"></i>
                    <span>Master Data</span></a>
                <ul class="dropdown-menu">
                    @can('ManageBanks')
                        <li class="{{ Request::is('{Banks}') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Banks') }}">Banks</a>
                        </li>
                    @endcan
                    @can('ManageCompanies')
                        <li class="{{ Request::is('{Company}') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Company') }}">Companies</a>
                        </li>
                    @endcan
                    @can('ManagePositions')
                        <li class="{{ Request::is('{Position}') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Position') }}">Positions</a>
                        </li>
                    @endcan
                    @can('ManageDepartments')
                        <li class="{{ Request::is('Department') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Department') }}">Departments</a>
                        </li>
                    @endcan
                    @can('ManageStores')
                        <li class="{{ Request::is('Store') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Store') }}">Locations</a>
                        </li>
                    @endcan
                    @can('ManageGrading')
                        <li class="{{ Request::is('Grading') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Grading') }}">Gradings</a>
                        </li>
                    @endcan
                    @can('ManagePH')
                        <li class="{{ Request::is('Pubholi') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Pubholi') }}">Public Holidays</a>
                        </li>
                    @endcan
                    @can('ManageShifts')
                        <li class="{{ Request::is('Shifts') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Shifts') }}">Shifts</a>
                        </li>
                    @endcan
                </ul>
            </li>
            @endrole
                    @can('ManageContracts')

            <li class="nav-item dropdown ">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                        class="fas fa-building"></i>
                    <span>Contracts</span></a>
                <ul class="dropdown-menu">
                        <li class="{{ Request::is('{contract}') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('contract') }}">Employees Contracts</a>
                        </li>
                </ul>
            </li>
                    @endcan
@canany(['ManageSktypes','ManageSkLetters'])
            <li class="nav-item dropdown ">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                        class="fas fa-file-signature"></i>
                    <span>SK</span></a>
                <ul class="dropdown-menu">
                        <li class="{{ Request::is('{Sktype}') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Sktype') }}">SK Type</a>
                        </li>
                        <li class="{{ Request::is('{SkLetters}') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('SkLetters') }}">SK Letters</a>
                        </li>
                    
                </ul>
            </li>
            @endcan
            {{-- <li class="nav-item dropdown ">
                        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                                class="fas fa-file-signature"></i>
                            <span>ST</span></a>
                        <ul class="dropdown-menu">
                            <li class="{{ Request::is('{StLetters}') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ url('StLetters') }}">ST Letters</a>
                            </li>
                            </ul>
                    </li> --}}
            {{-- <li class="nav-item dropdown ">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-wallet"></i>
                    <span>Payrolls</span></a>
                <ul class="dropdown-menu">
                    @can('ManagePayrolls')
                        <li class="{{ Request::is('payrollcomponents') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('payrollcomponents') }}">Payroll Components</a>
                        </li>
                        <li class="{{ Request::is('Payrolls') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Payrolls') }}">Payrolls</a>
                        </li>
                    @endcan
                </ul>
            </li> --}}
            @can('ManagePayrolls')
<li class="nav-item dropdown">
    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
        <i class="fas fa-wallet"></i>
        <span>Payrolls</span>
    </a>
    <ul class="dropdown-menu">
        <li class="{{ Request::is('payrollcomponents') ? 'active' : '' }}">
            <a class="nav-link" href="{{ url('payrollcomponents') }}">Payroll Components</a>
        </li>
        <li class="{{ Request::is('employee-salary') ? 'active' : '' }}">
            <a class="nav-link" href="{{ url('employee-salary') }}">Employee Salary</a>
        </li>
        <li class="{{ Request::is('payroll-period') ? 'active' : '' }}">
            <a class="nav-link" href="{{ url('payroll-period') }}">Payroll Period</a>
        </li>
        {{-- <li class="{{ Request::is('payroll') ? 'active' : '' }}">
            <a class="nav-link" href="{{ url('payroll') }}">Payroll</a>
        </li> --}}
        {{-- <li class="{{ Request::is('Payrolls') ? 'active' : '' }}">
            <a class="nav-link" href="{{ url('Payrolls') }}">Payrolls</a>
        </li> --}}
    </ul>
</li>
@endcan


            {{-- ════════════════════════════════════════════════════ --}}
            {{-- TOIL System Menu (untuk SEMUA role yang login)        --}}
            {{-- ════════════════════════════════════════════════════ --}}
            <li class="nav-item dropdown {{ Request::is('toil*') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                    <i class="fas fa-business-time"></i>
                    <span>TOIL</span>
                </a>
                <ul class="dropdown-menu">
                    @can('tiol')
                        <li class="{{ Request::is('toil/balance') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('toil.balance') }}">Balance</a>
                        </li>
                        <li class="{{ Request::is('toil/history*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('toil.history') }}">History</a>
                        </li>
                    @endcan

                    {{-- HR Only: Monitoring Saldo Semua Karyawan --}}
                    @can('allbalances')
                        <li class="{{ Request::is('toil/all-balances*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('toil.all-balances') }}">
                                All Balances
                            </a>
                        </li>
                    @endcan

                    <li class="{{ Request::is('toil/assignment*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('toil.assignment.index') }}">
                            Overtime Assignment
                        </a>
                    </li>
                    <li class="{{ Request::is('toil/approval*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('toil.approval.index') }}">
                            Approval
                        </a>
                    </li>

                </ul>
            </li>

            {{-- <li
                class="nav-item dropdown {{ Request::is('Fingerprints', 'Editedfinger', 'roster*', 'schedule*', 'fingerprint-recap*') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                        class="fas fa-calendar-check"></i>
                    <span>Attendance</span></a>
                <ul class="dropdown-menu">
                   

                </ul>
            </li> --}}
            <li class="nav-item dropdown ">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-calendar-check"></i>
                    <span>Attendance</span></a>
                <ul class="dropdown-menu">
                    @canany(['ManageFingerspot', 'ManageFingerspotSPVManager', 'ViewFingerspot'])

                    <li class="{{ Request::is('{Fingerprints}') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Fingerprints') }}">Fingerprints Data</a>
                        </li>
                        @endcanany
                        @can('ManageFingerspot')
                        <li class="{{ Request::is('fingerprint-recap*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('fingerprint-recap.index') }}">Fingerprint Recap</a>
                        </li>
                    @endcan
                @canany(['ManageRoster', 'ManageRosterSPVManager', 'ViewRoster'])

                    <li class="{{ Request::is('roster*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('roster.index') }}">Roster & Schedule</a>
                        </li>
                    @endcanany
                </ul>
            </li>

        </ul>
    </aside>
</div>
