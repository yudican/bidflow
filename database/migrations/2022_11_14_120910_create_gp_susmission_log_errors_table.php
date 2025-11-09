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
        Schema::create('gp_susmission_log_errors', function (Blueprint $table) {
            $table->id();
            $table->string('list_order_gp_id')->nullable();
            $table->string('ginee_id')->nullable();
            $table->text('error_message')->nullable();
            $table->string('type')->nullable();
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
        Schema::dropIfExists('gp_susmission_log_errors');
    }
};
