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
        Schema::create('order_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('uid_lead')->nullable();
            $table->string('title')->nullable();
            $table->string('order_number')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('transfer_number')->nullable();
            $table->foreignUuid('contact')->nullable();
            $table->foreignUuid('sales')->nullable();
            $table->foreignId('warehouse_id')->nullable();
            $table->unsignedBigInteger('master_bin_id')->nullable();
            $table->foreignUuid('user_created')->nullable();
            $table->foreignUuid('user_updated')->nullable();
            $table->foreignId('payment_term')->nullable();
            $table->string('preference_number')->nullable()->default('-');
            $table->string('notes')->nullable();
            $table->string('status')->nullable();
            $table->foreignId('address_id')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('shipping_type')->nullable();
            $table->foreignUuid('courier')->nullable();
            $table->string('status_penagihan')->nullable();
            $table->string('status_invoice')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status_pengiriman')->nullable();
            $table->date('grace_due_date')->nullable();
            $table->foreignId('company_id')->nullable();
            $table->date('assign_date')->nullable();
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
        Schema::dropIfExists('order_transfers');
    }
};
