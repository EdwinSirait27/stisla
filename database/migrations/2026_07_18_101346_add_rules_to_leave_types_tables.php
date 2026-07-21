<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_types_tables', function (Blueprint $table) {
            // SAKLAR: cuti khusus (jalur guard generik) atau biasa (jalur annual)?
            $table->boolean('is_special')->default(false)->after('default_balance');

            // all / male / female
            $table->string('gender_rule', 10)->default('all')->after('is_special');

            // durasi DIKUNCI (90 maternity, 2 pendamping). null = bebas.
            $table->unsignedSmallInteger('fixed_days')->nullable()->after('gender_rule');

            // batas atas durasi, dipakai hanya jika fixed_days null. null = tanpa batas.
            $table->unsignedSmallInteger('max_days')->nullable()->after('fixed_days');

            // wajib lampirkan bukti?
            $table->boolean('require_attachment')->default(false)->after('max_days');

            // wajib sudah menikah?
            $table->boolean('require_married')->default(false)->after('require_attachment');

            // status kepegawaian yang boleh, csv mis. "PKWT". null = semua status.
            $table->string('allowed_status', 100)->nullable()->after('require_married');

            // day_type di grid roster: 'Leave' / 'Cuti Melahirkan' (dibatasi di CRUD).
            $table->string('roster_day_type', 50)->default('Leave')->after('allowed_status');

            // jenis cuti masih dipakai / muncul di dropdown?
            $table->boolean('is_active')->default(true)->after('roster_day_type');
        });
    }

    public function down(): void
    {
        Schema::table('leave_types_tables', function (Blueprint $table) {
            $table->dropColumn([
                'is_special',
                'gender_rule',
                'fixed_days',
                'max_days',
                'require_attachment',
                'require_married',
                'allowed_status',
                'roster_day_type',
                'is_active',
            ]);
        });
    }
};