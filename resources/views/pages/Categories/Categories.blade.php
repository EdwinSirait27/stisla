@extends('layouts.app')
@section('title', 'Categories')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
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
                                <h6><i class="fas fa-user-shield"></i>Categories</i>
                            </div>

                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        {{-- <select id="categories-type-filter" class="form-control" name="parent_id">
                                            <option value="">-- Choose Categories --</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category }}">{{ $category }}</option>
                                            @endforeach
                                        </select> --}}
                                        <select name="parent_id" id="categories-type-filter" class="form-control">
                                            <option value="">-- Tanpa Parent --</option>
                                            @foreach($parentCategories as $category)
                                                <option value="{{ $category->id }}" {{ old('parent_id') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->full_category_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        {{-- <select name="parent_id" id="parent_id" class="form-control">
                                            <option value="">-- Tanpa Parent --</option>
                                            @foreach($parentCategories as $category)
                                                <option value="{{ $category->id }}" {{ old('parent_id') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->full_category_name }}
                                                </option>
                                            @endforeach
                                        </select> --}}
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
                                                {{-- <th width="5%">No</th>
                                                <th>Kode</th>
                                                <th>Nama Kategori</th>
                                                <th>Parent</th>
                                                <th>Jumlah Sub</th>
                                                <th width="15%">Aksi</th> --}}
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
    <!-- Load required libraries -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    {{-- <script>
        $(document).ready(function() {
            var table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('categories.categories') }}',
                    data: function(d) {
                        d.brand_name = $('#categories-type-filter').val();
                    },
                    error: function(xhr, error, thrown) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Failed to load data!'
                        });
                        console.error(xhr.responseText);
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
                columns: [
                    {
                        data: null,
                        name: 'id',
                        className: 'text-center align-middle',
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'parent_id',
                        name: 'parent_id',
                        className: 'text-center align-middle'
                    },


                    {
                        data: 'category_code',
                        name: 'category_code',
                        className: 'text-center align-middle'   
                    },
                    {
                        data: 'category_name',
                        name: 'category_name',
                        className: 'text-center align-middle',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        className: 'text-center align-middle',
                        orderable: false,
                        searchable: false
                    }
                ],
                initComplete: function() {
                    $('.dataTables_filter input').addClass('form-control');
                    $('.dataTables_length select').addClass('form-control');
                }
            });

            $('#categories-type-filter').change(function() {
                table.ajax.reload();
            });

            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '{{ session('success') }}',
                    timer: 3000
                });
            @endif
        });
    </script> --}}
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

            // Search functionality
            $('#search-category').keyup(function() {
                table.draw();
            });
        });

        // Delete confirmation
        // function deleteCategory(url) {
        //     Swal.fire({
        //         title: 'Hapus Kategori?',
        //         text: "Anda tidak akan dapat mengembalikan ini!",
        //         icon: 'warning',
        //         showCancelButton: true,
        //         confirmButtonColor: '#3085d6',
        //         cancelButtonColor: '#d33',
        //         confirmButtonText: 'Ya, Hapus!'
        //     }).then((result) => {
        //         if (result.isConfirmed) {
        //             $.ajax({
        //                 url: url,
        //                 type: 'DELETE',
        //                 data: {
        //                     _token: '{{ csrf_token() }}'
        //                 },
        //                 success: function(response) {
        //                     if (response.success) {
        //                         $('#categories-table').DataTable().draw();
        //                         Swal.fire('Terhapus!', response.message, 'success');
        //                     }
        //                 },
        //                 error: function(xhr) {
        //                     Swal.fire('Error!', xhr.responseJSON.message, 'error');
        //                 }
        //             });
        //         }
        //     });
        // }
    </script>
@endpush
{{-- @extends('layouts.app')
@section('title', 'Brands')

@section('main')

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Kategori</h5>
                    <a href="{{ route('Categories.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-2"></i>Tambah Kategori
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="categories-table" class="table table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Kode</th>
                                    <th>Nama Kategori</th>
                                    <th>Parent</th>
                                    <th>Jumlah Sub</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const table = $('#categories-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("categories.categories") }}',
            data: function(d) {
                d.category_name = $('#search-category').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'category_code', name: 'category_code' },
            { 
                data: 'full_category_name', 
                name: 'category_name',
                render: function(data, type, row) {
                    return `<strong>${row.category_name}</strong>${row.parent_id ? ` <small class="text-muted">(${row.full_category_name})</small>` : ''}`;
                }
            },
            { 
                data: 'parent_name', 
                name: 'parent_name',
                defaultContent: '-'
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

    // Search functionality
    $('#search-category').keyup(function() {
        table.draw();
    });
});

// Delete confirmation
function deleteCategory(url) {
    Swal.fire({
        title: 'Hapus Kategori?',
        text: "Anda tidak akan dapat mengembalikan ini!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        $('#categories-table').DataTable().draw();
                        Swal.fire('Terhapus!', response.message, 'success');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error!', xhr.responseJSON.message, 'error');
                }
            });
        }
    });
}
</script>
@endpush

@push('styles')
<style>
    #categories-table_filter {
        display: none;
    }
</style>
@endpush --}}
