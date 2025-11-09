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
        Schema::create('ongkir_logistic', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_ongkir_id')->constrained('master_ongkir');
            $table->foreignId('logistic_id')->constrained('logistics');
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
        Schema::dropIfExists('ongkir_logistic');
    }
};
