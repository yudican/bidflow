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
        Schema::create('purchase_requitition_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_requitition_id')->constrained('purchase_requititions');
            $table->string('item_name');
            $table->integer('item_qty');
            $table->string('item_unit');
            $table->string('item_price');
            $table->integer('item_tax');
            $table->string('item_note')->nullable();
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
        Schema::dropIfExists('purchase_requitition_items');
    }
};
