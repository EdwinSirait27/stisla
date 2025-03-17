<!-- resources/views/sales/create.blade.php -->
<div class="mb-3">
    <label for="barcode" class="form-label">Scan Barcode</label>
    <input type="text" class="form-control" id="barcode" name="barcode" autofocus>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const barcodeInput = document.getElementById('barcode');
    
    // Fokus pada input barcode saat halaman dimuat
    barcodeInput.focus();
    
    // Tangani input barcode
    barcodeInput.addEventListener('keydown', function(e) {
        // Jika tombol Enter ditekan
        if (e.key === 'Enter') {
            e.preventDefault();
            const barcode = this.value.trim();
            
            if (barcode) {
                // Kirim request AJAX untuk mendapatkan data produk
                fetchProductByBarcode(barcode);
                this.value = '';
            }
        }
    });
    
    // Fungsi untuk mendapatkan data produk berdasarkan barcode
    function fetchProductByBarcode(barcode) {
        fetch(`/api/products/barcode/${barcode}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    addProductToCart(data.product);
                } else {
                    alert('Produk tidak ditemukan!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memproses barcode');
            });
    }
    
    // Fungsi untuk menambahkan produk ke keranjang
    function addProductToCart(product) {
        // Cek apakah produk sudah ada di keranjang
        const existingRow = document.querySelector(`tr[data-product-id="${product.id}"]`);
        
        if (existingRow) {
            // Jika produk sudah ada, tambahkan kuantitas
            const qtyInput = existingRow.querySelector('.quantity-input');
            const currentQty = parseInt(qtyInput.value);
            qtyInput.value = currentQty + 1;
            updateSubtotal(existingRow);
        } else {
            // Jika produk belum ada, tambahkan baris baru
            addNewProductRow(product);
        }
        
        // Update total keseluruhan
        updateCartTotal();
    }
    
    // Fungsi implementasi lainnya...
});
</script>