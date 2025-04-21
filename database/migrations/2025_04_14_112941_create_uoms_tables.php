<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uoms_tables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('uom_code')->unique()->nullable(); 
            $table->enum('uom', ['Each', 'Kg', 'Box', 'Liter', 'Pack', 'Meter'])->nullable(); // Add all needed units
            $table->decimal('conversion_factor', 10, 4)->nullable()->comment('Base conversion factor to standard unit');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uoms_tables');
    }
};
