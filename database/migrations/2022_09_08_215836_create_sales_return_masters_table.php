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
        Schema::create('sales_return_masters', function (Blueprint $table) {
            $table->id();
            $table->string('uid_retur')->nullable();
            $table->string('sr_number')->nullable();
            $table->string('order_number')->nullable();
            $table->foreignId('brand_id')->nullable();
            $table->foreignUuid('contact')->nullable();
            $table->foreignUuid('sales')->nullable();
            $table->foreignId('payment_terms')->nullable();
            $table->foreignId('warehouse_id')->nullable();
            $table->text('shipping_address')->nullable();
            $table->text('warehouse_address')->nullable();
            $table->text('notes')->nullable();
            $table->string('total')->nullable();
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
        Schema::dropIfExists('sales_return_masters');
    }
};
