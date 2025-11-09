<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgentAddressRetursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agent_address_returs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->nullable();
            $table->string('alamat');
            $table->string('nama');
            $table->foreignId('provinsi_id')->nullable();
            $table->foreignId('kabupaten_id')->nullable();
            $table->foreignId('kecamatan_id')->nullable();
            $table->foreignId('kelurahan_id')->nullable();
            $table->char('kodepos', 6)->nullable();
            $table->string('telepon')->nullable();
            $table->text('catatan')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
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
        Schema::dropIfExists('agent_address_returs');
    }
}
