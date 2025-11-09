<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('address_users', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->string('nama')->nullable();
            $table->text('alamat')->nullable();
            $table->foreignId('provinsi_id')->nullable();
            $table->foreignId('kabupaten_id')->nullable();
            $table->foreignId('kecamatan_id')->nullable();
            $table->foreignId('kelurahan_id')->nullable();
            $table->char('kodepos', 6)->nullable();
            $table->string('telepon')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignUuid('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('address_users');
    }
}
