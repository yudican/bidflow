<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderManualsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_manuals', function (Blueprint $table) {
            $table->id();
            $table->string('uid_lead')->nullable();
            $table->string('title')->nullable();
            $table->foreignUuid('contact')->nullable();
            $table->foreignUuid('sales')->nullable();
            $table->string('customer_need')->nullable();
            $table->foreignUuid('user_created')->nullable();
            $table->foreignUuid('user_updated')->nullable();
            $table->foreignId('payment_term')->nullable();
            $table->foreignId('brand_id')->nullable();
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
        Schema::dropIfExists('order_manuals');
    }
}
