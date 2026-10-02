<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A Surat Tugas can now assign an employee to more than one store (etc.)
     * at once — only one of those rows represents the primary swap that
     * should update the employee's denormalized column on revert; the rest
     * are plain additions. This flag disambiguates the two.
     */
    public function up(): void
    {
        Schema::table('document_temporary_assignments', function (Blueprint $table) {
            $table->boolean('is_primary_change')->default(true)->after('was_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_temporary_assignments', function (Blueprint $table) {
            $table->dropColumn('is_primary_change');
        });
    }
};
