<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('pid')->nullable();
            $table->foreignId('category_id');
            $table->foreignId('brand_id');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->float('agent_price')->nullable();
            $table->float('customer_price')->nullable();
            $table->float('discount_price')->nullable();
            $table->float('discount_percent')->nullable();
            $table->integer('stock')->default(0)->nullable();
            $table->float('weight')->nullable();
            $table->char('is_varian', 1)->default(0)->nullable();
            $table->char('status', 1)->default(1)->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
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
        Schema::dropIfExists('products');
    }
}
