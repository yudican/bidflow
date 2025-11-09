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
        Schema::create('product_variant_bundlings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id');
            $table->foreignId('product_variant_id');
            $table->integer('product_qty');
            $table->string('sku');
            $table->integer('is_master');
            $table->foreignId('package_id');
            $table->foreignId('company_id');
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
        Schema::dropIfExists('product_variant_bundlings');
    }
};
