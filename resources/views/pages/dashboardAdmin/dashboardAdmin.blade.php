{{-- @extends('layouts.app')

@section('title', 'Dashboard Admin')

@push('style')
    <!-- CSS Libraries -->
    <link rel="stylesheet"
        href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Dashboard Admin</h1>
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-primary">
                            <i class="far fa-user"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total User</h4>
                            </div>
                            <div class="card-body">
                            {{$totaluser}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-danger">
                            <i class="far fa-newspaper"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>News</h4>
                            </div>
                            <div class="card-body">
                                42
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-warning">
                            <i class="far fa-file"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Reports</h4>
                            </div>
                            <div class="card-body">
                                1,201
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-success">
                            <i class="fas fa-circle"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Online Users</h4>
                            </div>
                            <div class="card-body">
                                47
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 col-md-12 col-12 col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Statistics</h4>
                            <div class="card-header-action">
                                <div class="btn-group">
                                    <a href="#"
                                        class="btn btn-primary">Week</a>
                                    <a href="#"
                                        class="btn">Month</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="myChart"
                                height="182"></canvas>
                            <div class="statistic-details mt-sm-4">
                                <div class="statistic-details-item">
                                    <span class="text-muted"><span class="text-primary"><i
                                                class="fas fa-caret-up"></i></span> 7%</span>
                                    <div class="detail-value">$243</div>
                                    <div class="detail-name">Today's Sales</div>
                                </div>
                                <div class="statistic-details-item">
                                    <span class="text-muted"><span class="text-danger"><i
                                                class="fas fa-caret-down"></i></span> 23%</span>
                                    <div class="detail-value">$2,902</div>
                                    <div class="detail-name">This Week's Sales</div>
                                </div>
                                <div class="statistic-details-item">
                                    <span class="text-muted"><span class="text-primary"><i
                                                class="fas fa-caret-up"></i></span>9%</span>
                                    <div class="detail-value">$12,821</div>
                                    <div class="detail-name">This Month's Sales</div>
                                </div>
                                <div class="statistic-details-item">
                                    <span class="text-muted"><span class="text-primary"><i
                                                class="fas fa-caret-up"></i></span> 19%</span>
                                    <div class="detail-value">$92,142</div>
                                    <div class="detail-name">This Year's Sales</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 col-12 col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Recent Activities</h4>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled list-unstyled-border">
                                <li class="media">
                                    <img class="rounded-circle mr-3"
                                        width="50"
                                        src="{{ asset('img/avatar/avatar-1.png') }}"
                                        alt="avatar">
                                    <div class="media-body">
                                        <div class="text-primary float-right">Now</div>
                                        <div class="media-title">Farhan A Mujib</div>
                                        <span class="text-small text-muted">Cras sit amet nibh libero, in gravida nulla.
                                            Nulla vel metus scelerisque ante sollicitudin.</span>
                                    </div>
                                </li>
                                <li class="media">
                                    <img class="rounded-circle mr-3"
                                        width="50"
                                        src="{{ asset('img/avatar/avatar-2.png') }}"
                                        alt="avatar">
                                    <div class="media-body">
                                        <div class="float-right">12m</div>
                                        <div class="media-title">Ujang Maman</div>
                                        <span class="text-small text-muted">Cras sit amet nibh libero, in gravida nulla.
                                            Nulla vel metus scelerisque ante sollicitudin.</span>
                                    </div>
                                </li>
                                <li class="media">
                                    <img class="rounded-circle mr-3"
                                        width="50"
                                        src="{{ asset('img/avatar/avatar-3.png') }}"
                                        alt="avatar">
                                    <div class="media-body">
                                        <div class="float-right">17m</div>
                                        <div class="media-title">Rizal Fakhri</div>
                                        <span class="text-small text-muted">Cras sit amet nibh libero, in gravida nulla.
                                            Nulla vel metus scelerisque ante sollicitudin.</span>
                                    </div>
                                </li>
                                <li class="media">
                                    <img class="rounded-circle mr-3"
                                        width="50"
                                        src="{{ asset('img/avatar/avatar-4.png') }}"
                                        alt="avatar">
                                    <div class="media-body">
                                        <div class="float-right">21m</div>
                                        <div class="media-title">Alfa Zulkarnain</div>
                                        <span class="text-small text-muted">Cras sit amet nibh libero, in gravida nulla.
                                            Nulla vel metus scelerisque ante sollicitudin.</span>
                                    </div>
                                </li>
                            </ul>
                            <div class="pt-1 pb-1 text-center">
                                <a href="#"
                                    class="btn btn-primary btn-lg btn-round">
                                    View All
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-12 col-12 col-sm-12">
                    <div class="card">
                        <div class="card-body pt-2 pb-2">
                            <div id="myWeather">Please wait</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 col-12 col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Authors</h4>
                        </div>
                        <div class="card-body">
                            <div class="row pb-2">
                                <div class="col-6 col-sm-3 col-lg-3 mb-md-0 mb-4">
                                    <div class="avatar-item mb-0">
                                        <img alt="image"
                                            src="{{ asset('img/avatar/avatar-5.png') }}"
                                            class="img-fluid"
                                            data-toggle="tooltip"
                                            title="Alfa Zulkarnain">
                                        <div class="avatar-badge"
                                            title="Editor"
                                            data-toggle="tooltip"><i class="fas fa-wrench"></i></div>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-3 col-lg-3 mb-md-0 mb-4">
                                    <div class="avatar-item mb-0">
                                        <img alt="image"
                                            src="{{ asset('img/avatar/avatar-4.png') }}"
                                            class="img-fluid"
                                            data-toggle="tooltip"
                                            title="Egi Ferdian">
                                        <div class="avatar-badge"
                                            title="Admin"
                                            data-toggle="tooltip"><i class="fas fa-cog"></i></div>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-3 col-lg-3 mb-md-0 mb-4">
                                    <div class="avatar-item mb-0">
                                        <img alt="image"
                                            src="{{ asset('img/avatar/avatar-1.png') }}"
                                            class="img-fluid"
                                            data-toggle="tooltip"
                                            title="Jaka Ramadhan">
                                        <div class="avatar-badge"
                                            title="Author"
                                            data-toggle="tooltip"><i class="fas fa-pencil-alt"></i></div>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-3 col-lg-3 mb-md-0 mb-4">
                                    <div class="avatar-item mb-0">
                                        <img alt="image"
                                            src="{{ asset('img/avatar/avatar-2.png') }}"
                                            class="img-fluid"
                                            data-toggle="tooltip"
                                            title="Ryan">
                                        <div class="avatar-badge"
                                            title="Admin"
                                            data-toggle="tooltip"><i class="fas fa-cog"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-6 col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Referral URL</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <div class="text-small font-weight-bold text-muted float-right">2,100</div>
                                <div class="font-weight-bold mb-1">Google</div>
                                <div class="progress"
                                    data-height="3">
                                    <div class="progress-bar"
                                        role="progressbar"
                                        data-width="80%"
                                        aria-valuenow="80"
                                        aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="text-small font-weight-bold text-muted float-right">1,880</div>
                                <div class="font-weight-bold mb-1">Facebook</div>
                                <div class="progress"
                                    data-height="3">
                                    <div class="progress-bar"
                                        role="progressbar"
                                        data-width="67%"
                                        aria-valuenow="25"
                                        aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="text-small font-weight-bold text-muted float-right">1,521</div>
                                <div class="font-weight-bold mb-1">Bing</div>
                                <div class="progress"
                                    data-height="3">
                                    <div class="progress-bar"
                                        role="progressbar"
                                        data-width="58%"
                                        aria-valuenow="25"
                                        aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="text-small font-weight-bold text-muted float-right">884</div>
                                <div class="font-weight-bold mb-1">Yahoo</div>
                                <div class="progress"
                                    data-height="3">
                                    <div class="progress-bar"
                                        role="progressbar"
                                        data-width="36%"
                                        aria-valuenow="25"
                                        aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="text-small font-weight-bold text-muted float-right">473</div>
                                <div class="font-weight-bold mb-1">Kodinger</div>
                                <div class="progress"
                                    data-height="3">
                                    <div class="progress-bar"
                                        role="progressbar"
                                        data-width="28%"
                                        aria-valuenow="25"
                                        aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="text-small font-weight-bold text-muted float-right">418</div>
                                <div class="font-weight-bold mb-1">Multinity</div>
                                <div class="progress"
                                    data-height="3">
                                    <div class="progress-bar"
                                        role="progressbar"
                                        data-width="20%"
                                        aria-valuenow="25"
                                        aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4>Popular Browser</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col text-center">
                                    <div class="browser browser-chrome"></div>
                                    <div class="font-weight-bold mt-2">Chrome</div>
                                    <div class="text-muted text-small"><span class="text-primary"><i
                                                class="fas fa-caret-up"></i></span> 48%</div>
                                </div>
                                <div class="col text-center">
                                    <div class="browser browser-firefox"></div>
                                    <div class="font-weight-bold mt-2">Firefox</div>
                                    <div class="text-muted text-small"><span class="text-primary"><i
                                                class="fas fa-caret-up"></i></span> 26%</div>
                                </div>
                                <div class="col text-center">
                                    <div class="browser browser-safari"></div>
                                    <div class="font-weight-bold mt-2">Safari</div>
                                    <div class="text-muted text-small"><span class="text-danger"><i
                                                class="fas fa-caret-down"></i></span> 14%</div>
                                </div>
                                <div class="col text-center">
                                    <div class="browser browser-opera"></div>
                                    <div class="font-weight-bold mt-2">Opera</div>
                                    <div class="text-muted text-small">7%</div>
                                </div>
                                <div class="col text-center">
                                    <div class="browser browser-internet-explorer"></div>
                                    <div class="font-weight-bold mt-2">IE</div>
                                    <div class="text-muted text-small"><span class="text-primary"><i
                                                class="fas fa-caret-up"></i></span> 5%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mt-sm-5 mt-md-0">
                        <div class="card-header">
                            <h4>Visitors</h4>
                        </div>
                        <div class="card-body">
                            <div id="visitorMap"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>This Week Stats</h4>
                            <div class="card-header-action">
                                <div class="dropdown">
                                    <a href="#"
                                        class="dropdown-toggle btn btn-primary"
                                        data-toggle="dropdown">Filter</a>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a href="#"
                                            class="dropdown-item has-icon"><i class="far fa-circle"></i> Electronic</a>
                                        <a href="#"
                                            class="dropdown-item has-icon"><i class="far fa-circle"></i> T-shirt</a>
                                        <a href="#"
                                            class="dropdown-item has-icon"><i class="far fa-circle"></i> Hat</a>
                                        <div class="dropdown-divider"></div>
                                        <a href="#"
                                            class="dropdown-item">View All</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="summary">
                                <div class="summary-info">
                                    <h4>$1,053</h4>
                                    <div class="text-muted">Sold 3 items on 2 customers</div>
                                    <div class="d-block mt-2">
                                        <a href="#">View All</a>
                                    </div>
                                </div>
                                <div class="summary-item">
                                    <h6>Item List <span class="text-muted">(3 Items)</span></h6>
                                    <ul class="list-unstyled list-unstyled-border">
                                        <li class="media">
                                            <a href="#">
                                                <img class="mr-3 rounded"
                                                    width="50"
                                                    src="{{ asset('img/products/product-1-50.png') }}"
                                                    alt="product">
                                            </a>
                                            <div class="media-body">
                                                <div class="media-right">$405</div>
                                                <div class="media-title"><a href="#">PlayStation 9</a></div>
                                                <div class="text-muted text-small">by <a href="#">Hasan Basri</a>
                                                    <div class="bullet"></div> Sunday
                                                </div>
                                            </div>
                                        </li>
                                        <li class="media">
                                            <a href="#">
                                                <img class="mr-3 rounded"
                                                    width="50"
                                                    src="{{ asset('img/products/product-2-50.png') }}"
                                                    alt="product">
                                            </a>
                                            <div class="media-body">
                                                <div class="media-right">$499</div>
                                                <div class="media-title"><a href="#">RocketZ</a></div>
                                                <div class="text-muted text-small">by <a href="#">Hasan Basri</a>
                                                    <div class="bullet"></div> Sunday
                                                </div>
                                            </div>
                                        </li>
                                        <li class="media">
                                            <a href="#">
                                                <img class="mr-3 rounded"
                                                    width="50"
                                                    src="{{ asset('img/products/product-3-50.png') }}"
                                                    alt="product">
                                            </a>
                                            <div class="media-body">
                                                <div class="media-right">$149</div>
                                                <div class="media-title"><a href="#">Xiaomay Readme 4.0</a></div>
                                                <div class="text-muted text-small">by <a href="#">Kusnaedi</a>
                                                    <div class="bullet"></div> Tuesday
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 class="d-inline">Tasks</h4>
                            <div class="card-header-action">
                                <a href="#"
                                    class="btn btn-primary">View All</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled list-unstyled-border">
                                <li class="media">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox"
                                            class="custom-control-input"
                                            id="cbx-1">
                                        <label class="custom-control-label"
                                            for="cbx-1"></label>
                                    </div>
                                    <img class="rounded-circle mr-3"
                                        width="50"
                                        src="{{ asset('img/avatar/avatar-4.png') }}"
                                        alt="avatar">
                                    <div class="media-body">
                                        <div class="badge badge-pill badge-danger float-right mb-1">Not Finished</div>
                                        <h6 class="media-title"><a href="#">Redesign header</a></h6>
                                        <div class="text-small text-muted">Alfa Zulkarnain <div class="bullet"></div>
                                            <span class="text-primary">Now</span>
                                        </div>
                                    </div>
                                </li>
                                <li class="media">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox"
                                            class="custom-control-input"
                                            id="cbx-2"
                                            checked="">
                                        <label class="custom-control-label"
                                            for="cbx-2"></label>
                                    </div>
                                    <img class="rounded-circle mr-3"
                                        width="50"
                                        src="{{ asset('img/avatar/avatar-5.png') }}"
                                        alt="avatar">
                                    <div class="media-body">
                                        <div class="badge badge-pill badge-primary float-right mb-1">Completed</div>
                                        <h6 class="media-title"><a href="#">Add a new component</a></h6>
                                        <div class="text-small text-muted">Serj Tankian <div class="bullet"></div> 4 Min
                                        </div>
                                    </div>
                                </li>
                                <li class="media">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox"
                                            class="custom-control-input"
                                            id="cbx-3">
                                        <label class="custom-control-label"
                                            for="cbx-3"></label>
                                    </div>
                                    <img class="rounded-circle mr-3"
                                        width="50"
                                        src="{{ asset('img/avatar/avatar-2.png') }}"
                                        alt="avatar">
                                    <div class="media-body">
                                        <div class="badge badge-pill badge-warning float-right mb-1">Progress</div>
                                        <h6 class="media-title"><a href="#">Fix modal window</a></h6>
                                        <div class="text-small text-muted">Ujang Maman <div class="bullet"></div> 8 Min
                                        </div>
                                    </div>
                                </li>
                                <li class="media">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox"
                                            class="custom-control-input"
                                            id="cbx-4">
                                        <label class="custom-control-label"
                                            for="cbx-4"></label>
                                    </div>
                                    <img class="rounded-circle mr-3"
                                        width="50"
                                        src="{{ asset('img/avatar/avatar-1.png') }}"
                                        alt="avatar">
                                    <div class="media-body">
                                        <div class="badge badge-pill badge-danger float-right mb-1">Not Finished</div>
                                        <h6 class="media-title"><a href="#">Remove unwanted classes</a></h6>
                                        <div class="text-small text-muted">Farhan A Mujib <div class="bullet"></div> 21
                                            Min</div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-5 col-md-12 col-12 col-sm-12">
                    <form method="post"
                        class="needs-validation"
                        novalidate="">
                        <div class="card">
                            <div class="card-header">
                                <h4>Quick Draft</h4>
                            </div>
                            <div class="card-body pb-0">
                                <div class="form-group">
                                    <label>Title</label>
                                    <input type="text"
                                        name="title"
                                        class="form-control"
                                        required>
                                    <div class="invalid-feedback">
                                        Please fill in the title
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Content</label>
                                    <textarea class="summernote-simple"></textarea>
                                </div>
                            </div>
                            <div class="card-footer pt-0">
                                <button class="btn btn-primary">Save Draft</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-7 col-md-12 col-12 col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Latest Posts</h4>
                            <div class="card-header-action">
                                <a href="#"
                                    class="btn btn-primary">View All</a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table-striped mb-0 table">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Author</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                Introduction Laravel 5
                                                <div class="table-links">
                                                    in <a href="#">Web Development</a>
                                                    <div class="bullet"></div>
                                                    <a href="#">View</a>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="#"
                                                    class="font-weight-600"><img
                                                        src="{{ asset('img/avatar/avatar-1.png') }}"
                                                        alt="avatar"
                                                        width="30"
                                                        class="rounded-circle mr-1"> Bagus Dwi Cahya</a>
                                            </td>
                                            <td>
                                                <a class="btn btn-primary btn-action mr-1"
                                                    data-toggle="tooltip"
                                                    title="Edit"><i class="fas fa-pencil-alt"></i></a>
                                                <a class="btn btn-danger btn-action"
                                                    data-toggle="tooltip"
                                                    title="Delete"
                                                    data-confirm="Are You Sure?|This action can not be undone. Do you want to continue?"
                                                    data-confirm-yes="alert('Deleted')"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                Laravel 5 Tutorial - Installation
                                                <div class="table-links">
                                                    in <a href="#">Web Development</a>
                                                    <div class="bullet"></div>
                                                    <a href="#">View</a>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="#"
                                                    class="font-weight-600"><img
                                                        src="{{ asset('img/avatar/avatar-1.png') }}"
                                                        alt="avatar"
                                                        width="30"
                                                        class="rounded-circle mr-1"> Bagus Dwi Cahya</a>
                                            </td>
                                            <td>
                                                <a class="btn btn-primary btn-action mr-1"
                                                    data-toggle="tooltip"
                                                    title="Edit"><i class="fas fa-pencil-alt"></i></a>
                                                <a class="btn btn-danger btn-action"
                                                    data-toggle="tooltip"
                                                    title="Delete"
                                                    data-confirm="Are You Sure?|This action can not be undone. Do you want to continue?"
                                                    data-confirm-yes="alert('Deleted')"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                Laravel 5 Tutorial - MVC
                                                <div class="table-links">
                                                    in <a href="#">Web Development</a>
                                                    <div class="bullet"></div>
                                                    <a href="#">View</a>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="#"
                                                    class="font-weight-600"><img
                                                        src="{{ asset('img/avatar/avatar-1.png') }}"
                                                        alt="avatar"
                                                        width="30"
                                                        class="rounded-circle mr-1"> Bagus Dwi Cahya</a>
                                            </td>
                                            <td>
                                                <a class="btn btn-primary btn-action mr-1"
                                                    data-toggle="tooltip"
                                                    title="Edit"><i class="fas fa-pencil-alt"></i></a>
                                                <a class="btn btn-danger btn-action"
                                                    data-toggle="tooltip"
                                                    title="Delete"
                                                    data-confirm="Are You Sure?|This action can not be undone. Do you want to continue?"
                                                    data-confirm-yes="alert('Deleted')"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                Laravel 5 Tutorial - Migration
                                                <div class="table-links">
                                                    in <a href="#">Web Development</a>
                                                    <div class="bullet"></div>
                                                    <a href="#">View</a>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="#"
                                                    class="font-weight-600"><img
                                                        src="{{ asset('img/avatar/avatar-1.png') }}"
                                                        alt="avatar"
                                                        width="30"
                                                        class="rounded-circle mr-1"> Bagus Dwi Cahya</a>
                                            </td>
                                            <td>
                                                <a class="btn btn-primary btn-action mr-1"
                                                    data-toggle="tooltip"
                                                    title="Edit"><i class="fas fa-pencil-alt"></i></a>
                                                <a class="btn btn-danger btn-action"
                                                    data-toggle="tooltip"
                                                    title="Delete"
                                                    data-confirm="Are You Sure?|This action can not be undone. Do you want to continue?"
                                                    data-confirm-yes="alert('Deleted')"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                Laravel 5 Tutorial - Deploy
                                                <div class="table-links">
                                                    in <a href="#">Web Development</a>
                                                    <div class="bullet"></div>
                                                    <a href="#">View</a>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="#"
                                                    class="font-weight-600"><img
                                                        src="{{ asset('img/avatar/avatar-1.png') }}"
                                                        alt="avatar"
                                                        width="30"
                                                        class="rounded-circle mr-1"> Bagus Dwi Cahya</a>
                                            </td>
                                            <td>
                                                <a class="btn btn-primary btn-action mr-1"
                                                    data-toggle="tooltip"
                                                    title="Edit"><i class="fas fa-pencil-alt"></i></a>
                                                <a class="btn btn-danger btn-action"
                                                    data-toggle="tooltip"
                                                    title="Delete"
                                                    data-confirm="Are You Sure?|This action can not be undone. Do you want to continue?"
                                                    data-confirm-yes="alert('Deleted')"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                Laravel 5 Tutorial - Closing
                                                <div class="table-links">
                                                    in <a href="#">Web Development</a>
                                                    <div class="bullet"></div>
                                                    <a href="#">View</a>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="#"
                                                    class="font-weight-600"><img
                                                        src="{{ asset('img/avatar/avatar-1.png') }}"
                                                        alt="avatar"
                                                        width="30"
                                                        class="rounded-circle mr-1"> Bagus Dwi Cahya</a>
                                            </td>
                                            <td>
                                                <a class="btn btn-primary btn-action mr-1"
                                                    data-toggle="tooltip"
                                                    title="Edit"><i class="fas fa-pencil-alt"></i></a>
                                                <a class="btn btn-danger btn-action"
                                                    data-toggle="tooltip"
                                                    title="Delete"
                                                    data-confirm="Are You Sure?|This action can not be undone. Do you want to continue?"
                                                    data-confirm-yes="alert('Deleted')"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <!-- JS Libraies -->
    <script src="{{ asset('library/simpleweather/jquery.simpleWeather.min.js') }}"></script>
    <script src="{{ asset('library/chart.js/dist/Chart.min.js') }}"></script>
    <script src="{{ asset('library/jqvmap/dist/jquery.vmap.min.js') }}"></script>
    <script src="{{ asset('library/jqvmap/dist/maps/jquery.vmap.world.js') }}"></script>
    <script src="{{ asset('library/summernote/dist/summernote-bs4.min.js') }}"></script>
    <script src="{{ asset('library/chocolat/dist/js/jquery.chocolat.min.js') }}"></script>

    <!-- Page Specific JS File -->
    <script src="{{ asset('js/page/index-0.js') }}"></script>
@endpush --}}
{{-- @extends('layouts.app')
@section('title', 'Dashboard Admin')
@push('style')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
@endpush
@section('title', 'Dashboard Admin')
@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Set Users</h1>
            </div>

            <div class="section-body">
                <style>
                    .text-center {
                        text-align: center;
                    }
                </style>
                <div class="row">
                    <div class="col-12">
                        <div class="card mb-4">
                            <div class="card-header pb-0">
                                <h6><i class="fas fa-user-shield"></i> Create Role users</h6>

                            </div>
                            <div class="card-body px-0 pt-0 pb-2">
                                <div class="table-responsive p-0">
                                    <table class="table align-items-center mb-0"id="users-table">
                                        <thead>
                                            <tr>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    No.</th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                    Name</th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                    Username</th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Access Rights</th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Role</th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Phone</th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Account Creation</th>
                                                <th
                                                    class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Action</th>
                                                <th>
                                            </tr>
                                        </thead>
                                    </table>
                                    <button type="button" onclick="window.location='{{ route('dashboardAdmin.create') }}'"
                                        class="btn btn-primary btn-sm">
                                        Create User
                                    </button>
                                    <button type="button" id="delete-selected" class="btn btn-danger btn-sm">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                    $(document).ready(function() {
                        let table = $('#users-table').DataTable({
                            processing: true,
                            serverSide: true,
                            ajax: '{{ route('users.users') }}',
                            lengthMenu: [
                                [10, 25, 50, 100, -1],
                                [10, 25, 50, 100, "All"]
                            ],
                            columns: [{
                                    data: null,
                                    name: 'id',
                                    className: 'text-center',
                                    render: function(data, type, row, meta) {
                                        return meta.row + 1;
                                    },
                                },
                                {
                                    data: 'name',
                                    name: 'name',
                                    className: 'text-center'
                                },

                                {
                                    data: 'username',
                                    name: 'username',
                                    className: 'text-center'
                                },
                                {
                                    data: 'user_type',
                                    name: 'user_type',
                                    className: 'text-center'
                                },
                                {
                                    data: 'role',
                                    name: 'role',
                                    className: 'text-center'
                                },
                                {
                                    data: 'phone',
                                    name: 'phone',
                                    className: 'text-center'
                                },
                                {
                                    data: 'created_at',
                                    name: 'created_at',
                                    className: 'text-center'
                                },
                                {
                                    data: 'action',
                                    name: 'action',
                                    orderable: false,
                                    searchable: false,
                                    className: 'text-center'
                                }
                            ]
                        });
                    });
                </script>

                @if (session('warning'))
                    <script>
                        Swal.fire({
                            icon: 'warning',
                            title: 'Oops...',
                            text: '{{ session('warning') }}',
                        });
                    </script>
                @endif
                @if (session('success'))
                    <script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: '{{ session('success') }}',
                        });
                    </script>
                @endif
                @if (session('warning'))
                    <script>
                        Swal.fire({
                            icon: 'warning',
                            title: 'Waduhhh',
                            text: '{{ session('warning') }}',
                        });
                    </script>
                @endif

            @endsection
        </div>
    </section>
</div>
@push('scripts')
@endpush --}}
@extends('layouts.app')
@section('title', 'Dashboard Admin')
@push('style')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        /* .text-center {
            text-align: center;
        }
        .users-table-container {
            padding: 20px;
        }
        .table-header {
            font-weight: 600;
            color: #333;
            padding: 15px 0;
        }
        .action-buttons {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .card:hover {
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .card-header {
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
        }
        .card-header h6 {
            margin: 0;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        .card-header h6 i {
            margin-right: 10px;
        }
        .btn-primary, .btn-danger {
            padding: 8px 16px;
            font-weight: 500;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background-color: #3d5af1;
            transform: translateY(-2px);
        }
        .btn-danger:hover {
            background-color: #e63946;
            transform: translateY(-2px);
        }
        #users-table_wrapper {
            padding: 0 15px;
        }
        #users-table_filter input {
            border-radius: 4px;
            border: 1px solid #ddd;
            padding: 6px 10px;
        }
        #users-table_length select {
            border-radius: 4px;
            border: 1px solid #ddd;
            padding: 6px 10px;
        }
        #users-table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .checkbox-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
        } */
    </style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Set Users</h1>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card mb-4">
                            <div class="card-header pb-0">
                                <h6><i class="fas fa-user-shield"></i> Create Role Users</h6>
                            </div>
                            <div class="card-body px-0 pt-0 pb-2">
                                <div class="table-responsive p-0 users-table-container">
                                    <table class="table align-items-center mb-0" id="users-table">
                                        <thead>
                                            <tr>
                                                
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    No.</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                    Name</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                    Username</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Access Rights</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Role</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Phone</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Account Creation</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                    <div class="action-buttons">
                                        <button type="button" onclick="window.location='{{ route('dashboardAdmin.create') }}'"
                                            class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus-circle"></i> Create User
                                        </button>
                                        {{-- <button type="button" id="delete-selected" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i> Delete Selected
                                        </button> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            let table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('users.users') }}',
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                columns: [
                    // {
                    //     data: null,
                    //     name: 'checkbox',
                    //     className: 'text-center',
                    //     orderable: false,
                    //     searchable: false,
                    //     render: function(data, type, row) {
                    //         return '<div class="checkbox-wrapper"><input type="checkbox" class="user-checkbox" value="' + row.id + '"></div>';
                    //     }
                    // },
                    {
                        data: null,
                        name: 'id',
                        className: 'text-center',
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        },
                    },
                    {
                        data: 'name',
                        name: 'name',
                        className: 'text-center'
                    },
                    {
                        data: 'username',
                        name: 'username',
                        className: 'text-center'
                    },
                    {
                        data: 'user_type',
                        name: 'user_type',
                        className: 'text-center'
                    },
                    {
                        data: 'role',
                        name: 'role',
                        className: 'text-center'
                    },
                    {
                        data: 'phone',
                        name: 'phone',
                        className: 'text-center'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        className: 'text-center'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ]
            });

            
        });

        // Alert handling
        @if (session('warning'))
            Swal.fire({
                icon: 'warning',
                title: 'Warning',
                text: '{{ session('warning') }}',
            });
        @endif
        
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session('success') }}',
            });
        @endif
    </script>
@endpush
{{-- // Select all checkbox functionality
            // $('#select-all').on('click', function() {
            //     $('.user-checkbox').prop('checked', this.checked);
            // });

            // Delete selected users functionality
            // $('#delete-selected').on('click', function() {
            //     const selectedIds = [];
            //     $('.user-checkbox:checked').each(function() {
            //         selectedIds.push($(this).val());
            //     });

            //     if (selectedIds.length === 0) {
            //         Swal.fire({
            //             icon: 'warning',
            //             title: 'Warning',
            //             text: 'Please select at least one user to delete.',
            //         });
            //         return;
            //     }

            //     Swal.fire({
            //         title: 'Are you sure?',
            //         text: "You won't be able to revert this!",
            //         icon: 'warning',
            //         showCancelButton: true,
            //         confirmButtonColor: '#3085d6',
            //         cancelButtonColor: '#d33',
            //         confirmButtonText: 'Yes, delete it!'
            //     }).then((result) => {
            //         if (result.isConfirmed) {
            //             $.ajax({
            //                 url: '{{ route('users.bulkDelete') }}',
            //                 type: 'POST',
            //                 data: {
            //                     ids: selectedIds,
            //                     _token: '{{ csrf_token() }}'
            //                 },
            //                 success: function(response) {
            //                     Swal.fire(
            //                         'Deleted!',
            //                         'Selected users have been deleted.',
            //                         'success'
            //                     );
            //                     table.ajax.reload();
            //                 },
            //                 error: function(error) {
            //                     Swal.fire(
            //                         'Error!',
            //                         'There was an error deleting users.',
            //                         'error'
            //                     );
            //                 }
            //             });
            //         }
            //     });
            // }); --}}