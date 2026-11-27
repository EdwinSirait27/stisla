<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Approval bertingkat untuk kontrak (sama seperti SK): Draft → diajukan HeadHR (approver 1) →
 * approver 2 → approver 3 (opsional) → Active. Bisa dikembalikan ke Draft atau dibatalkan.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE contracts MODIFY contract_status ENUM('Draft','Pending','Active','Expired','Terminated','Cancelled') NOT NULL DEFAULT 'Draft'");

        Schema::table('contracts', function (Blueprint $table) {
            $table->char('approver_1', 36)->nullable()->after('created_by');
            $table->char('approver_2', 36)->nullable()->after('approver_1');
            $table->char('approver_3', 36)->nullable()->after('approver_2');
            $table->timestamp('approver_1_at')->nullable()->after('approver_3');
            $table->timestamp('approver_2_at')->nullable()->after('approver_1_at');
            $table->timestamp('approver_3_at')->nullable()->after('approver_2_at');
            $table->string('approver_1_signature')->nullable()->after('approver_3_at');
            $table->string('approver_2_signature')->nullable()->after('approver_1_signature');
            $table->string('approver_3_signature')->nullable()->after('approver_2_signature');
            $table->text('returned_note')->nullable()->after('approver_3_signature');
            $table->char('returned_by', 36)->nullable()->after('returned_note');
            $table->timestamp('returned_at')->nullable()->after('returned_by');
            $table->timestamp('approved_at')->nullable()->after('returned_at');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['approver_1', 'approver_2', 'approver_3', 'approver_1_at', 'approver_2_at', 'approver_3_at',
                'approver_1_signature', 'approver_2_signature', 'approver_3_signature', 'returned_note', 'returned_by', 'returned_at', 'approved_at']);
        });
        DB::statement("UPDATE contracts SET contract_status = 'Terminated' WHERE contract_status IN ('Draft','Pending','Cancelled')");
        DB::statement("ALTER TABLE contracts MODIFY contract_status ENUM('Active','Expired','Terminated') NULL");
    }
};
