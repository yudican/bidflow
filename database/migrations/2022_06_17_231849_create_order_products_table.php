<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_products', function (Blueprint $table) {
            $table->id();
            $table->string('uid_lead')->nullable();
            $table->foreignId('product_id')->nullable();
            $table->float('price')->nullable();
            $table->integer('qty')->nullable();
            $table->float('final_price')->nullable();
            $table->string('status')->nullable();
            $table->foreignUuid('user_created')->nullable();
            $table->foreignUuid('user_updated')->nullable();
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
        Schema::dropIfExists('order_products');
    }
}
