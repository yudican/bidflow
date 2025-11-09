<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRefundMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refund_masters', function (Blueprint $table) {
            $table->id();
            $table->string('uid_refund')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('handphone')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('type_case')->nullable();
            $table->text('alasan')->nullable();
            $table->string('transaction_from')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('transfer_photo')->nullable();
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
        Schema::dropIfExists('refund_masters');
    }
}
