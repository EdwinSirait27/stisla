@extends('layouts.app')
@section('title', 'Payroll Periods')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        .section-header h1 { font-size: 1.4rem; font-weight: 600; color: #1e293b; margin: 0; }
        .section-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 1.25rem; }
        .page-actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .page-actions .btn { height: 36px; font-size: .825rem; padding: 0 1rem; display: inline-flex; align-items: center; gap: .4rem; border-radius: .5rem; }

        /* Stat cards */
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 1.25rem; }
        .stat-card { background: #fff; border: 1px solid #f1f5f9; border-radius: .625rem; padding: 14px 16px; box-shadow: 0 1px 2px rgba(0,0,0,.04); }
        .stat-card-label { font-size: .68rem; font-weight: 700; letter-spacing: .7px; color: #94a3b8; margin-bottom: 6px; }
        .stat-card-value { font-size: 1.5rem; font-weight: 600; line-height: 1; color: #1e293b; }
        .stat-card-value.green  { color: #16a34a; }
        .stat-card-value.amber  { color: #d97706; }
        .stat-card-value.red    { color: #dc2626; }
        .stat-card-sub { font-size: .7rem; color: #94a3b8; margin-top: 5px; }

        /* Card */
        .emp-card { border: none; border-radius: .625rem; box-shadow: 0 1px 3px rgba(0,0,0,.07); background: #fff; overflow: hidden; margin-bottom: 1.25rem; }
        .emp-card-header { background: #f8fafc; border-bottom: 1px solid #f1f5f9; padding: .875rem 1.25rem; display: flex; align-items: center; gap: .6rem; }
        .emp-card-header-icon { width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: .8rem; flex-shrink: 0; background: #eff6ff; color: #1d4ed8; }
        .emp-card-header-title { font-size: .9rem; font-weight: 600; color: #334155; flex: 1; }
        .emp-card-header-count { font-size: .72rem; color: #64748b; background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; padding: .15rem .7rem; }

        /* Toolbar */
        .dt-toolbar { padding: .75rem 1.25rem; border-bottom: 1px solid #f1f5f9; background: #fafafa; display: flex; align-items: center; gap: .5rem; flex-wrap: nowrap; }
        .dt-toolbar .btn { height: 32px; font-size: .775rem; padding: 0 .75rem; display: inline-flex; align-items: center; gap: .35rem; border-radius: .4rem; }
        .dt-toolbar select, .dt-toolbar input { height: 32px; font-size: .775rem; border: 1px solid #e2e8f0; border-radius: .4rem; padding: 0 .6rem; }

        /* Table */
        #period-table { width: 100% !important; font-size: .8rem; }
        #period-table thead th { background: #f8fafc; color: #64748b; font-size: .68rem; font-weight: 700; letter-spacing: .5px; padding: .7rem .9rem; border: none; border-bottom: 1px solid #f1f5f9; white-space: nowrap; }
        #period-table tbody td { padding: .75rem .9rem; vertical-align: middle; border: none; border-bottom: 1px solid #f8fafc; color: #334155; }
        #period-table tbody tr:last-child td { border-bottom: none; }
        #period-table tbody tr:hover td { background: #f8fafc; }

        /* Status badges */
        .status-badge { display: inline-flex; align-items: center; gap: .3rem; padding: .18rem .6rem; border-radius: 20px; font-size: .7rem; font-weight: 700; white-space: nowrap; }

        /* Action */
        .action-wrap { display: flex; gap: 5px; justify-content: center; }
        .act-btn { width: 28px; height: 28px; border-radius: 6px; border: 1px solid #e2e8f0; background: #fff; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: #64748b; font-size: .75rem; text-decoration: none; transition: all .15s; }
        .act-btn:hover { background: #f8fafc; }
        .act-btn.act-warning { border-color: #fde68a; background: #fffbeb; color: #d97706; }
        .act-btn.act-warning:hover { background: #fef3c7; }
        .act-btn.act-danger { border-color: #fecaca; background: #fef2f2; color: #dc2626; }
        .act-btn.act-danger:hover { background: #fee2e2; }

        /* Create form card */
        .create-card { background: #fff; border-radius: .625rem; box-shadow: 0 1px 3px rgba(0,0,0,.07); overflow: hidden; margin-bottom: 1.25rem; }
        .create-card-header { background: #f8fafc; border-bottom: 1px solid #f1f5f9; padding: .875rem 1.25rem; display: flex; align-items: center; gap: .6rem; }
        .create-card-body { padding: 1.25rem; }
        .create-form-grid { display: grid; grid-template-columns: 180px 120px 1fr auto; gap: .75rem; align-items: end; }
        .field-group { display: flex; flex-direction: column; gap: .35rem; }
        .field-group label { font-size: .78rem; font-weight: 600; color: #475569; }
        .field-group select, .field-group input { height: 38px; font-size: .825rem; border: 1px solid #e2e8f0; border-radius: .5rem; padding: 0 .75rem; }
        .field-group select:focus, .field-group input:focus { border-color: #1d4ed8; outline: none; box-shadow: 0 0 0 3px rgba(29,78,216,.08); }
        .btn-create { height: 38px; font-size: .825rem; padding: 0 1.25rem; display: inline-flex; align-items: center; gap: .4rem; border-radius: .5rem; background: #1d4ed8; border: none; color: #fff; cursor: pointer; white-space: nowrap; }
        .btn-create:hover { background: #1e40af; }

        @media (max-width: 768px) {
            .stats-row { grid-template-columns: repeat(2, 1fr); }
            .create-form-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
@endpush

@section('main')
<div class="main-content">
    <section class="section">

        {{-- Header --}}
        <div class="section-header">
            <div>
                <div style="font-size:.72rem;color:#94a3b8;margin-bottom:3px">
                    Dashboard /
                    <a href="#" style="color:#64748b;text-decoration:none">Payroll</a> /
                    <span style="color:#1e293b">Payroll Periods</span>
                </div>
                <h1>Payroll Periods</h1>
            </div>
        </div>

        {{-- Stat Cards --}}
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-card-label">Open</div>
                <div class="stat-card-value green">{{ $stats['open'] }}</div>
                <div class="stat-card-sub">Periode aktif</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">Closed</div>
                <div class="stat-card-value amber">{{ $stats['closed'] }}</div>
                <div class="stat-card-sub">Menunggu lock</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">Locked</div>
                <div class="stat-card-value red">{{ $stats['locked'] }}</div>
                <div class="stat-card-sub">Periode terkunci</div>
            </div>
        </div>

        {{-- Create Period Form --}}
        <div class="create-card">
            <div class="create-card-header">
                <div class="emp-card-header-icon"><i class="fas fa-plus-circle"></i></div>
                <span style="font-size:.9rem;font-weight:600;color:#334155">Buat Periode Baru</span>
            </div>
            <div class="create-card-body">
                <form action="{{ route('payrollperiod.store') }}" method="POST">
                    @csrf
                    <div class="create-form-grid">
                        <div class="field-group">
                            <label>Bulan</label>
                            <select name="period_month" required>
                                <option value="">Pilih Bulan</option>
                                @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $bulan)
                                    <option value="{{ $i + 1 }}" {{ old('period_month') == $i + 1 ? 'selected' : '' }}>
                                        {{ $bulan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field-group">
                            <label>Tahun</label>
                            <select name="period_year" required>
                                @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                                    <option value="{{ $y }}" {{ (old('period_year', now()->year)) == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="field-group">
                            <label>Note <span style="color:#94a3b8;font-weight:400">(opsional)</span></label>
                            <input type="text" name="note" value="{{ old('note') }}" placeholder="Catatan periode...">
                        </div>
                        <div class="field-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn-create">
                                <i class="fas fa-plus"></i> Buat Periode
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Preview periode yang akan dibuat --}}
                <div id="period-preview" style="margin-top:.75rem;font-size:.78rem;color:#64748b;display:none">
                    <i class="fas fa-info-circle" style="color:#1d4ed8"></i>
                    Periode: <strong id="preview-start"></strong> — <strong id="preview-end"></strong>
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="emp-card">
            <div class="emp-card-header">
                <div class="emp-card-header-icon"><i class="fas fa-calendar-alt"></i></div>
                <span class="emp-card-header-title">Daftar Periode</span>
                <span class="emp-card-header-count" id="record-count">— records</span>
            </div>

            {{-- Toolbar --}}
            <div class="dt-toolbar">
                <select id="filter-year" style="width:100px">
                    <option value="">Semua Tahun</option>
                    @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <select id="filter-status" style="width:130px">
                    <option value="">Semua Status</option>
                    <option value="open">Open</option>
                    <option value="closed">Closed</option>
                    <option value="locked">Locked</option>
                </select>
                <button class="btn btn-light" id="btn-search"><i class="fas fa-search"></i> Cari</button>
                <button class="btn btn-light" id="btn-reset"><i class="fas fa-redo"></i> Reset</button>
            </div>

            {{-- Table --}}
            <div style="padding:.75rem 1.25rem 1rem">
                <table id="period-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Periode</th>
                            <th>Range</th>
                            <th>Status</th>
                            <th>Note</th>
                            <th>Dibuat Oleh</th>
                            <th>Locked Oleh</th>
                            <th style="text-align:center">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </section>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(function () {

            // ── DataTable ──
            const table = $('#period-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('payrollperiod.data') }}',
                    data: function (d) {
                        d.year   = $('#filter-year').val();
                        d.status = $('#filter-status').val();
                    }
                },
                columns: [
                    {
                        data: null,
                        render: (d, t, r, m) => m.row + 1,
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'period_label',
                        render: data => `<strong>${data}</strong>`,
                    },
                    {
                        data: 'period_range',
                        render: data => `<span style="color:#64748b;font-size:.78rem">${data}</span>`,
                    },
                    { data: 'status_badge', orderable: false },
                    {
                        data: 'note',
                        defaultContent: '<span style="color:#cbd5e1">—</span>',
                        render: data => data ?? '<span style="color:#cbd5e1">—</span>',
                    },
                    { data: 'created_by_name', defaultContent: '-' },
                    { data: 'locked_by_name', defaultContent: '-' },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                    },
                ],
                drawCallback: function () {
                    const info = this.api().page.info();
                    $('#record-count').text(info.recordsTotal + ' records');
                },
                language: {
                    processing: 'Memuat data...',
                    emptyTable: 'Belum ada periode.',
                    zeroRecords: 'Data tidak ditemukan.',
                },
                order: [[1, 'desc']],
                pageLength: 25,
            });

            // ── Filter ──
            $('#btn-search').on('click', () => table.ajax.reload());
            $('#btn-reset').on('click', () => {
                $('#filter-year').val('{{ now()->year }}');
                $('#filter-status').val('');
                table.ajax.reload();
            });

            // ── Preview periode saat pilih bulan/tahun ──
            function updatePreview() {
                const month = $('select[name=period_month]').val();
                const year  = $('select[name=period_year]').val();

                if (!month || !year) {
                    $('#period-preview').hide();
                    return;
                }

                // Hitung period_start & period_end
                const m     = parseInt(month);
                const y     = parseInt(year);
                const end   = new Date(y, m - 1, 25);
                const start = new Date(y, m - 2, 26);

                const fmt = d => d.toLocaleDateString('id-ID', {
                    day: '2-digit', month: 'long', year: 'numeric'
                });

                $('#preview-start').text(fmt(start));
                $('#preview-end').text(fmt(end));
                $('#period-preview').show();
            }

            $('select[name=period_month], select[name=period_year]').on('change', updatePreview);
            updatePreview();

            // ── Flash messages ──
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#1d4ed8',
                    timer: 3000,
                    timerProgressBar: true,
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: '{{ session('error') }}',
                    confirmButtonColor: '#dc2626',
                });
            @endif
        });
    </script>
@endpush