@extends('layouts.app')
@section('title', 'Documents')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
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

    /* Filter labels */
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .filter-group label {
        margin: 0;
        font-size: 0.7rem;
        font-weight: 600;
        /* text-transform: uppercase; */
        letter-spacing: 0.5px;
        color: #8898aa;
    }

    /* Select2 overrides to match form-select-sm styling */
    .select2-container {
        min-width: 200px;
    }

    .select2-container--default .select2-selection--single {
        height: calc(1.5em + 0.5rem + 2px);
        display: flex;
        align-items: center;
        border: 1px solid #dde2ec;
        border-radius: 0.35rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        font-size: 0.8125rem;
        color: #4a5568;
        line-height: normal;
        padding-left: 0.75rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(1.5em + 0.5rem);
    }

    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #5e72e4;
    }

    .select2-dropdown {
        border-color: #dde2ec;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #5e72e4;
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
                                <div class="d-flex align-items-center gap-2">
                                    {{-- <a href="{{ route('documents.create') }}" class="btn btn-sm btn-primary"
                                        title="Create a new document">
                                        <i class="fas fa-plus"></i> Buat Dokumen
                                    </a> --}}
                                    <button type="button" id="btn-toggle-all" class="btn btn-sm btn-light border"
                                        title="Select or deselect all documents on this page">
                                        <i class="fas fa-check-square"></i>
                                        <span id="btn-toggle-all-label">Select All</span>
                                    </button>
                                    <button type="button" id="btn-bulk-send" class="btn btn-sm btn-success" disabled
                                        title="Select at least one document to enable bulk send">
                                        <i class="fas fa-paper-plane"></i> Bulk Send
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-end flex-wrap gap-2 mb-3">
                                    <div class="filter-group" style="min-width: 200px;">
                                        <label for="filter-grading">Grading</label>
                                        <select id="filter-grading" class="form-select form-select-sm select2"
                                            style="width: 100%;">
                                            <option value="">All Gradings</option>
                                            @foreach ($gradings as $grading)
                                                <option value="{{ $grading }}">{{ $grading }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="filter-group" style="min-width: 260px;">
                                        <label for="filter-document-name">Document Name</label>
                                        <select id="filter-document-name" class="form-select form-select-sm select2"
                                            style="width: 100%;">
                                            <option value="">All Document Names</option>
                                            @foreach ($documentNames as $documentName)
                                                <option value="{{ $documentName }}">{{ $documentName }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="filter-group" style="min-width: 160px;">
                                        <label for="filter-status">Status</label>
                                        <select id="filter-status" class="form-select form-select-sm select2"
                                            style="width: 100%;">
                                            <option value="">All Statuses</option>
                                            <option value="draft">Draft</option>
                                            <option value="issued">Issued</option>
                                            <option value="revoked">Revoked</option>
                                            <option value="expired">Expired</option>
                                        </select>
                                    </div>
                                    <div class="filter-group" style="margin-left: 1rem;">
                                        <label class="invisible">Reset</label>
                                        <button type="button" id="btn-reset-filter"
                                            class="btn btn-sm btn-light border">
                                            <i class="fas fa-rotate-left"></i> Reset Filter
                                        </button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="users-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center"></th>
                                                <th class="text-center">Employee Name</th>
                                                <th class="text-center">Grading</th>
                                                <th class="text-center">Document Name</th>
                                                <th class="text-center">Document Number</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-history"></i> Document Activity Log</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="activity-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th class="text-center">Description</th>
                                                <th class="text-center">Causer</th>
                                                <th class="text-center">Date</th>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        jQuery(document).ready(function($) {
            $('#filter-grading, #filter-document-name, #filter-status').select2({
                width: 'resolve'
            });

            var table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ajax: {
                    url: '{{ route('documents.documents') }}',
                    type: 'GET',
                    data: function(d) {
                        d.filter_grading = $('#filter-grading').val();
                        d.filter_document_name = $('#filter-document-name').val();
                        d.filter_status = $('#filter-status').val();
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
                        data: 'status',
                        name: 'status',
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

            // ─── Document Activity Log ────────────────────────────────────
            $('#activity-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ajax: '{{ route('documents.activities') }}',
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
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'description',
                        name: 'description',
                        className: 'text-center'
                    },
                    {
                        data: 'causer',
                        name: 'causer',
                        className: 'text-center'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
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
                    text: @json(session('success')),
                });
            @endif

            // ─── CSRF for AJAX ──────────────────────────────────────────
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // ─── Filters (Grading / Document Name / Status) ──────────────
            $('#filter-grading, #filter-document-name, #filter-status').on('change', function() {
                table.ajax.reload();
            });

            $('#btn-reset-filter').on('click', function() {
                $('#filter-grading').val('').trigger('change');
                $('#filter-document-name').val('').trigger('change');
                $('#filter-status').val('').trigger('change');
            });

            // ─── Selection state (toggle-all + bulk send button) ────────
            function updateSelectionUI() {
                const $checkboxes = $('#users-table tbody .doc-checkbox:not(:disabled)');
                const checkedCount = $checkboxes.filter(':checked').length;
                const allChecked = $checkboxes.length > 0 && checkedCount === $checkboxes.length;

                $('#btn-toggle-all-label').text(allChecked ? 'Deselect All' : 'Select All');
                $('#btn-toggle-all i')
                    .toggleClass('fas fa-check-square', !allChecked)
                    .toggleClass('far fa-square', allChecked);

                $('#btn-bulk-send')
                    .prop('disabled', checkedCount === 0)
                    .attr('title', checkedCount === 0 ?
                        'Select at least one document to enable bulk send' :
                        `Send ${checkedCount} selected document(s)`)
                    .html('<i class="fas fa-paper-plane"></i> Bulk Send' +
                        (checkedCount ? ` (${checkedCount})` : ''));
            }

            $('#btn-toggle-all').on('click', function() {
                const $checkboxes = $('#users-table tbody .doc-checkbox:not(:disabled)');
                const allChecked = $checkboxes.length > 0 && $checkboxes.filter(':checked')
                    .length === $checkboxes.length;
                $checkboxes.prop('checked', !allChecked);
                updateSelectionUI();
            });

            $('#users-table').on('change', '.doc-checkbox', updateSelectionUI);

            // Row checkboxes are wiped out on every server-side redraw, so
            // the toggle-all / bulk-send state must be recomputed each time.
            table.on('draw', updateSelectionUI);

            // ─── Send Email (per row) ───────────────────────────────────
            $('#users-table').on('click', '.btn-send-document', function() {
                const url = $(this).data('url');
                const $btn = $(this);
                const originalHtml = $btn.html();

                Swal.fire({
                    icon: 'question',
                    title: 'Send this document?',
                    text: 'The document will be emailed as a PDF attachment.',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, send it',
                    confirmButtonColor: '#1d4ed8'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $btn.prop('disabled', true)
                        .html('<i class="fas fa-spinner fa-spin"></i> Sending...');

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
                            $btn.prop('disabled', false).html(originalHtml);
                        });
                });
            });

            // ─── Bulk Send ───────────────────────────────────────────────
            $('#btn-bulk-send').on('click', function() {
                const ids = $('#users-table tbody .doc-checkbox:checked')
                    .map(function() { return $(this).val(); })
                    .get();

                if (ids.length === 0) {
                    return;
                }

                const $btn = $(this);
                const originalHtml = $btn.html();

                Swal.fire({
                    icon: 'question',
                    title: 'Bulk send documents?',
                    text: `${ids.length} document(s) will be emailed as PDF attachments.`,
                    showCancelButton: true,
                    confirmButtonText: 'Yes, send them',
                    confirmButtonColor: '#1d4ed8'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $btn.prop('disabled', true)
                        .html('<i class="fas fa-spinner fa-spin"></i> Sending...');

                    $.post('{{ route('documents.bulk-send') }}', {
                            document_ids: ids
                        })
                        .done(function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Queued',
                                text: res.message || 'Documents have been queued for sending.'
                            });
                            $('#users-table tbody .doc-checkbox').prop('checked', false);
                            updateSelectionUI();
                        })
                        .fail(function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed',
                                text: 'Failed to queue the documents for sending.'
                            });
                        })
                        .always(function() {
                            $btn.prop('disabled', false).html(originalHtml);
                            updateSelectionUI();
                        });
                });
            });
        });
    </script>
@endpush
