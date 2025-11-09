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
        Schema::create('barcode_masters', function (Blueprint $table) {
            $table->id();
            $table->string('product_id')->nullable();
            $table->string('location')->nullable();
            $table->integer('moq')->nullable();
            $table->string('batch_id')->nullable();
            $table->string('tipe_po')->nullable();
            $table->integer('qty')->nullable();
            $table->string('parent')->nullable();
            $table->string('prefixs')->nullable();
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
        Schema::dropIfExists('barcode_masters');
    }
};
