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
        Schema::create('list_order_gp_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_order_gp_id')->constrained('list_order_gp');
            $table->string('ginee_order_id');
            $table->string('so_number');
            $table->string('batch_number');
            $table->string('status')->default('failed'); // success, failed
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
        Schema::dropIfExists('list_order_gp_details');
    }
};
