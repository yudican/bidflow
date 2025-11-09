<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableTransactionAgents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_agents', function (Blueprint $table) {
            $table->id();
            $table->string('id_transaksi')->nullable();
            $table->foreignId('payment_method_id')->nullable();
            $table->foreignId('product_id')->nullable();
            $table->foreignUuid('user_id')->nullable();
            $table->foreignId('brand_id')->nullable();
            $table->foreignId('voucher_id')->nullable();
            $table->foreignId('address_user_id')->nullable();
            $table->foreignId('shipping_type_id')->nullable();
            $table->string('nominal', 15)->nullable();
            $table->string('diskon', 15)->nullable();
            $table->string('ongkir', 15)->nullable();
            $table->string('amount_to_pay', 15)->nullable();
            $table->string('payment_va_number', 191)->nullable();
            $table->string('payment_token', 191)->nullable();
            $table->string('payment_unique_code', 191)->nullable();
            $table->string('payment_qr_url', 191)->nullable();
            $table->boolean('payment_redirect')->nullable();
            $table->string('payment_redirect_url', 191)->nullable();
            $table->string('custommer_type', 191)->nullable();
            $table->string('resi', 191)->nullable();
            $table->string('note')->nullable();
            $table->char('status', 2)->nullable();
            $table->char('status_delivery', 2)->nullable();
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
        Schema::dropIfExists('transaction_agents');
    }
}
