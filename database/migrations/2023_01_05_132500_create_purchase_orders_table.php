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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('created_by')->nullable();
            $table->foreignId('payment_term_id')->nullable();
            $table->foreignId('warehouse_id')->nullable();
            $table->foreignUuid('warehouse_user_id')->nullable();
            $table->foreignId('company_id')->nullable();
            $table->string('po_number')->nullable();
            $table->string('vendor_code')->nullable();
            $table->string('currency')->nullable();
            $table->text('notes')->nullable();
            $table->char('status', 1)->nullable();
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
        Schema::dropIfExists('purchase_orders');
    }
};
