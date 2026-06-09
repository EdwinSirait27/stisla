<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fingerprint_recap_archives', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id');
            $table->string('employee_name');
            $table->string('store_name')->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('total_hari_kerja')->default(0);
            $table->integer('total_hari_telat')->default(0);
            $table->text('remarks')->nullable();
            $table->uuid('archived_by')->nullable();
            $table->timestamps();

            $table->index(['store_name', 'period_start', 'period_end'], 'fra_store_period_idx');
            $table->index(['employee_id', 'period_start', 'period_end'], 'fra_emp_period_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fingerprint_recap_archives');
    }
};