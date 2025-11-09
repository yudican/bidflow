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
        Schema::create('undian_flimty', function (Blueprint $table) {
            $table->id();
            $table->string('nomor');
            $table->string('nama');
            $table->string('whatsapp');
            $table->string('city');
            $table->string('nomor_undian');
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
        Schema::dropIfExists('undian_flimty');
    }
};
