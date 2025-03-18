{{-- @extends('layouts.app')

@section('title', 'Dashboard Manager')

@push('style')
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    body {
        background-color: #f8f9fa;
    }
    
    .navbar {
        background-color: #1a237e;
        color: white;
        padding: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .logo {
        font-size: 1.5rem;
        font-weight: bold;
    }
    
    .user-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .user-info img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #c5cae9;
        border: 2px solid white;
    }
    
    .sidebar {
        width: 260px;
        background-color: #ffffff;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        height: calc(100vh - 64px);
        position: fixed;
        top: 64px;
        overflow-y: auto;
    }
    
    .menu-category {
        padding: 1rem;
        font-size: 0.8rem;
        font-weight: bold;
        color: #7986cb;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .menu-item {
        padding: 0.8rem 1rem 0.8rem 2rem;
        display: flex;
        align-items: center;
        gap: 0.8rem;
        color: #424242;
        text-decoration: none;
        border-left: 4px solid transparent;
    }
    
    .menu-item:hover, .menu-item.active {
        background-color: #ede7f6;
        color: #3f51b5;
        border-left: 4px solid #3f51b5;
    }
    
    .menu-item-icon {
        font-size: 1.2rem;
        width: 20px;
        text-align: center;
    }
    
    .main-content {
        margin-left: 260px;
        padding: 1.5rem;
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    
    .page-title {
        font-size: 1.8rem;
        color: #212529;
    }
    
    .date-range {
        display: flex;
        gap: 1rem;
        align-items: center;
    }
    
    .date-range select {
        padding: 0.5rem;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 0.9rem;
    }
    
    .dashboard-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
    }
    
    .stat-card:nth-child(1)::before { background-color: #3f51b5; }
    .stat-card:nth-child(2)::before { background-color: #4caf50; }
    .stat-card:nth-child(3)::before { background-color: #ff9800; }
    .stat-card:nth-child(4)::before { background-color: #f44336; }
    
    .stat-title {
        color: #6c757d;
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
        color: #4caf50;
    }
    
    .negative {
        color: #f44336;
    }
    
    .content-cards {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
    }
    
    .card {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f2f2f2;
    }
    
    .card-title {
        font-size: 1.2rem;
        font-weight: bold;
        color: #343a40;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .card-icon {
        width: 24px;
        height: 24px;
        background-color: #e3f2fd;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #3f51b5;
    }
    
    .card-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .card-btn {
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
        border-radius: 4px;
        cursor: pointer;
        border: 1px solid #ced4da;
        background-color: white;
    }
    
    .btn-primary {
        background-color: #3f51b5;
        color: white;
        border: none;
    }
    
    .btn-outline {
        border: 1px solid #3f51b5;
        color: #3f51b5;
    }
    
    .table-responsive {
        overflow-x: auto;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
    }
    
    th, td {
        padding: 0.8rem;
        text-align: left;
        border-bottom: 1px solid #f2f2f2;
    }
    
    th {
        font-weight: 600;
        color: #495057;
        background-color: #f8f9fa;
    }
    
    tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .badge {
        padding: 0.3rem 0.6rem;
        font-size: 0.75rem;
        border-radius: 20px;
    }
    
    .badge-success {
        background-color: #e8f5e9;
        color: #4caf50;
    }
    
    .badge-warning {
        background-color: #fff3e0;
        color: #ff9800;
    }
    
    .badge-danger {
        background-color: #ffebee;
        color: #f44336;
    }
    
    .badge-info {
        background-color: #e3f2fd;
        color: #2196f3;
    }
    
    .action-btn {
        background: none;
        border: none;
        color: #3f51b5;
        cursor: pointer;
        font-size: 0.9rem;
    }
    
    .performance-chart {
        height: 250px;
        margin-top: 1rem;
        background-color: #f8f9fa;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .chart-placeholder {
        font-size: 1.2rem;
        color: #6c757d;
    }
    
    .top-products {
        list-style: none;
    }
    
    .product-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.8rem 0;
        border-bottom: 1px solid #f2f2f2;
    }
    
    .product-info {
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }
    
    .product-image {
        width: 40px;
        height: 40px;
        border-radius: 4px;
        background-color: #f8f9fa;
    }
    
    .product-name {
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .product-category {
        font-size: 0.8rem;
        color: #6c757d;
    }
    
    .product-stats {
        text-align: right;
    }
    
    .product-sales {
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .product-quantity {
        font-size: 0.8rem;
        color: #6c757d;
    }
    
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }
    
    .action-card {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .action-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.1);
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
        color: #6c757d;
    }
    
    .performance-summary {
        display: flex;
        justify-content: space-between;
        padding: 1rem 0;
    }
    
    .summary-item {
        text-align: center;
    }
    
    .summary-label {
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 0.5rem;
    }
    
    .summary-value {
        font-size: 1.2rem;
        font-weight: 600;
    }
</style>

@endpush

@section('main')<div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Dashboard Admin</h1>
            </div>

            <div class="section-body">
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
                    
                    <div class="content-cards">
                        <div>
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">
                                        <div class="card-icon">📈</div>
                                        <span>Tren Penjualan</span>
                                    </div>
                                    <div class="card-actions">
                                        <button class="card-btn btn-outline">Mingguan</button>
                                        <button class="card-btn btn-primary">Bulanan</button>
                                    </div>
                                </div>
                                
                                <div class="performance-chart">
                                    <div class="chart-placeholder">[Grafik Tren Penjualan]</div>
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
                                        <button class="card-btn btn-primary">Lihat Detail</button>
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
                                        <button class="card-btn btn-outline">Eksport</button>
                                        <button class="card-btn btn-primary">Kelola Stok</button>
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
                                    <div style="padding: 0.8rem; border-left: 4px solid #f44336; background-color: #ffebee; margin-bottom: 0.8rem;">
                                        <div style="font-weight: bold; margin-bottom: 0.3rem;">Stok Menipis</div>
                                        <div style="font-size: 0.9rem;">6 produk berada di bawah batas minimal stok</div>
                                        <div style="font-size: 0.8rem; color: #6c757d; margin-top: 0.3rem;">5 jam yang lalu</div>
                                    </div>
                                    
                                    <div style="padding: 0.8rem; border-left: 4px solid #ff9800; background-color: #fff3e0; margin-bottom: 0.8rem;">
                                        <div style="font-weight: bold; margin-bottom: 0.3rem;">Pembatalan Transaksi</div>
                                        <div style="font-size: 0.9rem;">Transaksi #INV-2019 dibatalkan oleh Supervisor</div>
                                        <div style="font-size: 0.8rem; color: #6c757d; margin-top: 0.3rem;">8 jam yang lalu</div>
                                    </div>
                                    
                                    <div style="padding: 0.8rem; border-left: 4px solid #4caf50; background-color: #e8f5e9; margin-bottom: 0.8rem;">
                                        <div style="font-weight: bold; margin-bottom: 0.3rem;">Penerimaan Stok</div>
                                        <div style="font-size: 0.9rem;">Pengiriman dari PT Sukses Makmur telah diterima</div>
                                        <div style="font-size: 0.8rem; color: #6c757d; margin-top: 0.3rem;">Kemarin, 15:30</div>
                                    </div>
                                    
                                    <div style="padding: 0.8rem; border-left: 4px solid #2196f3; background-color: #e3f2fd; margin-bottom: 0.8rem;">
                                        <div style="font-weight: bold; margin-bottom: 0.3rem;">Closing Harian</div>
                                        <div style="font-size: 0.9rem;">Closing harian tanggal 16 Mar 2025 selesai</div>
                                        <div style="font-size: 0.8rem; color: #6c757d; margin-top: 0.3rem;">Kemarin, 22:15</div>
                                    </div>
                                </div>
                            </div>
                            
                         
                                
                                
            </div>
        </section>
    </div>
@endsection
@push('scripts')
  
@endpush --}}
@extends('layouts.app')

@section('title', 'Dashboard Manager')

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

        /* Utility Classes */
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

        /* Page Header */
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

        /* Stats Cards */
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

        /* Grid Layout */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }

        /* Cards */
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

        /* Buttons */
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

        /* Tables */
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

        /* Badges */
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

        /* Action Buttons */
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

        /* Charts */
        .chart-container {
            height: 250px;
            margin-top: 1rem;
            background-color: var(--gray-100);
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Quick Actions */
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

        /* Performance Summary */
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

        /* Notification cards */
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

        /* Responsive design */
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

    <!-- JavaScript untuk fungsionalitas responsive -->

@endsection
