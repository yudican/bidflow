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
        Schema::create('stock_allocation_histories', function (Blueprint $table) {
            $table->id();
            $table->string('uid_inventory');
            $table->foreignId('product_id');
            $table->foreignId('from_warehouse_id')->nullable();
            $table->foreignId('to_warehouse_id')->nullable();
            $table->date('transfer_date')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('sku')->nullable();
            $table->string('u_of_m')->nullable();
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
        Schema::dropIfExists('stock_allocation_histories');
    }
};
