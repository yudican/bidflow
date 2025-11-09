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
        Schema::create('purchase_invoice_entry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_invoice_entry_id')->nullable();
            $table->foreignId('purchase_order_id')->nullable();
            $table->foreignId('product_id')->nullable();
            $table->string('uom')->nullable();
            $table->integer('qty')->nullable();
            $table->string('extended_cost')->nullable();
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
        Schema::dropIfExists('purchase_invoice_entry_items');
    }
};
