<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand bg-dark">
            <img src="{{ asset('img/1710675344-17-03-2024-iSZQk9yVubtJh31N46lxpnC7av5osrLW.png') }}" alt="logo"
                width="80" class="light mb-5 mt-2">
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="">MJM</a>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-header">Menu</li>
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
            @role('HeadHR')
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
                        <li class="{{ Request::is('Shifts') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Employee') }}">Shifts</a>
                        </li>
                        <li class="{{ Request::is('Payrolls') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Payrolls') }}">Payrolls</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown ">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-lock"></i>
                        <span>Create Data</span></a>
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
                            <a class="nav-link" href="{{ url('Store') }}">Stores</a>
                        </li>

                    </ul>
                </li>
                <li class="nav-item dropdown ">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-lock"></i>
                        <span>Attendance</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ Request::is('{Fingerspot}') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Fingerspot') }}">Fingerspot Data</a>
                        </li>
                        <li class="{{ Request::is('{Attendance}') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Attendance') }}">Attendance Data</a>
                        </li>
                      
                    </ul>
                </li>
                <li class="nav-item dropdown ">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-lock"></i>
                        <span>Submissions</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ Request::is('{Submussions}') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Submissions') }}">All Submissions</a>
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
                            <a class="nav-link" href="{{ url('Store') }}">Stores</a>
                        </li>

                    </ul>
                </li>
            @endrole
            @role('HR')
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
                        <li class="{{ Request::is('Shifts') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Employee') }}">Shifts</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown ">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-lock"></i>
                        <span>Create Data</span></a>
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
                            <a class="nav-link" href="{{ url('Store') }}">Stores</a>
                        </li>

                    </ul>
                </li>
                 <li class="nav-item dropdown ">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-lock"></i>
                        <span>Attendance</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ Request::is('{Fingerspot}') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Fingerspot') }}">Fingerspot Data</a>
                        </li>
                        <li class="{{ Request::is('{Attendance}') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('Attendance') }}">Attendance Data</a>
                        </li>
                      
                    </ul>
                </li>
            @endrole

            @role('HeadBuyer')

            <li class="menu-header">Buyer Contoh</li>
            <li class="nav-item dropdown ">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-columns"></i>
                    <span>buyer lagi</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ Request::is('Uoms') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('Uoms') }}">Uoms</a>
                    </li>
                    <li class="{{ Request::is('Brands') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('Brands') }}">Brands</a>
                    </li>
                    <li class="{{ Request::is('Categories') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('Categories') }}">Categories</a>
                    </li>
                    <li class="{{ Request::is('Taxstatus') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('Taxstatus') }}">Tax Status</a>
                    </li>
                    <li class="{{ Request::is('Statusproduct') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('Statusproduct') }}">Status Product</a>
                    </li>
                </ul>
            </li>
            <li class="{{ Request::is('Masterproducts') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('Masterproducts') }}"><i class="fas fa-house"></i>
                    <span>Master Products</span></a>
            </li>
            @endrole

            {{-- <li class="{{ Request::is('blank-page') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('blank-page') }}"><i class="far fa-square"></i> <span>Blank
                        Page</span></a>
            </li>
            <li class="nav-item dropdown">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-th"></i> <span>Bootstrap</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ Request::is('bootstrap-alert') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('bootstrap-alert') }}">Alert</a>
                    </li>
                    <li class="{{ Request::is('bootstrap-badge') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('bootstrap-badge') }}">Badge</a>
                    </li>
                    <li class="{{ Request::is('bootstrap-breadcrumb') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('bootstrap-breadcrumb') }}">Breadcrumb</a>
                    </li>
                    <li class="{{ Request::is('bootstrap-buttons') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('bootstrap-buttons') }}">Buttons</a>
                    </li>
                    <li class="{{ Request::is('bootstrap-card') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('bootstrap-card') }}">Card</a>
                    </li>
                    <li class="{{ Request::is('bootstrap-carousel') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('bootstrap-carousel') }}">Carousel</a>
                    </li>
                    <li class="{{ Request::is('bootstrap-collapse') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('bootstrap-collapse') }}">Collapse</a>
                    </li>
                    <li class="{{ Request::is('bootstrap-dropdown') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('bootstrap-dropdown') }}">Dropdown</a>
                    </li>
                    <li class="{{ Request::is('bootstrap-form') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('bootstrap-form') }}">Form</a>
                    </li>
                    <li class="{{ Request::is('bootstrap-list-group') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('bootstrap-list-group') }}">List Group</a>
                    </li>
                    <li class="{{ Request::is('bootstrap-media-object') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('bootstrap-media-object') }}">Media Object</a>
                    </li>
                    <li class="{{ Request::is('bootstrap-modal') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('bootstrap-modal') }}">Modal</a>
                    </li>
                    <li class="{{ Request::is('bootstrap-nav') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('bootstrap-nav') }}">Nav</a>
                    </li>
                    <li class="{{ Request::is('bootstrap-navbar') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('bootstrap-navbar') }}">Navbar</a>
                    </li>
                    <li class="{{ Request::is('bootstrap-pagination') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('bootstrap-pagination') }}">Pagination</a>
                    </li>
                    <li class="{{ Request::is('bootstrap-popover') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('bootstrap-popover') }}">Popover</a>
                    </li>
                    <li class="{{ Request::is('bootstrap-progress') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('bootstrap-progress') }}">Progress</a>
                    </li>
                    <li class="{{ Request::is('bootstrap-table') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('bootstrap-table') }}">Table</a>
                    </li>
                    <li class="{{ Request::is('bootstrap-tooltip') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('bootstrap-tooltip') }}">Tooltip</a>
                    </li>
                    <li class="{{ Request::is('bootstrap-typography') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('bootstrap-typography') }}">Typography</a>
                    </li>
                </ul>
            </li>
            <li class="menu-header">MJM</li>
            <li class="nav-item dropdown ">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-th-large"></i>
                    <span>Components</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ Request::is('components-article') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('components-article') }}">Article</a>
                    </li>
                    <li class="{{ Request::is('components-avatar') ? 'active' : '' }}">
                        <a class="nav-link beep beep-sidebar" href="{{ url('components-avatar') }}">Avatar</a>
                    </li>
                    <li class="{{ Request::is('components-chat-box') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('components-chat-box') }}">Chat Box</a>
                    </li>
                    <li class="{{ Request::is('components-empty-state') ? 'active' : '' }}">
                        <a class="nav-link beep beep-sidebar" href="{{ url('components-empty-state') }}">Empty
                            State</a>
                    </li>
                    <li class="{{ Request::is('components-gallery') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('components-gallery') }}">Gallery</a>
                    </li>
                    <li class="{{ Request::is('components-hero') ? 'active' : '' }}">
                        <a class="nav-link beep beep-sidebar" href="{{ url('components-hero') }}">Hero</a>
                    </li>
                    <li class="{{ Request::is('components-multiple-upload') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('components-multiple-upload') }}">Multiple Upload</a>
                    </li>
                    <li class="{{ Request::is('components-pricing') ? 'active' : '' }}">
                        <a class="nav-link beep beep-sidebar" href="{{ url('components-pricing') }}">Pricing</a>
                    </li>
                    <li class="{{ Request::is('components-statistic') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('components-statistic') }}">Statistic</a>
                    </li>
                    <li class="{{ Request::is('components-tab') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('components-tab') }}">Tab</a>
                    </li>
                    <li class="{{ Request::is('components-table') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('components-table') }}">Table</a>
                    </li>
                    <li class="{{ Request::is('components-user') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('components-user') }}">User</a>
                    </li>
                    <li class="{{ Request::is('components-wizard') ? 'active' : '' }}">
                        <a class="nav-link beep beep-sidebar" href="{{ url('components-wizard') }}">Wizard</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item dropdown ">
                <a href="#" class="nav-link has-dropdown"><i class="far fa-file-alt"></i>
                    <span>Forms</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ Request::is('forms-advanced-form') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('forms-advanced-form') }}">Advanced Form</a>
                    </li>
                    <li class="{{ Request::is('forms-editor') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('forms-editor') }}">Editor</a>
                    </li>
                    <li class="{{ Request::is('forms-validation') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('forms-validation') }}">Validation</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item dropdown">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-map-marker-alt"></i> <span>Google
                        Maps</span></a>
                <ul class="dropdown-menu">
                    <li><a href="gmaps-advanced-route.html">Advanced Route</a></li>
                    <li><a href="gmaps-draggable-marker.html">Draggable Marker</a></li>
                    <li><a href="gmaps-geocoding.html">Geocoding</a></li>
                    <li><a href="gmaps-geolocation.html">Geolocation</a></li>
                    <li><a href="gmaps-marker.html">Marker</a></li>
                    <li><a href="gmaps-multiple-marker.html">Multiple Marker</a></li>
                    <li><a href="gmaps-route.html">Route</a></li>
                    <li><a href="gmaps-simple.html">Simple</a></li>
                </ul>
            </li>
            <li class="nav-item dropdown ">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-plug"></i> <span>Modules</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ Request::is('modules-calendar') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('modules-calendar') }}">Calendar</a>
                    </li>
                    <li class="{{ Request::is('modules-chartjs') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('modules-chartjs') }}">ChartJS</a>
                    </li>
                    <li class="{{ Request::is('modules-datatables') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('modules-datatables') }}">DataTables</a>
                    </li>
                    <li class="{{ Request::is('modules-flag') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('modules-flag') }}">Flag</a>
                    </li>
                    <li class="{{ Request::is('modules-font-awesome') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('modules-font-awesome') }}">Font Awesome</a>
                    </li>
                    <li class="{{ Request::is('modules-ion-icons') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('modules-ion-icons') }}">Ion Icons</a>
                    </li>
                    <li class="{{ Request::is('modules-owl-carousel') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('modules-owl-carousel') }}">Owl Carousel</a>
                    </li>
                    <li class="{{ Request::is('modules-sparkline') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('modules-sparkline') }}">Sparkline</a>
                    </li>
                    <li class="{{ Request::is('modules-sweet-alert') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('modules-sweet-alert') }}">Sweet Alert</a>
                    </li>
                    <li class="{{ Request::is('modules-toastr') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('modules-toastr') }}">Toastr</a>
                    </li>
                    <li class="{{ Request::is('modules-vector-map') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('modules-vector-map') }}">Vector Map</a>
                    </li>
                    <li class="{{ Request::is('modules-weather-icon') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('modules-weather-icon') }}">Weather Icon</a>
                    </li>
                </ul>
            </li>
            <li class="menu-header">Pages</li>
            <li class="nav-item dropdown ">
                <a href="#" class="nav-link has-dropdown"><i class="far fa-user"></i> <span>Auth</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ Request::is('auth-forgot-password') ? 'active' : '' }}">
                        <a href="{{ url('auth-forgot-password') }}">Forgot Password</a>
                    </li>
                    <li class="{{ Request::is('auth-login') ? 'active' : '' }}">
                        <a href="{{ url('auth-login') }}">Login</a>
                    </li>
                    <li class="{{ Request::is('auth-login2') ? 'active' : '' }}">
                        <a class="beep beep-sidebar" href="{{ url('auth-login2') }}">Login 2</a>
                    </li>
                    <li class="{{ Request::is('auth-register') ? 'active' : '' }}">
                        <a href="{{ url('auth-register') }}">Register</a>
                    </li>
                    <li class="{{ Request::is('auth-reset-password') ? 'active' : '' }}">
                        <a href="{{ url('auth-reset-password') }}">Reset Password</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item dropdown ">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-exclamation"></i>
                    <span>Errors</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ Request::is('error-403') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('error-403') }}">403</a>
                    </li>
                    <li class="{{ Request::is('error-404') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('error-404') }}">404</a>
                    </li>
                    <li class="{{ Request::is('error-500') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('error-500') }}">500</a>
                    </li>
                    <li class="{{ Request::is('error-503') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('error-503') }}">503</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item dropdown ">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-bicycle"></i>
                    <span>Features</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ Request::is('features-activities') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('features-activities') }}">Activities</a>
                    </li>
                    <li class="{{ Request::is('features-post-create') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('features-post-create') }}">Post Create</a>
                    </li>
                    <li class="{{ Request::is('features-post') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('features-post') }}">Posts</a>
                    </li>
                    <li class="{{ Request::is('features-profile') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('features-profile') }}">Profile</a>
                    </li>
                    <li class="{{ Request::is('features-settings') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('features-settings') }}">Settings</a>
                    </li>
                    <li class="{{ Request::is('features-setting-detail') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('features-setting-detail') }}">Setting Detail</a>
                    </li>
                    <li class="{{ Request::is('features-tickets') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('features-tickets') }}">Tickets</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item dropdown">
            <li class="nav-item dropdown">
                <a href="#" class="nav-link has-dropdown"><i class="fas fa-ellipsis-h"></i>
                    <span>Utilities</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ Request::is('utilities-contact') ? 'active' : '' }}">
                        <a href="{{ url('utilities-contact') }}">Contact</a>
                    </li>
                    <li class="{{ Request::is('utilities-invoice') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('utilities-invoice') }}">Invoice</a>
                    </li>
                    <li class="{{ Request::is('utilities-subscribe') ? 'active' : '' }}">
                        <a href="{{ url('utilities-subscribe') }}">Subscribe</a>
                    </li>
                </ul>
            </li>
            <li class="{{ Request::is('credits') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('credits') }}"><i class="fas fa-pencil-ruler">
                    </i> <span>Credits</span>
                </a>
            </li> --}}
        </ul>

        {{-- <div class="hide-sidebar-mini mt-4 mb-4 p-3">
            <a href="https://getstisla.com/docs"
                class="btn btn-primary btn-lg btn-block btn-icon-split">
                <i class="fas fa-rocket"></i> Documentation
            </a>
        </div> --}}
    </aside>
</div>
