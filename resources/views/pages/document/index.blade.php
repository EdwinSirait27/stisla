@extends('layouts.app')
@section('title', 'Documents')
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
        /* text-transform: uppercase; */
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
                <h1>Documents</h1>
            </div>
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <h6><i class="fas fa-user-shield"></i> List Documents</h6>
                                <div class="d-flex gap-2">
                                    <button type="button" id="btn-check-all" class="btn btn-sm btn-light border">
                                        <i class="fas fa-check-square"></i> Check All
                                    </button>
                                    <button type="button" id="btn-uncheck-all" class="btn btn-sm btn-light border">
                                        <i class="far fa-square"></i> Uncheck All
                                    </button>
                                    <button type="button" id="btn-bulk-send" class="btn btn-sm btn-success">
                                        <i class="fas fa-paper-plane"></i> Bulk Send
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="users-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center"></th>
                                                <th class="text-center">Employee Name</th>
                                                <th class="text-center">Grading</th>
                                                <th class="text-center">Document Name</th>
                                                <th class="text-center">Document Number</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                    </table>
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
        jQuery(document).ready(function($) {
            var table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ajax: {
                    url: '{{ route('documents.documents') }}',
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
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'employee_name',
                        name: 'employee_name',
                        className: 'text-center'
                    },
                    {
                        data: 'grading_name',
                        name: 'grading_name',
                        className: 'text-center'
                    },
                    {
                        data: 'document_name',
                        name: 'document_name',
                        className: 'text-center'
                    },
                    {
                        data: 'document_number',
                        name: 'document_number',
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

            // ─── CSRF for AJAX ──────────────────────────────────────────
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // ─── Check All / Uncheck All ────────────────────────────────
            $('#btn-check-all').on('click', function() {
                $('#users-table tbody .doc-checkbox').prop('checked', true);
            });

            $('#btn-uncheck-all').on('click', function() {
                $('#users-table tbody .doc-checkbox').prop('checked', false);
            });

            // ─── Send Email (per row) ───────────────────────────────────
            $('#users-table').on('click', '.btn-send-document', function() {
                const url = $(this).data('url');
                const $btn = $(this);

                Swal.fire({
                    icon: 'question',
                    title: 'Send this document?',
                    text: 'The document will be emailed as a PDF attachment.',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, send it',
                    confirmButtonColor: '#1d4ed8'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $btn.prop('disabled', true);

                    $.post(url)
                        .done(function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Queued',
                                text: res.message || 'Document has been queued for sending.'
                            });
                        })
                        .fail(function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed',
                                text: 'Failed to queue the document for sending.'
                            });
                        })
                        .always(function() {
                            $btn.prop('disabled', false);
                        });
                });
            });

            // ─── Bulk Send ───────────────────────────────────────────────
            $('#btn-bulk-send').on('click', function() {
                const ids = $('#users-table tbody .doc-checkbox:checked')
                    .map(function() { return $(this).val(); })
                    .get();

                if (ids.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No document selected',
                        text: 'Please select at least one document first.'
                    });
                    return;
                }

                Swal.fire({
                    icon: 'question',
                    title: 'Bulk send documents?',
                    text: `${ids.length} document(s) will be emailed as PDF attachments.`,
                    showCancelButton: true,
                    confirmButtonText: 'Yes, send them',
                    confirmButtonColor: '#1d4ed8'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $.post('{{ route('documents.bulk-send') }}', {
                            document_ids: ids
                        })
                        .done(function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Queued',
                                text: res.message || 'Documents have been queued for sending.'
                            });
                        })
                        .fail(function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed',
                                text: 'Failed to queue the documents for sending.'
                            });
                        });
                });
            });
        });
    </script>
@endpush
