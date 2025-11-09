<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('id_transaksi');
            $table->string('invoice')->nullable();
            $table->foreignId('payment_method_id');
            $table->foreignId('product_id');
            $table->foreignUuid('user_id');
            $table->foreignId('brand_id');
            $table->foreignId('voucher_id')->nullable();
            $table->string('nominal', 15);
            $table->string('diskon', 15)->nullable()->default(0);
            $table->char('status', 1);
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
        Schema::dropIfExists('transactions');
    }
}
