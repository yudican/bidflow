<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRefundResisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refund_resis', function (Blueprint $table) {
            $table->id();
            $table->string('uid_refund')->nullable();
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
        Schema::dropIfExists('refund_resis');
    }
}
