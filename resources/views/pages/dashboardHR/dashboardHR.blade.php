@extends('layouts.app')
@section('title', 'HR Dashboard')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        /* =====================================================
                               ROOT & TOKENS
                            ===================================================== */
        :root {
            --brand-900: #0f172a;
            --brand-800: #1e293b;
            --brand-700: #25316D;
            --brand-600: #3E497A;
            --brand-400: #6c7fc4;
            --brand-100: #e8ecf8;
            --brand-50: #f1f4fb;

            --emerald-600: #059669;
            --emerald-500: #10b981;
            --amber-500: #f59e0b;
            --amber-600: #d97706;
            --rose-500: #f43f5e;
            --rose-600: #e11d48;
            --sky-500: #0ea5e9;
            --violet-500: #8b5cf6;

            --primary-gradient: linear-gradient(135deg, #25316D 0%, #3E497A 100%);
            --success-gradient: linear-gradient(135deg, #059669 0%, #047857 100%);
            --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --danger-gradient: linear-gradient(135deg, #f43f5e 0%, #be123c 100%);
            --info-gradient: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            --purple-gradient: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);

            --surface: #ffffff;
            /* --surface-2:   #f8fafc; */
            --border: #e2e8f0;
            --text-primary: #0f172a;
            --text-muted: #64748b;

            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;

            --shadow-sm: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, .08);
            --shadow-lg: 0 12px 32px rgba(0, 0, 0, .12);
            --shadow-xl: 0 24px 48px rgba(0, 0, 0, .16);

            --transition: all .25s cubic-bezier(.4, 0, .2, 1);
        }

        /* =====================================================
                               BASE
                            ===================================================== */
        body,
        .main-content {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--surface-2);
        }

        img.no-drag {
            -webkit-user-drag: none;
            user-select: none;
        }

        /* =====================================================
                               PROFILE HEADER
                            ===================================================== */
        .profile-header-card {
            background: var(--primary-gradient);
            border-radius: var(--radius-xl);
            padding: 36px 40px;
            color: white;
            margin-bottom: 28px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .profile-header-card::before {
            content: '';
            position: absolute;
            top: -60%;
            right: -8%;
            width: 380px;
            height: 380px;
            background: rgba(255, 255, 255, .08);
            border-radius: 50%;
            pointer-events: none;
        }

        .profile-header-card::after {
            content: '';
            position: absolute;
            bottom: -40%;
            left: -4%;
            width: 280px;
            height: 280px;
            background: rgba(255, 255, 255, .05);
            border-radius: 50%;
            pointer-events: none;
        }

        .profile-content {
            position: relative;
            z-index: 1;
        }

        .profile-avatar-large {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, .35);
            object-fit: cover;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .25);
        }

        .profile-info h2 {
            font-size: 1.75rem;
            font-weight: 800;
            margin-bottom: 6px;
            letter-spacing: -.5px;
        }

        .profile-meta {
            display: flex;
            gap: 20px;
            margin-top: 14px;
            flex-wrap: wrap;
        }

        .profile-meta-item {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: .875rem;
            opacity: .9;
            background: rgba(255, 255, 255, .12);
            padding: 5px 12px;
            border-radius: 20px;
        }

        /* =====================================================
                               QUICK ACTIONS
                            ===================================================== */
        .quick-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 28px;
        }

        .qa-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: var(--radius-md);
            border: 1.5px solid var(--border);
            background: var(--surface);
            color: var(--text-primary);
            font-weight: 600;
            font-size: .82rem;
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }

        .qa-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            color: var(--brand-700);
            border-color: var(--brand-400);
            text-decoration: none;
        }

        .qa-btn i {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            color: white;
        }

        .qa-btn .icon-primary {
            background: var(--primary-gradient);
        }

        .qa-btn .icon-success {
            background: var(--success-gradient);
        }

        .qa-btn .icon-warning {
            background: var(--warning-gradient);
        }

        .qa-btn .icon-danger {
            background: var(--danger-gradient);
        }

        .qa-btn .icon-info {
            background: var(--info-gradient);
        }

        .qa-btn .icon-purple {
            background: var(--purple-gradient);
        }

        /* =====================================================
                               STAT CARDS
                            ===================================================== */
        .stat-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            transition: var(--transition);
            height: 100%;
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .stat-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            color: white;
        }

        .stat-icon.primary {
            background: var(--primary-gradient);
        }

        .stat-icon.success {
            background: var(--success-gradient);
        }

        .stat-icon.warning {
            background: var(--warning-gradient);
        }

        .stat-icon.info {
            background: var(--info-gradient);
        }

        .stat-icon.danger {
            background: var(--danger-gradient);
        }

        .stat-icon.purple {
            background: var(--purple-gradient);
        }

        .stat-badge {
            font-size: .72rem;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 20px;
            letter-spacing: .3px;
        }

        .stat-badge.up {
            background: rgba(16, 185, 129, .12);
            color: var(--emerald-600);
        }

        .stat-badge.down {
            background: rgba(244, 63, 94, .12);
            color: var(--rose-600);
        }

        .stat-badge.neutral {
            background: rgba(100, 116, 139, .1);
            color: var(--text-muted);
        }

        .stat-content h3 {
            font-size: 2.1rem;
            font-weight: 800;
            margin: 0 0 2px 0;
            color: var(--text-primary);
            letter-spacing: -1px;
        }

        .stat-content p {
            margin: 0;
            color: var(--text-muted);
            font-size: .82rem;
            font-weight: 600;
            /* text-transform: uppercase; */
            letter-spacing: .4px;
        }

        .stat-footer {
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* =====================================================
                               ALERT BANNER (contract expiry, birthdays)
                            ===================================================== */
        .alert-banner {
            border-radius: var(--radius-md);
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 10px;
            font-size: .875rem;
            font-weight: 500;
            border: none;
        }

        .alert-banner.danger {
            background: rgba(244, 63, 94, .08);
            color: var(--rose-600);
            border-left: 4px solid var(--rose-500);
        }

        .alert-banner.warning {
            background: rgba(245, 158, 11, .08);
            color: var(--amber-600);
            border-left: 4px solid var(--amber-500);
        }

        .alert-banner.info {
            background: rgba(14, 165, 233, .08);
            color: var(--sky-500);
            border-left: 4px solid var(--sky-500);
        }

        .alert-banner.success {
            background: rgba(16, 185, 129, .08);
            color: var(--emerald-600);
            border-left: 4px solid var(--emerald-500);
        }

        .alert-banner .alert-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .alert-banner.danger .alert-icon {
            background: rgba(244, 63, 94, .15);
        }

        .alert-banner.warning .alert-icon {
            background: rgba(245, 158, 11, .15);
        }

        .alert-banner.info .alert-icon {
            background: rgba(14, 165, 233, .15);
        }

        .alert-banner.success .alert-icon {
            background: rgba(16, 185, 129, .15);
        }

        /* =====================================================
                               CARD GENERIC
                            ===================================================== */
        .dash-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .dash-card .dash-card-header {
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--surface);
        }

        .dash-card .dash-card-title {
            font-size: .95rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
        }

        .dash-card .dash-card-title .title-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            color: white;
        }

        .dash-card .dash-card-body {
            padding: 20px 22px;
        }

        #losChart {
            width: 100% !important;
            height: 100% !important;
        }

        /* =====================================================
                               SUBMISSION LIST
                            ===================================================== */


        .type-badge {
            font-size: .72rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            white-space: nowrap;
        }

        .type-badge.leave {
            background: rgba(139, 92, 246, .12);
            color: var(--violet-500);
        }

        .type-badge.overtime {
            background: rgba(245, 158, 11, .12);
            color: var(--amber-600);
        }

        .type-badge.sick {
            background: rgba(244, 63, 94, .12);
            color: var(--rose-600);
        }

        .type-badge.other {
            background: rgba(14, 165, 233, .12);
            color: var(--sky-500);
        }

        /* =====================================================
                               BIRTHDAY LIST
                            ===================================================== */
        .birthday-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 0;
            border-bottom: 1px solid var(--border);
        }

        .birthday-item:last-child {
            border-bottom: none;
        }

        .birthday-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border);
        }

        .birthday-info {
            flex: 1;
        }

        .birthday-name {
            font-size: .85rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .birthday-date {
            font-size: .77rem;
            color: var(--text-muted);
        }

        .birthday-today {
            font-size: .72rem;
            font-weight: 700;
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
        }

        /* =====================================================
                               CONTRACT EXPIRY
                            ===================================================== */
        .contract-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 0;
            border-bottom: 1px solid var(--border);
        }

        .contract-item:last-child {
            border-bottom: none;
        }

        .contract-days {
            width: 46px;
            height: 46px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            font-weight: 800;
            flex-shrink: 0;
        }

        .contract-days.urgent {
            background: rgba(244, 63, 94, .12);
            color: var(--rose-600);
        }

        .contract-days.warning {
            background: rgba(245, 158, 11, .12);
            color: var(--amber-600);
        }

        .contract-days.ok {
            background: rgba(16, 185, 129, .12);
            color: var(--emerald-600);
        }

        /* =====================================================
                               PAYROLL CARD
                            ===================================================== */
        .payroll-status-card {
            background: var(--primary-gradient);
            border-radius: var(--radius-lg);
            padding: 24px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .payroll-status-card::after {
            content: '';
            position: absolute;
            top: -40%;
            right: -15%;
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, .08);
            border-radius: 50%;
        }

        .payroll-status-card .payroll-label {
            font-size: .78rem;
            font-weight: 700;
            /* text-transform: uppercase; */
            letter-spacing: .5px;
            opacity: .8;
            margin-bottom: 6px;
        }

        .payroll-status-card .payroll-amount {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -1px;
        }

        .payroll-progress {
            margin-top: 16px;
            background: rgba(255, 255, 255, .2);
            border-radius: 8px;
            height: 8px;
            overflow: hidden;
        }

        .payroll-progress-bar {
            height: 100%;
            background: rgba(255, 255, 255, .8);
            border-radius: 8px;
            transition: width .8s ease;
        }

        .payroll-meta {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            font-size: .78rem;
            opacity: .85;
        }

        /* =====================================================
                               HR CALENDAR
                            ===================================================== */
        .calendar-event {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
        }

        .calendar-event:last-child {
            border-bottom: none;
        }

        .cal-date {
            width: 44px;
            height: 44px;
            border-radius: var(--radius-sm);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .cal-date .cal-day {
            font-size: 1.1rem;
            font-weight: 800;
            line-height: 1;
        }

        .cal-date .cal-mon {
            font-size: .65rem;
            font-weight: 700;
            /* text-transform: uppercase; */
            letter-spacing: .5px;
        }

        .cal-date.primary {
            background: var(--brand-50);
            color: var(--brand-700);
        }

        .cal-date.success {
            background: rgba(16, 185, 129, .1);
            color: var(--emerald-600);
        }

        .cal-date.warning {
            background: rgba(245, 158, 11, .1);
            color: var(--amber-600);
        }

        .cal-info {
            flex: 1;
        }

        .cal-title {
            font-size: .855rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .cal-sub {
            font-size: .77rem;
            color: var(--text-muted);
        }

        /* =====================================================
                               LEAVE SUMMARY BOX (inside submissions)
                            ===================================================== */
        .leave-summary-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: var(--radius-md);
            padding: 18px;
            color: white;
            margin-bottom: 18px;
        }

        .leave-summary-box h6 {
            color: white;
            font-weight: 700;
            margin-bottom: 14px;
            font-size: .875rem;
        }

        .leave-stat {
            text-align: center;
        }

        .leave-stat small {
            display: block;
            opacity: .85;
            font-size: .72rem;
            margin-bottom: 3px;
        }

        .leave-stat .leave-num {
            font-size: 1.6rem;
            font-weight: 800;
        }

        /* =====================================================
                               DATE FILTER
                            ===================================================== */
        .date-input {
            width: 140px !important;
            padding: 7px 10px;
            font-size: .85rem;
            border-radius: var(--radius-sm);
            border: 1.5px solid var(--border);
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: var(--transition);
        }

        .date-input:focus {
            outline: none;
            border-color: var(--brand-400);
            box-shadow: 0 0 0 3px rgba(108, 127, 196, .15);
        }

        /* =====================================================
                               CHART INFO BOX
                            ===================================================== */
        .chart-info-box {
            background: var(--brand-50);
            border-left: 4px solid var(--brand-400);
            border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
            padding: 12px 16px;
            margin-top: 14px;
            font-size: .82rem;
            color: var(--brand-700);
        }

        /* =====================================================
                               ANNOUNCEMENTS TABLE
                            ===================================================== */
        #users-table thead th {
            background: var(--surface-2);
            font-size: .78rem;
            font-weight: 700;
            /* text-transform: uppercase; */
            letter-spacing: .5px;
            color: var(--text-muted);
            border-bottom: 2px solid var(--border);
        }

        #users-table tbody tr:hover {
            background: var(--brand-50);
        }

        /* =====================================================
                               BUTTONS
                            ===================================================== */
        .btn {
            border-radius: var(--radius-sm);
            font-weight: 700;
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: var(--transition);
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(37, 49, 109, .35);
            color: white;
        }

        .btn-sm {
            padding: 6px 16px;
            font-size: .82rem;
        }

        .btn-outline-primary {
            border: 1.5px solid var(--brand-700);
            color: var(--brand-700);
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: var(--brand-700);
            color: white;
        }

        /* =====================================================
                               MODAL
                            ===================================================== */
        .modal-content {
            border-radius: var(--radius-xl);
            border: none;
            box-shadow: var(--shadow-xl);
        }

        .modal-header {
            background: var(--primary-gradient);
            color: white;
            border-radius: var(--radius-xl) var(--radius-xl) 0 0;
            padding: 22px 26px;
        }

        .modal-header .modal-title {
            font-weight: 700;
            color: white;
        }

        .modal-header .close {
            color: white;
            opacity: .9;
            font-size: 1.25rem;
        }

        .modal-body {
            padding: 26px;
        }

        .modal-footer {
            padding: 16px 26px;
            border-top: 1px solid var(--border);
        }

        .form-label {
            font-weight: 700;
            color: var(--brand-800);
            font-size: .85rem;
            margin-bottom: 7px;
        }

        .form-control {
            border-radius: var(--radius-sm);
            border: 1.5px solid var(--border);
            padding: 10px 14px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: .875rem;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--brand-400);
            box-shadow: 0 0 0 3px rgba(108, 127, 196, .15);
            outline: none;
        }

        /* =====================================================
                               INFO BOX (Annual Leave in modal)
                            ===================================================== */
        .info-box {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: var(--radius-md);
            padding: 18px;
            color: white;
        }

        .info-box p {
            margin-bottom: 7px;
            font-size: .875rem;
        }

        .info-box strong {
            font-weight: 700;
        }

        /* =====================================================
                               EMPLOYEE CHECKBOX
                            ===================================================== */
        .employee-checkbox-container {
            max-height: 200px;
            overflow-y: auto;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 10px;
            background: var(--surface-2);
        }

        .form-check {
            padding: 8px 12px;
            border-radius: 6px;
            transition: var(--transition);
        }

        .form-check:hover {
            background: white;
        }

        .form-check-input:checked {
            background-color: var(--brand-700);
            border-color: var(--brand-700);
        }

        /* =====================================================
                               SECTION LABEL
                            ===================================================== */
        .section-label {
            font-size: .72rem;
            font-weight: 800;
            /* text-transform: uppercase; */
            letter-spacing: .8px;
            color: var(--text-muted);
            margin-bottom: 14px;
        }

        /* =====================================================
                               RESPONSIVE
                            ===================================================== */
        @media (max-width: 768px) {
            .profile-header-card {
                padding: 24px 20px;
            }

            .profile-info h2 {
                font-size: 1.35rem;
            }

            .profile-meta {
                gap: 10px;
            }

            .quick-actions {
                gap: 8px;
            }

            .qa-btn {
                font-size: .78rem;
                padding: 8px 14px;
            }

            .stat-content h3 {
                font-size: 1.6rem;
            }

            .date-input {
                width: 100% !important;
            }
        }

        /* =====================================================
                               ANIMATE
                            ===================================================== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: fadeInUp .45s ease forwards;
        }

        .animate-in:nth-child(1) {
            animation-delay: .05s;
        }

        .animate-in:nth-child(2) {
            animation-delay: .10s;
        }

        .animate-in:nth-child(3) {
            animation-delay: .15s;
        }

        .animate-in:nth-child(4) {
            animation-delay: .20s;
        }

        /* =====================================================
                               DONUT LEGEND
                            ===================================================== */
        .donut-legend {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .donut-legend li {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .82rem;
            color: var(--text-muted);
            padding: 5px 0;
            font-weight: 500;
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .legend-val {
            margin-left: auto;
            font-weight: 700;
            color: var(--text-primary);
        }

        /* =====================================================
                               LATE/ABSENT TRACKER
                            ===================================================== */
        .tracker-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
        }

        .tracker-row:last-child {
            border-bottom: none;
        }

        .tracker-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border);
        }

        .tracker-info {
            flex: 1;
        }

        .tracker-name {
            font-size: .85rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .tracker-time {
            font-size: .75rem;
            color: var(--text-muted);
        }

        .tracker-tag {
            font-size: .72rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
        }

        .tracker-tag.late {
            background: rgba(245, 158, 11, .12);
            color: var(--amber-600);
        }

        .tracker-tag.absent {
            background: rgba(244, 63, 94, .12);
            color: var(--rose-600);
        }

        .tracker-tag.permit {
            background: rgba(14, 165, 233, .12);
            color: var(--sky-500);
        }

        /* Scrollable inner list */
        .inner-scroll {
            max-height: 290px;
            overflow-y: auto;
        }

        .inner-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .inner-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .inner-scroll::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 4px;
        }
    </style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">

            {{-- =========================================================
         PROFILE HEADER
    ========================================================= --}}
            <div class="profile-header-card animate-in">
                <div class="profile-content">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="d-flex align-items-center gap-4">
                                <img src="{{ Auth::user()->employee->photos
                                    ? route('useremployee.photo', basename(Auth::user()->employee->photos))
                                    : asset('img/avatar/avatar-1.png') }}"
                                    alt="Profile" class="profile-avatar-large no-drag">
                                <div class="profile-info">
                                    <h2>{{ Auth::user()->employee->employee_name ?? 'HR User' }}</h2>
                                    <div style="font-size:.85rem;opacity:.85;font-weight:500;">
                                        {{-- Human Resources Department --}}
                                    </div>
                                    <div class="profile-meta">
                                        <div class="profile-meta-item">
                                            <i class="fas fa-briefcase"></i>
                                            <span>{{ Auth::user()->employee->position->name ?? '-' }}</span>
                                        </div>
                                        <div class="profile-meta-item">
                                            <i class="fas fa-building"></i>
                                            <span>{{ Auth::user()->employee->department->department_name ?? '-' }}</span>
                                        </div>
                                        <div class="profile-meta-item">
                                            <i class="fas fa-id-badge"></i>
                                            <span>{{ Auth::user()->employee->employee_pengenal ?? '-' }}</span>
                                        </div>
                                        <div class="profile-meta-item">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span>{{ now()->format('l, F d, Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                            <div
                                style="background:rgba(255,255,255,.12);border-radius:12px;padding:16px 20px;display:inline-block;text-align:center;">
                                <div
                                    style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;opacity:.8;margin-bottom:6px;">
                                    Today's Attendance Rate
                                </div>
                                <div style="font-size:2.2rem;font-weight:800;letter-spacing:-1px;">
                                    {{ $attendanceRateToday ?? 0 }}%
                                </div>
                                <div style="font-size:.78rem;opacity:.8;margin-top:4px;">
                                    {{ $presentToday ?? 0 }} / {{ $totalEmployees ?? 0 }} Present
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- =========================================================
         QUICK ACTIONS BAR
    ========================================================= --}}
            <div class="quick-actions animate-in">
                <a href="{{ route('Employee.create') }}" class="qa-btn">
                    <i class="fas fa-user-plus icon-primary"></i> Add Employee
                </a>
                <a href="{{ route('pages.Fingerprints') }}" class="qa-btn">
                    <i class="fas fa-fingerprint icon-success"></i> Attendance Log
                </a>

                <a href="#" class="qa-btn" id="btn-announcement-quick">
                    <i class="fas fa-bullhorn icon-info"></i> Announcement
                </a>
                <a href="#" class="qa-btn">
                    <i class="fas fa-money-bill-wave icon-purple"></i> Payroll
                </a>
                <a href="#" class="qa-btn">
                    <i class="fas fa-file-contract icon-danger"></i> Contracts
                </a>
            </div>

            <div class="section-body">

                {{-- =====================================================
             ALERT BANNERS (Contract Expiry +)
        ===================================================== --}}
                @if (isset($contractsExpiringCount) && $contractsExpiringCount > 0)
                    <div class="alert-banner danger animate-in" role="alert">
                        <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <div>
                            <strong>{{ $contractsExpiringCount }} employee contract(s)</strong> expiring within 30 days.
                            <a href="#" class="ml-2"
                                style="font-weight:700;color:inherit;text-decoration:underline;">Review now →</a>
                        </div>
                    </div>
                @endif
                {{-- =====================================================
             TOP STAT CARDS
        ===================================================== --}}
                <div class="row mb-2">
                    {{-- Total Employees --}}
                    <div class="col-lg-3 col-md-6 col-12 mb-4 animate-in">
                        <div onclick="window.location='{{ route('pages.Employee') }}';" class="stat-card" role="button"
                            aria-label="View all employees">
                            <div class="stat-card-header">
                                <div class="stat-icon primary"><i class="fas fa-users"></i></div>
                                <span class="stat-badge up"><i class="fas fa-arrow-up mr-1"></i> Active</span>
                            </div>
                            <div class="stat-content">
                                <h3>{{ $totalEmployees ?? 0 }}</h3>
                                <p>Total Employees</p>
                            </div>
                            <div class="stat-footer">
                                <span class="stat-badge up"><i class="fas fa-hourglass-half mr-1"></i>
                                    {{ $totalEmployeespending ?? 0 }} Pending</span>
                                <span class="stat-badge down"><i class="fas fa-door-open mr-1"></i>
                                    {{ $totalEmployeesinactive ?? 0 }} Resigned this week</span>
                            </div>
                        </div>
                    </div>

                    {{-- Present Today --}}
                    <div class="col-lg-3 col-md-6 col-12 mb-4 animate-in">
                        <div onclick="window.location='{{ route('pages.Fingerprints') }}';" class="stat-card"
                            role="button" aria-label="View attendance">
                            <div class="stat-card-header">
                                <div class="stat-icon success"><i class="fas fa-user-check"></i></div>
                                @if (($trend ?? 0) >= 0)
                                    <span class="stat-badge up"><i class="fas fa-arrow-up mr-1"></i>
                                        +{{ $trend }}</span>
                                @else
                                    <span class="stat-badge down"><i class="fas fa-arrow-down mr-1"></i>
                                        {{ $trend }}</span>
                                @endif
                            </div>
                            <div class="stat-content">
                                <h3>{{ $presentToday ?? 0 }}</h3>
                                <p>Present Today</p>
                            </div>
                            <div class="stat-footer">
                                <span class="stat-badge neutral">Yesterday: {{ $presentYesterday ?? 0 }}</span>
                                <span class="stat-badge down"><i class="fas fa-user-times mr-1"></i>
                                    {{ $absentToday ?? 0 }} Absent</span>
                            </div>
                        </div>
                    </div>

                    {{-- Late Today --}}
                    <div class="col-lg-3 col-md-6 col-12 mb-4 animate-in">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div class="stat-icon warning"><i class="fas fa-clock"></i></div>
                                @if (($lateToday ?? 0) > 0)
                                    <span class="stat-badge down"><i class="fas fa-exclamation-circle mr-1"></i>
                                        Alert</span>
                                @else
                                    <span class="stat-badge up"><i class="fas fa-check-circle mr-1"></i> On Time</span>
                                @endif
                            </div>
                            <div class="stat-content">
                                <h3>{{ $lateToday ?? 0 }}</h3>
                                <p>Late Today</p>
                            </div>
                            <div class="stat-footer">
                                <span class="stat-badge neutral">Pending Approvals: {{ $pendingApprovals ?? 0 }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- On Leave --}}
                    <div class="col-lg-3 col-md-6 col-12 mb-4 animate-in">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <div class="stat-icon info"><i class="fas fa-calendar-check"></i></div>
                                <span class="stat-badge neutral">This Week</span>
                            </div>
                            <div class="stat-content">
                                <h3>{{ $onLeave ?? 0 }}</h3>
                                <p>On Leave</p>
                            </div>
                            <div class="stat-footer">
                                <span class="stat-badge warning"><i class="fas fa-file-medical mr-1"></i>
                                    {{ $onSickLeave ?? 0 }} Sick</span>
                                <span class="stat-badge up"><i class="fas fa-umbrella-beach mr-1"></i>
                                    {{ $onAnnualLeave ?? 0 }} Annual</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- =====================================================
             ROW 2 — Attendance Chart + Submissions
        ===================================================== --}}
                <div class="row mb-4">
                    {{-- Attendance Chart --}}
                    <div class="col-lg-8 col-12 mb-4">
                        <div class="dash-card">
                            <div class="dash-card-header">
                                <h4 class="dash-card-title">
                                    <span class="title-icon" style="background:var(--primary-gradient)"><i
                                            class="fas fa-chart-bar"></i></span>
                                    Monthly Attendance Rate
                                </h4>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="date" id="startDate" class="date-input" aria-label="Start Date">
                                    {{-- value="{{ now()->startOfMonth()->format('Y-m-d') }}"> --}}
                                    <input type="date" id="endDate" class="date-input" aria-label="End Date">
                                    {{-- value="{{ now()->endOfMonth()->format('Y-m-d') }}"> --}}
                                    <button id="filterButton" class="btn btn-primary btn-sm">
                                        <i class="fas fa-filter mr-1"></i> Filter
                                    </button>
                                </div>
                            </div>
                            <div class="dash-card-body">
                                <canvas id="attendanceChart" height="190"></canvas>
                                <div class="chart-info-box">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    X-axis: Date &nbsp;|&nbsp; Y-axis: Attendance percentage of total active employees
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Position request --}}
                    <div class="col-lg-4 col-12 mb-4">
                        <div class="dash-card h-100">
                            <div class="dash-card-header">
                                <h4 class="dash-card-title">
                                    <span class="title-icon" style="background:var(--primary-gradient)">
                                        <i class="fas fa-user-plus"></i>
                                    </span>
                                    Position Requests
                                </h4>
                                <div class="d-flex align-items-center gap-2">
                                    @if (isset($positionRequestCount) && $positionRequestCount > 0)
                                        <span class="stat-badge">{{ $positionRequestCount }} total Request</span>
                                    @endif
                                    <a href="{{ route('pages.Positionreqlist') }}" class="btn btn-sm btn-warning">
                                        Approve Now
                                    </a>
                                </div>
                            </div>

                            <div class="dash-card-body">
                                <div class="inner-scroll">
                                    @forelse($positionRequests as $req)
                                        <div class="contract-item">
                                            <div style="flex:1;">
                                                <div style="font-size:.85rem;font-weight:700;">
                                                    {{ $req->title ?? 'Position Request' }}
                                                </div>

                                                <div style="font-size:.75rem;color:var(--text-muted);">
                                                    {{ $req->submitter->employee_name ?? '-' }} ·
                                                    {{ \Carbon\Carbon::parse($req->created_at)->format('d M Y') }}
                                                </div>
                                            </div>

                                            <span class="badge bg-warning">
                                                {{ $req->status_label }}
                                            </span>
                                        </div>
                                    @empty
                                        <div class="text-center py-5">
                                            <i class="fas fa-user-plus fa-3x text-muted mb-3 d-block"></i>
                                            <p class="text-muted mb-0">No position requests</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- =====================================================
             ROW 3 — Dept Chart + Late Tracker + Birthdays
        ===================================================== --}}
                <div class="row mb-4">
                    {{-- Department Distribution --}}
                    <div class="col-lg-4 col-md-6 col-12 mb-4">
                        <div class="dash-card h-100">
                            <div class="dash-card-header">
                                <h4 class="dash-card-title">
                                    <span class="title-icon" style="background:var(--info-gradient)"><i
                                            class="fas fa-sitemap"></i></span>
                                    Employee by Department
                                </h4>
                            </div>
                            <div class="dash-card-body">
                                <!-- Wrap canvas dengan div yang punya height fix -->
                                <div style="position: relative; height: 350px;">
                                    <canvas id="deptChart"></canvas>
                                </div>

                                <ul class="donut-legend mt-3" id="deptLegend">
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Late / Absent Today --}}
                    <div class="col-lg-4 col-md-6 col-12 mb-4">
                        <div class="dash-card h-100">
                            <div class="dash-card-header">
                                <h4 class="dash-card-title">
                                    <span class="title-icon" style="background:var(--info-gradient)"><i
                                            class="fas fa-sitemap"></i></span>
                                    Employee by Company
                                </h4>
                            </div>
                            <div class="dash-card-body">
                                <!-- Wrap canvas dengan div yang punya height fix -->
                                <div style="position: relative; height: 350px;">
                                    <canvas id="companyChart"></canvas>
                                </div>

                                <ul class="horizontal-bar-legend mt-3" id="depthorizontal-barLegend">
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Upcoming Birthdays --}}
                    <div class="col-lg-4 col-md-6 col-12 mb-4">
                        <div class="dash-card h-100">
                            <div class="dash-card-header">
                                <h4 class="dash-card-title">
                                    <span class="title-icon" style="background:var(--success-gradient)">
                                        <i class="fas fa-chart-bar"></i>
                                    </span>
                                    Employee by Length of Service
                                </h4>
                            </div>
                            {{-- <div class="dash-card-body">
            <canvas id="losChart" height="200"></canvas>
        </div> --}}
                            {{-- <div class="dash-card-body d-flex align-items-center justify-content-center"> --}}
                            <div class="dash-card-body">

                                <canvas id="losChart"height="400"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-4">
                    {{-- Payroll Status --}}
                    <div class="col-lg-4 col-md-6 col-12 mb-4">
                        <div class="dash-card h-100">
                            <div class="dash-card-header">
                                <h4 class="dash-card-title">
                                    <span class="title-icon" style="background:var(--success-gradient)"><i
                                            class="fas fa-money-bill-wave"></i></span>
                                    Payroll Status
                                </h4>
                                <span class="stat-badge neutral">{{ now()->format('M Y') }}</span>
                            </div>
                            <div class="dash-card-body">
                                <div class="payroll-status-card mb-3">
                                    <div class="payroll-label">Total Payroll This Month</div>
                                    <div class="payroll-amount">
                                        Rp {{ number_format($totalPayroll ?? 0, 0, ',', '.') }}
                                    </div>
                                    <div class="payroll-progress">
                                        <div class="payroll-progress-bar" style="width:{{ $payrollProgress ?? 0 }}%">
                                        </div>
                                    </div>
                                    <div class="payroll-meta">
                                        <span>{{ $payrollProcessed ?? 0 }} Processed</span>
                                        <span>{{ $payrollPending ?? 0 }} Pending</span>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <div class="flex-fill text-center p-3"
                                        style="background:var(--surface-2);border-radius:var(--radius-sm);">
                                        <div
                                            style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--text-muted);">
                                            Processed</div>
                                        <div style="font-size:1.5rem;font-weight:800;color:var(--emerald-600);">
                                            {{ $payrollProcessed ?? 0 }}</div>
                                    </div>
                                    <div class="flex-fill text-center p-3"
                                        style="background:var(--surface-2);border-radius:var(--radius-sm);">
                                        <div
                                            style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--text-muted);">
                                            Pending</div>
                                        <div style="font-size:1.5rem;font-weight:800;color:var(--amber-600);">
                                            {{ $payrollPending ?? 0 }}</div>
                                    </div>
                                    <div class="flex-fill text-center p-3"
                                        style="background:var(--surface-2);border-radius:var(--radius-sm);">
                                        <div
                                            style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--text-muted);">
                                            Failed</div>
                                        <div style="font-size:1.5rem;font-weight:800;color:var(--rose-600);">
                                            {{ $payrollFailed ?? 0 }}</div>
                                    </div>
                                </div>

                                <a href="#" class="btn btn-outline-primary btn-block mt-3">
                                    <i class="fas fa-arrow-right mr-2"></i>Process Payroll
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Contract Expiry --}}
                    <div class="col-lg-4 col-md-6 col-12 mb-4">
                        <div class="dash-card h-100">
                            <div class="dash-card-header">
                                <h4 class="dash-card-title">
                                    <span class="title-icon" style="background:var(--danger-gradient)"><i
                                            class="fas fa-file-contract"></i></span>
                                    Contracts that Will Expire
                                </h4>
                                @if (isset($contractsExpiringCount) && $contractsExpiringCount > 0)
                                    <span class="stat-badge down">{{ $contractsExpiringCount }} expiring</span>
                                @endif
                            </div>
                            <div class="dash-card-body">
                                <div class="inner-scroll">

                                    @forelse($expiringContracts ?? [] as $contract)
                                        @php
                                            $daysLeft = now()->diffInDays(
                                                \Carbon\Carbon::parse($contract->end_date),
                                                false,
                                            );
                                            $urgencyClass =
                                                $daysLeft <= 7 ? 'urgent' : ($daysLeft <= 30 ? 'warning' : 'ok');
                                        @endphp
                                        <div class="contract-item">
                                            <div class="contract-days {{ $urgencyClass }}">
                                                {{ $daysLeft }}<span
                                                    style="font-size:.6rem;display:block;font-weight:600;">days</span>
                                            </div>
                                            <div style="flex:1;min-width:0;">
                                                <div style="font-size:.855rem;font-weight:700;color:var(--text-primary);">
                                                    {{ $contract->employee->employee_name }}</div>
                                                <div style="font-size:.77rem;color:var(--text-muted);">
                                                    Ends
                                                    {{ \Carbon\Carbon::parse($contract->end_date)->format('d M Y') }}
                                                    &nbsp;· {{ $contract->contract_type ?? 'PKWT' }}
                                                </div>
                                            </div>
                                            <a href="#" class="btn btn-sm btn-outline-primary">Renew</a>
                                        </div>
                                    @empty
                                        <div class="text-center py-5">
                                            <i class="fas fa-file-contract fa-3x text-muted mb-3 d-block"></i>
                                            <p class="text-muted mb-0" style="font-size:.875rem;">No contracts expiring
                                                soon</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- HR Calendar / Upcoming Events --}}
                    <div class="col-lg-4 col-12 mb-4">
                        <div class="dash-card h-100">
                            <div class="dash-card-header">
                                <h4 class="dash-card-title">
                                    <span class="title-icon" style="background:var(--primary-gradient)"><i
                                            class="fas fa-calendar-alt"></i></span>
                                    SK Letters
                                </h4>
                                <a href="#" class="btn btn-sm btn-outline-primary">+ Add</a>
                            </div>
                            <div class="dash-card-body">
                                <div class="inner-scroll">
                                    @forelse($upcomingEvents ?? [] as $event)
                                        @php
                                            $eventDate = \Carbon\Carbon::parse($event->event_date);
                                            $colorClass =
                                                $event->type === 'holiday'
                                                    ? 'success'
                                                    : ($event->type === 'deadline'
                                                        ? 'warning'
                                                        : 'primary');
                                        @endphp
                                        <div class="calendar-event">
                                            <div class="cal-date {{ $colorClass }}">
                                                <span class="cal-day">{{ $eventDate->format('d') }}</span>
                                                <span class="cal-mon">{{ $eventDate->format('M') }}</span>
                                            </div>
                                            <div class="cal-info">
                                                <div class="cal-title">{{ $event->title }}</div>
                                                <div class="cal-sub">{{ ucfirst($event->type) }} &nbsp;·&nbsp;
                                                    {{ $event->description ?? '' }}</div>
                                            </div>
                                        </div>
                                    @empty
                                        {{-- Static placeholder events when no DB data --}}
                                        <div class="calendar-event">
                                            <div class="cal-date primary">
                                                <span class="cal-day">{{ now()->addDays(3)->format('d') }}</span>
                                                <span class="cal-mon">{{ now()->addDays(3)->format('M') }}</span>
                                            </div>
                                            <div class="cal-info">
                                                <div class="cal-title">Monthly Payroll Deadline</div>
                                                <div class="cal-sub">Deadline · Finance & HR</div>
                                            </div>
                                        </div>
                                        <div class="calendar-event">
                                            <div class="cal-date success">
                                                <span class="cal-day">{{ now()->addDays(7)->format('d') }}</span>
                                                <span class="cal-mon">{{ now()->addDays(7)->format('M') }}</span>
                                            </div>
                                            <div class="cal-info">
                                                <div class="cal-title">Performance Review</div>
                                                <div class="cal-sub">HR Event · All Departments</div>
                                            </div>
                                        </div>
                                        <div class="calendar-event">
                                            <div class="cal-date warning">
                                                <span class="cal-day">{{ now()->addDays(14)->format('d') }}</span>
                                                <span class="cal-mon">{{ now()->addDays(14)->format('M') }}</span>
                                            </div>
                                            <div class="cal-info">
                                                <div class="cal-title">New Employee Onboarding</div>
                                                <div class="cal-sub">HR Event · 3 new hires</div>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

        {{-- =====================================================
             ANNOUNCEMENTS TABLE
        ===================================================== --}}
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="dash-card">
                            <div class="dash-card-header">
                                <h4 class="dash-card-title">
                                    <span class="title-icon" style="background:var(--primary-gradient)"><i
                                            class="fas fa-bullhorn"></i></span>
                                    Announcements
                                </h4>
                                <button id="btn-announcement" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus mr-1"></i> New Announcement
                                </button>
                            </div>
                            <div class="dash-card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="users-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center pl-4">Title</th>
                                                <th class="text-center">Publish Date</th>
                                                <th class="text-center">End Date</th>
                                                <th class="text-center pr-4">Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- /section-body --}}
        </section>
    </div>{{-- /main-content --}}
    {{-- Preview Modal --}}
    <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewModalLabel">
                        <i class="fas fa-eye mr-2"></i> Announcement Preview
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm mb-4" style="font-size:.875rem;">
                        <tbody>
                            <tr>
                                <th style="width:140px;color:var(--text-muted);">Publish Date</th>
                                <td id="previewDate" style="font-weight:700;"></td>
                            </tr>
                            <tr>
                                <th style="color:var(--text-muted);">End Date</th>
                                <td id="previewEndDate" style="font-weight:700;"></td>
                            </tr>
                            <tr>
                                <th style="color:var(--text-muted);">Created By</th>
                                <td id="previewEmployee" style="font-weight:700;"></td>
                            </tr>
                        </tbody>
                    </table>
                    <div id="previewContent" style="max-height:400px;overflow-y:auto;line-height:1.8;font-size:.9rem;">
                    </div>
                </div>
                <div class="modal-footer" style="background:var(--surface-2);">
                    <small class="text-muted text-center w-100">
                        <i class="fas fa-shield-alt mr-2"></i>Official announcement from HR Department ·
                        <a href="https://wa.me/6281138310552" target="_blank" class="text-success">Contact HR</a>
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Submission Modal --}}

@endsection

@push('scripts')
    {{-- <script src="{{ asset('library/chart.js/dist/Chart.min.js') }}"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3"></script>
    <script src="{{ asset('library/jqvmap/dist/jquery.vmap.min.js') }}"></script>
    <script src="{{ asset('library/jqvmap/dist/maps/jquery.vmap.world.js') }}"></script>
    <script src="{{ asset('library/summernote/dist/summernote-bs4.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>


    <script>
        //    SUBMISSION TYPE TOGGLE
        $(document).ready(function() {
            function toggleFields(type) {
                const isOvertime = type === 'Overtime';
                const isAnnual = type === 'Annual Leave';

                $('#leave_date_from, #leave_date_to').attr('type', isOvertime ? 'datetime-local' : 'date');
                $('#statusDiv').toggle(isOvertime);
                $('#annualLeaveInfo').toggle(isAnnual);
                @if ($canCreateOvertime)
                    $('#employeeList').toggle(isOvertime);
                @endif
            }

            toggleFields($('#type').val());
            $('#type').on('change', function() {
                toggleFields($(this).val());
            });
        });

        //    SELECT2


        //    ATTENDANCE CHART
        let ctx = document.getElementById('attendanceChart').getContext('2d');
        let attendanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Attendance %',
                    data: [],
                    backgroundColor: 'rgba(37,49,109,0.15)',
                    borderColor: 'rgba(37,49,109,0.8)',
                    borderWidth: 2,
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.parsed.y + '%'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: 'rgba(0,0,0,.05)'
                        },
                        ticks: {
                            callback: v => v + '%',
                            font: {
                                family: 'Plus Jakarta Sans',
                                size: 11
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: 'Plus Jakarta Sans',
                                size: 11
                            }
                        }
                    }
                }
            }
        });

        function loadChartData(startDate, endDate) {
            fetch(`{{ route('dashboardHR.data') }}?start_date=${startDate}&end_date=${endDate}`)
                .then(r => r.json())
                .then(data => {
                    attendanceChart.data.labels = data.data.map(i => i.date);
                    attendanceChart.data.datasets[0].data = data.data.map(i => i.percentage);
                    attendanceChart.update();
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadChartData(document.getElementById('startDate').value, document.getElementById('endDate').value);
        });

        document.getElementById('filterButton').addEventListener('click', function() {
            loadChartData(document.getElementById('startDate').value, document.getElementById('endDate').value);
        });
        //    DEPARTMENT bar CHART
        document.addEventListener('DOMContentLoaded', function() {
            const deptData = @json($departmentDistribution ?? []);
            const labels = deptData.map(d => d.name);
            const counts = deptData.map(d => d.count);
            const palette = ['#25316D', '#10b981', '#f59e0b', '#f43f5e', '#8b5cf6', '#0ea5e9', '#f97316',
                '#14b8a6'
            ];

            const dctx = document.getElementById('deptChart').getContext('2d');
            new Chart(dctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: counts,
                        backgroundColor: palette,
                        borderWidth: 2,
                        borderColor: '#fff',
                        hoverOffset: 6,
                    }]
                },
                options: {
                    cutout: '68%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: c => ` ${c.label}: ${c.parsed} employees`
                            }
                        }
                    }
                }
            });

            // Render legend
            const legendEl = document.getElementById('deptLegend');
            if (legendEl && labels.length) {
                const total = counts.reduce((a, b) => a + b, 0);
                labels.forEach((lbl, i) => {
                    const pct = total > 0 ? Math.round(counts[i] / total * 100) : 0;
                    legendEl.innerHTML += `
                    <li>
                        <span class="legend-dot" style="background:${palette[i % palette.length]}"></span>
                        ${lbl}
                        <span class="legend-val">${counts[i]} <span style="color:var(--text-muted);font-weight:500;">(${pct}%)</span></span>
                    </li>`;
                });
            }
        });

        //    DATATABLES — ANNOUNCEMENTS
        $(document).ready(function() {
            var table = $('#users-table').DataTable({
                processing: true,
                autoWidth: false,
                serverSide: true,
                ajax: {
                    url: '{{ route('announcements.announcements') }}',
                    type: 'GET'
                },
                responsive: true,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ],
                language: {
                    search: '_INPUT_',
                    searchPlaceholder: 'Search...'
                },
                columns: [{
                        data: 'title',
                        name: 'title',
                        className: 'text-center'
                    },
                    {
                        data: 'publish_date',
                        name: 'publish_date',
                        className: 'text-center',
                        render: d => {
                            if (!d) return '-';
                            const dt = new Date(d);
                            return `${String(dt.getDate()).padStart(2,'0')} ${['January','February','March','April','May','June','July','August','September','October','November','December'][dt.getMonth()]} ${dt.getFullYear()}`;
                        }
                    },
                    {
                        data: 'end_date',
                        name: 'end_date',
                        className: 'text-center',
                        render: d => {
                            if (!d) return '<span class="stat-badge neutral">Continuesly</span>';
                            const dt = new Date(d);
                            return `${String(dt.getDate()).padStart(2,'0')} ${['January','February','March','April','May','June','July','August','September','October','November','December'][dt.getMonth()]} ${dt.getFullYear()}`;
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ]
            });
        });

        //    PREVIEW MODAL
        $(document).on('click', '.preview-btn', function() {
            $('#previewEmployee').text($(this).data('employee'));
            $('#previewDate').text($(this).data('date'));
            $('#previewEndDate').text($(this).data('enddate'));
            $('#previewContent').html($(this).data('content'));
            $('#previewModal').modal('show');
        });

        //    ANNOUNCEMENT MODAL (SweetAlert)
        function openAnnouncementModal() {
            Swal.fire({
                title: 'Make an Announcement',
                html: `
                <form id="announcementForm" action="{{ route('dashboardHR.store') }}" method="POST">
                    @csrf
                    <div class="form-group mb-3 text-start">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group mb-3 text-start">
                        <label class="form-label">Announcement Content</label>
                        <textarea id="editor" name="content" class="form-control"></textarea>
                    </div>
                    <div class="form-group mb-3 text-start">
                        <label class="form-label">Publish Date</label>
                        <input type="date" name="publish_date" class="form-control" required>
                    </div>
                    <div class="form-group mb-3 text-start">
                        <label class="form-label">End Date <span class="text-muted">(optional)</span></label>
                        <input type="date" name="end_date" class="form-control">
                    </div>
                </form>`,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-paper-plane mr-1"></i> Publish',
                cancelButtonText: 'Cancel',
                width: '680px',
                focusConfirm: false,
                didOpen: () => {
                    if (tinymce.get('editor')) tinymce.get('editor').remove();
                    tinymce.init({
                        selector: '#editor',
                        plugins: 'lists link image table code',
                        toolbar: 'undo redo | styles | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code',
                        menubar: false,
                        height: 280,
                        license_key: 'gpl'
                    });
                },
                willClose: () => {
                    if (tinymce.get('editor')) tinymce.get('editor').remove();
                },
                preConfirm: () => {
                    tinymce.triggerSave();
                    const title = document.querySelector('input[name="title"]').value.trim();
                    const content = document.querySelector('textarea[name="content"]').value.trim();
                    const publish_date = document.querySelector('input[name="publish_date"]').value;
                    if (!title) {
                        Swal.showValidationMessage('Title is required');
                        return false;
                    }
                    if (!content) {
                        Swal.showValidationMessage('Content is required');
                        return false;
                    }
                    if (!publish_date) {
                        Swal.showValidationMessage('Publish date is required');
                        return false;
                    }
                    document.getElementById('announcementForm').submit();
                }
            });
        }

        document.getElementById('btn-announcement').addEventListener('click', openAnnouncementModal);
        document.getElementById('btn-announcement-quick').addEventListener('click', function(e) {
            e.preventDefault();
            openAnnouncementModal();
        });

        /* =====================================================
           FLASH MESSAGES
        ===================================================== */
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false
            });
        @endif
        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session('error') }}'
            });
        @endif

        /* =====================================================
           CLEANUP MODAL BACKDROP
        ===================================================== */
        $(document).on('hidden.bs.modal', function() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
        });
    </script>
    {{-- chart department --}}
    <script>
        let deptChartInstance = null;

        fetch('/dashboard/employee-by-department')
            .then(res => {
                if (!res.ok) throw new Error('Gagal memuat data');
                return res.json();
            })
            .then(data => {
                if (!data.length) return;

                const STATUS_CONFIG = [{
                        key: 'active',
                        label: 'Active',
                        color: '#1cc88a'
                    },
                    {
                        key: 'pending',
                        label: 'Pending',
                        color: '#f6c23e'
                    },
                    {
                        key: 'mutation',
                        label: 'Mutation',
                        color: '#36b9cc'
                    },
                    {
                        key: 'on_leave',
                        label: 'On Leave',
                        color: '#e74a3b'
                    },
                ];

                const canvas = document.getElementById('deptChart');
                const existing = Chart.getChart(canvas);
                if (existing) existing.destroy();

                deptChartInstance = new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: data.map(i => i.department),
                        datasets: STATUS_CONFIG.map(({
                            key,
                            label,
                            color
                        }) => ({
                            label,
                            data: data.map(i => i[key]),
                            backgroundColor: color,
                        })),
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    footer: (items) => {
                                        const total = items.reduce((sum, i) => sum + i.parsed.y, 0);
                                        return `Total: ${total}`;
                                    },
                                },
                            },
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    padding: 15,
                                },
                            },
                        },
                        scales: {
                            x: {
                                stacked: true
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                },
                            },
                        },
                    },
                });
            })
            .catch(err => console.error('Chart error:', err));

        let companyChartInstance = null;

        fetch('/dashboard/employee-by-company')
            .then(res => {
                if (!res.ok) throw new Error('Gagal memuat data');
                return res.json();
            })
            .then(data => {
                if (!data.length) return;

                const STATUS_CONFIG = [{
                        key: 'active',
                        label: 'Active',
                        color: '#1cc88a'
                    },
                    {
                        key: 'pending',
                        label: 'Pending',
                        color: '#f6c23e'
                    },
                    {
                        key: 'mutation',
                        label: 'Mutation',
                        color: '#36b9cc'
                    },
                    {
                        key: 'on_leave',
                        label: 'On Leave',
                        color: '#e74a3b'
                    },
                ];

                // Build custom legend
                const legend = document.getElementById('depthorizontal-barLegend');
                legend.innerHTML = STATUS_CONFIG.map(s => `
            <li style="display:inline-flex; align-items:center; gap:6px; margin-right:12px; font-size:12px; color:var(--text-muted, #6c757d);">
                <span style="width:10px; height:10px; border-radius:2px; background:${s.color}; display:inline-block;"></span>
                ${s.label}
            </li>
        `).join('');

                // Height dinamis
                const containerHeight = Math.max(300, data.length * 40 + 80);
                document.getElementById('companyChart').parentElement.style.height = containerHeight + 'px';

                const canvas = document.getElementById('companyChart');
                const existing = Chart.getChart(canvas);
                if (existing) existing.destroy();

                companyChartInstance = new Chart(canvas, {
                    type: 'bar', // v3: pakai 'bar' + indexAxis
                    data: {
                        labels: data.map(i => i.company),
                        datasets: STATUS_CONFIG.map(({
                            key,
                            label,
                            color
                        }) => ({
                            label,
                            data: data.map(i => i[key]),
                            backgroundColor: color,
                        })),
                    },
                    options: {
                        indexAxis: 'y', // v3: ini yang bikin horizontal
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    footer: (items) => {
                                        const total = items.reduce((s, i) => s + i.parsed.x, 0);
                                        return `Total: ${total}`;
                                    },
                                },
                            },
                        },
                        scales: {
                            x: {
                                stacked: true,
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            },
                            y: {
                                stacked: true
                            },
                        },
                    },
                });
            })
            .catch(err => console.error('Chart error:', err));
        // los
        document.addEventListener("DOMContentLoaded", function() {

            fetch('/dashboard/employee-by-los')
                .then(res => res.json())
                .then(data => {

                    const labels = data.map(i => i.range_label);
                    const values = data.map(i => i.total);

                    new Chart(document.getElementById('losChart'), {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Total Employee',
                                data: values,
                                backgroundColor: '#1cc88a',
                                borderRadius: 6
                            }]
                        },
                        options: {
                            indexAxis: 'y', // 🔥 INI KUNCI
                            responsive: true,
                            maintainAspectRatio: false,

                            layout: {
                                padding: 0
                            },

                            plugins: {
                                legend: {
                                    position: 'top'
                                }
                            },

                            scales: {
                                x: {
                                    beginAtZero: true,
                                    grace: 0
                                },
                                y: {
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                });
        });
    </script>
@endpush
