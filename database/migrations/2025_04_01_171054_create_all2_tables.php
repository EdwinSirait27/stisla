<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('permission_id');
            $table->uuid('employee_id');
            $table->string('username', 255);
            $table->string('password', 255);
            $table->enum('user_type', [
                'Admin',
                'Head Warehouse',
                'Warehouse',
                'Head Buyer',
                'Buyer',
                'Head Finance',
                'Finance',
                'GM',
                'Manager Store',
                'Supervisor Store',     
                'Store Cashier'
            ])->nullable();
            $table->set('role', [
                'Admin',
                'Head Warehouse',
                'Warehouse',
                'Head Buyer',
                'Buyer',
                'Head Finance',
                'Finance',
                'GM',
                'Manager Store',
                'Supervisor Store',
                'Store Cashier'
            ])->nullable();
            $table->string('remember_token')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->nullable();
            $table->timestamps();
        });
        Schema::create('permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('device_lan_mac', 100);
            $table->string('device_wifi_mac', 100);
            $table->string('description')->nullable();
            $table->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('activity_type'); // Misalnya: 'login', 'logout'
            $table->uuid('record_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamp('activity_time')->useCurrent();
            $table->string('device_lan_mac')->nullable();
            $table->string('device_wifi_mac')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('session_id')->unique();
            $table->string('ip_address')->nullable();
            $table->timestamp('last_activity')->nullable();
            $table->string('device_type')->nullable();
            $table->timestamps();
        });
        Schema::create('brands', function (Blueprint $table) {
            $table->uuid('brand_id')->primary();
            $table->string('brandCode', 20)->unique();
            $table->string('brandName', 100);
            $table->string('logo_url')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->uuid('createdBy')->nullable();
            $table->uuid('updatedBy')->nullable();
        });
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('category_id')->primary();
            $table->string('categoryCode', 20)->unique();
            $table->string('categoryName', 100);
            $table->uuid('parentCategory_id')->nullable();
            $table->tinyInteger('hierarchyLevel')->default(1);
            $table->string('categoryPath')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->uuid('createdBy')->nullable();
            $table->uuid('updatedBy')->nullable();
        });
        Schema::create('uoms', function (Blueprint $table) {
            $table->uuid('uom_id')->primary();
            $table->string('uomCode', 20)->unique();
            $table->enum('uom', ['Each', 'Kg', 'Box', 'Gram', 'Liter', 'Pack']);
            $table->string('abbreviation', 10);
            $table->decimal('conversionFactor', 12, 4)->default(1);
            $table->boolean('isDecimal')->default(false);
            $table->enum('status', ['Active', 'Inactive'])->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('tax_statuses', function (Blueprint $table) {
            $table->uuid('taxStatus_id')->primary();
            $table->enum('taxStatus', ['Taxable', 'Non-Taxable', 'Exempt']);
            $table->decimal('taxRate', 5, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->enum('status', ['Active', 'Inactive'])->nullable();
        });
        Schema::create('statuses', function (Blueprint $table) {
            $table->uuid('status_id')->primary();
            $table->enum('status', ['Active', 'Freeze', 'Inactive', 'Discontinued', 'Seasonal']);
            $table->timestamps();
        });
        Schema::create('master_products', function (Blueprint $table) {
            $table->uuid('product_id')->primary();
            $table->string('plu', 50)->unique();
            $table->string('sku', 50)->unique()->nullable();
            $table->string('description', 255);
            $table->text('longDescription')->nullable();
            $table->uuid('brand_id')->nullable();
            $table->uuid('category_id')->nullable();
            $table->uuid('uom_id');
            $table->uuid('taxStatus_id');
            // $table->uuid('status_id');
            $table->decimal('goodStock', 12, 3)->default(0);
            $table->decimal('badStock', 12, 3)->default(0);
            $table->decimal('cogs', 12, 4)->default(0);
            $table->decimal('retailPrice', 12, 4)->default(0);
            $table->decimal('memberBronzePrice', 12, 4)->default(0);
            $table->decimal('memberSilverPrice', 12, 4)->default(0);
            $table->decimal('memberGoldPrice', 12, 4)->default(0);
            $table->decimal('minStock', 10, 2)->default(0);
            $table->decimal('maxStock', 10, 2)->default(0);
            $table->decimal('weight', 10, 3)->default(0);
            $table->decimal('volume', 10, 3)->default(0);
            $table->enum('status', ['Active', 'Freeze', 'Inactive', 'Discontinued', 'Seasonal', 'Sale', 'Not Salable'])->default('Active');

            $table->uuid('createdBy')->nullable();
            $table->uuid('updatedBy')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('product_bundles', function (Blueprint $table) {
            $table->uuid('bundle_id')->primary();
            $table->uuid('parent_product_id');
            $table->uuid('component_product_id');
            $table->decimal('quantity', 10, 2);
            $table->unique(['parent_product_id', 'component_product_id'], 'product_bundles_parent_component_unique');
            $table->timestamps();


        });
        Schema::create('product_dimensions', function (Blueprint $table) {
            $table->uuid('dim_id')->primary();
            $table->uuid('product_id');
            $table->uuid('uom_id');
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('height', 10, 2)->nullable();
            $table->decimal('diameter', 10, 2)->nullable();
            $table->decimal('weight', 10, 3)->nullable();
            $table->string('imageUrl')->nullable();
            $table->boolean('isPrimary')->default(false);
            $table->timestamps();
        });
        Schema::create('product_images', function (Blueprint $table) {
            $table->uuid('image_id')->primary();
            $table->uuid('product_id');
            $table->string('imageUrl');
            $table->boolean('isThumbnail')->default(false);
            $table->integer('sortOrder')->default(0);
            $table->string('altText', 255)->nullable();
            $table->timestamps();
        });
        Schema::create('barcodes', function (Blueprint $table) {
            $table->uuid('barcode_id')->primary();
            $table->uuid('product_id');
            $table->string('barcode', 50);
            $table->boolean('isPrimary')->default(false);
            $table->timestamps();
        });
        Schema::create('uom_converts', function (Blueprint $table) {
            $table->uuid('uomConvert_id')->primary();
            $table->uuid('product_id');
            $table->uuid('fromUom_id');
            $table->uuid('toUom_id');
            $table->decimal('qtyConvert', 10, 4);
            $table->decimal('cogs', 10, 2);
            $table->decimal('retailPrice', 10, 2);
            $table->decimal('memberBronzePrice', 10, 2);
            $table->decimal('memberSilverPrice', 10, 2);
            $table->decimal('memberGoldPrice', 10, 2);
            $table->decimal('nonmember', 10, 2);
            $table->boolean('salable')->default(true);
            $table->timestamps();
        });
        Schema::create('parent_suppliers', function (Blueprint $table) {
            $table->uuid('parentSupplier_id')->primary();
            $table->string('supplierCode', 20)->unique();
            $table->string('supplierName', 100);
            $table->string('contactName', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('alternatePhone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 50)->nullable();
            $table->string('province', 50)->nullable();
            $table->string('postalcode', 10)->nullable();
            $table->string('country', 50)->default('Indonesia');
            $table->string('email', 100)->nullable();
            $table->string('website', 100)->nullable();
            $table->string('taxIdentificationNumber', 50)->nullable();
            $table->enum('paymentTerm', ['Cash', '7 Days', '14 Days', '30 Days', '60 Days'])->default('30 Days');
            $table->text('notes')->nullable();
            $table->enum('status', ['Active', 'Inactive', 'Blacklisted'])->default('Active');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('tax_supplier_statuses', function (Blueprint $table) {
            $table->uuid('taxSupplierStatus_id')->primary();
            $table->string('statusName', 50)->nullable();
            $table->text('description')->nullable();
            $table->decimal('taxRate', 5, 2)->default(0);
            $table->enum('status', ['Active', 'Inactive'])->nullable();
            $table->timestamps();
        });
        Schema::create('suppliers', function (Blueprint $table) {
            $table->uuid('supplier_id')->primary();
            $table->uuid('parentSupplier_id');
            $table->string('supplierCode', 20)->unique();
            $table->string('supplierNameCategory', 100);
            $table->string('contactName', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('email', 100)->nullable();
            $table->integer('top')->comment('Term of Payment in days')->default(0);
            $table->uuid('taxSupplierStatus_id');
            $table->decimal('creditLimit', 12, 2)->nullable();
            $table->decimal('currentBalance', 12, 2)->default(0);
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('product_suppliers', function (Blueprint $table) {
            $table->uuid('productSupplier_id')->primary();
            $table->uuid('product_id');
            $table->uuid('supplier_id');
            $table->decimal('purchasePrice', 10, 2);
            $table->enum('returnStatus', ['Allowed', 'Not Allowed'])->default('Allowed');
            $table->boolean('isDefaultSupplier')->default(false);
            $table->integer('supplierLeadTime')->nullable()->comment('In days');
            $table->string('discountTerms', 100)->nullable();
            $table->date('contractExpirationDate')->nullable();
            $table->unique(['product_id', 'supplier_id']);
            $table->timestamps();
        });
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->uuid('paymentMethod_id')->primary();
            $table->enum('methodName', ['Cash', 'Credit Card', 'Debit Card', 'E-Wallet', 'Mobile Payment', 'Bank Transfer', 'Qris']);
            $table->string('description')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->nullable();

            $table->timestamps();
        });
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('customer_id')->primary();
            $table->string('fullName', 100);
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable()->unique();
            $table->enum('membershipLevel', ['Bronze', 'Silver', 'Gold', 'Non Member'])->nullable();
            $table->integer('loyaltyPoints')->default(0);
            $table->date('birthday')->nullable();
            $table->date('registrationDate')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('sales', function (Blueprint $table) {
            $table->uuid('sale_id')->primary();
            $table->uuid('store_id');
            $table->uuid('customer_id')->nullable();
            $table->uuid('user_id');
            $table->string('invoiceNumber')->unique();
            $table->dateTime('saleDate');
            $table->decimal('totalAmount', 10, 2);
            $table->decimal('discountAmount', 10, 2)->default(0);
            $table->decimal('taxAmount', 10, 2)->default(0);
            $table->uuid('paymentMethod_id')->nullable();
            $table->enum('transactionStatus', ['Completed', 'Pending', 'Refunded', 'Partially Refunded', 'Cancelled']);
            $table->boolean('isTaxInclusive')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        Schema::create('sales_details', function (Blueprint $table) {
            $table->uuid('saleDetail_id')->primary();
            $table->uuid('sale_id');
            $table->uuid('product_id');
            $table->uuid('uom_id');
            $table->uuid('promotion_id')->nullable();
            $table->decimal('quantitySold', 10, 2)->unsigned();
            $table->decimal('unitPrice', 10, 2);
            $table->decimal('originalPrice', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('lineDiscount', 10, 2)->default(0);
            $table->decimal('totalLineAmount', 10, 2);
            $table->timestamps();
        });
        Schema::create('applied_promotions', function (Blueprint $table) {
            $table->uuid('applied_promotions_id')->primary();
            $table->uuid('sale_id');
            $table->uuid('promotion_id');
            $table->decimal('discount_amount', 12, 2);
            $table->timestamps();
        });
        Schema::create('sales_payments', function (Blueprint $table) {
            $table->uuid('payment_id')->primary();
            $table->uuid('sale_id');
            $table->uuid('paymentMethod_id');
            $table->decimal('amount', 10, 2);
            $table->string('reference', 100)->nullable();
            $table->timestamps();
        });
        Schema::create('returns', function (Blueprint $table) {
            $table->uuid('return_id')->primary();
            $table->uuid('sale_id');
            $table->uuid('product_id');
            $table->uuid('uom_id');

            $table->decimal('quantityReturned', 10, 2);
            $table->string('returnReason');
            $table->dateTime('returnDate');
            $table->decimal('refundAmount', 10, 2);
            $table->uuid('user_id');
            $table->timestamps();
        });
        Schema::create('promotion_types', function (Blueprint $table) {
            $table->uuid('promotion_type_id')->primary();
            $table->string('name'); // 'Percentage', 'Fixed Amount', 'Bundle', etc.
            $table->string('description');
        });
        Schema::create('promotions', function (Blueprint $table) {
            $table->uuid('promotion_id')->primary();
            $table->string('code')->unique();
            $table->uuid('promotion_type_id');
            $table->string('name');
            $table->text('description');
            $table->dateTime('startDate');
            $table->dateTime('endDate');
            $table->decimal('discountValue', 10, 2);
            $table->boolean('is_stackable')->default(false);
            $table->integer('priority')->default(0);
            $table->enum('status', ['Active', 'Inactive'])->nullable();

            $table->timestamps();
        });
        Schema::create('promotion_rules', function (Blueprint $table) {
            $table->uuid('promotion_rules_id')->primary();
            $table->uuid('promotion_id');
            $table->string('rule_type'); // 'product', 'category', 'total_amount', 'quantity'
            $table->uuid('product_id')->nullable();
            $table->uuid('category_id')->nullable();
            $table->decimal('min_amount', 12, 2)->nullable();
            $table->integer('min_quantity')->nullable();
            $table->timestamps();
        });
        Schema::create('stores', function (Blueprint $table) {
            $table->uuid('store_id')->primary();
            $table->string('storeName', 100);
            $table->text('address');
            $table->string('phone', 20);
            $table->uuid('user_id')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('warehouses', function (Blueprint $table) {
            $table->uuid('warehouse_id')->primary();
            $table->string('warehouseName', 100);
            $table->string('location', 255);
            $table->uuid('user_id')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('inventory', function (Blueprint $table) {
            $table->uuid('inventory_id')->primary();
            $table->uuid('product_id');
            $table->uuid('store_id');
            $table->unique(['product_id', 'store_id']);
            $table->decimal('quantity', 10, 2)->default(0);
            $table->date('lastRestockDate')->nullable();
            $table->date('nextRestockDate')->nullable();
            $table->string('aisle', 10)->nullable();
            $table->string('rack', 10)->nullable();
            $table->string('bin', 10)->nullable();
            $table->date('lastCountDate')->nullable();
            $table->decimal('lastCountQuantity', 10, 2)->nullable();
            $table->timestamps();
        });
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('movement_id')->primary();
            $table->uuid('product_id');
            $table->enum('movementType', ['Restock', 'Sale', 'Transfer', 'Adjustment', 'Return', 'Damage']);
            $table->decimal('quantity', 10, 2);
            $table->dateTime('movementDate');
            $table->uuid('store_id');
            $table->string('sourceDocument', 20)->nullable();
            $table->uuid('documentReference_id')->nullable();
            $table->string('documentReference_type')->nullable();
            $table->index(['documentReference_type', 'documentReference_id'], 'doc_ref_idx');
           
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        Schema::create('expiry_management', function (Blueprint $table) {
            $table->uuid('expiry_id')->primary();
            $table->uuid('product_id');
            $table->uuid('store_id');
            $table->string('batchNumber', 50);
            $table->date('expiryDate');
            $table->decimal('quantityInBatch', 10, 2);
            $table->timestamps();
        });
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->uuid('po_id')->primary();
            $table->string('poNumber')->unique();
            $table->uuid('supplier_id');
            $table->date('orderDate');
            $table->date('expectedDeliveryDate');
            $table->enum('status', ['Draft', 'Pending', 'Partially Received', 'Received', 'Cancelled']);
            $table->decimal('totalAmount', 10, 2);
            $table->uuid('createdBy');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        Schema::create('purchase_order_details', function (Blueprint $table) {
            $table->uuid('poDetail_id')->primary();
            $table->uuid('po_id');
            $table->uuid('product_id');
            $table->decimal('orderQuantity', 10, 2);
            $table->decimal('pricePerUnit', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('expectedDeliveryQuantity', 10, 2);
            $table->decimal('receivedQuantity', 10, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->uuid('transfer_id')->primary();
            $table->uuid('product_id');
            $table->decimal('quantity', 10, 2);
            $table->uuid('fromStore_id');
            $table->uuid('toStore_id');
            $table->dateTime('transferDate');
            $table->uuid('handledBy');
            $table->enum('status', ['Pending', 'In Transit', 'Completed', 'Cancelled']);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        Schema::create('product_logistics', function (Blueprint $table) {
            $table->uuid('logistics_id')->primary();
            $table->uuid('product_id');
            $table->uuid('warehouse_id');
            $table->string('shelfLocation', 50);
            $table->decimal('reorderLevel', 10, 2);
            $table->timestamps();
        });
        Schema::create('stock_alerts', function (Blueprint $table) {
            $table->uuid('alert_id')->primary();
            $table->uuid('product_id');
            $table->enum('alertType', ['Low Stock', 'Overstock', 'Expiry Warning']);
            $table->dateTime('alertDate');
            $table->uuid('store_id');
            $table->boolean('isResolved')->default(false);
            $table->dateTime('resolvedDate')->nullable();
            $table->uuid('resolvedBy')->nullable();
            $table->timestamps();
        });
        Schema::create('inventory_counts', function (Blueprint $table) {
            $table->uuid('count_id')->primary();
            $table->uuid('store_id');
            $table->dateTime('startDate')->nullable();
            $table->dateTime('endDate')->nullable();
            $table->enum('status', ['Pending', 'In Progress', 'Completed', 'Approved']);
            $table->uuid('createdBy');
            $table->uuid('approvedBy')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        Schema::create('inventory_count_items', function (Blueprint $table) {
            $table->uuid('countItem_id')->primary();
            $table->uuid('count_id');
            $table->uuid('product_id');
            $table->decimal('unit_cost', 12, 4);
            $table->decimal('expectedQuantity', 10, 2);
            $table->decimal('countedQuantity', 10, 2);
            $table->decimal('variance', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        Schema::create('integration_logs', function (Blueprint $table) {
            $table->uuid('integrationLog_id')->primary();
            $table->string('processName', 100);
            $table->enum('status', ['Success', 'Failed', 'Partial']);
            $table->text('message');
            $table->dateTime('logDate');
            $table->text('details')->nullable();
            $table->timestamps();
        });
        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('department_id')->primary();
            $table->string('departmentName', 100);
            $table->enum('status', ['Active', 'Inactive'])->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('employee_id')->primary();
            $table->string('fullName', 100);
            $table->string('position', 100);
            $table->uuid('department_id');
            $table->date('hireDate');
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->unique();
            $table->decimal('salary', 10, 2)->nullable();
            $table->enum('status', ['Contract','Permanent','Probation','Intern','Freelance', 'On Leave','Resigned','Retired', 'Suspended', 'Terminated']);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('shift_schedules', function (Blueprint $table) {
            $table->uuid('schedule_id')->primary();
            $table->uuid('user_id');
            $table->uuid('store_id');
            $table->date('workDate');
            $table->time('shiftStartTime');
            $table->time('shiftEndTime');
            $table->integer('breakDuration')->comment('In minutes')->default(0);
            $table->timestamps();
        });
        Schema::create('attendance', function (Blueprint $table) {
            $table->uuid('attendance_id')->primary();
            $table->uuid('user_id');
            $table->dateTime('logInTime');
            $table->dateTime('logOutTime')->nullable();
            $table->date('workDate');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
        Schema::create('price_history', function (Blueprint $table) {
            $table->uuid('priceHistory_id')->primary();
            $table->uuid('product_id');
            $table->enum('priceType', ['COGS', 'Retail', 'Bronze', 'Silver', 'Gold']);
            $table->decimal('oldPrice', 10, 2);
            $table->decimal('newPrice', 10, 2);
            $table->dateTime('changeDate');
            $table->uuid('updatedBy');
            $table->timestamps();
        });
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->uuid('audit_id')->primary();
            $table->string('entityName', 50);
            $table->nullableMorphs('entity'); // Menggunakan polymorphic relationship
            $table->string('actionType', 10)->comment('CREATE, UPDATE, DELETE');
            $table->string('fieldName', 50)->nullable();
            $table->text('oldValue')->nullable();
            $table->text('newValue')->nullable();
            $table->uuid('updatedBy');
            $table->dateTime('updateDate');
            $table->string('ipAddress', 45)->nullable();
            $table->timestamps();
        });
        Schema::create('loyalty_redeems', function (Blueprint $table) {
            $table->uuid('redeem_id')->primary();
            $table->uuid('customer_id');
            $table->integer('pointsRedeemed');
            $table->dateTime('transactionDate');
            $table->string('redeemedFor', 255);
            $table->uuid('sale_id')->nullable();
            $table->timestamps();
        });
        Schema::create('customer_feedback', function (Blueprint $table) {
            $table->uuid('feedback_id')->primary();
            $table->uuid('product_id');
            $table->uuid('customer_id');
            $table->tinyInteger('rating')->unsigned()->comment('1-5');
            $table->text('comments')->nullable();
            $table->dateTime('feedbackDate');
            $table->boolean('isPublished')->default(false);
            $table->timestamps();
        });
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('notification_id')->primary();
            $table->morphs('recipient');
            $table->string('title', 100);
            $table->text('message');
            $table->enum('status', ['Read', 'Unread', 'Archived'])->default('Unread');
            $table->dateTime('createdDate');
            $table->timestamps();
        });
        Schema::create('returns_supplier', function (Blueprint $table) {
            $table->uuid('returnSupplier_id')->primary();
            $table->uuid('product_id');
            $table->uuid('supplier_id');
            $table->decimal('quantityReturned', 10, 2);
            $table->dateTime('returnDate');
            $table->text('reason');
            $table->uuid('po_id')->nullable();
            $table->uuid('processedBy');
            $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Completed']);
            $table->timestamps();
        });





        Schema::table('users', function (Blueprint $table) {
            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')->onDelete('cascade');
            $table->foreign('employee_id')
                ->references('employee_id')
                ->on('employees')->onDelete('cascade');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('user_sessions', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->foreign('createdBy')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            $table->foreign('updatedBy')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->foreign('parentCategory_id')->references('category_id')->on('categories');
            $table->foreign('createdBy')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            $table->foreign('updatedBy')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });

        Schema::table('master_products', function (Blueprint $table) {
            $table->foreign('brand_id')->references('brand_id')->on('brands')->onDelete('set null');
            $table->foreign('category_id')->references('category_id')->on('categories')->onDelete('set null');
            $table->foreign('uom_id')->references('uom_id')->on('uoms');
            $table->foreign('taxStatus_id')->references('taxStatus_id')->on('tax_statuses');
            // $table->foreign('status_id')->references('status_id')->on('statuses');
            $table->foreign('createdBy')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updatedBy')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('product_bundles', function (Blueprint $table) {
            $table->foreign('parent_product_id')->references('product_id')->on('master_products');
            $table->foreign('component_product_id')->references('product_id')->on('master_products');
        });


        Schema::table('product_dimensions', function (Blueprint $table) {
            $table->foreign('product_id')->references('product_id')->on('master_products')->onDelete('cascade');
            $table->foreign('uom_id')->references('uom_id')->on('uoms');
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->foreign('product_id')->references('product_id')->on('master_products');

        });

        Schema::table('barcodes', function (Blueprint $table) {
            $table->foreign('product_id')->references('product_id')->on('master_products');
            $table->unique(['product_id', 'barcode']);

        });

        Schema::table('uom_converts', function (Blueprint $table) {
            $table->foreign('product_id')->references('product_id')->on('master_products');
            $table->foreign('fromUom_id')->references('uom_id')->on('uoms');
            $table->foreign('toUom_id')->references('uom_id')->on('uoms');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->foreign('parentSupplier_id')->references('parentSupplier_id')->on('parent_suppliers')->onDelete('cascade');
            $table->foreign('taxSupplierStatus_id')->references('taxSupplierStatus_id')->on('tax_supplier_statuses');
        });

        Schema::table('product_suppliers', function (Blueprint $table) {
            $table->foreign('product_id')->references('product_id')->on('master_products');
            $table->foreign('supplier_id')->references('supplier_id')->on('suppliers');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreign('store_id')->references('store_id')->on('stores');
            $table->foreign('customer_id')->references('customer_id')->on('customers');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('paymentMethod_id')->references('paymentMethod_id')->on('payment_methods');
        });

        Schema::table('sales_details', function (Blueprint $table) {
            $table->foreign('sale_id')->references('sale_id')->on('sales');
            $table->foreign('product_id')->references('product_id')->on('master_products');
            $table->foreign('uom_id')->references('uom_id')->on('uoms');
            $table->foreign('promotion_id')
                ->references('promotion_id')
                ->on('promotions')
                ->onDelete('cascade');
        });

        Schema::table('applied_promotions', function (Blueprint $table) {
            $table->foreign('sale_id')->references('sale_id')->on('sales')->onDelete('cascade');
            $table->foreign('promotion_id')
                ->references('promotion_id')
                ->on('promotions')
                ->onDelete('cascade');
        });


        Schema::table('sales_payments', function (Blueprint $table) {
            $table->foreign('sale_id')->references('sale_id')->on('sales');
            $table->foreign('paymentMethod_id')->references('paymentMethod_id')->on('payment_methods');
        });

        Schema::table('returns', function (Blueprint $table) {
            $table->foreign('sale_id')->references('sale_id')->on('sales');
            $table->foreign('uom_id')->references('uom_id')->on('uoms');
            $table->foreign('product_id')->references('product_id')->on('master_products');
            $table->foreign('user_id')->references('id')->on('users');
        });


        Schema::table('promotions', function (Blueprint $table) {
            $table->foreign('promotion_type_id')->references('promotion_type_id')->on('promotion_types');

        });

        Schema::table('promotion_rules', function (Blueprint $table) {
            $table->foreign('promotion_id')
                ->references('promotion_id')
                ->on('promotions')
                ->onDelete('cascade');

            // product_id - SET NULL
            $table->foreign('product_id')
                ->references('product_id')
                ->on('master_products')
                ->onDelete('set null');

            // category_id - SET NULL
            $table->foreign('category_id')
                ->references('category_id')
                ->on('categories')
                ->onDelete('set null');

        });


        Schema::table('stores', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::table('inventory', function (Blueprint $table) {
            $table->foreign('product_id')->references('product_id')->on('master_products');
            $table->foreign('store_id')->references('store_id')->on('stores');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreign('product_id')->references('product_id')->on('master_products');
            $table->foreign('store_id')->references('store_id')->on('stores');
        });

        Schema::table('expiry_management', function (Blueprint $table) {
            $table->foreign('product_id')->references('product_id')->on('master_products');
            $table->foreign('store_id')->references('store_id')->on('stores');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreign('supplier_id')->references('supplier_id')->on('suppliers');
            $table->foreign('createdBy')->references('id')->on('users');
        });

        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->foreign('po_id')->references('po_id')->on('purchase_orders');
            $table->foreign('product_id')->references('product_id')->on('master_products');
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->foreign('product_id')->references('product_id')->on('master_products');
            $table->foreign('fromStore_id')->references('store_id')->on('stores');
            $table->foreign('toStore_id')->references('store_id')->on('stores');
            $table->foreign('handledBy')->references('id')->on('users');
        });

        Schema::table('product_logistics', function (Blueprint $table) {
            $table->foreign('product_id')->references('product_id')->on('master_products');
            $table->foreign('warehouse_id')->references('warehouse_id')->on('warehouses');
        });

        Schema::table('stock_alerts', function (Blueprint $table) {
            $table->foreign('product_id')->references('product_id')->on('master_products');
            $table->foreign('store_id')->references('store_id')->on('stores');
            $table->foreign('resolvedBy')->references('id')->on('users');
        });

        Schema::table('inventory_counts', function (Blueprint $table) {
            $table->foreign('store_id')->references('store_id')->on('stores');
            $table->foreign('createdBy')->references('id')->on('users');
            $table->foreign('approvedBy')->references('id')->on('users');
        });

        Schema::table('inventory_count_items', function (Blueprint $table) {
            $table->foreign('count_id')->references('count_id')->on('inventory_counts');
            $table->foreign('product_id')->references('product_id')->on('master_products');
        });
        Schema::table('employees', function (Blueprint $table) {
            $table->foreign('department_id')->references('department_id')->on('departments');

        });

        Schema::table('shift_schedules', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('store_id')->references('store_id')->on('stores');
        });

        Schema::table('attendance', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::table('price_history', function (Blueprint $table) {
            $table->foreign('product_id')->references('product_id')->on('master_products');
            $table->foreign('updatedBy')->references('id')->on('users');

        });

        Schema::table('audit_trails', function (Blueprint $table) {
            $table->foreign('updatedBy')->references('id')->on('users');
        });

        Schema::table('loyalty_redeems', function (Blueprint $table) {
            $table->foreign('customer_id')->references('customer_id')->on('customers');
            $table->foreign('sale_id')->references('sale_id')->on('sales');
        });

        Schema::table('customer_feedback', function (Blueprint $table) {
            $table->foreign('product_id')->references('product_id')->on('master_products');
            $table->foreign('customer_id')->references('customer_id')->on('customers');
        });


        Schema::table('returns_supplier', function (Blueprint $table) {
            $table->foreign('product_id')->references('product_id')->on('master_products');
            $table->foreign('supplier_id')->references('supplier_id')->on('suppliers');
            $table->foreign('po_id')->references('po_id')->on('purchase_orders');
            $table->foreign('processedBy')->references('id')->on('users');
        });

    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('all2_tables');
    }
};

