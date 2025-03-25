{{-- @extends('layouts.app')
@section('title', 'Activity Logs')
@push('style')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        .text-center {
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
        }
    </style>
@endpush
@section('main')<div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Activity Logs</h1>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card mb-4">
                            <div class="card-header pb-0">
                                <h6><i class="fas fa-user-shield"></i>Activity Logs {{$activity->user->name}}</h6>
                            </div>
                            <div class="card-body px-0 pt-0 pb-2">
                                <div class="table-responsive p-0 users-table-container">
                                    <table class="table align-items-center mb-0" id="users-table">
                                        <thead>
                                            <tr>
                                                
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    No.</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                    Activity Type</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Activity Time</th>
                                            </tr>
                                        </thead>
                                    </table>
                                    <div class="action-buttons">
                                        <button type="button" onclick="window.location='{{ route('pages.Activity') }}'"
                                            class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus-circle"></i> All Activity
                                        </button>
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
                ajax: '{{ route('activity1.activity1') }}',
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                columns: [
                    {
                        data: null,
                        name: 'id',
                        className: 'text-center',
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        },
                    },
                    {
                        data: 'activity_type',
                        name: 'activity_type',
                        className: 'text-center'
                    },
                    {
                        data: 'activity_time',
                        name: 'activity_time',
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
@endpush --}}
@extends('layouts.app')

@section('title', 'Activity Logs')

@push('style')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    
    <style>
        /* Main Container Styles */
        /* .main-content {
            background-color: #f8f9fa;
        }
         */
        /* Card Styles */
        .card {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.15);
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
        }
        
        .card-header h6 {
            margin: 0;
            font-weight: 600;
            color: #495057;
            display: flex;
            align-items: center;
        }
        
        .card-header h6 i {
            margin-right: 0.75rem;
            color: #5e72e4;
        }
        
        /* Table Styles */
        .table-responsive {
            padding: 0 1.5rem;
        }
        
        .table thead th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            border-bottom-width: 1px;
            padding: 1rem 0.75rem;
        }
        
        .table tbody tr {
            transition: background-color 0.2s ease;
        }
        
        .table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .table tbody td {
            padding: 1rem 0.75rem;
            vertical-align: middle;
            color: #6c757d;
        }
        
        /* DataTable Customization */
        #users-table_wrapper {
            padding: 0;
        }
        
        #users-table_filter input {
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 0.375rem 0.75rem;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        
        #users-table_filter input:focus {
            border-color: #5e72e4;
            box-shadow: 0 0 0 0.2rem rgba(94, 114, 228, 0.25);
        }
        
        #users-table_length select {
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 0.375rem 1.75rem 0.375rem 0.75rem;
        }
        
        /* Section Header */
        .section-header h1 {
            font-weight: 600;
            color: #343a40;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .table-responsive {
                padding: 0 0.75rem;
            }
            
            .card-header {
                padding: 1rem;
            }
        }
    </style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Activity Logs</h1>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-user-shield"></i> Activity Logs {{$activity->user->name}}</h6>
                            </div>
                            
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <select id="activity-type-filter" class="form-control">
                                            <option value="">All Activity Types</option>
                                            <option value="Login">Login</option>
                                            <option value="Logout">Logout</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="users-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center">No.</th>
                                                <th class="text-center">Activity Type</th>
                                                <th class="text-center">Activity Time</th>
                                                <th class="text-center">Mac Wifi</th>
                                                <th class="text-center">Mac Lan</th>
                                            </tr>
                                        </thead>
                                    </table>
                                    <div class="action-buttons">
                                        <button type="button" onclick="window.location='{{ route('pages.Activity') }}'"
                                            class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus-circle"></i> All Activity
                                        </button>
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
    <!-- Script Libraries -->
    <script src="{{ asset('library/jquery/dist/jquery.min.js') }}"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('library/sweetalert/dist/sweetalert.min.js') }}"></script>
    
    {{-- <script>
        $(document).ready(function() {
            $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("activity1.activity1") }}',
                responsive: true,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                },
                columns: [
                    {
                        data: null,
                        name: 'id',
                        className: 'text-center align-middle',
                        render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1
                    },
                    {
                        data: 'activity_type',
                        name: 'activity_type',
                        className: 'text-center align-middle'
                    },
                    {
                        data: 'activity_time',
                        name: 'activity_time',
                        className: 'text-center align-middle'
                    }
                ],
                initComplete: function() {
                    $('.dataTables_filter input').addClass('form-control');
                    $('.dataTables_length select').addClass('form-control');
                }
            });
        });
    </script> --}}
    <script>
        $(document).ready(function() {
            var table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("activity1.activity1") }}',
                    data: function (d) {
                        d.activity_type = $('#activity-type-filter').val();
                    }
                },
                responsive: true,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                },
                columns: [
                    {
                        data: null,
                        name: 'id',
                        className: 'text-center align-middle',
                        render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1
                    },
                    {
                        data: 'activity_type',
                        name: 'activity_type',
                        className: 'text-center align-middle'
                    },
                    {
                        data: 'activity_time',
                        name: 'activity_time',
                        className: 'text-center align-middle'
                    },
                    {
    data: 'device_wifi_mac',
    name: 'device_wifi_mac',
    className: 'text-center align-middle',
    render: function(data, type, row) {
        return data === null ? 'Empty' : data;
    }
},
{
    data: 'device_lan_mac',
    name: 'device_lan_mac',
    className: 'text-center align-middle',
    render: function(data, type, row) {
        return data === null ? 'Empty' : data;
    }
}

                ],
                initComplete: function() {
                    $('.dataTables_filter input').addClass('form-control');
                    $('.dataTables_length select').addClass('form-control');
                }
            });

            // Add event listener for activity type filter change
            $('#activity-type-filter').change(function() {
                table.ajax.reload();
            });
        });
    </script>
@endpush
