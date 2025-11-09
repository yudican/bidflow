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
        Schema::table('mp_order_lists', function (Blueprint $table) {
            $table->string('shipping_fee_non_cashlesh')->nullable();
            $table->string('platform_rebate')->nullable();
            $table->string('voucher_seller')->nullable();
            $table->string('shipping_fee_deference')->nullable();
            $table->string('platform_fulfilment')->nullable();
            $table->string('service_fee')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mp_order_lists', function (Blueprint $table) {
            $table->dropColumn('shipping_fee_non_cashlesh');
            $table->dropColumn('platform_rebate');
            $table->dropColumn('voucher_seller');
            $table->dropColumn('shipping_fee_deference');
            $table->dropColumn('platform_fulfilment');
            $table->dropColumn('service_fee');
        });
    }
};
