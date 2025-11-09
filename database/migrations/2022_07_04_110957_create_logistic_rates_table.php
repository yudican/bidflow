<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogisticRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logistic_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('logistic_id');
            $table->string('logistic_rate_code');
            $table->string('logistic_rate_name');
            $table->boolean('logistic_rate_status')->default(true);
            $table->boolean('logistic_cod_status')->default(false);
            $table->foreign('logistic_id')->references('id')->on('logistics')->onDelete('cascade');
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
        Schema::dropIfExists('logistic_rates');
    }
}
