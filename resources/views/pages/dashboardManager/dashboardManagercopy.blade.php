{{-- 
@extends('layouts.app')

@section('title', 'Dashboard Manager')
@push('styles')
    <link rel="stylesheet" href="{{ asset('library/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endpush

@push('style')
    <style>
        * {
            margin: 20;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary: #3f51b5;
            --primary-dark: #1a237e;
            --primary-light: #c5cae9;
            --success: #4caf50;
            --warning: #ff9800;
            --danger: #f44336;
            --info: #2196f3;
            --gray-100: #f8f9fa;
            --gray-200: #f2f2f2;
            --gray-300: #e6e6e6;
            --gray-400: #ced4da;
            --gray-500: #adb5bd;
            --gray-600: #6c757d;
            --gray-700: #495057;
            --gray-800: #343a40;
            --gray-900: #212529;
            --white: #ffffff;
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.05);
            --border-radius: 8px;
        }

        body {
            background-color: var(--gray-100);
            line-height: 1.5;
            color: var(--gray-800);
        }

        .d-flex {
            display: flex;
        }

        .flex-column {
            flex-direction: column;
        }

        .justify-between {
            justify-content: space-between;
        }

        .align-center {
            align-items: center;
        }

        .gap-1 {
            gap: 0.5rem;
        }

        .gap-2 {
            gap: 1rem;
        }

        .gap-3 {
            gap: 1.5rem;
        }

        .mt-1 {
            margin-top: 0.5rem;
        }

        .mt-2 {
            margin-top: 1rem;
        }

        .mt-3 {
            margin-top: 1.5rem;
        }

        .mb-1 {
            margin-bottom: 0.5rem;
        }

        .mb-2 {
            margin-bottom: 1rem;
        }

        .mb-3 {
            margin-bottom: 1.5rem;
        }

        .mb-4 {
            margin-bottom: 2rem;
        }

        .p-2 {
            padding: 1rem;
        }

        .p-3 {
            padding: 1.5rem;
        }

        .py-2 {
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        .text-center {
            text-align: center;
        }

        .w-100 {
            width: 100%;
        }


        .layout-container {
            display: flex;
            padding-top: 64px;
            min-height: 100vh;
            position: relative;
        }



        .main-content {
            flex: 1;
            margin-left: 20px;
            padding: 1.5rem;
            transition: margin-left 0.3s ease;
        }

        .menu-category {
            padding: 1rem;
            font-size: 0.8rem;
            font-weight: bold;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .menu-item {
            padding: 0.8rem 1rem 0.8rem 2rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            color: var(--gray-700);
            text-decoration: none;
            border-left: 4px solid transparent;
            transition: all 0.2s ease;
        }

        .menu-item:hover,
        .menu-item.active {
            background-color: var(--gray-100);
            color: var(--primary);
            border-left: 4px solid var(--primary);
        }

        .menu-item-icon {
            font-size: 1.2rem;
            width: 20px;
            text-align: center;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title {
            font-size: 1.8rem;
            color: var(--gray-900);
            font-weight: 600;
        }

        .date-range {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .date-range select {
            padding: 0.5rem;
            border: 1px solid var(--gray-400);
            border-radius: 4px;
            font-size: 0.9rem;
            background-color: var(--white);
        }

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
        }

        .stat-card:nth-child(1)::before {
            background-color: var(--primary);
        }

        .stat-card:nth-child(2)::before {
            background-color: var(--success);
        }

        .stat-card:nth-child(3)::before {
            background-color: var(--warning);
        }

        .stat-card:nth-child(4)::before {
            background-color: var(--danger);
        }

        .stat-title {
            color: var(--gray-600);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .stat-comparison {
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .positive {
            color: var(--success);
        }

        .negative {
            color: var(--danger);
        }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }

        .card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-icon {
            width: 24px;
            height: 24px;
            background-color: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
        }

        .card-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border: none;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background-color: var(--primary-light);
        }

        .table-responsive {
            overflow-x: auto;
            margin-bottom: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        th,
        td {
            padding: 0.8rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        th {
            font-weight: 600;
            color: var(--gray-700);
            background-color: var(--gray-100);
        }

        tbody tr:hover {
            background-color: var(--gray-100);
        }

        .badge {
            padding: 0.3rem 0.6rem;
            font-size: 0.75rem;
            border-radius: 20px;
            font-weight: 500;
            display: inline-block;
        }

        .badge-success {
            background-color: #e8f5e9;
            color: var(--success);
        }

        .badge-warning {
            background-color: #fff3e0;
            color: var(--warning);
        }

        .badge-danger {
            background-color: #ffebee;
            color: var(--danger);
        }

        .badge-info {
            background-color: #e3f2fd;
            color: var(--info);
        }

        .action-btn {
            background: none;
            border: none;
            color: var(--primary);
            cursor: pointer;
            font-size: 0.9rem;
            padding: 0.3rem 0.6rem;
            border-radius: 4px;
            transition: background-color 0.2s ease;
        }

        .action-btn:hover {
            background-color: var(--gray-100);
        }

        .chart-container {
            height: 250px;
            margin-top: 1rem;
            background-color: var(--gray-100);
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .action-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .action-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .action-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .action-description {
            font-size: 0.8rem;
            color: var(--gray-600);
        }

        .performance-summary {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .summary-item {
            text-align: center;
            flex: 1;
            min-width: 100px;
        }

        .summary-label {
            font-size: 0.8rem;
            color: var(--gray-600);
            margin-bottom: 0.5rem;
        }

        .summary-value {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .notification-card {
            padding: 0.8rem;
            border-left: 4px solid transparent;
            margin-bottom: 0.8rem;
            border-radius: 4px;
            transition: transform 0.2s ease;
        }

        .notification-card:hover {
            transform: translateX(5px);
        }

        .notification-card.danger {
            background-color: #ffebee;
            border-left-color: var(--danger);
        }

        .notification-card.warning {
            background-color: #fff3e0;
            border-left-color: var(--warning);
        }

        .notification-card.success {
            background-color: #e8f5e9;
            border-left-color: var(--success);
        }

        .notification-card.info {
            background-color: #e3f2fd;
            border-left-color: var(--info);
        }

        .notification-title {
            font-weight: bold;
            margin-bottom: 0.3rem;
        }

        .notification-content {
            font-size: 0.9rem;
        }

        .notification-time {
            font-size: 0.8rem;
            color: var(--gray-600);
            margin-top: 0.3rem;
        }

        @media (max-width: 1200px) {
            .dashboard-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 992px) {
            .content-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .navbar-menu-toggle {
                display: block;
            }





            .main-content {
                margin-left: 0;
            }

            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 576px) {
            .dashboard-stats {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: 1fr;
            }

            .performance-summary {
                flex-direction: column;
            }

            .summary-item {
                width: 100%;
                margin-bottom: 1rem;
            }

            .stat-card {
                padding: 1rem;
            }

            .card {
                padding: 1rem;
            }

            .table-responsive {
                margin-left: -1rem;
                margin-right: -1rem;
                width: calc(100% + 2rem);
            }
        }
    </style>
@endpush

@section('main')
    <div class="layout-container">

        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">Dashboard Manager</h1>
                <div class="date-range">
                    <span>Periode:</span>
                    <select>
                        <option>Hari Ini</option>
                        <option>Minggu Ini</option>
                        <option selected>Bulan Ini</option>
                        <option>Bulan Lalu</option>
                        <option>Kuartal Ini</option>
                        <option>Tahun Ini</option>
                        <option>Kustom...</option>
                    </select>
                </div>
            </div>

            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-title">Total Pendapatan</div>
                    <div class="stat-value">Rp 154.780.500</div>
                    <div class="stat-comparison positive">
                        <span>▲ 15.2%</span>
                        <span>dari bulan lalu</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Total Pendapatan</div>
                    <div class="stat-value">Rp 154.780.500</div>
                    <div class="stat-comparison positive">
                        <span>▲ 15.2%</span>
                        <span>dari bulan lalu</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Laba Kotor</div>
                    <div class="stat-value">Rp 42.345.200</div>
                    <div class="stat-comparison positive">
                        <span>▲ 8.7%</span>
                        <span>dari bulan lalu</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Jumlah Transaksi</div>
                    <div class="stat-value">2,458</div>
                    <div class="stat-comparison positive">
                        <span>▲ 3.1%</span>
                        <span>dari bulan lalu</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Nilai Transaksi Rata-rata</div>
                    <div class="stat-value">Rp 62.900</div>
                    <div class="stat-comparison negative">
                        <span>▼ 2.5%</span>
                        <span>dari bulan lalu</span>
                    </div>
                </div>
            </div>

            <div class="quick-actions">
                <div class="action-card">
                    <div class="action-icon">📊</div>
                    <div class="action-title">Laporan Lengkap</div>
                    <div class="action-description">Lihat laporan penjualan, keuangan, dan operasional</div>
                </div>
                <div class="action-card">
                    <div class="action-icon">🏷️</div>
                    <div class="action-title">Kelola Produk</div>
                    <div class="action-description">Tambah, edit, atau hapus produk dan kategori</div>
                </div>
                <div class="action-card">
                    <div class="action-icon">👥</div>
                    <div class="action-title">Manajemen Pengguna</div>
                    <div class="action-description">Kelola akun staff dan pengaturan akses</div>
                </div>
            </div>

            <div class="content-grid">
                <div>
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <div class="card-icon">📈</div>
                                <span>Tren Penjualan</span>
                            </div>
                            <div class="card-actions">
                                <button class="btn btn-sm btn-outline">Mingguan</button>
                                <button class="btn btn-sm btn-primary">Bulanan</button>
                            </div>
                        </div>

                        <div class="chart-container">
                            <div>[Grafik Tren Penjualan]</div>
                        </div>

                        <div class="performance-summary">
                            <div class="summary-item">
                                <div class="summary-label">Penjualan Tertinggi</div>
                                <div class="summary-value">Rp 8.2 jt</div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-label">Penjualan Terendah</div>
                                <div class="summary-value">Rp 3.5 jt</div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-label">Rata-rata Harian</div>
                                <div class="summary-value">Rp 5.1 jt</div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-label">Pertumbuhan</div>
                                <div class="summary-value">+12.8%</div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <div class="card-icon">👥</div>
                                <span>Performa Staff</span>
                            </div>
                            <div class="card-actions">
                                <button class="btn btn-sm btn-primary">Lihat Detail</button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Posisi</th>
                                        <th>Transaksi</th>
                                        <th>Total Penjualan</th>
                                        <th>Rata-rata</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Dewi Lestari</td>
                                        <td>Kasir</td>
                                        <td>547</td>
                                        <td>Rp 36.450.000</td>
                                        <td>Rp 66.600</td>
                                        <td><span class="badge badge-success">Aktif</span></td>
                                    </tr>
                                    <tr>
                                        <td>Budi Santoso</td>
                                        <td>Kasir</td>
                                        <td>512</td>
                                        <td>Rp 32.180.000</td>
                                        <td>Rp 62.800</td>
                                        <td><span class="badge badge-success">Aktif</span></td>
                                    </tr>
                                    <tr>
                                        <td>Sari Widodo</td>
                                        <td>Supervisor</td>
                                        <td>385</td>
                                        <td>Rp 28.975.000</td>
                                        <td>Rp 75.200</td>
                                        <td><span class="badge badge-success">Aktif</span></td>
                                    </tr>
                                    <tr>
                                        <td>Andi Nugroho</td>
                                        <td>Kasir</td>
                                        <td>498</td>
                                        <td>Rp 29.840.000</td>
                                        <td>Rp 59.900</td>
                                        <td><span class="badge badge-success">Aktif</span></td>
                                    </tr>
                                    <tr>
                                        <td>Lina Putri</td>
                                        <td>Kasir</td>
                                        <td>516</td>
                                        <td>Rp 27.335.500</td>
                                        <td>Rp 53.000</td>
                                        <td><span class="badge badge-warning">Cuti</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <div class="card-icon">📦</div>
                                <span>Stok Produk</span>
                            </div>
                            <div class="card-actions">
                                <button class="btn btn-sm btn-outline">Eksport</button>
                                <button class="btn btn-sm btn-primary">Kelola Stok</button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Kategori</th>
                                        <th>Stok</th>
                                        <th>Min. Stok</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Beras Pulen Cap Bunga 5kg</td>
                                        <td>Bahan Pokok</td>
                                        <td>3</td>
                                        <td>8</td>
                                        <td><span class="badge badge-danger">Kritis</span></td>
                                        <td><button class="action-btn">Pesan</button></td>
                                    </tr>
                                    <tr>
                                        <td>Minyak Goreng Bimoli 2L</td>
                                        <td>Bahan Pokok</td>
                                        <td>5</td>
                                        <td>10</td>
                                        <td><span class="badge badge-warning">Menipis</span></td>
                                        <td><button class="action-btn">Pesan</button></td>
                                    </tr>
                                    <tr>
                                        <td>Tissue Paseo 250 sheets</td>
                                        <td>Keperluan Rumah</td>
                                        <td>4</td>
                                        <td>12</td>
                                        <td><span class="badge badge-danger">Kritis</span></td>
                                        <td><button class="action-btn">Pesan</button></td>
                                    </tr>
                                    <tr>
                                        <td>Sabun Lifebuoy 85g</td>
                                        <td>Peralatan Mandi</td>
                                        <td>25</td>
                                        <td>15</td>
                                        <td><span class="badge badge-success">Tersedia</span></td>
                                        <td><button class="action-btn">Detail</button></td>
                                    </tr>
                                    <tr>
                                        <td>Indomie Goreng</td>
                                        <td>Makanan Instan</td>
                                        <td>145</td>
                                        <td>50</td>
                                        <td><span class="badge badge-success">Tersedia</span></td>
                                        <td><button class="action-btn">Detail</button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <div class="card-icon">🔔</div>
                                <span>Notifikasi Sistem</span>
                            </div>
                        </div>

                        <div>
                            <div class="notification-card danger">
                                <div class="notification-title">Stok Menipis</div>
                                <div class="notification-content">6 produk berada di bawah batas minimal stok</div>
                                <div class="notification-time">5 jam yang lalu</div>
                            </div>

                            <div class="notification-card warning">
                                <div class="notification-title">Pembatalan Transaksi</div>
                                <div class="notification-content">Transaksi #INV-2019 dibatalkan oleh Supervisor</div>
                                <div class="notification-time">8 jam yang lalu</div>
                            </div>

                            <div class="notification-card success">
                                <div class="notification-title">Penerimaan Stok</div>
                                <div class="notification-content">Pengiriman dari PT Sukses Makmur telah diterima</div>
                                <div class="notification-time">Kemarin, 15:30</div>
                            </div>

                            <div class="notification-card info">
                                <div class="notification-title">Closing Harian</div>
                                <div class="notification-content">Closing harian tanggal 16 Mar 2025 selesai</div>
                                <div class="notification-time">Kemarin, 22:15</div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <div class="card-icon">🏆</div>
                                <span>Produk Terlaris</span>
                            </div>
                        </div>

                        <ul class="top-products">
                            <li class="product-item">
                                <div class="product-info">
                                    <div class="product-image"></div>
                                    <div>
                                        <div class="product-name">Beras Pulen Cap Bunga 5kg</div>
                                        <div class="product-category">Bahan Pokok</div>
                                    </div>
                                </div>
                                <div class="product-stats">
                                    <div class="product-sales">Rp 12.540.000</div>
                                    <div class="product-quantity">251 unit</div>
                                </div>
                            </li>
                            <li class="product-item">
                                <div class="product-info">
                                    <div class="product-image"></div>
                                    <div>
                                        <div class="product-name">Minyak Goreng Bimoli 2L</div>
                                        <div class="product-category">Bahan Pokok</div>
                                    </div>
                                </div>
                                <div class="product-stats">
                                    <div class="product-sales">Rp 9.875.000</div>
                                    <div class="product-quantity">235 unit</div>
                                </div>
                            </li>
                            <li class="product-item">
                                <div class="product-info">
                                    <div class="product-image"></div>
                                    <div>
                                        <div class="product-name">Gula Pasir Gulaku 1kg</div>
                                        <div class="product-category">Bahan Pokok</div>
                                    </div>
                                </div>
                                <div class="product-stats">
                                    <div class="product-sales">Rp 8.450.000</div>
                                    <div class="product-quantity">422 unit</div>
                                </div>
                            </li>
                            <li class="product-item">
                                <div class="product-info">
                                    <div class="product-image"></div>
                                    <div>
                                        <div class="product-name">Telur Ayam 1kg</div>
                                        <div class="product-category">Bahan Pokok</div>
                                    </div>
                                </div>
                                <div class="product-stats">
                                    <div class="product-sales">Rp 7.980.000</div>
                                    <div class="product-quantity">456 unit</div>
                                </div>
                            </li>
                            <li class="product-item">
                                <div class="product-info">
                                    <div class="product-image"></div>
                                    <div>
                                        <div class="product-name">Indomie Goreng</div>
                                        <div class="product-category">Makanan Instan</div>
                                    </div>
                                </div>
                                <div class="product-stats">
                                    <div class="product-sales">Rp 7.125.000</div>
                                    <div class="product-quantity">950 unit</div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection --}}

{{-- @extends('layouts.app')
@section('title', 'Manager Dashboard')

@push('style')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.min.css" />
<style>
    .card-icon { font-size: 2rem; color: #fff; }

    .quick-action-card {
        transition: all 0.25s ease-in-out;
        border-radius: 12px;
        border: none;
    }

    .quick-action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }

    .icon-circle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        border-radius: 50%;
    }

    .section-header h1 {
        font-weight: 600;
    }

    .list-unstyled li {
        margin-bottom: 8px;
    }

    /* === Fix agar Chart tidak mengecil === */
    .chart-container {
        position: relative;
        min-height: 300px;
        height: 100%;
    }

    canvas {
        width: 100% !important;
        height: 300px !important;
    }
</style>
@endpush

@section('main')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Dashboard Manager</h1>
        </div>

        <div class="section-body">
            <!-- Header Profil -->
            <div class="row align-items-center mb-4">
                <div class="col-md-8 d-flex align-items-center">
                    <img alt="image" src="{{ asset('img/avatar/avatar-2.png') }}" class="rounded-circle mr-3" width="70">
                    <div>
                        <h4 class="mb-1">{{ Auth::user()->employee->name ?? Auth::user()->username }}</h4>
                        <p class="text-muted mb-1">
                            {{ Auth::user()->employee->position->name ?? 'Manager' }} -
                            {{ Auth::user()->employee->department->name ?? 'Department' }}
                        </p>
                        <span class="badge badge-primary">Manager</span>
                    </div>
                </div>
                <div class="col-md-4 text-md-right mt-3 mt-md-0">
                    <small>Bergabung sejak {{ Auth::user()->employee->join_date ?? '2021-01-01' }}</small><br>
                    <small>Lama kerja: 3 Tahun 2 Bulan</small>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                @php
                    $actions = [
                        ['icon' => 'fa-user-check', 'label' => 'Setujui Cuti', 'url' => '#', 'color' => '#4e73df'],
                        ['icon' => 'fa-users', 'label' => 'Data Tim', 'url' => '#', 'color' => '#1cc88a'],
                        ['icon' => 'fa-chart-bar', 'label' => 'Evaluasi Kinerja', 'url' => '#', 'color' => '#36b9cc'],
                        ['icon' => 'fa-clock', 'label' => 'Monitoring Absensi', 'url' => '#', 'color' => '#f6c23e'],
                        ['icon' => 'fa-file-alt', 'label' => 'Laporan Bulanan', 'url' => '#', 'color' => '#e74a3b'],
                        ['icon' => 'fa-calendar-alt', 'label' => 'Agenda Tim', 'url' => '#', 'color' => '#858796'],
                    ];
                @endphp

                @foreach ($actions as $action)
                    <div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-4">
                        <a href="{{ $action['url'] }}" class="text-decoration-none">
                            <div class="card text-center shadow-sm quick-action-card h-100">
                                <div class="card-body py-4">
                                    <div class="icon-circle mb-3" style="background: {{ $action['color'] }}20;">
                                        <i class="fas {{ $action['icon'] }}" style="color: {{ $action['color'] }}; font-size: 28px;"></i>
                                    </div>
                                    <div class="text-dark font-weight-bold small">{{ $action['label'] }}</div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            <!-- Statistik Utama -->
            <div class="row">
                @php
                    $stats = [
                        ['color' => 'bg-primary', 'icon' => 'fa-users', 'title' => 'Total Anggota Tim', 'value' => '12'],
                        ['color' => 'bg-success', 'icon' => 'fa-plane', 'title' => 'Cuti Aktif', 'value' => '3 Orang'],
                        ['color' => 'bg-warning', 'icon' => 'fa-clock', 'title' => 'Kehadiran Rata-rata', 'value' => '92%'],
                        ['color' => 'bg-info', 'icon' => 'fa-chart-line', 'title' => 'Performa Tim', 'value' => '89 / 100'],
                    ];
                @endphp

                @foreach ($stats as $stat)
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card card-statistic-1">
                        <div class="card-icon {{ $stat['color'] }}">
                            <i class="fas {{ $stat['icon'] }}"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>{{ $stat['title'] }}</h4></div>
                            <div class="card-body">{{ $stat['value'] }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Grafik & Notifikasi -->
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-header"><h4>Grafik Kehadiran Tim</h4></div>
                        <div class="card-body chart-container">
                            <canvas id="teamAttendanceChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header"><h4>Notifikasi HR</h4></div>
                        <div class="card-body">
                            <div class="alert alert-info mb-2"><i class="fas fa-bullhorn"></i> Evaluasi Q4 segera dimulai</div>
                            <div class="alert alert-warning mb-0"><i class="fas fa-user-clock"></i> 2 pengajuan cuti menunggu persetujuan</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aktivitas & Performa -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header"><h4>Aktivitas Tim Terbaru</h4></div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Rina melakukan clock-in pukul 08:05</li>
                                <li><i class="fas fa-plane text-primary"></i> Budi mengajukan cuti 3 hari</li>
                                <li><i class="fas fa-chart-line text-info"></i> Evaluasi tim marketing selesai</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header"><h4>Performa Departemen</h4></div>
                        <div class="card-body chart-container">
                            <canvas id="performanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Hapus grafik sebelumnya agar tidak duplikat jika halaman re-render
    if (window.teamChart) window.teamChart.destroy();
    if (window.perfChart) window.perfChart.destroy();

    const teamCtx = document.getElementById('teamAttendanceChart').getContext('2d');
    window.teamChart = new Chart(teamCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt'],
            datasets: [{
                label: 'Kehadiran Tim (%)',
                data: [88, 90, 91, 92, 93, 94, 92, 95, 94, 96],
                borderColor: '#004085',
                borderWidth: 2,
                fill: false,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#004085'
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            scales: { y: { beginAtZero: true, max: 100 } }
        }
    });

    const perfCtx = document.getElementById('performanceChart').getContext('2d');
    window.perfChart = new Chart(perfCtx, {
        type: 'bar',
        data: {
            labels: ['Finance', 'HR', 'Marketing', 'IT', 'Sales'],
            datasets: [{
                label: 'Nilai Kinerja',
                data: [85, 90, 88, 92, 89],
                backgroundColor: '#36b9cc'
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            scales: { y: { beginAtZero: true, max: 100 } }
        }
    });
});
</script>
@endpush --}}
{{-- @extends('layouts.app')
@section('title', 'Manager Dashboard')

@push('style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.min.css" />
    <style>
        .card-icon {
            font-size: 2rem;
            color: #fff;
        }

        .quick-action-card {
            transition: all 0.25s ease-in-out;
            border-radius: 12px;
            border: none;
        }

        .quick-action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .icon-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            border-radius: 50%;
        }

        .section-header h1 {
            font-weight: 600;
        }

        .list-unstyled li {
            margin-bottom: 12px;
            padding: 8px;
            border-radius: 6px;
            transition: background 0.2s;
        }

        .list-unstyled li:hover {
            background: #f8f9fa;
        }

        .chart-container {
            position: relative;
            min-height: 300px;
            height: 100%;
        }

        canvas {
            width: 100% !important;
            height: 100px !important;
        }

        .stat-trend {
            font-size: 0.85rem;
            margin-top: 5px;
        }

        .stat-trend.up {
            color: #28a745;
        }

        .stat-trend.down {
            color: #dc3545;
        }

        .team-member-card {
            border-left: 3px solid #6777ef;
            margin-bottom: 10px;
            padding: 12px;
            border-radius: 6px;
            background: #fff;
            transition: all 0.2s;
        }

        .team-member-card:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-present {
            background: #d4edda;
            color: #155724;
        }

        .status-late {
            background: #fff3cd;
            color: #856404;
        }

        .status-absent {
            background: #f8d7da;
            color: #721c24;
        }

        .status-leave {
            background: #d1ecf1;
            color: #0c5460;
        }

        .pending-approval {
            border-left: 3px solid #ffc107;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 6px;
            background: #fff;
        }

        .approval-actions {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }

        .notification-item {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .notification-item:hover {
            transform: translateX(5px);
        }

        .time-ago {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .progress-thin {
            height: 8px;
            border-radius: 10px;
        }
    </style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Dashboard Manager</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active">Dashboard</div>
                </div>
            </div>

            <div class="section-body">
                <div class="row align-items-center mb-4">
                    <div class="col-md-8 d-flex align-items-center">
                        <img alt="image" src="{{ asset('img/avatar/avatar-2.png') }}" class="rounded-circle mr-3"
                            width="70">
                        <div>
                            <h4 class="mb-1">{{ Auth::user()->employee->name ?? Auth::user()->username }}</h4>
                            <p class="text-muted mb-1">
                                {{ Auth::user()->employee->position->name ?? 'Manager' }} -
                                {{ Auth::user()->employee->department->name ?? 'Department' }}
                            </p>
                            <span class="badge badge-primary">Manager</span>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-right mt-3 mt-md-0">
                        <small class="d-block"><i class="fas fa-calendar-check"></i> Joined since
                            {{ Auth::user()->employee->join_date ?? '2021-01-01' }}</small>
                        <small class="d-block text-primary"><i class="fas fa-briefcase"></i> {{ date('l, d F Y') }}</small>
                    </div>
                </div>
                <div class="row mb-4">
                    @php
                        $actions = [
                            [
                                'icon' => 'fa-user-check',
                                'label' => 'Approval',
                                'url' => route('Position.create'),
                                'color' => '#4e73df',
                                'badge' => '2',
                            ],
                            [
                                'icon' => 'fa-users',
                                'label' => 'Team Data',
                                'url' => route('Position.create'),
                                'color' => '#1cc88a',
                                'badge' => '',
                            ],
                            [
                                'icon' => 'fa-clock',
                                'label' => 'Attendance Monitoring',
                                'url' => route('Position.create'),
                                'color' => '#f6c23e',
                                'badge' => '',
                            ],
                            [
                                'icon' => 'fa-calendar-alt',
                                'label' => 'Team Agenda',
                                'url' => route('Position.create'),
                                'color' => '#858796',
                                'badge' => '',
                            ],
                        ];
                    @endphp

                    @foreach ($actions as $action)
                        <div class="col-lg-3 col-md-3 col-sm-4 col-8 mb-4">
                            <a href="{{ $action['url'] }}" class="text-decoration-none position-relative">
                                @if ($action['badge'])
                                    <span class="badge badge-danger position-absolute"
                                        style="top: -5px; right: 15px; z-index: 10;">{{ $action['badge'] }}</span>
                                @endif
                                <div class="card text-center shadow-sm quick-action-card h-100">
                                    <div class="card-body py-4">
                                        <div class="icon-circle mb-3" style="background: {{ $action['color'] }}20;">
                                            <i class="fas {{ $action['icon'] }}"
                                                style="color: {{ $action['color'] }}; font-size: 28px;"></i>
                                        </div>
                                        <div class="text-dark font-weight-bold small">{{ $action['label'] }}</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                <div class="row">
                    @php
                        $stats = [
                            [
                                'color' => 'bg-primary',
                                'icon' => 'fa-users',
                                'title' => 'Total Team Members',
                                'value' => '12',
                                'trend' => 'up',
                                'trendValue' => '+2 bulan ini',
                            ],
                            [
                                'color' => 'bg-success',
                                'icon' => 'fa-plane',
                                'title' => 'Active Leave',
                                'value' => '3 Orang',
                                'trend' => '',
                                'trendValue' => '25% dari tim',
                            ],
                            [
                                'color' => 'bg-warning',
                                'icon' => 'fa-clock',
                                'title' => 'Average Attendance',
                                'value' => '92%',
                                'trend' => 'up',
                                'trendValue' => '+2% dari bulan lalu',
                            ],
                            [
                                'color' => 'bg-info',
                                'icon' => 'fa-chart-line',
                                'title' => 'Submission',
                                'value' => '0',
                                'trend' => 'up',
                                'trendValue' => '+3',
                            ],
                        ];
                    @endphp

                    @foreach ($stats as $stat)
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card card-statistic-1">
                                <div class="card-icon {{ $stat['color'] }}">
                                    <i class="fas {{ $stat['icon'] }}"></i>
                                </div>
                                <div class="card-wrap">
                                    <div class="card-header">
                                        <h4>{{ $stat['title'] }}</h4>
                                    </div>
                                    <div class="card-body">
                                        {{ $stat['value'] }}
                                        @if ($stat['trend'])
                                            <div class="stat-trend {{ $stat['trend'] }}">
                                                <i class="fas fa-arrow-{{ $stat['trend'] }}"></i>
                                                {{ $stat['trendValue'] }}
                                            </div>
                                        @else
                                            <div class="stat-trend text-muted">{{ $stat['trendValue'] }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="row">
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Team Attendance Chart</h4>
                                <div class="card-header-action">
                                    <a href="{{ route('Position.create') }}" class="btn btn-primary btn-sm">Details</a>
                                </div>
                            </div>
                            <div class="card-body chart-container">
                                <canvas id="teamAttendanceChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Waiting for Approval</h4>
                                <div class="card-header-action">
                                    <span class="badge badge-warning">2 Item</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="pending-approval">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>edw</strong>
                                            <p class="mb-1 small">Annual Leave - 3 Days</p>
                                            <small class="text-muted">15-17 Nov 2024</small>
                                        </div>
                                        <span class="badge badge-warning">Pending</span>
                                    </div>
                                    <div class="approval-actions">
                                        <button class="btn btn-success btn-sm"><i class="fas fa-check"></i> Approve</button>
                                        <button class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Deny</button>
                                    </div>
                                </div>

                                <div class="pending-approval">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>edw</strong>
                                            <p class="mb-1 small">Izin Sakit</p>
                                            <small class="text-muted">22 Okt 2024</small>
                                        </div>
                                        <span class="badge badge-warning">Pending</span>
                                    </div>
                                    <div class="approval-actions">
                                        <button class="btn btn-success btn-sm"><i class="fas fa-check"></i> Approve</button>
                                        <button class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Deny</button>
                                    </div>
                                </div>

                                <a href="{{ route('Position.create') }}" class="btn btn-primary btn-block mt-3">
                                    See Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">

                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Notifikasi HR</h4>
                                <div class="card-header-action">
                                    <a href="#" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="notification-item alert alert-info mb-2">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <i class="fas fa-bullhorn"></i> <strong>Evaluasi Q4</strong>
                                            <p class="mb-1 small">Evaluasi Q4 segera dimulai. Siapkan dokumen tim Anda.</p>
                                        </div>
                                    </div>
                                    <span class="time-ago"><i class="fas fa-clock"></i> 2 jam lalu</span>
                                </div>

                                <div class="notification-item alert alert-warning mb-2">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <i class="fas fa-user-clock"></i> <strong>Pengajuan Cuti</strong>
                                            <p class="mb-1 small">2 pengajuan cuti menunggu persetujuan Anda.</p>
                                        </div>
                                    </div>
                                    <span class="time-ago"><i class="fas fa-clock"></i> 5 jam lalu</span>
                                </div>

                                <div class="notification-item alert alert-success mb-0">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <i class="fas fa-check-circle"></i> <strong>Laporan Disetujui</strong>
                                            <p class="mb-1 small">Laporan bulanan Anda telah disetujui direktur.</p>
                                        </div>
                                    </div>
                                    <span class="time-ago"><i class="fas fa-clock"></i> 1 hari lalu</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Aktivitas Tim Terbaru</h4>
                                <div class="card-header-action">
                                    <a href="#" class="btn btn-primary btn-sm">Refresh</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li>
                                        <i class="fas fa-check-circle text-success"></i>
                                        <strong>Rina Wijaya</strong> melakukan clock-in pukul 08:05
                                        <span class="time-ago float-right">5 menit lalu</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-plane text-primary"></i>
                                        <strong>Budi Santoso</strong> mengajukan cuti 3 hari
                                        <span class="time-ago float-right">2 jam lalu</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-chart-line text-info"></i>
                                        Evaluasi tim <strong>Marketing</strong> selesai
                                        <span class="time-ago float-right">1 hari lalu</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-file-alt text-warning"></i>
                                        <strong>Ahmad Yani</strong> submit laporan mingguan
                                        <span class="time-ago float-right">2 hari lalu</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-calendar-check text-success"></i>
                                        Meeting tim dijadwalkan untuk besok jam 10:00
                                        <span class="time-ago float-right">3 hari lalu</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Performa Departemen</h4>
                                <div class="card-header-action">
                                    <a href="{{ route('Position.create') }}" class="btn btn-primary btn-sm">Detail</a>
                                </div>
                            </div>
                            <div class="card-body chart-container">
                                <canvas id="performanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.teamChart) window.teamChart.destroy();
            if (window.perfChart) window.perfChart.destroy();

            const teamCtx = document.getElementById('teamAttendanceChart').getContext('2d');
            window.teamChart = new Chart(teamCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt'],
                    datasets: [{
                        label: 'Attendance Team (%)',
                        data: [88, 90, 91, 92, 93, 94, 92, 95, 94, 96],
                        borderColor: '#6777ef',
                        backgroundColor: 'rgba(103, 119, 239, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointBackgroundColor: '#6777ef',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 14
                            },
                            bodyFont: {
                                size: 13
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            const perfCtx = document.getElementById('performanceChart').getContext('2d');
            window.perfChart = new Chart(perfCtx, {
                type: 'bar',
                data: {
                    labels: ['Finance', 'HR', 'Marketing', 'IT', 'Sales'],
                    datasets: [{
                        label: 'Nilai Kinerja',
                        data: [85, 90, 88, 92, 89],
                        backgroundColor: [
                            'rgba(103, 119, 239, 0.8)',
                            'rgba(28, 200, 138, 0.8)',
                            'rgba(252, 196, 25, 0.8)',
                            'rgba(54, 185, 204, 0.8)',
                            'rgba(231, 74, 59, 0.8)'
                        ],
                        borderColor: [
                            '#6777ef',
                            '#1cc88a',
                            '#fcc419',
                            '#36b9cc',
                            '#e74a3b'
                        ],
                        borderWidth: 2,
                        borderRadius: 8
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value;
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush --}}