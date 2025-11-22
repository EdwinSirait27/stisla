@extends('layouts.app')
@section('title', 'Structures')
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

    /* #tree {
        width: 100%;
        height: 100vh;
        background: #f5f5f5;
    } */
</style>

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1><i class="fas fa-sitemap"></i> Structures Overview</h1>
            </div>
            <div class="section-body">
                {{-- <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 text-primary">
                                    <i class="fas fa-network-wired me-1"></i> Organization Chart
                                </h6>
                               <button id="toggleSecondaryLinks" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye-slash me-1"></i>
                                    <span id="toggleText">Secondary Supervisors</span>
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="tree" style="height: 700px;"></div>
                            </div>
                        </div>
                    </div>
                </div> --}}
                <div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-primary">
                    <i class="fas fa-network-wired me-1"></i> Organization Chart
                </h6>
                <button id="toggleSecondaryLinks" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-eye-slash me-1"></i>
                    <span id="toggleText">Secondary Supervisors</span>
                </button>
            </div>
            <div class="card-body p-0">
                <div class="row g-0">
                    <!-- Grading Sidebar -->
                    <div class="col-auto border-end">
                        <div id="gradingSidebar" class="grading-sidebar-inline">
                            <div class="sidebar-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-layer-group me-2"></i>Grading Levels
                                </h6>
                            </div>
                            <div class="sidebar-content" id="gradingList">
                                <div class="grading-item active" data-grading="all">
                                    <span class="grading-badge all-badge">All</span>
                                    <span class="grading-count" id="count-all">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Organization Chart -->
                    <div class="col">
                        <div id="tree" style="height: 700px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



                <div class="row">


                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 text-primary">
                                    <i class="fas fa-list-ul me-1"></i> List Structures
                                </h6>
                                {{-- <button type="button" onclick="window.location='{{ route('Structuresnew.create') }}'"
                                    class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus-circle me-1"></i> Create Structure
                                </button> --}}
                            </div>



                            <form id="bulk-delete-form" method="POST" action="{{ route('structuresnew.bulkDelete') }}">
                                @csrf
                                @method('DELETE')
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover align-middle" id="users-table">
                                            <thead class="table-light">
                                                <tr>

                                                    <th class="text-center">Company</th>
                                                    <th class="text-center">Department</th>
                                                    <th class="text-center">Location</th>
                                                    <th class="text-center">Position</th>
                                                    <th class="text-center">Structure Code</th>
                                                    <th class="text-center">Is Manager?</th>
                                                    <th class="text-center">Direct Superior</th>
                                                    <th class="text-center">All Subordinate</th>
                                                    <th class="text-center">Status</th>
                                                    <th class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>


                                    {{-- <form id="bulk-delete-form" action="{{ route('structuresnew.bulkDelete') }}"
                                        method="POST">
                                        @csrf
                                        <div class="d-flex flex-wrap gap-2 align-items-stretch">
                                            <input type="hidden" name="structure_ids" id="bulk-delete-hidden">
                                            <button type="submit" class="btn btn-danger h-100 d-flex align-items-center">
                                                <i class="fas fa-trash me-1"></i> Delete selected
                                            </button>
                                    </form> --}}
                                </div>
                            </form>


                        </div>
                    </div>
                </div>
            </div>
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Manager Submissions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table-striped" id="submissions-table">
                            <thead>
                                <tr>
                                    <th class="text-center">Manager</th>
                                    <th class="text-center">Position Request</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">action</th>
                                    {{-- <th class="text-center">Date</th> --}}
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>



            <div class="card mt-4">
                <div class="card-header">
                    <h5>Structures History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="activityTable" class="table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th class="text-center">Description</th>
                                    <th class="text-center">By</th>
                                    <th class="text-center">Date</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

        </section>
    </div>
    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Preview Submission Position</h5>
                </div>
                <div class="modal-body">
                    <table class="table table-striped align-middle">
                        <tr>
                            <th style="width: 25%;">Company</th>
                            <td id="preview-company"></td>
                        </tr>
                        <tr>
                            <th>Department</th>
                            <td id="preview-department"></td>
                        </tr>
                        <tr>
                            <th>Manager Name</th>
                            <td id="preview-manager"></td>
                        </tr>
                        <tr>
                            <th>Location Request</th>
                            <td id="preview-store"></td>
                        </tr>
                        <tr>
                            <th>Position Request</th>
                            <td id="preview-position"></td>
                        </tr>
                        <tr>
                            <th>Role Summary</th>
                            <td id="preview-role-summary"></td>
                        </tr>
                        <tr>
                            <th>Key Responsibility</th>
                            <td id="preview-key-responsibility"></td>
                        </tr>
                        <tr>
                            <th>Qualifications</th>
                            <td id="preview-qualifications"></td>
                        </tr>
                        <tr>
                            <th>HRD Approver</th>
                            <td id="preview-approver1"></td>
                        </tr>
                        <tr>
                            <th>DIR Approver</th>
                            <td id="preview-approver2"></td>
                        </tr>
                        <tr>
                            <th>Salary</th>
                            <td id="preview-salary"></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td id="preview-status"></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://balkan.app/js/OrgChart.js"></script>
    <script>
        $(function() {
            $('#activityTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('datastructures.datastructures') }}",
                columns: [{
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
                    // { data: 'changes', name: 'changes' },
                    {
                        data: 'causer',
                        name: 'causer',
                        className: 'text-center'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        className: 'text-center'
                    },
                ],
                order: [
                    [3, 'desc']
                ],
                language: {
                    searchPlaceholder: 'Search...',
                    sSearch: '',
                    lengthMenu: '_MENU_ Show entries',
                },
                responsive: true,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ]
            });
        });
    </script>
    <script>
        $(document).on('click', '.store-btn', function() {
            const hashedId = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "This submission will be imported to Structures!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/store-to-structure/' + hashedId,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Stored!',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            // reload DataTable setelah sukses
                            $('#submissions-table').DataTable().ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            const msg = xhr.responseJSON?.message || 'Something went wrong';
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: msg
                            });
                        }
                    });
                }
            });
        });
    </script>

    <script>
        $(function() {
            $(document).on('click', '.preview-btn', function() {
                // Text-only fields
                $('#preview-company').text($(this).data('company'));
                $('#preview-department').text($(this).data('department'));
                $('#preview-manager').text($(this).data('submitter'));
                $('#preview-store').text($(this).data('store'));
                $('#preview-position').text($(this).data('position'));
                $('#preview-approver1').text($(this).data('approver1'));
                const salaryData = $(this).data('salary'); // ambil data-salary
                if (salaryData) {
                    const [salaryStart, salaryEnd] = salaryData.toString().split('|');
                    $('#preview-salary').text(
                        `${Number(salaryStart).toLocaleString()} - ${Number(salaryEnd).toLocaleString()}`
                    );
                } else {
                    $('#preview-salary').text('-');
                }
                $('#preview-approver2').text($(this).data('approver2'));
                $('#preview-status').text($(this).data('status'));
                $('#preview-role-summary').html(JSON.parse($(this).data('role-summary') || '""'));
                $('#preview-key-responsibility').html(JSON.parse($(this).data('key-responsibility') ||
                    '""'));
                $('#preview-qualifications').html(JSON.parse($(this).data('qualifications') || '""'));
                $('#previewModal').modal('show');
            });
        });
    </script>
    <script>
        $(document).ready(function() {
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
                    // {
                    //     data: 'checkbox',
                    //     name: 'checkbox',
                    //     orderable: false,
                    //     searchable: false,
                    //     className: 'text-center align-middle'
                    // },
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
    data: 'is_manager',
    className: 'text-center',
    render: function(data) {
        if (data == 1) {
            return `<span class="badge bg-success">Yes</span>`;
        }
        return `<span class="badge bg-danger">No</span>`;
    }
},

                    {
                        data: 'parent',
                        name: 'parent',
                        className: 'text-center'
                    },
                    // {
                    //     data: 'children',
                    //     name: 'children',
                    //     className: 'text-center'
                    // },
                    {
                        data: 'allChildren',
                        name: 'allChildren',
                        className: 'text-center'
                    },
                    {
                        data: 'status',
                        className: 'text-center',
                        render: function(data) {
                            const badges = {
                                'active': 'success'
                            };
                            return `<span class="badge bg-${badges[data] || 'light'}">${data}</span>`;
                        }
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }

                ],
                initComplete: function() {}
            });


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
            // fetch("{{ route('orgchart.orgchart') }}")
            //     .then(res => res.json())
            //     .then(data => {
            //         console.log(data);
            //         new OrgChart(document.getElementById("orgchart"), {
            //             nodes: data,
            //             nodeBinding: {
            //                 field_0: "name",
            //                 field_1: "title",
            //                 field_2: "tags"
            //             },
            //             template: "olivia",
            //             collapse: {
            //                 level: 3
            //             },
            //             nodeMouseClick: OrgChart.action.none
            //         });
            //     });

// // ===== ORGCHART SETUP =====
// OrgChart.templates.myTemplate = Object.assign({}, OrgChart.templates.ana);
// OrgChart.templates.myTemplate.size = [250, 150];

// OrgChart.templates.myTemplate.node =
//     `<rect x="0" y="0" width="250" height="150" fill="#ffffff"
//         stroke="#cccccc" stroke-width="5" rx="10" ry="10"></rect>`;

// OrgChart.templates.myTemplate.field_ =
//     `<text style="font-size:14px;font-weight:700;" fill="#212121" x="125" y="40" text-anchor="middle">{val}</text>`;

// OrgChart.templates.myTemplate.fieldgrading =
//     `<text style="font-size:13px;font-weight:600;" fill="#616161" x="125" y="60" text-anchor="middle">{val}</text>`;

// OrgChart.templates.myTemplate.field_0 =
//     `<text style="font-size:12px;font-weight:500;" fill="#424242" x="125" y="80" text-anchor="middle">{val}</text>`;

// OrgChart.templates.myTemplate.field_1 =
//     `<text style="font-size:11px;font-weight:500;" fill="#757575" x="125" y="95" text-anchor="middle">{val}</text>`;

// OrgChart.templates.myTemplate.field_2 =
//     `<g transform="translate(60,105)">
//         <rect width="130" height="25" rx="12" ry="12" fill="{val}"></rect>
//     </g>`;

// OrgChart.templates.myTemplate.field_3 =
//     `<text style="font-size:12px;font-weight:600;" fill="#ffffff" x="125" y="122" text-anchor="middle">{val}</text>`;

// const statusColors = {
//     active: '#4CAF50',
//     inactive: '#F44336',
//     vacant: '#9E9E9E',
// };

// const chart = new OrgChart(document.getElementById("tree"), {
//     template: "myTemplate",
//     enableSearch: true,
//     mouseScrool: OrgChart.action.zoom,
//     scaleInitial: OrgChart.match.boundary,
//     layout: OrgChart.normal,
    
//     levelSeparation: 100, // Dikurangi karena kita manual adjust
//     siblingSeparation: 100,
    
//     nodeBinding: {
//         field_: "Employee",
//         fieldgrading: "Grading",
//         field_0: "Position",
//         field_1: "Location",
//         field_2: "statusColor",
//         field_3: "status"
//     },

//     toolbar: {
//         zoom: true,
//         fit: true,
//         expandAll: true
//     },

//     nodeMenu: null,
//     nodeMouseClick: OrgChart.action.none
// });

// // Hapus prerender event yang tidak efektif
// // chart.on('prerender', function(sender, args) {
// //     adjustNodePositionsByGrading(args.nodes);
// //     return args;
// // });

// // ===== ADJUST NODE POSITIONS SETELAH RENDER (POST-PROCESSING) =====
// function forceAdjustNodesByGrading() {
//     if (!window.orgData) return;
    
//     const treeElement = document.getElementById("tree");
//     if (!treeElement) return;
    
//     const SVG = treeElement.querySelector('svg');
//     if (!SVG) return;
    
//     const gradingGap = 220; // Jarak antar grading level
//     const baseY = 80;
    
//     // Map untuk menyimpan X position yang sudah ada
//     const nodePositions = new Map();
    
//     // Ambil semua node groups dari SVG
//     const nodeGroups = SVG.querySelectorAll('[node-id]');
    
//     nodeGroups.forEach(nodeGroup => {
//         const nodeId = nodeGroup.getAttribute('node-id');
//         const nodeData = window.orgData.find(d => d.id == nodeId);
        
//         if (nodeData && nodeData.level !== undefined) {
//             // Hitung Y position berdasarkan grading level
//             const targetY = baseY + (nodeData.level * gradingGap);
            
//             // Ambil current transform
//             const currentTransform = nodeGroup.getAttribute('transform') || 'translate(0,0)';
//             const matches = currentTransform.match(/translate\(([^,]+),\s*([^)]+)\)/);
            
//             if (matches) {
//                 const currentX = parseFloat(matches[1]);
                
//                 // Update transform dengan Y baru
//                 nodeGroup.setAttribute('transform', `translate(${currentX}, ${targetY})`);
                
//                 // Simpan posisi untuk redraw links
//                 nodePositions.set(nodeId, { x: currentX, y: targetY });
//             }
//         }
//     });
    
//     // Redraw semua connection lines
//     redrawAllLinks(nodePositions);
// }

// // ===== REDRAW SEMUA GARIS KONEKSI =====
// function redrawAllLinks(nodePositions) {
//     if (!window.orgData) return;
    
//     const treeElement = document.getElementById("tree");
//     const SVG = treeElement?.querySelector('svg');
//     if (!SVG) return;
    
//     // Hapus semua path lama (kecuali secondary links)
//     const oldPaths = SVG.querySelectorAll('path:not(.secondary-link)');
//     oldPaths.forEach(path => path.remove());
    
//     // Buat path baru berdasarkan hierarchy
//     window.orgData.forEach(node => {
//         if (!node.pid) return; // Skip root
        
//         const childPos = nodePositions.get(node.id);
//         const parentPos = nodePositions.get(node.pid);
//         const childData = window.orgData.find(d => d.id == node.id);
//         const parentData = window.orgData.find(d => d.id == node.pid);
        
//         if (childPos && parentPos && childData && parentData) {
//             // Hitung koordinat untuk garis vertikal + horizontal
//             const childX = childPos.x + 125; // Tengah node (250/2)
//             const childY = childPos.y;
//             const parentX = parentPos.x + 125;
//             const parentY = parentPos.y + 150; // Bottom of parent node
            
//             // Cek perbedaan level untuk menentukan panjang garis
//             const levelDiff = Math.abs((childData.level || 0) - (parentData.level || 0));
            
//             // Jika perbedaan level > 1, buat garis lebih panjang dengan tambahan segmen
//             let pathData;
//             if (levelDiff > 1) {
//                 // Buat garis dengan segmen tambahan untuk menunjukkan skip level
//                 const quarterY = parentY + (childY - parentY) * 0.25;
//                 const threeQuarterY = parentY + (childY - parentY) * 0.75;
//                 pathData = `M ${parentX} ${parentY} L ${parentX} ${quarterY} L ${childX} ${threeQuarterY} L ${childX} ${childY}`;
//             } else {
//                 // Garis normal
//                 const midY = (parentY + childY) / 2;
//                 pathData = `M ${parentX} ${parentY} L ${parentX} ${midY} L ${childX} ${midY} L ${childX} ${childY}`;
//             }
            
//             const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
//             path.setAttribute("d", pathData);
//             path.setAttribute("stroke", "#cccccc");
//             path.setAttribute("stroke-width", "2");
//             path.setAttribute("fill", "none");
//             path.setAttribute("class", "orgchart-link");
            
//             // Insert sebelum nodes agar tidak menutupi
//             SVG.insertBefore(path, SVG.firstChild);
//         }
//     });
// }

// // ===== STYLING UNTUK INLINE SIDEBAR =====
// (function addInlineStyles() {
//     if (document.getElementById('inlineSidebarStyles')) return;
    
//     const style = document.createElement('style');
//     style.id = 'inlineSidebarStyles';
//     style.textContent = `
//         .grading-sidebar-inline {
//             width: 220px;
//             height: 700px;
//             background: #fafafa;
//             overflow-y: auto;
//             display: flex;
//             flex-direction: column;
//         }
        
//         .grading-sidebar-inline .sidebar-header {
//             padding: 20px 15px;
//             background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
//             color: white;
//             border-bottom: 3px solid #5568d3;
//             flex-shrink: 0;
//         }
        
//         .grading-sidebar-inline .sidebar-header h6 {
//             margin: 0;
//             font-size: 14px;
//             font-weight: 600;
//         }
        
//         .grading-sidebar-inline .sidebar-header i {
//             font-size: 14px;
//         }
        
//         .grading-sidebar-inline .sidebar-content {
//             padding: 10px 0;
//             flex: 1;
//             overflow-y: auto;
//         }
        
//         .grading-sidebar-inline .grading-item {
//             padding: 12px 15px;
//             cursor: pointer;
//             display: flex;
//             justify-content: space-between;
//             align-items: center;
//             border-left: 4px solid transparent;
//             transition: all 0.3s ease;
//             margin: 2px 0;
//         }
        
//         .grading-sidebar-inline .grading-item:hover {
//             background: #e3f2fd;
//             border-left-color: #667eea;
//         }
        
//         .grading-sidebar-inline .grading-item.active {
//             background: #e8eaf6;
//             border-left-color: #667eea;
//             font-weight: 600;
//         }
        
//         .grading-sidebar-inline .grading-badge {
//             font-size: 13px;
//             font-weight: 500;
//             color: #424242;
//             flex: 1;
//         }
        
//         .grading-sidebar-inline .grading-count {
//             background: #bdbdbd;
//             color: white;
//             padding: 3px 10px;
//             border-radius: 12px;
//             font-size: 11px;
//             font-weight: 600;
//             min-width: 30px;
//             text-align: center;
//         }
        
//         .grading-sidebar-inline .grading-item.active .grading-count {
//             background: #667eea;
//             color: white;
//         }
        
//         .grading-sidebar-inline .all-badge {
//             color: #667eea;
//             font-weight: 700;
//         }
        
//         /* Custom Scrollbar */
//         .grading-sidebar-inline .sidebar-content::-webkit-scrollbar {
//             width: 6px;
//         }
        
//         .grading-sidebar-inline .sidebar-content::-webkit-scrollbar-track {
//             background: #f1f1f1;
//         }
        
//         .grading-sidebar-inline .sidebar-content::-webkit-scrollbar-thumb {
//             background: #667eea;
//             border-radius: 3px;
//         }
        
//         .grading-sidebar-inline .sidebar-content::-webkit-scrollbar-thumb:hover {
//             background: #5568d3;
//         }
        
//         /* Responsive */
//         @media (max-width: 768px) {
//             .grading-sidebar-inline {
//                 width: 70px;
//             }
            
//             .grading-sidebar-inline .grading-badge {
//                 font-size: 10px;
//                 overflow: hidden;
//                 text-overflow: ellipsis;
//                 white-space: nowrap;
//             }
            
//             .grading-sidebar-inline .sidebar-header h6 {
//                 font-size: 11px;
//             }
//         }
//     `;
//     document.head.appendChild(style);
// })();

// // ===== FUNGSI GAMBAR GARIS SECONDARY =====
// function drawSecondaryLinks() {
//     const treeElement = document.getElementById("tree");
//     if (!treeElement) return;

//     const SVG = treeElement.querySelector('svg');
//     if (!SVG) return;

//     const existingLinks = SVG.querySelectorAll('.secondary-link');
//     existingLinks.forEach(link => link.remove());

//     if (!window.orgData) return;

//     window.orgData.forEach(node => {
//         if (!node.secondary || node.secondary.length === 0) return;

//         node.secondary.forEach(secData => {
//             const secId = typeof secData === 'object' ? secData.id : secData;
//             const fromNode = chart.getNode(secId);
//             const toNode = chart.getNode(node.id);

//             if (!fromNode || !toNode) return;

//             const fx = fromNode.x + fromNode.w / 2;
//             const fy = fromNode.y + fromNode.h;
//             const tx = toNode.x + toNode.w / 2;
//             const ty = toNode.y;

//             if (isNaN(fx) || isNaN(fy) || isNaN(tx) || isNaN(ty)) return;

//             const midY = (fy + ty) / 2;
//             const pathData = `M ${fx} ${fy} C ${fx} ${midY}, ${tx} ${midY}, ${tx} ${ty}`;

//             const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
//             path.setAttribute("d", pathData);
//             path.setAttribute("stroke", "#FF5722");
//             path.setAttribute("stroke-width", "5");
//             path.setAttribute("stroke-dasharray", "15,8");
//             path.setAttribute("fill", "none");
//             path.setAttribute("class", "secondary-link");
//             path.setAttribute("stroke-linecap", "round");

//             SVG.appendChild(path);
//         });
//     });
// }

// // ===== POPULATE SIDEBAR =====
// function populateGradingSidebar(data) {
//     const gradingOrder = ['Director', 'Head', 'Senior Manager', 'Manager', 
//                           'Assistant Manager', 'Supervisor', 'Staff', 'Daily Worker'];
    
//     const gradingCounts = {};
//     data.forEach(node => {
//         const grading = node.Grading || 'Empty';
//         gradingCounts[grading] = (gradingCounts[grading] || 0) + 1;
//     });

//     const gradingListContainer = document.getElementById('gradingList');
    
//     if (!gradingListContainer) {
//         console.error('Grading list container not found');
//         return;
//     }

//     const countAllElement = document.getElementById('count-all');
//     if (countAllElement) {
//         countAllElement.textContent = data.length;
//     }

//     // Clear existing items (except "All")
//     const existingItems = gradingListContainer.querySelectorAll('.grading-item:not([data-grading="all"])');
//     existingItems.forEach(item => item.remove());

//     // Add grading items
//     gradingOrder.forEach(grading => {
//         if (gradingCounts[grading]) {
//             const item = document.createElement('div');
//             item.className = 'grading-item';
//             item.dataset.grading = grading;
//             item.innerHTML = `
//                 <span class="grading-badge">${grading}</span>
//                 <span class="grading-count">${gradingCounts[grading]}</span>
//             `;
//             gradingListContainer.appendChild(item);
//         }
//     });

//     // Event Listeners
//     document.querySelectorAll('.grading-item').forEach(item => {
//         item.addEventListener('click', function() {
//             document.querySelectorAll('.grading-item').forEach(i => i.classList.remove('active'));
//             this.classList.add('active');

//             const selectedGrading = this.dataset.grading;
//             filterByGrading(selectedGrading);
//         });
//     });
// }

// function filterByGrading(grading) {
//     if (!window.orgData) return;

//     if (grading === 'all') {
//         chart.load(window.orgData);
//     } else {
//         const filtered = [];
//         const includedIds = new Set();

//         // Collect matching nodes
//         window.orgData.forEach(node => {
//             if (node.Grading === grading) {
//                 filtered.push(node);
//                 includedIds.add(node.id);
//             }
//         });

//         // Add required parents
//         window.orgData.forEach(node => {
//             if (node.Grading === grading) {
//                 let parentId = node.pid;
//                 while (parentId) {
//                     if (!includedIds.has(parentId)) {
//                         const parentNode = window.orgData.find(n => n.id === parentId);
//                         if (parentNode) {
//                             filtered.push(parentNode);
//                             includedIds.add(parentId);
//                             parentId = parentNode.pid;
//                         } else {
//                             break;
//                         }
//                     } else {
//                         break;
//                     }
//                 }
//             }
//         });

//         chart.load(filtered);
//     }

//     setTimeout(drawSecondaryLinks, 500);
// }

// // ===== FETCH DATA =====
// fetch("{{ route('orgchart.orgchart') }}")
//     .then(res => res.json())
//     .then(data => {
//         const processed = data.map(n => ({
//             ...n,
//             statusColor: statusColors[(n.status || '').toLowerCase()] || '#9E9E9E'
//         }));

//         window.orgData = processed;
        
//         // Populate sidebar yang sudah ada di HTML
//         populateGradingSidebar(processed);
        
//         // Load chart
//         chart.load(processed);
        
//         setTimeout(drawSecondaryLinks, 1000);
//     })
//     .catch(err => console.error('Error loading org chart data:', err));

// // ===== EVENT LISTENERS =====
// chart.on("init", function() {
//     setTimeout(drawSecondaryLinks, 500);
// });

// chart.on("redraw", function() {
//     setTimeout(drawSecondaryLinks, 300);
// });

// // Toggle Secondary Links
// let secondaryLinksVisible = true;
// const toggleButton = document.getElementById('toggleSecondaryLinks');
// if (toggleButton) {
//     toggleButton.addEventListener('click', function() {
//         const treeElement = document.getElementById("tree");
//         const SVG = treeElement ? treeElement.querySelector('svg') : null;
//         const toggleText = document.getElementById('toggleText');
//         const icon = this.querySelector('i');

//         if (!SVG) return;

//         const secondaryLinks = SVG.querySelectorAll('.secondary-link');

//         if (secondaryLinksVisible) {
//             secondaryLinks.forEach(link => link.style.display = 'none');
//             if (toggleText) toggleText.textContent = 'Show Secondary Links';
//             if (icon) {
//                 icon.classList.remove('fa-eye-slash');
//                 icon.classList.add('fa-eye');
//             }
//             this.classList.remove('btn-outline-primary');
//             this.classList.add('btn-outline-secondary');
//         } else {
//             secondaryLinks.forEach(link => link.style.display = 'block');
//             if (toggleText) toggleText.textContent = 'Hide Secondary Links';
//             if (icon) {
//                 icon.classList.remove('fa-eye');
//                 icon.classList.add('fa-eye-slash');
//             }
//             this.classList.remove('btn-outline-secondary');
//             this.classList.add('btn-outline-primary');
//         }
//         secondaryLinksVisible = !secondaryLinksVisible;
//     });
// }

// ===== ORGCHART TEMPLATE =====
const base = OrgChart.templates.ana;
OrgChart.templates.myTemplate = {
    ...base,
    size: [250, 150],
    node: `<rect width="250" height="150" fill="#fff" stroke="#ccc" stroke-width="5" rx="10"></rect>`,
    field_: `<text x="125" y="40" text-anchor="middle" style="font-size:14px;font-weight:700">{val}</text>`,
    fieldgrading: `<text x="125" y="60" text-anchor="middle" style="font-size:13px;font-weight:600">{val}</text>`,
    field_0: `<text x="125" y="80" text-anchor="middle" style="font-size:12px;font-weight:500">{val}</text>`,
    field_1: `<text x="125" y="95" text-anchor="middle" style="font-size:11px;font-weight:500">{val}</text>`,
    field_2: `
        <g transform="translate(60,105)">
            <rect width="130" height="25" rx="12" fill="{val}"></rect>
        </g>`,
    field_3: `<text x="125" y="122" text-anchor="middle" style="font-size:12px;font-weight:600" fill="#fff">{val}</text>`
};
// ===== CHART SETUP =====
const statusColors = {
    active: '#4CAF50',
    inactive: '#F44336',
    vacant: '#9E9E9E'
};

const chart = new OrgChart(document.getElementById("tree"), {
    template: "myTemplate",
    enableSearch: true,
    mouseScroll: OrgChart.action.zoom,
    scaleInitial: OrgChart.match.boundary,
    layout: OrgChart.normal,

    levelSeparation: 80,
    siblingSeparation: 100,

    nodeBinding: {
        field_: "Employee",
        fieldgrading: "Grading",
        field_0: "Position",
        field_1: "Location",
        field_2: "statusColor",
        field_3: "status"
    },

    toolbar: { zoom: true, fit: true, expandAll: true },
    nodeMenu: null,
    nodeMouseClick: OrgChart.action.none
});

// ===== FORCE Y-POSITION BY GRADING =====
function forceAdjustNodesByGrading() {
    if (!window.orgData) return;

    const svg = document.querySelector("#tree svg");
    if (!svg) return;

    const GAP = 180;
    const BASE = 60;

    const positions = new Map();
    const nodes = svg.querySelectorAll("[node-id]");

    nodes.forEach(node => {
        const id = node.getAttribute("node-id");
        const data = window.orgData.find(n => n.id == id);
        if (!data) return;

        const levelY = BASE + data.level * GAP;

        const transform = node.getAttribute("transform");
        const [, x] = transform.match(/translate\(([^,]+),/);

        node.setAttribute("transform", `translate(${x}, ${levelY})`);
        positions.set(data.id, { x: +x, y: levelY });
    });

    redrawLinks(positions);
}

// ===== REDRAW PRIMARY LINKS =====
function redrawLinks(pos) {
    const svg = document.querySelector("#tree svg");
    if (!svg) return;

    // Hapus path lama (kecuali secondary)
    svg.querySelectorAll("path.org-link").forEach(p => p.remove());

    const N = window.orgData;

    N.forEach(node => {
        if (!node.pid) return;

        const parent = pos.get(node.pid);
        const child  = pos.get(node.id);
        if (!parent || !child) return;

        const px = parent.x + 125, py = parent.y + 150;
        const cx = child.x + 125,  cy = child.y;

        const midY = (py + cy) / 2;

        const d = `M ${px} ${py} L ${px} ${midY} L ${cx} ${midY} L ${cx} ${cy}`;

        const p = document.createElementNS("http://www.w3.org/2000/svg", "path");
        p.setAttribute("d", d);
        p.setAttribute("stroke", "#ccc");
        p.setAttribute("stroke-width", "2");
        p.setAttribute("fill", "none");
        p.classList.add("org-link");
        svg.insertBefore(p, svg.firstChild);
    });
}
(function addInlineStyles() {
    if (document.getElementById('inlineSidebarStyles')) return;
    
    const style = document.createElement('style');
    style.id = 'inlineSidebarStyles';
    style.textContent = `
        .grading-sidebar-inline {
            width: 220px;
            height: 700px;
            background: #fafafa;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        
        .grading-sidebar-inline .sidebar-header {
            padding: 20px 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom: 3px solid #5568d3;
            flex-shrink: 0;
        }
        
        .grading-sidebar-inline .sidebar-header h6 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
        }
        
        .grading-sidebar-inline .sidebar-header i {
            font-size: 14px;
        }
        
        .grading-sidebar-inline .sidebar-content {
            padding: 10px 0;
            flex: 1;
            overflow-y: auto;
        }
        
        .grading-sidebar-inline .grading-item {
            padding: 12px 15px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
            margin: 2px 0;
        }
        
        .grading-sidebar-inline .grading-item:hover {
            background: #e3f2fd;
            border-left-color: #667eea;
        }
        
        .grading-sidebar-inline .grading-item.active {
            background: #e8eaf6;
            border-left-color: #667eea;
            font-weight: 600;
        }
        
        .grading-sidebar-inline .grading-badge {
            font-size: 13px;
            font-weight: 500;
            color: #424242;
            flex: 1;
        }
        
        .grading-sidebar-inline .grading-count {
            background: #bdbdbd;
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            min-width: 30px;
            text-align: center;
        }
        
        .grading-sidebar-inline .grading-item.active .grading-count {
            background: #667eea;
            color: white;
        }
        
        .grading-sidebar-inline .all-badge {
            color: #667eea;
            font-weight: 700;
        }
        
        /* Custom Scrollbar */
        .grading-sidebar-inline .sidebar-content::-webkit-scrollbar {
            width: 6px;
        }
        
        .grading-sidebar-inline .sidebar-content::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .grading-sidebar-inline .sidebar-content::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 3px;
        }
        
        .grading-sidebar-inline .sidebar-content::-webkit-scrollbar-thumb:hover {
            background: #5568d3;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .grading-sidebar-inline {
                width: 70px;
            }
            
            .grading-sidebar-inline .grading-badge {
                font-size: 10px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            
            .grading-sidebar-inline .sidebar-header h6 {
                font-size: 11px;
            }
        }
    `;
    document.head.appendChild(style);
})();
function drawSecondaryLinks() {
    const svg = document.querySelector("#tree svg");
    if (!svg || !window.orgData) return;

    svg.querySelectorAll(".secondary-link").forEach(p => p.remove());

    window.orgData.forEach(node => {
        (node.secondary || []).forEach(sec => {
            const from = chart.getNode(sec.id || sec);
            const to   = chart.getNode(node.id);
            if (!from || !to) return;

            const fx = from.x + from.w / 2;
            const fy = from.y + from.h;
            const tx = to.x + to.w / 2;
            const ty = to.y;
            const mid = (fy + ty) / 2;

            const d = `M ${fx} ${fy} C ${fx} ${mid}, ${tx} ${mid}, ${tx} ${ty}`;

            const p = document.createElementNS("http://www.w3.org/2000/svg", "path");
            p.setAttribute("d", d);
            p.setAttribute("stroke", "#FF5722");
            p.setAttribute("stroke-width", "5");
            p.setAttribute("stroke-dasharray", "15,8");
            p.setAttribute("fill", "none");
            p.classList.add("secondary-link");
            svg.appendChild(p);
        });
    });
}

chart.on("redraw", () => {
    setTimeout(() => {
        forceAdjustNodesByGrading();
        drawSecondaryLinks();
    }, 200);
});

fetch("{{ route('orgchart.orgchart') }}")
    .then(r => r.json())
    .then(data => {
        window.orgData = data.map(n => ({
            ...n,
            statusColor: statusColors[n.status?.toLowerCase()] || '#9E9E9E'
        }));

        chart.load(window.orgData);

        setTimeout(drawSecondaryLinks, 500);
    });



        });
    </script>
    <script>
        document.getElementById('bulk-delete-form').addEventListener('submit', function(e) {
            const checked = document.querySelectorAll('input.payroll-checkbox:checked');
            if (checked.length === 0) {
                e.preventDefault();
                Swal.fire("Failed", "No data selected.", "error");
                return;
            }

            e.preventDefault();

            Swal.fire({
                title: 'Are you sure you want to delete the selected data?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes!',
                cancelButtonText: 'Abort'
            }).then((result) => {
                if (result.isConfirmed) {
                    const ids = Array.from(checked).map(cb => cb.value);
                    document.getElementById('bulk-delete-hidden').value = ids.join(',');

                    e.target.submit();
                }
            });
        });
        $('#select-all').on('click', function() {
            let isChecked = $(this).data('checked') || false;
            $('input.payroll-checkbox').prop('checked', !isChecked);
            $(this).data('checked', !isChecked);
            $(this).text(!isChecked ? 'Deselect All' : 'Select All');
        });
    </script>
    <script>
        $(document).ready(function() {
            var table = $('#submissions-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('submissionsreq.submissionsreq') }}',
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
                columns: [{
                        data: 'sub',
                        name: 'sub',
                        className: 'text-center'
                    },
                    {
                        data: 'position_name',
                        name: 'position_name',
                        className: 'text-center'
                    },
                    {
                        data: 'status',
                        className: 'text-center',
                        render: function(data) {
                            const badges = {
                                'Accepted': 'success',
                                'On review': 'warning',
                                'Pending': 'secondary',
                                'Draft': 'info',
                                'Reject': 'danger'
                            };
                            return `<span class="badge bg-${badges[data] || 'light'}">${data}</span>`;
                        }
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }

                ],
                initComplete: function() {}
            });
        });
    </script>
@endpush


{{-- // ===== REDRAW SEMUA GARIS KONEKSI =====
// function redrawAllLinks(nodePositions) {
//     if (!window.orgData) return;
    
//     const treeElement = document.getElementById("tree");
//     const SVG = treeElement?.querySelector('svg');
//     if (!SVG) return;
    
//     // Hapus semua path lama (kecuali secondary links)
//     const oldPaths = SVG.querySelectorAll('path:not(.secondary-link)');
//     oldPaths.forEach(path => path.remove());
    
//     // Buat path baru berdasarkan hierarchy
//     window.orgData.forEach(node => {
//         if (!node.pid) return; // Skip root
        
//         const childPos = nodePositions.get(node.id);
//         const parentPos = nodePositions.get(node.pid);
        
//         if (childPos && parentPos) {
//             // Hitung koordinat untuk garis vertikal + horizontal
//             const childX = childPos.x + 125; // Tengah node (250/2)
//             const childY = childPos.y;
//             const parentX = parentPos.x + 125;
//             const parentY = parentPos.y + 150; // Bottom of parent node
            
//             // Buat path dengan style yang sama seperti OrgChart.js
//             const midY = (parentY + childY) / 2;
//             const pathData = `M ${parentX} ${parentY} L ${parentX} ${midY} L ${childX} ${midY} L ${childX} ${childY}`;
            
//             const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
//             path.setAttribute("d", pathData);
//             path.setAttribute("stroke", "#cccccc");
//             path.setAttribute("stroke-width", "2");
//             path.setAttribute("fill", "none");
//             path.setAttribute("class", "orgchart-link");
            
//             // Insert sebelum nodes agar tidak menutupi
//             SVG.insertBefore(path, SVG.firstChild);
//         }
//     });
// } --}}