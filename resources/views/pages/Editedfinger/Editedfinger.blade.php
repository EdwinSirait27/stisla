
@extends('layouts.app')
@section('title', 'Edited Fingerprints')

@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endpush

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
    }
    .table-responsive {
        padding: 0 1.5rem;
    }
    .table { width: 100%; border-collapse: separate; border-spacing: 0; }
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
    }
    .table tbody tr { transition: all 0.25s ease; }
    .table tbody td {
        padding: 1.1rem 0.75rem;
        vertical-align: middle;
        color: #4a5568;
        font-size: 0.85rem;
        border: none;
        background: #fff;
    }
    .table tbody tr:hover td { color: #2d3748; background-color: rgba(94, 114, 228, 0.03); }

    /* Modal attachment */
    #attachmentModal .modal-dialog { max-width: 700px; }
    #attachmentModal img {
        max-width: 100%;
        border-radius: 0.5rem;
        box-shadow: 0 0.25rem 0.75rem rgba(0,0,0,0.15);
    }
    #attachmentModal .modal-body {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 200px;
    }
    #attachmentLoadingSpinner {
        display: none;
        text-align: center;
        padding: 2rem;
    }

    @media (max-width: 768px) {
        .table-responsive { padding: 0 0.75rem; }
        .table thead th { font-size: 0.65rem; padding: 0.75rem 0.5rem; }
        .table tbody td { padding: 0.85rem 0.5rem; font-size: 0.8rem; }
    }
</style>

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Edited Fingerprints</h1>
            </div>
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-user-shield"></i> List Edited Fingerprints</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="users-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center">No</th>
                                                <th class="text-center">PIN</th>
                                                <th class="text-center">Employee Name</th>
                                                <th class="text-center">Position</th>
                                                <th class="text-center">Location</th>
                                                <th class="text-center">Scan Date</th>
                                                <th class="text-center">Scan 1</th>
                                                <th class="text-center">Device 1</th>
                                                <th class="text-center">Scan 2</th>
                                                <th class="text-center">Device 2</th>
                                                <th class="text-center">Scan 3</th>
                                                <th class="text-center">Device 3</th>
                                                <th class="text-center">Scan 4</th>
                                                <th class="text-center">Device 4</th>
                                                <th class="text-center">Scan 5</th>
                                                <th class="text-center">Device 5</th>
                                                <th class="text-center">Duration</th>
                                                <th class="text-center">Attachment</th>
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

    {{-- Modal Attachment --}}
    <div class="modal fade" id="attachmentModal" tabindex="-1" aria-labelledby="attachmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="attachmentModalLabel">
                        <i class="fas fa-image me-2"></i> Attachment
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{-- Loading spinner --}}
                    <div id="attachmentLoadingSpinner">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                        <p class="mt-2 text-muted">Loading attachment...</p>
                    </div>
                    {{-- Image --}}
                    <img id="attachmentImage" src="" alt="Attachment" style="display:none;">
                    {{-- Error --}}
                    <div id="attachmentError" style="display:none;" class="text-center text-danger">
                        <i class="fas fa-exclamation-circle fa-2x"></i>
                        <p class="mt-2">Gagal memuat attachment.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <a id="attachmentDownloadBtn" href="#" target="_blank" class="btn btn-success" style="display:none;">
                        <i class="fas fa-download me-1"></i> Buka di Tab Baru
                    </a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        jQuery(document).ready(function ($) {

            var table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ajax: {
                    url: '{{ route('editedfinger.editedfinger') }}',
                    type: 'GET'
                },
                responsive: true,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                language: {
                    search: '_INPUT_',
                    searchPlaceholder: 'Search...',
                },
                columns: [
                    {
                        data: null,
                        className: 'text-center',
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    { data: 'pin',           name: 'pin',           className: 'text-center' },
                    { data: 'employee_name', name: 'employee_name', className: 'text-center' },
                    { data: 'position_name', name: 'position_name', className: 'text-center' },
                    { data: 'store_name',    name: 'store_name',    className: 'text-center' },
                    { data: 'scan_date',     name: 'scan_date',     className: 'text-center' },
                    { data: 'in_1',          name: 'in_1',          className: 'text-center', render: d => d || '-' },
                    { data: 'device_1',      name: 'device_1',      className: 'text-center', render: d => d || '-' },
                    { data: 'in_2',          name: 'in_2',          className: 'text-center', render: d => d || '-' },
                    { data: 'device_2',      name: 'device_2',      className: 'text-center', render: d => d || '-' },
                    { data: 'in_3',          name: 'in_3',          className: 'text-center', render: d => d || '-' },
                    { data: 'device_3',      name: 'device_3',      className: 'text-center', render: d => d || '-' },
                    { data: 'in_4',          name: 'in_4',          className: 'text-center', render: d => d || '-' },
                    { data: 'device_4',      name: 'device_4',      className: 'text-center', render: d => d || '-' },
                    { data: 'in_5',          name: 'in_5',          className: 'text-center', render: d => d || '-' },
                    { data: 'device_5',      name: 'device_5',      className: 'text-center', render: d => d || '-' },
                    { data: 'duration',      name: 'duration',      className: 'text-center', render: d => d || '-' },
                    {
                        data: 'attachment',
                        name: 'attachment',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function (data, type, row) {
                            if (!data || data === '-') return '<span class="text-muted">-</span>';
                            return data; // HTML button dari controller
                        }
                    }
                ],
                initComplete: function () {
                    $('.dataTables_filter input').addClass('form-control');
                    $('.dataTables_length select').addClass('form-control');
                }
            });

            // ── Klik tombol Lihat Attachment ──
            $(document).on('click', '.btn-view-attachment', function () {
                const attachmentUrl = $(this).data('url');

                // Reset modal
                $('#attachmentImage').hide().attr('src', '');
                $('#attachmentLoadingSpinner').show();
                $('#attachmentError').hide();
                $('#attachmentDownloadBtn').hide();

                $('#attachmentModal').modal('show');

                // Load image
                const img = new Image();
                img.onload = function () {
                    $('#attachmentLoadingSpinner').hide();
                    $('#attachmentImage').attr('src', attachmentUrl).show();
                    $('#attachmentDownloadBtn').attr('href', attachmentUrl).show();
                };
                img.onerror = function () {
                    $('#attachmentLoadingSpinner').hide();
                    $('#attachmentError').show();
                };
                img.src = attachmentUrl;
            });

            // Reset modal saat ditutup
            $('#attachmentModal').on('hidden.bs.modal', function () {
                $('#attachmentImage').hide().attr('src', '');
                $('#attachmentLoadingSpinner').hide();
                $('#attachmentError').hide();
                $('#attachmentDownloadBtn').hide();
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