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
        Schema::create('attendance_logs', function (Blueprint $table) {
             $table->uuid('id')->primary();
            $table->uuid('employee_id');
            $table->uuid('store_id')->nullable(); // null kalau flexible/field
            $table->enum('type', ['checkin', 'checkout']);

            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('distance_from_store', 8, 2)->nullable(); // dalam meter
            $table->boolean('is_within_geofence')->default(false);
            $table->boolean('is_mock_location')->default(false); // flag dari device

            $table->string('photo_path')->nullable(); // path di MinIO
            $table->decimal('liveness_score', 5, 2)->nullable(); // hasil liveness check
            $table->boolean('liveness_passed')->default(false);

            $table->string('device_id');
            $table->enum('status', ['valid', 'flagged', 'rejected'])->default('valid');
            $table->text('flag_reason')->nullable(); // kenapa di-flag (mock location, luar radius, dll)

            $table->timestamp('logged_at');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees_tables')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('stores_tables')->onDelete('set null');
            $table->index(['employee_id', 'logged_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
