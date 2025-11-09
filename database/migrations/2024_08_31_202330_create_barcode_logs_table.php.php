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
        Schema::create('barcode_logs', function (Blueprint $table) {
            $table->id();
            $table->string('barcode_parent')->nullable();
            $table->string('barcode_child')->nullable();
            $table->string('batch_id')->nullable();
            $table->string('status')->nullable();
            $table->string('purchase_order_id')->nullable();
            $table->string('created_by')->nullable();
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
        //
    }
};
