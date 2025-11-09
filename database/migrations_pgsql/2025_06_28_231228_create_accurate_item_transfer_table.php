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
        Schema::create('accurate_item_transfer', function (Blueprint $table) {
            $table->id(); // auto increment
            $table->unsignedBigInteger('accurate_id')->unique(); 
            $table->string('approval_status')->nullable();
            $table->string('number')->nullable();
            $table->date('trans_date')->nullable();
            $table->text('description')->nullable();
            $table->string('char_field3')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->string('warehouse_name')->nullable();
            $table->unsignedBigInteger('reference_warehouse_id')->nullable();
            $table->string('reference_warehouse_name')->nullable();
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
        Schema::dropIfExists('accurate_item_transfer');
    }
};
