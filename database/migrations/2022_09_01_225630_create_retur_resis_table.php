<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturResisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('retur_resis', function (Blueprint $table) {
            $table->id();
            $table->string('uid_retur')->nullable();
            $table->string('expedition_name')->nullable();
            $table->string('resi')->nullable();
            $table->string('sender_name')->nullable();
            $table->string('sender_phone')->nullable();
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
        Schema::dropIfExists('retur_resis');
    }
}
