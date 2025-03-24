@extends('layouts.app')
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
                                    {{-- <button type="button" id="delete-selected" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i> Delete Selected
                                    </button> --}}
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
@endpush
