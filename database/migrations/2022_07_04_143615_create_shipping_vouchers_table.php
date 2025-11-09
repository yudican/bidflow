<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShippingVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_vouchers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('logistic_rate_id');
            $table->string('shipping_price_discount');
            $table->dateTime('shipping_price_discount_start');
            $table->dateTime('shipping_price_discount_end');
            $table->boolean('shipping_price_discount_status')->default(true);

            // create a foreign key for logistic_rate_id
            $table->foreign('logistic_rate_id')->references('id')->on('logistic_rates')->onDelete('cascade');
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
        Schema::dropIfExists('shipping_vouchers');
    }
}
