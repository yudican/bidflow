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
        Schema::create('master_bins', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->string('telepon')->nullable();
            $table->string('location')->nullable();
            $table->unsignedBigInteger('provinsi_id')->nullable();
            $table->unsignedBigInteger('kabupaten_id')->nullable();
            $table->unsignedBigInteger('kecamatan_id')->nullable();
            $table->unsignedBigInteger('kelurahan_id')->nullable();
            $table->unsignedBigInteger('kodepos')->nullable();
            $table->string('address')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->char('status', 1)->default(0);
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
        Schema::dropIfExists('master_bins');
    }
};
