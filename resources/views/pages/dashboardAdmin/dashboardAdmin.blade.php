@extends('layouts.app')
@section('title', 'Dashboard Admin')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        /* ─── Page header ────────────────────────────────────── */
        .section-header h1 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 1.25rem;
        }

        /* ─── Card shell ─────────────────────────────────────── */
        .adm-card {
            border: none;
            border-radius: .625rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .07);
            background: #fff;
            overflow: hidden;
            margin-bottom: 1.25rem;
        }

        .adm-card-header {
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
            padding: .875rem 1.25rem;
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .adm-card-header-icon {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            flex-shrink: 0;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .adm-card-header-title {
            font-size: .9rem;
            font-weight: 600;
            color: #334155;
            flex: 1;
        }

        .adm-card-header-count {
            font-size: .72rem;
            color: #64748b;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: .15rem .7rem;
        }

        /* ─── Table ──────────────────────────────────────────── */
        #users-table {
            width: 100% !important;
            font-size: .8rem;
        }

        #users-table thead th {
            background: #f8fafc;
            color: #64748b;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .5px;
            padding: .7rem .9rem;
            border: none;
            border-bottom: 1px solid #f1f5f9;
            white-space: nowrap;
        }

        #users-table tbody td {
            padding: .75rem .9rem;
            vertical-align: middle;
            border: none;
            border-bottom: 1px solid #f8fafc;
            color: #334155;
        }

        #users-table tbody tr:last-child td {
            border-bottom: none;
        }

        #users-table tbody tr:hover td {
            background: #f8fafc;
        }

        /* ─── Status badges ──────────────────────────────────── */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: .18rem .6rem;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-active {
            background: #f0fdf4;
            color: #166534;
        }

        .badge-inactive {
            background: #fef2f2;
            color: #991b1b;
        }

        .badge-leave {
            background: #fffbeb;
            color: #92400e;
        }

        .badge-mutation {
            background: #eff6ff;
            color: #1e40af;
        }

        .badge-default {
            background: #f8fafc;
            color: #475569;
        }

        /* ─── Role badge ─────────────────────────────────────── */
        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: .18rem .6rem;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 700;
            background: #f5f3ff;
            color: #5b21b6;
            white-space: nowrap;
        }

        /* ─── Action buttons ─────────────────────────────────── */
        .action-wrap {
            display: flex;
            gap: 5px;
            justify-content: center;
        }

        .act-btn {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #64748b;
            font-size: .75rem;
            text-decoration: none;
            transition: all .15s;
        }

        .act-btn:hover {
            background: #f8fafc;
            color: #1e293b;
        }

        .act-btn.act-danger {
            border-color: #fecaca;
            background: #fef2f2;
            color: #dc2626;
        }

        .act-btn.act-danger:hover {
            background: #fee2e2;
        }

        /* ─── Footer action bar ──────────────────────────────── */
        .adm-footer {
            padding: .875rem 1.25rem;
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .adm-footer-hint {
            font-size: .75rem;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .adm-bulk-btn {
            height: 36px;
            padding: 0 1rem;
            font-size: .825rem;
            font-weight: 600;
            border-radius: .5rem;
            border: none;
            background: linear-gradient(135deg, #6777ef, #7c3aed);
            color: #fff;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(103, 119, 239, .3);
            transition: all .2s;
        }

        .adm-bulk-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(103, 119, 239, .4);
        }

        /* ─── DataTables overrides ───────────────────────────── */
        div.dataTables_wrapper div.dataTables_filter input,
        div.dataTables_wrapper div.dataTables_length select {
            height: 32px;
            font-size: .775rem;
            border: 1px solid #e2e8f0;
            border-radius: .4rem;
        }

        div.dataTables_wrapper div.dataTables_info {
            font-size: .75rem;
            color: #64748b;
            padding-top: .5rem;
        }

        div.dataTables_wrapper div.dataTables_paginate {
            padding-top: .3rem;
        }

        div.dataTables_wrapper div.dataTables_paginate .paginate_button {
            font-size: .75rem;
            border-radius: .375rem !important;
            padding: .2rem .5rem;
        }

        .dataTables_wrapper {
            padding: .75rem 1.25rem 1rem;
        }

        /* ─── Responsive ─────────────────────────────────────── */
        @media (max-width: 768px) {
            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">

            {{-- ── Page Header ── --}}
            <div class="section-header">
                <div>
                    <div style="font-size:.72rem;color:#94a3b8;margin-bottom:3px">
                        Dashboard / <span style="color:#64748b">Admin</span>
                    </div>
                    <h1>Dashboard Admin</h1>
                </div>
            </div>

            <div class="section-body">
                <div class="adm-card">

                    {{-- ── Card Header ── --}}
                    <div class="adm-card-header">
                        <div class="adm-card-header-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <span class="adm-card-header-title">List Users</span>
                        <span class="adm-card-header-count" id="user-count">Loading...</span>
                    </div>

                    {{-- ── Table ── --}}
                    <div class="table-responsive">
                        <table id="users-table" class="table">
                            <thead>
                                <tr>
                                    <th class="text-center">Choose</th>
                                    <th class="text-center">Name</th>
                                    <th class="text-center">Grading</th>
                                    <th class="text-center">Store</th>
                                    <th class="text-center">Position</th>
                                    <th class="text-center">Pin</th>
                                    <th class="text-center">Username</th>
                                    <th class="text-center">Account Creation</th>
                                    <th class="text-center">Mac Wifi</th>
                                    <th class="text-center">Mac Lan</th>
                                    <th class="text-center">Roles</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">2FA Status</th>
                                    <th class="text-center">2FA Action</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                    {{-- ── Footer action bar ── --}}
                    <div class="adm-footer">
                        <div class="adm-footer-hint">
                            <i class="fas fa-circle-info"></i>
                            Check users then click the button to bulk-assign role
                        </div>
                        <button id="bulk-update-btn" class="adm-bulk-btn">
                            <i class="fas fa-users-gear"></i> Update Role to Human
                        </button>
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
        jQuery(document).ready(function($) {

            /* ── Status badge helper ── */
            const STATUS_MAP = {
                'Active': 'badge-active',
                'Inactive': 'badge-inactive',
                'On leave': 'badge-leave',
                'Mutation': 'badge-mutation',
            };

            var table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ajax: {
                    url: '{{ route('users.users') }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                },
                dom: "<'row align-items-center mb-2'<'col-sm-6'l><'col-sm-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ],
                pageLength: 10,
                language: {
                    search: '',
                    searchPlaceholder: 'Search users...',
                    lengthMenu: 'Show _MENU_',
                    info: 'Showing _START_–_END_ of _TOTAL_',
                    infoEmpty: 'No users found',
                    paginate: {
                        previous: '‹',
                        next: '›'
                    }
                },
                columns: [{
                        data: 'checkbox',
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
                        name: 'Employee.grading.grading_name',
                        className: 'text-center'
                    },
                    {
                        data: 'store_name',
                        name: 'Employee.store.name',
                        className: 'text-center'
                    },
                    {
                        data: 'position_name',
                        name: 'position_name',
                        className: 'text-center'
                    },
                    {
                        data: 'pin',
                        name: 'pin',
                        className: 'text-center'
                    },
                    {
                        data: 'username',
                        name: 'username',
                        className: 'text-center'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        className: 'text-center',
                        render: d => d ? `<span style="font-size:.75rem;color:#64748b">${d}</span>` :
                            '-'
                    },
                    {
                        data: 'device_wifi_mac',
                        name: 'device_wifi_mac',
                        className: 'text-center',
                        render: d => d ? `<code style="font-size:.72rem;color:#475569">${d}</code>` :
                            '<span style="color:#cbd5e1">—</span>'
                    },
                    {
                        data: 'device_lan_mac',
                        name: 'device_lan_mac',
                        className: 'text-center',
                        render: d => d ? `<code style="font-size:.72rem;color:#475569">${d}</code>` :
                            '<span style="color:#cbd5e1">—</span>'
                    },
                    {
                        data: 'roles',
                        name: 'roles',
                        className: 'text-center',
                        render: d => d ?
                            `<span class="role-badge"><i class="fas fa-shield-halved"></i>${d}</span>` :
                            '-'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        className: 'text-center',
                        render: function(d) {
                            const cls = STATUS_MAP[d] || 'badge-default';
                            return `<span class="status-badge ${cls}">${d ?? '-'}</span>`;
                        }
                    },
                    { data: 'two_factor_status', name: 'two_factor_status', title: '2FA Status', orderable: false },
    { data: 'two_factor_action', name: 'two_factor_action', title: '2FA Action',  orderable: false, searchable: false },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: d => `<div class="action-wrap">${d}</div>`
                    }
                ],
                drawCallback: function(settings) {
                    const info = settings.json;
                    if (info && info.recordsTotal !== undefined) {
                        $('#user-count').text(info.recordsTotal + ' records');
                    }
                },
                initComplete: function() {
                    $('.dataTables_filter input').addClass('form-control form-control-sm');
                    $('.dataTables_length select').addClass('form-select form-select-sm');
                }
            });

            /* ── Auto-reload every 60s (skip when searching) ── */
            setInterval(function() {
                if (!$('.dataTables_filter input').val().trim()) {
                    table.ajax.reload(null, false);
                }
            }, 60000);

            /* ── Bulk update ── */
            $('#bulk-update-btn').on('click', function() {
                var ids = $('.user-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (!ids.length) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No users selected',
                        text: 'Please check at least one user before updating.',
                        confirmButtonColor: '#6777ef'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Update role?',
                    html: `<strong>${ids.length}</strong> selected user(s) will be assigned the <strong>Human</strong> role.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#6777ef',
                    cancelButtonColor: '#e2e8f0',
                    confirmButtonText: '<i class="fas fa-users-gear"></i> Yes, update',
                    cancelButtonText: 'Cancel',
                }).then(result => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: '{{ route('users.bulkUpdateRole') }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            user_ids: ids
                        },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Updated!',
                                    text: res.message,
                                    confirmButtonColor: '#6777ef'
                                });
                                table.ajax.reload(null, false);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: res.message
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to update users. Please try again.'
                            });
                        }
                    });
                });
            });

            /* ── Session flash ── */
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#6777ef',
                    timer: 3000,
                    timerProgressBar: true
                });
            @endif

        });
    </script>
@endpush
