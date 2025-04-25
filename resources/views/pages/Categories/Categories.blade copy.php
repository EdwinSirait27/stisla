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

                                        <select name="parent_id" id="parent-filter" class="form-control select2">
                                            <option value="">-- All Categories --</option>
                                            @php
                                                $renderOptions = function ($categories, $prefix = '') use (
                                                    &$renderOptions,
                                                ) {
                                                    foreach ($categories as $category) {
                                                        echo '<option value="' .
                                                            $category->id .
                                                            '">' .
                                                            $prefix .
                                                            htmlspecialchars($category->category_name) .
                                                            '</option>';

                                                        if (
                                                            isset($category->children) &&
                                                            $category->children &&
                                                            count($category->children)
                                                        ) {
                                                            $renderOptions($category->children, $prefix . '— ');
                                                        }
                                                    }
                                                };
                                                $renderOptions($categories);
                                            @endphp
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" class="form-control" id="search-category"
                                            placeholder="Search category...">
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="categories-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center">No.</th>
                                                <th class="text-center">Code</th>
                                                <th class="text-center">Parent</th>
                                                <th class="text-center">Category Name</th>
                                                <th class="text-center">Action</th>
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
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('#parent-filter').select2({
                placeholder: "Select parent category",
                allowClear: true
            });

            const table = $('#categories-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('categories.categories') }}',
                    data: function(d) {
                        d.parent_id = $('#parent-filter').val();
                        d.category_name = $('#search-category').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        className: 'text-center',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'category_code',
                        name: 'category_code',
                        className: 'text-center'
                    },
                    {
                        data: 'parent_name',
                        name: 'parent.name',
                        defaultContent: '-',
                        className: 'text-center'
                    },
                    {
                        data: 'category_name',
                        name: 'category_name',
                        className: 'text-center',
                        render: function(data, type, row) {
                            return row.full_category_name || data;
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
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                },
                dom: '<"top"lf>rt<"bottom"ip>',
                initComplete: function() {
                    $('.dataTables_filter').hide();
                }
            });

            $('#search-category').on('keyup', function() {
                table.draw();
            });

            $('#parent-filter').on('change', function() {
                table.draw();
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
{{-- @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <style>
        .select2-container--default .select2-selection--single {
            height: calc(2.25rem + 2px);
            padding: .375rem .75rem;
        }
    </style>
@endpush --}}
{{-- @section('main')
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
                                <h6><i class="fas fa-user-shield"></i>Categories</i>
                            </div>

                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-3">

                                        <select name="parent_id" id="categories-type-filter" class="form-control">
                                            <option value="">-- Tanpa Parent --</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}"
                                                    {{ old('parent_id') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->full_category_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="users-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center">No.</th>
                                                <th class="text-center">Category Code</th>
                                                <th class="text-center">Category Name</th>
                                                <th class="text-center">Parent</th>
                                                <th class="text-center">Number of Subcategories</th>
                                                <th class="text-center">Action</th>

                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="action-buttons">
                                    <button type="button" onclick="window.location='{{ route('Categories.create') }}'"
                                        class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus-circle"></i> Create Categories
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
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            const table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('categories.categories') }}',
                    data: function(d) {
                        d.category_name = $('#search-category').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        className: 'text-center',

                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'category_code',
                        name: 'category_code',
                        className: 'text-center'
                    },
                    {
                        data: 'full_category_name',
                        name: 'category_name',
                        className: 'text-center',

                        render: function(data, type, row) {
                            return `<strong>${row.category_name}</strong>${row.parent_id ? ` <small class="text-muted">(${row.full_category_name})</small>` : ''}`;
                        }
                    },
                    {
                        data: 'parent_name',
                        name: 'parent_name',
                        defaultContent: '-',
                        className: 'text-center'

                    },
                    {
                        data: 'children_count',
                        name: 'children_count',
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
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                }
            });

            $('#search-category').keyup(function() {
                table.draw();
            });
        });
    </script>
@endpush --}}
