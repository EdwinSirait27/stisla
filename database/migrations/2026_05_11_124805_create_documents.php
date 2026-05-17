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
         Schema::create('documents', function (Blueprint $table) {
             $table->uuid('id')->primary();
             $table->uuid('company_document_config_id')->nullable();
             $table->uuid('employee_id')->nullable();
             $table->uuid('issued_by')->nullable();
             $table->date('issued_date');
             $table->enum('status', ['draft', 'issued', 'revoked'])->default('draft');
             $table->string('file_path', 255)->nullable();
             $table->string('document_number');
               $table->foreign('company_document_config_id')
                 ->references('id')->on('company_document_configs')
                 ->onDelete('cascade');
                   $table->foreign('employee_id')
                 ->references('id')->on('employees_tables')
                 ->onDelete('cascade');
                   $table->foreign('issued_by')
                 ->references('id')->on('employees_tables')
                 ->onDelete('cascade');
         });
     }
     /**
      * Reverse the migrations.
      */
     public function down(): void
     {
         Schema::dropIfExists('documents');
     }
 };