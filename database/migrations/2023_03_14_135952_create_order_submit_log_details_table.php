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
        Schema::create('order_submit_log_details', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('order_submit_log_id')->nullable();
            $table->foreignId('order_id')->nullable();
            $table->char('status')->nullable();
            $table->string('error_message')->nullable();
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
        Schema::dropIfExists('order_submit_log_details');
    }
};
