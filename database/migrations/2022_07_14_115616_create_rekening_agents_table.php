<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRekeningAgentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agent_rekenings', function (Blueprint $table) {
            $table->id();
            $table->string('nama_bank');
            $table->string('nomor_rekening');
            $table->string('nama_pemilik_rekening');
            $table->string('cabang')->nullable();
            $table->string('kode_bank')->nullable();
            $table->char('status', 1)->default(1);
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
        Schema::dropIfExists('agent_rekenings');
    }
}
