<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::create('roster_ph_carryovers', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->uuid('employee_id');
        $table->date('ph_date');          // tanggal PH asal (mis. 2026-02-17)
        $table->string('ph_name');        // nama PH (remark), mis. "Tahun Baru Imlek"
        $table->date('expired_at');       // akhir periode +2 (mis. 2026-04-25)
        $table->string('status')->default('available'); // available | used
        $table->date('used_date')->nullable();          // tanggal hari pengganti dipakai
        $table->timestamps();

        $table->index(['employee_id', 'status']);
    });
}

public function down(): void
{
    Schema::dropIfExists('roster_ph_carryovers');
}
};
