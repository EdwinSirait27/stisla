<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Milon\Barcode\DNS1D;
use App\Models\Product;
class ProductController extends Controller
{
    // Fungsi untuk menyimpan produk baru
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'barcode' => 'nullable|string|unique:products',
            'sku' => 'nullable|string|unique:products',
        ]);

        // Jika barcode tidak diisi, generate barcode baru
        if (!$request->barcode) {
            $validatedData['barcode'] = 'P' . time(); // Barcode unik berdasarkan timestamp
        }

        // Jika SKU tidak diisi, generate SKU baru
        if (!$request->sku) {
            $validatedData['sku'] = Product::generateSKU();
        }

        // Simpan produk baru
        Product::create($validatedData);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    // Fungsi untuk mencetak barcode satu produk
    public function printBarcode($id)
    {
        $product = Product::findOrFail($id);

        $barcode = new DNS1D();
        $barcode_img = $barcode->getBarcodePNG($product->barcode, 'C128', 2, 50);

        return view('products.print_barcode', compact('product', 'barcode_img'));
    }

    // Fungsi untuk mencetak barcode beberapa produk yang dipilih
    public function printSelectedBarcodes(Request $request)
    {
        $productIds = $request->input('product_ids', []);
        $quantities = $request->input('quantities', []);

        $products = [];
        foreach ($productIds as $key => $id) {
            $product = Product::findOrFail($id);
            $quantity = isset($quantities[$key]) ? $quantities[$key] : 1;

            for ($i = 0; $i < $quantity; $i++) {
                $products[] = $product;
            }
        }

        $barcode = new DNS1D();

        return view('products.print_barcodes', compact('products', 'barcode'));
    }
}
