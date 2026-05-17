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
         Schema::create('company_document_configs', function (Blueprint $table) {
             $table->uuid('id')->primary();
             $table->uuid('company_id')->nullable();
             $table->uuid('document_type_id')->nullable();
             $table->string('bank_name')->nullable();
             $table->string('savings_type')->nullable();
             $table->string('promo_code')->nullable();
             $table->string('community_code')->nullable();
             $table->string('service_name')->nullable();
             $table->string('pic_name')->nullable();
             $table->string('pic_email')->nullable();
             $table->string('pic_name_2')->nullable();
             $table->string('pic_email_2')->nullable();
             $table->boolean('is_active')->default(true);
             $table->timestamps();
             $table->foreign('company_id')
                 ->references('id')->on('company_tables')
                 ->onDelete('cascade');
             $table->foreign('document_type_id')
                 ->references('id')->on('document_types')
                 ->onDelete('cascade');
             });
     }
     /**
      * Reverse the migrations.
      */
     public function down(): void
     {
         Schema::dropIfExists('company_document_configs');
     }
 };