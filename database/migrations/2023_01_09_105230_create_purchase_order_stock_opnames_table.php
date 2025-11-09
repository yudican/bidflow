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
        Schema::create('purchase_order_stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->nullable();
            $table->foreignId('product_id')->nullable();
            $table->date('stock_opname_date')->nullable();
            $table->integer('stock_opname_qty')->nullable();
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
        Schema::dropIfExists('purchase_order_stock_opnames');
    }
};
