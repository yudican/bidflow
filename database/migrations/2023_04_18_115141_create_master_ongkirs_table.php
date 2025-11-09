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
        Schema::create('master_ongkir', function (Blueprint $table) {
            $table->id();
            $table->string('nama_ogkir')->nullable();
            $table->string('kode_ongkir')->nullable();
            $table->string('harga_ongkir')->nullable();
            $table->boolean('status_ongkir')->default(1);
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
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
        Schema::dropIfExists('master_ongkir');
    }
};
