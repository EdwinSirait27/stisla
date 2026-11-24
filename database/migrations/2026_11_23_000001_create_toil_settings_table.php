<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pengaturan TOIL/lembur yang sebelumnya hardcode di controller & model.
 * Satu baris saja (id = 1). Nilai awal = nilai hardcode lama, jadi perilaku tidak berubah.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('toil_settings', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedTinyInteger('toil_expiry_months')->default(3);          // masa berlaku saldo TOIL
            $table->unsignedTinyInteger('cash_expiry_months')->default(1);          // masa berlaku saldo Cash (sampai dibayar payroll)
            $table->unsignedTinyInteger('expiring_soon_days')->default(14);         // tanda "segera hangus" di aplikasi mobile
            $table->decimal('hours_per_day', 4, 2)->default(8);                     // 1 hari libur TOIL = sekian jam
            $table->unsignedSmallInteger('max_leave_range_days')->default(31);      // rentang libur TOIL sekali ajuan
            $table->decimal('min_overtime_hours', 4, 2)->default(0.5);              // jam lembur minimal per hari
            $table->decimal('max_overtime_hours', 4, 2)->default(24);               // jam lembur maksimal per hari
            $table->unsignedSmallInteger('checkin_early_minutes')->default(60);     // check-in lembur mobile paling cepat sebelum jam mulai
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
        });

        DB::table('toil_settings')->insert(['id' => 1, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::dropIfExists('toil_settings');
    }
};
