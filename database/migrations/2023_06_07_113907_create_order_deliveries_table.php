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
        Schema::create('order_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('uid_lead', 15);
            $table->foreignUuid('user_id');
            $table->unsignedBigInteger('product_need_id');
            $table->bigInteger('qty_delivered')->default(0);
            $table->string('resi')->nullable();
            $table->string('courier')->nullable();
            $table->string('sender_name')->nullable();
            $table->string('sender_phone')->nullable();
            $table->date('delivery_date')->nullable();
            $table->text('attachments')->nullable();
            $table->enum('status', ['delivery', 'cancel'])->default('delivery');
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
        Schema::dropIfExists('order_deliveries');
    }
};
