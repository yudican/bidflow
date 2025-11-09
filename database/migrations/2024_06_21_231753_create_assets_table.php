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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->integer('purchase_order_id')->nullable();
            $table->string('barcode')->nullable();
            $table->integer('product_id')->nullable();
            $table->string('item_name')->nullable();
            $table->date('generate_date')->nullable();
            $table->date('exp_date')->nullable();
            $table->integer('company_id')->nullable();
            $table->string('generate_by')->nullable();
            $table->text('notes')->nullable();
            $table->string('owner')->nullable();
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
        Schema::dropIfExists('assets');
    }
};
