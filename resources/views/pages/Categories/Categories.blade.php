@extends('layouts.app')
@section('title', 'Categories')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
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

        .highlight {
            background-color: #a2f5a2;
            /* Warna hijau muda */
            padding: 0.1em 0.2em;
            border-radius: 3px;
        }
    </style>
@endpush
@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Categories</h1>
            </div>
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-list-alt"></i> Categories</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-3">

                                    </div>

                                    <div class="col-md-2">

                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="categories-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center">No.</th>
                                                <th class="text-center">Department Code</th>
                                                <th class="text-center">Department Name</th>
                                                <th class="text-center">Categories Code</th>
                                                <th class="text-center">Categories Name</th>
                                                <th class="text-center">Sub Categories Code</th>
                                                <th class="text-center">Sub Categories Name</th>
                                                <th class="text-center">Family Code</th>
                                                <th class="text-center">Family Name</th>
                                                <th class="text-center">Sub Family Code</th>
                                                <th class="text-center">Sub Family Name</th>
                                                
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="action-buttons mt-3">
                                    <a href="{{ route('Categories.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus-circle"></i> Create Category
                                    </a>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mark.js/8.11.1/jquery.mark.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#categories-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('categories.categories') }}',
                    data: function(d) {
                        d.category_name = $('#category_name_filter').val();
                    }
                },
                responsive: true,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                pageLength: 10,
                language: {
                    lengthMenu: "Show _MENU_ entries",
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    },
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)"
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'level_1_code',
                        name: 'level_1_code',
                        className: 'text-center'
                    },
                    {
                        data: 'level_1_name',
                        name: 'level_1_name',
                        className: 'text-center'
                    },
                    {
                        data: 'level_2_code',
                        name: 'level_2_code',
                        className: 'text-center'
                    },
                    {
                        data: 'level_2_name',
                        name: 'level_2_name',
                        className: 'text-center'
                    },
                    {
                        data: 'level_3_code',
                        name: 'level_3_code',
                        className: 'text-center'
                    },
                    {
                        data: 'level_3_name',
                        name: 'level_3_name',
                        className: 'text-center'
                    },
                    {
                        data: 'level_4_code',
                        name: 'level_4_code',
                        className: 'text-center'
                    },
                    {
                        data: 'level_4_name',
                        name: 'level_4_name',
                        className: 'text-center'
                    },
                    {
                        data: 'level_5_code',
                        name: 'level_5_code',
                        className: 'text-center'
                    },
                    {
                        data: 'level_5_name',
                        name: 'level_5_name',
                        className: 'text-center'
                    }
                ],
                order: [
                    [1, 'asc'],
                    [3, 'asc'],
                    [5, 'asc'],
                    [7, 'asc'],
                    [9, 'asc']
                ],
                initComplete: function() {
                    // Inisialisasi mark.js setelah tabel selesai dimuat
                    this.api().on('draw', function() {
                        highlightSearchResults();
                    });
                }
            });

            function highlightSearchResults() {
                var searchTerm = table.search();
                if (searchTerm) {
                    // Hapus highlight sebelumnya
                    $('#categories-table').unmark();

                    // Highlight teks yang sesuai dengan warna hijau
                    $('#categories-table').mark(searchTerm, {
                        className: 'highlight',
                        separateWordSearch: false,
                        done: function() {
                            console.log('Highlighting completed');
                        }
                    });
                } else {
                    $('#categories-table').unmark();
                }
            }

            $('#filter-btn').click(function() {
                table.ajax.reload(null, false, function() {
                    highlightSearchResults();
                });
            });

            $('#reset-filter-btn').click(function() {
                $('#category_name_filter').val('');
                table.search('').draw();
                $('#categories-table').unmark();
            });

            $('#category_name_filter').keypress(function(e) {
                if (e.which == 13) {
                    table.ajax.reload(null, false, function() {
                        highlightSearchResults();
                    });
                }
            });

            // Tambahkan event listener untuk pencarian global
            $('.dataTables_filter input').on('keyup', function() {
                setTimeout(function() {
                    highlightSearchResults();
                }, 500);
            });
        });

        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session('success') }}',
                timer: 3000
            });
        @endif
    </script>
@endpush
