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
        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->id();
            $table->string('uid_retur')->nullable();
            $table->foreignId('product_id')->nullable();
            $table->float('price')->nullable();
            $table->integer('qty')->nullable();
            $table->string('status')->nullable();
            $table->foreignId('tax_id')->nullable();
            $table->foreignId('discount_id')->nullable();
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
        Schema::dropIfExists('sales_return_items');
    }
};
