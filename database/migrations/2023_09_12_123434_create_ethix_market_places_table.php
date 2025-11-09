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

        Schema::create('ethix_market_places', function (Blueprint $table) {
            $table->id();
            $table->string('channel_origin')->nullable();
            $table->string('shop_name')->nullable();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('full_address')->nullable();
            $table->string('shipping_price')->nullable();
            $table->string('receipent_name')->nullable();
            $table->string('receipent_phone')->nullable();
            $table->string('receipent_address')->nullable();
            $table->string('total_discount')->nullable();
            $table->string('sku')->nullable();
            $table->string('qty')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('status')->nullable();
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
        Schema::dropIfExists('ethix_market_places');
    }
};
