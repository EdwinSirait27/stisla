@extends('layouts.app')
@section('title', 'Structuresnew')
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
            {{-- SECTION HEADER --}}
            <div class="section-header">
                <h1><i class="fas fa-sitemap"></i> Structures Overview</h1>
            </div>

            <div class="section-body">
                {{-- DATATABLES --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 text-primary">
                                    <i class="fas fa-list-ul me-1"></i> List Structures
                                </h6>
                                <button type="button" onclick="window.location='{{ route('Structuresnew.create') }}'"
                                    class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus-circle me-1"></i> Create Structure
                                </button>
                            </div>
  <form id="bulk-delete-form" method="POST" action="{{ route('structuresnew.bulkDelete') }}">
                                    @csrf
                                    @method('DELETE')
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle" id="users-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center">
                                                        <button type="button" id="select-all"
                                                            class="btn btn-primary btn-sm">
                                                            Select All
                                                        </button>
                                                    </th>
                                                <th class="text-center">Company</th>
                                                <th class="text-center">Department</th>
                                                <th class="text-center">Location</th>
                                                <th class="text-center">Position</th>
                                                <th class="text-center">Structure Code</th>
                                                <th class="text-center">Manager Location</th>
                                                <th class="text-center">Manager Department</th>
                                                <th class="text-center">Hierarchy</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                
                                
                                <form id="bulk-delete-form" action="{{ route('payrolls.bulkDelete') }}"
                                                                         method="POST">
                                                                         @csrf
                                                             <div class="d-flex flex-wrap gap-2 align-items-stretch">
                                                                     <input type="hidden" name="structure_ids" id="bulk-delete-hidden">
                                                                     <button type="submit" class="btn btn-danger h-100 d-flex align-items-center">
                                                                         <i class="fas fa-trash me-1"></i> Delete selected
                                                                     </button>
                                                                     </form>
                                </div>
                            </form>


                            </div>
                        </div>
                    </div>
                </div>

                {{-- ORG CHART --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-primary">
                                    <i class="fas fa-network-wired me-1"></i> Organization Chart
                                </h6>
                            </div>
                            <div class="card-body">
                                <div id="orgchart" style="height: 700px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    {{-- DataTables & SweetAlert --}}
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://balkan.app/js/OrgChart.js"></script>

    <script>
        $(document).ready(function() {
            // === DATATABLES ===
            var table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('structuresnew.structuresnew') }}',
                    type: 'GET'
                },
                responsive: true,
                autoWidth: false,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search..."
                },
                columns: [
                    {
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        className: 'text-center align-middle'
                    },
                    {
                        data: 'company_name',
                        name: 'company_name',
                        className: 'text-center'
                    },
                    {
                        data: 'department_name',
                        name: 'department_name',
                        className: 'text-center'
                    },
                    {
                        data: 'store_name',
                        name: 'store_name',
                        className: 'text-center'
                    },
                    {
                        data: 'position_name',
                        name: 'position_name',
                        className: 'text-center'
                    },
                    {
                        data: 'structure_code',
                        name: 'structure_code',
                        className: 'text-center'
                    },
                    {
                        data: 'is_manager_store',
                        name: 'is_manager_store',
                        className: 'text-center',
                        render: function(data) {
                            return data == 1 ?
                                '<span class="badge bg-success">Yes</span>' :
                                '<span class="badge bg-danger">No</span>';
                        }
                    },
                    {
                        data: 'is_manager_department',
                        name: 'is_manager_department',
                        className: 'text-center',
                        render: function(data) {
                            return data == 1 ?
                                '<span class="badge bg-success">Yes</span>' :
                                '<span class="badge bg-danger">No</span>';
                        }
                    },
                    {
                        data: 'parent',
                        name: 'parent',
                        className: 'text-center'
                    }
                    // {
                    //     data: 'checkbox',
                    //     name: 'checkbox',
                    //     orderable: false,
                    //     searchable: false,
                    //     className: 'text-center'
                    // }
                ],
                initComplete: function() {
                    // $('.dataTables_filter input').addClass('form-control form-control-sm');
                    // $('.dataTables_length select').addClass('form-select form-select-sm');
                }
            });

            // === SWEETALERT SUCCESS ===
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '{{ session('success') }}',
                    timer: 2000,
                    showConfirmButton: false
                });
            @endif

            // === ORGCHART ===
            fetch("{{ route('orgchart.orgchart') }}")
                .then(res => res.json())
                .then(data => {
                    new OrgChart(document.getElementById("orgchart"), {
                        nodes: data,
                        nodeBinding: {
                            field_0: "name",
                            field_1: "title"
                        },
                        template: "olivia",
                        collapse: {
                            level: 3
                        },
                        nodeMouseClick: OrgChart.action.none
                    });
                });
        });
    </script>

    <script>
        document.getElementById('bulk-delete-form').addEventListener('submit', function(e) {
            const checked = document.querySelectorAll('input.payroll-checkbox:checked');
            if (checked.length === 0) {
                e.preventDefault();
                Swal.fire("Gagal", "Tidak ada data yang dipilih.", "error");
                return;
            }

            e.preventDefault(); // jangan langsung submit

            Swal.fire({
                title: 'Yakin ingin menghapus data terpilih?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // ambil semua id payroll yang diceklis
                    const ids = Array.from(checked).map(cb => cb.value);
                    // document.getElementById('bulk-delete-hidden').value = JSON.stringify(ids);
                    document.getElementById('bulk-delete-hidden').value = ids.join(',');

                    e.target.submit();
                }
            });
        });
        $('#select-all').on('click', function() {
            // Ambil state sekarang (default false)
            let isChecked = $(this).data('checked') || false;

            // Toggle semua checkbox berdasarkan state
            $('input.payroll-checkbox').prop('checked', !isChecked);

            // Simpan state baru
            $(this).data('checked', !isChecked);

            // Ubah tulisan tombol
            $(this).text(!isChecked ? 'Deselect All' : 'Select All');
        });
    </script>
@endpush


{{-- 
@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Structuresnew</h1>
            </div>
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-user-shield"></i> List Structuresnew</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="users-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Company</th>
                                                <th class="text-center">Department</th>
                                                <th class="text-center">Locaton</th>
                                                <th class="text-center">Position</th>
                                                <th class="text-center">Structure Code</th>
                                                <th class="text-center">Manager Location</th>
                                                <th class="text-center">Manager Depart</th>
                                                <th class="text-center">Hierarchy</th>
                                             
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="action-buttons">
                                    <button type="button" onclick="window.location='{{ route('Structuresnew.create') }}'"
                                        class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus-circle"></i> Create Structure
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div class="container mt-5">
    <h4>Bagan Organisasi</h4>
    <div id="orgchart" style="height:700px;"></div>
</div>
@endsection
@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://balkan.app/js/OrgChart.js"></script>

    <script>
        jQuery(document).ready(function($) {
            var table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ajax: {
                    url: '{{ route('structuresnew.structuresnew') }}',
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
                columns: [

                    {
                        data: 'company_name',
                        name: 'company_name',
                        className: 'text-center'
                    },
                    {
                        data: 'department_name',
                        name: 'department_name',
                        className: 'text-center'
                    },
                    {
                        data: 'store_name',
                        name: 'store_name',
                        className: 'text-center'
                    },
                    {
                        data: 'position_name',
                        name: 'position_name',
                        className: 'text-center'
                    },
                    {
                        data: 'structure_code',
                        name: 'structure_code',
                        className: 'text-center'
                    },
                    {
                        data: 'is_manager_store',
                        name: 'is_manager_store',
                        className: 'text-center',
                        render: function(data, type, row) {
                            if (data == 1) {
                                return '<span class="badge bg-success">Yes</span>';
                            } else {
                                return '<span class="badge bg-danger">No</span>';
                            }
                        }
                    },
                    {
                        data: 'is_manager_department',
                        name: 'is_manager_department',
                        className: 'text-center',
                        render: function(data, type, row) {
                            if (data == 1) {
                                return '<span class="badge bg-success">Yes</span>';
                            } else {
                                return '<span class="badge bg-danger">No</span>';
                            }
                        }
                    },
                    {
                        data: 'parent',
                        name: 'parent',
                        className: 'text-center'
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
    </script>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    fetch("{{ route('orgchart.orgchart') }}")
        .then(res => res.json())
        .then(data => {
            var chart = new OrgChart(document.getElementById("orgchart"), {
                nodes: data,
                nodeBinding: {
                    field_0: "name",
                    field_1: "title"
                },
                template: "olivia"
            });
        });
});
</script>
    @endpush --}}
