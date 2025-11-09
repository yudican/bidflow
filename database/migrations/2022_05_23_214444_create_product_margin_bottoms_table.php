<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductMarginBottomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_margin_bottoms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable();
            $table->string('basic_price', 15)->nullable();
            $table->foreignUuid('role_id')->nullable();
            $table->string('margin', 15)->nullable();
            $table->string('description')->nullable();
            $table->foreignUuid('user_created')->nullable();
            $table->foreignUuid('user_updated')->nullable();
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
        Schema::dropIfExists('product_margin_bottoms');
    }
}
