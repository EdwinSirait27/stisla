<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('parent_id')->nullable();
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->text('description')->nullable();
            $table->string('image', 255)->nullable();
            $table->enum('status', ['Active','Inactive'])->nullable();
            $table->timestamps();
        });
        
        // Menambahkan foreign key setelah tabel dibuat
        Schema::table('product_categories', function (Blueprint $table) {
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('product_categories')
                  ->onDelete('cascade');
        });
        

        // Create products table
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('category_id');
            $table->string('sku', 50);
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2);
            $table->decimal('cost', 15, 2);
            $table->decimal('weight', 10, 2)->nullable();
            $table->enum('status', ['Active','Inactive'])->nullable();
            $table->timestamps();
            
            $table->foreign('category_id')
                  ->references('id')
                  ->on('product_categories');
            
            $table->index('category_id');
            $table->index('slug');
            $table->index('sku');
        });

        // Create product_images table
        Schema::create('product_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->string('image_path', 255);
            $table->enum('status', ['Active','Inactive'])->nullable();
            $table->timestamps();
            
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('cascade');
        });

        // Create product_attributes table
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->timestamps();
        });

        // Create product_attribute_values table
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('attribute_id');
            $table->string('value', 100);
            $table->timestamps();
            
            $table->foreign('attribute_id')
                  ->references('id')
                  ->on('product_attributes')
                  ->onDelete('cascade');
        });

        // Create product_variants table
        Schema::create('product_variants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->string('sku', 50);
            $table->decimal('price', 15, 2);
            $table->integer('stock')->default(0);
            $table->timestamps();
            
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('cascade');
            
            $table->index('product_id');
        });

        // Create product_variant_attributes table
        Schema::create('product_variant_attributes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('variant_id');
            $table->uuid('attribute_value_id');
            $table->timestamps();
            
            $table->foreign('variant_id')
                  ->references('id')
                  ->on('product_variants')
                  ->onDelete('cascade');
            $table->foreign('attribute_value_id')
                  ->references('id')
                  ->on('product_attribute_values')
                  ->onDelete('cascade');
        });

        // Create warehouses table
        Schema::create('warehouses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->text('address');
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->enum('status', ['Active','Inactive'])->nullable();
            $table->timestamps();
        });

        // Create inventory table
        Schema::create('inventory', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('warehouse_id');
            $table->uuid('product_id');
            $table->uuid('variant_id')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->timestamps();
            
            $table->foreign('warehouse_id')
                  ->references('id')
                  ->on('warehouses');
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products');
            $table->foreign('variant_id')
                  ->references('id')
                  ->on('product_variants')
                  ->onDelete('set null');
            
            $table->index('product_id');
            $table->index('warehouse_id');
            $table->index('variant_id');
        });

        // Create inventory_movements table
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inventory_id');
            $table->integer('quantity_change');
            $table->enum('type', ['In', 'Out']);
            $table->uuid('reference_id')->nullable();
            $table->string('reference_type', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('inventory_id')
                  ->references('id')
                  ->on('inventory');
        });

        // Create suppliers table
        Schema::create('suppliers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->string('contact_person', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->enum('status', ['Active','Inactive'])->nullable();
            $table->timestamps();
        });

       

        // Create permissions table
        Schema::create('permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('device_lan_mac', 100);
            $table->string('device_wifi_mac', 100);
            $table->timestamps();
        });

        // Create users table
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('permission_id');
            $table->string('phone')->nullable();
            $table->string('name')->nullable();
            $table->string('username', 255);
            $table->string('password', 255);
            $table->enum('user_type', [ 'Admin', 
            'Head Warehouse',
            'Warehouse',
            'Head Buyer',
            'Buyer',
            'Head Finance',
            'Finance',
            'GM',
            'Manager Store',
            'Supervisor Store',
            'Store Cashier'])->nullable();
            $table->set('role', ['Admin','Head Warehouse',
            'Warehouse',
            'Head Buyer',
            'Buyer',
            'Head Finance',
            'Finance',
            'GM',
            'Manager Store',
            'Supervisor Store',
            'Store Cashier'])->nullable();
            $table->string('remember_token')->nullable();

            $table->enum('status', ['Active','Inactive'])->nullable();
            $table->timestamps();
        });
        
        // Menambahkan foreign key setelah tabel dibuat
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('permission_id')
            ->references('id')
            ->on('permissions')->onDelete('cascade');
        });

        // Create purchase_orders table
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('supplier_id');
            $table->string('order_number', 50)->unique();
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->enum('status', ['Draft', 'Ordered', 'Received', 'Cancelled'])->default('Draft');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->uuid('created_by');
            $table->timestamps();
            
            $table->foreign('supplier_id')
                  ->references('id')
                  ->on('suppliers');
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users');
            
            $table->index('supplier_id');
        });

        // Create purchase_order_items table
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('purchase_order_id');
            $table->uuid('product_id');
            $table->uuid('variant_id')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
            
            $table->foreign('purchase_order_id')
                  ->references('id')
                  ->on('purchase_orders')
                  ->onDelete('cascade');
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products');
            $table->foreign('variant_id')
                  ->references('id')
                  ->on('product_variants')
                  ->onDelete('set null');
            
            $table->index('purchase_order_id');
        });

        // Create goods_receipts table
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('purchase_order_id');
            $table->string('receipt_number', 50)->unique();
            $table->date('receipt_date');
            $table->text('notes')->nullable();
            $table->uuid('created_by');
            $table->timestamps();
            
            $table->foreign('purchase_order_id')
                  ->references('id')
                  ->on('purchase_orders');
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users');
            
            $table->index('purchase_order_id');
        });

        // Create goods_receipt_items table
        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('goods_receipt_id');
            $table->uuid('purchase_order_item_id');
            $table->integer('quantity_received');
            $table->timestamps();
            
            $table->foreign('goods_receipt_id')
                  ->references('id')
                  ->on('goods_receipts')
                  ->onDelete('cascade');
            $table->foreign('purchase_order_item_id')
                  ->references('id')
                  ->on('purchase_order_items');
        });

        // Create customers table
        Schema::create('members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->enum('member_type', ['Bronze', 'Silver', 'Gold','Platinum'])->nullable();
            $table->string('points', 20)->nullable();
            $table->timestamps();
        });

        // Create sales table
        Schema::create('sales', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('member_id')->nullable();
            $table->string('invoice_number', 50)->unique();
            $table->datetime('transaction_date');
            $table->decimal('total_amount', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('final_amount', 15, 2);
            $table->enum('payment_status', ['paid', 'partial', 'unpaid'])->default('unpaid');
            $table->string('payment_method', 50)->nullable();
            $table->uuid('user_id');
            $table->timestamps();
            
            $table->foreign('member_id')
                  ->references('id')
                  ->on('members')
                  ->onDelete('set null');
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users');
            
            $table->index('member_id');
            $table->index('invoice_number');
        });

        // Create sale_items table
        Schema::create('sale_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sale_id');
            $table->uuid('product_id');
            $table->uuid('variant_id')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
            
            $table->foreign('sale_id')
                  ->references('id')
                  ->on('sales')
                  ->onDelete('cascade');
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products');
            $table->foreign('variant_id')
                  ->references('id')
                  ->on('product_variants')
                  ->onDelete('set null');
            
            $table->index('sale_id');
            $table->index('product_id');
        });

        // Create payments table
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sale_id');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 50);
            $table->datetime('payment_date');
            $table->enum('status', ['Pending', 'Completed', 'Failed', 'Expired','Cancel'])->nullable();
            $table->string('transaction_id', 100)->nullable();
            $table->string('payment_type', 50)->nullable();
            $table->datetime('expiry_time')->nullable();
            $table->json('midtrans_response')->nullable();
            $table->timestamps();
            
            $table->foreign('sale_id')
                  ->references('id')
                  ->on('sales')
                  ->onDelete('cascade');
            
            $table->index('sale_id');
            $table->index('status');
        });

        
        Schema::create('chart_of_accounts', function (Blueprint $table) {
                $table->uuid('id')->primary();
            $table->string('code', 20)->unique();
            $table->string('name', 255);
            $table->enum('type', ['Asset', 'Liability', 'Equity', 'Revenue', 'Expense']);
            $table->uuid('parent_id')->nullable();
            $table->timestamps();
        });
        
        // Menambahkan foreign key setelah tabel dibuat
        Schema::table('chart_of_accounts', function (Blueprint $table) {
                $table->foreign('parent_id')
                  ->references('id')
                  ->on('chart_of_accounts')
                  ->onDelete('cascade');
        });
        // Create journal_entries table
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('date');
            $table->string('reference_number', 50);
            $table->uuid('reference_id')->nullable();
            $table->string('reference_type', 50)->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['Active','Inactive'])->nullable();
            $table->uuid('created_by');
            $table->timestamps();
            
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users');
            
            $table->index('date');
            $table->index('reference_number');
        });

        // Create journal_items table
        Schema::create('journal_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('journal_entry_id');
            $table->uuid('account_id');
            $table->text('description')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->timestamps();
            
            $table->foreign('journal_entry_id')
                  ->references('id')
                  ->on('journal_entries')
                  ->onDelete('cascade');
            $table->foreign('account_id')
                  ->references('id')
                  ->on('chart_of_accounts');
        });

        Schema::create('activity', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('activity_type'); // Misalnya: 'login', 'logout'
            $table->timestamp('activity_time')->useCurrent();
            $table->string('device_lan_mac')->nullable();
            $table->string('device_wifi_mac')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('user_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('user_id');
            $table->string('session_id')->unique();
            $table->string('ip_address')->nullable();
            $table->timestamp('last_activity')->nullable();
            $table->string('device_type')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
            Schema::dropIfExists('journal_items');
            Schema::dropIfExists('journal_entries');
            Schema::dropIfExists('chart_of_accounts');
            Schema::dropIfExists('payments');
            Schema::dropIfExists('sale_items');
            Schema::dropIfExists('sales');
            Schema::dropIfExists('members');
            Schema::dropIfExists('goods_receipt_items');
            Schema::dropIfExists('goods_receipts');
            Schema::dropIfExists('purchase_order_items');
            Schema::dropIfExists('purchase_orders');
            Schema::dropIfExists('users');
            Schema::dropIfExists('permissions');
            Schema::dropIfExists('suppliers');
            Schema::dropIfExists('inventory_movements');
            Schema::dropIfExists('inventory');
            Schema::dropIfExists('warehouses');
            Schema::dropIfExists('product_variant_attributes');
            Schema::dropIfExists('product_variants');
            Schema::dropIfExists('product_attribute_values');
            Schema::dropIfExists('product_attributes');
            Schema::dropIfExists('product_images');
            Schema::dropIfExists('products');
            Schema::dropIfExists('product_categories');
    }
};
