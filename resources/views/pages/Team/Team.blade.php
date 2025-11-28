@extends('layouts.app')
@section('title', 'Team')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
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

    .secondary-link {
        stroke-dasharray: 4;
        stroke-width: 2px;
        stroke: #ff9800 !important;
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
                <h1>Teams Table</h1>
            </div>
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-user-shield"></i> List Team</h6>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="users-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Name</th>
                                                <th class="text-center">NIP</th>
                                                <th class="text-center">Grading</th>
                                                <th class="text-center">Departments</th>
                                                <th class="text-center">Location</th>
                                                <th class="text-center">Position</th>
                                                <th class="text-center">Status Employee</th>
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
            </div>



            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 text-primary">
                                <i class="fas fa-network-wired me-1"></i> Organization Chart
                            </h6>
                            {{-- TAMBAHKAN TOMBOL TOGGLE --}}
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
    <script src="https://balkan.app/js/OrgChart.js"></script>

    <script>
        OrgChart.templates.myTemplate = Object.assign({}, OrgChart.templates.ana);
        OrgChart.templates.myTemplate.size = [250, 150];

        OrgChart.templates.myTemplate.node =
            `<rect x="0" y="0" width="250" height="150" fill="#ffffff"
        stroke="#cccccc" stroke-width="5" rx="10" ry="10"></rect>`;

        OrgChart.templates.myTemplate.field_ =
            `<text style="font-size:14px;font-weight:700;" fill="#212121" x="125" y="40" text-anchor="middle">{val}</text>`;

        OrgChart.templates.myTemplate.fieldgrading =
            `<text style="font-size:13px;font-weight:600;" fill="#616161" x="125" y="60" text-anchor="middle">{val}</text>`;

        OrgChart.templates.myTemplate.field_0 =
            `<text style="font-size:12px;font-weight:500;" fill="#424242" x="125" y="80" text-anchor="middle">{val}</text>`;

        OrgChart.templates.myTemplate.field_1 =
            `<text style="font-size:11px;font-weight:500;" fill="#757575" x="125" y="95" text-anchor="middle">{val}</text>`;

        OrgChart.templates.myTemplate.field_2 =
            `<g transform="translate(60,105)">
        <rect width="130" height="25" rx="12" ry="12" fill="{val}"></rect>
    </g>`;

        OrgChart.templates.myTemplate.field_3 =
            `<text style="font-size:12px;font-weight:600;" fill="#ffffff" x="125" y="122" text-anchor="middle">{val}</text>`;


        const statusColors = {
            active: '#4CAF50',
            inactive: '#F44336',
            vacant: '#9E9E9E',
        };


        // === INIT CHART ===
        const chart = new OrgChart(document.getElementById("tree"), {
            template: "myTemplate",
            enableSearch: true,
            mouseScrool: OrgChart.action.zoom,
            scaleInitial: OrgChart.match.boundary,

            nodeBinding: {
                field_: "Employee",
                fieldgrading: "Grading",
                field_0: "Position",
                field_1: "Location",
                field_2: "statusColor",
                field_3: "status"
            },

            toolbar: {
                zoom: true,
                fit: true,
                expandAll: true
            },

            // ⬇️ TAMBAHKAN INI: Nonaktifkan panel detail
            nodeMenu: null, // Hilangkan menu node
            nodeMouseClick: OrgChart.action.none // Tidak ada aksi saat klik node
        });


        // === FUNGSI GAMBAR GARIS SECONDARY ===
        function drawSecondaryLinks() {
            // console.log('🔵 === drawSecondaryLinks DIPANGGIL ===');

            // Akses SVG langsung dari DOM
            const treeElement = document.getElementById("tree");
            if (!treeElement) {
                // console.log('❌ Element tree tidak ditemukan');
                return;
            }

            const SVG = treeElement.querySelector('svg');
            if (!SVG) {
                // console.log('❌ SVG belum siap');
                return;
            }
            // console.log('✅ SVG siap');

            // Hapus garis lama
            const existingLinks = SVG.querySelectorAll('.secondary-link');
            // console.log('🗑️ Menghapus', existingLinks.length, 'garis lama');
            existingLinks.forEach(link => link.remove());

            if (!window.orgData) {
                // console.log('❌ window.orgData tidak ada');
                return;
            }
            // console.log('✅ window.orgData ada, jumlah nodes:', window.orgData.length);

            let totalLinksCreated = 0;

            window.orgData.forEach(node => {
                if (!node.secondary || node.secondary.length === 0) return;

                // console.log('👤 Node dengan secondary:', {
                //     employee: node.Employee,
                //     nodeId: node.id,
                //     secondaryData: node.secondary
                // });

                node.secondary.forEach(secData => {
                    // ⬇️ PERUBAHAN: Ambil ID dari objek, bukan langsung value
                    const secId = typeof secData === 'object' ? secData.id : secData;

                    // console.log('   🔍 Mencari nodes - FROM ID:', secId, 'TO ID:', node.id);

                    const fromNode = chart.getNode(secId);
                    const toNode = chart.getNode(node.id);

                    // console.log('   📍 fromNode:', fromNode ? 'FOUND ✅' : 'NOT FOUND ❌');
                    // console.log('   📍 toNode:', toNode ? 'FOUND ✅' : 'NOT FOUND ❌');

                    if (!fromNode || !toNode) {
                        // console.log('   ⚠️ SKIP: Node tidak lengkap');
                        return;
                    }

                    // Koordinat
                    const fx = fromNode.x + fromNode.w / 2;
                    const fy = fromNode.y + fromNode.h;
                    const tx = toNode.x + toNode.w / 2;
                    const ty = toNode.y;

                    // console.log('   📐 Koordinat FROM: (', fx, ',', fy, ')');
                    // console.log('   📐 Koordinat TO: (', tx, ',', ty, ')');

                    // Buat PATH dengan curve
                    const midY = (fy + ty) / 2;
                    const pathData =
                        `M ${fx} ${fy} C ${fx} ${midY}, ${tx} ${midY}, ${tx} ${ty}`;

                    const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
                    path.setAttribute("d", pathData);
                    path.setAttribute("stroke", "#FF5722");
                    path.setAttribute("stroke-width", "5");
                    path.setAttribute("stroke-dasharray", "15,8");
                    path.setAttribute("fill", "none");
                    path.setAttribute("class", "secondary-link");
                    path.setAttribute("stroke-linecap", "round");

                    SVG.appendChild(path);

                    totalLinksCreated++;
                    // console.log('   ✅ GARIS BERHASIL DIBUAT!');
                });
            });

            // console.log('🎯 === TOTAL GARIS DIBUAT:', totalLinksCreated, '===');
        }


        // === FETCH DATA ===
        fetch("{{ route('orgchartteam.orgchartteam') }}")
            .then(res => res.json())
            .then(data => {
                // console.log('Raw data:', data);

                const processed = data.map(n => ({
                    ...n,
                    statusColor: statusColors[(n.status || '').toLowerCase()] || '#9E9E9E'
                }));

                // console.log('Nodes with secondary:', processed.filter(n => n.secondary && n.secondary.length > 0));

                window.orgData = processed;
                chart.load(processed);

                // Panggil manual dengan delay lebih lama
                setTimeout(() => {
                    // console.log('⏰ Timeout: Panggil drawSecondaryLinks manual');
                    drawSecondaryLinks();
                }, 2000); // ⬅ 2 detik
            });


        // === EVENT LISTENERS ===
        chart.on("init", function() {
            // console.log('🎬 EVENT: init');
            setTimeout(drawSecondaryLinks, 500);
        });
        chart.on("redraw", function() {
            setTimeout(drawSecondaryLinks, 300);
        });
        let secondaryLinksVisible = true;
        document.getElementById('toggleSecondaryLinks').addEventListener('click', function() {
            const treeElement = document.getElementById("tree");
            const SVG = treeElement.querySelector('svg');
            const toggleText = document.getElementById('toggleText');
            const icon = this.querySelector('i');

            if (!SVG) return;

            const secondaryLinks = SVG.querySelectorAll('.secondary-link');

            if (secondaryLinksVisible) {
                secondaryLinks.forEach(link => {
                    link.style.display = 'none';
                });
                toggleText.textContent = 'Show Secondary Links';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-outline-secondary');
            } else {
                secondaryLinks.forEach(link => {
                    link.style.display = 'block';
                });
                toggleText.textContent = 'Hide Secondary Links';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                this.classList.remove('btn-outline-secondary');
                this.classList.add('btn-outline-primary');
            }
            secondaryLinksVisible = !secondaryLinksVisible;
        });
    </script>
    <script>
        $(document).ready(function() {
            var table = $('#users-table').DataTable({
                dom: '<"top row mb-2"<"col-sm-12 col-md-6 d-flex align-items-center"lB><"col-sm-12 col-md-6"f>>rt<"bottom"ip>',
                buttons: [{
                        extend: 'csv',
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        className: 'btn btn-sm btn-primary ms-2 me-2',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7]
                        }
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-sm btn-success',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7]
                        }
                    }
                ],
                processing: true,
                serverSide: true,
                autoWidth: false,
                ajax: {
                    url: '{{ route('teams.teams') }}',
                    data: function(d) {
                        d.activity_type = $('#activity-type-filter').val();
                    },
                    error: function(xhr) {
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
                        data: 'employee_name',
                        className: 'text-center'
                    },
                    {
                        data: 'employee_pengenal',
                        className: 'text-center'
                    },
                    {
                        data: 'grading_name',
                        className: 'text-center'
                    },
                    {
                        data: 'department_name',
                        className: 'text-center'
                    },
                    {
                        data: 'name',
                        className: 'text-center'
                    },
                    {
                        data: 'oldposition_name',
                        className: 'text-center'
                    },
                    {
                        data: 'status_employee',
                        className: 'text-center'
                    },
                    {
                        data: 'status',
                        className: 'text-center',
                        render: function(data) {
                            const badges = {
                                'Active': 'success',
                                'Inactive': 'danger',
                                'On leave': 'warning',
                                'Mutation': 'info',
                                'Pending': 'secondary',
                                'Resign': 'warning text-dark'
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
                initComplete: function() {
                    $('.dataTables_filter input').addClass('form-control form-control-sm');
                    $('.dataTables_length select').addClass('form-select form-select-sm');
                }
            });

            $('#activity-type-filter').change(function() {
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
    </script>
@endpush
