<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductConvertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_converts', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('convert_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->dateTime('convert_date')->nullable()->default(now());
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
        Schema::dropIfExists('product_converts');
    }
}
