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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->text('primary_address')->nullable();
            $table->text('purchase_address')->nullable();
            $table->text('ship_address')->nullable();
            $table->text('remit_address')->nullable();
            $table->string('contact')->nullable();
            $table->string('address_1')->nullable();
            $table->string('address_2')->nullable();
            $table->string('address_3')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('country')->nullable();
            $table->string('phone_1')->nullable();
            $table->string('phone_2')->nullable();
            $table->string('phone_3')->nullable();
            $table->string('fax')->nullable();
            $table->string('ups_zone')->nullable();
            $table->string('shipping_method')->nullable();
            $table->string('tax_schedule')->nullable();
            $table->string('tax_id')->nullable();
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
        Schema::dropIfExists('vendors');
    }
};
