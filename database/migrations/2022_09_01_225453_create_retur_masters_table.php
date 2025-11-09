<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('retur_masters', function (Blueprint $table) {
            $table->id();
            $table->string('uid_retur')->nullable();
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
        Schema::dropIfExists('retur_masters');
    }
}
