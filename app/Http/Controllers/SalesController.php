<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use App\Models\Terms;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use App\Rules\NoXSSInput;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function create()
    {
        $settings = Setting::first();
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . Str::random(5);
        return view('sales.create', compact('settings', 'invoiceNumber'));
    }

    // Fungsi untuk menyimpan transaksi penjualan
    public function store(Request $request)
    {
        $request->validate([
            'invoice_number' => 'required|string|unique:sales',
            'customer_name' => 'nullable|string',
            'product_ids' => 'required|array',
            'quantities' => 'required|array',
            'prices' => 'required|array',
            'total_amount' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'tax' => 'nullable|numeric',
            'final_amount' => 'required|numeric',
            'payment_method' => 'required|in:tunai,kartu,transfer,qris',
            'amount_paid' => 'required|numeric',
        ]);

        DB::beginTransaction();

        try {
            // Simpan data penjualan
            $sale = Sale::create([
                'invoice_number' => $request->invoice_number,
                'user_id' => auth()->id(),
                'customer_name' => $request->customer_name,
                'total_amount' => $request->total_amount,
                'discount' => $request->discount ?? 0,
                'tax' => $request->tax ?? 0,
                'final_amount' => $request->final_amount,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->amount_paid >= $request->final_amount ? 'lunas' : 'sebagian',
                'amount_paid' => $request->amount_paid,
                'change_amount' => max(0, $request->amount_paid - $request->final_amount),
                'notes' => $request->notes,
            ]);

            // Simpan item penjualan
            foreach ($request->product_ids as $key => $productId) {
                $product = Product::findOrFail($productId);
                $quantity = $request->quantities[$key];
                $price = $request->prices[$key];
                $subtotal = $price * $quantity;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);

                // Kurangi stok produk
                if ($product->track_stock) {
                    $product->stock -= $quantity;
                    $product->save();

                    // Catat penyesuaian stok
                    $product->stockAdjustments()->create([
                        'user_id' => auth()->id(),
                        'adjustment_type' => 'kurang',
                        'quantity' => $quantity,
                        'stock_before' => $product->stock + $quantity,
                        'stock_after' => $product->stock,
                        'notes' => 'Penjualan: ' . $sale->invoice_number,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('sales.receipt', $sale->id)
                ->with('success', 'Transaksi berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Fungsi untuk mencetak struk
    public function receipt($id)
    {
        $sale = Sale::with(['user', 'saleItems.product'])->findOrFail($id);
        $settings = Setting::first();

        return view('sales.receipt', compact('sale', 'settings'));
    }
}
