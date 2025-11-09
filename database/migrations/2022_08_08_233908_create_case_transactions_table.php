<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCaseTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('case_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->nullable();
            $table->foreignUuid('contact')->nullable();
            $table->string('email')->nullable();
            $table->string('total')->nullable();
            $table->date('transaction_date')->nullable();
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
        Schema::dropIfExists('case_transactions');
    }
}
