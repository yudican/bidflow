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
        Schema::table('order_manuals', function (Blueprint $table) {
            $table->string('order_number')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('shipping_type')->nullable();
            $table->foreignId('address_id')->nullable();
            $table->string('notes')->nullable();
            $table->foreignUuid('courier')->nullable();
            $table->string('status_penagihan')->nullable();
            $table->string('status_invoice')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status_pengiriman')->nullable();
            $table->date('grace_due_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_manuals', function (Blueprint $table) {
            $table->dropColumn('order_number');
            $table->dropColumn('invoice_number');
            $table->dropColumn('reference_number');
            $table->dropColumn('shipping_type');
            $table->dropColumn('address_id');
            $table->dropColumn('notes');
            $table->dropColumn('courier');
            $table->dropColumn('status_penagihan');
            $table->dropColumn('status_invoice');
            $table->dropColumn('due_date');
            $table->dropColumn('status_pengiriman');
            $table->dropColumn('grace_due_date');
        });
    }
};
