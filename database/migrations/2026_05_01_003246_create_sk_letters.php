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
        Schema::create('sk_letters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sk_type_id')->nullable();
            $table->uuid('company_id')->nullable();
            $table->uuid('structure_id')->nullable();
            $table->uuid('approver_1')->nullable();
            $table->uuid('approver_2')->nullable();
            $table->uuid('approver_3')->nullable();
            $table->timestamp('approver_1_at')->nullable();
            $table->timestamp('approver_2_at')->nullable();
            $table->timestamp('approver_3_at')->nullable();
            $table->date('effective_date')->nullable();
            $table->date('inactive_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['Draft', 'Cancelled', 'Approved HR', 'Approved Director', 'Approved Managing Director'])->default('Draft');
            $table->timestamps();
            $table->foreign('sk_type_id')
                ->references('id')
                ->on('sk_type')
                ->nullOnDelete();
            $table->foreign('company_id')
                ->references('id')
                ->on('company_tables')
                ->nullOnDelete();
            $table->foreign('approver_1')
                ->references('id')
                ->on('employees_tables')
                ->nullOnDelete();
            $table->foreign('approver_2')
                ->references('id')
                ->on('employees_tables')
                ->nullOnDelete();
            $table->foreign('approver_3')
                ->references('id')
                ->on('employees_tables')
                ->nullOnDelete();
            $table->foreign('structure_id')
                ->references('id')
                ->on('structures_tables')
                ->nullOnDelete();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sk_letters');
    }
};
