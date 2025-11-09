<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_points', function (Blueprint $table) {
            $table->id();
            $table->string('point')->nullable();
            $table->integer('min_trans')->nullable();
            $table->integer('max_trans')->nullable();
            $table->integer('nominal')->nullable();
            $table->float('percentage')->nullable();
            $table->foreignId('product_id')->nullable();
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
        Schema::dropIfExists('master_points');
    }
}
