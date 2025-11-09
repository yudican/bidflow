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
        Schema::create('mp_order_lists', function (Blueprint $table) {
            $table->id();
            $table->string('trx_id')->nullable();
            $table->string('customer_code')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('channel')->nullable();
            $table->string('store')->nullable();
            $table->string('amount')->nullable();
            $table->string('shipping_fee')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('warehouse')->nullable();
            $table->string('mp_fee')->nullable();
            $table->string('discount')->nullable();
            $table->string('trx_date')->nullable();
            $table->string('courir')->nullable();
            $table->string('awb')->nullable();
            $table->string('status')->nullable();
            $table->string('shipping_status')->nullable();
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
        Schema::dropIfExists('mp_order_lists');
    }
};
