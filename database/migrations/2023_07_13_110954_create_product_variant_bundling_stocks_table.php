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
        Schema::create('product_variant_bundling_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_bundling_id');
            $table->integer('qty');
            $table->integer('stock_off_market');
            $table->foreignId('warehouse_id');
            $table->foreignId('company_id');
            $table->text('description');
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
        Schema::dropIfExists('product_variant_bundling_stocks');
    }
};
