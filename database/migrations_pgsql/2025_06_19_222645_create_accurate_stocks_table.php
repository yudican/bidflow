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
        Schema::connection('pgsql')->create('accurate_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('accurate_id')->unique(); // dari "id"
            $table->string('item_no')->nullable();               // dari "no"
            $table->string('name')->nullable();                  // dari "name"
            $table->decimal('quantity', 20, 6)->nullable();      // dari "quantity"
            $table->string('quantity_in_all_unit')->nullable();  // dari "quantityInAllUnit" (string!)
            $table->string('upc_no')->nullable();                // dari "upcNo"
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
        Schema::table('accurate_stocks', function (Blueprint $table) {
            //
        });
    }
};
