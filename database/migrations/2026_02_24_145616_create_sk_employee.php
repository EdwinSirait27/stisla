<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sk_employee', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sk_type_id')->nullable();
            $table->foreign('sk_type_id')
                ->references('id')
                ->on('sk_type')
                ->onDelete('cascade');
            $table->uuid('sk_template_id')->nullable();
            $table->foreign('sk_template_id')
                ->references('id')
                ->on('sk_template')
                ->onDelete('cascade');
            $table->uuid('store_id')->nullable();
            $table->foreign('store_id')
                ->references('id')
                ->on('stores_tables')
                ->nullOnDelete();
            $table->string('sk_number');
            $table->string('title');
            $table->date('issued_date')->nullable();
            $table->date('effective_date');
            $table->longText('header_text')->nullable();
            $table->longText('consideration')->nullable();
            $table->longText('legal_basis')->nullable();
            $table->longText('decision_text')->nullable();
            $table->longText('footer_text')->nullable();
            $table->string('status')->default('draft');
            $table->uuid('approver_1')->nullable();
            $table->foreign('approver_1')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->uuid('approver_2')->nullable();
            $table->foreign('approver_2')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->timestamps();
            $table->unique(['sk_number', 'store_id']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sk_employee');
    }
};
