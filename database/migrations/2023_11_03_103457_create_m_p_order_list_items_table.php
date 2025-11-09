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
        Schema::create('mp_order_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mp_order_list_id')->nullable();
            $table->string('sku')->nullable();
            $table->string('product_name')->nullable();
            $table->string('price')->nullable();
            $table->string('final_price')->nullable();
            $table->integer('qty')->nullable()->default(0);
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
        Schema::dropIfExists('m_p_order_list_items');
    }
};
