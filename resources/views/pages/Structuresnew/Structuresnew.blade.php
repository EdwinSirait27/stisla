@extends('layouts.app')
@section('title', 'Structures')

@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="{{ asset('library/orgchart/jquery.orgchart.min.css') }}"
          onerror="this.onerror=null;this.href='https://unpkg.com/orgchart@2.1.9/dist/css/jquery.orgchart.min.css'">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* ═══════════════════════════════════════
           CARD & TABLE (existing styles)
        ═══════════════════════════════════════ */
        .card {
            border: none;
            box-shadow: 0 0.25rem 0.75rem rgba(0,0,0,.08);
            border-radius: .5rem;
            overflow: hidden;
            transition: all .3s cubic-bezier(.25,.8,.25,1);
            background-color: #fff;
        }
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.12);
        }
        .card-header {
            background-color: #f8fafc;
            border-bottom: 1px solid rgba(0,0,0,.03);
            padding: 1.25rem 1.5rem;
        }
        .card-header h6 {
            margin: 0; font-weight: 600; color: #4a5568;
            display: flex; align-items: center; font-size: .95rem;
        }
        .card-header h6 i { margin-right: .75rem; color: #5e72e4; transition: color .3s ease; }

        .table-responsive { padding: 0 1.5rem; overflow: hidden; }
        .table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .table thead th {
            background-color: #f8fafc; color: #4a5568; font-weight: 600;
            text-transform: uppercase; font-size: .7rem; letter-spacing: .5px;
            border: none; padding: 1rem .75rem; position: sticky; top: 0; z-index: 10;
        }
        .table tbody tr { transition: all .25s ease; position: relative; }
        .table tbody tr:not(:last-child)::after {
            content:''; position:absolute; bottom:0; left:0; right:0;
            height:1px; background:rgba(0,0,0,.05);
        }
        .table tbody tr:hover { background-color: rgba(94,114,228,.03); transform: scale(1.002); }
        .table tbody td {
            padding: 1.1rem .75rem; vertical-align: middle; color: #4a5568;
            font-size: .85rem; border: none; background: #fff;
        }
        .table tbody tr:hover td { color: #2d3748; }
        .btn-primary { background-color: #5e72e4; border-color: #5e72e4; transition: all .3s ease; }
        .btn-primary:hover { background-color: #4a5bd1; border-color: #4a5bd1; transform: translateY(-1px); }
        .section-header h1 { font-weight: 600; color: #2d3748; font-size: 1.5rem; }

        @media (max-width:768px) {
            .table-responsive { padding:0 .75rem; border-radius:.5rem; border:1px solid rgba(0,0,0,.05); }
            .card-header { padding: 1rem; }
            .table thead th { font-size: .65rem; padding: .75rem .5rem; }
            .table tbody td { padding: .85rem .5rem; font-size: .8rem; }
        }

        /* ═══════════════════════════════════════
           GRADING SIDEBAR
        ═══════════════════════════════════════ */
        .grading-sidebar-inline {
            width: 170px;
            height: 700px;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
        }
        .sidebar-header {
            padding: 14px 16px;
            border-bottom: 1px solid #e2e8f0;
            background: #fff;
        }
        .sidebar-header h6 {
            font-size: 12px; font-weight: 700; color: #475569;
            text-transform: uppercase; letter-spacing: .5px; margin: 0;
        }
        .sidebar-content { padding: 8px 10px; overflow-y: auto; flex: 1; }
        .grading-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 7px 10px; border-radius: 8px; cursor: pointer;
            margin-bottom: 4px; transition: background .15s;
        }
        .grading-item:hover    { background: #e2e8f0; }
        .grading-item.active   { background: #dbeafe; }
        .grading-badge {
            font-size: 10px; font-weight: 700; padding: 2px 8px;
            border-radius: 99px; text-transform: uppercase; letter-spacing: .3px;
        }
        .all-badge          { background:#1e293b; color:#fff; }
        .badge-director     { background:#1e3a8a; color:#fff; }
        .badge-head         { background:#1d4ed8; color:#fff; }
        .badge-senior       { background:#0369a1; color:#fff; }
        .badge-manager      { background:#0891b2; color:#fff; }
        .badge-assistant    { background:#0d9488; color:#fff; }
        .badge-supervisor   { background:#059669; color:#fff; }
        .badge-staff        { background:#16a34a; color:#fff; }
        .badge-daily        { background:#65a30d; color:#fff; }
        .badge-empty        { background:#94a3b8; color:#fff; }
        .grading-count {
            font-size: 11px; font-weight: 700; color: #64748b;
            background: #e2e8f0; border-radius: 99px;
            min-width: 22px; text-align: center; padding: 1px 5px;
        }

        /* ═══════════════════════════════════════
           ORG CHART CONTAINER
        ═══════════════════════════════════════ */

        /* Wrapper keseluruhan chart area */
        .orgchart-wrapper {
            display: flex;
            flex-direction: column;
            height: 700px;
        }

        /* Bar atas: search + toolbar */
        .orgchart-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            flex-shrink: 0;
            gap: 10px;
        }

        /* Search di topbar */
        .orgchart-search {
            position: relative;
            display: flex;
            align-items: center;
        }
        .orgchart-search .search-icon {
            position: absolute; left: 10px; color: #94a3b8;
            pointer-events: none; font-size: 13px;
        }
        .orgchart-search input {
            border: 1px solid #cbd5e1; border-radius: 8px;
            padding: 6px 12px 6px 30px; font-size: 13px; outline: none;
            width: 240px; box-shadow: 0 1px 3px rgba(0,0,0,.06);
            transition: border-color .2s;
        }
        .orgchart-search input:focus { border-color: #1d4ed8; }

        /* Toolbar di topbar */
        .orgchart-toolbar {
            display: flex;
            gap: 6px;
        }
        .orgchart-toolbar button {
            background: #fff; border: 1px solid #cbd5e1; border-radius: 8px;
            padding: 6px 12px; font-size: 13px; cursor: pointer;
            color: #374151; transition: all .2s; box-shadow: 0 1px 3px rgba(0,0,0,.06);
            display: flex; align-items: center; gap: 5px;
        }
        .orgchart-toolbar button:hover { background:#1d4ed8; color:#fff; border-color:#1d4ed8; }

        /* Chart scroll area */
        #tree {
            flex: 1;
            overflow: auto;
            background: #f8fafc;
            position: relative;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Loading */
        #orgchart-loading {
            position: absolute; inset: 0; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            background: #f8fafc; z-index: 200; gap: 12px;
        }
        .spinner {
            width: 40px; height: 40px;
            border: 4px solid #e2e8f0; border-top-color: #1d4ed8;
            border-radius: 50%; animation: spin .8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* OrgChart lib overrides */
        .orgchart { background: transparent !important; padding: 30px 30px 30px !important; }
        .orgchart .node { width: 190px !important; transition: all .2s; }
        .orgchart .node:hover { filter: brightness(1.05); }
        .orgchart .lines .downLine  { background-color: #94a3b8 !important; }
        .orgchart .lines .rightLine { border-right: 1px solid #94a3b8 !important; }
        .orgchart .lines .leftLine  { border-left:  1px solid #94a3b8 !important; }
        .orgchart .lines .topLine   { border-top:   1px solid #94a3b8 !important; }

        /* Node card */
        .org-node-card {
            background: #fff; border-radius: 10px; border: 1.5px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,.08); padding: 10px 12px;
            cursor: pointer; transition: box-shadow .2s, border-color .2s;
            min-width: 160px; max-width: 200px; text-align: left; position: relative;
        }
        .org-node-card:hover { box-shadow: 0 6px 20px rgba(29,78,216,.15); border-color: #1d4ed8; }
        .org-node-card.highlighted { border-color:#f59e0b !important; box-shadow: 0 0 0 3px rgba(245,158,11,.3) !important; }
        .org-node-card.dimmed { opacity: .35; }

        /* Grading badge in node */
        .org-grading {
            display: inline-block; font-size: 10px; font-weight: 700;
            padding: 2px 7px; border-radius: 99px; margin-bottom: 6px;
            letter-spacing: .4px; text-transform: uppercase;
        }
        .grading-director   { background:#1e3a8a; color:#fff; }
        .grading-head       { background:#1d4ed8; color:#fff; }
        .grading-senior     { background:#0369a1; color:#fff; }
        .grading-manager    { background:#0891b2; color:#fff; }
        .grading-assistant  { background:#0d9488; color:#fff; }
        .grading-supervisor { background:#059669; color:#fff; }
        .grading-staff      { background:#16a34a; color:#fff; }
        .grading-daily      { background:#65a30d; color:#fff; }
        .grading-empty      { background:#94a3b8; color:#fff; }

        .org-emp-name {
            font-size: 13px; font-weight: 600; color: #1e293b;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 176px;
        }
        .org-position {
            font-size: 11px; color: #64748b;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            max-width: 176px; margin-top: 2px;
        }
        .org-location {
            font-size: 10px; color: #94a3b8; margin-top: 4px;
            display: flex; align-items: center; gap: 3px;
        }
        .org-secondary-badge {
            position: absolute; top: -6px; right: -6px;
            background: #f59e0b; color: #fff; border-radius: 99px;
            font-size: 9px; font-weight: 700; padding: 1px 5px;
            min-width: 16px; text-align: center; line-height: 16px;
            height: 16px; border: 1.5px solid #fff;
        }
        .org-status-dot {
            width: 7px; height: 7px; border-radius: 50%;
            display: inline-block; margin-right: 4px;
        }
        .status-active   { background: #22c55e; }
        .status-inactive { background: #ef4444; }

        /* ═══════════════════════════════════════
           DETAIL MODAL
        ═══════════════════════════════════════ */
        .org-modal-backdrop {
            position: fixed; inset: 0; background: rgba(15,23,42,.45);
            z-index: 9998; display: none; align-items: center; justify-content: center;
        }
        .org-modal-backdrop.show { display: flex; }
        .org-modal {
            background: #fff; border-radius: 14px; padding: 28px 32px;
            width: 420px; max-width: 95vw;
            box-shadow: 0 24px 60px rgba(0,0,0,.2);
            position: relative; animation: modalIn .2s ease;
        }
        @keyframes modalIn {
            from { transform: scale(.92) translateY(12px); opacity:0; }
            to   { transform: scale(1)   translateY(0);    opacity:1; }
        }
        .org-modal-close {
            position: absolute; top: 14px; right: 16px;
            background: none; border: none; font-size: 20px;
            cursor: pointer; color: #94a3b8; transition: color .2s;
        }
        .org-modal-close:hover { color: #1e293b; }
        .org-modal h3 { font-size: 18px; font-weight: 700; color: #0f172a; margin: 0 0 4px; }
        .org-modal .modal-position { color: #64748b; font-size: 13px; margin-bottom: 16px; }
        .org-modal-divider { border: none; border-top: 1px solid #e2e8f0; margin: 12px 0; }
        .org-modal-row { display: flex; gap: 8px; margin-bottom: 8px; align-items: flex-start; }
        .org-modal-label {
            font-size: 12px; color: #94a3b8; font-weight: 600;
            min-width: 110px; text-transform: uppercase; letter-spacing: .4px;
        }
        .org-modal-value { font-size: 13px; color: #1e293b; font-weight: 500; }
        .org-modal-secondary { margin-top: 14px; }
        .org-modal-secondary h4 {
            font-size: 12px; font-weight: 700; color: #94a3b8;
            text-transform: uppercase; letter-spacing: .4px; margin: 0 0 8px;
        }
        .secondary-chip {
            display: inline-flex; align-items: center; gap: 5px;
            background: #f1f5f9; border-radius: 99px;
            padding: 4px 10px; font-size: 12px; color: #334155;
            margin: 3px 3px 0 0; font-weight: 500;
        }
    </style>
@endpush

@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1><i class="fas fa-sitemap"></i> Structures Overview</h1>
        </div>

        <div class="section-body">

            {{-- ══════════════════════════════════════
                 ORG CHART CARD
            ══════════════════════════════════════ --}}
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 text-primary">
                                <i class="fas fa-network-wired me-1"></i> Organization Chart
                            </h6>
                            <button id="toggleSecondaryLinks" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye-slash me-1"></i>
                                <span id="toggleText">Hide Secondary</span>
                            </button>
                        </div>

                        <div class="card-body p-0">
                            <div class="row g-0">

                                {{-- Grading Sidebar --}}
                                <div class="col-auto border-end">
                                    <div class="grading-sidebar-inline">
                                        <div class="sidebar-header">
                                            <h6><i class="fas fa-layer-group me-2"></i>Grading Levels</h6>
                                        </div>
                                        <div class="sidebar-content" id="gradingList">
                                            <div class="grading-item active" data-grading="all">
                                                <span class="grading-badge all-badge">All</span>
                                                <span class="grading-count" id="count-all">0</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Org Chart --}}
                                <div class="col" style="min-width:0;">
                                    <div class="orgchart-wrapper">

                                        {{-- Top bar: search + toolbar --}}
                                        <div class="orgchart-topbar" id="orgchart-topbar" style="display:none;">
                                            <div class="orgchart-search">
                                                <i class="fa fa-search search-icon"></i>
                                                <input type="text" id="orgchart-search-input"
                                                       placeholder="Search employee or position…">
                                            </div>
                                            <div class="orgchart-toolbar">
                                                <button onclick="orgChartZoomIn()"  title="Zoom In"><i class="fa fa-plus"></i></button>
                                                <button onclick="orgChartZoomOut()" title="Zoom Out"><i class="fa fa-minus"></i></button>
                                                <button onclick="orgChartReset()"   title="Reset"><i class="fa fa-expand-arrows-alt"></i></button>
                                                <button onclick="orgChartExport()"  title="Export PNG"><i class="fa fa-download"></i></button>
                                            </div>
                                        </div>

                                        {{-- Chart scroll area --}}
                                        <div id="tree">
                                            <div id="orgchart-loading">
                                                <div class="spinner"></div>
                                                <span style="color:#64748b;font-size:14px;">Loading org chart…</span>
                                            </div>
                                        </div>

                                    </div>{{-- /orgchart-wrapper --}}
                                </div>

                            </div>{{-- /row g-0 --}}
                        </div>{{-- /card-body --}}
                    </div>{{-- /card --}}
                </div>
            </div>

            {{-- ══════════════════════════════════════
                 LIST STRUCTURES TABLE
            ══════════════════════════════════════ --}}
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 text-primary">
                                <i class="fas fa-list-ul me-1"></i> List Structures
                            </h6>
                        </div>
                        <form id="bulk-delete-form" method="POST" action="{{ route('structuresnew.bulkDelete') }}">
                            @csrf
                            @method('DELETE')
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle" id="users-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center">Employee Name</th>
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
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════
                 MANAGER SUBMISSIONS
            ══════════════════════════════════════ --}}
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
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════
                 STRUCTURES HISTORY
            ══════════════════════════════════════ --}}
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

        </div>{{-- /section-body --}}
    </section>
</div>

{{-- ══════════════════════════════════════
     DETAIL MODAL (outside main-content, fixed positioning)
══════════════════════════════════════ --}}
<div class="org-modal-backdrop" id="orgModalBackdrop">
    <div class="org-modal">
        <button class="org-modal-close" onclick="closeOrgModal()"><i class="fa fa-times"></i></button>
        <div id="orgModalContent"></div>
    </div>
</div>

{{-- ══════════════════════════════════════
     SUBMISSION PREVIEW MODAL
══════════════════════════════════════ --}}
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Preview Submission Position</h5>
            </div>
            <div class="modal-body">
                <table class="table table-striped align-middle">
                    <tr><th style="width:25%;">Company</th>         <td id="preview-company"></td></tr>
                    <tr><th>Department</th>                          <td id="preview-department"></td></tr>
                    <tr><th>Manager Name</th>                        <td id="preview-manager"></td></tr>
                    <tr><th>Location Request</th>                    <td id="preview-store"></td></tr>
                    <tr><th>Position Request</th>                    <td id="preview-position"></td></tr>
                    <tr><th>Role Summary</th>                        <td id="preview-role-summary"></td></tr>
                    <tr><th>Key Responsibility</th>                  <td id="preview-key-responsibility"></td></tr>
                    <tr><th>Qualifications</th>                      <td id="preview-qualifications"></td></tr>
                    <tr><th>HRD Approver</th>                        <td id="preview-approver1"></td></tr>
                    <tr><th>DIR Approver</th>                        <td id="preview-approver2"></td></tr>
                    <tr><th>Salary</th>                              <td id="preview-salary"></td></tr>
                    <tr><th>Status</th>                              <td id="preview-status"></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- ══════════════════════════════════════
     ALL SCRIPTS — satu @push saja
     JANGAN tambah <script src="jquery"> lagi!
     jQuery sudah di-load oleh layouts.app
══════════════════════════════════════ --}}
@push('scripts')

{{-- DataTable & Sweetalert (tidak konflik, load biasa) --}}
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
/* ════════════════════════════════════════════════════════════
   IIFE — selalu pakai jQuery dari project, bukan jQuery lain
   OrgChart.js di-load DINAMIS agar pasti attach ke $ project
════════════════════════════════════════════════════════════ */
(function ($) {
    'use strict';

    /* ── Load OrgChart.js secara dinamis, baru init chart ── */
    function loadScript(src, callback) {
        if (document.querySelector('script[src="' + src + '"]')) {
            callback(); return;
        }
        const s = document.createElement('script');
        s.src = src;
        s.onload  = callback;
        s.onerror = function() { console.error('Gagal load: ' + src); };
        document.head.appendChild(s);
    }

    /* ── Coba beberapa CDN secara berurutan ── */
    function loadScriptWithFallback(urls, callback) {
        if (!urls.length) {
            $('#orgchart-loading').html(`
                <i class="fa fa-circle-exclamation" style="font-size:32px;color:#ef4444;"></i>
                <span style="color:#ef4444;font-size:14px;">Gagal load library OrgChart.js</span>
                <small style="color:#94a3b8;">Periksa koneksi internet Anda</small>`);
            return;
        }
        const src = urls[0];
        const rest = urls.slice(1);
        if (document.querySelector('script[src="' + src + '"]')) {
            callback(); return;
        }
        const s = document.createElement('script');
        s.src = src;
        s.onload  = callback;
        s.onerror = function () {
            console.warn('Gagal load dari: ' + src + ', mencoba fallback…');
            loadScriptWithFallback(rest, callback);
        };
        document.head.appendChild(s);
    }

    const ORGCHART_CDNS = [
        // ① Prioritas utama: file lokal
        '/library/orgchart/jquery.orgchart.min.js',
        // ② Fallback CDN pakai versi 2.1.9 (versi yang ada di npm)
        'https://unpkg.com/orgchart@2.1.9/dist/js/jquery.orgchart.min.js',
        'https://cdn.jsdelivr.net/npm/orgchart@2.1.9/dist/js/jquery.orgchart.min.js',
    ];

    /* ── Grading config ── */
    const GRADING_CONFIG = [
        { key:'Director',         cls:'grading-director',   badgeCls:'badge-director'   },
        { key:'Head',             cls:'grading-head',       badgeCls:'badge-head'        },
        { key:'Senior Manager',   cls:'grading-senior',     badgeCls:'badge-senior'      },
        { key:'Manager',          cls:'grading-manager',    badgeCls:'badge-manager'     },
        { key:'Assistant Manager',cls:'grading-assistant',  badgeCls:'badge-assistant'   },
        { key:'Supervisor',       cls:'grading-supervisor', badgeCls:'badge-supervisor'  },
        { key:'Staff',            cls:'grading-staff',      badgeCls:'badge-staff'       },
        { key:'Daily Worker',     cls:'grading-daily',      badgeCls:'badge-daily'       },
        { key:'Empty',            cls:'grading-empty',      badgeCls:'badge-empty'       },
    ];
    const gradingMap = {};
    GRADING_CONFIG.forEach(g => { gradingMap[g.key] = g; });

    function getGradingCls(g) { return (gradingMap[g] || gradingMap['Empty']).cls; }

    /* ── HTML escape ── */
    function esc(str) {
        return String(str || '')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ── Build tree from flat array ── */
    function buildTree(flat) {
        const map = {}, roots = [];
        flat.forEach(n => { map[n.id] = { ...n, relationship:'', children:[] }; });
        flat.forEach(n => {
            if (n.pid && map[n.pid]) map[n.pid].children.push(map[n.id]);
            else roots.push(map[n.id]);
        });
        if (roots.length === 1) return roots[0];
        return { id:'__root__', Employee:'Organisation', Position:'', Grading:'', Location:'', status:'active', secondary:[], children:roots };
    }

    function setRelationships(node) {
        node.relationship = (node.pid?'1':'0') + '1' + (node.children&&node.children.length?'1':'0');
        if (node.children) node.children.forEach(setRelationships);
    }

    /* ── Node template ── */
    function nodeTemplate(node) {
        if (node.id === '__root__') {
            return `<div class="org-node-card" style="background:#1e3a8a;border-color:#1e3a8a;text-align:center;">
                        <div class="org-emp-name" style="color:#fff;font-size:15px;">${esc(node.Employee)}</div>
                    </div>`;
        }
        const gc  = getGradingCls(node.Grading);
        const loc = node.Location && node.Location !== 'Empty'
            ? `<div class="org-location"><i class="fa fa-location-dot"></i> ${esc(node.Location)}</div>` : '';
        const statusCls = node.status === 'active' ? 'status-active' : 'status-inactive';
        const secBadge  = node.secondary && node.secondary.length
            ? `<span class="org-secondary-badge">${node.secondary.length}</span>` : '';
        return `<div class="org-node-card" data-id="${node.id}">
            ${secBadge}
            <span class="org-grading ${gc}">${esc(node.Grading)}</span>
            <div class="org-emp-name"><span class="org-status-dot ${statusCls}"></span>${esc(node.Employee)}</div>
            <div class="org-position">${esc(node.Position)}</div>
            ${loc}
        </div>`;
    }

    /* ── Detail Modal ── */
    window.closeOrgModal = function () {
        document.getElementById('orgModalBackdrop').classList.remove('show');
    };
    document.getElementById('orgModalBackdrop').addEventListener('click', function (e) {
        if (e.target === this) closeOrgModal();
    });

    function showNodeDetail(node) {
        if (node.id === '__root__') return;
        const gc = getGradingCls(node.Grading);
        const statusLabel = node.status === 'active'
            ? `<span style="color:#22c55e;font-weight:600;">● Active</span>`
            : `<span style="color:#ef4444;font-weight:600;">● Inactive</span>`;
        let secHtml = '';
        if (node.secondary && node.secondary.length) {
            const chips = node.secondary.map(s =>
                `<span class="secondary-chip">
                    <i class="fa fa-user" style="color:#94a3b8"></i>
                    ${esc(s.employee_name)} <span style="color:#94a3b8">— ${esc(s.position)}</span>
                 </span>`).join('');
            secHtml = `<div class="org-modal-secondary">
                <h4><i class="fa fa-users"></i> Secondary Supervisors (${node.secondary.length})</h4>
                ${chips}</div>`;
        }
        document.getElementById('orgModalContent').innerHTML = `
            <span class="org-grading ${gc}" style="font-size:11px;">${esc(node.Grading)}</span>
            <h3>${esc(node.Employee)}</h3>
            <div class="modal-position">Position : ${esc(node.Position)}</div>
            <hr class="org-modal-divider">
            <div class="org-modal-row">
                <span class="org-modal-label">Company</span>
                <span class="org-modal-value">${node.Company}</span>
            </div>
            <div class="org-modal-row">
                <span class="org-modal-label">Status</span>
                <span class="org-modal-value">${statusLabel}</span>
            </div>
            <div class="org-modal-row">
                <span class="org-modal-label">Location</span>
                <span class="org-modal-value"><i class="fa fa-location-dot" style="color:#94a3b8"></i> ${esc(node.Location)}</span>
            </div>
            ${secHtml}`;
        document.getElementById('orgModalBackdrop').classList.add('show');
    }

    /* ── Zoom ── */
    let currentScale = 0.85;
    window.orgChartZoomIn  = () => { currentScale = Math.min(currentScale + 0.1, 2);   applyZoom(); };
    window.orgChartZoomOut = () => { currentScale = Math.max(currentScale - 0.1, 0.3); applyZoom(); };
    window.orgChartReset   = () => { currentScale = 0.85; applyZoom(); };
    function applyZoom() {
        $('#tree .orgchart').css({ transform:`scale(${currentScale})`, transformOrigin:'top center' });
    }

    /* ── Export ── */
    window.orgChartExport = function () {
        const el = document.querySelector('#tree .orgchart');
        if (!el || typeof html2canvas === 'undefined') { alert('Export not available'); return; }
        html2canvas(el, { backgroundColor:'#f0f4f8', scale:1.5 }).then(canvas => {
            const a = document.createElement('a');
            a.download = 'org-chart.png';
            a.href = canvas.toDataURL('image/png');
            a.click();
        });
    };

    /* ── Search ── */
    document.getElementById('orgchart-search-input').addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        document.querySelectorAll('#tree .org-node-card').forEach(card => {
            if (!q) { card.classList.remove('highlighted'); return; }
            card.classList.toggle('highlighted', card.textContent.toLowerCase().includes(q));
        });
    });

    /* ── Grading sidebar builder ── */
    function buildGradingSidebar(flatData) {
        const counts = {};
        flatData.forEach(n => {
            const g = n.Grading || 'Empty';
            counts[g] = (counts[g] || 0) + 1;
        });

        const $list = $('#gradingList');
        $('#count-all').text(flatData.length);

        GRADING_CONFIG.forEach(g => {
            if (!counts[g.key]) return;
            $list.append(`
                <div class="grading-item" data-grading="${g.key}">
                    <span class="grading-badge ${g.badgeCls}">${g.key}</span>
                    <span class="grading-count">${counts[g.key]}</span>
                </div>`);
        });

        // Filter click
        $(document).on('click', '.grading-item', function () {
            $('.grading-item').removeClass('active');
            $(this).addClass('active');
            const selected = $(this).data('grading');
            document.querySelectorAll('#tree .org-node-card').forEach(card => {
                const grading = card.querySelector('.org-grading')?.textContent?.trim() || '';
                if (selected === 'all' || grading.toLowerCase() === selected.toLowerCase()) {
                    card.classList.remove('dimmed');
                } else {
                    card.classList.add('dimmed');
                }
            });
        });
    }

    /* ── Toggle secondary badge visibility ── */
    let secondaryVisible = true;
    $('#toggleSecondaryLinks').on('click', function () {
        secondaryVisible = !secondaryVisible;
        $('.org-secondary-badge').toggle(secondaryVisible);
        $('#toggleText').text(secondaryVisible ? 'Hide Secondary' : 'Show Secondary');
        $(this).find('i').toggleClass('fa-eye-slash fa-eye');
    });

    /* ══════════════════════════════════════
       INIT — wait for DOM ready
    ══════════════════════════════════════ */
    $(function () {

        /* ── Load OrgChart.js dulu, baru fetch data ── */
        loadScriptWithFallback(ORGCHART_CDNS, function () {

        /* ── Org Chart ── */
        $.ajax({
            url: '{{ route("orgchart.orgchart") }}',
            method: 'GET',
            success: function (rawData) {
                const tree = buildTree(rawData);
                setRelationships(tree);

                $('#tree').orgchart({
                    data: tree,
                    nodeContent: 'Position',
                    createNode: function ($node, data) {
                        $node.find('.title, .content').remove();
                        $node.prepend(nodeTemplate(data));
                        $node.find('.org-node-card').on('click', function () { showNodeDetail(data); });
                    },
                    pan: true,
                    zoom: false,
                    toggleSiblingsResp: false,
                    direction: 't2b',
                    exportButton: false,
                    chartClass: 'orgchart-custom',
                });

                applyZoom();
                buildGradingSidebar(rawData);
                $('#orgchart-loading').fadeOut(300);
                $('#orgchart-topbar').show();
            },
            error: function (xhr) {
                $('#orgchart-loading').html(`
                    <i class="fa fa-circle-exclamation" style="font-size:32px;color:#ef4444;"></i>
                    <span style="color:#ef4444;font-size:14px;">Failed to load org chart data</span>
                    <small style="color:#94a3b8;">${xhr.status} ${xhr.statusText}</small>`);
            }
        });

        }); // end loadScript callback

        /* ── DataTable: List Structures ── */
        $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: { url: '{{ route("structuresnew.structuresnew") }}', type: 'GET' },
            responsive: true,
            autoWidth: false,
            lengthMenu: [[10,25,50,100,-1],[10,25,50,100,"All"]],
            language: { search:"_INPUT_", searchPlaceholder:"Search..." },
            columns: [
                { data:'employee_name', name:'employee_name', className:'text-center' },
                { data:'company_name',  name:'company_name',  className:'text-center' },
                { data:'department_name',name:'department_name',className:'text-center' },
                { data:'store_name',    name:'store_name',    className:'text-center' },
                { data:'position_name', name:'position_name', className:'text-center' },
                { data:'structure_code',name:'structure_code',className:'text-center' },
                { data:'is_manager', className:'text-center', render: d =>
                    d==1 ? `<span class="badge bg-success">Yes</span>`
                         : `<span class="badge bg-danger">No</span>` },
                { data:'parent',     name:'parent',     className:'text-center' },
                { data:'allChildren',name:'allChildren',className:'text-center' },
                { data:'status', className:'text-center', render: d =>
                    `<span class="badge bg-${d==='active'?'success':'secondary'}">${d}</span>` },
                { data:'action', orderable:false, searchable:false, className:'text-center' }
            ]
        });

        /* ── DataTable: Submissions ── */
        $('#submissions-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: { url: '{{ route("submissionsreq.submissionsreq") }}', type: 'GET' },
            responsive: true,
            autoWidth: false,
            lengthMenu: [[10,25,50,100,-1],[10,25,50,100,"All"]],
            language: { search:"_INPUT_", searchPlaceholder:"Search..." },
            columns: [
                { data:'sub',           name:'sub',           className:'text-center' },
                { data:'position_name', name:'position_name', className:'text-center' },
                { data:'status', className:'text-center', render: d => {
                    const b = {Accepted:'success','On review':'warning',Pending:'secondary',Draft:'info',Reject:'danger'};
                    return `<span class="badge bg-${b[d]||'light'}">${d}</span>`;
                }},
                { data:'action', orderable:false, searchable:false, className:'text-center' }
            ]
        });

        /* ── DataTable: Activity Log ── */
        $('#activityTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("datastructures.datastructures") }}',
            columns: [
                { data:'DT_RowIndex', name:'DT_RowIndex', orderable:false, searchable:false, className:'text-center' },
                { data:'description', name:'description', className:'text-center' },
                { data:'causer',      name:'causer',      className:'text-center' },
                { data:'created_at',  name:'created_at',  className:'text-center' }
            ],
            order: [[3,'desc']],
            language: { searchPlaceholder:'Search...', sSearch:'', lengthMenu:'_MENU_ Show entries' },
            responsive: true,
            lengthMenu: [[10,25,50,100,-1],[10,25,50,100,"All"]]
        });

        /* ── Store to Structure ── */
        $(document).on('click', '.store-btn', function () {
            const hashedId = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: 'This submission will be imported to Structures!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes!'
            }).then(result => {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: '/store-to-structure/' + hashedId,
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        Swal.fire({ icon:'success', title:'Stored!', text:res.message, timer:1500, showConfirmButton:false });
                        $('#submissions-table').DataTable().ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        Swal.fire({ icon:'error', title:'Error!', text: xhr.responseJSON?.message || 'Something went wrong' });
                    }
                });
            });
        });

        /* ── Preview Modal ── */
        $(document).on('click', '.preview-btn', function () {
            $('#preview-company').text($(this).data('company'));
            $('#preview-department').text($(this).data('department'));
            $('#preview-manager').text($(this).data('submitter'));
            $('#preview-store').text($(this).data('store'));
            $('#preview-position').text($(this).data('position'));
            $('#preview-approver1').text($(this).data('approver1'));
            $('#preview-approver2').text($(this).data('approver2'));
            $('#preview-status').text($(this).data('status'));
            const salaryData = $(this).data('salary');
            if (salaryData) {
                const [s, e] = salaryData.toString().split('|');
                $('#preview-salary').text(`${Number(s).toLocaleString()} - ${Number(e).toLocaleString()}`);
            } else {
                $('#preview-salary').text('-');
            }
            $('#preview-role-summary').html(JSON.parse($(this).data('role-summary') || '""'));
            $('#preview-key-responsibility').html(JSON.parse($(this).data('key-responsibility') || '""'));
            $('#preview-qualifications').html(JSON.parse($(this).data('qualifications') || '""'));
            $('#previewModal').modal('show');
        });

        /* ── Bulk Delete ── */
        document.getElementById('bulk-delete-form').addEventListener('submit', function (e) {
            e.preventDefault();
            const checked = document.querySelectorAll('input.payroll-checkbox:checked');
            if (!checked.length) { Swal.fire("Failed","No data selected.","error"); return; }
            Swal.fire({
                title: 'Delete selected data?', icon: 'warning',
                showCancelButton: true, confirmButtonText: 'Yes!', cancelButtonText: 'Abort'
            }).then(result => {
                if (!result.isConfirmed) return;
                const ids = Array.from(checked).map(cb => cb.value);
                document.getElementById('bulk-delete-hidden').value = ids.join(',');
                e.target.submit();
            });
        });

        $('#select-all').on('click', function () {
            const isChecked = $(this).data('checked') || false;
            $('input.payroll-checkbox').prop('checked', !isChecked);
            $(this).data('checked', !isChecked).text(!isChecked ? 'Deselect All' : 'Select All');
        });

        /* ── Session flash ── */
        @if (session('success'))
        Swal.fire({ icon:'success', title:'Success', text:'{{ session("success") }}', timer:2000, showConfirmButton:false });
        @endif

    }); // end document.ready

}(jQuery)); // ← pass project's jQuery explicitly
</script>
@endpush