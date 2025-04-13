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
        Schema::create('shift_swaps_tables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('requester_id')->nullable(); // Karyawan yang meminta tukar shift
            $table->uuid('receiver_id')->nullable(); // Karyawan yang diajak tukar shift
            $table->uuid('original_shift_id')->nullable(); // Shift asal (requester)
            $table->uuid('new_shift_id')->nullable(); // Shift tujuan (receiver)
            $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Canceled'])->default('Pending')->nullable();
            $table->text('reason')->nullable(); // Alasan permintaan
            $table->text('rejection_reason')->nullable();
            $table->uuid('approved_by')->nullable(); // HR/Manager yang menyetujui
            $table->datetime('approved_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('requester_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('receiver_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('original_shift_id')
                ->references('id')
                ->on('employee_shifts_tables')
                ->onDelete('cascade');

            $table->foreign('new_shift_id')
                ->references('id')
                ->on('employee_shifts_tables')
                ->onDelete('cascade');

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shift_swaps_tables');
    }
};
