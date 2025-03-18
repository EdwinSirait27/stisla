{{-- @extends('layouts.app')

@section('title', 'Dashboard Manager')

@push('style')
    <style>
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
            background-color: #343a40;
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
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: #1c354a;
        }
        
        .container {
            padding: 1rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .pos-area {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .products-area {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 1rem;
        }
        
        .search-bar {
            display: flex;
            margin-bottom: 1rem;
        }
        
        .search-bar input {
            flex: 1;
            padding: 0.5rem;
            border: 1px solid #ced4da;
            border-radius: 4px 0 0 4px;
            font-size: 1rem;
        }
        
        .search-bar button {
            padding: 0.5rem 1rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
        }
        
        .product-item {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 0.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .product-item:hover {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0,123,255,0.5);
        }
        
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin: 0 auto;
            display: block;
        }
        
        .product-name {
            font-size: 0.9rem;
            margin: 0.5rem 0;
        }
        
        .product-price {
            font-weight: bold;
            color: #343a40;
        }
        
        .cart-area {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 1rem;
            display: flex;
            flex-direction: column;
            height: calc(100vh - 120px);
        }
        
        .cart-header {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .cart-title {
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .clear-cart {
            color: #dc3545;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .cart-items {
            flex: 1;
            overflow-y: auto;
            margin-bottom: 1rem;
        }
        
        .cart-item {
            display: grid;
            grid-template-columns: 3fr 1fr 1fr 1fr;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f2f2f2;
        }
        
        .cart-item-name {
            font-size: 0.9rem;
        }
        
        .cart-item-qty {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .qty-btn {
            width: 25px;
            height: 25px;
            border: 1px solid #dee2e6;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .qty-value {
            width: 30px;
            text-align: center;
        }
        
        .cart-item-price,
        .cart-item-total {
            text-align: right;
            font-size: 0.9rem;
        }
        
        .cart-summary {
            border-top: 1px solid #dee2e6;
            padding-top: 1rem;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .grand-total {
            font-size: 1.2rem;
            font-weight: bold;
            margin: 1rem 0;
        }
        
        .checkout-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 0.8rem;
            width: 100%;
            font-size: 1rem;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 1rem;
        }
        
        .barcode-scanner {
            text-align: center;
            margin-bottom: 1rem;
            padding: 1rem;
            border: 2px dashed #dee2e6;
            border-radius: 4px;
        }
        
        .scanner-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #007bff;
        }
        
        .scan-input {
            padding: 0.5rem;
            width: 100%;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 1rem;
            text-align: center;
        }
        
        .category-filters {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
        }
        
        .category-btn {
            padding: 0.3rem 0.8rem;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 20px;
            font-size: 0.9rem;
            cursor: pointer;
            white-space: nowrap;
        }
        
        .category-btn.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
    </style>
    </style>
    @endpush
    @section('main')

    
    <div class="container">
        <div class="pos-area">
            <div class="products-area">
                <div class="barcode-scanner">
                    <div class="scanner-icon">📷</div>
                    <input type="text" class="scan-input" placeholder="Scan barcode atau ketik kode produk" autofocus>
                </div>
                
                <div class="search-bar">
                    <input type="text" placeholder="Cari produk...">
                    <button>Cari</button>
                </div>
                
                <div class="category-filters">
                    <button class="category-btn active">Semua</button>
                    <button class="category-btn">Makanan</button>
                    <button class="category-btn">Minuman</button>
                    <button class="category-btn">Snack</button>
                    <button class="category-btn">Peralatan Dapur</button>
                    <button class="category-btn">Pembersih</button>
                </div>
                
                <div class="product-grid">
                    <div class="product-item">
                        <img src="/api/placeholder/80/80" alt="Product" class="product-image">
                        <div class="product-name">Chitato Original 100g</div>
                        <div class="product-price">Rp 15.000</div>
                    </div>
                    <div class="product-item">
                        <img src="/api/placeholder/80/80" alt="Product" class="product-image">
                        <div class="product-name">Teh Botol Sosro 350ml</div>
                        <div class="product-price">Rp 5.000</div>
                    </div>
                    <div class="product-item">
                        <img src="/api/placeholder/80/80" alt="Product" class="product-image">
                        <div class="product-name">Indomie Goreng</div>
                        <div class="product-price">Rp 3.500</div>
                    </div>
                    <div class="product-item">
                        <img src="/api/placeholder/80/80" alt="Product" class="product-image">
                        <div class="product-name">Pocari Sweat 500ml</div>
                        <div class="product-price">Rp 7.000</div>
                    </div>
                    <div class="product-item">
                        <img src="/api/placeholder/80/80" alt="Product" class="product-image">
                        <div class="product-name">Sabun Lifebuoy 85g</div>
                        <div class="product-price">Rp 4.500</div>
                    </div>
                    <div class="product-item">
                        <img src="/api/placeholder/80/80" alt="Product" class="product-image">
                        <div class="product-name">Coca Cola 1.5L</div>
                        <div class="product-price">Rp 16.000</div>
                    </div>
                    <div class="product-item">
                        <img src="/api/placeholder/80/80" alt="Product" class="product-image">
                        <div class="product-name">Ultra Milk 1L</div>
                        <div class="product-price">Rp 18.000</div>
                    </div>
                    <div class="product-item">
                        <img src="/api/placeholder/80/80" alt="Product" class="product-image">
                        <div class="product-name">Mie Sedaap Goreng</div>
                        <div class="product-price">Rp 3.000</div>
                    </div>
                </div>
            </div>
            
            <div class="cart-area">
                <div class="cart-header">
                    <span class="cart-title">Keranjang Belanja</span>
                    <button class="clear-cart">Bersihkan</button>
                </div>
                
                <div class="cart-items">
                    <div class="cart-item">
                        <div class="cart-item-name">Chitato Original 100g</div>
                        <div class="cart-item-qty">
                            <button class="qty-btn">-</button>
                            <span class="qty-value">2</span>
                            <button class="qty-btn">+</button>
                        </div>
                        <div class="cart-item-price">Rp 15.000</div>
                        <div class="cart-item-total">Rp 30.000</div>
                    </div>
                    <div class="cart-item">
                        <div class="cart-item-name">Teh Botol Sosro 350ml</div>
                        <div class="cart-item-qty">
                            <button class="qty-btn">-</button>
                            <span class="qty-value">3</span>
                            <button class="qty-btn">+</button>
                        </div>
                        <div class="cart-item-price">Rp 5.000</div>
                        <div class="cart-item-total">Rp 15.000</div>
                    </div>
                    <div class="cart-item">
                        <div class="cart-item-name">Indomie Goreng</div>
                        <div class="cart-item-qty">
                            <button class="qty-btn">-</button>
                            <span class="qty-value">5</span>
                            <button class="qty-btn">+</button>
                        </div>
                        <div class="cart-item-price">Rp 3.500</div>
                        <div class="cart-item-total">Rp 17.500</div>
                    </div>
                </div>
                
                <div class="cart-summary">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>Rp 62.500</span>
                    </div>
                    <div class="summary-row">
                        <span>PPN (10%)</span>
                        <span>Rp 6.250</span>
                    </div>
                    <div class="summary-row">
                        <span>Diskon</span>
                        <span>Rp 0</span>
                    </div>
                    <div class="grand-total">
                        <span>Total</span>
                        <span>Rp 68.750</span>
                    </div>
                    
                    <button class="checkout-btn">Bayar</button>
                </div>
            </div>
        </div>
    </div>
@endsection --}}
@extends('layouts.app')

@section('title', 'Point of Sale Dashboard')

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
            background-color: #343a40;
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
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: #6c757d;
        }
        
        .container {
            padding: 1rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .pos-area {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .products-area {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 1rem;
        }
        
        .search-bar {
            display: flex;
            margin-bottom: 1rem;
        }
        
        .search-bar input {
            flex: 1;
            padding: 0.5rem;
            border: 1px solid #ced4da;
            border-radius: 4px 0 0 4px;
            font-size: 1rem;
        }
        
        .search-bar button {
            padding: 0.5rem 1rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
        }
        
        .product-item {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 0.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .product-item:hover {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0,123,255,0.5);
        }
        
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin: 0 auto;
            display: block;
        }
        
        .product-name {
            font-size: 0.9rem;
            margin: 0.5rem 0;
        }
        
        .product-price {
            font-weight: bold;
            color: #343a40;
        }
        
        .cart-area {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 1rem;
            display: flex;
            flex-direction: column;
            height: calc(100vh - 120px);
        }
        
        .cart-header {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .cart-title {
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .clear-cart {
            color: #dc3545;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .cart-items {
            flex: 1;
            overflow-y: auto;
            margin-bottom: 1rem;
        }
        
        .cart-item {
            display: grid;
            grid-template-columns: 3fr 1fr 1fr 1fr;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f2f2f2;
        }
        
        .cart-item-name {
            font-size: 0.9rem;
        }
        
        .cart-item-qty {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .qty-btn {
            width: 25px;
            height: 25px;
            border: 1px solid #dee2e6;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .qty-value {
            width: 30px;
            text-align: center;
        }
        
        .cart-item-price,
        .cart-item-total {
            text-align: right;
            font-size: 0.9rem;
        }
        
        .cart-summary {
            border-top: 1px solid #dee2e6;
            padding-top: 1rem;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .grand-total {
            font-size: 1.2rem;
            font-weight: bold;
            margin: 1rem 0;
        }
        
        .checkout-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 0.8rem;
            width: 100%;
            font-size: 1rem;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 1rem;
        }
        
        .barcode-scanner {
            text-align: center;
            margin-bottom: 1rem;
            padding: 1rem;
            border: 2px dashed #dee2e6;
            border-radius: 4px;
        }
        
        .scanner-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #007bff;
        }
        
        .scan-input {
            padding: 0.5rem;
            width: 100%;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 1rem;
            text-align: center;
        }
        
        .category-filters {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
        }
        
        .category-btn {
            padding: 0.3rem 0.8rem;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 20px;
            font-size: 0.9rem;
            cursor: pointer;
            white-space: nowrap;
        }
        
        .category-btn.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #343a40;
            text-align: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #dee2e6;
        }
    </style>
@endpush

@section('main')
<div class="layout-container">
    <div class="main-content">
        <div class="page-header">
            <br>
            <br>
            <br>
            <h1 class="page-title">Dashboard Kasir</h1>
        </div>
        <div class="pos-area">
            <div class="products-area">
                <div class="barcode-scanner">
                    <div class="scanner-icon">📷</div>
                    <input type="text" class="scan-input" placeholder="Scan barcode atau ketik kode produk" autofocus>
                </div>
                
                <div class="search-bar">
                    <input type="text" placeholder="Cari produk...">
                    <button>Cari</button>
                </div>
                
                <div class="category-filters">
                    <button class="category-btn active">Semua</button>
                    <button class="category-btn">Makanan</button>
                    <button class="category-btn">Minuman</button>
                    <button class="category-btn">Snack</button>
                    <button class="category-btn">Peralatan Dapur</button>
                    <button class="category-btn">Pembersih</button>
                </div>
                
                <div class="product-grid">
                    <div class="product-item">
                        <img src="/api/placeholder/80/80" alt="Product" class="product-image">
                        <div class="product-name">Chitato Original 100g</div>
                        <div class="product-price">Rp 15.000</div>
                    </div>
                    <div class="product-item">
                        <img src="/api/placeholder/80/80" alt="Product" class="product-image">
                        <div class="product-name">Teh Botol Sosro 350ml</div>
                        <div class="product-price">Rp 5.000</div>
                    </div>
                    <div class="product-item">
                        <img src="/api/placeholder/80/80" alt="Product" class="product-image">
                        <div class="product-name">Indomie Goreng</div>
                        <div class="product-price">Rp 3.500</div>
                    </div>
                    <div class="product-item">
                        <img src="/api/placeholder/80/80" alt="Product" class="product-image">
                        <div class="product-name">Pocari Sweat 500ml</div>
                        <div class="product-price">Rp 7.000</div>
                    </div>
                    <div class="product-item">
                        <img src="/api/placeholder/80/80" alt="Product" class="product-image">
                        <div class="product-name">Sabun Lifebuoy 85g</div>
                        <div class="product-price">Rp 4.500</div>
                    </div>
                    <div class="product-item">
                        <img src="/api/placeholder/80/80" alt="Product" class="product-image">
                        <div class="product-name">Coca Cola 1.5L</div>
                        <div class="product-price">Rp 16.000</div>
                    </div>
                    <div class="product-item">
                        <img src="/api/placeholder/80/80" alt="Product" class="product-image">
                        <div class="product-name">Ultra Milk 1L</div>
                        <div class="product-price">Rp 18.000</div>
                    </div>
                    <div class="product-item">
                        <img src="/api/placeholder/80/80" alt="Product" class="product-image">
                        <div class="product-name">Mie Sedaap Goreng</div>
                        <div class="product-price">Rp 3.000</div>
                    </div>
                </div>
            </div>
            
            <div class="cart-area">
                <div class="cart-header">
                    <span class="cart-title">Keranjang Belanja</span>
                    <button class="clear-cart">Bersihkan</button>
                </div>
                
                <div class="cart-items">
                    <div class="cart-item">
                        <div class="cart-item-name">Chitato Original 100g</div>
                        <div class="cart-item-qty">
                            <button class="qty-btn">-</button>
                            <span class="qty-value">2</span>
                            <button class="qty-btn">+</button>
                        </div>
                        <div class="cart-item-price">Rp 15.000</div>
                        <div class="cart-item-total">Rp 30.000</div>
                    </div>
                    <div class="cart-item">
                        <div class="cart-item-name">Teh Botol Sosro 350ml</div>
                        <div class="cart-item-qty">
                            <button class="qty-btn">-</button>
                            <span class="qty-value">3</span>
                            <button class="qty-btn">+</button>
                        </div>
                        <div class="cart-item-price">Rp 5.000</div>
                        <div class="cart-item-total">Rp 15.000</div>
                    </div>
                    <div class="cart-item">
                        <div class="cart-item-name">Indomie Goreng</div>
                        <div class="cart-item-qty">
                            <button class="qty-btn">-</button>
                            <span class="qty-value">5</span>
                            <button class="qty-btn">+</button>
                        </div>
                        <div class="cart-item-price">Rp 3.500</div>
                        <div class="cart-item-total">Rp 17.500</div>
                    </div>
                </div>
                
                <div class="cart-summary">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>Rp 62.500</span>
                    </div>
                    <div class="summary-row">
                        <span>PPN (10%)</span>
                        <span>Rp 6.250</span>
                    </div>
                    <div class="summary-row">
                        <span>Diskon</span>
                        <span>Rp 0</span>
                    </div>
                    <div class="grand-total">
                        <span>Total</span>
                        <span>Rp 68.750</span>
                    </div>
                    
                    <button class="checkout-btn">Bayar</button>
                </div>
            </div>
        </div>
    </div>
@endsection