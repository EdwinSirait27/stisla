<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Snapshot of what a Surat Tugas (ST) document changed on an employee's
     * department/store/position assignment, so the change can be reverted
     * once the document's expired_date has passed.
     */
    public function up(): void
    {
        Schema::create('document_temporary_assignments', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->uuid('id')->primary();
            $table->uuid('document_id');
            $table->enum('type', ['department', 'store', 'position']);

            // Assignment before the ST took effect (null = employee had none).
            $table->uuid('previous_pivot_id')->nullable();
            $table->uuid('previous_reference_id')->nullable();
            $table->string('previous_name')->nullable();

            // Assignment the ST put in place.
            $table->uuid('new_pivot_id');
            $table->uuid('new_reference_id');
            $table->string('new_name');

            // Whether new_pivot_id was created solely for this ST (and must
            // be deleted on revert) or already existed (only its is_primary
            // flag is flipped back on revert).
            $table->boolean('was_created')->default(false);

            $table->timestamp('reverted_at')->nullable();
            $table->timestamps();

            $table->foreign('document_id')
                ->references('id')->on('documents')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_temporary_assignments');
    }
};
