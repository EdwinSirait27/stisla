{{-- @extends('layouts.app')
@section('title', 'Blank Page')
@push('style')
    <!-- CSS Libraries -->
@endpush
@section('main')<div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Blank Page</h1>
            </div>
            <div class="section-body">
                  <div class="action-buttons">
                                <button type="button" onclick="window.location='{{ route('pages.Importfingerspot') }}'"
                                    class="btn btn-dark btn-sm ml-2">
                                    <i class="fas fa-users"></i> Import Fingerspot
                                </button>
                                <button type="button" onclick="window.location='{{ route('pages.Importattendance') }}'"
                                    class="btn btn-dark btn-sm ml-2">
                                    <i class="fas fa-users"></i> Import Attendance
                                </button>
                                <!-- New button added here -->
                            </div>
            </div>
        </section>
    </div>
@endsection
@push('scripts')
    <!-- JS Libraies -->

    <!-- Page Specific JS File -->
@endpush --}}
@extends('layouts.app')
@section('title', 'Employees Fingerspot')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endpush
<style>
    /* Card Styles */
    .card {
        border: none;
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.08);
        border-radius: 0.5rem;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        background-color: #fff;
    }

    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.12);
    }

    .card-header {
        background-color: #f8fafc;
        border-bottom: 1px solid rgba(0, 0, 0, 0.03);
        padding: 1.25rem 1.5rem;
    }

    .card-header h6 {
        margin: 0;
        font-weight: 600;
        color: #4a5568;
        display: flex;
        align-items: center;
        font-size: 0.95rem;
    }

    .card-header h6 i {
        margin-right: 0.75rem;
        color: #5e72e4;
        transition: color 0.3s ease;
    }

    /* Table Styles */
    .table-responsive {
        padding: 0 1.5rem;
        overflow: hidden;
    }

    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table thead th {
        background-color: #f8fafc;
        color: #4a5568;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.5px;
        border: none;
        padding: 1rem 0.75rem;
        position: sticky;
        top: 0;
        z-index: 10;
        transition: all 0.3s ease;
    }

    .table tbody tr {
        transition: all 0.25s ease;
        position: relative;
    }

    .table tbody tr:not(:last-child)::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: rgba(0, 0, 0, 0.05);
    }

    .table tbody tr:hover {
        background-color: rgba(94, 114, 228, 0.03);
        transform: scale(1.002);
    }

    .table tbody td {
        padding: 1.1rem 0.75rem;
        vertical-align: middle;
        color: #4a5568;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        border: none;
        background: #fff;
    }

    .table tbody tr:hover td {
        color: #2d3748;
    }

    /* Text alignment for specific columns */
    .text-center {
        text-align: center;
    }

    /* Action Buttons */
    .action-buttons {
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: flex-end;
    }

    .btn-primary {
        background-color: #5e72e4;
        border-color: #5e72e4;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #4a5bd1;
        border-color: #4a5bd1;
        transform: translateY(-1px);
    }

    /* Section Header */
    .section-header h1 {
        font-weight: 600;
        color: #2d3748;
        font-size: 1.5rem;
    }

    /* Smooth scroll for table */
    .table-responsive {
        -webkit-overflow-scrolling: touch;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .table-responsive {
            padding: 0 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-header {
            padding: 1rem;
        }

        .table thead th {
            font-size: 0.65rem;
            padding: 0.75rem 0.5rem;
        }

        .table tbody td {
            padding: 0.85rem 0.5rem;
            font-size: 0.8rem;
        }
    }
</style>


@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Employees Fingerspot</h1>
            </div>
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-user-shield"></i> List Employees Fingerspot</h6>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="users-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center">No.</th>
                                                <th class="text-center">Name</th>
                                                <th class="text-center">Position</th>
                                                <th class="text-center">Departments</th>
                                                <th class="text-center">Store</th>
                                                <th class="text-center">Pin</th>
                                                <th class="text-center">Status</th>
                                                {{-- <th class="text-center">Length of service</th> --}}
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="action-buttons">
                                    <button type="button" onclick="window.location='{{ route('pages.Importfingerspot') }}'"
                                    class="btn btn-primary btn-sm ml-2">
                                    <i class="fas fa-users"></i> Syncronize Fingerspot
                                </button>
             
                                    <!-- New button added here -->
                                    <button type="button" onclick="window.location='{{ route('pages.Employeeall') }}'"
                                        class="btn btn-success btn-sm ml-2">
                                        <i class="fas fa-users"></i> All Employees
                                    </button>
                                </div>
                                {{-- <div class="d-flex justify-content-end mb-3">
                                    <div class="input-group me-2" style="max-width: 200px;">
                                        <span class="input-group-text">Date</span>
                                        <input type="date" id="payrollDate" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                    <button id="transferAllBtn" class="btn btn-primary">
                                        <i class="fas fa-money-bill-transfer"></i> Transfer All to Payroll
                                    </button>
                                </div> --}}
                                <div class="alert alert-secondary mt-4" role="alert">
                                    <span class="text-dark">
                                        <strong>Important Note:</strong> <br>
                                        - If you want to print payroll, ignore the day, just look at the year and month, you can only print payrolls once a month, okay.<br>
                                        <br>

                                    </span>
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
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Wait for jQuery to be fully loaded
        jQuery(document).ready(function($) {
            // Initialize DataTable with proper configuration
            var table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('fingerspot.fingerspot') }}',
                    type: 'GET'
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
                columns: [{
                        data: null,
                        name: 'id',
                        className: 'text-center align-middle',
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'employee_name',
                        name: 'employee_name',
                        className: 'text-center'
                    },
                    
                    {
                        data: 'position_name',
                        name: 'position_name',
                        className: 'text-center'
                    },
                    {
                        data: 'department_name',
                        name: 'department_name',
                        className: 'text-center'
                    },
                    {
                        data: 'name_store',
                        name: 'name_store',
                        className: 'text-center'
                    },
                    {
                        data: 'pin',
                        name: 'pin',
                        className: 'text-center'
                    },
                     {
                        data: 'status',
                        name: 'status',
                        className: 'text-center',
                        render: function(data, type, row) {
                            if (data === 'Active') {
                                return '<span class="badge bg-success">Active</span>';
                            } else if (data === 'Inactive') {
                                return '<span class="badge bg-danger">Inactive</span>';
                            } else if (data === 'On leave') {
                                return '<span class="badge bg-warning">On Leave</span>';
                            } else if (data === 'Mutation') {
                                return '<span class="badge bg-info">Mutation</span>';
                            } else if (data === 'Pending') {
                                return '<span class="badge bg-secondary">Pending</span>';
                            }
                            return '<span class="badge bg-secondary">Pending</span>';
                        }
                    },
                    

                    

                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                initComplete: function() {
                    $('.dataTables_filter input').addClass('form-control');
                    $('.dataTables_length select').addClass('form-control');
                }
            });

            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '{{ session('success') }}',
                });
            @endif

        });

//         $(document).ready(function() {
//     $('#transferAllBtn').on('click', function() {
//         // Ambil nilai tanggal dari input
//         const selectedDate = $('#payrollDate').val();
        
//         if (confirm('Are you sure you want to transfer all employee IDs to Payroll for ' + 
//                     new Date(selectedDate).toLocaleDateString('en-US', {day: 'numeric', month: 'long', year: 'numeric'}) + '?')) {
//             $.ajax({
//                 url: "{{ route('employees.transferAllToPayroll') }}",
//                 type: "POST",
//                 data: {
//                     "_token": "{{ csrf_token() }}",
//                     "month_year": selectedDate
//                 },
//                 success: function(response) {
//                     if (response.success) {
//                         alert(response.message);
//                     } else {
//                         alert('Error: ' + response.message);
//                     }
//                 },
//                 error: function(xhr) {
//                     alert('Error: ' + xhr.responseText);
//                 }
//             });
//         }
//     });
// });

$(document).ready(function() {
    $('#transferAllBtn').on('click', function() {
        // Ambil nilai tanggal dari input
        const selectedDate = $('#payrollDate').val();
        const formattedDate = new Date(selectedDate).toLocaleDateString('en-US', {
            day: 'numeric', 
            month: 'long', 
            year: 'numeric'
        });
        
        // Tampilkan konfirmasi dengan SweetAlert2
        Swal.fire({
            title: 'Confirm Transfer',
            text: `Are you sure you want to transfer all employee IDs to Payroll for ${formattedDate}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, transfer!',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading
                Swal.fire({
                    title: 'Processing...',
                    html: 'Please wait while we transfer the data.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Lakukan AJAX request
                $.ajax({
                    url: "{{ route('employees.transferAllToPayroll') }}",
                    type: "POST",
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "month_year": selectedDate
                    },
                    success: function(response) {
                        if (response.success) {
                            // Tampilkan hasil dengan SweetAlert2
                            Swal.fire({
                                title: 'Transfer Successful!',
                                html: `
                                    <div class="text-left">
                                        <p><strong>Period:</strong> ${response.period}</p>
                                        <p><strong>Transferred:</strong> ${response.transferred} employee(s)</p>
                                        <p><strong>Skipped:</strong> ${response.skipped} employee(s) (already exist)</p>
                                    </div>`,
                                icon: 'success',
                                confirmButtonText: 'Great!'
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr) {
                        // Tampilkan error dengan SweetAlert2
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to process your request. Please try again.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        console.error("Error:", xhr);
                    }
                });
            }
        });
    });
});
    </script>
@endpush

