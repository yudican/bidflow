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
        Schema::create('refund_rekenings', function (Blueprint $table) {
            $table->id();
            $table->string('uid_refund', 15)->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
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
        Schema::dropIfExists('refund_rekenings');
    }
};
