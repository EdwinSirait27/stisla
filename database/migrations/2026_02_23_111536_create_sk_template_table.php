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
        Schema::create('sk_template', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('template_name');
            $table->uuid('sk_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreign('sk_type_id')
            ->references('id')
            ->on('sk_type')->onDelete('cascade');
            $table->uuid('company_id')->nullable()->constrained()->nullOnDelete();
 $table->foreign('company_id')
            ->references('id')
            ->on('company_tables')->onDelete('cascade');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sk_template');
    }
};



