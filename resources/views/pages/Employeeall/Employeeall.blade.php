@extends('layouts.app')
@section('title', 'Employees Details')
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
                <h1>Employees Detail Table</h1>
            </div>
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-user-shield"></i> List Employees Details</h6>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="users-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center">No.</th>
                                                <th class="text-center">Username</th>
                                                <th class="text-center">Employee Name</th>
                                                <th class="text-center">Employee ID</th>
                                                <th class="text-center">Position</th>
                                                <th class="text-center">Department</th>
                                                <th class="text-center">Store</th>
                                                <th class="text-center">Status Employee</th>
                                                <th class="text-center">Join Date</th>
                                                <th class="text-center">Marriage</th>
                                                <th class="text-center">Child</th>
                                                <th class="text-center">Telephone Number</th>
                                                <th class="text-center">NIK</th>
                                                <th class="text-center">Gender</th>
                                                <th class="text-center">Date of Birth</th>
                                                <th class="text-center">Place of Birth</th>
                                                <th class="text-center">Mother's Name</th>
                                                <th class="text-center">Religion</th>
                                                <th class="text-center">Current Address</th>
                                                <th class="text-center">ID Card Address</th>
                                                <th class="text-center">Last Education</th>
                                                <th class="text-center">Institution</th>
                                                <th class="text-center">NPWP</th>
                                                <th class="text-center">BPJS Kesehatan</th>
                                                <th class="text-center">BPJS Ketenagakerjaan</th>
                                                <th class="text-center">Email</th>
                                                <th class="text-center">Emergency Contact Name</th>
                                                <th class="text-center">Salary</th>
                                                <th class="text-center">House Allowance</th>
                                                <th class="text-center">Meal Allowance</th>
                                                <th class="text-center">Transport Allowance</th>
                                                <th class="text-center">Total Salary</th>
                                                <th class="text-center">Notes</th>
                                                <th class="text-center">Account Creation</th>
                                                <th class="text-center">Status</th>
                                               
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="action-buttons">
                                
                                    <!-- New button added here -->
                                    <button type="button" onclick="window.location='{{ route('pages.Employee') }}'" 
                                    class="btn btn-danger btn-sm ml-2">
                                <i class="fas fa-users"></i> Back To Employee
                            </button>
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
                    url: '{{ route('employeesall.employeesall') }}',
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
                        data: 'username',
                        name: 'username',
                        className: 'text-center'
                    },
                    {
                        data: 'employee_name',
                        name: 'employee_name',
                        className: 'text-center'
                    },
                    {
                        data: 'employee_pengenal',
                        name: 'employee_pengenal',
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
                        data: 'status_employee',
                        name: 'name_store',
                        className: 'text-center'
                    },
                    {
                        data: 'join_date',
                        name: 'join_date',
                        className: 'text-center'
                    },
                    {
                        data: 'marriage',
                        name: 'marriage',
                        className: 'text-center'
                    },
                    {
                        data: 'child',
                        name: 'child',
                        className: 'text-center'
                    },
                    {
                        data: 'telp_number',
                        name: 'telp_number',
                        className: 'text-center'
                    },
                    {
                        data: 'nik',
                        name: 'nik',
                        className: 'text-center'
                    },
                    {
                        data: 'gender',
                        name: 'gender',
                        className: 'text-center'
                    },
                    {
                        data: 'date_of_birth',
                        name: 'date_of_birth',
                        className: 'text-center'
                    },
                    {
                        data: 'place_of_birth',
                        name: 'place_of_birth',
                        className: 'text-center'
                    },
                    {
                        data: 'biological_mother_name',
                        name: 'biological_mother_name',
                        className: 'text-center'
                    },
                    {
                        data: 'religion',
                        name: 'religion',
                        className: 'text-center'
                    },
                    {
                        data: 'current_address',
                        name: 'current_address',
                        className: 'text-center'
                    },
                    {
                        data: 'id_card_address',
                        name: 'id_card_address',
                        className: 'text-center'
                    },
                    {
                        data: 'last_education',
                        name: 'last_education',
                        className: 'text-center'
                    },
                    {
                        data: 'institution',
                        name: 'institution',
                        className: 'text-center'
                    },
                    {
                        data: 'npwp',
                        name: 'npwp',
                        className: 'text-center'
                    },
                    {
                        data: 'bpjs_kes',
                        name: 'bpjs_kes',
                        className: 'text-center'
                    },
                    {
                        data: 'bpjs_ket',
                        name: 'bpjs_ket',
                        className: 'text-center'
                    },
                    {
                        data: 'email',
                        name: 'email',
                        className: 'text-center'
                    },
                    {
                        data: 'emergency_contact_name',
                        name: 'emergency_contact_name',
                        className: 'text-center'
                    },
                    {
                        data: 'salary',
                        name: 'salary',
                        className: 'text-center'
                    },
                    {
                        data: 'house_allowance',
                        name: 'house_allowance',
                        className: 'text-center'
                    },
                    {
                        data: 'meal_allowance',
                        name: 'meal_allowance',
                        className: 'text-center'
                    },
                    {
                        data: 'transport_allowance',
                        name: 'transport_allowance',
                        className: 'text-center'
                    },
                    {
                        data: 'total_salary',
                        name: 'total_salary',
                        className: 'text-center'
                    },
                    {
                        data: 'notes',
                        name: 'notes',
                        className: 'text-center'
                    },
                  
                    {
                        data: 'created_at',
                        name: 'created_at',
                        className: 'text-center'
                    },
                    {
    data: 'status',
    name: 'status',
    className: 'text-center',
    render: function (data, type, row) {
        if (data === 'Active') {
            return '<span class="badge bg-success">Active</span>';
        } else if (data === 'Inactive') {
            return '<span class="badge bg-danger">Inactive</span>';
        } else if (data === 'On leave') {
            return '<span class="badge bg-warning">On Leave</span>';
        } else if (data === 'Mutation') {
            return '<span class="badge bg-info">Tidak Aktif</span>';
        } else if (data === 'Pending') {
            return '<span class="badge bg-secondary">Pending</span>';
        }
        return '<span class="badge bg-secondary">Pending</span>';
    }
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
    </script>
@endpush
