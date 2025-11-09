<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogThirdPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_third_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->string('third_transaction_id');
            $table->string('third_transaction_status')->nullable();
            $table->string('third_transaction_message')->nullable();
            $table->string('third_transaction_payment_type')->nullable();
            $table->string('third_transaction_gross_amount')->nullable();
            $table->string('third_transaction_fraud_status')->nullable();
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
        Schema::dropIfExists('log_third_payments');
    }
}
